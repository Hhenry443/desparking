<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

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

// Load Stripe
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

$stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);
$conn   = Dbh::getConnection();

try {
    // Fetch booking and verify ownership
    $stmt = $conn->prepare("
        SELECT * FROM bookings
        WHERE booking_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $bookingID]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking || (int) $booking['booking_user_id'] !== $userID) {
        header("Location: /account.php?error=" . urlencode("Booking not found"));
        exit;
    }

    if (($booking['booking_status'] ?? '') === 'cancelled') {
        header("Location: /account.php?error=" . urlencode("This booking is already cancelled"));
        exit;
    }

    $isMonthly = !empty($booking['is_monthly']);

    // Fetch the relevant payment record
    $stmt = $conn->prepare("
        SELECT * FROM payments
        WHERE booking_id = :booking_id
          AND type = :type
          AND status = 'succeeded'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([
        ':booking_id' => $bookingID,
        ':type'       => $isMonthly ? 'subscription' : 'initial',
    ]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    $conn->beginTransaction();

    if ($isMonthly) {
        /*
        |--------------------------------------------------------------
        | CANCEL SUBSCRIPTION
        | Sets cancel_at_period_end so the user keeps access until
        | their next renewal date, then Stripe stops charging.
        |--------------------------------------------------------------
        */
        if ($payment) {
            $stripe->subscriptions->update(
                $payment['stripe_payment_intent_id'],
                ['cancel_at_period_end' => true]
            );
        }

        $stmt = $conn->prepare("
            UPDATE bookings
            SET booking_status = 'cancelled'
            WHERE booking_id = :id
        ");
        $stmt->execute([':id' => $bookingID]);

        $conn->commit();

        $accessUntil = date('d M Y', strtotime($booking['booking_end']));
        header("Location: /account.php?success=" . urlencode(
            "Subscription cancelled. You will keep access until {$accessUntil}."
        ));
        exit;

    } else {
        /*
        |--------------------------------------------------------------
        | CANCEL ONE-TIME BOOKING + FULL REFUND
        |--------------------------------------------------------------
        */
        if ($payment) {
            $refund = $stripe->refunds->create([
                'payment_intent' => $payment['stripe_payment_intent_id'],
                'reason'         => 'requested_by_customer',
            ]);

            $stmt = $conn->prepare("
                INSERT INTO payments
                    (booking_id, user_id, stripe_payment_intent_id, stripe_customer_id,
                     amount, currency, type, status)
                VALUES
                    (:booking_id, :user_id, :pi_id, :customer_id,
                     :amount, :currency, 'refund', 'succeeded')
            ");
            $stmt->execute([
                ':booking_id'  => $bookingID,
                ':user_id'     => $userID,
                ':pi_id'       => $refund->payment_intent,
                ':customer_id' => $payment['stripe_customer_id'],
                ':amount'      => $payment['amount'],
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

        $refundNote = $payment
            ? ' A full refund of £' . number_format($payment['amount'] / 100, 2) . ' has been issued.'
            : '';

        header("Location: /account.php?success=" . urlencode(
            "Booking cancelled.{$refundNote}"
        ));
        exit;
    }

} catch (\Stripe\Exception\ApiErrorException $e) {
    $conn->rollBack();
    error_log("Stripe cancel error: " . $e->getMessage());
    header("Location: /booking.php?id={$bookingID}&error=" . urlencode("Payment provider error: " . $e->getMessage()));
    exit;

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Cancel booking error: " . $e->getMessage());
    header("Location: /booking.php?id={$bookingID}&error=" . urlencode("Could not cancel booking. Please try again."));
    exit;
}
