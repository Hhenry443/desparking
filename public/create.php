<?php

session_start();

// If not logged in, kick them out
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

    <script>
        const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>"
    </script>
</head>
<body class="min-h-screen bg-gray-100 pt-20">

<nav class="w-full h-16 bg-white/80 backdrop-blur-md shadow-md fixed top-0 left-0 z-50 flex items-center justify-between px-6">
    <div class="flex items-center space-x-2">
        <a href="/" class="text-xl font-semibold text-gray-800">DesParking</a>
    </div>
    <div class="hidden md:flex space-x-6 text-gray-700 font-medium">
        <a href="/" class="hover:text-green-600 transition">Back to Map</a>
        <a href="/account.php" class="hover:text-green-600 transition">Account</a>
    </div>
</nav>

<div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-lg p-8 mt-10 mb-10">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            Create a Car Park
        </h1>
        <p class="text-gray-500 mt-2">
            Add a new parking space and start accepting bookings.
        </p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <p class="font-bold">Success!</p>
            <p class="text-sm">Car park created successfully. <a href="/carpark.php?id=<?= htmlspecialchars($_GET['id'] ?? '') ?>" class="underline">View car park</a></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <p class="font-bold">Error</p>
            <p class="text-sm"><?= htmlspecialchars(urldecode($_GET['error'])) ?></p>
        </div>
    <?php endif; ?>

    <form action="/php/api/index.php?id=insertCarpark" method="POST" class="space-y-6" id="create-form">

        <!-- Car Park Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Car Park Name *
            </label>
            <input
                type="text"
                name="carpark_name"
                required
                placeholder="e.g. Queens Road Space"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea
                name="carpark_description"
                rows="3"
                placeholder="e.g. Secure parking with CCTV, close to city centre"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ></textarea>
        </div>

        <!-- Address with Location Search -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Address *
            </label>
            <div class="relative">
                <input
                    type="text"
                    id="address-search"
                    placeholder="Start typing an address..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
                <div id="address-results" class="absolute w-full bg-white border border-gray-300 rounded-lg mt-1 hidden shadow-lg z-10 max-h-60 overflow-y-auto"></div>
            </div>
            <input type="hidden" name="carpark_address" id="carpark_address" required>
            <input type="hidden" name="carpark_lat" id="carpark_lat" required>
            <input type="hidden" name="carpark_lng" id="carpark_lng" required>
            <p class="text-xs text-gray-500 mt-2" id="selected-location">Search and select a location from the dropdown</p>
        </div>

        <!-- Mini Map Preview -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Location Preview
            </label>
            <div id="preview-map" class="w-full h-64 rounded-lg border border-gray-300 bg-gray-100 flex items-center justify-center">
                <p class="text-gray-500 text-sm">Select an address to preview location</p>
            </div>
        </div>

        <!-- Capacity -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Capacity (number of spaces) *
            </label>
            <input
                type="number"
                name="carpark_capacity"
                required
                min="1"
                placeholder="e.g. 10"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>

        <!-- Features -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Features
            </label>
            <textarea
                name="carpark_features"
                rows="2"
                placeholder="e.g. CCTV, Covered, EV Charging, Disabled Access"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ></textarea>
        </div>

        <hr class="my-8">

        <!-- Pricing Rates Section -->
        <div>
            <h2 class="text-xl font-semibold text-gray-800 mb-2">Pricing Rates</h2>
            <p class="text-sm text-gray-600 mb-4">Add pricing tiers for different durations. You can add more rates after creating the car park.</p>
            
            <div id="rates-container" class="space-y-3">
                <!-- Rate rows will be added here -->
            </div>

            <button
                type="button"
                onclick="addRateRow()"
                class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-4 rounded-lg transition"
            >
                + Add Rate
            </button>
        </div>

        <!-- Submit -->
        <div class="pt-6 flex gap-4">
            <button
                type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition shadow-md"
            >
                Create Car Park
            </button>
            <a
                href="/"
                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 rounded-lg transition text-center"
            >
                Cancel
            </a>
        </div>

    </form>

</div>

<script>
let map = null;
let marker = null;
let searchTimeout = null;
let rateCounter = 0;

// Address search functionality
const addressSearch = document.getElementById('address-search');
const addressResults = document.getElementById('address-results');

addressSearch.addEventListener('input', function(e) {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();
    
    if (query.length < 3) {
        addressResults.classList.add('hidden');
        return;
    }
    
    searchTimeout = setTimeout(() => searchAddress(query), 300);
});

async function searchAddress(query) {
    try {
        const response = await fetch(
            `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?access_token=${MAPBOX_TOKEN}&limit=5`
        );
        const data = await response.json();
        
        if (data.features && data.features.length > 0) {
            displayResults(data.features);
        } else {
            addressResults.innerHTML = '<div class="p-3 text-gray-500 text-sm">No results found</div>';
            addressResults.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Geocoding error:', error);
    }
}

function displayResults(features) {
    addressResults.innerHTML = features.map(feature => `
        <div class="p-3 hover:bg-gray-100 cursor-pointer border-b last:border-b-0" onclick='selectLocation(${JSON.stringify(feature)})'>
            <p class="font-medium text-gray-800 text-sm">${feature.text}</p>
            <p class="text-xs text-gray-500">${feature.place_name}</p>
        </div>
    `).join('');
    addressResults.classList.remove('hidden');
}

function selectLocation(feature) {
    const [lng, lat] = feature.center;
    
    document.getElementById('carpark_address').value = feature.place_name;
    document.getElementById('carpark_lat').value = lat;
    document.getElementById('carpark_lng').value = lng;
    document.getElementById('address-search').value = feature.place_name;
    document.getElementById('selected-location').textContent = `Selected: ${feature.place_name}`;
    document.getElementById('selected-location').classList.add('text-green-600');
    
    addressResults.classList.add('hidden');
    
    // Initialize or update map
    updateMapPreview(lat, lng);
}

function updateMapPreview(lat, lng) {
    const mapContainer = document.getElementById('preview-map');
    
    if (!map) {
        mapContainer.innerHTML = '';
        map = new mapboxgl.Map({
            container: 'preview-map',
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [lng, lat],
            zoom: 15,
            accessToken: MAPBOX_TOKEN
        });
        
        marker = new mapboxgl.Marker({ color: '#22c55e' })
            .setLngLat([lng, lat])
            .addTo(map);
    } else {
        map.setCenter([lng, lat]);
        marker.setLngLat([lng, lat]);
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#address-search') && !e.target.closest('#address-results')) {
        addressResults.classList.add('hidden');
    }
});

// Rates management
function addRateRow() {
    rateCounter++;
    const container = document.getElementById('rates-container');
    const row = document.createElement('div');
    row.className = 'flex gap-4 items-center';
    row.innerHTML = `
        <div class="flex-1">
            <input
                type="number"
                name="rate_durations[]"
                placeholder="Duration (mins)"
                min="1"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>
        <div class="flex-1">
            <input
                type="number"
                name="rate_prices[]"
                placeholder="Price (£)"
                min="0"
                step="0.01"
                class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>
        <button
            type="button"
            onclick="this.parentElement.remove()"
            class="text-red-600 hover:text-red-800 font-medium px-3 py-2"
        >
            Remove
        </button>
    `;
    container.appendChild(row);
}

// Add initial rate row
addRateRow();
</script>

</body>
</html>