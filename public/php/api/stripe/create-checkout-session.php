<?php
require 'vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadRates.php';

header('Content-Type: application/json');

// Get data from frontend (e.g., via POST or URL params)
$carparkID = $_POST['carpark_id'] ?? 100;
$startTime = new DateTime($_POST['start_time']); // e.g. "2025-12-17 14:00"
$endTime = new DateTime($_POST['end_time']);

// Calculate duration
$interval = $startTime->diff($endTime);
$totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

// Calculate Price
$rateReader = new ReadRates();
$totalCents = $rateReader->calculateOptimalPrice($carparkID, $totalMinutes);

try {
    $stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);

    $checkout_session = $stripe->checkout->sessions->create([
        'line_items' => [[
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Parking Session (' . $totalMinutes . ' mins)',
                ],
                'unit_amount' => $totalCents, // Calculated dynamically
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'ui_mode' => 'embedded',
        'return_url' => 'https://desparking.ddev.site/book/return?session_id={CHECKOUT_SESSION_ID}',
    ]);

    echo json_encode(['clientSecret' => $checkout_session->client_secret]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
