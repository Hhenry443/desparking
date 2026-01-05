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
<body class="min-h-screen bg-gray-100 pt-20">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-4xl mx-auto bg-gray-200 rounded-2xl shadow-lg p-8 mt-10">

    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-gray-800">
                Edit Car Park
            </h1>
            <?php if ($isAdminOverride): ?>
                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">
                    ADMIN MODE
                </span>
            <?php endif; ?>
        </div>

        <p class="text-gray-500 mt-1">
            Car Park ID: <?= htmlspecialchars($carpark['carpark_id']) ?>
            <?php if ($isAdminOverride): ?>
                · Owner ID: <?= htmlspecialchars($carpark['carpark_owner']) ?>
            <?php endif; ?>
        </p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <p class="font-bold">Success!</p>
            <p class="text-sm">Car park details updated successfully.</p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <p class="font-bold">Error</p>
            <p class="text-sm"><?= htmlspecialchars(urldecode($_GET['error'])) ?></p>
        </div>
    <?php endif; ?>

    <!-- Edit Form -->
    <form method="POST" action="/php/api/index.php?id=updateCarpark" class="space-y-6">
        
        <input type="hidden" name="carpark_id" value="<?= htmlspecialchars($carpark['carpark_id']) ?>">

        <!-- Name -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Car Park Name
            </label>
            <input 
                type="text" 
                name="carpark_name" 
                value="<?= htmlspecialchars($carpark['carpark_name']) ?>"
                required
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea 
                name="carpark_description" 
                rows="4"
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ><?= htmlspecialchars($carpark['carpark_description'] ?? '') ?></textarea>
        </div>

        <!-- Address -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Address
            </label>
            <input 
                type="text" 
                name="carpark_address" 
                value="<?= htmlspecialchars($carpark['carpark_address']) ?>"
                required
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>

        <!-- Capacity  -->            
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Capacity (spaces)
            </label>
            <input 
                type="number" 
                name="carpark_capacity" 
                value="<?= htmlspecialchars($carpark['carpark_capacity']) ?>"
                required
                min="1"
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
        </div>

        <!-- Latitude and Longitude -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Latitude
                </label>
                <input 
                    type="number" 
                    name="carpark_lat" 
                    value="<?= htmlspecialchars($carpark['carpark_lat']) ?>"
                    required
                    step="any"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Longitude
                </label>
                <input 
                    type="number" 
                    name="carpark_lng" 
                    value="<?= htmlspecialchars($carpark['carpark_lng']) ?>"
                    required
                    step="any"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
            </div>

        </div>

        <!-- Features -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
                Features
            </label>
            <textarea 
                name="carpark_features" 
                rows="3"
                placeholder="e.g., CCTV, Covered, EV Charging, Disabled Access"
                class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ><?= htmlspecialchars($carpark['carpark_features'] ?? '') ?></textarea>
        </div>

        <!-- If carpark type is affiliate, show affiliate URL field -->
         <?php if ($carpark['carpark_type'] === 'affiliate'): ?>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Affiliate Link (optional)
                </label>
                <input 
                    type="url" 
                    name="carpark_affiliate_url" 
                    value="<?= htmlspecialchars($carpark['carpark_affiliate_url'] ?? '') ?>"
                    placeholder="https://example.com"
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
            </div>
        <?php endif; ?>


        <!-- Submit Buttons -->
        <div class="flex gap-4 pt-4">
            <button 
                type="submit"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition"
            >
                Save Changes
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

<!-- Pricing Rates Section -->
<div class="max-w-4xl mx-auto bg-gray-200 rounded-2xl shadow-lg p-8 mt-8">
    
    <h2 class="text-2xl font-bold text-gray-800 mb-4">
        Pricing Rates
    </h2>
    
    <p class="text-gray-600 mb-6">
        Set custom pricing for different durations. Customers will be charged based on these rates.
    </p>

    <?php
    // Get existing rates for this car park
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';
    $ReadRates = new ReadRates();
    $rates = $ReadRates->getCarparkRates((int)$carparkId);
    ?>

    <!-- Existing Rates Table -->
    <?php if (!empty($rates)): ?>
        <div class="overflow-x-auto mb-6">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm text-gray-600">
                        <th class="p-3 border-b">Duration (minutes)</th>
                        <th class="p-3 border-b">Price (£)</th>
                        <th class="p-3 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rates as $rate): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 border-b font-medium text-gray-800">
                                <?= htmlspecialchars($rate['duration_minutes']) ?> mins
                            </td>
                            <td class="p-3 border-b text-gray-700">
                                £<?= number_format($rate['price'] / 100, 2) ?>
                            </td>
                            <td class="p-3 border-b">
                                <form method="POST" action="/php/api/index.php?id=deleteRate" class="inline" onsubmit="return confirm('Are you sure you want to delete this rate?');">
                                    <input type="hidden" name="rate_id" value="<?= $rate['rate_id'] ?>">
                                    <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
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
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg">
            <p class="text-sm">
                <strong>No rates set yet.</strong> Add your first pricing rate below.
            </p>
        </div>
    <?php endif; ?>

    <!-- Add New Rate Form -->
    <div class="border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Add New Rate</h3>
        
        <form method="POST" action="/php/api/index.php?id=addRate" class="space-y-4">
            <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Duration (minutes)
                    </label>
                    <input 
                        type="number" 
                        name="duration_minutes" 
                        required
                        min="1"
                        placeholder="e.g., 60"
                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">How many minutes this rate covers</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Price (£)
                    </label>
                    <input 
                        type="number" 
                        name="price" 
                        required
                        min="0"
                        step="0.01"
                        placeholder="e.g., 2.50"
                        class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                    <p class="text-xs text-gray-500 mt-1">Price in pounds (e.g., 2.50)</p>
                </div>
            </div>

            <button 
                type="submit"
                class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-6 rounded-lg transition"
            >
                Add Rate
            </button>
        </form>
    </div>

</div>

<!-- Bookings for this car park go here -->
<div class="max-w-4xl mx-auto bg-gray-200 rounded-2xl shadow-lg p-8 mt-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        Bookings for this Car Park
    </h2>

    <?php if (empty($bookings)): ?>
        <p class="text-gray-600">
            There are no bookings for this car park yet.
        </p>
    <?php else: ?>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-left text-sm text-gray-600">
                        <th class="p-3 border-b">Booking ID</th>
                        <th class="p-3 border-b">User ID</th>
                        <th class="p-3 border-b">Name</th>
                        <th class="p-3 border-b">Start</th>
                        <th class="p-3 border-b">End</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-3 border-b font-medium text-gray-800">
                                <?= htmlspecialchars($booking['booking_id']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= htmlspecialchars($booking['booking_user_id']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= htmlspecialchars($booking['booking_name']) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= date(
                                    'd M Y, H:i',
                                    strtotime($booking['booking_start'])
                                ) ?>
                            </td>

                            <td class="p-3 border-b text-gray-700">
                                <?= date(
                                    'd M Y, H:i',
                                    strtotime($booking['booking_end'])
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

</body>
</html>