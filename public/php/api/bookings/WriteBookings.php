<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class WriteBookings extends Bookings
{
    private PDO $db;

    public function writeBooking()
    {
        // Collect POST data safely
        $carparkID   = $_POST['booking_carpark_id'] ?? null;
        $name        = $_POST['booking_name'] ?? null;
        $date        = $_POST['booking_date'] ?? null;
        $time        = $_POST['booking_time'] ?? null;

        if (!$carparkID || !$name || !$date || !$time) {
            return ["success" => false, "message" => "Missing required fields."];
        }

        // Build booking_start datetime
        $bookingStart = $date . " " . $time . ":00";

        // Add 30 minutes for booking_end
        $startDT = new DateTime($bookingStart);
        $endDT   = clone $startDT;
        $endDT->modify("+30 minutes");

        $bookingEnd = $endDT->format("Y-m-d H:i:s");

        // Insert booking using model function below
        $result = $this->insertBooking($carparkID, $name, $bookingStart, $bookingEnd);

        return $result;
    }
}
