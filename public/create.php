<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Create Car Park · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.css" rel="stylesheet">
    <script src="https://api.mapbox.com/mapbox-gl-js/v3.17.0-beta.1/mapbox-gl.js"></script>
    <link href="/css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
    <script>
        const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>";
    </script>
</head>

<body class="min-h-screen bg-[#ebebeb] pt-24">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 mb-12">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Create a Car Park</h1>
            <p class="text-gray-500 text-sm mt-1">
                Add a new parking space and start accepting bookings.
            </p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-lg text-sm">
                Car park created successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <form action="/php/api/index.php?id=insertCarpark" method="POST" class="space-y-6" id="create-form">

            <!-- Name -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Car Park Name *</label>
                <input type="text" name="carpark_name" required
                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                <textarea name="carpark_description" rows="3"
                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"></textarea>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Address *</label>
                <div class="relative">
                    <input type="text" id="address-search"
                        placeholder="Search for an address…"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

                    <div id="address-results"
                        class="absolute w-full bg-white rounded-lg shadow-[0_6px_18px_rgba(0,0,0,0.15)]
                           mt-1 hidden z-10 max-h-60 overflow-y-auto border border-gray-200"></div>
                </div>

                <input type="hidden" name="carpark_address" id="carpark_address" required>
                <input type="hidden" name="carpark_lat" id="carpark_lat" required>
                <input type="hidden" name="carpark_lng" id="carpark_lng" required>

                <p id="selected-location" class="text-xs text-gray-500 mt-2">
                    Search and select a location from the dropdown.
                </p>
            </div>

            <!-- Map Preview -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Location Preview</label>
                <div id="preview-map"
                    class="w-full h-64 rounded-2xl border border-gray-200 shadow-sm bg-gray-100
                       flex items-center justify-center">
                    <p class="text-gray-500 text-sm">Select an address to preview location</p>
                </div>
            </div>

            <!-- Capacity -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Capacity *</label>
                <input type="number" name="carpark_capacity" required min="1"
                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
            </div>

            <!-- Features -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-3">Features</label>

                <div class="grid grid-cols-2 gap-2">
                    <?php
                    $features = ['Covered', 'Security', 'EV Charging', '24/7 Access', 'Indoor', 'Outdoor'];
                    foreach ($features as $feature): ?>
                        <div class="flex items-center">
                            <input type="checkbox" name="features[]" value="<?= htmlspecialchars($feature) ?>" id="feature-<?= strtolower(str_replace(' ', '-', $feature)) ?>" class="mr-2">
                            <label for="feature-<?= strtolower(str_replace(' ', '-', $feature)) ?>" class="text-sm"><?= htmlspecialchars($feature) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Monthly Fee Toggle -->
            <div class="bg-blue-50 p-4 rounded-xl">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-semibold text-gray-800">Monthly Fee</h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="monthly-toggle" name="monthly-toggle" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Monthly Fee</span>
                    </label>
                </div>

                <div id="monthly-fee-input" class="hidden">
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Monthly Fee (£)</label>
                        <input type="number" name="monthly_fee" placeholder="Enter monthly fee"
                            class="w-full py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Billing Period</label>
                        <select name="billing_period" class="w-full py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Rates Section -->
            <div class="bg-gray-50 p-4 rounded-xl" id="ratesInput">
                <h3 class="font-semibold text-gray-800 mb-3">Pricing Rates</h3>

                <div id="rates-container">
                    <div class="flex gap-4 items-center bg-gray-50 p-3 rounded-xl shadow-sm mb-3">
                        <input type="number" name="rate_durations[]" placeholder="Duration (mins)"
                            class="flex-1 py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

                        <input type="number" name="rate_prices[]" placeholder="Price (£)"
                            class="flex-1 py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

                        <button type="button" onclick="this.parentElement.remove()"
                            class="py-2 px-3 rounded-lg bg-red-50 text-red-600 text-xs font-bold
                               hover:bg-red-100 transition">
                            Remove
                        </button>
                    </div>
                </div>

                <button type="button" id="add-rate-btn" class="mt-2 py-2 px-4 bg-gray-200 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-300 transition">
                    Add Rate
                </button>
            </div>

            <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                Create Car Park
            </button>
        </form>
    </div>

    <script>
        let map = null;
        let marker = null;
        let searchTimeout = null;

        // Address search
        const addressSearch = document.getElementById('address-search');
        const addressResults = document.getElementById('address-results');

        addressSearch.addEventListener('input', e => {
            clearTimeout(searchTimeout);
            const q = e.target.value.trim();
            if (q.length < 3) {
                addressResults.classList.add('hidden');
                return;
            }
            searchTimeout = setTimeout(() => searchAddress(q), 300);
        });

        async function searchAddress(query) {
            const res = await fetch(
                `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&limit=5`
            );
            const data = await res.json();

            if (!data.features || !data.features.length) {
                addressResults.innerHTML = `<div class="p-3 text-gray-500 text-sm">No results found</div>`;
                addressResults.classList.remove('hidden');
                return;
            }

            addressResults.innerHTML = data.features.map(f => `
        <div class="p-3 hover:bg-gray-100 cursor-pointer transition"
             onclick='selectLocation(${JSON.stringify(f)})'>
            <p class="text-sm font-semibold text-gray-800">${f.text}</p>
            <p class="text-xs text-gray-500">${f.place_name}</p>
        </div>
    `).join('');
            addressResults.classList.remove('hidden');
        }

        function selectLocation(feature) {
            const [lng, lat] = feature.center;

            document.getElementById('carpark_address').value = feature.place_name;
            document.getElementById('carpark_lat').value = lat;
            document.getElementById('carpark_lng').value = lng;
            addressSearch.value = feature.place_name;

            const label = document.getElementById('selected-location');
            label.textContent = `Selected: ${feature.place_name}`;
            label.classList.remove('text-gray-500');
            label.classList.add('text-[#6ae6fc]', 'font-semibold');

            addressResults.classList.add('hidden');
            updateMapPreview(lat, lng);
        }

        function updateMapPreview(lat, lng) {
            if (!map) {
                map = new mapboxgl.Map({
                    container: 'preview-map',
                    style: 'mapbox://styles/mapbox/streets-v12',
                    center: [lng, lat],
                    zoom: 15,
                    accessToken: MAPBOX_TOKEN
                });

                marker = new mapboxgl.Marker({
                        color: '#6ae6fc'
                    })
                    .setLngLat([lng, lat])
                    .addTo(map);
            } else {
                map.setCenter([lng, lat]);
                marker.setLngLat([lng, lat]);
            }
        }

        // Close dropdown
        document.addEventListener('click', e => {
            if (!e.target.closest('#address-search') && !e.target.closest('#address-results')) {
                addressResults.classList.add('hidden');
            }
        });

        // Monthly fee toggle
        document.getElementById('monthly-toggle').addEventListener('change', function() {
            const monthlyFeeInput = document.getElementById('monthly-fee-input');
            const ratesInput = document.getElementById('ratesInput');

            if (this.checked) {
                monthlyFeeInput.classList.remove('hidden');
                ratesInput.classList.add("hidden")
            } else {
                monthlyFeeInput.classList.add('hidden');
                ratesInput.classList.remove("hidden")
            }
        });

        // Rates
        function addRateRow() {
            const container = document.getElementById('rates-container');
            const row = document.createElement('div');
            row.className = 'flex gap-4 items-center bg-gray-50 p-3 rounded-xl shadow-sm mb-3';

            row.innerHTML = `
        <input type="number" name="rate_durations[]" placeholder="Duration (mins)"
            class="flex-1 py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                   border border-gray-300 focus:outline-none
                   focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

        <input type="number" name="rate_prices[]" placeholder="Price (£)"
            class="flex-1 py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                   border border-gray-300 focus:outline-none
                   focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

        <button type="button" onclick="this.parentElement.remove()"
            class="py-2 px-3 rounded-lg bg-red-50 text-red-600 text-xs font-bold
                   hover:bg-red-100 transition">
            Remove
        </button>
    `;
            container.appendChild(row);
        }

        // Add rate button event
        document.getElementById('add-rate-btn').addEventListener('click', addRateRow);

        // Initialize with one rate row
        addRateRow();
    </script>

</body>

</html>