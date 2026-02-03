<?php
session_start();

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

<head>
    <meta charset="utf-8">
    <title>Edit Car Park · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">
</head>

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

            <form method="POST" action="/php/api/index.php?id=updateCarpark" class="space-y-6">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

                <!-- Features -->
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Features</label>
                    <textarea
                        name="carpark_features"
                        rows="3"
                        placeholder="e.g., CCTV, Covered, EV Charging, Disabled Access"
                        class="w-full py-3 px-4 rounded-lg bg-gray-200 text-gray-700 text-sm
                           border border-gray-300 focus:outline-none
                           focus:ring-2 focus:ring-[#6ae6fc] focus:border-transparent"><?= htmlspecialchars($carpark['carpark_features'] ?? '') ?></textarea>
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

        <!-- Pricing Rates -->
        <div class="mt-10 bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Pricing Rates</h2>
            <p class="text-sm text-gray-500 mb-6">
                Set custom pricing for different durations. Customers will be charged based on these rates.
            </p>

            <?php
            include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
            $ReadRates = new ReadRates();
            $rates = $ReadRates->getCarparkRates((int)$carparkId);
            ?>

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
                <div class="mb-6 p-4 bg-amber-50 text-amber-800 rounded-lg text-sm">
                    <strong>No rates set yet.</strong> Add your first pricing rate below.
                </div>
            <?php endif; ?>

            <!-- Add New Rate -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Add New Rate</h3>
                <p class="text-sm text-gray-500 mb-4">Add a duration and price tier.</p>

                <form method="POST" action="/php/api/index.php?id=addRate" class="space-y-4">
                    <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                            <p class="text-xs text-gray-500 mt-1">How many minutes this rate covers</p>
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
                            <p class="text-xs text-gray-500 mt-1">Price in pounds (e.g., 2.50)</p>
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
</body>

</html>