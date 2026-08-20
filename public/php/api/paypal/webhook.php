<?php

/**
 * PayPal Webhook Handler
 *
 * Register this URL in the PayPal Developer Dashboard (Sandbox and Live apps
 * have separate webhooks — each has its own Webhook ID, see php/config/paypal.php):
 *   https://desparking.ddev.site/php/api/paypal/webhook.php
 *
 * Events to enable:
 *   PAYMENT.CAPTURE.COMPLETED
 *   BILLING.SUBSCRIPTION.ACTIVATED
 *   PAYMENT.SALE.COMPLETED           (fires per subscription billing cycle)
 *   BILLING.SUBSCRIPTION.PAYMENT.FAILED
 *   BILLING.SUBSCRIPTION.CANCELLED
 *
 * This is the backstop, not the primary path: the browser-driven
 * capture-order.php / confirm-subscription.php calls normally create the
 * booking first. Every handler here is idempotent against that.
 */

ini_set('log_errors', 1);
ini_set('display_errors', 0);

$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
];
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/BookingFulfillment.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/helpers/FlowLog.php';

header('Content-Type: application/json');

$payload = file_get_contents('php://input');

$headers = [];
foreach (getallheaders() as $name => $value) {
    $headers[strtoupper($name)] = $value;
}

try {
    if (!PayPalClient::verifyWebhookSignature($headers, $payload)) {
        error_log('PayPal webhook signature verification failed');
        FlowLog::write('webhook', 'signature_failed');
        http_response_code(400);
        exit;
    }
} catch (Exception $e) {
    error_log('PayPal webhook verification error: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

$event = json_decode($payload, true);
$type  = $event['event_type'] ?? '';
$res   = $event['resource'] ?? [];

FlowLog::write('webhook', 'event_received', null, $res['id'] ?? null, $type);

$conn = Dbh::getConnection();

switch ($type) {
    case 'PAYMENT.CAPTURE.COMPLETED':
        handleCaptureCompleted($res, $conn);
        break;

    case 'BILLING.SUBSCRIPTION.ACTIVATED':
        handleSubscriptionActivated($res, $conn);
        break;

    case 'PAYMENT.SALE.COMPLETED':
        handleSubscriptionRenewed($res, $conn);
        break;

    case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
        handleSubscriptionPaymentFailed($res, $conn);
        break;

    case 'BILLING.SUBSCRIPTION.CANCELLED':
        handleSubscriptionCancelled($res, $conn);
        break;

    default:
        // Unhandled event type — ignore
        break;
}

http_response_code(200);
echo json_encode(['received' => true]);


// =============================================================================
// EVENT HANDLERS
// =============================================================================

function handleCaptureCompleted(array $capture, PDO $conn): void
{
    $orderId = $capture['supplementary_data']['related_ids']['order_id'] ?? null;
    $captureId = $capture['id'] ?? null;

    if (!$orderId || !$captureId) {
        error_log('Webhook: capture completed but missing order/capture id');
        return;
    }

    $amountPence = Money::decimalToPence($capture['amount']['value'] ?? '0');
    $currency    = strtolower($capture['amount']['currency_code'] ?? 'GBP');
    $payerId     = $capture['payer']['payer_id'] ?? null;

    $paymentsModel = new WritePayments();
    $checkout      = $paymentsModel->getPendingCheckoutByOrderId($orderId);
    $checkoutType  = $checkout['type'] ?? 'booking';

    if ($checkoutType === 'extension') {
        fulfillExtension($orderId, $captureId, $conn);
        return;
    }

    fulfillOrderBooking($orderId, $captureId, $payerId, $amountPence, $currency, $conn);
}

function handleSubscriptionActivated(array $subscription, PDO $conn): void
{
    $subscriptionId = $subscription['id'] ?? null;
    $checkoutRef    = $subscription['custom_id'] ?? null;
    $payerId        = $subscription['subscriber']['payer_id'] ?? null;

    if (!$subscriptionId) {
        return;
    }

    $paymentsModel = new WritePayments();

    if ($checkoutRef && !$paymentsModel->getPendingCheckoutBySubscriptionId($subscriptionId)) {
        $paymentsModel->linkSubscriptionToPendingCheckout($checkoutRef, $subscriptionId);
    }

    fulfillSubscriptionBooking($subscriptionId, $payerId, $conn);
}

/**
 * Subscription renewed successfully — push the booking_end date forward one month.
 * (PayPal's PAYMENT.SALE.COMPLETED resource has no "new period end" field like
 * Stripe's invoice line items, so we advance from the current stored end date —
 * safe since every plan we create is a fixed monthly cycle.)
 */
function handleSubscriptionRenewed(array $sale, PDO $conn): void
{
    $subscriptionId = $sale['billing_agreement_id'] ?? null;
    if (!$subscriptionId) {
        return; // one-off sale, not a subscription payment
    }

    $stmt = $conn->prepare("
        SELECT b.booking_id, b.booking_end
        FROM bookings b
        INNER JOIN payments p ON p.booking_id = b.booking_id
        WHERE p.paypal_subscription_id = :sub_id
        LIMIT 1
    ");
    $stmt->execute([':sub_id' => $subscriptionId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        return; // initial activation payment — handled by handleSubscriptionActivated
    }

    $newEnd = date('Y-m-d H:i:s', strtotime($booking['booking_end'] . ' +1 month'));

    $stmt = $conn->prepare("UPDATE bookings SET booking_end = :new_end WHERE booking_id = :id");
    $stmt->execute([':new_end' => $newEnd, ':id' => $booking['booking_id']]);

    error_log("Webhook: subscription renewed — extended booking to {$newEnd} for sub {$subscriptionId}");
}

/**
 * Subscription payment failed — log it. PayPal will retry per the plan's
 * payment_failure_threshold; we do not revoke access here, that happens on
 * BILLING.SUBSCRIPTION.CANCELLED once retries are exhausted.
 */
function handleSubscriptionPaymentFailed(array $subscription, PDO $conn): void
{
    $subscriptionId = $subscription['id'] ?? null;
    error_log("Webhook: subscription payment failed for sub {$subscriptionId} — PayPal will retry");

    if ($subscriptionId) {
        try {
            (new Notifier($conn))->subscriptionPaymentFailed($subscriptionId);
        } catch (Throwable $e) {
            error_log('Notification failed [subscriptionPaymentFailed]: ' . $e->getMessage());
        }
    }
}

/**
 * Subscription fully cancelled. Mark the booking as cancelled.
 */
function handleSubscriptionCancelled(array $subscription, PDO $conn): void
{
    $subscriptionId = $subscription['id'] ?? null;
    if (!$subscriptionId) {
        return;
    }

    $stmt = $conn->prepare("
        UPDATE bookings b
        INNER JOIN payments p ON p.booking_id = b.booking_id
        SET b.booking_status = 'cancelled'
        WHERE p.paypal_subscription_id = :sub_id
        LIMIT 1
    ");
    $stmt->execute([':sub_id' => $subscriptionId]);

    error_log("Webhook: subscription {$subscriptionId} cancelled — booking marked cancelled");
}
