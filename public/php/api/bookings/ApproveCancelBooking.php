<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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

// Load PayPal client
$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
];
$autoloadPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}
if (!$autoloadPath) {
    header("Location: /account.php?error=" . urlencode("System configuration error"));
    exit;
}

require_once $autoloadPath;
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/paypal.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/Money.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/paypal/PayPalClient.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';

$conn = Dbh::getConnection();

try {
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

    // Only the carpark owner or an admin can approve
    if (!$isAdmin && (int) $booking['carpark_owner'] !== $reviewerID) {
        header("Location: /account.php?error=" . urlencode("Unauthorized"));
        exit;
    }

    if (($booking['booking_status'] ?? '') !== 'cancel_pending') {
        header("Location: /booking.php?id={$bookingID}&error=" . urlencode("No pending cancellation request for this booking"));
        exit;
    }

    // Fetch the original payment
    $stmt = $conn->prepare("
        SELECT * FROM payments
        WHERE booking_id = :booking_id
          AND type = 'initial'
          AND status = 'succeeded'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([':booking_id' => $bookingID]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    // Use cancellation_requested_at for refund calc — fair to the customer
    $requestedAt    = !empty($booking['cancellation_requested_at'])
        ? new DateTime($booking['cancellation_requested_at'])
        : new DateTime();
    $bookingStart   = new DateTime($booking['booking_start']);
    $bookingEnd     = new DateTime($booking['booking_end']);
    $originalAmount = $payment ? (int) $payment['amount'] : 0;

    $totalDays      = (int) ceil(
        ($bookingEnd->getTimestamp() - $bookingStart->getTimestamp()) / 86400
    );
    $isLongTerm     = $totalDays >= 30;
    $secsUntilStart = $bookingStart->getTimestamp() - $requestedAt->getTimestamp();

    if ($secsUntilStart >= (48 * 3600)) {
        $refundAmount = $originalAmount;
        $refundType   = 'full';
    } elseif ($isLongTerm) {
        $secsIntoSession = $requestedAt->getTimestamp() - $bookingStart->getTimestamp();
        if ($secsIntoSession <= (48 * 3600)) {
            $secsRemaining = max(0, $bookingEnd->getTimestamp() - $requestedAt->getTimestamp());
            $daysRemaining = (int) ceil($secsRemaining / 86400);
            $refundAmount  = ($originalAmount > 0 && $totalDays > 0)
                ? (int) round($originalAmount * $daysRemaining / $totalDays)
                : 0;
            $refundType = 'prorata';
        } else {
            $dailyRate    = $totalDays > 0 ? $originalAmount / $totalDays : 0;
            $refundAmount = max(0, (int) round($originalAmount - $dailyRate * 30));
            $refundType   = 'minus30';
        }
    } else {
        $refundAmount = 0;
        $refundType   = 'none';
    }

    $conn->beginTransaction();

    if ($payment && $refundAmount > 0) {
        PayPalClient::refundCapture(
            $payment['paypal_capture_id'],
            $refundAmount < $originalAmount ? $refundAmount : null,
            $payment['currency']
        );

        $stmt = $conn->prepare("
            INSERT INTO payments
                (booking_id, user_id, paypal_order_id, paypal_capture_id, paypal_payer_id,
                 amount, currency, type, status)
            VALUES
                (:booking_id, :user_id, :order_id, :capture_id, :payer_id,
                 :amount, :currency, 'refund', 'succeeded')
        ");
        $stmt->execute([
            ':booking_id'  => $bookingID,
            ':user_id'     => (int) $booking['booking_user_id'],
            ':order_id'    => $payment['paypal_order_id'],
            ':capture_id'  => $payment['paypal_capture_id'],
            ':payer_id'    => $payment['paypal_payer_id'],
            ':amount'      => $refundAmount,
            ':currency'    => $payment['currency'],
        ]);
    }

    $stmt = $conn->prepare("
        UPDATE bookings
        SET booking_status = 'cancelled'
        WHERE booking_id = :id
    ");
    $stmt->execute([':id' => $bookingID]);

    $conn->commit();

    try {
        (new Notifier($conn))->cancellationApproved($bookingID, $refundAmount);
    } catch (Throwable $e) {
        error_log("Notification failed [cancellationApproved]: " . $e->getMessage());
    }

    if ($refundAmount > 0) {
        $refundGBP = number_format($refundAmount / 100, 2);
        $messages  = [
            'full'    => "Cancellation approved. A full refund of £{$refundGBP} has been issued to the customer.",
            'prorata' => "Cancellation approved. A pro-rata refund of £{$refundGBP} has been issued to the customer.",
            'minus30' => "Cancellation approved. A refund of £{$refundGBP} has been issued to the customer (total minus 30 days' cost).",
        ];
        $successMsg = $messages[$refundType] ?? "Cancellation approved. A refund of £{$refundGBP} has been issued.";
    } else {
        $successMsg = "Cancellation approved. No refund was due under the cancellation policy.";
    }

    header("Location: /booking.php?id={$bookingID}&success=" . urlencode($successMsg));
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Approve cancel error: " . $e->getMessage());
    header("Location: /booking.php?id={$bookingID}&error=" . urlencode("Could not process cancellation. Please try again."));
    exit;
}
