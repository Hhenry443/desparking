<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';

$WriteBookings = new WriteBookings();

$action = $_POST['action'] ?? null;

switch ($action) {
    case 'insertBooking':
        $carpark_ID = $_POST['carpark_id'];
        $booking_name = $_POST['booking_name'];

        $newBooking = $WriteBookings->writeBooking($carpark_ID, $booking_name);
}
