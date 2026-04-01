<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get booking data from POST
$isGuest  = !isset($_SESSION['user_id']);
$userID   = $isGuest ? null : (int) $_SESSION['user_id'];

$carparkID           = $_POST['booking_carpark_id'] ?? null;
$bookingName         = $_POST['booking_name'] ?? null;
$bookingEmail        = $_POST['booking_email'] ?? null;
$vehicleID           = $isGuest ? null : ($_POST['booking_vehicle_id'] ?? null);
$bookingRegistration = $isGuest ? (trim($_POST['booking_registration'] ?? '')) : null;
$isMonthly           = ($_POST['booking_is_monthly'] ?? '0') === '1';

// Validate common required fields
if (!$carparkID || !$bookingName || !$bookingEmail) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Missing required fields"));
    exit();
}

if ($isGuest && !$bookingRegistration) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Please enter your vehicle registration"));
    exit();
}

if (!$isGuest && !$vehicleID) {
    header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Please select a vehicle"));
    exit();
}

if ($isMonthly) {
    $startDate    = $_POST['booking_start_date'] ?? date('Y-m-d');
    $bookingStart = $startDate . " 00:00:00";
    $bookingEnd   = date('Y-m-d H:i:s', strtotime('+1 month', strtotime($startDate)));
} else {
    $startDate = $_POST['booking_start_date'] ?? null;
    $endDate   = $_POST['booking_end_date'] ?? null;
    $startTime = $_POST['booking_start_time'] ?? null;
    $endTime   = $_POST['booking_end_time'] ?? null;

    if (!$startDate || !$endDate || !$startTime || !$endTime) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Missing required fields"));
        exit();
    }

    $bookingStart = $startDate . " " . $startTime . ":00";
    $bookingEnd   = $endDate   . " " . $endTime   . ":00";

    if ($bookingStart >= $bookingEnd) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("End time must be after start time"));
        exit();
    }
}

// For logged-in users, verify the vehicle belongs to them
if (!$isGuest) {
    $db = Dbh::getConnection();

    $stmt = $db->prepare("
        SELECT vehicle_id
        FROM vehicles
        WHERE vehicle_id = :vehicleID
        AND user_id = :userID
        LIMIT 1
    ");

    $stmt->execute([
        ':vehicleID' => $vehicleID,
        ':userID'    => $userID
    ]);

    if (!$stmt->fetch()) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Invalid vehicle selected"));
        exit();
    }
}

// Overlap / capacity check before showing payment form
if (!$isMonthly) {
    $bookingsModel  = new WriteBookings();
    $ReadCarparks   = new ReadCarparks();
    $carparkForCap  = $ReadCarparks->getCarparkById($carparkID);
    $capacity       = (int) ($carparkForCap['carpark_capacity'] ?? 1);
    $overlapping    = $bookingsModel->countOverlappingBookings((int) $carparkID, $bookingStart, $bookingEnd);

    if ($overlapping >= $capacity) {
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode("Sorry, this car park is fully booked for your selected time. Please choose a different slot."));
        exit();
    }
}

// Store booking data in session
$_SESSION['pending_booking'] = [
    'carpark_id'   => (int) $carparkID,
    'name'         => $bookingName,
    'email'        => $bookingEmail,
    'start'        => $bookingStart,
    'end'          => $bookingEnd,
    'vehicle_id'   => $vehicleID ? (int) $vehicleID : null,
    'user_id'      => $userID,
    'is_monthly'   => $isMonthly,
    'registration' => $bookingRegistration,
];

if (!isset($carparkForCap)) {
    $ReadCarparks = new ReadCarparks();
    $carpark = $ReadCarparks->getCarparkById($carparkID);
} else {
    $carpark = $carparkForCap;
}
$title = "Payment –" . htmlspecialchars($carpark['carpark_name']);

?>
<!DOCTYPE html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#ebebeb] min-h-screen">

    <!-- IMAGE HEADER -->
    <div class="w-full h-56 md:h-72 lg:h-80 overflow-hidden">
        <img
            src=" /images/default-carpark-image.png"
            class="w-full h-full object-cover"
            alt="Car Park Image">
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-xl p-6 mt-6 border border-gray-200">
        <!-- Back Button -->
        <a href="/map.php" class="text-blue-600 hover:underline text-sm mb-3 inline-block">
            ← Back to map
        </a>

        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Complete Your Payment</h1>

        <div id="checkout">
            <!-- Stripe Checkout will insert the payment form here -->
        </div>
    </div>

    <br><br>

    <script>
        // Initialize Stripe.js
        const stripe = Stripe("<?= STRIPE_PUBLIC_KEY ?>");

        initialize();

        async function initialize() {
            const isMonthly = <?= $isMonthly ? 'true' : 'false' ?>;
            const endpoint = isMonthly ?
                "/php/api/stripe/create-subscription-session.php" :
                "/php/api/stripe/create-checkout-session.php";

            const fetchClientSecret = async () => {
                try {
                    const payload = isMonthly ? {
                        carpark_id: "<?= $carparkID ?>"
                    } : {
                        carpark_id: "<?= $carparkID ?>",
                        start_time: "<?= $bookingStart ?>",
                        end_time: "<?= $bookingEnd ?>",
                        vehicle_id: "<?= $vehicleID ?>"
                    };

                    const response = await fetch(endpoint, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify(payload)
                    });

                    const text = await response.text();

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        throw new Error("Server returned invalid JSON: " + text.substring(0, 200));
                    }

                    if (data.error) {
                        throw new Error("Stripe API Error: " + data.error);
                    }

                    if (!data.clientSecret || typeof data.clientSecret !== "string") {
                        throw new Error("No client secret received from server");
                    }

                    return data.clientSecret;
                } catch (error) {
                    console.error("Error in fetchClientSecret:", error);
                    document.getElementById('checkout').innerHTML =
                        '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">' +
                        '<p class="font-bold">Payment Error</p>' +
                        '<p class="text-sm">' + error.message + '</p>' +
                        '</div>';
                    throw error;
                }
            };

            try {
                const checkout = await stripe.initEmbeddedCheckout({
                    fetchClientSecret
                });
                checkout.mount("#checkout");
            } catch (error) {
                console.error("Failed to initialize checkout:", error);
            }
        }
    </script>
</body>

</html>