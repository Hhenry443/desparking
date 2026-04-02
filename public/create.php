<?php
session_start();

$title = "Create a Carpark";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="min-h-screen bg-[#ebebeb] pt-24">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 mb-12">
        <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-6 sm:p-8">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Create a Car Park</h1>
                <p class="text-gray-500 text-sm mt-1">
                    Add a new parking space and start accepting bookings.
                </p>
            </div>

            <?php if (isset($_GET['submitted'])): ?>
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm">
                    <p class="font-semibold mb-0.5">Submitted for approval</p>
                    <p>Your car park has been submitted and is awaiting review by our team. We'll be in touch if we need any further information.</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                    <?= htmlspecialchars(urldecode($_GET['error'])) ?>
                </div>
            <?php endif; ?>

            <form action="/php/api/index.php?id=insertCarpark" method="POST" enctype="multipart/form-data" class="space-y-6" id="create-form">

                <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                    <!-- Affiliate toggle — admin only -->
                    <div class="bg-[#060745] text-white p-4 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-semibold text-sm">Affiliate Listing</p>
                                <p class="text-xs text-white/60 mt-0.5">Booking is handled externally — no pricing needed.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="affiliate-toggle" name="is_affiliate" class="sr-only peer">
                                <div class="w-11 h-6 bg-white/20 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#6ae6fc]"></div>
                            </label>
                        </div>

                        <div id="affiliate-url-section" class="hidden mt-4">
                            <label class="block text-xs font-semibold text-white/70 mb-1">Affiliate URL *</label>
                            <input type="url" name="carpark_affiliate_url" id="affiliate-url-input"
                                placeholder="https://partner-site.com/book"
                                class="w-full py-3 px-4 rounded-lg bg-white/10 text-white placeholder-white/40 text-sm
                               border border-white/20 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                        </div>
                    </div>
                <?php endif; ?>

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

                <!-- Access Instructions -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Access Instructions *</label>
                    <p class="text-xs text-gray-400 mb-2">Explain how bookers access the car park — e.g. gate codes, key collection, entry points. This will be included in their booking confirmation email.</p>
                    <textarea name="access_instructions" rows="4" required
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"
                        placeholder="e.g. Enter via the side gate on Elm Street. The code is 1234. Park in any unmarked bay."></textarea>
                </div>

                <!-- Owner Contact Details -->
                <div class="bg-gray-50 p-4 rounded-xl">
                    <h3 class="font-semibold text-gray-800 mb-3">Your Contact Details</h3>
                    <p class="text-xs text-gray-500 mb-4">These details will be visible to admins, bookers will be sent contact details upon making a successful booking (not address).</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Phone Number</label>
                            <input type="tel" name="owner_phone" placeholder="e.g. 07700 900000"
                                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Your Address</label>
                            <input type="text" name="owner_address" placeholder="e.g. 12 Example Street, Norwich"
                                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Car Park Address *</label>
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
                        $features = [
                            'CCTV',
                            'Motorbike Ground Anchor',
                            'On-site Staff',
                            'Parking Post (bollards)',
                            'Security Alarm',
                            'Security Gates',
                            'Security Lighting',
                            'Smoke Detector',
                            'Electric Vehicle Car Charging',
                            'Fire Alarm',
                            'Lift Access',
                            'Private Entrance',
                            'Undercover',
                        ];
                        foreach ($features as $feature): ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="features[]" value="<?= htmlspecialchars($feature) ?>" id="feature-<?= strtolower(str_replace(' ', '-', $feature)) ?>" class="mr-2">
                                <label for="feature-<?= strtolower(str_replace(' ', '-', $feature)) ?>" class="text-sm"><?= htmlspecialchars($feature) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Monthly Fee Toggle -->
                <div class="bg-blue-50 p-4 rounded-xl" id="monthly-fee-section">
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
                            <input type="number" name="monthly_fee" placeholder="Enter monthly fee" step="0.01" min="0"
                                class="w-full py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Billing Period</label>
                            <input type="hidden" name="billing_period" value="monthly">
                            <p class="text-sm text-gray-600">Monthly</p>
                        </div>
                    </div>
                </div>

                <!-- Rates Section -->
                <div class="bg-gray-50 p-4 rounded-xl" id="ratesInput">
                    <h3 class="font-semibold text-gray-800 mb-3">Pricing Rates</h3>

                    <div id="rates-container">
                        <div class="flex flex-wrap gap-2 items-center bg-gray-50 p-3 rounded-xl shadow-sm mb-3">
                            <input type="number" name="rate_durations[]" placeholder="Duration (mins)"
                                class="flex-1 min-w-[120px] py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

                            <input type="number" name="rate_prices[]" placeholder="Price (£)" step="0.01" min="0"
                                class="flex-1 min-w-[100px] py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
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

                <!-- Space Size -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Space Size *</label>
                    <select name="space_size" required
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        <option value="small">Small – suitable for compact/city cars</option>
                        <option value="medium" selected>Medium – suits most standard cars</option>
                        <option value="large">Large – suitable for SUVs and vans</option>
                    </select>
                </div>

                <!-- Type of Space -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Type of Space *</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" id="space-type-cards">
                        <?php
                        $spaceTypes = [
                            'car'      => ['label' => 'Car Space',       'icon' => 'fa-car'],
                            'garage'   => ['label' => 'Garage',          'icon' => 'fa-warehouse'],
                            'motorbike'=> ['label' => 'Motorbike Space', 'icon' => 'fa-motorcycle'],
                            'multiple' => ['label' => 'Multiple Spaces', 'icon' => 'fa-square-parking'],
                        ];
                        foreach ($spaceTypes as $val => $info): ?>
                            <label class="space-type-card cursor-pointer rounded-xl border-2 border-gray-200 bg-gray-50 p-3 flex flex-col items-center gap-2 text-center hover:border-[#6ae6fc] transition has-[:checked]:border-[#6ae6fc] has-[:checked]:bg-[#6ae6fc]/10">
                                <input type="radio" name="space_type" value="<?= $val ?>" class="sr-only" <?= $val === 'car' ? 'checked' : '' ?>>
                                <i class="fa-solid <?= $info['icon'] ?> text-xl text-[#060745]"></i>
                                <span class="text-xs font-semibold text-gray-700"><?= $info['label'] ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Access & Availability -->
                <div class="bg-gray-50 p-4 rounded-xl">
                    <h3 class="font-semibold text-gray-800 mb-3">Access &amp; Availability</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Requires a key or fob for access</p>
                                <p class="text-xs text-gray-500">Bookers will be told they need to collect a key from you</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="requires_key" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Available on weekends</p>
                                <p class="text-xs text-gray-500">Allow bookings on Saturdays and Sundays</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="weekend_available" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Allocated space</p>
                                <p class="text-xs text-gray-500">This space has a designated bay number or assigned spot</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_allocated" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">Available immediately</p>
                                    <p class="text-xs text-gray-500">Space is ready to accept bookings right away</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="available_immediately" id="available-immediately-toggle" class="sr-only peer" checked>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                            <div id="available-from-section" class="hidden">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Available from *</label>
                                <input type="date" name="available_from" id="available-from-input"
                                    class="w-full py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                                           border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Time Restrictions -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Time Restrictions</label>
                    <p class="text-xs text-gray-400 mb-2">Specify any hours during which the space is not available, e.g. "Weekdays 9am – 5pm only" or "Not available before 8am or after 10pm".</p>
                    <input type="text" name="time_restrictions" maxlength="500"
                        placeholder="e.g. Weekdays 8am – 6pm only"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                </div>

                <!-- Minimum Booking Time -->
                <div id="min-booking-section">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Minimum Booking Duration (minutes) *</label>
                    <input type="number" name="min_booking_minutes" value="30" min="1" required
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                       border border-gray-300 focus:outline-none
                       focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Bookings shorter than this will not be allowed.</p>
                </div>

                <!-- Photos -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Photos</label>
                    <div class="w-full rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:border-[#6ae6fc] transition"
                        onclick="document.getElementById('photo-input').click()">
                        <i class="fa-solid fa-camera text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Click to upload photos. It is reccomended to add multiple pictures of the space, and even screenhots from online maps to help people find your space <span class="text-xs">(JPEG, PNG, WebP — multiple allowed)</span></p>
                        <input type="file" id="photo-input" name="carpark_photos[]" multiple accept="image/jpeg,image/png,image/webp,image/gif" class="hidden"
                            onchange="previewPhotos(this)">
                    </div>
                    <div id="photo-preview" class="mt-3 flex flex-wrap gap-2"></div>
                </div>

                <!-- Unavailable Dates -->
                <div id="unavailable-dates-section" class="bg-gray-50 p-4 rounded-xl">
                    <h3 class="font-semibold text-gray-800 mb-1">Unavailable Dates</h3>
                    <p class="text-xs text-gray-500 mb-3">Add specific dates when your space will not be available. Bookers will not be able to book on these dates.</p>

                    <div class="mb-3">
                        <button type="button" id="unavail-date-trigger"
                            class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 border border-gray-200
                                   text-sm text-gray-700 hover:bg-gray-200 transition">
                            <i class="fa-regular fa-calendar text-[#6ae6fc]"></i>
                            <span id="unavail-date-label">Pick a date to block</span>
                        </button>
                        <input type="hidden" id="unavail-date-hidden">
                    </div>

                    <div id="unavail-dates-list" class="flex flex-wrap gap-2"></div>
                </div>

                <!-- Data Consent -->
                <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" name="data_consent" id="data-consent" required
                            class="mt-0.5 h-4 w-4 rounded border-gray-300 text-[#6ae6fc] focus:ring-[#6ae6fc]">
                        <span class="text-sm text-gray-700">
                            I consent to my personal information and parking space details being stored and used by EveryonesParking to facilitate bookings on this platform, in accordance with our
                            <a href="/privacy.php" class="text-[#060745] font-semibold underline" target="_blank">Privacy Policy</a>. *
                        </span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    Create Car Park
                </button>
            </form>
        </div>
    </div>

    <script>
        let map = null;
        let marker = null;
        let searchTimeout = null;

        const MAPBOX_TOKEN = "<?= getenv('MAPBOX_TOKEN') ?>"

        // Address search
        const addressSearch = document.getElementById('address-search');
        const addressResults = document.getElementById('address-results');

        addressSearch.addEventListener('input', e => {
            clearTimeout(searchTimeout);
            const q = e.target.value.trim();
            if (q.length < 0) {
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

            window._addressFeatures = data.features;
            addressResults.innerHTML = data.features.map((f, i) => `
        <div class="p-3 hover:bg-gray-100 cursor-pointer transition"
             onclick="selectLocation(window._addressFeatures[${i}])">
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

        // Affiliate toggle
        const affiliateToggle = document.getElementById('affiliate-toggle');
        if (affiliateToggle) {
            const affiliatePricingSections = [
                document.getElementById('monthly-fee-section'),
                document.getElementById('ratesInput'),
                document.getElementById('min-booking-section'),
            ];

            affiliateToggle.addEventListener('change', function() {
                const isAffiliate = this.checked;
                document.getElementById('affiliate-url-section').classList.toggle('hidden', !isAffiliate);
                document.getElementById('affiliate-url-input').required = isAffiliate;
                affiliatePricingSections.forEach(el => {
                    if (el) el.classList.toggle('hidden', isAffiliate);
                });
            });
        }

        // Available immediately toggle
        document.getElementById('available-immediately-toggle').addEventListener('change', function() {
            const section = document.getElementById('available-from-section');
            const input   = document.getElementById('available-from-input');
            section.classList.toggle('hidden', this.checked);
            input.required = !this.checked;
        });

        // Monthly fee toggle
        document.getElementById('monthly-toggle').addEventListener('change', function() {
            const monthlyFeeInput = document.getElementById('monthly-fee-input');
            const ratesInput = document.getElementById('ratesInput');
            const unavailSection = document.getElementById('unavailable-dates-section');

            if (this.checked) {
                monthlyFeeInput.classList.remove('hidden');
                ratesInput.classList.add('hidden');
                unavailSection.classList.add('hidden');
            } else {
                monthlyFeeInput.classList.add('hidden');
                ratesInput.classList.remove('hidden');
                unavailSection.classList.remove('hidden');
            }
        });

        // Rates
        function addRateRow(duration = '', price = '') {
            const container = document.getElementById('rates-container');
            const row = document.createElement('div');
            row.className = 'flex flex-wrap gap-2 items-center bg-gray-50 p-3 rounded-xl shadow-sm mb-3';

            row.innerHTML = `
        <input type="number" name="rate_durations[]" placeholder="Duration (mins)"
            value="${duration}"
            class="flex-1 min-w-[120px] py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
                   border border-gray-300 focus:outline-none
                   focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">

        <input type="number" name="rate_prices[]" placeholder="Price (£)" step="0.01" min="0"
            value="${price}"
            class="flex-1 min-w-[100px] py-2 px-3 rounded-lg bg-gray-200 text-gray-700 text-sm
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
        document.getElementById('add-rate-btn').addEventListener('click', () => addRateRow());

        // ── Unavailable dates ────────────────────────────────────────────────
        function addUnavailableDate(date) {
            if (!date) return;
            const list = document.getElementById('unavail-dates-list');

            // Prevent duplicates
            if (list.querySelector(`[data-date="${date}"]`)) return;

            const label = new Date(date + 'T00:00:00').toLocaleDateString('en-GB', {
                weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
            });

            const tag = document.createElement('div');
            tag.dataset.date = date;
            tag.className = 'flex items-center gap-1.5 bg-white border border-gray-200 rounded-lg px-3 py-1.5 text-sm shadow-sm';
            tag.innerHTML = `
                <i class="fa-regular fa-calendar text-[#6ae6fc] text-xs"></i>
                <span class="font-medium text-gray-700">${label}</span>
                <input type="hidden" name="unavailable_dates[]" value="${date}">
                <button type="button" onclick="removeUnavailableDate(this)"
                    class="text-gray-400 hover:text-red-500 transition ml-1">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            `;
            list.appendChild(tag);
        }

        function removeUnavailableDate(btn) {
            btn.closest('[data-date]').remove();
        }

        // Initialise custom date picker for unavailable dates
        (function() {
            const pad     = n => String(n).padStart(2, '0');
            const today   = new Date(); today.setHours(0, 0, 0, 0);
            const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

            window._unavailDatePicker = makeDatePicker(
                'unavail-date-trigger',
                'unavail-date-label',
                'unavail-date-hidden',
                function(dateStr) {
                    addUnavailableDate(dateStr);
                    document.getElementById('unavail-date-label').textContent = 'Add another date';
                },
                'above'
            );

            if (window._unavailDatePicker) {
                // seed viewYear/viewMonth so the calendar renders correctly on first open
                window._unavailDatePicker.select(fmtDate(today));
                // reset label and hidden — we don't want today pre-added as a blocked date
                document.getElementById('unavail-date-label').textContent = 'Pick a date to block';
                document.getElementById('unavail-date-hidden').value = '';
            }
        })();

        // ── localStorage persistence ──────────────────────────────────────────

        const FORM_KEY = 'desparking_create_form';

        function saveForm() {
            const form = document.getElementById('create-form');
            const data = {};

            form.querySelectorAll('input:not([type=file]):not([type=submit]), textarea, select').forEach(el => {
                if (!el.name || el.type === 'hidden') return;
                if (el.type === 'checkbox') {
                    if (el.name === 'features[]') {
                        (data.features = data.features || []);
                        if (el.checked) data.features.push(el.value);
                    } else {
                        data[el.name] = el.checked;
                    }
                } else {
                    data[el.name] = el.value;
                }
            });

            // Address display text
            data._addressDisplay = document.getElementById('address-search').value;
            data._carparkAddress = document.getElementById('carpark_address').value;
            data._carparkLat = document.getElementById('carpark_lat').value;
            data._carparkLng = document.getElementById('carpark_lng').value;

            // Dynamic rate rows
            const durations = [...form.querySelectorAll('input[name="rate_durations[]"]')].map(e => e.value);
            const prices = [...form.querySelectorAll('input[name="rate_prices[]"]')].map(e => e.value);
            data._rates = durations.map((d, i) => ({
                duration: d,
                price: prices[i] || ''
            }));

            // Unavailable dates
            data._unavailableDates = [...document.querySelectorAll('#unavail-dates-list [data-date]')]
                .map(el => el.dataset.date);

            localStorage.setItem(FORM_KEY, JSON.stringify(data));
        }

        function restoreForm() {
            const raw = localStorage.getItem(FORM_KEY);
            if (!raw) {
                addRateRow();
                return;
            }

            let data;
            try {
                data = JSON.parse(raw);
            } catch {
                addRateRow();
                return;
            }

            const form = document.getElementById('create-form');

            // Restore named fields
            Object.entries(data).forEach(([name, value]) => {
                if (name.startsWith('_') || name === 'features') return;
                const el = form.querySelector(`[name="${CSS.escape(name)}"]`);
                if (!el) return;
                if (el.type === 'checkbox') {
                    el.checked = !!value;
                    el.dispatchEvent(new Event('change'));
                } else {
                    el.value = value;
                }
            });

            // Restore feature checkboxes
            if (data.features) {
                form.querySelectorAll('input[name="features[]"]').forEach(el => {
                    el.checked = data.features.includes(el.value);
                });
            }

            // Restore address
            if (data._addressDisplay) {
                document.getElementById('address-search').value = data._addressDisplay;
                document.getElementById('carpark_address').value = data._carparkAddress || '';
                document.getElementById('carpark_lat').value = data._carparkLat || '';
                document.getElementById('carpark_lng').value = data._carparkLng || '';
                const lbl = document.getElementById('selected-location');
                lbl.textContent = 'Selected: ' + data._addressDisplay;
                lbl.classList.remove('text-gray-500');
                lbl.classList.add('text-[#6ae6fc]', 'font-semibold');
                if (data._carparkLat && data._carparkLng) {
                    updateMapPreview(parseFloat(data._carparkLat), parseFloat(data._carparkLng));
                }
            }

            // Restore dynamic rate rows
            document.getElementById('rates-container').innerHTML = '';
            if (data._rates && data._rates.length) {
                data._rates.forEach(r => addRateRow(r.duration, r.price));
            } else {
                addRateRow();
            }

            // Restore unavailable dates
            if (data._unavailableDates && data._unavailableDates.length) {
                data._unavailableDates.forEach(d => addUnavailableDate(d));
            }
        }

        // Clear saved data after successful submission
        <?php if (isset($_GET['submitted'])): ?>
            localStorage.removeItem(FORM_KEY);
        <?php endif; ?>

        // Restore on load, then start saving on any change
        restoreForm();
        document.getElementById('create-form').addEventListener('input', saveForm);
        document.getElementById('create-form').addEventListener('change', saveForm);

        // Photo preview
        function previewPhotos(input) {
            const preview = document.getElementById('photo-preview');
            preview.innerHTML = '';
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'h-24 w-24 object-cover rounded-lg border border-gray-200';
                    preview.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    </script>

</body>

</html>