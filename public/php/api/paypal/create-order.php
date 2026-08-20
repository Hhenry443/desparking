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
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
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

try {
    require_once $autoloadPath;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

ob_clean();
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$carparkID    = $data['carpark_id'] ?? null;
$startTimeStr = $data['start_time'] ?? null;
$endTimeStr   = $data['end_time'] ?? null;

if (!$carparkID || !$startTimeStr || !$endTimeStr) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

try {
    $startTime = new DateTime($startTimeStr);
    $endTime   = new DateTime($endTimeStr);
    $interval  = $startTime->diff($endTime);
    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    $rateReader  = new ReadRates();
    $totalCents  = $rateReader->calculateOptimalPrice($carparkID, $totalMinutes);

    if ($totalCents <= 0) {
        $totalCents = 100; // Minimum £1.00
    }

    $feeCents    = (int) round($totalCents * 0.19);
    $subtotal    = $totalCents + $feeCents;
    $paypalCents = (int) round($subtotal * 0.029 + 30); // PayPal UK domestic online rate estimate
    $grandTotal  = $subtotal + $paypalCents;

    $pending = $_SESSION['pending_booking'] ?? [];

    $order = PayPalClient::createOrder([[
        'amount' => [
            'currency_code' => 'GBP',
            'value'         => Money::penceToDecimal($grandTotal),
            'breakdown'     => [
                'item_total' => [
                    'currency_code' => 'GBP',
                    'value'         => Money::penceToDecimal($grandTotal),
                ],
            ],
        ],
        'items' => [
            [
                'name'         => 'Parking Session (' . $totalMinutes . ' mins)',
                'quantity'     => '1',
                'unit_amount'  => ['currency_code' => 'GBP', 'value' => Money::penceToDecimal($totalCents)],
            ],
            [
                'name'         => 'Service Fee',
                'quantity'     => '1',
                'unit_amount'  => ['currency_code' => 'GBP', 'value' => Money::penceToDecimal($feeCents)],
            ],
            [
                'name'         => 'Payment Processing Fee',
                'quantity'     => '1',
                'unit_amount'  => ['currency_code' => 'GBP', 'value' => Money::penceToDecimal($paypalCents)],
            ],
        ],
    ]]);

    $paymentsModel = new WritePayments();
    $paymentsModel->insertPendingCheckout([
        'order_id'     => $order['id'],
        'type'         => 'booking',
        'carpark_id'   => (int) ($pending['carpark_id'] ?? $carparkID),
        'user_id'      => $pending['user_id'] ?? null,
        'vehicle_id'   => $pending['vehicle_id'] ?? null,
        'registration' => $pending['registration'] ?? null,
        'name'         => $pending['name'] ?? '',
        'email'        => $pending['email'] ?? null,
        'start'        => $pending['start'] ?? $startTimeStr,
        'end'          => $pending['end'] ?? $endTimeStr,
        'owner_amount' => (int) round($totalCents * 0.98),
    ]);

    echo json_encode(['orderId' => $order['id']]);
} catch (Exception $e) {
    error_log("PayPal create-order error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
