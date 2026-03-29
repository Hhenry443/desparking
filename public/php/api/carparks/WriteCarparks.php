<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Rates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

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

        // New fields
        $allowedSizes = ['small', 'medium', 'large'];
        $spaceSize = in_array($_POST['space_size'] ?? '', $allowedSizes) ? $_POST['space_size'] : 'medium';
        $requiresKey = isset($_POST['requires_key']) && $_POST['requires_key'] === 'on';
        $weekendAvailable = isset($_POST['weekend_available']) && $_POST['weekend_available'] === 'on';
        $minBookingMinutes = max(1, (int)($_POST['min_booking_minutes'] ?? 30));

        // Owner contact details
        $ownerPhone = trim($_POST['owner_phone'] ?? '');
        $ownerAddress = trim($_POST['owner_address'] ?? '');

        $carparkFeaturesArray = $_POST['features'] ?? [];

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

        // Keep only valid values
        $carparkFeaturesArray = array_intersect($carparkFeaturesArray, $allowedFeatures);

        // Convert to string for DB
        $carparkFeatures = implode(',', $carparkFeaturesArray);

        $ownerID = $_SESSION['user_id'];

        // Get rates arrays
        $rateDurations = $_POST['rate_durations'] ?? [];
        $ratePrices = $_POST['rate_prices'] ?? [];
        $monthlyFlag = $_POST['monthly-toggle'] ?? null;
        $monthlyAmount = $_POST['monthly_fee'] ?? null;

        // Validate required fields
        if (!$carparkName || !$carparkAddress || !$carparkLat || !$carparkLng || !$carparkCapacity) {
            $errorMessage = "Please fill in all required fields.";
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Save owner contact details
        if ($ownerPhone !== '' || $ownerAddress !== '') {
            $this->upsertOwnerDetails($ownerID, $ownerPhone, $ownerAddress);
        }

        $carparkID = $this->insertCarpark(
            $carparkName,
            $carparkDescription,
            $carparkAddress,
            (float)$carparkLat,
            (float)$carparkLng,
            (int)$carparkCapacity,
            $carparkFeatures,
            $ownerID,
            $monthlyFlag === 'on',
            $spaceSize,
            $requiresKey,
            $weekendAvailable,
            $minBookingMinutes
        );

        // Check if insert was successful
        if (is_array($carparkID) && !$carparkID['success']) {
            $errorMessage = "Database error: " . $carparkID['message'];
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Handle photo uploads
        if (!empty($_FILES['carpark_photos']['name'][0])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/carparks/' . $carparkID . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $files = $_FILES['carpark_photos'];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $files['tmp_name'][$i]);
                finfo_close($finfo);

                if (!in_array($mime, $allowedMimeTypes)) {
                    continue;
                }

                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
                $dest = $uploadDir . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                    $this->insertCarparkPhoto((int)$carparkID, '/uploads/carparks/' . $carparkID . '/' . $filename);
                }
            }
        }

        // Handle monthly rate vs regular rates
        if ($monthlyFlag === 'on' && !empty($monthlyAmount)) {
            // Insert monthly rate using the new function
            $ratesModel = new Rates();
            $ratesModel->insertMonthlyRate(
                (int)$carparkID,
                (float)$monthlyAmount
            );
        } else {
            // Insert regular rates if provided
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
        }

        // Redirect to the new carpark page with success
        header("Location: /carpark.php?id=" . $carparkID . "&success=created");
        exit();
    }

    public function updateCarparkDetails()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        $carparkID = $_POST['carpark_id'] ?? null;
        $carparkName = $_POST['carpark_name'] ?? null;
        $carparkDescription = $_POST['carpark_description'] ?? '';
        $carparkAddress = $_POST['carpark_address'] ?? null;
        $carparkCapacity = $_POST['carpark_capacity'] ?? null;
        $carparkLat = $_POST['carpark_lat'] ?? null;
        $carparkLng = $_POST['carpark_lng'] ?? null;
        $carparkAffiliateUrl = $_POST['carpark_affiliate_url'] ?? '';
        $monthlyFlag = $_POST['monthly_flag'] ?? null;

        // New fields
        $allowedSizes = ['small', 'medium', 'large'];
        $spaceSize = in_array($_POST['space_size'] ?? '', $allowedSizes) ? $_POST['space_size'] : 'medium';
        $requiresKey = isset($_POST['requires_key']) && $_POST['requires_key'] === 'on';
        $weekendAvailable = isset($_POST['weekend_available']) && $_POST['weekend_available'] === 'on';
        $minBookingMinutes = max(1, (int)($_POST['min_booking_minutes'] ?? 30));

        // Owner contact details
        $ownerPhone = trim($_POST['owner_phone'] ?? '');
        $ownerAddress = trim($_POST['owner_address'] ?? '');

        $carparkFeaturesArray = $_POST['features'] ?? [];
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

        $carparkFeaturesArray = array_intersect($carparkFeaturesArray, $allowedFeatures);
        $carparkFeatures = implode(',', $carparkFeaturesArray);

        if (
            !$carparkID || !$carparkName || !$carparkAddress || !$carparkCapacity ||
            !$carparkLat || !$carparkLng
        ) {
            $errorMessage = "Please fill in all required fields.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        $ownerID = $_SESSION["user_id"];

        // Save owner contact details
        if ($ownerPhone !== '' || $ownerAddress !== '') {
            $this->upsertOwnerDetails($ownerID, $ownerPhone, $ownerAddress);
        }

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
            $monthlyFlag === 'on',
            $spaceSize,
            $requiresKey,
            $weekendAvailable,
            $minBookingMinutes
        );

        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        // Handle photo uploads
        if (!empty($_FILES['carpark_photos']['name'][0])) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/carparks/' . $carparkID . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $files = $_FILES['carpark_photos'];

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                    continue;
                }
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $files['tmp_name'][$i]);
                finfo_close($finfo);

                if (!in_array($mime, $allowedMimeTypes)) {
                    continue;
                }

                $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
                $dest = $uploadDir . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                    $this->insertCarparkPhoto((int)$carparkID, '/uploads/carparks/' . $carparkID . '/' . $filename);
                }
            }
        }

        header("Location: /carpark.php?id=" . $carparkID . "&success=1");
        exit();
    }

    public function deletePhoto()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header("Location: /login.php");
            exit();
        }

        $photoId = (int)($_POST['photo_id'] ?? 0);
        $carparkId = (int)($_POST['carpark_id'] ?? 0);

        if (!$photoId || !$carparkId) {
            header("Location: /");
            exit();
        }

        // Verify the user owns this carpark
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById($carparkId);

        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
        if (!$carpark || (!$isAdmin && $carpark['carpark_owner'] != $_SESSION['user_id'])) {
            header("Location: /");
            exit();
        }

        $this->deleteCarparkPhoto($photoId, $carparkId);

        header("Location: /carpark.php?id=" . $carparkId . "&success=1");
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
