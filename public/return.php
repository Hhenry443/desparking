<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_GET['session_id'] ?? null;
$type = $_GET['type'] ?? 'booking'; // 'booking' or 'extension'
$bookingID = $_GET['booking_id'] ?? null;

if (!$session_id) {
    die("No session ID provided");
}

// Verify the payment with Stripe
try {
    $stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);
    $session = $stripe->checkout->sessions->retrieve(
        $session_id,
        ['expand' => ['payment_intent']]
    );

    // Check if payment was successful
    if ($session->payment_status !== 'paid') {
        if ($type === 'extension') {
            header("Location: /account.php?error=" . urlencode("Payment was not completed"));
        } else {
            header("Location: /book.php?carpark_id=" . ($_SESSION['pending_booking']['carpark_id'] ?? '') . "&error=" . urlencode("Payment was not completed"));
        }
        exit();
    }

    // HANDLE BOOKING EXTENSION
    if ($type === 'extension' && $bookingID) {
        $conn = Dbh::getConnection();
        $conn->beginTransaction();

        try {
            // Get the payment intent
            $paymentIntent = $session->payment_intent;

            // Update payment status in database - update the pending record
            $stmt = $conn->prepare("
                UPDATE payments 
                SET status = 'succeeded',
                    stripe_payment_intent_id = :payment_intent_id
                WHERE booking_id = :booking_id 
                  AND type = 'initial' 
                  AND status = 'pending'
            ");
            $stmt->bindParam(':payment_intent_id', $paymentIntent->id);
            $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
            $stmt->execute();

            // Get the new booking times from session 
            $newStart = $_SESSION['new_start'] ?? null;
            $newEnd = $_SESSION['new_end'] ?? null;
            
            error_log("Extension Session data: start=$newStart, end=$newEnd");
            
            // Fallback: try to get from PHP session if not in Stripe metadata
            if (!$newStart || !$newEnd) {
                error_log("Metadata missing from Stripe, checking PHP session");
                if (isset($_SESSION['pending_extension'])) {
                    $newStart = $_SESSION['pending_extension']['new_start'] ?? null;
                    $newEnd = $_SESSION['pending_extension']['new_end'] ?? null;
                    error_log("Got times from PHP session: start=$newStart, end=$newEnd");
                }
            }

            if ($newStart && $newEnd) {
                // Update booking times
                $stmt = $conn->prepare("
                    UPDATE bookings 
                    SET booking_start = :new_start, 
                        booking_end = :new_end
                    WHERE booking_id = :booking_id
                ");
                $stmt->bindParam(':new_start', $newStart);
                $stmt->bindParam(':new_end', $newEnd);
                $stmt->bindParam(':booking_id', $bookingID, PDO::PARAM_INT);
                $stmt->execute();

                error_log("Booking $bookingID updated with new times: $newStart to $newEnd");
            } else {
                error_log("ERROR: Still missing new_start or new_end after checking both sources");
                throw new Exception("Missing booking times - cannot update booking");
            }

            $conn->commit();

            // Clear session data
            unset($_SESSION['pending_extension']);

            header("Location: /account.php?success=" . urlencode("Payment successful! Your booking has been updated."));
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            error_log("Extension payment error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            header("Location: /account.php?error=" . urlencode("Error updating booking"));
            exit();
        }
    }

    // HANDLE NEW BOOKING (original code)
    // Get booking data from session
    $bookingData = $_SESSION['pending_booking'] ?? null;
    if (!$bookingData) {
        die("No pending booking found");
    }

    // Verify capacity one more time before inserting
    $ReadCarparks = new ReadCarparks();
    $carpark = $ReadCarparks->getCarparkById($bookingData['carpark_id']);
    if (!$carpark || !isset($carpark['carpark_capacity'])) {
        header("Location: /book.php?carpark_id=" . $bookingData['carpark_id'] . "&error=" . urlencode("Car park not found"));
        exit();
    }

    $capacity = (int) $carpark['carpark_capacity'];

    // Check for overlapping bookings
    $bookingsModel = new WriteBookings();
    $activeBookings = $bookingsModel->countOverlappingBookings(
        (int) $bookingData['carpark_id'],
        $bookingData['start'],
        $bookingData['end']
    );

    if ($activeBookings >= $capacity) {
        // Carpark is full - ideally you'd refund the payment here
        header("Location: /book.php?carpark_id=" . $bookingData['carpark_id'] . "&error=" . urlencode("Car park became full during payment. Please try another time or location."));
        exit();
    }

    // Insert the booking
    $newBookingID = $bookingsModel->insertBooking(
        $bookingData['carpark_id'],
        $bookingData['name'],
        $bookingData['start'],
        $bookingData['end'],
        $bookingData['user_id']
    );

    // Clear pending booking from session
    unset($_SESSION['pending_booking']);

    // Check if insert was successful
    if (is_array($newBookingID) && !$newBookingID['success']) {
        header("Location: /book.php?carpark_id=" . $bookingData['carpark_id'] . "&error=" . urlencode("Database error: " . $newBookingID['message']));
        exit();
    }

    // Record the payment
    $paymentIntent = $session->payment_intent;
    $paymentsModel = new WritePayments();

    if ($paymentsModel->paymentExists($paymentIntent->id)) {
        header("Location: /booking-confirmation.php?booking_id=" . $newBookingID);
        exit();
    }

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

    // Redirect to confirmation page
    header("Location: /booking-confirmation.php?booking_id=" . $newBookingID);
    exit();

} catch (Exception $e) {
    error_log("Stripe error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    if ($type === 'extension') {
        header("Location: /account.php?error=" . urlencode("Payment verification failed"));
    } else {
        header("Location: /book.php?carpark_id=" . ($_SESSION['pending_booking']['carpark_id'] ?? '') . "&error=" . urlencode("Payment verification failed"));
    }
    exit();
}