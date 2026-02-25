<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Vehicles.php';

class ReadVehicles extends Vehicles
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    /**
     * Get all vehicles belonging to a specific user
     */
    public function getVehiclesByUserId(int $userId)
    {
        $sql = "
            SELECT vehicle_id,
                   registration_plate,
                   make,
                   model,
                   colour,
                   created_at
            FROM vehicles
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get single vehicle (ownership enforced)
     */
    public function getVehicleById(int $vehicleId, int $userId)
    {
        $sql = "
            SELECT *
            FROM vehicles
            WHERE vehicle_id = :vehicle_id
            AND user_id = :user_id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(":vehicle_id", $vehicleId, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch() ?: null;
    }
}