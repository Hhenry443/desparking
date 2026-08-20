<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();

$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
];
$autoloadPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

if (!$autoloadPath) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Composer autoload not found.']);
    exit;
}

require_once $autoloadPath;
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/BookingFulfillment.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/helpers/FlowLog.php';

ob_clean();
header('Content-Type: application/json');

$data           = json_decode(file_get_contents('php://input'), true);
$subscriptionId = $data['subscriptionID'] ?? null;
$checkoutRef    = $data['checkoutRef'] ?? null;

if (!$subscriptionId || !$checkoutRef) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing subscriptionID or checkoutRef']);
    exit;
}

try {
    $paymentsModel = new WritePayments();
    $paymentsModel->linkSubscriptionToPendingCheckout($checkoutRef, $subscriptionId);

    $subscription = PayPalClient::getSubscription($subscriptionId);
    $payerId      = $subscription['subscriber']['payer_id'] ?? null;

    $conn      = Dbh::getConnection();
    $bookingId = fulfillSubscriptionBooking($subscriptionId, $payerId, $conn);

    unset($_SESSION['pending_booking']);

    echo json_encode([
        'redirect' => $bookingId
            ? ('/return.php?subscription_id=' . urlencode($subscriptionId) . '&type=subscription')
            : ('/account.php?error=' . urlencode('Subscription is being processed. Check your bookings shortly.')),
    ]);
} catch (Exception $e) {
    error_log('PayPal confirm-subscription error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
