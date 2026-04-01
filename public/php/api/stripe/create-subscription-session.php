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
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
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
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
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

$carparkID = $data['carpark_id'] ?? null;

if (!$carparkID) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing carpark_id']);
    exit;
}

try {
    // Look up the carpark name and monthly rate
    $ReadCarparks = new ReadCarparks();
    $carpark = $ReadCarparks->getCarparkById($carparkID);

    if (!$carpark) {
        http_response_code(400);
        echo json_encode(['error' => 'Car park not found']);
        exit;
    }

    $ReadRates = new ReadRates();
    $monthlyRate = $ReadRates->getMonthlyRateByCarpark((int) $carparkID);

    if (!$monthlyRate || empty($monthlyRate['price'])) {
        http_response_code(400);
        echo json_encode(['error' => 'No monthly rate configured for this car park']);
        exit;
    }

    $priceCents  = (int) $monthlyRate['price'];
    $feeCents    = (int) round($priceCents * 0.19);
    $subtotal    = $priceCents + $feeCents;
    $stripeCents = (int) round($subtotal * 0.015 + 20);
    $pending     = $_SESSION['pending_booking'] ?? [];

    $stripe = new \Stripe\StripeClient(['api_key' => STRIPE_SECRET_KEY]);

    $checkout_session = $stripe->checkout->sessions->create([
        'line_items' => [
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Monthly Parking – ' . $carpark['carpark_name']],
                    'unit_amount'  => $priceCents,
                    'recurring'    => ['interval' => 'month'],
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Service Fee'],
                    'unit_amount'  => $feeCents,
                    'recurring'    => ['interval' => 'month'],
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Payment Processing Fee'],
                    'unit_amount'  => $stripeCents,
                    'recurring'    => ['interval' => 'month'],
                ],
                'quantity' => 1,
            ],
        ],
        'mode'       => 'subscription',
        'ui_mode'    => 'embedded',
        'return_url' => (getenv('ENVIRONMENT') === 'production' ? 'https://blog.henryyy.com' : 'https://blog.henryyy.com') . '/return.php?session_id={CHECKOUT_SESSION_ID}&type=subscription',
        'metadata'   => [
            'carpark_id'   => (string) ($pending['carpark_id'] ?? ''),
            'user_id'      => (string) ($pending['user_id'] ?? ''),
            'vehicle_id'   => (string) ($pending['vehicle_id'] ?? ''),
            'registration' => (string) ($pending['registration'] ?? ''),
            'name'         => (string) ($pending['name'] ?? ''),
            'start'        => (string) ($pending['start'] ?? ''),
            'end'          => (string) ($pending['end'] ?? ''),
            'is_monthly'   => '1',
            'owner_amount' => (string) (int) round($priceCents * 0.98),
        ],
    ]);

    echo json_encode(['clientSecret' => $checkout_session->client_secret]);
} catch (Exception $e) {
    error_log("Stripe subscription session error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
