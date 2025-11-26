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
    <div id="navbar" class="w-full h-16 bg-red-100 flex items-center justify-around">
        <div id="navbar-title">
            <p>DesParking</p>
        </div>

        <div id="navbar-links" class="flex space-x-2">
            <p>Link 1</p>
            <p>Link 2</p>
            <p>Link 3</p>
        </div>

    </div>
    <div id="map"></div>

    <div id="booking-form-container" class="w-24 h-24 hidden bg-amber-200">
    </div>

    <script src="./js/map.js"></script>
</body>

</html>