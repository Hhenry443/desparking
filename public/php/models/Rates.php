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
        $sql = "SELECT * FROM carpark_rates 
                WHERE carpark_id = :carparkID 
                ORDER BY duration_minutes DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':carparkID' => $carparkID]);
        return $stmt->fetchAll() ?: [];
    }
}
