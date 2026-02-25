<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Vehicles.php';

class WriteVehicles extends Vehicles
{
    public function addVehicle()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        $userId = $_SESSION['user_id'];

        // Collect POST data
        $registrationPlate = trim($_POST['registration_plate'] ?? '');
        $make = trim($_POST['make'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $colour = trim($_POST['colour'] ?? '');

        // Basic validation
        if (!$registrationPlate || !$make || !$model || !$colour) {
            $errorMessage = "Please fill in all vehicle fields.";
            header("Location: /account.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Normalise plate (optional but recommended)
        $registrationPlate = strtoupper(str_replace(' ', '', $registrationPlate));

        // Insert vehicle
        $result = $this->saveVehicle(
            (int)$userId,
            $registrationPlate,
            $make,
            $model,
            $colour
        );

        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /account.php?error=" . urlencode($errorMessage));
            exit();
        }

        header("Location: /account.php?success=vehicle_added");
        exit();
    }

    public function deleteVehicle()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        $userId = $_SESSION['user_id'];
        $vehicleId = $_POST['vehicle_id'] ?? null;

        if (!$vehicleId) {
            header("Location: /account.php");
            exit();
        }

        // Ensure user can only delete their own vehicle
        $result = $this->removeVehicle(
            (int)$vehicleId,
            (int)$userId
        );

        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /account.php?error=" . urlencode($errorMessage));
            exit();
        }

        header("Location: /account.php?success=vehicle_deleted");
        exit();
    }
}