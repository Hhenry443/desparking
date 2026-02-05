<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingID = $_GET['id'] ?? null;
$isAdminOverride = isset($_GET['admin']) && $_GET['admin'] == '1' && $_SESSION['is_admin'] === true;

if (!$bookingID || !ctype_digit($bookingID)) {
    header("Location: /");
    exit;
}

// Get booking details
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$booking = $ReadBookings->getBookingByBookingId((int)$bookingID);

if (!$booking) {
    header("Location: /account.php");
    exit;
}

// Owner-only access (or admin override)
if (!$isAdminOverride && $_SESSION['user_id'] != $booking['booking_user_id'] && $_SESSION['user_id'] != $booking['carpark_owner']) {
    header("Location: /account.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Booking Details · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/output.css" rel="stylesheet">


    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <section class="py-16">
        <div class="max-w-6xl mx-auto px-6">

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-[#060745]">Booking Management</h1>
                <p class="text-gray-600 mt-1">View and manage this booking.</p>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 grid grid-cols-1 md:grid-cols-2 gap-12">

                <!-- Left: Booking Info -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Booking Details</h2>

                    <div class="space-y-3 text-sm text-gray-700">
                        <p><span class="font-semibold text-gray-900">Booking ID:</span> #<?= htmlspecialchars($booking['booking_id']) ?></p>
                        <p><span class="font-semibold text-gray-900">Booking Name:</span> <?= htmlspecialchars($booking['booking_name']) ?></p>
                        <p><span class="font-semibold text-gray-900">Car Park Address:</span> <?= htmlspecialchars($booking['carpark_address']) ?></p>
                        <p><span class="font-semibold text-gray-900">Start Time:</span> <?= htmlspecialchars($booking['booking_start']) ?></p>
                        <p><span class="font-semibold text-gray-900">End Time:</span> <?= htmlspecialchars($booking['booking_end']) ?></p>
                        <p><span class="font-semibold text-gray-900">User ID:</span> <?= htmlspecialchars($booking['booking_user_id']) ?></p>
                    </div>
                </div>

                <!-- Right: Status + Actions -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Booking Actions</h2>

                    <!-- Status -->
                    <div class="mb-6">
                        <?php
                        $now = new DateTime();
                        $bookingEnd = new DateTime($booking['booking_end']);
                        $isExpired = $bookingEnd < $now;

                        $status = $isExpired ? 'expired' : $booking['booking_status'] ?? 'active';

                        $statusClasses = match ($status) {
                            'active' => 'bg-green-100 text-green-700',
                            'expired' => 'bg-gray-200 text-gray-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default => 'bg-yellow-100 text-yellow-700'
                        };
                        ?>
                        <span class="inline-block px-4 py-1 rounded-full text-sm font-semibold <?= $statusClasses ?>">
                            <?= ucfirst($status) ?>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-4">
                        <a href="/account.php"
                            class="px-6 py-2 rounded-xl bg-gray-200 text-gray-800 font-semibold">
                            Back
                        </a>

                        <?php if ($status === 'active'): ?>
                            <form method="POST" action="/php/api/bookings/CancelBooking.php">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <button type="submit"
                                    class="px-6 py-2 rounded-xl bg-red-100 text-red-700 font-semibold hover:bg-red-200">
                                    Cancel Booking
                                </button>
                            </form>
                        <?php endif; ?>

                        <a href="/edit-booking.php?id=<?= $booking['booking_id'] ?>"
                            class="px-6 py-2 rounded-xl bg-[#6ae6fc] text-gray-900 font-semibold hover:bg-cyan-400">
                            Edit Booking
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </section>

</body>

</html>