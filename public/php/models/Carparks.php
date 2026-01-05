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

    public function insertCarpark(
        string $carparkName,
        string $carparkDescription,
        string $carparkAddress,
        float $carparkLat,
        float $carparkLng,
        int $carparkCapacity,
        string $carparkFeatures,
        int $ownerID
    ) {
        try {
            $query = "
                INSERT INTO carparks (
                    carpark_name,
                    carpark_description,
                    carpark_address,
                    carpark_lat,
                    carpark_lng,
                    carpark_capacity,
                    carpark_features,
                    carpark_owner
                ) VALUES (
                    :name,
                    :description,
                    :address,
                    :lat,
                    :lng,
                    :capacity,
                    :features,
                    :owner
                )
            ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":name", $carparkName, PDO::PARAM_STR);
            $stmt->bindValue(":description", $carparkDescription, PDO::PARAM_STR);
            $stmt->bindValue(":address", $carparkAddress, PDO::PARAM_STR);
            $stmt->bindValue(":lat", $carparkLat, PDO::PARAM_STR);
            $stmt->bindValue(":lng", $carparkLng, PDO::PARAM_STR);
            $stmt->bindValue(":capacity", $carparkCapacity, PDO::PARAM_INT);
            $stmt->bindValue(":features", $carparkFeatures, PDO::PARAM_STR);
            $stmt->bindValue(":owner", $ownerID, PDO::PARAM_INT);

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    protected function selectCarparksByUserId($userId)
    {
        $sql = "
        SELECT *
        FROM carparks
        WHERE carpark_owner = :userId
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':userId'   => $userId
        ]);

        $results = $stmt->fetchAll();

        if (!empty($results)) {
            return $results;
        } else {
            return false;
        }
    } //selectCarparksByUserId

    public function updateCarpark(
        int $carparkID,
        string $carparkName,
        string $carparkDescription,
        string $carparkAddress,
        int $carparkCapacity,
        float $carparkLat,
        float $carparkLng,
        string $carparkFeatures,
        string $carparkAffiliateUrl,
    ) {
        try {
            $query = "
                UPDATE carparks SET
                    carpark_name = :name,
                    carpark_description = :description,
                    carpark_address = :address,
                    carpark_capacity = :capacity,
                    carpark_lat = :lat,
                    carpark_lng = :lng,
                    carpark_features = :features,
                    carpark_affiliate_url = :affiliate_url
                WHERE carpark_id = :id
            ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":id", $carparkID, PDO::PARAM_INT);
            $stmt->bindValue(":name", $carparkName, PDO::PARAM_STR);
            $stmt->bindValue(":description", $carparkDescription, PDO::PARAM_STR);
            $stmt->bindValue(":address", $carparkAddress, PDO::PARAM_STR);
            $stmt->bindValue(":capacity", $carparkCapacity, PDO::PARAM_INT);
            $stmt->bindValue(":lat", $carparkLat, PDO::PARAM_STR);
            $stmt->bindValue(":lng", $carparkLng, PDO::PARAM_STR);
            $stmt->bindValue(":features", $carparkFeatures, PDO::PARAM_STR);
            $stmt->bindValue(":affiliate_url", $carparkAffiliateUrl, PDO::PARAM_STR);

            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function deleteCarpark(int $carparkID)
    {
        try {
            // First delete all related rates
            $query = "DELETE FROM rates WHERE carpark_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $carparkID]);

            // Then delete all related bookings
            $query = "DELETE FROM bookings WHERE booking_carpark_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $carparkID]);

            // Finally delete the carpark
            $query = "DELETE FROM carparks WHERE carpark_id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $carparkID]);

            return true;
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // Get all carparks (for admin)
    public function getAllCarparks()
    {
        $query = "SELECT * FROM carparks ORDER BY carpark_id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}// class Carparks