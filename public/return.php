<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/stripe.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_GET['session_id'] ?? null;
$type       = $_GET['type']       ?? 'booking'; // booking | extension | subscription
$bookingID  = $_GET['booking_id'] ?? null;

if (!$session_id) {
    die("No session ID provided");
}

try {

    $stripe = new \Stripe\StripeClient(["api_key" => STRIPE_SECRET_KEY]);

    /*
    |--------------------------------------------------------------------------
    | HANDLE MONTHLY SUBSCRIPTION
    |--------------------------------------------------------------------------
    | Primary path: webhook has already created the booking.
    | Fallback path: webhook hasn't fired yet — create the booking here.
    */
    if ($type === 'subscription') {

        $session = $stripe->checkout->sessions->retrieve(
            $session_id,
            ['expand' => ['subscription']]
        );

        if ($session->status !== 'complete') {
            header("Location: /book.php?carpark_id=" .
                ($_SESSION['pending_booking']['carpark_id'] ?? '') .
                "&error=" . urlencode("Subscription was not completed"));
            exit();
        }

        $subscriptionId = is_string($session->subscription)
            ? $session->subscription
            : $session->subscription->id;

        $paymentsModel = new WritePayments();

        // --- Primary path: webhook already created the booking ---
        $existingBookingId = $paymentsModel->getBookingIdBySubscriptionId($subscriptionId);
        if ($existingBookingId) {
            unset($_SESSION['pending_booking']);
            header("Location: /booking-confirmation.php?booking_id=" . $existingBookingId);
            exit();
        }

        // --- Fallback path: webhook hasn't fired yet ---
        $bookingData = $_SESSION['pending_booking'] ?? null;
        if (!$bookingData) {
            header("Location: /account.php?error=" . urlencode("Booking is being processed. Check your bookings shortly."));
            exit();
        }

        $conn = Dbh::getConnection();
        $conn->beginTransaction();
        try {
            $bookingsModel = new WriteBookings();
            $newBookingID  = $bookingsModel->insertBooking(
                (int) $bookingData['carpark_id'],
                $bookingData['name'],
                $bookingData['start'],
                $bookingData['end'],
                $bookingData['user_id'] ? (int) $bookingData['user_id'] : null,
                $bookingData['vehicle_id'] ? (int) $bookingData['vehicle_id'] : null,
                true,
                $bookingData['registration'] ?? null
            );

            if (is_array($newBookingID) && !$newBookingID['success']) {
                throw new Exception("Database error: " . $newBookingID['message']);
            }

            // Only insert if webhook hasn't raced us
            if (!$paymentsModel->subscriptionPaymentExists($subscriptionId)) {
                $paymentsModel->insertPayment([
                    'booking_id'             => $newBookingID,
                    'user_id'                => $bookingData['user_id'],
                    'stripe_payment_intent_id' => null,
                    'stripe_subscription_id' => $subscriptionId,
                    'stripe_customer_id'     => $session->customer,
                    'amount'                 => $session->amount_total ?? 0,
                    'currency'               => $session->currency ?? 'gbp',
                    'type'                   => 'subscription',
                    'status'                 => 'succeeded',
                ]);
            }

            $conn->commit();
            unset($_SESSION['pending_booking']);

            header("Location: /booking-confirmation.php?booking_id=" . $newBookingID);
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("return.php subscription fallback error: " . $e->getMessage());
            header("Location: /book.php?carpark_id=" .
                ($bookingData['carpark_id'] ?? '') .
                "&error=" . urlencode($e->getMessage()));
            exit();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ONE-TIME PAYMENT (booking or extension)
    |--------------------------------------------------------------------------
    */
    $session = $stripe->checkout->sessions->retrieve(
        $session_id,
        ['expand' => ['payment_intent']]
    );

    if ($session->payment_status !== 'paid') {
        $redirect = $type === 'extension'
            ? "/account.php?error=" . urlencode("Payment was not completed")
            : "/book.php?carpark_id=" . ($_SESSION['pending_booking']['carpark_id'] ?? '') .
            "&error=" . urlencode("Payment was not completed");
        header("Location: $redirect");
        exit();
    }

    $paymentIntent = $session->payment_intent;

    /*
    |--------------------------------------------------------------------------
    | HANDLE BOOKING EXTENSION
    |--------------------------------------------------------------------------
    */
    if ($type === 'extension' && $bookingID) {

        $conn = Dbh::getConnection();
        $conn->beginTransaction();
        try {
            $stmt = $conn->prepare("
                UPDATE payments
                SET status = 'succeeded',
                    stripe_payment_intent_id = :payment_intent_id
                WHERE booking_id = :booking_id
                  AND type = 'initial'
                  AND status = 'pending'
            ");
            $stmt->execute([
                ':payment_intent_id' => $paymentIntent->id,
                ':booking_id'        => $bookingID,
            ]);

            $newStart = $_SESSION['pending_extension']['new_start'] ?? $_SESSION['new_start'] ?? null;
            $newEnd   = $_SESSION['pending_extension']['new_end']   ?? $_SESSION['new_end']   ?? null;

            if (!$newStart || !$newEnd) {
                throw new Exception("Missing booking times for extension.");
            }

            $stmt = $conn->prepare("
                UPDATE bookings
                SET booking_start = :new_start, booking_end = :new_end
                WHERE booking_id = :booking_id
            ");
            $stmt->execute([
                ':new_start'  => $newStart,
                ':new_end'    => $newEnd,
                ':booking_id' => $bookingID,
            ]);

            $conn->commit();
            unset($_SESSION['pending_extension']);

            header("Location: /account.php?success=" . urlencode("Your booking has been updated."));
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            error_log("return.php extension error: " . $e->getMessage());
            header("Location: /account.php?error=" . urlencode("Error updating booking"));
            exit();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE NEW ONE-TIME BOOKING
    |--------------------------------------------------------------------------
    | Primary path: webhook already created the booking.
    | Fallback path: webhook hasn't fired yet — create it here.
    */
    $paymentsModel = new WritePayments();

    // --- Primary path ---
    $existingBookingId = $paymentsModel->getBookingIdByPaymentIntent($paymentIntent->id);
    if ($existingBookingId) {
        unset($_SESSION['pending_booking']);
        header("Location: /booking-confirmation.php?booking_id=" . $existingBookingId);
        exit();
    }

    // --- Fallback path ---
    $bookingData = $_SESSION['pending_booking'] ?? null;
    if (!$bookingData) {
        header("Location: /account.php?error=" . urlencode("Booking is being processed. Check your bookings shortly."));
        exit();
    }

    $conn = Dbh::getConnection();
    $conn->beginTransaction();
    try {
        $ReadCarparks = new ReadCarparks();
        $carpark      = $ReadCarparks->getCarparkById($bookingData['carpark_id']);

        if (!$carpark || !isset($carpark['carpark_capacity'])) {
            throw new Exception("Car park not found");
        }

        $bookingsModel  = new WriteBookings();
        $activeBookings = $bookingsModel->countOverlappingBookings(
            (int) $bookingData['carpark_id'],
            $bookingData['start'],
            $bookingData['end']
        );

        if ($activeBookings >= (int) $carpark['carpark_capacity']) {
            throw new Exception("Car park became full during payment.");
        }

        if ($bookingData['user_id']) {
            $stmt = $conn->prepare("
                SELECT vehicle_id FROM vehicles
                WHERE vehicle_id = :vehicleID AND user_id = :userID
                LIMIT 1
            ");
            $stmt->execute([':vehicleID' => $bookingData['vehicle_id'], ':userID' => $bookingData['user_id']]);
            if (!$stmt->fetch()) {
                throw new Exception("Invalid vehicle selected.");
            }
        }

        $newBookingID = $bookingsModel->insertBooking(
            (int) $bookingData['carpark_id'],
            $bookingData['name'],
            $bookingData['start'],
            $bookingData['end'],
            $bookingData['user_id'] ? (int) $bookingData['user_id'] : null,
            $bookingData['vehicle_id'] ? (int) $bookingData['vehicle_id'] : null,
            false,
            $bookingData['registration'] ?? null
        );

        if (is_array($newBookingID) && !$newBookingID['success']) {
            throw new Exception("Database error: " . $newBookingID['message']);
        }

        // Only insert if webhook hasn't raced us
        if (!$paymentsModel->paymentExists($paymentIntent->id)) {
            $paymentsModel->insertPayment([
                'booking_id'               => $newBookingID,
                'user_id'                  => $bookingData['user_id'],
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_subscription_id'   => null,
                'stripe_customer_id'       => $session->customer,
                'amount'                   => $paymentIntent->amount_received,
                'currency'                 => $paymentIntent->currency,
                'type'                     => 'initial',
                'status'                   => 'succeeded',
            ]);
        }

        $conn->commit();
        unset($_SESSION['pending_booking']);

        header("Location: /booking-confirmation.php?booking_id=" . $newBookingID);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        error_log("return.php fallback booking error: " . $e->getMessage());
        header("Location: /book.php?carpark_id=" .
            ($bookingData['carpark_id'] ?? '') .
            "&error=" . urlencode($e->getMessage()));
        exit();
    }
} catch (Exception $e) {
    error_log("return.php Stripe error: " . $e->getMessage());
    $redirect = $type === 'extension'
        ? "/account.php?error=" . urlencode("Payment verification failed")
        : "/book.php?carpark_id=" .
        ($_SESSION['pending_booking']['carpark_id'] ?? '') .
        "&error=" . urlencode("Payment verification failed");
    header("Location: $redirect");
    exit();
}
