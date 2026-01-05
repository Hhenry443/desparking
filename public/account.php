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

    <nav class="w-full h-16 bg-white/80 backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
            <a href="/" class="text-xl font-semibold text-gray-800">DesParking</a>
        </div>

        <!-- Nav Links -->
        <div class="hidden md:flex space-x-6 text-gray-700 font-medium">

            <!-- Create a new car park -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/create.php" class="hover:text-green-600 transition">Create Car Park</a>
            <?php endif; ?>


            <!-- If user is admin, show admin link -->
            <?php if (isset($_SESSION['user_id']) && $_SESSION['is_admin'] === true): ?>
                <a href="/admin.php" class="hover:text-green-600 transition">Admin</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/logout.php" class="hover:text-red-600 transition">
                    Logout
                </a>
            <?php else: ?>
                <a href="/login.php" class="hover:text-green-600 transition">
                    Login
                </a>
            <?php endif; ?>
        </div>

        <!-- Mobile Menu Icon -->
        <button class="md:hidden p-2 rounded hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>

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
