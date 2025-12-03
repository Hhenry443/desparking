<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class WriteBookings extends Bookings
{
    private PDO $db;

    public function writeBooking()
    {
        // Collect all POST variables safely
        $booking_carpark_id    = $_POST['booking_carpark_id'] ?? null;
        $booking_name  = $_POST['booking_name'] ?? null;
        $booking_date  = $_POST['booking_date'] ?? null; // e.g. "2025-12-03"
        $booking_time  = $_POST['booking_time'] ?? null; // e.g. "14:30"

        // OPTIONAL: basic sanity checks (recommended)
        if (!$booking_carpark_id || !$booking_name || !$booking_date || !$booking_time) {
            return [
                'success' => false,
                'error' => 'Missing required fields'
            ];
        }

        // Pass all the data into your model method
        $newBooking = $this->insertBooking(
            $booking_carpark_id,
            $booking_name,
            $booking_date,
            $booking_time
        );

        return $newBooking;
    }
}
