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
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';

ob_clean();
header('Content-Type: application/json');

$data      = json_decode(file_get_contents('php://input'), true);
$carparkID = $data['carpark_id'] ?? null;

if (!$carparkID) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing carpark_id']);
    exit;
}

try {
    $ReadCarparks = new ReadCarparks();
    $carpark      = $ReadCarparks->getCarparkById($carparkID);

    if (!$carpark) {
        http_response_code(400);
        echo json_encode(['error' => 'Car park not found']);
        exit;
    }

    $ReadRates   = new ReadRates();
    $monthlyRate = $ReadRates->getMonthlyRateByCarpark((int) $carparkID);

    if (!$monthlyRate || empty($monthlyRate['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No monthly rate configured for this car park']);
        exit;
    }

    $priceCents  = (int) $monthlyRate['price'];
    $feeCents    = (int) round($priceCents * 0.19);
    $subtotal    = $priceCents + $feeCents;
    $paypalCents = (int) round($subtotal * 0.029 + 30);
    $totalCents  = $subtotal + $paypalCents;
    $pending     = $_SESSION['pending_booking'] ?? [];

    $productId = PayPalClient::getOrCreateProduct();
    $planId    = PayPalClient::createPlan(
        $productId,
        $totalCents,
        'Monthly Parking – ' . $carpark['carpark_name']
    );

    $paymentsModel = new WritePayments();
    $checkoutRef   = $paymentsModel->insertPendingCheckout([
        'type'         => 'subscription',
        'carpark_id'   => (int) $carparkID,
        'user_id'      => $pending['user_id'] ?? null,
        'vehicle_id'   => $pending['vehicle_id'] ?? null,
        'registration' => $pending['registration'] ?? null,
        'name'         => $pending['name'] ?? '',
        'email'        => $pending['email'] ?? null,
        'start'        => $pending['start'] ?? date('Y-m-d H:i:s'),
        'end'          => $pending['end'] ?? date('Y-m-d H:i:s', strtotime('+1 month')),
        'amount'       => $totalCents,
        'owner_amount' => (int) round($priceCents * 0.98),
    ]);

    echo json_encode(['planId' => $planId, 'checkoutRef' => $checkoutRef]);
} catch (Exception $e) {
    error_log('PayPal create-subscription-session error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
