<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Rates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

class WriteRates extends Rates
{
    public function addRate()
    {
        // Start session to verify ownership
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        // Collect POST data
        $carparkID = $_POST['carpark_id'] ?? null;
        $durationMinutes = $_POST['duration_minutes'] ?? null;
        $priceGBP = $_POST['price'] ?? null;

        // Validate required fields
        if (!$carparkID || !$durationMinutes || $priceGBP === null) {
            $errorMessage = "Please fill in all fields.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Verify ownership
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById((int)$carparkID);
        
        if (!$carpark) {
            header("Location: /");
            exit();
        }

        if ($carpark['carpark_owner'] != $_SESSION['user_id']) {
            $errorMessage = "You do not have permission to edit this car park.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Convert price from GBP to pennies
        $pricePennies = round((float)$priceGBP * 100);

        // Insert the rate
        $result = $this->insertRate(
            (int)$carparkID,
            (int)$durationMinutes,
            $pricePennies
        );

        // Check if insert was successful
        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Redirect back with success
        header("Location: /carpark.php?id=" . $carparkID . "&success=rate_added");
        exit();
    }

    public function deleteRate()
    {
        // Start session to verify ownership
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        // Collect POST data
        $rateID = $_POST['rate_id'] ?? null;
        $carparkID = $_POST['carpark_id'] ?? null;

        if (!$rateID || !$carparkID) {
            header("Location: /");
            exit();
        }

        // Verify ownership of the carpark
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById((int)$carparkID);
        
        if (!$carpark) {
            header("Location: /");
            exit();
        }

        if ($carpark['carpark_owner'] != $_SESSION['user_id']) {
            $errorMessage = "You do not have permission to edit this car park.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Delete the rate
        $result = $this->removeRate((int)$rateID);

        // Check if delete was successful
        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Redirect back with success
        header("Location: /carpark.php?id=" . $carparkID . "&success=rate_deleted");
        exit();
    }
}