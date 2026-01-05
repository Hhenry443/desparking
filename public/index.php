<?php

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


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
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-40 w-full max-w-5xl px-4">
        <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-lg p-4 flex flex-col md:flex-row gap-3">

            <input
                id="search-location"
                type="text"
                placeholder="Enter a location"
                class="flex-2 rounded-xl border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />

            <div class="flex items-center gap-2 flex-1">
                <input
                    id="search-radius"
                    type="number"
                    value="5"
                    min="1"
                    max="100"
                    class="w-full rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />
                <span class="text-sm text-gray-600 font-medium">km</span>
            </div>

            <input
                id="search-start"
                type="datetime-local"
                class="rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />

            <input
                id="search-end"
                type="datetime-local"
                class="rounded-xl border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500" />

            <button
                onclick="searchCarparks()"
                class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-xl transition">
                Search
            </button>
        </div>
    </div>


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