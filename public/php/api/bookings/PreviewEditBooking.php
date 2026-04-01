<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingID  = $_POST['booking_id'] ?? null;
$newStart   = $_POST['start_time'] ?? null;
$newEnd     = $_POST['end_time'] ?? null;

if (!$bookingID || !$newStart || !$newEnd || !ctype_digit($bookingID)) {
    header("Location: /account.php?error=" . urlencode("Invalid request"));
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

$ReadBookings = new ReadBookings();
$booking = $ReadBookings->getBookingByBookingId((int)$bookingID);

if (!$booking) {
    header("Location: /account.php?error=" . urlencode("Booking not found"));
    exit;
}

// Permission check
if (
    $_SESSION['user_id'] != $booking['booking_user_id'] &&
    $_SESSION['user_id'] != $booking['carpark_owner']
) {
    header("Location: /account.php?error=" . urlencode("Unauthorized"));
    exit;
}

// Parse dates
try {
    $oldStart = new DateTime($booking['booking_start']);
    $oldEnd   = new DateTime($booking['booking_end']);
    $newStartDT = new DateTime($newStart);
    $newEndDT   = new DateTime($newEnd);
} catch (Exception $e) {
    header("Location: /booking/edit-booking.php?id=$bookingID&error=" . urlencode("Invalid date format"));
    exit;
}

// Logical checks
if ($newEndDT <= $newStartDT) {
    header("Location: /edit-booking.php?id=$bookingID&error=" . urlencode("End time must be after start time"));
    exit;
}

$now = new DateTime();
if ($newStartDT < $now) {
    header("Location: /edit-booking.php?id=$bookingID&error=" . urlencode("Booking cannot start in the past"));
    exit;
}

// Calculate durations (minutes)
$oldMinutes = ($oldStart->diff($oldEnd)->days * 1440)
    + ($oldStart->diff($oldEnd)->h * 60)
    + $oldStart->diff($oldEnd)->i;

$newMinutes = ($newStartDT->diff($newEndDT)->days * 1440)
    + ($newStartDT->diff($newEndDT)->h * 60)
    + $newStartDT->diff($newEndDT)->i;

// Fetch carpark for availability rules
$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById((int)$booking['booking_carpark_id']);

if (!$carpark) {
    header("Location: /account.php?error=" . urlencode("Car park not found"));
    exit;
}

// Weekend availability check
if (empty($carpark['weekend_available'])) {
    $dayOfWeek = (int) $newStartDT->format('N'); // 6 = Sat, 7 = Sun
    if ($dayOfWeek >= 6) {
        header("Location: /edit-booking.php?id=$bookingID&error=" . urlencode("This car park is not available on weekends."));
        exit;
    }
}

// Minimum booking duration check
$minMinutes = (int) ($carpark['min_booking_minutes'] ?? 0);
if ($minMinutes > 0 && $newMinutes < $minMinutes) {
    header("Location: /edit-booking.php?id=$bookingID&error=" . urlencode("The minimum booking duration for this car park is {$minMinutes} minutes."));
    exit;
}

// Capacity / overlap check — exclude the booking being edited
$capacity    = (int)($carpark['carpark_capacity'] ?? 1);
$overlapping = $ReadBookings->countOverlappingBookingsExcludingBooking(
    (int)$booking['booking_carpark_id'],
    $newStartDT->format('Y-m-d H:i:s'),
    $newEndDT->format('Y-m-d H:i:s'),
    (int)$bookingID
);
if ($overlapping >= $capacity) {
    header("Location: /edit-booking.php?id=$bookingID&error=" . urlencode("This car park is fully booked for the selected time slot."));
    exit;
}

// Price calculation
$rateReader = new ReadRates();

$oldPrice = $rateReader->calculateOptimalPrice(
    (int)$booking['booking_carpark_id'],
    $oldMinutes
);

$newPrice = $rateReader->calculateOptimalPrice(
    (int)$booking['booking_carpark_id'],
    $newMinutes
);

// Safety net
$oldPrice = max($oldPrice, 0);
$newPrice = max($newPrice, 0);

$difference = $newPrice - $oldPrice;

// Store preview in session (authoritative)
$_SESSION['booking_edit_preview'] = [
    'booking_id'      => (int)$bookingID,
    'carpark_id'      => (int)$booking['booking_carpark_id'],
    'old_start'       => $booking['booking_start'],
    'old_end'         => $booking['booking_end'],
    'new_start'       => $newStartDT->format('Y-m-d H:i:s'),
    'new_end'         => $newEndDT->format('Y-m-d H:i:s'),
    'old_price'       => $oldPrice,
    'new_price'       => $newPrice,
    'difference'      => $difference,
    'preview_created' => time()
];

// Redirect to confirmation screen
header("Location: /confirm-edit.php?id=" . $bookingID);
exit;
