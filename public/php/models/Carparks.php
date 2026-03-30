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
        string $endTime,
        bool $includesWeekend = false
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
                (c.carpark_capacity - COUNT(DISTINCT b.booking_id)) AS spaces_left,
                rp.min_price,
                rp.monthly_price
            FROM carparks c
            LEFT JOIN bookings b
                ON b.booking_carpark_id = c.carpark_id
                AND b.booking_start < :endTime
                AND b.booking_end   > :startTime
                AND b.booking_status != 'cancelled'
            LEFT JOIN (
                SELECT carpark_id,
                       MIN(CASE WHEN is_monthly = 0 THEN price END) AS min_price,
                       MIN(CASE WHEN is_monthly = 1 THEN price END) AS monthly_price
                FROM rates
                GROUP BY carpark_id
            ) rp ON rp.carpark_id = c.carpark_id
            WHERE (:includesWeekend = 0 OR c.weekend_available = 1)
            GROUP BY c.carpark_id
            HAVING distance <= :radius
            AND spaces_left > 0
            ORDER BY distance ASC
            ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':carpark_lat'      => $lat,
            ':carpark_lng'      => $lng,
            ':radius'           => $radiusKm,
            ':startTime'        => $startTime,
            ':endTime'          => $endTime,
            ':includesWeekend'  => $includesWeekend ? 1 : 0,
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
        int $ownerID,
        bool $isMonthly = true,
        string $spaceSize = 'medium',
        bool $requiresKey = false,
        bool $weekendAvailable = true,
        int $minBookingMinutes = 30,
        bool $isAffiliate = false,
        string $affiliateUrl = ''
    ) {
        try {
            $carparkType = $isAffiliate ? 'affiliate' : 'standard';

            $query = "
                INSERT INTO carparks (
                    carpark_name,
                    carpark_description,
                    carpark_address,
                    carpark_lat,
                    carpark_lng,
                    carpark_capacity,
                    carpark_features,
                    carpark_owner,
                    is_monthly,
                    space_size,
                    requires_key,
                    weekend_available,
                    min_booking_minutes,
                    carpark_type,
                    carpark_affiliate_url
                ) VALUES (
                    :name,
                    :description,
                    :address,
                    :lat,
                    :lng,
                    :capacity,
                    :features,
                    :owner,
                    :is_monthly,
                    :space_size,
                    :requires_key,
                    :weekend_available,
                    :min_booking_minutes,
                    :carpark_type,
                    :affiliate_url
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
            $stmt->bindValue(":is_monthly", $isMonthly ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":space_size", $spaceSize, PDO::PARAM_STR);
            $stmt->bindValue(":requires_key", $requiresKey ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":weekend_available", $weekendAvailable ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":min_booking_minutes", $minBookingMinutes, PDO::PARAM_INT);
            $stmt->bindValue(":carpark_type", $carparkType, PDO::PARAM_STR);
            $stmt->bindValue(":affiliate_url", $affiliateUrl, PDO::PARAM_STR);

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function upsertOwnerDetails(int $userId, string $phone, string $address)
    {
        try {
            $query = "
                INSERT INTO owner_details (user_id, phone_number, owner_address)
                VALUES (:user_id, :phone, :address)
                ON DUPLICATE KEY UPDATE
                    phone_number  = VALUES(phone_number),
                    owner_address = VALUES(owner_address)
            ";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $stmt->bindValue(":phone", $phone, PDO::PARAM_STR);
            $stmt->bindValue(":address", $address, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function insertCarparkPhoto(int $carparkId, string $photoPath)
    {
        try {
            $query = "INSERT INTO carpark_photos (carpark_id, photo_path) VALUES (:carpark_id, :photo_path)";
            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":carpark_id", $carparkId, PDO::PARAM_INT);
            $stmt->bindValue(":photo_path", $photoPath, PDO::PARAM_STR);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function getCarparkPhotos(int $carparkId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM carpark_photos WHERE carpark_id = :id ORDER BY photo_id ASC");
        $stmt->execute([':id' => $carparkId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function deleteCarparkPhoto(int $photoId, int $carparkId): bool
    {
        // carparkId guard prevents deleting photos belonging to other carparks
        $stmt = $this->db->prepare("DELETE FROM carpark_photos WHERE photo_id = :photo_id AND carpark_id = :carpark_id");
        $stmt->execute([':photo_id' => $photoId, ':carpark_id' => $carparkId]);
        return $stmt->rowCount() > 0;
    }

    public function getOwnerDetails(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM owner_details WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
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
        bool $isMonthly = true,
        string $spaceSize = 'medium',
        bool $requiresKey = false,
        bool $weekendAvailable = true,
        int $minBookingMinutes = 30
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
                    carpark_affiliate_url = :affiliate_url,
                    is_monthly = :is_monthly,
                    space_size = :space_size,
                    requires_key = :requires_key,
                    weekend_available = :weekend_available,
                    min_booking_minutes = :min_booking_minutes
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
            $stmt->bindValue(":is_monthly", $isMonthly ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":space_size", $spaceSize, PDO::PARAM_STR);
            $stmt->bindValue(":requires_key", $requiresKey ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":weekend_available", $weekendAvailable ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":min_booking_minutes", $minBookingMinutes, PDO::PARAM_INT);

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

    // Get all monthly carparks
    public function getAllMonthlyCarparks()
    {
        $query = "SELECT * FROM carparks WHERE is_monthly = 1 ORDER BY carpark_id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all non-monthly carparks
    public function getAllNonMonthlyCarparks()
    {
        $query = "SELECT * FROM carparks WHERE is_monthly = 0 ORDER BY carpark_id DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Check if a carpark is monthly
    public function isCarparkMonthly(int $carparkID)
    {
        $query = "SELECT is_monthly FROM carparks WHERE carpark_id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $carparkID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (bool)$result['is_monthly'] : false;
    }

}// class Carparks
