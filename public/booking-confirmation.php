<?php

$title = 'Booking Confirmed';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Optional: pull booking ID from query string
$bookingID = $_GET['booking_id'] ?? null;

// Guests have no account to send them to, so show the access link that also
// went out in their confirmation email. Only for the booking this session just
// paid for — booking ids are sequential, and the token must not be reachable by
// guessing one.
$guestLink = null;

if (
    $bookingID
    && ctype_digit((string) $bookingID)
    && !isset($_SESSION['user_id'])
    && (int) ($_SESSION['completed_booking_id'] ?? 0) === (int) $bookingID
) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
    $confirmedBooking = (new ReadBookings())->getBookingByBookingId((int) $bookingID);

    if ($confirmedBooking && !empty($confirmedBooking['booking_access_token'])) {
        $guestLink = '/booking.php?id=' . (int) $bookingID
            . '&t=' . urlencode($confirmedBooking['booking_access_token']);
    }
}
?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#ebebeb] min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center">

        <!-- Tick -->
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center">
                <svg xmlns'svg' viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    class="w-8 h-8 text-green-600" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-semibold text-gray-800 mb-2">
            Booking Confirmed
        </h1>

        <p class="text-gray-600 mb-6">
            Your parking space has been successfully reserved.
        </p>

        <?php if ($bookingID): ?>
            <p class="text-sm text-gray-500 mb-6">
                Booking reference:<br>
                <span class="font-mono text-gray-800 font-semibold">
                    #<?= htmlspecialchars($bookingID) ?>
                </span>
            </p>
        <?php endif; ?>

        <?php if ($guestLink): ?>
            <p class="text-sm text-gray-600 mb-6">
                We've emailed your confirmation. Keep it — the link inside is how you get
                back to this booking, and it will save the booking to your account if you sign up.
            </p>
        <?php endif; ?>

        <div class="space-y-3">
            <a
                href="/index.php"
                class="block w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 rounded-lg transition">
                Back to Map
            </a>

            <?php if ($guestLink): ?>
                <a
                    href="<?= htmlspecialchars($guestLink) ?>"
                    class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 rounded-lg transition">
                    View My Booking
                </a>
            <?php else: ?>
                <a
                    href="/account.php"
                    class="block w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 rounded-lg transition">
                    View My Bookings
                </a>
            <?php endif; ?>
        </div>

    </div>

</body>

</html>