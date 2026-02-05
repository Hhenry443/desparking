<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$extensionData = $_SESSION['pending_extension'] ?? null;

if (!$extensionData) {
    header("Location: /account.php?error=" . urlencode("Payment session not found"));
    exit;
}

$bookingID = $extensionData['booking_id'];
$amount = $extensionData['amount'];
$currency = $extensionData['currency'] ?? 'gbp';

// Create Stripe Checkout Session
try {
    $stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);
    
    // set new start and end times in session
    $_SESSION['new_start'] = $extensionData['new_start'];
    $_SESSION['new_end'] = $extensionData['new_end'];
    
    $checkout_session = $stripe->checkout->sessions->create([
        'line_items' => [[
            'price_data' => [
                'currency' => $currency,
                'product_data' => [
                    'name' => 'Booking Extension - Additional Payment',
                    'description' => 'Additional charge for booking #' . $bookingID,
                ],
                'unit_amount' => $amount, // Already in pence
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'ui_mode' => 'embedded',
        'return_url' => 'https://desparking.ddev.site/return.php?session_id={CHECKOUT_SESSION_ID}&type=extension&booking_id=' . $bookingID,
        'metadata' => [
            'booking_id' => $bookingID,
            'user_id' => $_SESSION['user_id'],
            'type' => 'extension',
            'new_start' => $extensionData['new_start'],
            'new_end' => $extensionData['new_end'],
        ],
    ]);
    
    $clientSecret = $checkout_session->client_secret;
    
    error_log("Checkout session created for booking extension: " . $checkout_session->id);
    
} catch (Exception $e) {
    error_log("Stripe error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    header("Location: /account.php?error=" . urlencode("Payment session creation failed"));
    exit;
}

function pounds(int $pence): string {
    return '£' . number_format($pence / 100, 2);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Additional Payment Required · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body class="min-h-screen bg-[#ebebeb] pt-24">
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/partials/navbar.php'; ?>

<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 mb-12">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Additional Payment Required</h1>
        <p class="text-gray-500 text-sm mt-1">
            Complete your payment to confirm the booking extension.
        </p>
    </div>

    <!-- Payment Summary -->
    <div class="bg-amber-50 border-2 border-amber-200 rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-700 font-medium">Amount Due:</span>
            <span class="text-2xl font-bold text-gray-900"><?= pounds($amount) ?></span>
        </div>
        <p class="text-sm text-amber-800">
            <i class="fa-solid fa-info-circle mr-1"></i>
            This is the additional cost for extending your booking time.
        </p>
    </div>

    <!-- Stripe Embedded Checkout -->
    <div id="checkout">
        <!-- Stripe Checkout will be mounted here -->
    </div>

    <!-- Security Notice -->
    <div class="text-xs text-gray-500 text-center mt-6">
        <i class="fa-solid fa-lock mr-1"></i>
        Payments are securely processed by Stripe. We never store your card details.
    </div>

</div>

<script>
// Initialize Stripe
const stripe = Stripe('pk_test_51QcmGqFqIlX5PkTwDEPdg8QF3lIRn0RlNQaMbXc5ZFDq4dJAYyqTVPVD14GqXawbTuEbEAGxGi1bSQM7iZEDZOy800FCfWGJqk');

// Initialize the embedded checkout (async)
(async () => {
    const checkout = await stripe.initEmbeddedCheckout({
        clientSecret: '<?= $clientSecret ?>'
    });

    // Mount the checkout
    checkout.mount('#checkout');
})();
</script>

</body>
</html>