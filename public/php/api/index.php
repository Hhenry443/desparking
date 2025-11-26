<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';

switch ($_GET['id']) {
    case 'insertBooking':
        $WriteBookings = new WriteBookings();
        $data = $WriteBookings->writeBooking();
        rtn(201, 'Booking successful', $data);
        break;

    default:
        # code...
        break;
}

function rtn($status, $feedback, $data)
{

    $rtn = array(
        "status" => $status,
        "feedback" => $feedback,
        "data" => $data
    );

    header('Content-Type: application/json; charset=utf-8');
    http_response_code($status);
    echo json_encode($rtn, true);

    die();
}
