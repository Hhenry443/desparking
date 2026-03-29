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
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';

$stripe = new \Stripe\StripeClient(["api_key" => STRIPE_SECRET_KEY]);
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
        if ($payment && !empty($payment['stripe_subscription_id'])) {
            $stripe->subscriptions->update(
                $payment['stripe_subscription_id'],
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
        | CANCEL ONE-TIME BOOKING — apply refund policy from parking contract
        |
        | Short-term (≤ 29 days):
        |   ≥ 48h before start  → full refund
        |   < 48h / started     → no refund
        |
        | Long-term (≥ 30 days):
        |   ≥ 48h before start          → full refund
        |   Started, within first 48h   → pro-rata (unused days)
        |   After first 48h of session  → refund minus 30 days' cost
        |--------------------------------------------------------------
        */
        $bookingStart    = new DateTime($booking['booking_start']);
        $bookingEnd      = new DateTime($booking['booking_end']);
        $now             = new DateTime();
        $originalAmount  = $payment ? (int) $payment['amount'] : 0;

        $totalDays       = (int) ceil(
            ($bookingEnd->getTimestamp() - $bookingStart->getTimestamp()) / 86400
        );
        $isLongTerm      = $totalDays >= 30;
        $secsUntilStart  = $bookingStart->getTimestamp() - $now->getTimestamp();

        if ($secsUntilStart >= (48 * 3600)) {
            // Full refund — cancelled at least 48 hours before start
            $refundAmount = $originalAmount;
            $refundType   = 'full';

        } elseif ($isLongTerm) {
            $secsIntoSession = $now->getTimestamp() - $bookingStart->getTimestamp();

            if ($secsIntoSession <= (48 * 3600)) {
                // Pro-rata — session started but within first 2 days
                $secsRemaining = max(0, $bookingEnd->getTimestamp() - $now->getTimestamp());
                $daysRemaining = (int) ceil($secsRemaining / 86400);
                $refundAmount  = ($originalAmount > 0 && $totalDays > 0)
                    ? (int) round($originalAmount * $daysRemaining / $totalDays)
                    : 0;
                $refundType = 'prorata';
            } else {
                // Refund minus 30 days' cost (notice-period penalty)
                $dailyRate    = $totalDays > 0 ? $originalAmount / $totalDays : 0;
                $refundAmount = max(0, (int) round($originalAmount - $dailyRate * 30));
                $refundType   = 'minus30';
            }
        } else {
            // Short-term, less than 48h until start (or already started) — no refund
            $refundAmount = 0;
            $refundType   = 'none';
        }

        // Issue Stripe refund if applicable
        if ($payment && $refundAmount > 0) {
            $refundParams = [
                'payment_intent' => $payment['stripe_payment_intent_id'],
                'reason'         => 'requested_by_customer',
            ];
            // Only set amount for partial refunds; omit for full refunds
            if ($refundAmount < $originalAmount) {
                $refundParams['amount'] = $refundAmount;
            }

            $refund = $stripe->refunds->create($refundParams);

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

        // Build user-facing message
        if ($refundAmount > 0) {
            $refundGBP = number_format($refundAmount / 100, 2);
            $messages  = [
                'full'    => "Booking cancelled. A full refund of £{$refundGBP} has been issued.",
                'prorata' => "Booking cancelled. A pro-rata refund of £{$refundGBP} for the unused days has been issued.",
                'minus30' => "Booking cancelled. A refund of £{$refundGBP} has been issued (total minus 30 days' notice cost).",
            ];
            $successMsg = $messages[$refundType] ?? "Booking cancelled. A refund of £{$refundGBP} has been issued.";
        } else {
            $successMsg = "Booking cancelled. No refund is due under our cancellation policy.";
        }

        header("Location: /account.php?success=" . urlencode($successMsg));
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
