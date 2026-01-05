<?php
session_start();

// If not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Get user's bookings
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$bookings = $ReadBookings->getBookingsByUserId($userId);

// Get users car parks
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getCarparksByUserId($userId);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Account · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 pt-20">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-xl mx-auto bg-gray-200 rounded-2xl shadow-lg p-8 mt-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">
        Account
    </h1>

    <!-- User Info Section -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-2">User Information</h2>
        <p class="text-gray-600">User ID: <?= htmlspecialchars($userId) ?></p>
    </div>

    <!-- Bookings Section -->
    <div>
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Bookings</h2>

        <?php if (empty($bookings)): ?>
            <p class="text-gray-600">You have no bookings yet.</p>
        <?php else: ?>
            <ul class="space-y-4">
                <?php foreach ($bookings as $booking): ?>
                    <li class="p-4 bg-gray-50 rounded-lg border">
                        <p class="font-medium text-gray-800">
                            Booking ID: <?= htmlspecialchars($booking['booking_id']) ?>
                        </p>

                        <p class="text-gray-700 font-semibold">
                            <?= htmlspecialchars($booking['carpark_name']) ?>
                        </p>

                        <p class="text-gray-500 text-sm">
                            <?= htmlspecialchars($booking['carpark_address']) ?>
                        </p>

                        <p class="text-gray-600 mt-2">
                            Name: <?= htmlspecialchars($booking['booking_name']) ?>
                        </p>

                        <p class="text-gray-600">
                            Start: <?= htmlspecialchars($booking['booking_start']) ?>
                        </p>

                        <p class="text-gray-600">
                            End: <?= htmlspecialchars($booking['booking_end']) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Car Parks Section -->
    <div class="mt-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Your Car Parks</h2>
        <?php if (empty($carparks)): ?>
            <p class="text-gray-600">You have no car parks yet.</p>
        <?php else: ?>
            <ul class="space-y-4">
                <?php foreach ($carparks as $carpark): ?>
                    <li>
                        <a
                            href="/carpark.php?id=<?= urlencode($carpark['carpark_id']) ?>"
                            class="block p-4 bg-gray-50 rounded-lg border hover:bg-gray-100 hover:border-green-500 transition"
                        >
                            <p class="font-medium text-gray-800">
                                Car Park ID: <?= htmlspecialchars($carpark['carpark_id']) ?>
                            </p>

                            <p class="text-gray-700 font-semibold">
                                <?= htmlspecialchars($carpark['carpark_name']) ?>
                            </p>

                            <p class="text-gray-500 text-sm">
                                <?= htmlspecialchars($carpark['carpark_address']) ?>
                            </p>

                            <p class="text-gray-600 mt-2">
                                Capacity: <?= htmlspecialchars($carpark['carpark_capacity']) ?>
                            </p>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>


</body>
</html>
