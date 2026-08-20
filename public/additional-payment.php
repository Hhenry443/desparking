<?php
$title = "Additional Payment";

require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';

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
$amount    = $extensionData['amount'];
$currency  = strtoupper($extensionData['currency'] ?? 'GBP');

try {
    $conn = Dbh::getConnection();
    $stmt = $conn->prepare("SELECT booking_carpark_id, booking_name FROM bookings WHERE booking_id = :id LIMIT 1");
    $stmt->execute([':id' => $bookingID]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        throw new Exception("Booking not found");
    }

    $order = PayPalClient::createOrder([[
        'amount' => [
            'currency_code' => $currency,
            'value'         => Money::penceToDecimal($amount),
            'breakdown'     => [
                'item_total' => ['currency_code' => $currency, 'value' => Money::penceToDecimal($amount)],
            ],
        ],
        'items' => [[
            'name'        => 'Booking Extension - Additional Payment #' . $bookingID,
            'quantity'    => '1',
            'unit_amount' => ['currency_code' => $currency, 'value' => Money::penceToDecimal($amount)],
        ]],
    ]]);

    $paymentsModel = new WritePayments();
    $paymentsModel->insertPendingCheckout([
        'order_id'   => $order['id'],
        'type'       => 'extension',
        'carpark_id' => (int) $booking['booking_carpark_id'],
        'booking_id' => (int) $bookingID,
        'user_id'    => $_SESSION['user_id'],
        'name'       => $booking['booking_name'],
        'start'      => $extensionData['new_start'],
        'end'        => $extensionData['new_end'],
    ]);

    $orderId = $order['id'];

    error_log("PayPal order created for booking extension: " . $orderId);
} catch (Exception $e) {
    error_log("PayPal error: " . $e->getMessage());
    header("Location: /account.php?error=" . urlencode("Payment session creation failed"));
    exit;
}

function pounds(int $pence): string
{
    return '£' . number_format($pence / 100, 2);
}
?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


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

        <!-- PayPal Buttons -->
        <div id="checkout">
            <!-- PayPal Buttons will be mounted here -->
        </div>

        <!-- Security Notice -->
        <div class="text-xs text-gray-500 text-center mt-6">
            <i class="fa-solid fa-lock mr-1"></i>
            Payments are securely processed by PayPal. We never store your card details.
        </div>

    </div>

    <script src="https://www.paypal.com/sdk/js?client-id=<?= urlencode(PAYPAL_CLIENT_ID) ?>&currency=<?= urlencode($currency) ?>&intent=capture"></script>
    <script>
        paypal.Buttons({
            createOrder: function () {
                return "<?= $orderId ?>";
            },
            onApprove: async function (data) {
                const response = await fetch("/php/api/paypal/capture-order.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ orderID: data.orderID })
                });
                const result = await response.json();
                if (result.error) {
                    console.error("Capture error:", result.error);
                    return;
                }
                window.location = result.redirect;
            },
            onError: function (err) {
                console.error("PayPal order error:", err);
            }
        }).render('#checkout');
    </script>

</body>

</html>
