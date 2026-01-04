<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$carparkId = $_GET['id'] ?? null;

if (!$carparkId || !ctype_digit($carparkId)) {
    header("Location: /");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById((int)$carparkId);

// Get all bookings for this car park
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$bookings = $ReadBookings->getBookingsByCarparkId((int)$carparkId);

if (!$carpark) {
    header("Location: /");
    exit;
}

// Owner-only access
if ($_SESSION['user_id'] != $carpark['carpark_owner']) {
    header("Location: /");
    exit;
}

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

<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-10">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <?= htmlspecialchars($carpark['carpark_name']) ?>
        </h1>

        <p class="text-gray-500 mt-1">
            Car Park ID: <?= htmlspecialchars($carpark['carpark_id']) ?>
        </p>
    </div>

    <!-- Description -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Description</h2>
        <p class="text-gray-700 leading-relaxed">
            <?= nl2br(htmlspecialchars($carpark['carpark_description'] ?: 'No description provided.')) ?>
        </p>
    </div>

    <!-- Core Info Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

        <div>
            <p class="text-sm text-gray-500">Address</p>
            <p class="font-medium text-gray-800">
                <?= htmlspecialchars($carpark['carpark_address']) ?>
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Capacity</p>
            <p class="font-medium text-gray-800">
                <?= htmlspecialchars($carpark['carpark_capacity']) ?> spaces
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Price</p>
            <p class="font-medium text-gray-800">
                £<?= number_format($carpark['carpark_price'], 2) ?>
            </p>
        </div>

        <div>
            <p class="text-sm text-gray-500">Type</p>
            <p class="font-medium text-gray-800">
                <?= htmlspecialchars(ucfirst($carpark['carpark_type'])) ?>
            </p>
        </div>

    </div>

    <!-- Location -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Location</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-500">Latitude</p>
                <p class="font-medium text-gray-800">
                    <?= htmlspecialchars($carpark['carpark_lat']) ?>
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Longitude</p>
                <p class="font-medium text-gray-800">
                    <?= htmlspecialchars($carpark['carpark_lng']) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-3">Features</h2>
        <?php if (empty($carpark['carpark_features'])): ?>
            <p class="text-gray-600">No features listed.</p>
        <?php else: ?>
            <?=  htmlspecialchars($carpark['carpark_features']) ?>
        <?php endif; ?>
    </div>

    <!-- Affiliate -->
    <?php if (!empty($carpark['carpark_affiliate_url'])): ?>
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Affiliate Link</h2>
            <a
                href="<?= htmlspecialchars($carpark['carpark_affiliate_url']) ?>"
                target="_blank"
                rel="noopener"
                class="text-green-600 hover:underline break-all"
            >
                <?= htmlspecialchars($carpark['carpark_affiliate_url']) ?>
            </a>
        </div>
    <?php endif; ?>

    <!-- Marker Colour -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-700 mb-2">Map Marker</h2>
        <div class="flex items-center gap-3">
            <div
                class="w-6 h-6 rounded-full border"
                style="background-color: <?= htmlspecialchars($carpark['marker_colour']) ?>"
            ></div>
            <span class="text-gray-700">
                <?= htmlspecialchars($carpark['marker_colour']) ?>
            </span>
        </div>
    </div>

    <!-- Owner Metadata -->
    <div class="border-t pt-6 text-sm text-gray-500">
        <p>Owner User ID: <?= htmlspecialchars($carpark['carpark_owner']) ?></p>
    </div>

</div>

<!-- Bookings for this car park go here -->
<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Bookings for this Car Park
    </h2>

    <?php if (empty($bookings)): ?>
        <p class="text-gray-600">
            There are no bookings for this car park yet.
        </p>
    <?php else: ?>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm text-gray-600">
                        <th class="p-3 border-b">Booking ID</th>
                        <th class="p-3 border-b">User ID</th>
                        <th class="p-3 border-b">Name</th>
                        <th class="p-3 border-b">Start</th>
                        <th class="p-3 border-b">End</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 border-b font-medium text-gray-800">
                                <?= htmlspecialchars($booking['booking_id']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= htmlspecialchars($booking['booking_user_id']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= htmlspecialchars($booking['booking_name']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= date(
                                    'd M Y, H:i',
                                    strtotime($booking['booking_start'])
                                ) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= date(
                                    'd M Y, H:i',
                                    strtotime($booking['booking_end'])
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

</body>
</html>