<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Bookings extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function insertBooking($carparkID, $bookingName, $bookingStart, $bookingEnd)
    {
        try {
            $query = "
            INSERT INTO bookings 
            (booking_carpark_id, booking_name, booking_start, booking_end) 
            VALUES (:carparkID, :name, :start, :end)
        ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":carparkID", $carparkID, PDO::PARAM_INT);
            $stmt->bindValue(":name", $bookingName, PDO::PARAM_STR);
            $stmt->bindValue(":start", $bookingStart, PDO::PARAM_STR);
            $stmt->bindValue(":end", $bookingEnd, PDO::PARAM_STR);

            $stmt->execute();

            return ["success" => true, "message" => "Booking created successfully."];
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}// class Users