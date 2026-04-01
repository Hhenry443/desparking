<?php
session_start();

$title = "Your Account";

// If not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Get user's bookings
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$bookings = $ReadBookings->getBookingsByUserId($userId);

// Get users car parks
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getCarparksByUserId($userId);

// Get current user details for profile form
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/users/ReadUsers.php';
$ReadUsers = new ReadUsers();
$currentUser = $ReadUsers->getUserById($userId);

// get users car
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/ReadVehicles.php';
$ReadVehicles = new ReadVehicles();
$vehicles = $ReadVehicles->getVehiclesByUserId($userId);

// Owner payment details (only if they have listings)
$ownerPaymentDetails = null;
if (!empty($carparks)) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/owner-payment-details/ReadOwnerPaymentDetails.php';
    $ReadOwnerPaymentDetails = new ReadOwnerPaymentDetails();
    $ownerPaymentDetails = $ReadOwnerPaymentDetails->getByUserId($userId);
}

// Owner earnings (only if they have listings)
$ownerEarnings = [];
$pendingTotal  = 0;
$paidTotal     = 0;
if (!empty($carparks)) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/payments/ReadPayments.php';
    $ReadPayments  = new ReadPayments();
    $ownerEarnings = $ReadPayments->getEarningsByOwner($userId);
    foreach ($ownerEarnings as $row) {
        if ($row['payout_id']) {
            $paidTotal += (int) $row['owner_amount'];
        } else {
            $pendingTotal += (int) $row['owner_amount'];
        }
    }
}

?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#f3f3f3] pt-20 min-h-screen">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-6 py-12">

        <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm">
                <?= htmlspecialchars(urldecode($_GET['success'])) ?>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-12">

            <!-- Sidebar -->
            <aside class="w-full lg:w-56 text-sm text-[#1e1e4b]">

                <!-- Mobile: horizontal scrollable tabs -->
                <nav class="flex gap-1 overflow-x-auto pb-2 lg:flex-col lg:gap-0 lg:overflow-visible lg:pb-48 border-b border-gray-200 lg:border-0">

                    <p
                        class="nav-link font-bold whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left">
                        Dashboard
                    </p>

                    <button data-target="bookings"
                        class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                        My bookings
                    </button>

                    <button data-target="profile"
                        class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                        Profile Settings
                    </button>

                    <button data-target="vehicle"
                        class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                        My vehicle
                    </button>

                    <button data-target="listings"
                        class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                        My listings
                    </button>

                    <?php if (!empty($carparks)): ?>
                        <button data-target="earnings"
                            class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                            My earnings
                        </button>
                        <button data-target="payment-details"
                            class="nav-link whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none text-left lg:mt-4 hover:underline hover:cursor-pointer">
                            Payout details
                        </button>
                    <?php endif; ?>

                    <a href="/logout.php"
                        class="whitespace-nowrap px-3 py-2 rounded lg:px-0 lg:py-0 lg:rounded-none block hover:underline lg:mt-6 text-red-600 lg:text-[#1e1e4b]">
                        Log Out
                    </a>

                </nav>

            </aside>

            <!-- Content -->
            <main class="flex-1 min-w-0">

                <section data-section="bookings">
                    <div class="bg-white border border-gray-300 p-8">

                        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
                            <h2 class="text-lg font-semibold text-[#1e1e4b]">
                                My bookings
                            </h2>
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2 cursor-pointer select-none text-xs text-gray-500" onclick="toggleExpired()">
                                    <span>Show expired</span>
                                    <div class="relative">
                                        <div id="toggleExpiredTrack" class="w-9 h-5 bg-gray-200 rounded-full transition-colors duration-200"></div>
                                        <div id="toggleExpiredThumb" class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 cursor-pointer select-none text-xs text-gray-500" onclick="toggleCancelled()">
                                    <span>Show cancelled</span>
                                    <div class="relative">
                                        <div id="toggleCancelledTrack" class="w-9 h-5 bg-gray-200 rounded-full transition-colors duration-200"></div>
                                        <div id="toggleCancelledThumb" class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (empty($bookings)): ?>

                            <p class="text-gray-600 text-sm">
                                You have no bookings yet.
                            </p>

                        <?php else: ?>

                            <div class="divide-y divide-gray-200">

                                <?php foreach ($bookings as $booking): ?>

                                    <?php
                                    $now             = new DateTime();
                                    $bookingEnd      = new DateTime($booking['booking_end']);
                                    $isMonthly       = !empty($booking['is_monthly']);
                                    $isCancelled     = ($booking['booking_status'] ?? '') === 'cancelled';
                                    $isExpired       = !$isMonthly && !$isCancelled && $bookingEnd < $now;
                                    ?>

                                    <?php
                                    $extraClass = $isExpired ? ' expired-booking' : ($isCancelled ? ' cancelled-booking' : '');
                                    $hidden = ($isExpired || $isCancelled) ? 'style="display:none"' : '';
                                    ?>
                                    <div class="py-6 flex justify-between items-start<?= $extraClass ?>" <?= $hidden ?>>

                                        <!-- Left Info -->
                                        <div class="space-y-1">

                                            <div class="flex items-center gap-2">
                                                <p class="font-semibold text-gray-800">
                                                    <?= htmlspecialchars($booking['carpark_name']) ?>
                                                </p>
                                                <?php if ($isMonthly): ?>
                                                    <span class="text-xs font-semibold bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full">
                                                        Monthly
                                                    </span>
                                                <?php endif; ?>
                                            </div>

                                            <p class="text-sm text-gray-500">
                                                <?= htmlspecialchars($booking['carpark_address']) ?>
                                            </p>

                                            <?php if ($isMonthly): ?>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Subscribed:</span>
                                                    <?= date('d M Y', strtotime($booking['booking_start'])) ?>
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">
                                                        <?= $isCancelled ? 'Access until:' : 'Next renewal:' ?>
                                                    </span>
                                                    <?= date('d M Y', strtotime($booking['booking_end'])) ?>
                                                </p>
                                            <?php else: ?>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Arrive:</span>
                                                    <?= date('d M Y, g:ia', strtotime($booking['booking_start'])) ?>
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Leave by:</span>
                                                    <?= date('d M Y, g:ia', strtotime($booking['booking_end'])) ?>
                                                </p>
                                            <?php endif; ?>

                                            <p class="text-xs text-gray-400">
                                                Booking ID: <?= htmlspecialchars($booking['booking_id']) ?>
                                            </p>

                                        </div>

                                        <!-- Right Side -->
                                        <div class="text-right space-y-3">

                                            <?php if ($isCancelled && $isMonthly): ?>
                                                <span class="inline-block text-xs font-semibold bg-orange-100 text-orange-700 px-3 py-1 rounded-full">
                                                    Cancels <?= date('d M Y', strtotime($booking['booking_end'])) ?>
                                                </span>
                                            <?php elseif ($isCancelled): ?>
                                                <span class="inline-block text-xs font-semibold bg-red-100 text-red-600 px-3 py-1 rounded-full">
                                                    Cancelled
                                                </span>
                                            <?php elseif ($isMonthly): ?>
                                                <span class="inline-block text-xs font-semibold bg-cyan-100 text-cyan-700 px-3 py-1 rounded-full">
                                                    Active subscription
                                                </span>
                                            <?php elseif ($isExpired): ?>
                                                <span class="inline-block text-xs font-semibold bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
                                                    Expired
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-block text-xs font-semibold bg-green-100 text-green-600 px-3 py-1 rounded-full">
                                                    Active
                                                </span>
                                            <?php endif; ?>

                                            <div>
                                                <a href="/booking.php?id=<?= urlencode($booking['booking_id']) ?>"
                                                    class="text-sm font-semibold text-[#1e1e4b] hover:underline">
                                                    View →
                                                </a>
                                            </div>

                                        </div>

                                    </div>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </div>
                </section>

                <section data-section="payments" class="hidden">
                    <div class="bg-white border border-gray-300 p-8">
                        Payment methods content here.
                    </div>
                </section>

                <section data-section="profile" class="hidden">
                    <div class="bg-white border border-gray-300 p-8">

                        <h2 class="text-lg font-semibold text-[#1e1e4b] mb-6">
                            Profile Settings
                        </h2>

                        <form action="/php/api/index.php?id=updateProfile"
                            method="POST"
                            class="space-y-6">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Username
                                    </label>
                                    <input type="text"
                                        name="user_name"
                                        value="<?= htmlspecialchars($currentUser['user_name'] ?? '') ?>"
                                        required
                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                </div>

                            </div>

                            <hr class="border-gray-200">

                            <p class="text-sm text-gray-500">
                                Leave the new password fields blank to keep your current password.
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        New Password
                                    </label>
                                    <input type="password"
                                        name="new_password"
                                        autocomplete="new-password"
                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm New Password
                                    </label>
                                    <input type="password"
                                        name="confirm_password"
                                        autocomplete="new-password"
                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                </div>

                            </div>

                            <hr class="border-gray-200">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Current Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password"
                                    name="current_password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Required to save any changes"
                                    class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400 md:max-w-sm">
                            </div>

                            <div class="pt-2">
                                <button type="submit"
                                    class="bg-[#1e1e4b] text-white text-sm px-6 py-2 hover:bg-gray-800 transition">
                                    Save Changes
                                </button>
                            </div>

                        </form>

                    </div>
                </section>

                <section data-section="vehicle" class="hidden">

                    <div class="bg-white border border-gray-300 p-8">

                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-lg font-semibold text-[#1e1e4b]">
                                My Vehicles
                            </h2>

                            <button type="button"
                                onclick="toggleAddVehicle()"
                                class="bg-[#1e1e4b] text-white text-sm px-5 py-2 hover:bg-gray-800 transition">
                                + Add Vehicle
                            </button>
                        </div>

                        <!-- Add Vehicle Form (Hidden by default) -->
                        <div id="addVehicleForm" class="hidden border border-gray-200 p-6 mb-8">

                            <form action="/php/api/index.php?id=insertVehicle"
                                method="POST"
                                class="space-y-4">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Registration Plate
                                        </label>
                                        <input type="text"
                                            name="registration_plate"
                                            required
                                            class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400 uppercase">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Make
                                        </label>
                                        <input type="text"
                                            name="make"
                                            required
                                            class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Model
                                        </label>
                                        <input type="text"
                                            name="model"
                                            required
                                            class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Colour
                                        </label>
                                        <input type="text"
                                            name="colour"
                                            required
                                            class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                    </div>

                                </div>

                                <div class="pt-4">
                                    <button type="submit"
                                        class="bg-[#1e1e4b] text-white text-sm px-6 py-2 hover:bg-gray-800 transition">
                                        Save Vehicle
                                    </button>
                                </div>

                            </form>

                        </div>

                        <!-- Existing Vehicles -->
                        <?php if (empty($vehicles)): ?>

                            <p class="text-sm text-gray-600">
                                You haven't added any vehicles yet.
                            </p>

                        <?php else: ?>

                            <div class="space-y-8">

                                <?php foreach ($vehicles as $vehicle): ?>

                                    <div class="border border-gray-200 p-6">

                                        <form action="/php/api/index.php?id=insertVehicle"
                                            method="POST"
                                            class="space-y-4">

                                            <input type="hidden"
                                                name="vehicle_id"
                                                value="<?= htmlspecialchars($vehicle['vehicle_id']) ?>">

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Registration Plate
                                                    </label>
                                                    <input type="text"
                                                        name="registration_plate"
                                                        value="<?= htmlspecialchars($vehicle['registration_plate']) ?>"
                                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400 uppercase">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Make
                                                    </label>
                                                    <input type="text"
                                                        name="make"
                                                        value="<?= htmlspecialchars($vehicle['make']) ?>"
                                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Model
                                                    </label>
                                                    <input type="text"
                                                        name="model"
                                                        value="<?= htmlspecialchars($vehicle['model']) ?>"
                                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                                        Colour
                                                    </label>
                                                    <input type="text"
                                                        name="colour"
                                                        value="<?= htmlspecialchars($vehicle['colour']) ?>"
                                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                                </div>

                                            </div>

                                            <div class="flex justify-between items-center pt-4">

                                                <button type="submit"
                                                    class="bg-[#1e1e4b] text-white text-sm px-6 py-2 hover:bg-gray-800 transition">
                                                    Update
                                                </button>

                                        </form>

                                        <form action="/php/api/index.php?id=deleteVehicle"
                                            method="POST"
                                            onsubmit="return confirm('Delete this vehicle?');">

                                            <input type="hidden"
                                                name="vehicle_id"
                                                value="<?= htmlspecialchars($vehicle['vehicle_id']) ?>">

                                            <button type="submit"
                                                class="text-sm text-red-600 hover:underline">
                                                Delete
                                            </button>

                                        </form>

                                    </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

        </div>

        </section>

        <section data-section="listings" class="hidden">

            <div class="bg-white border border-gray-300 p-8">

                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold text-[#1e1e4b]">
                        My Listings
                    </h2>

                    <a href="/create.php"
                        class="bg-[#1e1e4b] text-white text-sm px-5 py-2 hover:bg-gray-800 transition">
                        + Add Car Park
                    </a>
                </div>

                <?php if (empty($carparks)): ?>

                    <p class="text-sm text-gray-600">
                        You haven't listed any car parks yet.
                    </p>

                <?php else: ?>

                    <div class="divide-y divide-gray-200">

                        <?php foreach ($carparks as $carpark): ?>

                            <div class="py-6 flex justify-between items-start">

                                <!-- Left Info -->
                                <div class="space-y-1">

                                    <p class="font-semibold text-gray-800">
                                        <?= htmlspecialchars($carpark['carpark_name']) ?>
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        <?= htmlspecialchars($carpark['carpark_address']) ?>
                                    </p>

                                    <p class="text-sm text-gray-600">
                                        Capacity:
                                        <span class="font-medium">
                                            <?= htmlspecialchars($carpark['carpark_capacity']) ?>
                                        </span>
                                    </p>

                                    <p class="text-xs text-gray-400">
                                        Car Park ID:
                                        <?= htmlspecialchars($carpark['carpark_id']) ?>
                                    </p>

                                </div>

                                <!-- Right Actions -->
                                <div class="text-right space-y-3">

                                    <a href="/carpark.php?id=<?= urlencode($carpark['carpark_id']) ?>"
                                        class="text-sm font-semibold text-[#1e1e4b] hover:underline">
                                        Manage →
                                    </a>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </section>

        <?php if (!empty($carparks)): ?>
            <section data-section="payment-details" class="hidden">
                <div class="bg-white border border-gray-300 p-8">

                    <h2 class="text-lg font-semibold text-[#1e1e4b] mb-2">Payout Details</h2>
                    <p class="text-sm text-gray-500 mb-6">Tell us how you'd like to receive your monthly earnings.</p>

                    <?php if ($ownerPaymentDetails): ?>
                        <!-- Current details -->
                        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-xl text-sm space-y-1">
                            <?php if ($ownerPaymentDetails['payment_type'] === 'bank_transfer'): ?>
                                <p class="font-semibold text-gray-700">Bank Transfer</p>
                                <p class="text-gray-600">Account name: <span class="font-medium"><?= htmlspecialchars($ownerPaymentDetails['account_name']) ?></span></p>
                                <p class="text-gray-600">Sort code: <span class="font-medium"><?= htmlspecialchars($ownerPaymentDetails['sort_code']) ?></span></p>
                                <p class="text-gray-600">Account number: <span class="font-medium"><?= htmlspecialchars($ownerPaymentDetails['account_number']) ?></span></p>
                            <?php else: ?>
                                <p class="font-semibold text-gray-700">PayPal</p>
                                <p class="text-gray-600">Email: <span class="font-medium"><?= htmlspecialchars($ownerPaymentDetails['paypal_email']) ?></span></p>
                            <?php endif; ?>
                        </div>

                        <form method="POST" action="/php/api/index.php?id=deleteOwnerPaymentDetails"
                            onsubmit="return confirm('Remove your payout details?')"
                            class="mb-8">
                            <button type="submit" class="text-sm text-red-600 hover:underline">Remove payout details</button>
                        </form>
                    <?php endif; ?>

                    <!-- Save / update form -->
                    <form method="POST" action="/php/api/index.php?id=saveOwnerPaymentDetails" class="space-y-5">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Payment method</label>
                            <select name="payment_type" id="payout-type-select"
                                class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                <option value="bank_transfer" <?= ($ownerPaymentDetails['payment_type'] ?? '') === 'bank_transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                                <option value="paypal" <?= ($ownerPaymentDetails['payment_type'] ?? '') === 'paypal' ? 'selected' : '' ?>>PayPal</option>
                            </select>
                        </div>

                        <!-- Bank transfer fields -->
                        <div id="payout-bank-fields" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Account Name</label>
                                <input type="text" name="account_name"
                                    value="<?= htmlspecialchars($ownerPaymentDetails['account_name'] ?? '') ?>"
                                    class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort Code</label>
                                    <input type="text" name="sort_code" placeholder="00-00-00"
                                        value="<?= htmlspecialchars($ownerPaymentDetails['sort_code'] ?? '') ?>"
                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Account Number</label>
                                    <input type="text" name="account_number" placeholder="12345678"
                                        value="<?= htmlspecialchars($ownerPaymentDetails['account_number'] ?? '') ?>"
                                        class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                                </div>
                            </div>
                        </div>

                        <!-- PayPal fields -->
                        <div id="payout-paypal-fields" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">PayPal Email</label>
                            <input type="email" name="paypal_email" placeholder="you@example.com"
                                value="<?= htmlspecialchars($ownerPaymentDetails['paypal_email'] ?? '') ?>"
                                class="w-full border border-gray-300 px-4 py-2 text-sm focus:ring-2 focus:ring-cyan-400">
                        </div>

                        <button type="submit"
                            class="bg-[#1e1e4b] text-white text-sm px-6 py-2 hover:bg-gray-800 transition">
                            Save Payout Details
                        </button>
                    </form>

                </div>
            </section>
        <?php endif; ?>

        <?php if (!empty($carparks)): ?>
            <section data-section="earnings" class="hidden">
                <div class="bg-white border border-gray-300 p-8">

                    <h2 class="text-lg font-semibold text-[#1e1e4b] mb-6">My Earnings</h2>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="border border-gray-200 p-4">
                            <p class="text-xs text-gray-500 mb-1">Pending payout</p>
                            <p class="text-2xl font-bold text-[#1e1e4b]">
                                £<?= number_format($pendingTotal / 100, 2) ?>
                            </p>
                        </div>
                        <div class="border border-gray-200 p-4">
                            <p class="text-xs text-gray-500 mb-1">Total paid out</p>
                            <p class="text-2xl font-bold text-green-700">
                                £<?= number_format($paidTotal / 100, 2) ?>
                            </p>
                        </div>
                    </div>

                    <?php if (empty($ownerEarnings)): ?>
                        <p class="text-sm text-gray-500">No earnings yet.</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse min-w-[500px]">
                                <thead>
                                    <tr class="border-b text-left text-gray-500">
                                        <th class="pb-2 pr-4">Date</th>
                                        <th class="pb-2 pr-4">Booking</th>
                                        <th class="pb-2 pr-4">Amount</th>
                                        <th class="pb-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <?php foreach ($ownerEarnings as $row): ?>
                                        <tr>
                                            <td class="py-3 pr-4 text-gray-600">
                                                <?= htmlspecialchars(date('d M Y', strtotime($row['created_at']))) ?>
                                            </td>
                                            <td class="py-3 pr-4">
                                                <a href="/booking.php?id=<?= urlencode($row['booking_id']) ?>"
                                                    class="text-[#1e1e4b] hover:underline">
                                                    #<?= htmlspecialchars($row['booking_id']) ?>
                                                </a>
                                            </td>
                                            <td class="py-3 pr-4 font-medium">
                                                £<?= number_format($row['owner_amount'] / 100, 2) ?>
                                            </td>
                                            <td class="py-3">
                                                <?php if ($row['payout_id']): ?>
                                                    <span class="text-xs font-semibold bg-green-100 text-green-700 px-2 py-0.5 rounded-full">
                                                        Paid <?= htmlspecialchars(date('d M Y', strtotime($row['paid_at']))) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-xs font-semibold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                                        Pending
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </section>
        <?php endif; ?>

        </main>

    </div>

    </div>

    <script>
        function activateSection(target) {
            document.querySelectorAll('[data-section]').forEach(section => {
                section.classList.add('hidden');
            });
            document.querySelector(`[data-section="${target}"]`)
                ?.classList.remove('hidden');

            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('font-semibold', 'bg-gray-100');
            });
            document.querySelector(`.nav-link[data-target="${target}"]`)
                ?.classList.add('font-semibold', 'bg-gray-100');
        }

        document.querySelectorAll('.nav-link').forEach(button => {
            button.addEventListener('click', () => activateSection(button.dataset.target));
        });

        // Auto-open section from query string (e.g. after profile save redirect)
        const urlSection = new URLSearchParams(window.location.search).get('section');
        if (urlSection) {
            activateSection(urlSection);
        }

        if (!urlSection) {
            activateSection('bookings');
        }
    </script>

    <script>
        let expiredVisible = false;
        let cancelledVisible = false;

        function toggleExpired() {
            expiredVisible = !expiredVisible;
            document.querySelectorAll('.expired-booking').forEach(el => {
                el.style.display = expiredVisible ? '' : 'none';
            });
            document.getElementById('toggleExpiredTrack').classList.toggle('bg-[#1e1e4b]', expiredVisible);
            document.getElementById('toggleExpiredTrack').classList.toggle('bg-gray-200', !expiredVisible);
            document.getElementById('toggleExpiredThumb').style.transform = expiredVisible ? 'translateX(16px)' : '';
        }

        function toggleCancelled() {
            cancelledVisible = !cancelledVisible;
            document.querySelectorAll('.cancelled-booking').forEach(el => {
                el.style.display = cancelledVisible ? '' : 'none';
            });
            document.getElementById('toggleCancelledTrack').classList.toggle('bg-[#1e1e4b]', cancelledVisible);
            document.getElementById('toggleCancelledTrack').classList.toggle('bg-gray-200', !cancelledVisible);
            document.getElementById('toggleCancelledThumb').style.transform = cancelledVisible ? 'translateX(16px)' : '';
        }
    </script>

    <script>
        (function() {
            const sel = document.getElementById('payout-type-select');
            if (!sel) return;

            function toggle() {
                const isBank = sel.value === 'bank_transfer';
                document.getElementById('payout-bank-fields').classList.toggle('hidden', !isBank);
                document.getElementById('payout-paypal-fields').classList.toggle('hidden', isBank);
            }
            sel.addEventListener('change', toggle);
            toggle();
        })();
    </script>

    <script>
        function toggleAddVehicle() {
            const form = document.getElementById('addVehicleForm');
            form.classList.toggle('hidden');
        }
    </script>

    <?php include_once __DIR__ . '/partials/footer.php'; ?>

</body>

</html>