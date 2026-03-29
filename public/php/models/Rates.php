<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Rates extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    // Fetch all rates for a specific carpark, sorted by duration descending
    protected function selectRatesByCarpark($carparkID)
    {
        $sql = "SELECT * FROM rates 
                WHERE carpark_id = :carparkID AND is_monthly = 0
                ORDER BY duration_minutes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':carparkID' => $carparkID]);
        return $stmt->fetchAll() ?: [];
    }

    // Get the monthly rate for a specific carpark
    public function getMonthlyRateByCarpark($carparkID)
    {
        $sql = "SELECT * FROM rates 
                WHERE carpark_id = :carparkID 
                AND is_monthly = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':carparkID' => $carparkID]);
        return $stmt->fetch() ?: null;
    }

    // Insert a new rate
    public function insertRate(int $carparkID, int $durationMinutes, int $priceCents)
    {
        try {
            $query = "
                INSERT INTO rates
                (carpark_id, duration_minutes, price, is_monthly)
                VALUES (:carparkID, :duration, :price, 0)
            ";

            $stmt = $this->db->prepare($query);

            $stmt->bindValue(":carparkID", $carparkID, PDO::PARAM_INT);
            $stmt->bindValue(":duration", $durationMinutes, PDO::PARAM_INT);
            $stmt->bindValue(":price", $priceCents, PDO::PARAM_INT);

            $stmt->execute();

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    // Insert a monthly rate
    public function insertMonthlyRate(int $carparkID, float $amount)
    {
        // Check if a monthly rate already exists
        $check = $this->db->prepare(
            "SELECT rate_id FROM rates WHERE carpark_id = :carpark_id LIMIT 1"
        );
        $check->bindParam(':carpark_id', $carparkID, PDO::PARAM_INT);
        $check->execute();

        $existing = $check->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing monthly rate
            $stmt = $this->db->prepare(
                "UPDATE rates
                SET price = :price
                WHERE carpark_id = :carpark_id"
            );
        } else {
            // Insert new monthly rate
            $stmt = $this->db->prepare(
                "INSERT INTO rates (carpark_id, price, is_monthly)
                VALUES (:carpark_id, :price, 1)"
            );
        }

        $stmt->bindParam(':carpark_id', $carparkID, PDO::PARAM_INT);
        $stmt->bindParam(':price', $amount, PDO::PARAM_INT);

        return $stmt->execute();
    }

    // Delete a rate
    public function removeRate(int $rateID)
    {
        try {
            $query = "DELETE FROM rates WHERE rate_id = :rateID";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(":rateID", $rateID, PDO::PARAM_INT);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}
