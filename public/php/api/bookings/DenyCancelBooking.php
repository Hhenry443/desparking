<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$reviewerID = (int) $_SESSION['user_id'];
$bookingID  = (int) ($_POST['booking_id'] ?? 0);
$isAdmin    = $_SESSION['is_admin'] === true;

if (!$bookingID) {
    header("Location: /account.php?error=" . urlencode("Invalid booking"));
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';
$conn = Dbh::getConnection();

$stmt = $conn->prepare("
    SELECT b.*, c.carpark_owner
    FROM bookings b
    INNER JOIN carparks c ON b.booking_carpark_id = c.carpark_id
    WHERE b.booking_id = :id
    LIMIT 1
");
$stmt->execute([':id' => $bookingID]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header("Location: /account.php?error=" . urlencode("Booking not found"));
    exit;
}

if (!$isAdmin && (int) $booking['carpark_owner'] !== $reviewerID) {
    header("Location: /account.php?error=" . urlencode("Unauthorized"));
    exit;
}

if (($booking['booking_status'] ?? '') !== 'cancel_pending') {
    header("Location: /booking.php?id={$bookingID}&error=" . urlencode("No pending cancellation request for this booking"));
    exit;
}

$stmt = $conn->prepare("
    UPDATE bookings
    SET booking_status = NULL,
        cancellation_requested_at = NULL
    WHERE booking_id = :id
");
$stmt->execute([':id' => $bookingID]);

try {
    (new Notifier($conn))->cancellationDenied($bookingID);
} catch (Throwable $e) {
    error_log("Notification failed [cancellationDenied]: " . $e->getMessage());
}

header("Location: /booking.php?id={$bookingID}&success=" . urlencode("Cancellation request denied. The booking remains active."));
exit;
