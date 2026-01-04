<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';

class WriteCarparks extends Carparks
{
    private PDO $db;

    public function writeCarpark()
    {
        // Collect POST data safely
        $carpark_name = $_POST['carpark_name'] ?? null;
        $carpark_address = $_POST['carpark_address'] ?? null;
        $carpark_lat = $_POST['carpark_lat'] ?? null;
        $carpark_lng = $_POST['carpark_lng'] ?? null;
        $carpark_price = $_POST['carpark_price'] ?? null;
        $carpark_description = $_POST['carpark_description'] ?? null;

        if (!$carpark_name || !$carpark_address || !$carpark_lat || !$carpark_lng || !$carpark_price || !$carpark_description) {
            $errorMessage = "Please fill in all form fields.";
            $encodedError = urlencode($errorMessage);

            header("Location: /create.php?error=" . $encodedError);

            exit;
        }

        // Insert + get ID
        $carparkID = $this->insertCarpark(
            $carpark_name,
            $carpark_address,
            $carpark_lat,
            $carpark_lng,
            $carpark_price,
            $carpark_description
        );

        // Check if insert was successful 
        if (is_array($carparkID) && !$carparkID['success']) {
            return $carparkID; // Return database error if one occurred
        }

        header("Location: /account.php?carpark_id=" . $carparkID);
        exit;
    }

}
