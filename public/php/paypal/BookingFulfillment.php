<?php

/**
 * Shared booking-creation logic for PayPal payments.
 *
 * Both the synchronous browser-driven capture/confirm calls (capture-order.php,
 * confirm-subscription.php) and the async webhook can reach these — whichever
 * gets there first wins, the other is a no-op via the idempotency check at the
 * top of each function. This mirrors the Stripe webhook's role but PayPal's
 * explicit-capture model means the synchronous path is now primary and the
 * webhook is the backstop, not the other way round.
 */

function fulfillOrderBooking(string $orderId, string $captureId, ?string $payerId, int $amountPence, string $currency, PDO $conn): ?int
{
    $paymentsModel = new WritePayments();

    if ($paymentsModel->paymentExists($orderId)) {
        return $paymentsModel->getBookingIdByOrderId($orderId);
    }

    $checkout = $paymentsModel->getPendingCheckoutByOrderId($orderId);
    if (!$checkout) {
        FlowLog::write('paypal', 'abort_missing_pending_checkout', null, $orderId);
        return null;
    }

    $conn->beginTransaction();
    try {
        $carparkReader = new ReadCarparks();
        $carpark       = $carparkReader->getCarparkById($checkout['carpark_id']);
        $capacity      = (int) ($carpark['carpark_capacity'] ?? 1);

        $bookingsModel = new WriteBookings();
        $overlapping   = $bookingsModel->countOverlappingBookings((int) $checkout['carpark_id'], $checkout['start'], $checkout['end']);

        if ($overlapping >= $capacity) {
            FlowLog::write('paypal', 'abort_carpark_full', null, $orderId,
                "PAID BUT NO BOOKING — carpark {$checkout['carpark_id']} full ({$overlapping}/{$capacity}) for {$checkout['start']}–{$checkout['end']}. Needs refund.");
            $conn->rollBack();
            return null;
        }

        $bookingId = $bookingsModel->insertBooking(
            (int) $checkout['carpark_id'],
            $checkout['name'],
            $checkout['start'],
            $checkout['end'],
            $checkout['user_id'] ? (int) $checkout['user_id'] : null,
            $checkout['vehicle_id'] ? (int) $checkout['vehicle_id'] : null,
            false,
            $checkout['registration'],
            $checkout['email'] ?: null
        );

        if (is_array($bookingId)) {
            throw new Exception('insertBooking failed: ' . ($bookingId['message'] ?? ''));
        }

        $paymentsModel->insertPayment([
            'booking_id'        => $bookingId,
            'user_id'           => $checkout['user_id'],
            'paypal_order_id'   => $orderId,
            'paypal_capture_id' => $captureId,
            'paypal_payer_id'   => $payerId,
            'amount'            => $amountPence,
            'owner_amount'      => $checkout['owner_amount'],
            'currency'          => $currency,
            'type'              => 'initial',
            'status'            => 'succeeded',
        ]);

        $conn->commit();
        FlowLog::write('paypal', 'booking_committed', (int) $bookingId, $orderId);

        try {
            $notifier = new Notifier($conn);
            if ($checkout['user_id']) {
                $notifier->bookingConfirmed($bookingId, (int) $checkout['user_id']);
            } elseif ($checkout['email']) {
                $notifier->bookingConfirmedGuest($bookingId, $checkout['name'], $checkout['email']);
            } else {
                FlowLog::write('paypal', 'notify_SKIPPED', (int) $bookingId, $orderId, 'NO RECIPIENT');
            }
        } catch (Throwable $e) {
            error_log('Notification failed [bookingConfirmed]: ' . $e->getMessage());
        }

        return (int) $bookingId;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('PayPal order fulfillment failed: ' . $e->getMessage());
        FlowLog::write('paypal', 'booking_failed', null, $orderId, $e->getMessage());
        return null;
    }
}

function fulfillExtension(string $orderId, string $captureId, PDO $conn): ?int
{
    $paymentsModel = new WritePayments();
    $checkout = $paymentsModel->getPendingCheckoutByOrderId($orderId);
    if (!$checkout || !$checkout['booking_id']) {
        FlowLog::write('paypal', 'abort_missing_pending_checkout', null, $orderId);
        return null;
    }

    $bookingId = (int) $checkout['booking_id'];

    $conn->beginTransaction();
    try {
        $stmt = $conn->prepare("
            UPDATE payments
            SET status = 'succeeded', paypal_order_id = :order_id, paypal_capture_id = :capture_id
            WHERE booking_id = :booking_id AND type = 'initial' AND status = 'pending'
        ");
        $stmt->execute([
            ':order_id'   => $orderId,
            ':capture_id' => $captureId,
            ':booking_id' => $bookingId,
        ]);

        $stmt = $conn->prepare("
            UPDATE bookings SET booking_start = :start, booking_end = :end WHERE booking_id = :booking_id
        ");
        $stmt->execute([':start' => $checkout['start'], ':end' => $checkout['end'], ':booking_id' => $bookingId]);

        $conn->commit();
        FlowLog::write('paypal', 'extension_committed', $bookingId, $orderId);

        try {
            (new Notifier($conn))->bookingEdited($bookingId, (int) ($checkout['user_id'] ?? 0));
        } catch (Throwable $e) {
            error_log('Notification failed [bookingEdited]: ' . $e->getMessage());
        }

        return $bookingId;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('PayPal extension fulfillment failed: ' . $e->getMessage());
        FlowLog::write('paypal', 'extension_failed', $bookingId, $orderId, $e->getMessage());
        return null;
    }
}

function fulfillSubscriptionBooking(string $subscriptionId, ?string $payerId, PDO $conn): ?int
{
    $paymentsModel = new WritePayments();

    if ($paymentsModel->subscriptionPaymentExists($subscriptionId)) {
        return $paymentsModel->getBookingIdBySubscriptionId($subscriptionId);
    }

    $checkout = $paymentsModel->getPendingCheckoutBySubscriptionId($subscriptionId);
    if (!$checkout) {
        FlowLog::write('paypal', 'abort_missing_pending_checkout', null, $subscriptionId);
        return null;
    }

    $amountPence = (int) ($checkout['amount'] ?? 0);
    $currency    = 'gbp';

    $conn->beginTransaction();
    try {
        $bookingsModel = new WriteBookings();
        $bookingId = $bookingsModel->insertBooking(
            (int) $checkout['carpark_id'],
            $checkout['name'],
            $checkout['start'],
            $checkout['end'],
            $checkout['user_id'] ? (int) $checkout['user_id'] : null,
            $checkout['vehicle_id'] ? (int) $checkout['vehicle_id'] : null,
            true,
            $checkout['registration'],
            $checkout['email'] ?: null
        );

        if (is_array($bookingId)) {
            throw new Exception('insertBooking failed: ' . ($bookingId['message'] ?? ''));
        }

        $paymentsModel->insertPayment([
            'booking_id'             => $bookingId,
            'user_id'                => $checkout['user_id'],
            'paypal_subscription_id' => $subscriptionId,
            'paypal_payer_id'        => $payerId,
            'amount'                 => $amountPence,
            'owner_amount'           => $checkout['owner_amount'],
            'currency'               => $currency,
            'type'                   => 'subscription',
            'status'                 => 'succeeded',
        ]);

        $conn->commit();
        FlowLog::write('paypal', 'subscription_booking_committed', (int) $bookingId, $subscriptionId);

        try {
            (new Notifier($conn))->subscriptionCreated($bookingId, $checkout['user_id'] ? (int) $checkout['user_id'] : null);
        } catch (Throwable $e) {
            error_log('Notification failed [subscriptionCreated]: ' . $e->getMessage());
        }

        return (int) $bookingId;
    } catch (Exception $e) {
        $conn->rollBack();
        error_log('PayPal subscription fulfillment failed: ' . $e->getMessage());
        FlowLog::write('paypal', 'subscription_booking_failed', null, $subscriptionId, $e->getMessage());
        return null;
    }
}
