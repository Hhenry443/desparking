<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class WriteBookings extends Bookings
{
    private PDO $db;

    public function writeBooking()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit;
    }

    $userID = (int) $_SESSION['user_id'];

    // Collect POST data safely
    $carparkID    = $_POST['booking_carpark_id'] ?? null;
    $name         = $_POST['booking_name'] ?? null;
    $date         = $_POST['booking_date'] ?? null;
    $vehicleID    = $_POST['booking_vehicle_id'] ?? null;

    $startTimeRaw = $_POST['booking_start_time'] ?? null;
    $endTimeRaw   = $_POST['booking_end_time'] ?? null;

    if (!$carparkID || !$name || !$date || !$startTimeRaw || !$endTimeRaw || !$vehicleID) {
        $errorMessage = "Please fill in all form fields.";
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    $bookingStart = $date . " " . $startTimeRaw . ":00";
    $bookingEnd   = $date . " " . $endTimeRaw . ":00";

    if ($bookingStart >= $bookingEnd) {
        $errorMessage = "Booking end cannot be before booking start.";
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    // 🔒 Verify vehicle belongs to user
    $stmt = $this->db->prepare("
        SELECT vehicle_id
        FROM vehicles
        WHERE vehicle_id = :vehicleID
        AND user_id = :userID
        LIMIT 1
    ");

    $stmt->execute([
        ':vehicleID' => $vehicleID,
        ':userID'    => $userID
    ]);

    if (!$stmt->fetch()) {
        $errorMessage = "Invalid vehicle selected.";
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    // Get Carpark Capacity
    $ReadCarparks = new ReadCarparks();
    $carpark = $ReadCarparks->getCarparkById($carparkID);

    if (!$carpark || !isset($carpark['carpark_capacity'])) {
        $errorMessage = "Selected car park data not found.";
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    $capacity = (int) $carpark['carpark_capacity'];

    // Count overlapping bookings
    $activeBookings = $this->countOverlappingBookings(
        (int) $carparkID,
        $bookingStart,
        $bookingEnd
    );

    if ($activeBookings >= $capacity) {
        $errorMessage = "This car park is fully booked for the selected time slot.";
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    // Insert booking with vehicle ID
    $bookingID = $this->insertBooking(
        (int) $carparkID,
        $name,
        $bookingStart,
        $bookingEnd,
        $userID,
        (int) $vehicleID
    );

    if (is_array($bookingID) && !$bookingID['success']) {
        $errorMessage = "Database error: " . $bookingID['message'];
        header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . urlencode($errorMessage));
        exit;
    }

    header("Location: /booking-confirmation.php?booking_id=" . $bookingID);
    exit;
}
}
