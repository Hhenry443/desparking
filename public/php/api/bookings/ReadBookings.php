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

    public function getBookingsByCarparkId($carparkID)
    {
        $bookings = $this->selectBookingsByCarparkId($carparkID);

        return $bookings;
    } // function getBookingsByCarparkId
    
}// class ReadBookings