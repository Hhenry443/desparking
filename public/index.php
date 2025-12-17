<?php

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