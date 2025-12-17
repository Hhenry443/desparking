<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Bookings.php';

class WriteBookings extends Bookings
{
    private PDO $db;

    public function writeBooking()
    {
        // Collect POST data safely
        $carparkID      = $_POST['booking_carpark_id'] ?? null;
        $name           = $_POST['booking_name'] ?? null;
        $date           = $_POST['booking_date'] ?? null;

        // Collect new start and end times
        $startTimeRaw   = $_POST['booking_start_time'] ?? null;
        $endTimeRaw     = $_POST['booking_end_time'] ?? null;

        if (!$carparkID || !$name || !$date || !$startTimeRaw || !$endTimeRaw) {
            $errorMessage = "Please fill in all form fields.";
            $encodedError = urlencode($errorMessage);

            header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . $encodedError);

            exit;
        }

        $bookingStart = $date . " " . $startTimeRaw . ":00";
        $bookingEnd   = $date . " " . $endTimeRaw . ":00";

        // Basic time order validation
        if ($bookingStart >= $bookingEnd) {
            $errorMessage = "Booking end cannot be before booking start.";
            $encodedError = urlencode($errorMessage);

            header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . $encodedError);

            exit;
        }

        // Get Carpark Capacity
        $ReadCarparks = new ReadCarparks();
        $carpark = $ReadCarparks->getCarparkById($carparkID);

        if (!$carpark || !isset($carpark['carpark_capacity'])) {
            // Handle carpark not found, or carpark capacity not found error
            $errorMessage = "Selected car park data not found.";
            $encodedError = urlencode($errorMessage);

            header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . $encodedError);

            exit;
        }

        $capacity = (int) $carpark['carpark_capacity'];

        // Count Overlapping Bookings using the new Bookings model method
        $activeBookings = $this->countOverlappingBookings(
            (int) $carparkID,
            $bookingStart,
            $bookingEnd
        );

        // Final check against capacity
        if ($activeBookings >= $capacity) {
            // Return JSON error
            $errorMessage = "This car park is fully booked for the selected time slot. Please choose another time.";
            $encodedError = urlencode($errorMessage);

            header("Location: /book.php?carpark_id=" . $carparkID . "&error=" . $encodedError);

            exit;
        }

        // Insert + get ID
        $bookingID = $this->insertBooking(
            $carparkID,
            $name,
            $bookingStart,
            $bookingEnd
        );

        // Check if insert was successful 
        if (is_array($bookingID) && !$bookingID['success']) {
            return $bookingID; // Return database error if one occurred
        }

        header("Location: /booking-confirmation.php?booking_id=" . $bookingID);
        exit;
    }
}
