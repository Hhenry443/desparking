<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class ReadBookings extends Bookings
{
    private PDO $db;

    public function getBookings()
    {
        $bookings = array();

        $bookings = $this->selectAllBookings();

        return $bookings;
    } // function getBookings

    public function getBookingsByUserId($userID)
    {
        $bookings = $this->selectBookingsByUserId($userID);

        return $bookings;
    }

    public function getBookingByBookingId($bookingID)
    {
        $booking = $this->selectBookingByBookingId($bookingID);

        return $booking;
    } // function getBookingByBookingId

    public function getBookingsByCarparkId($carparkID)
    {
        $bookings = $this->selectBookingsByCarparkId($carparkID);

        return $bookings;
    } // function getBookingsByCarparkId

    public function getPendingCancellations(): array
    {
        return $this->selectPendingCancellations();
    }

    public function getAllBookingsWithCarparks(): array
    {
        return $this->selectAllBookingsWithCarparks();
    }

    public function getBookingDetailsJSON(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Not authorised']);
            exit;
        }

        $bookingID = $_GET['booking_id'] ?? null;

        if (!$bookingID || !ctype_digit((string)$bookingID)) {
            echo json_encode(['success' => false, 'message' => 'Missing booking_id']);
            exit;
        }

        $booking = $this->selectBookingFullDetails((int)$bookingID);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit;
        }

        echo json_encode(['success' => true, 'booking' => $booking]);
        exit;
    }
}// class ReadBookings