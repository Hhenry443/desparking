<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getCarparks();

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>DesParking</title>
    <meta name="viewport" content="initial-scale=1,maximum-scale=1,user-scalable=no">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <script src="js/bookingForm.js"></script>

    <link href="./css/output.css" rel="stylesheet">

    <script>
        const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>"
        const markers = <?= json_encode($carparks) ?>
    </script>
    <style>
        body {
            margin: 0;
            padding: 0;
        }

        #map {
            position: absolute;
            top: 0;
            bottom: 0;
            width: 100%;
            z-index: -1;
        }
    </style>
</head>

<body>
    <nav class="w-full h-16 bg-white/80 backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6">
        <!-- Logo -->
        <div class="flex items-center space-x-2">
            <span class="text-xl font-semibold text-gray-800">DesParking</span>
        </div>

        <!-- Nav Links -->
        <div class="hidden md:flex space-x-6 text-gray-700 font-medium">
            <a href="#" class="hover:text-green-600 transition">Book</a>
            <a href="#" class="hover:text-green-600 transition">Carparks</a>
            <a href="#" class="hover:text-green-600 transition">Account</a>
        </div>

        <!-- Mobile Menu Icon -->
        <button class="md:hidden p-2 rounded hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </nav>


    <div id="map"></div>

    <div
        id="booking-form-container"
        class="hidden fixed bottom-4 right-4 z-51"
        data-current=""></div>

    <div
        id="carpark-information-container"
        class="hidden fixed left-0 h-full w-92 z-51 mt-16"
        data-current="">

    </div>

    <script src="./js/map.js"></script>
</body>

</html>