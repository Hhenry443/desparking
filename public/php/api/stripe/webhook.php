<?php
/**
 * Stripe Webhook Handler
 *
 * Register this URL in your Stripe dashboard (or Stripe CLI for local dev):
 *   https://desparking.ddev.site/php/api/stripe/webhook.php
 *
 * Local dev with Stripe CLI:
 *   stripe listen --forward-to https://desparking.ddev.site/php/api/stripe/webhook.php
 *   (copy the whsec_... secret it prints and paste below)
 *
 * Events to enable:
 *   checkout.session.completed
 *   invoice.paid
 *   invoice.payment_failed
 *   customer.subscription.deleted
 */

ini_set('log_errors', 1);
ini_set('display_errors', 0);

$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
];
foreach ($possiblePaths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

header('Content-Type: application/json');

$payload    = file_get_contents('php://input');
$sigHeader  = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    error_log("Webhook signature verification failed: " . $e->getMessage());
    http_response_code(400);
    exit;
}

$conn = Dbh::getConnection();

switch ($event->type) {

    case 'checkout.session.completed':
        handleCheckoutComplete($event->data->object, $conn);
        break;

    case 'invoice.paid':
        handleInvoicePaid($event->data->object, $conn);
        break;

    case 'invoice.payment_failed':
        handleInvoicePaymentFailed($event->data->object, $conn);
        break;

    case 'customer.subscription.deleted':
        handleSubscriptionDeleted($event->data->object, $conn);
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

/**
 * Primary booking creation handler.
 * Called for both one-time payments and new subscriptions.
 * Idempotency is enforced before any write — safe to receive twice.
 */
function handleCheckoutComplete($session, PDO $conn): void
{
    $meta = $session->metadata;

    $carparkId   = (int) ($meta->carpark_id   ?? 0);
    $userId      = (int) ($meta->user_id      ?? 0);
    $vehicleId   = (int) ($meta->vehicle_id   ?? 0);
    $name        = (string) ($meta->name      ?? '');
    $start       = (string) ($meta->start     ?? '');
    $end         = (string) ($meta->end       ?? '');
    $isMonthly   = ($meta->is_monthly ?? '0') === '1';
    $ownerAmount = isset($meta->owner_amount) ? (int) $meta->owner_amount : null;

    if (!$carparkId || !$userId || !$start || !$end) {
        error_log("Webhook: missing metadata on session {$session->id}");
        return;
    }

    $paymentsModel = new WritePayments();

    if ($session->mode === 'subscription') {
        $subscriptionId = is_string($session->subscription)
            ? $session->subscription
            : $session->subscription->id;

        // Idempotency — already handled by return.php or a previous delivery
        if ($paymentsModel->subscriptionPaymentExists($subscriptionId)) {
            return;
        }

        $conn->beginTransaction();
        try {
            $bookingsModel = new WriteBookings();
            $bookingId = $bookingsModel->insertBooking(
                $carparkId, $name, $start, $end, $userId, $vehicleId, true
            );

            if (is_array($bookingId)) {
                throw new Exception("insertBooking failed: " . ($bookingId['message'] ?? ''));
            }

            $paymentsModel->insertPayment([
                'booking_id'               => $bookingId,
                'user_id'                  => $userId,
                'stripe_payment_intent_id' => null,
                'stripe_subscription_id'   => $subscriptionId,
                'stripe_customer_id'       => $session->customer,
                'amount'                   => $session->amount_total ?? 0,
                'owner_amount'             => $ownerAmount,
                'currency'                 => $session->currency ?? 'gbp',
                'type'                     => 'subscription',
                'status'                   => 'succeeded',
            ]);

            $conn->commit();
            error_log("Webhook: subscription booking {$bookingId} created for sub {$subscriptionId}");

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Webhook: subscription booking creation failed: " . $e->getMessage());
        }

    } else {
        // One-time payment
        $paymentIntentId = is_string($session->payment_intent)
            ? $session->payment_intent
            : $session->payment_intent->id;

        if ($paymentsModel->paymentExists($paymentIntentId)) {
            return;
        }

        $conn->beginTransaction();
        try {
            $bookingsModel = new WriteBookings();

            // Check capacity before inserting
            $carparkReader = new ReadCarparks();
            $carpark       = $carparkReader->getCarparkById($carparkId);
            $capacity      = (int) ($carpark['carpark_capacity'] ?? 1);
            $overlapping   = $bookingsModel->countOverlappingBookings($carparkId, $start, $end);

            if ($overlapping >= $capacity) {
                error_log("Webhook: carpark {$carparkId} full for {$start}–{$end}, refusing booking for pi {$paymentIntentId}");
                $conn->rollBack();
                return;
            }

            $bookingId = $bookingsModel->insertBooking(
                $carparkId, $name, $start, $end, $userId, $vehicleId, false
            );

            if (is_array($bookingId)) {
                throw new Exception("insertBooking failed: " . ($bookingId['message'] ?? ''));
            }

            $paymentsModel->insertPayment([
                'booking_id'               => $bookingId,
                'user_id'                  => $userId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_subscription_id'   => null,
                'stripe_customer_id'       => $session->customer,
                'amount'                   => $session->amount_total ?? 0,
                'owner_amount'             => $ownerAmount,
                'currency'                 => $session->currency ?? 'gbp',
                'type'                     => 'initial',
                'status'                   => 'succeeded',
            ]);

            $conn->commit();
            error_log("Webhook: one-time booking {$bookingId} created for pi {$paymentIntentId}");

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Webhook: one-time booking creation failed: " . $e->getMessage());
        }
    }
}

/**
 * Subscription renewed successfully — push the booking_end date forward.
 */
function handleInvoicePaid($invoice, PDO $conn): void
{
    $subscriptionId = $invoice->subscription;
    if (!$subscriptionId) return;

    // Get the new period end from the first line item
    $periodEnd = $invoice->lines->data[0]->period->end ?? null;
    if (!$periodEnd) return;

    $newEnd = date('Y-m-d H:i:s', $periodEnd);

    $stmt = $conn->prepare("
        UPDATE bookings b
        INNER JOIN payments p ON p.booking_id = b.booking_id
        SET b.booking_end = :new_end
        WHERE p.stripe_subscription_id = :sub_id
        LIMIT 1
    ");
    $stmt->execute([':new_end' => $newEnd, ':sub_id' => $subscriptionId]);

    error_log("Webhook: invoice.paid — extended booking to {$newEnd} for sub {$subscriptionId}");
}

/**
 * Subscription payment failed — log it. Stripe will retry automatically.
 * We do not revoke access here; that happens on customer.subscription.deleted
 * after all retries are exhausted.
 */
function handleInvoicePaymentFailed($invoice, PDO $conn): void
{
    $subscriptionId = $invoice->subscription;
    error_log("Webhook: invoice.payment_failed for sub {$subscriptionId} — Stripe will retry");
}

/**
 * Subscription fully deleted (all retries exhausted, or cancelled immediately).
 * Mark the booking as cancelled.
 */
function handleSubscriptionDeleted($subscription, PDO $conn): void
{
    $stmt = $conn->prepare("
        UPDATE bookings b
        INNER JOIN payments p ON p.booking_id = b.booking_id
        SET b.booking_status = 'cancelled'
        WHERE p.stripe_subscription_id = :sub_id
        LIMIT 1
    ");
    $stmt->execute([':sub_id' => $subscription->id]);

    error_log("Webhook: subscription {$subscription->id} deleted — booking marked cancelled");
}
