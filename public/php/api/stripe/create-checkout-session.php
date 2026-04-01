<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_start();

// Try to find vendor autoload
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
    echo json_encode([
        'error' => 'Composer autoload not found. Tried paths: ' . implode(', ', $possiblePaths),
        'current_dir' => __DIR__,
        'document_root' => $_SERVER['DOCUMENT_ROOT']
    ]);
    exit;
}

try {
    require_once $autoloadPath;
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load dependencies: ' . $e->getMessage()]);
    exit;
}

ob_clean();
header('Content-Type: application/json');

// Get JSON data from request body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Get data from JSON
$carparkID = $data['carpark_id'] ?? null;
$startTimeStr = $data['start_time'] ?? null;
$endTimeStr = $data['end_time'] ?? null;

// Log what we received
error_log("Received: carpark_id=$carparkID, start_time=$startTimeStr, end_time=$endTimeStr");

if (!$carparkID || !$startTimeStr || !$endTimeStr) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters. Received: carpark_id=' . ($carparkID ?? 'null') . ', start_time=' . ($startTimeStr ?? 'null') . ', end_time=' . ($endTimeStr ?? 'null')]);
    exit;
}

try {
    $startTime = new DateTime($startTimeStr);
    $endTime = new DateTime($endTimeStr);

    // Calculate duration
    $interval = $startTime->diff($endTime);
    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

    error_log("Calculated duration: $totalMinutes minutes");

    // Calculate Price
    $rateReader = new ReadRates();
    $totalCents = $rateReader->calculateOptimalPrice($carparkID, $totalMinutes);

    error_log("Calculated price: $totalCents cents");

    // If price is 0, set a minimum
    if ($totalCents <= 0) {
        $totalCents = 100; // Minimum £1.00
        error_log("Price was 0, setting to minimum: $totalCents");
    }

    $feeCents    = (int) round($totalCents * 0.19);
    $subtotal    = $totalCents + $feeCents;
    $stripeCents = (int) round($subtotal * 0.015 + 20);

    $pending = $_SESSION['pending_booking'] ?? [];

    $stripe = new \Stripe\StripeClient(["api_key" => STRIPE_SECRET_KEY]);

    $checkout_session = $stripe->checkout->sessions->create([
        'line_items' => [
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Parking Session (' . $totalMinutes . ' mins)'],
                    'unit_amount'  => $totalCents,
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Service Fee'],
                    'unit_amount'  => $feeCents,
                ],
                'quantity' => 1,
            ],
            [
                'price_data' => [
                    'currency'     => 'gbp',
                    'product_data' => ['name' => 'Payment Processing Fee'],
                    'unit_amount'  => $stripeCents,
                ],
                'quantity' => 1,
            ],
        ],
        'mode'              => 'payment',
        'customer_creation' => 'always',
        'ui_mode'           => 'embedded',
        'return_url'        => (getenv('ENVIRONMENT') === 'production' ? 'https://blog.henryyy.com' : 'https://blog.henryyy.com') . '/return.php?session_id={CHECKOUT_SESSION_ID}',
        'metadata'          => [
            'carpark_id'   => (string) ($pending['carpark_id'] ?? ''),
            'user_id'      => (string) ($pending['user_id'] ?? ''),
            'vehicle_id'   => (string) ($pending['vehicle_id'] ?? ''),
            'registration' => (string) ($pending['registration'] ?? ''),
            'name'         => (string) ($pending['name'] ?? ''),
            'start'        => (string) ($pending['start'] ?? ''),
            'end'          => (string) ($pending['end'] ?? ''),
            'is_monthly'   => '0',
            'owner_amount' => (string) (int) round($totalCents * 0.98),
        ],
    ]);

    error_log("Stripe session created successfully");
    echo json_encode(['clientSecret' => $checkout_session->client_secret]);
} catch (Exception $e) {
    error_log("Stripe error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
