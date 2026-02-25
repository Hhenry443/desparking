<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

$carparkID = $_GET['carpark_id'] ?? null;

if (!$carparkID) {
    die("Invalid car park.");
}

$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById($carparkID);

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';

$ReadRates = new ReadRates();
$rates = $ReadRates->getCarparkRates((int)$carparkID);

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/ReadVehicles.php';

$ReadVehicles = new ReadVehicles();
$vehicles = $ReadVehicles->getVehiclesByUserId((int)$_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Book a Space – <?= htmlspecialchars($carpark['carpark_name']) ?></title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <link href="./css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

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

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                <p class="font-bold">Booking Error</p>
                <p class="text-sm"><?= htmlspecialchars(urldecode($_GET['error'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="/index.php" class="text-blue-600 hover:underline text-sm mb-3 inline-block">
            ← Back to map
        </a>

        <!-- TITLE -->
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">
            <?= htmlspecialchars($carpark["carpark_name"]) ?>
        </h1>

        <!-- DESCRIPTION -->
        <p class="text-gray-600 mb-4">
            <?= htmlspecialchars($carpark["carpark_description"]) ?>
        </p>

        <!-- STATS GRID -->
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">Capacity</p>
            <p class="font-semibold text-gray-800">
                <?= htmlspecialchars($carpark["carpark_capacity"]) ?>
            </p>
        </div>

        <div class="my-4 space-y-2 ">
                <p class="font-medium text-gray-700">Parking rates</p>

                <ul class="divide-y rounded-lg border bg-white">
                    <?php foreach ($rates as $rate): ?>
                        <li class="flex justify-between px-4 py-3 text-sm">
                            <span class="text-gray-600">
                                <?= $rate['duration_minutes'] ?> minute(s)
                            </span>
                            <span class="font-medium text-gray-900">
                                £<?= number_format($rate['price'] / 100, 2) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        <!-- FEATURES -->
        <?php
        $featuresArray = explode(',', $carpark["carpark_features"]);
        ?>

        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ($featuresArray as $feature): ?>
                <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full border">
                    <?= htmlspecialchars($feature) ?>
                </span>
            <?php endforeach; ?>
        </div>

        <!-- BOOKING FORM -->
        <h2 class="text-xl font-semibold text-gray-800 mb-3">Book Your Space</h2>

        <form
            method="POST"
            action="/checkout.php"
            class="space-y-5"
            id="booking-form">

            <input type="hidden" name="booking_carpark_id" value="<?= $carparkID ?>">
            
            <!-- NAME -->
            <div>
                <label class="block text-sm font-medium mb-1">Your Name</label>
                <input
                    type="text"
                    name="booking_name"
                    required
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
                    placeholder="John Smith">
            </div>

            <!-- EMAIL -->
            <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input
                    type="email"
                    name="booking_email"
                    required
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
                    placeholder="you@example.com">
            </div>
            
            <!-- VEHICLE -->
            <div>
                <label class="block text-sm font-medium mb-1">Select Vehicle</label>

                <?php if (empty($vehicles)): ?>
                    <div class="p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-200">
                        You must add a vehicle before booking.
                        <a href="/account.php" class="underline ml-1">Add vehicle</a>
                    </div>
                <?php else: ?>
                    <select
                        name="booking_vehicle_id"
                        required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">

                        <option value="">Select your vehicle</option>

                        <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?= $vehicle['vehicle_id'] ?>">
                                <?= htmlspecialchars($vehicle['registration_plate']) ?>
                                —
                                <?= htmlspecialchars($vehicle['make']) ?>
                                <?= htmlspecialchars($vehicle['model']) ?>
                                (<?= htmlspecialchars($vehicle['colour']) ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Booking Date</label>
                <input
                    type="date"
                    name="booking_date"
                    required
                    value="<?= date('Y-m-d') ?>"
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">
            </div>

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">Start Time</label>
                    <input
                        type="time"
                        name="booking_start_time"
                        required
                        value="<?= date('H:i') ?>"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">End Time</label>
                    <input
                        type="time"
                        name="booking_end_time" 
                        required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">
                </div>
            </div>

            <!-- SUBMIT BUTTON -->
            <button
                type="submit"
                <?= empty($vehicles) ? 'disabled class="w-full bg-gray-400 text-white font-medium py-3 rounded-lg cursor-not-allowed"' :
                    'class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition cursor-pointer"' ?>>
                Proceed to Payment
            </button>
        </form>

    </div>

    <br><br>
</body>

</html>