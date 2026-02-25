<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_GET['session_id'] ?? null;
$type = $_GET['type'] ?? 'booking'; // booking | extension
$bookingID = $_GET['booking_id'] ?? null;

if (!$session_id) {
    die("No session ID provided");
}

try {

    // 🔐 Verify Stripe payment
    $stripe = new \Stripe\StripeClient([
        "api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP'
    ]);

    $session = $stripe->checkout->sessions->retrieve(
        $session_id,
        ['expand' => ['payment_intent']]
    );

    if ($session->payment_status !== 'paid') {

        if ($type === 'extension') {
            header("Location: /account.php?error=" . urlencode("Payment was not completed"));
        } else {
            header("Location: /book.php?carpark_id=" .
                ($_SESSION['pending_booking']['carpark_id'] ?? '') .
                "&error=" . urlencode("Payment was not completed"));
        }

        exit();
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE BOOKING EXTENSION
    |--------------------------------------------------------------------------
    */
    if ($type === 'extension' && $bookingID) {

        $conn = Dbh::getConnection();
        $conn->beginTransaction();

        try {

            $paymentIntent = $session->payment_intent;

            // Mark payment as succeeded
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
                ':booking_id' => $bookingID
            ]);

            $newStart = $_SESSION['new_start'] ?? null;
            $newEnd   = $_SESSION['new_end'] ?? null;

            if (!$newStart || !$newEnd) {
                if (isset($_SESSION['pending_extension'])) {
                    $newStart = $_SESSION['pending_extension']['new_start'] ?? null;
                    $newEnd   = $_SESSION['pending_extension']['new_end'] ?? null;
                }
            }

            if (!$newStart || !$newEnd) {
                throw new Exception("Missing booking times for extension.");
            }

            $stmt = $conn->prepare("
                UPDATE bookings 
                SET booking_start = :new_start, 
                    booking_end = :new_end
                WHERE booking_id = :booking_id
            ");

            $stmt->execute([
                ':new_start' => $newStart,
                ':new_end'   => $newEnd,
                ':booking_id'=> $bookingID
            ]);

            $conn->commit();

            unset($_SESSION['pending_extension']);

            header("Location: /account.php?success=" .
                urlencode("Payment successful! Your booking has been updated."));
            exit();

        } catch (Exception $e) {

            $conn->rollBack();
            error_log("Extension payment error: " . $e->getMessage());

            header("Location: /account.php?error=" .
                urlencode("Error updating booking"));
            exit();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE NEW BOOKING
    |--------------------------------------------------------------------------
    */

    $bookingData = $_SESSION['pending_booking'] ?? null;

    if (!$bookingData) {
        die("No pending booking found");
    }

    $conn = Dbh::getConnection();
    $conn->beginTransaction();

    try {

        // Re-verify carpark exists
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById($bookingData['carpark_id']);

        if (!$carpark || !isset($carpark['carpark_capacity'])) {
            throw new Exception("Car park not found");
        }

        $capacity = (int) $carpark['carpark_capacity'];

        // Re-check overlapping bookings
        $bookingsModel = new WriteBookings();

        $activeBookings = $bookingsModel->countOverlappingBookings(
            (int) $bookingData['carpark_id'],
            $bookingData['start'],
            $bookingData['end']
        );

        if ($activeBookings >= $capacity) {
            throw new Exception("Car park became full during payment.");
        }

        // 🔒 Re-verify vehicle ownership
        $stmt = $conn->prepare("
            SELECT vehicle_id
            FROM vehicles
            WHERE vehicle_id = :vehicleID
            AND user_id = :userID
            LIMIT 1
        ");

        $stmt->execute([
            ':vehicleID' => $bookingData['vehicle_id'],
            ':userID'    => $bookingData['user_id']
        ]);

        if (!$stmt->fetch()) {
            throw new Exception("Invalid vehicle selected.");
        }

        // Insert booking
        $newBookingID = $bookingsModel->insertBooking(
            (int) $bookingData['carpark_id'],
            $bookingData['name'],
            $bookingData['start'],
            $bookingData['end'],
            (int) $bookingData['user_id'],
            (int) $bookingData['vehicle_id']
        );

        if (is_array($newBookingID) && !$newBookingID['success']) {
            throw new Exception("Database error: " . $newBookingID['message']);
        }

        // Record payment
        $paymentIntent = $session->payment_intent;
        $paymentsModel = new WritePayments();

        if (!$paymentsModel->paymentExists($paymentIntent->id)) {

            $paymentsModel->insertPayment([
                'booking_id' => $newBookingID,
                'user_id' => $bookingData['user_id'],
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $paymentIntent->customer,
                'amount' => $paymentIntent->amount_received,
                'currency' => $paymentIntent->currency,
                'type' => 'initial',
                'status' => 'succeeded'
            ]);
        }

        $conn->commit();

        unset($_SESSION['pending_booking']);

        header("Location: /booking-confirmation.php?booking_id=" . $newBookingID);
        exit();

    } catch (Exception $e) {

        $conn->rollBack();
        error_log("Booking return error: " . $e->getMessage());

        header("Location: /book.php?carpark_id=" .
            ($bookingData['carpark_id'] ?? '') .
            "&error=" . urlencode($e->getMessage()));
        exit();
    }

} catch (Exception $e) {

    error_log("Stripe verification error: " . $e->getMessage());

    if ($type === 'extension') {
        header("Location: /account.php?error=" .
            urlencode("Payment verification failed"));
    } else {
        header("Location: /book.php?carpark_id=" .
            ($_SESSION['pending_booking']['carpark_id'] ?? '') .
            "&error=" . urlencode("Payment verification failed"));
    }

    exit();
}