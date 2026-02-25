<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';

class WriteCarparks extends Carparks
{
    private PDO $db;

    public function insertCarparkWithRates()
    {
        // Start session to get user ID
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        // Collect POST data
        $carparkName = $_POST['carpark_name'] ?? null;
        $carparkDescription = $_POST['carpark_description'] ?? '';
        $carparkAddress = $_POST['carpark_address'] ?? null;
        $carparkLat = $_POST['carpark_lat'] ?? null;
        $carparkLng = $_POST['carpark_lng'] ?? null;
        $carparkCapacity = $_POST['carpark_capacity'] ?? null;
        $carparkFeatures = $_POST['carpark_features'] ?? '';
        $ownerID = $_SESSION['user_id'];

        // Get rates arrays
        $rateDurations = $_POST['rate_durations'] ?? [];
        $ratePrices = $_POST['rate_prices'] ?? [];

        // Validate required fields
        if (!$carparkName || !$carparkAddress || !$carparkLat || !$carparkLng || !$carparkCapacity) {
            $errorMessage = "Please fill in all required fields.";
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Insert the carpark (note: carpark_price is no longer used, but keep for compatibility)
        $carparkID = $this->insertCarpark(
            $carparkName,
            $carparkDescription,
            $carparkAddress,
            (float)$carparkLat,
            (float)$carparkLng,
            (int)$carparkCapacity,
            $carparkFeatures,
            $ownerID
        );

        // Check if insert was successful
        if (is_array($carparkID) && !$carparkID['success']) {
            $errorMessage = "Database error: " . $carparkID['message'];
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Insert rates if provided
        if (!empty($rateDurations) && !empty($ratePrices)) {
            $ratesModel = new Rates();
            
            for ($i = 0; $i < count($rateDurations); $i++) {
                $duration = $rateDurations[$i] ?? null;
                $price = $ratePrices[$i] ?? null;
                
                // Skip empty rows
                if (empty($duration) || empty($price)) {
                    continue;
                }
                
                // Convert price from GBP to cents
                $priceCents = round((float)$price * 100);
                
                // Insert rate
                $ratesModel->insertRate(
                    (int)$carparkID,
                    (int)$duration,
                    $priceCents
                );
            }
        }

        // Redirect to the new carpark page with success
        header("Location: /carpark.php?id=" . $carparkID . "&success=created");
        exit();
    }

    public function updateCarparkDetails()
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
        $carparkName = $_POST['carpark_name'] ?? null;
        $carparkDescription = $_POST['carpark_description'] ?? '';
        $carparkAddress = $_POST['carpark_address'] ?? null;
        $carparkCapacity = $_POST['carpark_capacity'] ?? null;
        $carparkLat = $_POST['carpark_lat'] ?? null;
        $carparkLng = $_POST['carpark_lng'] ?? null;
        $carparkFeaturesArray = $_POST['carpark_features'] ?? [];

        if (!is_array($carparkFeaturesArray)) {
            $carparkFeaturesArray = [];
        }

        $allowedFeatures = [
            "CCTV",
            "24/7 Access",
            "EV Charging",
            "Covered Parking",
            "Disabled Access",
            "Security Gate",
            "Lighting",
            "Permit Required",
            "Staffed",
            "Motorcycle Spaces"
        ];

        // Keep only valid features
        $carparkFeaturesArray = array_intersect($carparkFeaturesArray, $allowedFeatures);

        // Convert to string for DB storage
        $carparkFeatures = implode(',', $carparkFeaturesArray);
        $carparkAffiliateUrl = $_POST['carpark_affiliate_url'] ?? '';

        // Validate required fields
        if (!$carparkID || !$carparkName || !$carparkAddress || !$carparkCapacity || 
            !$carparkLat || !$carparkLng) {
            
            $errorMessage = "Please fill in all required fields.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Verify ownership (or admin override)
        $existingCarpark = $this->selectCarparkByID((int)$carparkID);
        
        if (!$existingCarpark) {
            header("Location: /");
            exit();
        }

        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

        if (!$isAdmin && $existingCarpark['carpark_owner'] != $_SESSION['user_id']) {
            $errorMessage = "You do not have permission to edit this car park.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Update the carpark
        $result = $this->updateCarpark(
            (int)$carparkID,
            $carparkName,
            $carparkDescription,
            $carparkAddress,
            (int)$carparkCapacity,
            (float)$carparkLat,
            (float)$carparkLng,
            $carparkFeatures,
            $carparkAffiliateUrl,
        );

        // Check if update was successful
        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Redirect back to the carpark page with success message
        header("Location: /carpark.php?id=" . $carparkID . "&success=1");
        exit();
    }

    public function deleteCarparkByID()
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

        if (!$carparkID) {
            header("Location: /");
            exit();
        }

        // Verify ownership (or admin override)
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById((int)$carparkID);
        
        if (!$carpark) {
            header("Location: /");
            exit();
        }

        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

        if (!$isAdmin && $carpark['carpark_owner'] != $_SESSION['user_id']) {
            $errorMessage = "You do not have permission to edit this car park.";
            header("Location: /admin.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Delete the rate
        $result = $this->deleteCarpark((int)$carparkID);

        // Check if delete was successful
        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /admin.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Redirect back with success
        header("Location: /admin.php?success=carpark_deleted");
        exit();
    }

}
