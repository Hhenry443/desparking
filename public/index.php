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
    <script>const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>";</script>
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

                <!-- Search box -->
                <form
                    id="homepage-search-form"
                    class="bg-white rounded-3xl p-6 shadow-[0_0_25px_rgba(0,0,0,0.15)] w-full mt-10 max-w-xl mx-auto lg:mx-0"
                    action="/map.php"
                    method="GET">

                    <!-- Toggle -->
                    <div class="flex w-full gap-3 mb-6">
                        <a href="/map.php"
                            class="w-1/2 py-2 rounded-lg bg-gray-100 text-gray-700 text-sm font-semibold text-center hover:bg-[#6ae6fc] transition">
                            Monthly
                        </a>
                        <button type="button"
                            class="w-1/2 py-2 rounded-lg bg-[#6ae6fc] text-gray-800 text-sm font-semibold">
                            Hourly / Daily
                        </button>
                    </div>

                    <input type="hidden" name="booking_type" value="hourly">

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
                            <select id="home-from-time" name="entry_time"
                                class="w-28 py-3 px-3 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                            </select>
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
                            <select id="home-until-time" name="exit_time"
                                class="w-28 py-3 px-3 rounded-xl bg-gray-100 text-gray-700 text-sm font-medium border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                            </select>
                        </div>
                        <input id="home-until-date" type="hidden" name="exit_date" />
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
                        const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;

                        const today = new Date();
                        today.setHours(0, 0, 0, 0);
                        const todayStr = fmtDate(today);
                        const tomorrowDate = new Date(today);
                        tomorrowDate.setDate(today.getDate() + 1);
                        const tomorrowStr = fmtDate(tomorrowDate);

                        const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                        const DAYS   = ['Mo','Tu','We','Th','Fr','Sa','Su'];

                        function friendlyLabel(dateStr) {
                            if (dateStr === todayStr)    return 'Today';
                            if (dateStr === tomorrowStr) return 'Tomorrow';
                            return new Date(dateStr + 'T00:00:00')
                                .toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' });
                        }

                        // Build a calendar popup element and attach it to <body>
                        function buildCalendarEl(id) {
                            const el = document.createElement('div');
                            el.id = id;
                            el.className = [
                                'fixed z-[9999] hidden select-none',
                                'bg-white rounded-2xl border border-gray-100',
                                'shadow-[0_8px_32px_rgba(0,0,0,0.14)]',
                                'p-4 w-72',
                            ].join(' ');
                            el.innerHTML = `
                                <div class="flex items-center justify-between mb-4">
                                    <button type="button" data-action="prev"
                                        class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                                        <i class="fa-solid fa-chevron-left text-xs"></i>
                                    </button>
                                    <span data-role="title" class="text-sm font-bold text-[#060745]"></span>
                                    <button type="button" data-action="next"
                                        class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center text-gray-500 transition">
                                        <i class="fa-solid fa-chevron-right text-xs"></i>
                                    </button>
                                </div>
                                <div class="grid grid-cols-7 mb-2">
                                    ${DAYS.map(d => `<span class="text-center text-xs font-semibold text-gray-400 pb-1">${d}</span>`).join('')}
                                </div>
                                <div data-role="grid" class="grid grid-cols-7 gap-y-0.5"></div>
                            `;
                            document.body.appendChild(el);
                            return el;
                        }

                        function makePicker(triggerId, labelId, hiddenId) {
                            const trigger = document.getElementById(triggerId);
                            const label   = document.getElementById(labelId);
                            const hidden  = document.getElementById(hiddenId);
                            const calEl   = buildCalendarEl(triggerId + '-cal');

                            const titleEl = calEl.querySelector('[data-role="title"]');
                            const gridEl  = calEl.querySelector('[data-role="grid"]');

                            let viewYear, viewMonth, selectedStr;
                            let isOpen = false;

                            function position() {
                                const r = trigger.getBoundingClientRect();
                                calEl.style.top  = (r.bottom + 6) + 'px';
                                // Keep calendar within viewport
                                let left = r.left;
                                const calW = 288; // w-72
                                if (left + calW > window.innerWidth - 8) {
                                    left = window.innerWidth - calW - 8;
                                }
                                calEl.style.left = left + 'px';
                            }

                            function render() {
                                titleEl.textContent = `${MONTHS[viewMonth]} ${viewYear}`;

                                // Monday-start: Sun=0 → offset 6, Mon=1 → offset 0, etc.
                                const firstDow  = new Date(viewYear, viewMonth, 1).getDay();
                                const offset    = (firstDow === 0) ? 6 : firstDow - 1;
                                const daysTotal = new Date(viewYear, viewMonth + 1, 0).getDate();

                                let html = '';
                                for (let i = 0; i < offset; i++) html += '<div></div>';

                                for (let d = 1; d <= daysTotal; d++) {
                                    const ds = `${viewYear}-${pad(viewMonth + 1)}-${pad(d)}`;
                                    const isPast     = ds < todayStr;
                                    const isToday    = ds === todayStr;
                                    const isSelected = ds === selectedStr;

                                    let cls = 'h-8 w-8 mx-auto flex items-center justify-center rounded-full text-xs transition ';
                                    if (isPast) {
                                        cls += 'text-gray-300 cursor-default';
                                    } else if (isSelected) {
                                        cls += 'bg-[#6ae6fc] text-gray-900 font-bold cursor-pointer';
                                    } else if (isToday) {
                                        cls += 'ring-2 ring-[#6ae6fc] text-gray-800 font-semibold hover:bg-[#6ae6fc]/20 cursor-pointer';
                                    } else {
                                        cls += 'text-gray-700 hover:bg-gray-100 cursor-pointer font-medium';
                                    }

                                    html += isPast
                                        ? `<div class="${cls}">${d}</div>`
                                        : `<div class="${cls}" data-date="${ds}">${d}</div>`;
                                }
                                gridEl.innerHTML = html;
                            }

                            function open() {
                                isOpen = true;
                                position();
                                calEl.classList.remove('hidden');
                                render();
                            }

                            function close() {
                                isOpen = false;
                                calEl.classList.add('hidden');
                            }

                            function select(dateStr) {
                                selectedStr = dateStr;
                                hidden.value = dateStr;
                                label.textContent = friendlyLabel(dateStr);
                                close();
                            }

                            function init(dateStr) {
                                selectedStr = dateStr;
                                hidden.value = dateStr;
                                label.textContent = friendlyLabel(dateStr);
                                const d = new Date(dateStr + 'T00:00:00');
                                viewYear  = d.getFullYear();
                                viewMonth = d.getMonth();
                            }

                            // Events
                            trigger.addEventListener('click', (e) => {
                                e.stopPropagation();
                                isOpen ? close() : open();
                            });

                            calEl.querySelector('[data-action="prev"]').addEventListener('click', (e) => {
                                e.stopPropagation();
                                if (--viewMonth < 0) { viewMonth = 11; viewYear--; }
                                render();
                            });

                            calEl.querySelector('[data-action="next"]').addEventListener('click', (e) => {
                                e.stopPropagation();
                                if (++viewMonth > 11) { viewMonth = 0; viewYear++; }
                                render();
                            });

                            gridEl.addEventListener('click', (e) => {
                                const cell = e.target.closest('[data-date]');
                                if (cell) select(cell.dataset.date);
                            });

                            window.addEventListener('resize', () => { if (isOpen) position(); });

                            return { init, close, get isOpen() { return isOpen; } };
                        }

                        // Build time selects (30-min slots)
                        function buildTimeOptions(selectId, selectedHour) {
                            const sel = document.getElementById(selectId);
                            for (let h = 0; h < 24; h++) {
                                for (const m of [0, 30]) {
                                    const label = new Date(2000, 0, 1, h, m)
                                        .toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
                                    const opt = new Option(label, `${pad(h)}:${pad(m)}`);
                                    if (h === selectedHour && m === 0) opt.selected = true;
                                    sel.appendChild(opt);
                                }
                            }
                        }

                        const now = new Date();
                        buildTimeOptions('home-from-time',  now.getHours());
                        buildTimeOptions('home-until-time', now.getHours());

                        const pickerFrom  = makePicker('from-date-trigger',  'from-date-label',  'home-from-date');
                        const pickerUntil = makePicker('until-date-trigger', 'until-date-label', 'home-until-date');

                        pickerFrom.init(todayStr);
                        pickerUntil.init(tomorrowStr);

                        // Close on outside click
                        document.addEventListener('click', () => {
                            pickerFrom.close();
                            pickerUntil.close();
                        });

                        // Form validation
                        document.getElementById('homepage-search-form').addEventListener('submit', function(e) {
                            if (!document.getElementById('home-from-date').value ||
                                !document.getElementById('home-until-date').value) {
                                e.preventDefault();
                                alert('Please select arrival and departure dates.');
                            }
                        });
                    })();
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
                            if (q.length < 3) { results.classList.add('hidden'); return; }
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
                                results.innerHTML = data.features.map(f => `
                                    <div class="px-4 py-3 hover:bg-gray-50 cursor-pointer transition border-b border-gray-100 last:border-0"
                                         onclick='selectLocation(${JSON.stringify(f)})'>
                                        <p class="text-sm font-semibold text-gray-800">${f.text}</p>
                                        <p class="text-xs text-gray-500">${f.place_name}</p>
                                    </div>
                                `).join('');
                                results.classList.remove('hidden');
                            } catch { results.classList.add('hidden'); }
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
                                    const { latitude: lat, longitude: lng } = pos.coords;
                                    try {
                                        const res = await fetch(
                                            `https://api.mapbox.com/search/geocode/v6/reverse?longitude=${lng}&latitude=${lat}&access_token=${MAPBOX_TOKEN}`
                                        );
                                        const data = await res.json();
                                        const feature = data.features && data.features[0];
                                        input.value = feature
                                            ? (feature.properties.full_address || feature.properties.name)
                                            : `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                                    } catch {
                                        input.value = `${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                                    }
                                    geoBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                                },
                                () => {
                                    geoBtn.innerHTML = '<i class="fa-solid fa-location-crosshairs"></i>';
                                    alert('Could not get your location. Please check your browser permissions.');
                                },
                                { timeout: 10000 }
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



    <section id="section-2" class="bg-gray-100 py-10">
        <!-- Dog-leg strip -->
        <div class="absolute inset-x-0 pointer-events-none hidden lg:block">
            <!-- Vertical down from left -->
            <div class="absolute left-[20%] top-0 w-10 h-50 bg-gray-300"></div>

            <!-- Horizontal to step 2 -->
            <div class="absolute left-[20%] top-40 w-[60%] h-10 bg-gray-300"></div>

            <!-- Vertical down -->
            <div class="absolute left-[80%] top-40 w-10 h-40 bg-gray-300"></div>
        </div>

        <div class="max-w-6xl mx-auto px-6 relative ">

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
    <section id="section-3" class="bg-white pt-16">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-20 items-start">

            <!-- LEFT: Images -->
            <div class="relative hidden lg:flex justify-center items-start pb-32">

                <!-- Left-side background image, anchored to bottom of section -->
                <div class="absolute bottom-0 right-1/2 w-[480px] opacity-5 z-0 pointer-events-none">
                    <img
                        src="/images/desparking-icon.png"
                        class="w-full grayscale object-contain"
                        alt="" />
                </div>

                <!-- Back image (CAR) – aligned with "The Benefits" text -->
                <img
                    src="/images/homepage-image-4.jpg"
                    class="w-[300px] h-[420px] object-cover rounded-2xl shadow-lg absolute left-0 top-0 border-white border-[6px]"
                    alt="" />

                <!-- Front image (GIRL) – aligned with bottom of last benefit item -->
                <img
                    src="/images/homepage-image-3.jpg"
                    class="w-[300px] h-[420px] object-cover rounded-2xl shadow-xl relative z-10 ml-32 mt-40 border-white border-[6px]"
                    alt="" />

            </div>


            <!-- RIGHT: Text content -->
            <div>
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
                                Our customer support team is available 24/7 to assist you with any questions or concerns. You can reach us by email at support@desparking.uk.
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