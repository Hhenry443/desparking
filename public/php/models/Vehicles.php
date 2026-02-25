<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Vehicles extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function saveVehicle($userId, $plate, $make, $model, $colour)
    {
        try {

            // Check if vehicle already exists for this user
            $check = $this->db->prepare("
                SELECT vehicle_id
                FROM vehicles
                WHERE user_id = :user_id
                AND registration_plate = :plate
                LIMIT 1
            ");

            $check->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $check->bindValue(":plate", $plate);
            $check->execute();

            $existing = $check->fetch();

            if ($existing) {

                // UPDATE
                $update = $this->db->prepare("
                    UPDATE vehicles
                    SET make = :make,
                        model = :model,
                        colour = :colour,
                        updated_at = NOW()
                    WHERE vehicle_id = :vehicle_id
                    AND user_id = :user_id
                ");

                $update->bindValue(":make", $make);
                $update->bindValue(":model", $model);
                $update->bindValue(":colour", $colour);
                $update->bindValue(":vehicle_id", $existing['vehicle_id'], PDO::PARAM_INT);
                $update->bindValue(":user_id", $userId, PDO::PARAM_INT);

                $update->execute();

                return [
                    "success" => true,
                    "action" => "updated"
                ];
            }

            // INSERT
            $insert = $this->db->prepare("
                INSERT INTO vehicles
                (user_id, registration_plate, make, model, colour)
                VALUES (:user_id, :plate, :make, :model, :colour)
            ");

            $insert->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $insert->bindValue(":plate", $plate);
            $insert->bindValue(":make", $make);
            $insert->bindValue(":model", $model);
            $insert->bindValue(":colour", $colour);

            $insert->execute();

            return [
                "success" => true,
                "action" => "inserted"
            ];

        } catch (PDOException $e) {
            return [
                "success" => false,
                "message" => $e->getMessage()
            ];
        }
    }

    public function removeVehicle($vehicleId, $userId)
    {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM vehicles
                WHERE vehicle_id = :vehicle_id
                AND user_id = :user_id
            ");

            $stmt->bindValue(":vehicle_id", $vehicleId, PDO::PARAM_INT);
            $stmt->bindValue(":user_id", $userId, PDO::PARAM_INT);

            $stmt->execute();

            return true;

        } catch (PDOException $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }
}