<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Bookings extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function insertBooking($booking_carpark_id, $booking_name, $booking_date, $booking_time)
    {
        $sql = "INSERT INTO bookings (booking_carpark_id, booking_name, booking_date, booking_time)
            VALUES (:booking_carpark_id, :booking_name, :booking_date, :booking_time)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_carpark_id'   => $booking_carpark_id,
            ':booking_name' => $booking_name,
            ':booking_date' => $booking_date,
            ':booking_time' => $booking_time
        ]);

        return $this->db->lastInsertId();
    }
}// class Users