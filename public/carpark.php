<?php
session_start();

$title = "Edit Carpark";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$carparkId = $_GET['id'] ?? null;
$isAdminOverride = isset($_GET['admin']) && $_GET['admin'] == '1' && $_SESSION['is_admin'] === true;

if (!$carparkId || !ctype_digit($carparkId)) {
    header("Location: /");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById((int)$carparkId);

// Fetch owner contact details and existing photos for pre-population
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';
$carparkModel = new Carparks();
$ownerDetails = $carparkModel->getOwnerDetails((int)($_SESSION['user_id'] ?? 0));
$carparkPhotos = $carparkModel->getCarparkPhotos((int)$carparkId);

// Get all bookings for this car park
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$bookings = $ReadBookings->getBookingsByCarparkId((int)$carparkId);

if (!$carpark) {
    header("Location: /");
    exit;
}

// Owner-only access (or admin override)
if (!$isAdminOverride && $_SESSION['user_id'] != $carpark['carpark_owner']) {
    header("Location: /");
    exit;
}
?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-6">

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900">Edit Car Park</h1>

                <?php if ($isAdminOverride): ?>
                    <span class="px-3 py-1 bg-red-50 text-red-600 text-xs font-bold rounded-full">
                        ADMIN MODE
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-sm text-gray-500 mt-1">
                Car Park ID: <?= htmlspecialchars($carpark['carpark_id']) ?>
                <?php if ($isAdminOverride): ?>
                    · Owner ID: <?= htmlspecialchars($carpark['carpark_owner']) ?>
                <?php endif; ?>
            </p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 text-emerald-700 rounded-lg text-sm">
                Car park details updated successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <!-- Edit Card -->
        <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Details</h2>
            <p class="text-sm text-gray-500 mb-6">Update your car park info. Changes apply immediately.</p>

            <form method="POST" action="/php/api/index.php?id=updateCarpark" enctype="multipart/form-data" class="space-y-6">
                <input type="hidden" name="carpark_id" value="<?= htmlspecialchars($carpark['carpark_id']) ?>">

                <!-- Name -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Car Park Name</label>
                    <input
                        type="text"
                        name="carpark_name"
                        value="<?= htmlspecialchars($carpark['carpark_name']) ?>"
                        required
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                    <textarea
                        name="carpark_description"
                        rows="4"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"><?= htmlspecialchars($carpark['carpark_description'] ?? '') ?></textarea>
                </div>

                <!-- Address -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Address</label>
                    <input
                        type="text"
                        name="carpark_address"
                        value="<?= htmlspecialchars($carpark['carpark_address']) ?>"
                        required
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                </div>

                <!-- Capacity -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Capacity (spaces)</label>
                    <input
                        type="number"
                        name="carpark_capacity"
                        value="<?= htmlspecialchars($carpark['carpark_capacity']) ?>"
                        required
                        min="1"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                </div>

                <!-- Lat/Lng -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Latitude</label>
                        <input
                            type="number"
                            name="carpark_lat"
                            value="<?= htmlspecialchars($carpark['carpark_lat']) ?>"
                            required
                            step="any"
                            class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Longitude</label>
                        <input
                            type="number"
                            name="carpark_lng"
                            value="<?= htmlspecialchars($carpark['carpark_lng']) ?>"
                            required
                            step="any"
                            class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    </div>
                </div>

                <!-- Features / Tags -->
                <?php
                $allowedFeatures = [
                    "CCTV",
                    "24/7 Access",
                    "EV Charging",
                    "Covered Parking",
                    "Disabled Access",
                    "Security Gate",
                    "Lighting",
                    "Permit Required",
                    "Staffed",
                    "Motorcycle Spaces"
                ];
                $currentFeatures = array_map('trim', explode(',', $carpark['carpark_features'] ?? ''));
                ?>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-3">Features / Tags</label>
                    <div class="grid grid-cols-2 gap-2">
                        <?php foreach ($allowedFeatures as $feature):
                            $featureId = 'feat-' . strtolower(str_replace([' ', '/'], '-', $feature));
                            $checked = in_array($feature, $currentFeatures) ? 'checked' : '';
                        ?>
                            <div class="flex items-center">
                                <input type="checkbox" name="features[]" value="<?= htmlspecialchars($feature) ?>"
                                    id="<?= $featureId ?>" <?= $checked ?> class="mr-2">
                                <label for="<?= $featureId ?>" class="text-sm text-gray-700"><?= htmlspecialchars($feature) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Owner Contact Details -->
                <div class="bg-gray-50 p-4 rounded-xl">
                    <h3 class="font-semibold text-gray-800 mb-3">Your Contact Details</h3>
                    <p class="text-xs text-gray-500 mb-4">Visible to bookers so they can contact you about access.</p>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Phone Number</label>
                            <input type="tel" name="owner_phone"
                                value="<?= htmlspecialchars($ownerDetails['phone_number'] ?? '') ?>"
                                placeholder="e.g. 07700 900000"
                                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                                   border border-gray-300 focus:outline-none
                                   focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Your Address</label>
                            <input type="text" name="owner_address"
                                value="<?= htmlspecialchars($ownerDetails['owner_address'] ?? '') ?>"
                                placeholder="e.g. 12 Example Street, Norwich"
                                class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                                   border border-gray-300 focus:outline-none
                                   focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        </div>
                    </div>
                </div>

                <!-- Space Size -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Space Size</label>
                    <select name="space_size"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                        <?php foreach (['small' => 'Small – suitable for compact/city cars', 'medium' => 'Medium – suits most standard cars', 'large' => 'Large – suitable for SUVs and vans'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($carpark['space_size'] ?? 'medium') === $val ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Access & Availability -->
                <div class="bg-gray-50 p-4 rounded-xl">
                    <h3 class="font-semibold text-gray-800 mb-3">Access &amp; Availability</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Requires a key or fob for access</p>
                                <p class="text-xs text-gray-500">Bookers will be told they need to collect a key</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="requires_key" class="sr-only peer"
                                    <?= !empty($carpark['requires_key']) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-700">Available on weekends</p>
                                <p class="text-xs text-gray-500">Allow bookings on Saturdays and Sundays</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="weekend_available" class="sr-only peer"
                                    <?= ($carpark['weekend_available'] ?? 1) ? 'checked' : '' ?>>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Minimum Booking Duration -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Minimum Booking Duration (minutes)</label>
                    <input type="number" name="min_booking_minutes" min="1" required
                        value="<?= htmlspecialchars($carpark['min_booking_minutes'] ?? 30) ?>"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    <p class="text-xs text-gray-500 mt-1">Bookings shorter than this will not be allowed.</p>
                </div>

                <!-- Affiliate URL -->
                <?php if ($carpark['carpark_type'] === 'affiliate'): ?>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Affiliate Link</label>
                        <input
                            type="url"
                            name="carpark_affiliate_url"
                            value="<?= htmlspecialchars($carpark['carpark_affiliate_url'] ?? '') ?>"
                            placeholder="https://example.com"
                            class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                               border border-gray-300 focus:outline-none
                               focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                    </div>
                <?php endif; ?>

                <!-- Add Photos -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Add Photos</label>
                    <div class="w-full rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center cursor-pointer hover:border-[#6ae6fc] transition"
                        onclick="document.getElementById('edit-photo-input').click()">
                        <i class="fa-solid fa-camera text-gray-400 text-2xl mb-2"></i>
                        <p class="text-sm text-gray-500">Click to add photos <span class="text-xs">(JPEG, PNG, WebP — multiple allowed)</span></p>
                        <input type="file" id="edit-photo-input" name="carpark_photos[]" multiple
                            accept="image/jpeg,image/png,image/webp,image/gif" class="hidden"
                            onchange="previewEditPhotos(this)">
                    </div>
                    <div id="edit-photo-preview" class="mt-3 flex flex-wrap gap-2"></div>
                </div>

                <!-- Actions -->
                <div class="flex gap-4 pt-2">
                    <button
                        type="submit"
                        class="flex-1 py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                           hover:bg-cyan-400 transition shadow-md">
                        Save Changes
                    </button>

                    <a
                        href="/"
                        class="flex-1 py-3 rounded-lg bg-gray-200 text-gray-700 text-sm font-semibold
                           hover:bg-gray-300 transition text-center shadow-sm">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Existing Photos -->
        <?php if (!empty($carparkPhotos)): ?>
            <div class="mt-10 bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">
                <h2 class="text-xl font-bold text-gray-900 mb-1">Photos</h2>
                <p class="text-sm text-gray-500 mb-6">Remove photos or add new ones using the form above.</p>
                <div class="flex flex-wrap gap-4">
                    <?php foreach ($carparkPhotos as $photo): ?>
                        <div class="relative group">
                            <img src="<?= htmlspecialchars($photo['photo_path']) ?>"
                                alt="Car park photo"
                                class="h-32 w-32 object-cover rounded-xl border border-gray-200">
                            <form method="POST" action="/php/api/index.php?id=deletePhoto"
                                onsubmit="return confirm('Delete this photo?');"
                                class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                                <input type="hidden" name="photo_id" value="<?= $photo['photo_id'] ?>">
                                <input type="hidden" name="carpark_id" value="<?= $carpark['carpark_id'] ?>">
                                <button type="submit"
                                    class="w-7 h-7 rounded-full bg-red-600 text-white text-xs flex items-center justify-center shadow hover:bg-red-700 transition">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
        $ReadRates = new ReadRates();
        $rates = $ReadRates->getCarparkRates((int)$carparkId);
        ?>

        <?php if (!empty($rates) && empty($carpark_monthly_fee)): ?>
            <!-- Pricing Rates -->
            <div class="mt-10 bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Pricing Rates</h2>
                <p class="text-sm text-gray-500 mb-6">
                    Set custom pricing for different durations. Customers will be charged based on these rates.
                </p>

                <?php if (!empty($rates)): ?>
                    <div class="overflow-hidden rounded-xl border border-gray-200 mb-6">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50">
                                    <th class="p-3 border-b border-gray-200 text-left">Duration</th>
                                    <th class="p-3 border-b border-gray-200 text-left">Price</th>
                                    <th class="p-3 border-b border-gray-200 text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rates as $rate): ?>
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="p-3 border-b border-gray-100 text-sm font-semibold text-gray-800">
                                            <?= htmlspecialchars($rate['duration_minutes']) ?> mins
                                        </td>
                                        <td class="p-3 border-b border-gray-100 text-sm text-gray-700">
                                            £<?= number_format($rate['price'] / 100, 2) ?>
                                        </td>
                                        <td class="p-3 border-b border-gray-100">
                                            <form method="POST" action="/php/api/index.php?id=deleteRate" class="inline"
                                                onsubmit="return confirm('Are you sure you want to delete this rate?');">
                                                <input type="hidden" name="rate_id" value="<?= $rate['rate_id'] ?>">
                                                <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                                                <button
                                                    type="submit"
                                                    class="py-1.5 px-3 rounded-lg bg-red-50 text-red-600 text-xs font-bold
                                                hover:bg-red-100 transition">
                                                    Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-xl p-4 text-gray-500 text-sm mb-6">
                        No rates added yet. Add one below.
                    </div>
                <?php endif; ?>

                <!-- Add New Rate -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Add New Rate</h3>
                    <p class="text-sm text-gray-500 mb-4">Add a duration and price tier.</p>

                    <form method="POST" action="/php/api/index.php?id=addRate" class="space-y-4">
                        <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Duration (minutes)</label>
                                <input
                                    type="number"
                                    name="duration_minutes"
                                    required
                                    min="1"
                                    placeholder="e.g., 60"
                                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                                    border border-gray-300 focus:outline-none
                                    focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Price (£)</label>
                                <input
                                    type="number"
                                    name="price"
                                    required
                                    min="0"
                                    step="0.01"
                                    placeholder="e.g., 2.50"
                                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                                    border border-gray-300 focus:outline-none
                                    focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                            </div>
                        </div>

                        <button
                            type="submit"
                            class="py-3 px-6 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                            hover:bg-cyan-400 transition shadow-md">
                            Add Rate
                        </button>
                    </form>
                </div>
            </div>

        <?php endif ?>

        <?php
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
        $ReadRates = new ReadRates();
        $carpark_monthly_fee = $ReadRates->getCarparkMonthlyRates((int)$carparkId);
        ?>
        <?php if (empty($rates) && !empty($carpark_monthly_fee)): ?>
            <div class="mt-10 bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">

                <h2 class="text-2xl font-bold text-gray-900 mb-2">Monthly Pricing</h2>
                <p class="text-sm text-gray-500 mb-6">
                    This car park uses a monthly pricing model instead of time-based rates.
                </p>

                <form method="POST" action="/php/api/index.php?id=updateMonthlyRate">
                    <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">

                    <div class="border border-gray-200 rounded-xl p-6 bg-gray-50">
                        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">

                            <div class="flex-1">
                                <label class="block text-xs font-semibold text-gray-500 mb-1">
                                    Monthly Fee (£)
                                </label>

                                <input
                                    type="number"
                                    name="price"

                                    min="0"
                                    step="0.01"
                                    value="<?= number_format($carpark_monthly_fee['price'] / 100, 2) ?>"
                                    class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                                    border border-gray-300 focus:outline-none
                                    focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent">
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full">
                                    Monthly Plan
                                </span>

                                <button
                                    type="submit"
                                    class="py-2 px-5 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                                    hover:bg-cyan-400 transition shadow-md">
                                    Update
                                </button>
                            </div>

                        </div>
                    </div>
                </form>

            </div>
        <?php endif; ?>

        <!-- Bookings -->
        <div class="mt-10 mb-16 bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Bookings for this Car Park</h2>
            <p class="text-sm text-gray-500 mb-6">View all bookings made for this car park.</p>

            <?php if (empty($bookings)): ?>
                <div class="bg-gray-50 rounded-xl p-6 text-gray-500 text-sm">
                    There are no bookings for this car park yet.
                </div>
            <?php else: ?>
                <div class="overflow-hidden rounded-xl border border-gray-200">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="text-xs text-gray-500 uppercase tracking-wide bg-gray-50">
                                <th class="p-3 border-b border-gray-200 text-left">Booking ID</th>
                                <th class="p-3 border-b border-gray-200 text-left">User ID</th>
                                <th class="p-3 border-b border-gray-200 text-left">Name</th>
                                <th class="p-3 border-b border-gray-200 text-left">Start</th>
                                <th class="p-3 border-b border-gray-200 text-left">End</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr
                                    onclick="window.location='/booking.php?id=<?= $booking['booking_id'] ?>'"
                                    class="cursor-pointer hover:bg-gray-100 transition">
                                    <td class="p-3 border-b border-gray-100 text-sm font-semibold text-gray-800">
                                        <?= htmlspecialchars($booking['booking_id']) ?>
                                    </td>
                                    <td class="p-3 border-b border-gray-100 text-sm text-gray-700">
                                        <?= htmlspecialchars($booking['booking_user_id']) ?>
                                    </td>
                                    <td class="p-3 border-b border-gray-100 text-sm text-gray-700">
                                        <?= htmlspecialchars($booking['booking_name']) ?>
                                    </td>
                                    <td class="p-3 border-b border-gray-100 text-sm text-gray-700">
                                        <?= date('d M Y, H:i', strtotime($booking['booking_start'])) ?>
                                    </td>
                                    <td class="p-3 border-b border-gray-100 text-sm text-gray-700">
                                        <?= date('d M Y, H:i', strtotime($booking['booking_end'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script>
        function previewEditPhotos(input) {
            const preview = document.getElementById('edit-photo-preview');
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