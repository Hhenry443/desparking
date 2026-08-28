<?php
require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/WritePayments.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$orderId        = $_GET['order_id']        ?? null;
$subscriptionId = $_GET['subscription_id'] ?? null;
$type           = $_GET['type']            ?? 'booking'; // booking | extension | subscription

/*
|--------------------------------------------------------------------------
| Confirmation lookup
|--------------------------------------------------------------------------
| By the time the browser lands here, capture-order.php or
| confirm-subscription.php has already captured the payment and created the
| booking synchronously (that's the primary path now — PayPal's explicit
| capture step means there's no "paid but redirect never happened" gap like
| Stripe's async checkout had). This page just looks the result up. The only
| time it won't find anything yet is a rare race with the webhook, which is
| the backstop for both paths.
|--------------------------------------------------------------------------
*/

$paymentsModel = new WritePayments();

if ($type === 'subscription') {
    if (!$subscriptionId) {
        die("No subscription ID provided");
    }

    $bookingId = $paymentsModel->getBookingIdBySubscriptionId($subscriptionId);
    if ($bookingId) {
        unset($_SESSION['pending_booking']);
        // Lets booking-confirmation.php show a guest their access link without
        // handing tokens out to anyone who guesses a booking id.
        $_SESSION['completed_booking_id'] = (int) $bookingId;
        header("Location: /booking-confirmation.php?booking_id=" . $bookingId);
        exit();
    }

    header("Location: /account.php?error=" . urlencode("Subscription is being processed. Check your bookings shortly."));
    exit();
}

if (!$orderId) {
    die("No order ID provided");
}

$bookingId = $paymentsModel->getBookingIdByOrderId($orderId);

if ($bookingId) {
    unset($_SESSION['pending_booking']);
    unset($_SESSION['pending_extension']);

    if ($type === 'extension') {
        header("Location: /account.php?success=" . urlencode("Your booking has been updated."));
    } else {
        $_SESSION['completed_booking_id'] = (int) $bookingId;
        header("Location: /booking-confirmation.php?booking_id=" . $bookingId);
    }
    exit();
}

$redirect = $type === 'extension'
    ? "/account.php?error=" . urlencode("Payment is being processed. Check your account shortly.")
    : "/account.php?error=" . urlencode("Booking is being processed. Check your bookings shortly.");
header("Location: $redirect");
exit();
