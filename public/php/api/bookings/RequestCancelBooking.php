<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$userID    = (int) $_SESSION['user_id'];
$bookingID = (int) ($_POST['booking_id'] ?? 0);

if (!$bookingID) {
    header("Location: /account.php?error=" . urlencode("Invalid booking"));
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';
$conn = Dbh::getConnection();

$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = :id LIMIT 1");
$stmt->execute([':id' => $bookingID]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking || (int) $booking['booking_user_id'] !== $userID) {
    header("Location: /account.php?error=" . urlencode("Booking not found"));
    exit;
}

if (!empty($booking['is_monthly'])) {
    // Monthly subscriptions cancel directly — redirect to the existing handler
    header("Location: /booking.php?id={$bookingID}");
    exit;
}

$currentStatus = $booking['booking_status'] ?? '';

if ($currentStatus === 'cancelled') {
    header("Location: /account.php?error=" . urlencode("This booking is already cancelled"));
    exit;
}

if ($currentStatus === 'cancel_pending') {
    header("Location: /booking.php?id={$bookingID}");
    exit;
}

$stmt = $conn->prepare("
    UPDATE bookings
    SET booking_status = 'cancel_pending',
        cancellation_requested_at = NOW()
    WHERE booking_id = :id
");
$stmt->execute([':id' => $bookingID]);

try {
    (new Notifier($conn))->cancellationRequested($bookingID);
} catch (Throwable $e) {
    error_log("Notification failed [cancellationRequested]: " . $e->getMessage());
}

header("Location: /booking.php?id={$bookingID}&success=" . urlencode("Cancellation request submitted. The car park owner will review it shortly."));
exit;
