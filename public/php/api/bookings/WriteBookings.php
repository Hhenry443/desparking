<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class WriteBookings extends Bookings
{
    private PDO $db;

    public function writeBooking()
    {
        $carpark_ID = $_POST['carpark_id'];
        $booking_name = $_POST['booking_name'];

        $newBooking = $this->insertBooking($carpark_ID, $booking_name);

        return $newBooking;
    } // function writeBooking

}// class WriteBookings