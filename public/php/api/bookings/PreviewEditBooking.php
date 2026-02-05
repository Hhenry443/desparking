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
    header("Location: /booking/edit.php?id=$bookingID&error=" . urlencode("Invalid date format"));
    exit;
}

// Logical checks
if ($newEndDT <= $newStartDT) {
    header("Location: /booking/edit.php?id=$bookingID&error=" . urlencode("End time must be after start time"));
    exit;
}

$now = new DateTime();
if ($newStartDT < $now) {
    header("Location: /booking/edit.php?id=$bookingID&error=" . urlencode("Booking cannot start in the past"));
    exit;
}
    
// Calculate durations (minutes)
$oldMinutes = ($oldStart->diff($oldEnd)->days * 1440)
            + ($oldStart->diff($oldEnd)->h * 60)
            + $oldStart->diff($oldEnd)->i;

$newMinutes = ($newStartDT->diff($newEndDT)->days * 1440)
            + ($newStartDT->diff($newEndDT)->h * 60)
            + $newStartDT->diff($newEndDT)->i;

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
