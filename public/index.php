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
    <title>EveryonesParking - Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>

    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">

    <link href="./css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
    <script>
        const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>";
    </script>
    <script src="./js/datePicker.js"></script>
</head>

<body class="min-h-screen bg-white">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <!-- HERO / SECTION 1 -->
    <section id="section-1" class="relative bg-white overflow-hidden pt-28 lg:pt-48 pb-12 lg:pb-32">

        <!-- Right-side background image, anchored to bottom of section -->
        <div class="absolute bottom-0 left-3/4 -translate-x-1/2 w-[480px] opacity-5 z-0 pointer-events-none hidden lg:block">
            <img
                src="/images/desparking-icon.png"
                class="w-full grayscale object-contain"
                alt="" />
        </div>

        <!-- Inner container (tightens everything like section 3) -->
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-start">

            <!-- LEFT: Text + Form -->
            <div>
                <p class="text-3xl font-bold text-[#6ae6fc]">Park Easy.</p>
                <p class="text-3xl font-bold text-black mt-2">Anytime. Anywhere.</p>

                <div class="flex flex-wrap items-center mt-4 gap-4 text-sm text-gray-600 font-bold">
                    <p>Stress-Free Booking</p>
                    <p class="text-[#6ae6fc]">24 Hr Services</p>
                    <p>Best Parking Solution</p>
                </div>

                <!-- Trust badges -->
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-6">
                    <span class="flex items-center gap-1.5 text-sm text-gray-700 font-medium">
                        <i class="fa-solid fa-circle-check text-[#6ae6fc]"></i> Satisfaction guaranteed
                    </span>
                    <span class="flex items-center gap-1.5 text-sm text-gray-700 font-medium">
                        <i class="fa-solid fa-circle-check text-[#6ae6fc]"></i> Availability guaranteed
                    </span>
                    <span class="flex items-center gap-1.5 text-sm text-gray-700 font-medium">
                        <i class="fa-solid fa-circle-check text-[#6ae6fc]"></i> Instant confirmation
                    </span>
                </div>

                <!-- Search box -->
                <form
                    id="homepage-search-form"
                    class="bg-white rounded-3xl p-6 shadow-[0_0_25px_rgba(0,0,0,0.15)] w-full mt-10 max-w-xl mx-auto lg:mx-0"
                    action="/map.php"
                    method="GET">

                    <!-- Toggle -->
                    <div class="flex w-full gap-1 mb-6 bg-gray-100 rounded-xl p-1">
                        <button type="button" id="toggle-hourly" onclick="setBookingType('hourly')"
                            class="flex-1 py-2 rounded-lg bg-[#6ae6fc] text-gray-800 text-sm font-semibold transition-all">
                            Hourly / Daily
                        </button>
                        <button type="button" id="toggle-monthly" onclick="setBookingType('monthly')"
                            class="flex-1 py-2 rounded-lg text-gray-600 text-sm font-semibold transition-all hover:bg-white/50">
                            Monthly
                        </button>
                    </div>

                    <input type="hidden" id="booking_type_hidden" name="booking_type" value="hourly">

                    <!-- Location -->
                    <div class="mb-3 relative">
                        <i class="fa-solid fa-location-dot absolute left-3 top-1/2 -translate-y-1/2 text-[#6ae6fc] pointer-events-none z-10"></i>
                        <input
                            id="home-location"
                            name="location"
                            type="text"
                            autocomplete="off"
                            placeholder="Where would you like to park?"
                            class="w-full py-3 pl-9 pr-10 rounded-xl bg-gray-100 text-gray-700 text-sm border border-gray-200
                                   focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]"
                            required />
                        <button type="button" id="home-geolocate" title="Use my location"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#6ae6fc] transition z-10">
                            <i class="fa-solid fa-location-crosshairs"></i>
                        </button>
                        <div id="home-location-results"
                            class="absolute w-full bg-white rounded-xl shadow-[0_6px_18px_rgba(0,0,0,0.15)]
                                   mt-1 hidden z-50 max-h-60 overflow-y-auto border border-gray-200"></div>
                    </div>

                    <!-- Hourly / Daily fields -->
                    <div id="hourly-fields">

                        <!-- From -->
                        <div class="mb-3">
                            <p class="text-xs font-bold text-[#060745] uppercase tracking-wide mb-2">Arrive</p>
                            <div class="flex gap-2">
                                <button type="button" id="from-date-trigger"
                                    class="flex-1 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 hover:border-[#6ae6fc] focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] transition text-left min-w-0">
                                    <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                                    <span id="from-date-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Today</span>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs flex-shrink-0"></i>
                                </button>
                                <button type="button" id="home-from-time-btn"
                                    class="flex items-center gap-1.5 px-3 py-3 rounded-xl bg-gray-100 border border-gray-200 hover:border-[#6ae6fc] transition text-sm font-medium text-gray-700 whitespace-nowrap">
                                    <span id="home-from-time-label">--:--</span>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                                </button>
                                <input type="hidden" id="home-from-time" name="entry_time" />
                            </div>
                            <input id="home-from-date" type="hidden" name="entry_date" />
                        </div>

                        <!-- Until -->
                        <div class="mb-6">
                            <p class="text-xs font-bold text-[#060745] uppercase tracking-wide mb-2">Leave by</p>
                            <div class="flex gap-2">
                                <button type="button" id="until-date-trigger"
                                    class="flex-1 flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 hover:border-[#6ae6fc] focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] transition text-left min-w-0">
                                    <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                                    <span id="until-date-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Tomorrow</span>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs flex-shrink-0"></i>
                                </button>
                                <button type="button" id="home-until-time-btn"
                                    class="flex items-center gap-1.5 px-3 py-3 rounded-xl bg-gray-100 border border-gray-200 hover:border-[#6ae6fc] transition text-sm font-medium text-gray-700 whitespace-nowrap">
                                    <span id="home-until-time-label">--:--</span>
                                    <i class="fa-solid fa-chevron-down text-gray-400 text-xs"></i>
                                </button>
                                <input type="hidden" id="home-until-time" name="exit_time" />
                            </div>
                            <input id="home-until-date" type="hidden" name="exit_date" />
                        </div>

                    </div><!-- end #hourly-fields -->

                    <!-- Monthly fields -->
                    <div id="monthly-fields" class="hidden mb-6">
                        <p class="text-xs font-bold text-[#060745] uppercase tracking-wide mb-2">Subscription start</p>
                        <button type="button" id="monthly-start-trigger"
                            class="w-full flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 hover:border-[#6ae6fc] focus:outline-none focus:ring-2 focus:ring-[#6ae6fc] transition text-left">
                            <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                            <span id="monthly-start-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Today</span>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs flex-shrink-0"></i>
                        </button>
                        <input type="hidden" id="monthly-start-hidden" />
                        <p class="text-xs text-gray-400 mt-2">Your subscription will run for 1 month from this date.</p>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-xl bg-[#6ae6fc] text-gray-900 text-sm font-bold shadow-md hover:bg-cyan-400 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Show parking spaces
                    </button>

                </form>

                <script>
                    (function() {
                        const pad = n => String(n).padStart(2, '0');
                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        const tmr = new Date(today);
                        tmr.setDate(today.getDate() + 1);
                        const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
                        const now = new Date();
                        const mins = now.getMinutes();
                        const fromTime = new Date(now);
                        fromTime.setMinutes(mins <= 30 ? 30 : 0, 0, 0);
                        if (mins > 30) fromTime.setHours(fromTime.getHours() + 1);
                        const untilTime = new Date(fromTime.getTime() + 60 * 60 * 1000);
                        const fmtTime = d => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

                        const pickerFrom = makeDatePicker('from-date-trigger', 'from-date-label', 'home-from-date');
                        const pickerUntil = makeDatePicker('until-date-trigger', 'until-date-label', 'home-until-date');
                        pickerFrom.select(fmtDate(today));
                        pickerUntil.select(fmtDate(tmr));

                        const timeFrom = makeTimePicker('home-from-time-btn', 'home-from-time-label', 'home-from-time');
                        const timeUntil = makeTimePicker('home-until-time-btn', 'home-until-time-label', 'home-until-time');
                        if (timeFrom) timeFrom.setValue(fmtTime(fromTime));
                        if (timeUntil) timeUntil.setValue(fmtTime(untilTime));

                        const pickerMonthly = makeDatePicker('monthly-start-trigger', 'monthly-start-label', 'monthly-start-hidden');
                        if (pickerMonthly) pickerMonthly.select(fmtDate(today));

                        document.getElementById('homepage-search-form').addEventListener('submit', function(e) {
                            const bookingType = document.getElementById('booking_type_hidden').value;
                            if (bookingType === 'monthly') {
                                const startDate = document.getElementById('monthly-start-hidden').value;
                                if (!startDate) {
                                    e.preventDefault();
                                    alert('Please select a subscription start date.');
                                    return;
                                }
                                document.getElementById('home-from-date').value = startDate;
                                document.getElementById('home-until-date').value = '';
                                document.getElementById('home-from-time').value = '';
                                document.getElementById('home-until-time').value = '';
                            } else if (!document.getElementById('home-from-date').value ||
                                       !document.getElementById('home-until-date').value) {
                                e.preventDefault();
                                alert('Please select arrival and departure dates.');
                            }
                        });
                    })();

                    const _activeTab = 'flex-1 py-2 rounded-lg bg-[#6ae6fc] text-gray-800 text-sm font-semibold transition-all';
                    const _inactiveTab = 'flex-1 py-2 rounded-lg text-gray-600 text-sm font-semibold transition-all hover:bg-white/50';

                    function setBookingType(type) {
                        document.getElementById('booking_type_hidden').value = type;
                        const hourlyFields  = document.getElementById('hourly-fields');
                        const monthlyFields = document.getElementById('monthly-fields');
                        const toggleHourly  = document.getElementById('toggle-hourly');
                        const toggleMonthly = document.getElementById('toggle-monthly');
                        if (type === 'monthly') {
                            hourlyFields.classList.add('hidden');
                            monthlyFields.classList.remove('hidden');
                            toggleHourly.className  = _inactiveTab;
                            toggleMonthly.className = _activeTab;
                        } else {
                            hourlyFields.classList.remove('hidden');
                            monthlyFields.classList.add('hidden');
                            toggleHourly.className  = _activeTab;
                            toggleMonthly.className = _inactiveTab;
                        }
                    }
                </script>

                <script>
                    (function() {
                        const input = document.getElementById('home-location');
                        const results = document.getElementById('home-location-results');
                        const geoBtn = document.getElementById('home-geolocate');
                        let debounceTimer;

                        input.addEventListener('input', () => {
                            clearTimeout(debounceTimer);
                            const q = input.value.trim();
                            if (q.length < 3) {
                                results.classList.add('hidden');
                                return;
                            }
                            debounceTimer = setTimeout(() => fetchSuggestions(q), 280);
                        });

                        document.addEventListener('click', (e) => {
                            if (!e.target.closest('#home-location') && !e.target.closest('#home-location-results')) {
                                results.classList.add('hidden');
                            }
                        });

                        async function fetchSuggestions(query) {
                            try {
                                const res = await fetch(
                                    `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&limit=5`
                                );
                                const data = await res.json();
                                if (!data.features || !data.features.length) {
                                    results.innerHTML = '<div class="p-3 text-gray-500 text-sm">No results found</div>';
                                    results.classList.remove('hidden');
                                    return;
                                }
                                window._homeFeatures = data.features;
                                results.innerHTML = data.features.map((f, i) => `
                                    <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                         onclick="selectLocation(window._homeFeatures[${i}])">
                                        <p class="text-sm font-semibold text-gray-800">${f.text}</p>
                                        <p class="text-xs text-gray-500">${f.place_name}</p>
                                    </div>
                                `).join('');
                                results.classList.remove('hidden');
                            } catch {
                                results.classList.add('hidden');
                            }
                        }

                        window.selectLocation = function(feature) {
                            input.value = feature.place_name;
                            results.classList.add('hidden');
                        };

                        geoBtn.addEventListener('click', () => {
                            if (!navigator.geolocation) return;
                            geoBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                            navigator.geolocation.getCurrentPosition(
                                async (pos) => {
                                        const {
                                            latitude: lat,
                                            longitude: lng
                                        } = pos.coords;
                                        try {
                                            const res = await fetch(
                                                `https://api.mapbox.com/search/geocode/v6/reverse?longitude=${lng}&latitude=${lat}&access_token=${MAPBOX_TOKEN}`
                                            );
                                            const data = await res.json();
                                            const feature = data.features && data.features[0];
                                            input.value = feature ?
                                                (feature.properties.full_address || feature.properties.name) :
                                                `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                                        } catch {
                                            input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                                        }
                                        geoBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                                    },
                                    () => {
                                        geoBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                                        alert('Could not get your location. Please check your browser permissions.');
                                    }, {
                                        timeout: 10000
                                    }
                            );
                        });
                    })();
                </script>
            </div>

            <!-- RIGHT: Images -->
            <div class="relative hidden lg:block">
                <div class="relative flex items-start pl-12 pb-24">
                    <!-- Back image (cars, smaller, raised) -->
                    <img
                        src="/images/homepage-image-2.jpg"
                        class="absolute top-0 right-0 w-[300px] h-[420px]
                        object-cover rounded-3xl
                        border-[6px] border-white
                        shadow-lg z-0"
                        alt="" />

                    <!-- Front image (girl, bigger, dominant) -->
                    <img
                        src="/images/homepage-image-1.jpg"
                        class="relative w-[300px] h-[420px]
                        object-cover rounded-3xl
                        border-[6px] border-white
                        shadow-2xl z-10
                        mt-40"
                        alt="" />
                </div>
            </div>


        </div>
    </section>



    <section id="section-2" class="bg-gray-100 my-10">
        <!-- Dog-leg strip -->
        <div class="absolute inset-x-0 pointer-events-none hidden lg:block">
            <!-- Vertical down from left -->
            <div class="absolute left-[10%] top-0 w-10 h-50 bg-gray-300"></div>

            <!-- Horizontal to step 2 -->
            <div class="absolute left-[10%] top-50 w-[80%] h-10 bg-gray-300"></div>

            <!-- Vertical down -->
            <div class="absolute left-[90%] top-50 w-10 h-50 bg-gray-300"></div>
        </div>

        <div class="max-w-6xl mx-auto px-6 py-10 relative ">

            <h2 class="text-center text-4xl font-bold text-gray-900 mb-20">
                How to Park
            </h2>



            <!-- Steps -->
            <div class="relative grid grid-cols-1 lg:grid-cols-3 gap-12 z-10">

                <!-- Step 1 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        1
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Search from anywhere</h3>
                    <p class="mt-2 text-gray-600">
                        Search and find parking using our booking system.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        2
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Book in advance or on demand</h3>
                    <p class="mt-2 text-gray-600">
                        Pre-book your space or book it when you arrive.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="text-center">
                    <div class="mx-auto w-24 h-24 rounded-xl bg-[#060745] flex items-center justify-center text-white text-3xl font-bold">
                        3
                    </div>
                    <h3 class="mt-6 text-xl font-bold text-[#060745]">Park with confidence</h3>
                    <p class="mt-2 text-gray-600">
                        Manage your parking session from anywhere.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <!-- SECTION 3 – Benefits -->
    <section id="section-3" class="bg-white pt-16 pb-8 lg:pb-0">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-start">

            <!-- LEFT: Images -->
            <div class="relative flex justify-center items-start h-[260px] lg:h-auto lg:pb-32 order-2 lg:order-1">

                <!-- Left-side background image, anchored to bottom of section -->
                <div class="hidden lg:block absolute bottom-0 right-1/2 w-[480px] opacity-5 z-0 pointer-events-none">
                    <img
                        src="/images/desparking-icon.png"
                        class="w-full grayscale object-contain"
                        alt="" />
                </div>

                <!-- Centered wrapper for the overlapping pair (mobile only) -->
                <div class="relative w-[220px] h-[240px] lg:hidden">
                    <!-- Back image (CAR) -->
                    <img
                        src="/images/homepage-image-4.jpg"
                        class="w-[140px] h-[200px] object-cover rounded-2xl shadow-lg absolute left-0 top-0 border-white border-[6px]"
                        alt="" />
                    <!-- Front image (GIRL) -->
                    <img
                        src="/images/homepage-image-3.jpg"
                        class="w-[140px] h-[200px] object-cover rounded-2xl shadow-xl absolute left-[80px] top-[40px] z-10 border-white border-[6px]"
                        alt="" />
                </div>

                <!-- Desktop images (original layout) -->
                <img
                    src="/images/homepage-image-4.jpg"
                    class="hidden lg:block w-[300px] h-[420px] object-cover rounded-2xl shadow-lg absolute left-0 top-0 border-white border-[6px]"
                    alt="" />
                <img
                    src="/images/homepage-image-3.jpg"
                    class="hidden lg:block w-[300px] h-[420px] object-cover rounded-2xl shadow-xl relative z-10 ml-32 mt-40 border-white border-[6px]"
                    alt="" />

            </div>


            <!-- RIGHT: Text content -->
            <div class="order-1 lg:order-2">
                <p class="text-cyan-400 font-bold tracking-wide uppercase mb-2">
                    The Benefits
                </p>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">
                    Parking Solutions
                </h2>
                <p class="text-gray-600 mb-12 max-w-lg">
                    There are several benefits of renting out your car parking space to others through EveryonesParking:
                </p>

                <div class="space-y-8">

                    <!-- Item 1 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-mobile-screen text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Easy Management</h3>
                            <p class="text-gray-600 max-w-md">
                                With EveryonesParking, you have full control over how you rent out your space.
                                Tell us the availability, set your own rates and offer optional extras to renters.
                            </p>
                        </div>
                    </div>


                    <!-- Item 2 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-money-bill-wave text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Generating Extra Income</h3>
                            <p class="text-gray-600 max-w-md">
                                By renting out your parking space through EveryonesParking, you can turn an underutilised asset
                                into a steady stream of additional income.
                            </p>
                        </div>
                    </div>

                    <!-- Item 3 -->
                    <div class="flex gap-6 items-start">
                        <div class="w-16 h-16 bg-[#060745] rounded-xl flex items-center justify-center text-white">
                            <i class="fa-solid fa-car text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Expanding your reach</h3>
                            <p class="text-gray-600 max-w-md">
                                By listing your space on EveryonesParking, you gain access to our extensive network of customers
                                looking for convenient parking solutions.
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- SECTION 4 – FAQ -->
    <section id="section-4" class="bg-gray-100 py-32">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-24 items-start">

            <!-- LEFT: FAQ -->
            <div>
                <p class="text-[#6ae6fc] font-bold tracking-wide uppercase mb-2">
                    Frequently Asked Questions
                </p>
                <h2 class="text-4xl font-bold text-gray-900 mb-12">
                    Have Questions?
                </h2>

                <div class="space-y-6" id="faq">

                    <!-- FAQ Item -->
                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            How can I contact EveryonesParking customer support for assistance?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                Our customer support team is available 24/7 to assist you with any questions or concerns. You can reach us by email at support@everyonesparking.com.
                            </p>
                            <p class="pb-4">
                                Alternatively, you can use the contact form on our website to send us a message, and we’ll get back to you promptly.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            What amenities do EveryonesParking facilities offer?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                Our facilities are meticulously designed not only to ensure accessibility and security but also to enhance the overall parking experience for our customers.
                            </p>
                            <p class="pb-2">
                                Depending on the location, you can expect a range of amenities that add convenience and comfort to your visit. These amenities may include covered parking options to protect your vehicle from the elements, EV charging stations for electric vehicles, and convenient car wash services to keep your vehicle looking its best.
                            </p>
                            <p class="pb-4">
                                We continuously strive to offer a comprehensive range of services that cater to your needs, making your parking experience with EveryonesParking both comfortable and convenient.
                            </p>
                        </div>
                    </div>

                    <div class="faq-item border-b border-gray-200 pb-4">
                        <button
                            class="faq-toggle w-full flex justify-between items-center text-left text-lg font-semibold text-gray-900 py-4">
                            Are EveryonesParking facilities accessible for individuals with disabilities?
                            <svg
                                class="faq-icon w-5 h-5 transition-transform duration-300 origin-center"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>

                        </button>
                        <div class="faq-content max-h-0 overflow-hidden transition-all duration-300 text-gray-600">
                            <p class="pb-2">
                                EveryonesParking is dedicated to providing fully accessible parking facilities that comply with all relevant accessibility standards. Our spaces are designed to ensure equal access for individuals with disabilities, including conveniently located accessible parking spots and clear, obstacle-free pathways.
                            </p>
                            <p class="pb-4">
                                Our trained staff are also available to provide assistance as needed, ensuring a welcoming and inclusive experience for all customers.
                            </p>
                        </div>
                    </div>

                    <!-- View More Button -->
                    <a
                        class="mt-8 px-8 py-3 rounded-xl bg-[#6ae6fc] text-gray-900 font-semibold shadow-md hover:bg-cyan-400 transition"
                        href="/faq.php">
                        View More
                    </a>

                </div>
            </div>

            <!-- RIGHT: Image -->
            <div class="relative hidden lg:flex justify-center">
                <img
                    src="/images/faq-homepage.jpg"
                    class="w-[420px] h-[540px] object-cover rounded-3xl shadow-xl"
                    alt="FAQ image" />
            </div>

        </div>
    </section>

    <script>
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const item = button.parentElement;
                const content = item.querySelector('.faq-content');
                const icon = button.querySelector('.faq-icon');

                // Close others (accordion style)
                document.querySelectorAll('.faq-item').forEach(other => {
                    if (other !== item) {
                        other.querySelector('.faq-content').style.maxHeight = null;
                        other.querySelector('.faq-icon').classList.remove('rotate-180');
                    }
                });

                // Toggle current
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    icon.classList.remove('rotate-180');
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    icon.classList.add('rotate-180');
                }
            });
        });
    </script>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>
</body>

</html>