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

    protected function selectPendingCarparks()
    {
        $sql = "
            SELECT c.*, u.user_email,
                   od.phone_number AS owner_phone,
                   od.owner_address AS owner_address
            FROM carparks c
            LEFT JOIN users u ON u.user_id = c.carpark_owner
            LEFT JOIN owner_details od ON od.user_id = c.carpark_owner
            WHERE c.carpark_status = 'pending'
            ORDER BY c.carpark_id DESC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    protected function approveCarparkByID(int $carparkID): bool
    {
        $stmt = $this->db->prepare("UPDATE carparks SET carpark_status = 'approved' WHERE carpark_id = :id");
        $stmt->execute([':id' => $carparkID]);
        return $stmt->rowCount() > 0;
    }

    public function setPendingByID(int $carparkID): bool
    {
        $stmt = $this->db->prepare("UPDATE carparks SET carpark_status = 'pending' WHERE carpark_id = :id");
        $stmt->execute([':id' => $carparkID]);
        return $stmt->rowCount() > 0;
    }

    protected function selectAvailableCarparks(
        float $lat,
        float $lng,
        float $radiusKm,
        string $startTime,
        string $endTime,
        bool $includesWeekend = false,
        string $bookingType = 'all'
    ) {
        // -1 = no filter, 0 = hourly only, 1 = monthly only
        $filterMonthly = $bookingType === 'monthly' ? 1 : ($bookingType === 'hourly' ? 0 : -1);

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
                AND (b.booking_status IS NULL OR b.booking_status != 'cancelled')
                AND b.booking_start < :endTime AND b.booking_end > :startTime
                AND (:filterMonthly = -1 OR b.is_monthly = :filterMonthly2)
            LEFT JOIN (
                SELECT carpark_id,
                       MIN(CASE WHEN is_monthly = 0 THEN price END) AS min_price,
                       MIN(CASE WHEN is_monthly = 1 THEN price END) AS monthly_price
                FROM rates
                GROUP BY carpark_id
            ) rp ON rp.carpark_id = c.carpark_id
            WHERE c.carpark_status = 'approved'
            AND (:includesWeekend = 0 OR c.weekend_available = 1)
            AND (:filterMonthly = -1 OR c.is_monthly = :filterMonthly)
            AND (c.available_from IS NULL OR c.available_from <= DATE(:avail_start))
            AND NOT EXISTS (
                SELECT 1 FROM carpark_unavailable_dates cud
                WHERE cud.carpark_id = c.carpark_id
                AND cud.unavailable_date BETWEEN DATE(:unavail_start) AND DATE(:unavail_end)
            )
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
            ':filterMonthly'    => $filterMonthly,
            ':filterMonthly2'   => $filterMonthly,
            ':avail_start'      => $startTime,
            ':unavail_start'    => $startTime,
            ':unavail_end'      => $endTime,
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
        string $accessInstructions = '',
        bool $isMonthly = true,
        string $spaceSize = 'medium',
        bool $requiresKey = false,
        bool $weekendAvailable = true,
        int $minBookingMinutes = 30,
        bool $isAffiliate = false,
        string $affiliateUrl = '',
        string $spaceType = 'car',
        bool $isAllocated = false,
        ?string $availableFrom = null,
        ?string $timeRestrictions = null
    ) {
        try {
            $carparkType = $isAffiliate ? 'affiliate' : 'bookable';

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
                    access_instructions,
                    is_monthly,
                    space_size,
                    requires_key,
                    weekend_available,
                    min_booking_minutes,
                    carpark_type,
                    carpark_affiliate_url,
                    space_type,
                    is_allocated,
                    available_from,
                    time_restrictions,
                    carpark_status
                ) VALUES (
                    :name,
                    :description,
                    :address,
                    :lat,
                    :lng,
                    :capacity,
                    :features,
                    :owner,
                    :access_instructions,
                    :is_monthly,
                    :space_size,
                    :requires_key,
                    :weekend_available,
                    :min_booking_minutes,
                    :carpark_type,
                    :affiliate_url,
                    :space_type,
                    :is_allocated,
                    :available_from,
                    :time_restrictions,
                    'pending'
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
            $stmt->bindValue(":access_instructions", $accessInstructions, PDO::PARAM_STR);
            $stmt->bindValue(":is_monthly", $isMonthly ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":space_size", $spaceSize, PDO::PARAM_STR);
            $stmt->bindValue(":requires_key", $requiresKey ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":weekend_available", $weekendAvailable ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":min_booking_minutes", $minBookingMinutes, PDO::PARAM_INT);
            $stmt->bindValue(":carpark_type", $carparkType, PDO::PARAM_STR);
            $stmt->bindValue(":affiliate_url", $affiliateUrl, PDO::PARAM_STR);
            $stmt->bindValue(":space_type", $spaceType, PDO::PARAM_STR);
            $stmt->bindValue(":is_allocated", $isAllocated ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":available_from", $availableFrom, $availableFrom === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(":time_restrictions", $timeRestrictions, $timeRestrictions === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    public function replaceUnavailableDates(int $carparkId, array $dates): void
    {
        $this->db->prepare("DELETE FROM carpark_unavailable_dates WHERE carpark_id = :id")
                 ->execute([':id' => $carparkId]);

        if (empty($dates)) return;

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO carpark_unavailable_dates (carpark_id, unavailable_date) VALUES (:id, :date)"
        );
        foreach ($dates as $date) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                $stmt->execute([':id' => $carparkId, ':date' => $date]);
            }
        }
    }

    public function getUnavailableDates(int $carparkId): array
    {
        $stmt = $this->db->prepare(
            "SELECT unavailable_date FROM carpark_unavailable_dates WHERE carpark_id = :id ORDER BY unavailable_date ASC"
        );
        $stmt->execute([':id' => $carparkId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'unavailable_date');
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
        string $accessInstructions = '',
        bool $isMonthly = true,
        string $spaceSize = 'medium',
        bool $requiresKey = false,
        bool $weekendAvailable = true,
        int $minBookingMinutes = 30,
        string $spaceType = 'car',
        bool $isAllocated = false,
        ?string $availableFrom = null,
        ?string $timeRestrictions = null
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
                    access_instructions = :access_instructions,
                    is_monthly = :is_monthly,
                    space_size = :space_size,
                    requires_key = :requires_key,
                    weekend_available = :weekend_available,
                    min_booking_minutes = :min_booking_minutes,
                    space_type = :space_type,
                    is_allocated = :is_allocated,
                    available_from = :available_from,
                    time_restrictions = :time_restrictions,
                    carpark_status = 'pending'
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
            $stmt->bindValue(":access_instructions", $accessInstructions, PDO::PARAM_STR);
            $stmt->bindValue(":is_monthly", $isMonthly ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":space_size", $spaceSize, PDO::PARAM_STR);
            $stmt->bindValue(":requires_key", $requiresKey ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":weekend_available", $weekendAvailable ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":min_booking_minutes", $minBookingMinutes, PDO::PARAM_INT);
            $stmt->bindValue(":space_type", $spaceType, PDO::PARAM_STR);
            $stmt->bindValue(":is_allocated", $isAllocated ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":available_from", $availableFrom, $availableFrom === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(":time_restrictions", $timeRestrictions, $timeRestrictions === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

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

    // --- Pending changes (edit submissions from approved carparks) ---

    public function savePendingChanges(int $carparkId, array $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO carpark_pending_changes (carpark_id, proposed_data)
            VALUES (:id, :data)
            ON DUPLICATE KEY UPDATE proposed_data = VALUES(proposed_data), submitted_at = NOW()
        ");
        $stmt->execute([':id' => $carparkId, ':data' => json_encode($data)]);
    }

    public function getPendingChanges(int $carparkId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM carpark_pending_changes WHERE carpark_id = :id");
        $stmt->execute([':id' => $carparkId]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['proposed_data'] = json_decode($row['proposed_data'], true);
        return $row;
    }

    public function clearPendingChanges(int $carparkId): void
    {
        $this->db->prepare("DELETE FROM carpark_pending_changes WHERE carpark_id = :id")
            ->execute([':id' => $carparkId]);
    }

    public function applyPendingChanges(int $carparkId): bool
    {
        $pending = $this->getPendingChanges($carparkId);
        if (!$pending) return false;
        $d = $pending['proposed_data'];

        // Apply field changes without touching carpark_status
        $stmt = $this->db->prepare("
            UPDATE carparks SET
                carpark_name          = :name,
                carpark_description   = :description,
                carpark_address       = :address,
                carpark_capacity      = :capacity,
                carpark_lat           = :lat,
                carpark_lng           = :lng,
                carpark_features      = :features,
                carpark_affiliate_url = :affiliate_url,
                access_instructions   = :access_instructions,
                is_monthly            = :is_monthly,
                space_size            = :space_size,
                requires_key          = :requires_key,
                weekend_available     = :weekend_available,
                min_booking_minutes   = :min_booking_minutes,
                space_type            = :space_type,
                is_allocated          = :is_allocated,
                available_from        = :available_from,
                time_restrictions     = :time_restrictions
            WHERE carpark_id = :id
        ");
        $stmt->execute([
            ':id'                  => $carparkId,
            ':name'                => $d['carpark_name'],
            ':description'         => $d['carpark_description'] ?? '',
            ':address'             => $d['carpark_address'],
            ':capacity'            => (int)$d['carpark_capacity'],
            ':lat'                 => (float)$d['carpark_lat'],
            ':lng'                 => (float)$d['carpark_lng'],
            ':features'            => $d['carpark_features'] ?? '',
            ':affiliate_url'       => $d['carpark_affiliate_url'] ?? '',
            ':access_instructions' => $d['access_instructions'] ?? '',
            ':is_monthly'          => (int)(bool)$d['is_monthly'],
            ':space_size'          => $d['space_size'] ?? 'medium',
            ':requires_key'        => (int)(bool)$d['requires_key'],
            ':weekend_available'   => (int)(bool)$d['weekend_available'],
            ':min_booking_minutes' => (int)$d['min_booking_minutes'],
            ':space_type'          => $d['space_type'] ?? 'car',
            ':is_allocated'        => (int)(bool)$d['is_allocated'],
            ':available_from'      => $d['available_from'] ?? null,
            ':time_restrictions'   => $d['time_restrictions'] ?? null,
        ]);

        if (isset($d['unavailable_dates']) && is_array($d['unavailable_dates'])) {
            $this->replaceUnavailableDates($carparkId, $d['unavailable_dates']);
        }

        return true;
    }

    public function getCarparkIdsWithPendingChanges(): array
    {
        $stmt = $this->db->prepare("SELECT carpark_id FROM carpark_pending_changes");
        $stmt->execute();
        return array_flip(array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'carpark_id'));
    }

}// class Carparks
