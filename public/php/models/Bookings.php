<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Bookings extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function insertBooking($carpark_id, $booking_name)
    {
        $sql = "
        INSERT INTO bookings
        (booking_carpark_id, booking_name)
        VALUES
        (:carpark_id, :booking_name)
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':carpark_id', $carpark_id, PDO::PARAM_INT);
        $stmt->bindParam(':booking_name', $booking_name, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    } //selectAllUsers

}// class Users