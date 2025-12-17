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

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function countOverlappingBookings(int $carparkID, string $bookingStart, string $bookingEnd): int
    {
        $query = "
            SELECT COUNT(booking_id) AS active_bookings
            FROM bookings
            WHERE booking_carpark_id = :carparkID
            AND booking_start < :bookingEnd
            AND booking_end > :bookingStart
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':carparkID'   => $carparkID,
            ':bookingStart' => $bookingStart,
            ':bookingEnd'   => $bookingEnd,
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int) ($result['active_bookings'] ?? 0);
    }
}// class Users