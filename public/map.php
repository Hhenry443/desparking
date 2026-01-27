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

<body class="bg-[#ebebeb]">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="fixed top-20 left-1/2 -translate-x-1/2 z-40 w-full max-w-5xl px-4">
        <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.15)] p-4">
            <div class="grid grid-cols-1 md:grid-cols-9 gap-3">

                <!-- Location (3 columns) -->
                <div class="md:col-span-3 relative">
                    <input
                        id="search-location"
                        type="text"
                        placeholder="Where would you like to park?"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm font-medium
                            border border-gray-300
                            focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
                    />
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-[#6ae6fc] text-lg">
                        📍
                    </span>
                </div>

                <!-- Radius (1 column) -->
                <div class="md:col-span-1 flex items-center gap-2">
                    <input
                        id="search-radius"
                        type="number"
                        value="5"
                        min="1"
                        max="100"
                        class="w-full py-3 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                            border border-gray-300
                            focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
                    />
                    <span class="text-xs font-semibold text-gray-500">km</span>
                </div>

                <!-- Start DateTime (2 columns) -->
                <input
                    id="search-start"
                    type="datetime-local"
                    class="md:col-span-2 py-3 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                        border border-gray-300
                        focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
                />

                <!-- End DateTime (2 columns) -->
                <input
                    id="search-end"
                    type="datetime-local"
                    class="md:col-span-2 py-3 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                        border border-gray-300
                        focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
                />

                <!-- Search Button (1 column) -->
                <button
                    onclick="searchCarparks()"
                    class="md:col-span-1 w-full py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                        hover:bg-cyan-400 transition shadow-md"
                >
                    Search
                </button>

            </div>
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