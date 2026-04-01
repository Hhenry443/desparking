<?php

$title = "Find Cheap Parking";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


?>
<!doctype html>
<html>

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#ebebeb]">

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

        /* Clean date/time input */
        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            opacity: 0.5;
            cursor: pointer;
        }

        /* Glassmorphism search surface */
        .search-glass {
            background: rgba(255, 255, 255, 0.93);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.55);
        }

        /* Carpark info panel — bottom sheet on mobile, side panel on desktop */
        #carpark-information-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 62vh;
            z-index: 51;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            pointer-events: none;
            border-radius: 1.5rem 1.5rem 0 0;
            overflow: hidden;
            box-shadow: 0 -8px 48px rgba(0, 0, 0, 0.13);
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
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            }

            #carpark-information-container.panel-open {
                transform: translateX(0);
            }
        }
    </style>

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- Search bar (full form) -->
    <div id="search-bar" class="fixed top-16 left-0 right-0 z-40 px-3 pt-2">
        <div class="max-w-5xl mx-auto search-glass rounded-2xl shadow-[0_4px_24px_rgba(0,0,0,0.10)] p-3">

            <!-- Booking type toggle -->
            <div class="flex gap-1 mb-2 bg-gray-100/60 rounded-xl p-1">
                <button type="button" id="map-toggle-hourly" onclick="setMapBookingType('hourly')"
                    class="flex-1 py-1.5 rounded-lg bg-[#6ae6fc] text-gray-800 text-xs font-bold transition-all whitespace-nowrap">
                    Hourly / Daily
                </button>
                <button type="button" id="map-toggle-monthly" onclick="setMapBookingType('monthly')"
                    class="flex-1 py-1.5 rounded-lg text-gray-600 text-xs font-semibold transition-all whitespace-nowrap hover:bg-white/50">
                    Monthly
                </button>
            </div>

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
                        class="w-full pl-9 pr-10 py-3 rounded-xl bg-gray-100/80 text-gray-800 text-sm
                               border border-gray-200/60 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]" />
                    <button type="button" id="map-geolocate" title="Use my location"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#6ae6fc] transition z-10">
                        <i class="fa-solid fa-location-crosshairs"></i>
                    </button>
                    <input type="hidden" id="search-lat" value="">
                    <input type="hidden" id="search-lng" value="">
                    <div id="location-results"
                        class="absolute w-full bg-white rounded-xl shadow-[0_6px_18px_rgba(0,0,0,0.15)]
                               mt-1 hidden z-50 max-h-60 overflow-y-auto border border-gray-200"></div>
                </div>

                <!-- From + Until: stacked on mobile, inline on desktop (lg:contents dissolves the wrapper) -->
                <div class="flex flex-col gap-2 lg:contents">

                    <!-- From -->
                    <div class="flex items-center flex-1 bg-gray-100/80 rounded-xl border border-gray-200/60 min-w-0 overflow-hidden">
                        <button type="button" id="map-from-trigger"
                            class="flex-1 flex items-center gap-1.5 px-3 py-2.5 min-w-0 hover:bg-black/5 transition">
                            <i class="fa-regular fa-calendar text-[#6ae6fc] text-xs flex-shrink-0"></i>
                            <span id="map-from-label" class="text-xs lg:text-sm font-medium text-gray-700 truncate">Today</span>
                        </button>
                        <div id="map-from-time-sep" class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                        <button type="button" id="map-from-time-btn"
                            class="flex items-center gap-1 pl-2 pr-3 py-2.5 text-xs lg:text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap flex-shrink-0">
                            <span id="map-from-time-label">--:--</span>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-[0.55rem]"></i>
                        </button>
                        <input type="hidden" id="search-from-date" />
                        <input type="hidden" id="search-from-time" />
                    </div>

                    <!-- Until -->
                    <div id="map-until-section" class="flex items-center flex-1 bg-gray-100/80 rounded-xl border border-gray-200/60 min-w-0 overflow-hidden">
                        <button type="button" id="map-until-trigger"
                            class="flex-1 flex items-center gap-1.5 px-3 py-2.5 min-w-0 hover:bg-black/5 transition">
                            <i class="fa-solid fa-flag-checkered text-[#6ae6fc] text-xs flex-shrink-0"></i>
                            <span id="map-until-label" class="text-xs lg:text-sm font-medium text-gray-700 truncate">Tomorrow</span>
                        </button>
                        <div class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                        <button type="button" id="map-until-time-btn"
                            class="flex items-center gap-1 pl-2 pr-3 py-2.5 text-xs lg:text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap flex-shrink-0">
                            <span id="map-until-time-label">--:--</span>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-[0.55rem]"></i>
                        </button>
                        <input type="hidden" id="search-until-date" />
                        <input type="hidden" id="search-until-time" />
                    </div>

                </div>

                <!-- Hidden radius -->
                <input type="hidden" id="search-radius" value="25" />

                <!-- Search button — icon-only on mobile, icon+text on desktop -->
                <button
                    onclick="searchCarparks()"
                    class="flex items-center justify-center gap-2 px-5 lg:px-6 py-3 rounded-xl bg-[#6ae6fc] text-gray-900
                           font-bold hover:bg-cyan-400 active:scale-95 transition-all shadow-sm whitespace-nowrap">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    <span class="hidden lg:inline text-sm">Search</span>
                </button>

            </div>
        </div>
    </div>

    <!-- Post-search compact pill — mobile only, swaps in after searching -->
    <div id="search-pill" class="hidden fixed top-16 left-0 right-0 z-40 px-3 pt-2 lg:hidden">
        <div class="search-glass rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.10)] px-4 py-3 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-[#6ae6fc]/20 flex items-center justify-center flex-shrink-0 border border-[#6ae6fc]/30">
                <i class="fa-solid fa-location-dot text-[#060745] text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p id="pill-location" class="text-sm font-bold text-gray-900 truncate leading-tight"></p>
                <p id="pill-dates" class="text-xs text-gray-400 mt-0.5 truncate"></p>
            </div>
            <button onclick="expandMobileSearch()"
                class="flex-shrink-0 text-xs font-bold text-gray-900 bg-[#6ae6fc] px-3 py-1.5 rounded-xl
                       hover:bg-cyan-400 active:scale-95 transition-all whitespace-nowrap shadow-sm">
                Edit
            </button>
        </div>
    </div>

    <div id="map"></div>

    <div
        id="booking-form-container"
        class="hidden fixed bottom-4 right-4 z-51"
        data-current=""></div>

    <div id="carpark-information-container" data-current=""></div>

    <!-- Mobile list view (shown when toggled, mobile only) -->
    <div id="list-view" class="hidden fixed inset-0 z-30 overflow-y-auto bg-gray-100 lg:hidden"></div>

    <!-- Map / List toggle (mobile only, shown after first search) -->
    <div id="view-toggle" class="hidden fixed bottom-6 left-1/2 -translate-x-1/2 z-50 lg:hidden">
        <div class="flex bg-[#060745] rounded-full p-1 shadow-xl">
            <button id="toggle-map-btn" onclick="setView('map')"
                class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold transition-all bg-white text-[#060745]">
                <i class="fa-solid fa-map text-xs"></i> Map
            </button>
            <button id="toggle-list-btn" onclick="setView('list')"
                class="flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-bold transition-all text-white/70">
                <i class="fa-solid fa-list text-xs"></i> List
            </button>
        </div>
    </div>

    <script>
        // Fill dates with defaults, then override with any saved search
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

            // Restore last search from localStorage
            try {
                const saved = JSON.parse(localStorage.getItem('desparking_map_search') || 'null');
                if (saved) {
                    if (saved.location) document.getElementById('search-location').value = saved.location;
                    if (saved.lat) document.getElementById('search-lat').value = saved.lat;
                    if (saved.lng) document.getElementById('search-lng').value = saved.lng;
                    if (saved.fromDate) {
                        document.getElementById('search-from-date').value = saved.fromDate;
                        const fl = document.getElementById('map-from-label');
                        if (fl && window.friendlyDateLabel) fl.textContent = friendlyDateLabel(saved.fromDate);
                    }
                    if (saved.fromTime && window._mapPickerFromTime) window._mapPickerFromTime.setValue(saved.fromTime);
                    if (saved.untilDate) {
                        document.getElementById('search-until-date').value = saved.untilDate;
                        const ul = document.getElementById('map-until-label');
                        if (ul && window.friendlyDateLabel) ul.textContent = friendlyDateLabel(saved.untilDate);
                    }
                    if (saved.untilTime && window._mapPickerUntilTime) window._mapPickerUntilTime.setValue(saved.untilTime);
                }
            } catch {}
        })();
    </script>

    <script src="./js/datePicker.js"></script>
    <script src="./js/bookingForm.js?v=2"></script>
    <script src="./js/map.js?v=2"></script>
</body>

</html>