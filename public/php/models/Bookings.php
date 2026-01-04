<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Bookings extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function insertBooking($carparkID, $bookingName, $bookingStart, $bookingEnd, $userID)
    {
        try {
            $query = "
            INSERT INTO bookings 
            (booking_carpark_id, booking_name, booking_start, booking_end, booking_user_id) 
            VALUES (:carparkID, :name, :start, :end, :userID)
        ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":carparkID", $carparkID, PDO::PARAM_INT);
            $stmt->bindValue(":name", $bookingName, PDO::PARAM_STR);
            $stmt->bindValue(":start", $bookingStart, PDO::PARAM_STR);
            $stmt->bindValue(":end", $bookingEnd, PDO::PARAM_STR);
            $stmt->bindValue(":userID", $userID, PDO::PARAM_INT);

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

    public function selectBookingsByUserId(int $userID): array
    {
        $query = "
            SELECT 
                b.*,
                c.carpark_name,
                c.carpark_address,
                c.carpark_lat,
                c.carpark_lng
            FROM bookings b
            INNER JOIN carparks c
                ON b.booking_carpark_id = c.carpark_id
            WHERE b.booking_user_id = :userID
            ORDER BY b.booking_start DESC
        ";

        $stmt = $this->db->prepare($query);
        $stmt->execute([':userID' => $userID]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function selectAllBookings(): array
    {
        $query = "SELECT * FROM bookings ORDER BY booking_start DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}// class Users