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
    <title>DesParking – Find Parking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>
    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>

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

        /* Clean date/time input — remove default browser chrome on webkit */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
        }

        /* Carpark info panel — bottom sheet on mobile, side panel on desktop */
        #carpark-information-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 70vh;
            z-index: 51;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.32, 0.72, 0, 1);
            pointer-events: none;
            border-radius: 1.25rem 1.25rem 0 0;
            overflow: hidden;
        }

        #carpark-information-container.panel-open {
            transform: translateY(0);
            pointer-events: auto;
        }

        @media (min-width: 1024px) {
            #carpark-information-container {
                top: 4rem;
                bottom: 0;
                left: 0;
                right: auto;
                height: auto;
                width: 26rem;
                transform: translateX(-100%);
                border-radius: 0;
            }

            #carpark-information-container.panel-open {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-[#ebebeb]">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Search bar -->
    <div class="fixed top-16 left-0 right-0 z-40 px-3 pt-2">
        <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.13)] p-3">
            <div class="flex flex-col lg:flex-row items-stretch gap-2">

                <!-- Location -->
                <div class="relative flex-[2]">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#6ae6fc] pointer-events-none text-base z-10">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                    <input
                        id="search-location"
                        type="text"
                        autocomplete="off"
                        placeholder="Where would you like to park?"
                        class="w-full pl-9 pr-3 py-3 rounded-xl bg-gray-100 text-gray-800 text-sm
                               border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]" />
                    <input type="hidden" id="search-lat" value="">
                    <input type="hidden" id="search-lng" value="">
                    <div id="location-results"
                         class="absolute w-full bg-white rounded-xl shadow-[0_6px_18px_rgba(0,0,0,0.15)]
                                mt-1 hidden z-50 max-h-60 overflow-y-auto border border-gray-200"></div>
                </div>

                <!-- From -->
                <div class="flex items-center gap-2 flex-1 bg-gray-100 rounded-xl px-4 py-2 border border-gray-200 min-w-0">
                    <span class="text-xs font-bold text-[#060745] uppercase tracking-wide whitespace-nowrap">From</span>
                    <div class="flex gap-1 flex-1 min-w-0">
                        <input
                            id="search-from-date"
                            type="date"
                            class="flex-1 min-w-0 bg-transparent text-gray-700 text-sm focus:outline-none" />
                        <input
                            id="search-from-time"
                            type="time"
                            class="w-20 bg-transparent text-gray-700 text-sm focus:outline-none" />
                    </div>
                </div>

                <!-- Until -->
                <div class="flex items-center gap-2 flex-1 bg-gray-100 rounded-xl px-4 py-2 border border-gray-200 min-w-0">
                    <span class="text-xs font-bold text-[#060745] uppercase tracking-wide whitespace-nowrap">Until</span>
                    <div class="flex gap-1 flex-1 min-w-0">
                        <input
                            id="search-until-date"
                            type="date"
                            class="flex-1 min-w-0 bg-transparent text-gray-700 text-sm focus:outline-none" />
                        <input
                            id="search-until-time"
                            type="time"
                            class="w-20 bg-transparent text-gray-700 text-sm focus:outline-none" />
                    </div>
                </div>

                <!-- Hidden radius -->
                <input type="hidden" id="search-radius" value="25" />

                <!-- Search button -->
                <button
                    onclick="searchCarparks()"
                    class="flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[#6ae6fc] text-gray-900
                           text-sm font-bold hover:bg-cyan-400 transition shadow-sm whitespace-nowrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>Search</span>
                </button>

            </div>
        </div>
    </div>

    <div id="map"></div>

    <div
        id="booking-form-container"
        class="hidden fixed bottom-4 right-4 z-51"
        data-current=""></div>

    <div id="carpark-information-container" data-current=""></div>

    <script>
        // Auto-fill: From = today at current hour, Until = tomorrow same time
        (function() {
            const now = new Date();
            const tomorrow = new Date(now);
            tomorrow.setDate(tomorrow.getDate() + 1);

            const pad = n => String(n).padStart(2, '0');
            const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
            const time = `${pad(now.getHours())}:00`;

            document.getElementById('search-from-date').value = fmtDate(now);
            document.getElementById('search-from-time').value = time;
            document.getElementById('search-until-date').value = fmtDate(tomorrow);
            document.getElementById('search-until-time').value = time;
        })();
    </script>

    <script src="./js/bookingForm.js?v=2"></script>
    <script src="./js/map.js?v=2"></script>
</body>

</html>