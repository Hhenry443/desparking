<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingID = $_GET['id'] ?? null;
$isAdminOverride = isset($_GET['admin']) && $_GET['admin'] == '1' && $_SESSION['is_admin'] === true;

if (!$bookingID || !ctype_digit($bookingID)) {
    header("Location: /account.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$booking = $ReadBookings->getBookingByBookingId((int)$bookingID);

if (!$booking) {
    header("Location: /account.php");
    exit;
}

// Permission check
if (
    !$isAdminOverride &&
    $_SESSION['user_id'] != $booking['booking_user_id'] &&
    $_SESSION['user_id'] != $booking['carpark_owner']
) {
    header("Location: /account.php");
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Booking · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-[#ebebeb] pt-24">
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 mb-12">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Booking</h1>
        <p class="text-gray-500 text-sm mt-1">
            Update the booking times. Any price difference will be calculated before confirmation.
        </p>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
            <?= htmlspecialchars(urldecode($_GET['error'])) ?>
        </div>
    <?php endif; ?>

    <form action="/php/api/bookings/PreviewEditBooking.php" method="POST" class="space-y-6">

        <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">

        <!-- Booking name (readonly) -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Booking Name</label>
            <input type="text" value="<?= htmlspecialchars($booking['booking_name']) ?>" disabled
                class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-500 text-sm border border-gray-200">
        </div>

        <!-- Start time -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Start Time *</label>
            <input type="datetime-local" name="start_time" required
                value="<?= date('Y-m-d\TH:i', strtotime($booking['booking_start'])) ?>"
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
        </div>

        <!-- End time -->
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">End Time *</label>
            <input type="datetime-local" name="end_time" required
                value="<?= date('Y-m-d\TH:i', strtotime($booking['booking_end'])) ?>"
                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
        </div>

        <hr class="my-6">

        <!-- Submit -->
        <div class="flex gap-4">
            <button type="submit"
                class="flex-1 py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                       hover:bg-cyan-400 transition shadow-md">
                Review Changes
            </button>

            <a href="/booking.php?id=<?= $booking['booking_id'] ?>"
                class="flex-1 py-3 rounded-lg bg-gray-200 text-gray-700 text-sm font-semibold
                       hover:bg-gray-300 transition text-center shadow-sm">
                Cancel
            </a>
        </div>

    </form>
</div>
</body>
</html>
