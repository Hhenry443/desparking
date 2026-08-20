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
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/BookingFulfillment.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/helpers/FlowLog.php';

ob_clean();
header('Content-Type: application/json');

$data    = json_decode(file_get_contents('php://input'), true);
$orderId = $data['orderID'] ?? null;

if (!$orderId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing orderID']);
    exit;
}

try {
    $capture = PayPalClient::captureOrder($orderId);

    if (($capture['status'] ?? '') !== 'COMPLETED') {
        throw new Exception('Order not completed: ' . ($capture['status'] ?? 'unknown'));
    }

    $purchaseUnit = $capture['purchase_units'][0] ?? [];
    $captureObj   = $purchaseUnit['payments']['captures'][0] ?? [];
    $captureId    = $captureObj['id'] ?? null;

    if (!$captureId) {
        throw new Exception('No capture id in PayPal response');
    }

    $amountPence = Money::decimalToPence($captureObj['amount']['value'] ?? '0');
    $currency    = strtolower($captureObj['amount']['currency_code'] ?? 'GBP');
    $payerId     = $capture['payer']['payer_id'] ?? null;

    $conn = Dbh::getConnection();

    $paymentsModel = new WritePayments();
    $checkout      = $paymentsModel->getPendingCheckoutByOrderId($orderId);
    $type          = $checkout['type'] ?? 'booking';

    if ($type === 'extension') {
        $bookingId = fulfillExtension($orderId, $captureId, $conn);
        unset($_SESSION['pending_extension']);
        echo json_encode([
            'redirect' => $bookingId
                ? ('/return.php?order_id=' . urlencode($orderId) . '&type=extension')
                : ('/account.php?error=' . urlencode('Payment received, updating your booking. Check your account shortly.')),
        ]);
        exit;
    }

    $bookingId = fulfillOrderBooking($orderId, $captureId, $payerId, $amountPence, $currency, $conn);
    unset($_SESSION['pending_booking']);

    echo json_encode([
        'redirect' => $bookingId
            ? ('/return.php?order_id=' . urlencode($orderId) . '&type=booking')
            : ('/account.php?error=' . urlencode('Booking is being processed. Check your bookings shortly.')),
    ]);
} catch (Exception $e) {
    error_log('PayPal capture-order error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
