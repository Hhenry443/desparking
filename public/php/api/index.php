<?php
session_start();

date_default_timezone_set('UTC');

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/WriteCarparks.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/users/WriteUsers.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/users/ReadUsers.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/WriteRates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/WriteVehicle.php';

switch ($_GET['id'] ?? null) {

    case 'insertBooking':
        $WriteBookings = new WriteBookings();
        $data = $WriteBookings->writeBooking();
        rtn(201, 'Booking successful', $data);
        break;


    case 'insertCarpark':
        $WriteCarparks = new WriteCarparks();
        $data = $WriteCarparks->insertCarparkWithRates();
        rtn(201, 'Carpark created successfully', $data);
        break;

    case 'updateCarpark':
        $WriteCarparks = new WriteCarparks();
        $data = $WriteCarparks->updateCarparkDetails();
        rtn(200, 'Carpark updated successfully', $data);
        break;
    
    case 'deleteCarpark':
        $WriteCarparks = new WriteCarparks();
        $data = $WriteCarparks->deleteCarparkByID();
        rtn(200, 'Carpark deleted successfully', $data);
        break;

    case 'addRate':
        $WriteRates = new WriteRates();
        $data = $WriteRates->addRate();
        rtn(201, 'Rate added successfully', $data);
        break;

    case 'deleteRate':
        $WriteRates = new WriteRates();
        $data = $WriteRates->deleteRate();
        rtn(200, 'Rate deleted successfully', $data);
        break;

    case 'getCarparkRates':
        $ReadRates = new ReadRates();  // Changed from WriteRates
        $ReadRates->getCarparkRatesJSON();
        break;

    case 'searchCarparks':
        $ReadCarparks = new ReadCarparks();

        $lat      = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
        $lng      = isset($_GET['lng']) ? (float) $_GET['lng'] : null;
        $radiusKm = isset($_GET['radius']) ? (float) $_GET['radius'] : 5;

        $startRaw = $_GET['startTime'] ?? null;
        $endRaw   = $_GET['endTime'] ?? null;

        if (!$lat || !$lng || !$startRaw || !$endRaw) {
            rtn(400, 'Missing required parameters', null);
        }

        try {
            // Incoming values are ISO 8601 with timezone (from JS)
            $startUTC = (new DateTime($startRaw))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s');
 
            $endUTC = (new DateTime($endRaw))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            rtn(400, 'Invalid datetime format', null);
        }

        $data = $ReadCarparks->searchAvailableCarparks(
            $lat,
            $lng,
            $radiusKm,
            $startUTC,
            $endUTC
        );

        rtn(200, 'Available carparks retrieved', $data);
        break;

    case 'insertUser':
        $WriteUsers = new WriteUsers();
        
        $userID = $WriteUsers->writeUser();
        rtn(201, 'User created successfully', $userID);
        break;

    case 'login':
        $ReadUsers = new ReadUsers();
        
        $user = $ReadUsers->loginUser();
        rtn(201, 'User logged in successfully', $user);
        break;

    case 'insertVehicle':
        $WriteVehicle = new WriteVehicles();

        $vehicle = $WriteVehicle->addVehicle();
        rtn(201, 'Vehicle created successfully', $vehicle);

    case 'deleteVehicle':
        $WriteVehicle = new WriteVehicles();

        $vehicle = $WriteVehicle->deleteVehicle();
        rtn(201, 'Vehicle deleted successfully', $vehicle);

    default:
        rtn(404, 'Invalid API endpoint', null);
        break;
}

function rtn($status, $feedback, $data)
{
    $rtn = array(
        "status"   => $status,
        "feedback" => $feedback,
        "data"     => $data
    );

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($rtn);

    die();
}
