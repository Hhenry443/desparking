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
                WHERE carpark_id = :carparkID 
                ORDER BY duration_minutes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':carparkID' => $carparkID]);
        return $stmt->fetchAll() ?: [];
    }

    // Insert a new rate
    public function insertRate(int $carparkID, int $durationMinutes, int $priceCents)
    {
        try {
            $query = "
                INSERT INTO rates 
                (carpark_id, duration_minutes, price) 
                VALUES (:carparkID, :duration, :price)
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
