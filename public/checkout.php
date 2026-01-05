<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get booking data from POST
$carparkID = $_POST['booking_carpark_id'] ?? null;
$bookingName = $_POST['booking_name'] ?? null;
$bookingEmail = $_POST['booking_email'] ?? null;
$bookingDate = $_POST['booking_date'] ?? null;
$startTime = $_POST['booking_start_time'] ?? null;
$endTime = $_POST['booking_end_time'] ?? null;
$userID = $_POST['booking_user_id'] ?? null;

// Validate all required fields
if (!$carparkID || !$bookingName || !$bookingEmail || !$bookingDate || !$startTime || !$endTime || !$userID) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Missing required fields"));
    exit();
}

// Build full datetime strings
$bookingStart = $bookingDate . " " . $startTime . ":00";
$bookingEnd = $bookingDate . " " . $endTime . ":00";

// Basic validation
if ($bookingStart >= $bookingEnd) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("End time must be after start time"));
    exit();
}

// Store booking data in session for later use after payment
$_SESSION['pending_booking'] = [
    'carpark_id' => $carparkID,
    'name' => $bookingName,
    'email' => $bookingEmail,
    'start' => $bookingStart,
    'end' => $bookingEnd,
    'user_id' => $userID
];

$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById($carparkID);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Payment – <?= htmlspecialchars($carpark['carpark_name']) ?></title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <script src="https://js.stripe.com/v3/"></script>

    <link href="./css/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- IMAGE HEADER -->
    <div class="w-full h-56 md:h-72 lg:h-80 overflow-hidden">
        <img
            src=" /images/default-carpark-image.png"
            class="w-full h-full object-cover"
            alt="Car Park Image">
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-xl p-6 mt-6 border border-gray-200">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Complete Your Payment</h1>
        
        <div id="checkout">
            <!-- Stripe Checkout will insert the payment form here -->
        </div>
    </div>
    
    <br><br>

    <script>
        // Initialize Stripe.js
        const stripe = Stripe("pk_test_wGQVF7QeuldBJrMPt10D2esF");

        initialize();

        // Fetch Checkout Session and retrieve the client secret
        async function initialize() {
            const fetchClientSecret = async () => {
                try {
                    console.log("Sending request with:", {
                        carpark_id: "<?= $carparkID ?>",
                        start_time: "<?= $bookingStart ?>",
                        end_time: "<?= $bookingEnd ?>"
                    });

                    const response = await fetch(
                        "/php/api/stripe/create-checkout-session.php",
                        {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                carpark_id: "<?= $carparkID ?>",
                                start_time: "<?= $bookingStart ?>",
                                end_time: "<?= $bookingEnd ?>"
                            })
                        }
                    );

                    console.log("Response status:", response.status);
                    
                    const text = await response.text();
                    console.log("Raw response:", text);

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error("Failed to parse JSON:", e);
                        throw new Error("Server returned invalid JSON: " + text.substring(0, 200));
                    }

                    // DEBUG: Look at your console!
                    console.log("Parsed response object:", data);
                    console.log("Client Secret value:", data.clientSecret);

                    if (data.error) {
                        throw new Error("Stripe API Error: " + data.error);
                    }

                    if (!data.clientSecret || typeof data.clientSecret !== "string") {
                        console.error("ERROR: clientSecret is missing or not a string!");
                        throw new Error("No client secret received from server");
                    }

                    return data.clientSecret;
                } catch (error) {
                    console.error("Error in fetchClientSecret:", error);
                    document.getElementById('checkout').innerHTML = 
                        '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">' +
                        '<p class="font-bold">Payment Error</p>' +
                        '<p class="text-sm">' + error.message + '</p>' +
                        '<p class="text-sm mt-2">Check the browser console (F12) for more details.</p>' +
                        '</div>';
                    throw error;
                }
            };

            try {
                const checkout = await stripe.initEmbeddedCheckout({
                    fetchClientSecret,
                });

                checkout.mount("#checkout");
            } catch (error) {
                console.error("Failed to initialize checkout:", error);
            }
        }
    </script>
</body>

</html>