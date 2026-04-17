<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Rates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';

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

        $allowedSpaceTypes = ['car', 'garage', 'motorbike', 'multiple'];
        $spaceType = in_array($_POST['space_type'] ?? '', $allowedSpaceTypes) ? $_POST['space_type'] : 'car';
        $isAllocated = isset($_POST['is_allocated']) && $_POST['is_allocated'] === 'on';
        $availableImmediately = isset($_POST['available_immediately']) && $_POST['available_immediately'] === 'on';
        $availableFrom = (!$availableImmediately && !empty($_POST['available_from']))
            ? $_POST['available_from'] : null;
        $timeRestrictions = trim($_POST['time_restrictions'] ?? '') ?: null;

        $unavailableDates = $_POST['unavailable_dates'] ?? [];
        if (!is_array($unavailableDates)) $unavailableDates = [];

        // Owner contact details (stored per carpark)
        $ownerName = trim($_POST['owner_name'] ?? '');
        $ownerPhone = trim($_POST['owner_phone'] ?? '');
        $ownerAddress = trim($_POST['owner_address'] ?? '');

        $accessInstructions = trim($_POST['access_instructions'] ?? '');

        $carparkFeaturesArray = $_POST['features'] ?? [];

        if (!is_array($carparkFeaturesArray)) {
            $carparkFeaturesArray = [];
        }

        $allowedFeatures = [
            "CCTV",
            "Motorbike Ground Anchor",
            "On-site Staff",
            "Parking Post (bollards)",
            "Security Alarm",
            "Security Gates",
            "Security Lighting",
            "Smoke Detector",
            "Electric Vehicle Car Charging",
            "Fire Alarm",
            "Lift Access",
            "Private Entrance",
            "Undercover",
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

        // Affiliate fields (admin only)
        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
        $isAffiliate = $isAdmin && isset($_POST['is_affiliate']) && $_POST['is_affiliate'] === 'on';
        $affiliateUrl = $isAffiliate ? trim($_POST['carpark_affiliate_url'] ?? '') : '';

        // Validate required fields
        if (!$carparkName || !$carparkAddress || !$carparkLat || !$carparkLng || !$carparkCapacity || $accessInstructions === '' || $ownerName === '') {
            $errorMessage = "Please fill in all required fields.";
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        if ($isAffiliate && $affiliateUrl === '') {
            $errorMessage = "An affiliate URL is required for affiliate listings.";
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
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
            $accessInstructions,
            $monthlyFlag === 'on',
            $spaceSize,
            $requiresKey,
            $weekendAvailable,
            $minBookingMinutes,
            $isAffiliate,
            $affiliateUrl,
            $spaceType,
            $isAllocated,
            $availableFrom,
            $timeRestrictions,
            $ownerName,
            $ownerPhone,
            $ownerAddress
        );

        // Check if insert was successful
        if (is_array($carparkID) && !$carparkID['success']) {
            $errorMessage = "Database error: " . $carparkID['message'];
            header("Location: /create.php?error=" . urlencode($errorMessage));
            exit();
        }

        // Save unavailable dates
        if (!empty($unavailableDates)) {
            $this->replaceUnavailableDates((int)$carparkID, $unavailableDates);
        }

        try {
            (new Notifier(Dbh::getConnection()))->carparkPendingApproval((int)$carparkID);
        } catch (Throwable $e) {
            error_log("Notification failed [carparkPendingApproval]: " . $e->getMessage());
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
                (int)round((float)$monthlyAmount * 100)
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

        // Redirect to confirmation — carpark is pending approval
        header("Location: /create.php?submitted=1");
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
        // monthly_flag is only sent from forms that explicitly include it (e.g. create.php).
        // When not present, preserve the carpark's existing is_monthly value to avoid silent resets.
        $monthlyFlagRaw = $_POST['monthly_flag'] ?? null;

        // New fields
        $allowedSizes = ['small', 'medium', 'large'];
        $spaceSize = in_array($_POST['space_size'] ?? '', $allowedSizes) ? $_POST['space_size'] : 'medium';
        $requiresKey = isset($_POST['requires_key']) && $_POST['requires_key'] === 'on';
        $weekendAvailable = isset($_POST['weekend_available']) && $_POST['weekend_available'] === 'on';
        $minBookingMinutes = max(1, (int)($_POST['min_booking_minutes'] ?? 30));

        $allowedSpaceTypes = ['car', 'garage', 'motorbike', 'multiple'];
        $spaceType = in_array($_POST['space_type'] ?? '', $allowedSpaceTypes) ? $_POST['space_type'] : 'car';
        $isAllocated = isset($_POST['is_allocated']) && $_POST['is_allocated'] === 'on';
        $availableImmediately = isset($_POST['available_immediately']) && $_POST['available_immediately'] === 'on';
        $availableFrom = (!$availableImmediately && !empty($_POST['available_from']))
            ? $_POST['available_from'] : null;
        $timeRestrictions = trim($_POST['time_restrictions'] ?? '') ?: null;

        $unavailableDates = $_POST['unavailable_dates'] ?? [];
        if (!is_array($unavailableDates)) $unavailableDates = [];

        // Owner contact details (stored per carpark)
        $ownerName = trim($_POST['owner_name'] ?? '');
        $ownerPhone = trim($_POST['owner_phone'] ?? '');
        $ownerAddress = trim($_POST['owner_address'] ?? '');

        $accessInstructions = trim($_POST['access_instructions'] ?? '');

        $carparkFeaturesArray = $_POST['features'] ?? [];
        if (!is_array($carparkFeaturesArray)) {
            $carparkFeaturesArray = [];
        }

        $allowedFeatures = [
            "CCTV",
            "Motorbike Ground Anchor",
            "On-site Staff",
            "Parking Post (bollards)",
            "Security Alarm",
            "Security Gates",
            "Security Lighting",
            "Smoke Detector",
            "Electric Vehicle Car Charging",
            "Fire Alarm",
            "Lift Access",
            "Private Entrance",
            "Undercover",
        ];

        $carparkFeaturesArray = array_intersect($carparkFeaturesArray, $allowedFeatures);
        $carparkFeatures = implode(',', $carparkFeaturesArray);

        if (!$carparkID || !ctype_digit((string)$carparkID)) {
            header("Location: /account.php");
            exit();
        }

        if (!$carparkName || !$carparkAddress || !$carparkCapacity || !$carparkLat || !$carparkLng || $accessInstructions === '' || $ownerName === '') {
            $errorMessage = "Please fill in all required fields.";
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        $ownerID = $_SESSION["user_id"];

        // Handle photo uploads immediately (admin can see them in review)
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

        // Load the current carpark to check its live status
        $existing = $this->selectCarparkByID((int)$carparkID);

        // Resolve is_monthly: use the explicitly-submitted value when present,
        // otherwise fall back to the carpark's current value so edits never
        // accidentally flip a monthly carpark to non-monthly (or vice versa).
        $isMonthly = ($monthlyFlagRaw !== null)
            ? ($monthlyFlagRaw === 'on')
            : (bool)($existing['is_monthly'] ?? false);

        if ($existing && $existing['carpark_status'] === 'approved') {
            // Snapshot the current live rates so they can be restored on rejection
            include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
            $ReadRates = new ReadRates();
            $liveRates        = $ReadRates->getCarparkRates((int)$carparkID);
            $liveMonthlyRate  = $ReadRates->getCarparkMonthlyRates((int)$carparkID);

            // Carpark is live — stage changes for admin review, don't touch live data
            $this->savePendingChanges((int)$carparkID, [
                'carpark_name'          => $carparkName,
                'carpark_description'   => $carparkDescription,
                'carpark_address'       => $carparkAddress,
                'carpark_capacity'      => $carparkCapacity,
                'carpark_lat'           => $carparkLat,
                'carpark_lng'           => $carparkLng,
                'carpark_affiliate_url' => $carparkAffiliateUrl,
                'is_monthly'            => $isMonthly,
                'space_size'            => $spaceSize,
                'requires_key'          => $requiresKey,
                'weekend_available'     => $weekendAvailable,
                'min_booking_minutes'   => $minBookingMinutes,
                'space_type'            => $spaceType,
                'is_allocated'          => $isAllocated,
                'available_from'        => $availableFrom,
                'time_restrictions'     => $timeRestrictions,
                'carpark_features'      => $carparkFeatures,
                'access_instructions'   => $accessInstructions,
                'owner_name'            => $ownerName,
                'owner_phone'           => $ownerPhone,
                'owner_address'         => $ownerAddress,
                'unavailable_dates'     => $unavailableDates,
                // Snapshot of live rates for rollback on rejection
                '_live_rates'           => $liveRates,
                '_live_monthly_rate'    => $liveMonthlyRate,
            ]);
            $this->setPendingByID((int)$carparkID);

            try {
                (new Notifier(Dbh::getConnection()))->carparkPendingApproval((int)$carparkID);
            } catch (Throwable $e) {
                error_log("Notification failed [carparkPendingApproval edit]: " . $e->getMessage());
            }

            $adminParam = (isset($_POST['admin_override']) && $_POST['admin_override'] === '1' && $_SESSION['is_admin'] === true) ? '&admin=1' : '';
            header("Location: /carpark.php?id=" . $carparkID . "&success=1" . $adminParam);
            exit();
        }

        // Carpark is new/already-pending — update directly
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
            $accessInstructions,
            $isMonthly,
            $spaceSize,
            $requiresKey,
            $weekendAvailable,
            $minBookingMinutes,
            $spaceType,
            $isAllocated,
            $availableFrom,
            $timeRestrictions,
            $ownerName,
            $ownerPhone,
            $ownerAddress
        );

        $this->replaceUnavailableDates((int)$carparkID, $unavailableDates);

        if (is_array($result) && !$result['success']) {
            $errorMessage = "Database error: " . $result['message'];
            header("Location: /carpark.php?id=" . $carparkID . "&error=" . urlencode($errorMessage));
            exit();
        }

        $adminParam = (isset($_POST['admin_override']) && $_POST['admin_override'] === '1' && $_SESSION['is_admin'] === true) ? '&admin=1' : '';
        header("Location: /carpark.php?id=" . $carparkID . "&success=1" . $adminParam);
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
        $this->setPendingByID($carparkId);

        header("Location: /carpark.php?id=" . $carparkId . "&success=1");
        exit();
    }

    public function handleApproveCarpark()
    {
        if (session_status() == PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
            header("Location: /");
            exit();
        }

        $carparkID = (int)($_POST['carpark_id'] ?? 0);
        if (!$carparkID) {
            header("Location: /admin.php?error=" . urlencode("Invalid carpark ID."));
            exit();
        }

        // Apply any staged changes from an edit submission before approving
        if ($this->getPendingChanges($carparkID)) {
            $this->applyPendingChanges($carparkID);
            $this->clearPendingChanges($carparkID);
        }

        $this->approveCarparkByID($carparkID);

        try {
            (new Notifier(Dbh::getConnection()))->carparkApproved($carparkID);
        } catch (Throwable $e) {
            error_log("Notification failed [carparkApproved]: " . $e->getMessage());
        }

        header("Location: /admin.php?success=approved");
        exit();
    }

    public function handleRejectCarparkChanges()
    {
        if (session_status() == PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
            header("Location: /");
            exit();
        }

        $carparkID = (int)($_POST['carpark_id'] ?? 0);
        if (!$carparkID) {
            header("Location: /admin.php?error=" . urlencode("Invalid carpark ID."));
            exit();
        }

        // Restore live rate snapshot so any rate changes made while pending are reverted
        $pending = $this->getPendingChanges($carparkID);
        if ($pending) {
            $d = $pending['proposed_data'];
            if (isset($d['_live_rates']) || isset($d['_live_monthly_rate'])) {
                include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Rates.php';
                $ratesModel = new Rates();

                // Wipe all current rates for this carpark
                $db = \Dbh::getConnection();
                $db->prepare("DELETE FROM rates WHERE carpark_id = :id")->execute([':id' => $carparkID]);

                // Restore live regular rates
                foreach ($d['_live_rates'] ?? [] as $r) {
                    $ratesModel->insertRate((int)$carparkID, (int)$r['duration_minutes'], (int)$r['price']);
                }

                // Restore live monthly rate
                if (!empty($d['_live_monthly_rate'])) {
                    $ratesModel->insertMonthlyRate((int)$carparkID, (int)$d['_live_monthly_rate']['price']);
                }
            }
        }

        // Discard pending changes and reinstate the approved live listing
        $this->clearPendingChanges($carparkID);
        $this->approveCarparkByID($carparkID);

        header("Location: /admin.php?rejected_changes=1");
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
