<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

$ReadCarparks = new ReadCarparks();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {

    case 'getAllCarparks':
        $carparks = $ReadCarparks->getCarparks();
        echo json_encode($carparks);
        break;
}
