<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_GET['session_id'] ?? null;

if (!$session_id) {
    die("No session ID provided");
}

// Verify the payment with Stripe
try {
    $stripe = new \Stripe\StripeClient(["api_key" => 'sk_test_NbE079Ks9Vg2NYlFuLBFFrRP']);
    $session = $stripe->checkout->sessions->retrieve($session_id);

    // Check if payment was successful
    if ($session->payment_status !== 'paid') {
        header("Location: /book.php?carpark_id=" . ($_SESSION['pending_booking']['carpark_id'] ?? '') . "&error=" . urlencode("Payment was not completed"));
        exit();
    }

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
        header("Location: /book.php?carpark_id=" . $bookingData['carpark_id'] . "&error=" . urlencode("Car park became full during payment. Please contact support for a refund."));
        exit();
    }

    // Insert the booking
    $bookingID = $bookingsModel->insertBooking(
        $bookingData['carpark_id'],
        $bookingData['name'],
        $bookingData['start'],
        $bookingData['end'],
        $bookingData['user_id']
    );

    // Clear pending booking from session
    unset($_SESSION['pending_booking']);

    // Check if insert was successful
    if (is_array($bookingID) && !$bookingID['success']) {
        header("Location: /book.php?carpark_id=" . $bookingData['carpark_id'] . "&error=" . urlencode("Database error: " . $bookingID['message']));
        exit();
    }

    // Redirect to confirmation page
    header("Location: /booking-confirmation.php?booking_id=" . $bookingID);
    exit();

} catch (Exception $e) {
    error_log("Stripe error: " . $e->getMessage());
    header("Location: /book.php?carpark_id=" . ($_SESSION['pending_booking']['carpark_id'] ?? '') . "&error=" . urlencode("Payment verification failed"));
    exit();
}