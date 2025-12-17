<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Carparks extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    protected function selectAllCarparks()
    {
        $sql = "
    SELECT *
    FROM carparks
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll();

        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }
    } //selectAllCarparks

    protected function selectCarparkByID($carparkID)
    {
        $sql = "
    SELECT *
    FROM carparks
    WHERE carpark_id = :carparkID
    ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':carparkID'   => $carparkID
        ]);

        $results = $stmt->fetch();

        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }
    } //selectAllCarparks

    protected function selectAvailableCarparks(
        float $lat,
        float $lng,
        float $radiusKm,
        string $startTime,
        string $endTime
    ) {
        $sql = "
            SELECT 
                c.*,
                (
                    6371 * acos(
                        cos(radians(:carpark_lat)) *
                        cos(radians(c.carpark_lat)) *
                        cos(radians(c.carpark_lng) - radians(:carpark_lng)) +
                        sin(radians(:carpark_lat)) *
                        sin(radians(c.carpark_lat))
                    )
                ) AS distance,
                COUNT(DISTINCT b.booking_id) AS active_bookings,
                (c.carpark_capacity - COUNT(DISTINCT b.booking_id)) AS spaces_left
            FROM carparks c
            LEFT JOIN bookings b
                ON b.booking_carpark_id = c.carpark_id
                AND b.booking_start < :endTime
                AND b.booking_end   > :startTime
            GROUP BY c.carpark_id
            HAVING distance <= :radius
            AND spaces_left > 0
            ORDER BY distance ASC
            ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':carpark_lat'       => $lat,
            ':carpark_lng'       => $lng,
            ':radius'    => $radiusKm,
            ':startTime' => $startTime,
            ':endTime'   => $endTime
        ]);

        return $stmt->fetchAll() ?: [];
    } // searchAvailableCarparks


}// class Carparks