<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$carparkID = $_GET['carpark_id'] ?? null;

if (!$carparkID) {
    die("Invalid car park.");
}

$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById($carparkID);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Book a Space – <?= htmlspecialchars($carpark['carpark_name']) ?></title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <script src="https://js.stripe.com/v3/"></script>
    <script src="js/stripe.js"></script>

    <link href="./css/output.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">

    <!-- IMAGE HEADER -->
    <div class="w-full h-56 md:h-72 lg:h-80 overflow-hidden">
        <img
            src=" /images/default-carpark-image.png"
            class="w-full h-full object-cover"
            alt="Car Park Image">
    </div>

    <!-- MAIN CONTENT -->
    <div id="checkout">
        <!-- Checkout will insert the payment form here -->
    </div>
    <br><br>
</body>

</html>