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
<body class="min-h-screen bg-[#ebebeb] pt-24">

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-6xl mx-auto px-6 mb-16">

    <!-- Header -->
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-gray-900">Your Account</h1>
        <p class="text-gray-500 text-sm mt-1">
            Manage your bookings and car parks from one place
        </p>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Sidebar / User Info -->
        <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Profile</h2>

            <div class="space-y-2 text-sm text-gray-600">
                <p><span class="font-semibold text-gray-800">User ID:</span> <?= htmlspecialchars($userId) ?></p>
            </div>

            <div class="mt-6">
                <a href="/create.php"
                   class="block text-center py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                          hover:bg-cyan-400 transition shadow-md">
                    Create a Car Park
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="lg:col-span-2 space-y-10">

            <!-- Bookings -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Your Bookings</h2>

                <?php if (empty($bookings)): ?>
                    <div class="bg-gray-50 rounded-xl p-6 text-gray-500 text-sm">
                        You have no bookings yet.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($bookings as $booking): ?>

                        <?php
                            $now = new DateTime();
                            $bookingEnd = new DateTime($booking['booking_end']);
                            $isExpired = $bookingEnd < $now;
                        ?>

                        <a href="/booking.php?id=<?= urlencode($booking['booking_id']) ?>" class="bg-gray-100 rounded-2xl p-5 
                                    shadow-[0_6px_20px_rgba(0,0,0,0.12)]
                                    border border-gray-100
                                    hover:shadow-[0_10px_28px_rgba(0,0,0,0.18)]
                                    hover:-translate-y-1 transition-all duration-200
                                    <?= $isExpired ? 'opacity-60 grayscale' : '' ?>">

                            <!-- Accent strip -->
                            <div class="w-full h-1 rounded-full 
                                <?= $isExpired ? 'bg-red-400' : 'bg-[#6ae6fc]' ?> 
                                mb-3"></div>

                            <!-- Status -->
                            <?php if ($isExpired): ?>
                                <span class="inline-block mb-2 px-3 py-1 text-xs font-bold 
                                            bg-red-100 text-red-600 rounded-full">
                                    Expired
                                </span>
                            <?php else: ?>
                                <span class="inline-block mb-2 px-3 py-1 text-xs font-bold 
                                            bg-green-100 text-green-600 rounded-full">
                                    Active
                                </span>
                            <?php endif; ?>

                            <p class="text-sm font-bold text-gray-800">
                                <?= htmlspecialchars($booking['carpark_name']) ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($booking['carpark_address']) ?>
                            </p>

                            <div class="mt-3 text-sm text-gray-600 space-y-1">
                                <p><span class="font-semibold">Start:</span> <?= htmlspecialchars($booking['booking_start']) ?></p>
                                <p><span class="font-semibold">End:</span> <?= htmlspecialchars($booking['booking_end']) ?></p>
                            </div>

                            <p class="mt-2 text-xs text-gray-400">
                                Booking ID: <?= htmlspecialchars($booking['booking_id']) ?>
                            </p>

                        </a>

                        <?php endforeach; ?>

                    </div>
                <?php endif; ?>
            </div>

            <!-- Car Parks -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 mb-4">Your Car Parks</h2>

                <?php if (empty($carparks)): ?>
                    <div class="bg-gray-50 rounded-xl p-6 text-gray-500 text-sm">
                        You have no car parks yet.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($carparks as $carpark): ?>
                            <a
                            href="/carpark.php?id=<?= urlencode($carpark['carpark_id']) ?>"
                            class="block bg-white rounded-2xl p-5
                                shadow-[0_6px_20px_rgba(0,0,0,0.12)]
                                border border-gray-100
                                hover:shadow-[0_10px_28px_rgba(0,0,0,0.18)]
                                hover:-translate-y-1 transition-all duration-200">

                            <!-- Accent strip -->
                            <div class="w-full h-1 rounded-full bg-[#6ae6fc] mb-3"></div>

                            <p class="text-sm font-bold text-gray-800">
                                <?= htmlspecialchars($carpark['carpark_name']) ?>
                            </p>

                            <p class="text-xs text-gray-500">
                                <?= htmlspecialchars($carpark['carpark_address']) ?>
                            </p>

                            <p class="mt-2 text-sm text-gray-600">
                                Capacity: <?= htmlspecialchars($carpark['carpark_capacity']) ?>
                            </p>

                            <p class="mt-2 text-xs text-gray-400">
                                Car Park ID: <?= htmlspecialchars($carpark['carpark_id']) ?>
                            </p>
                        </a>

                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>

</html>
