<?php


// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user_id']);

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';

$carparkID = $_GET['carpark_id'] ?? null;

if (!$carparkID) {
    die("Invalid car park.");
}

$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById($carparkID);

$title = "Book a Space –" . htmlspecialchars($carpark['carpark_name']);

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/rates/ReadRates.php';

if ($carpark["is_monthly"] != 1) {
    $ReadRates = new ReadRates();
    $rates = $ReadRates->getCarparkRates((int)$carparkID);
} else {
    $ReadRates = new ReadRates();
    $rates = $ReadRates->getMonthlyRateByCarpark((int)$carparkID);
}


$vehicles = [];
if ($isLoggedIn) {
    include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/ReadVehicles.php';
    $ReadVehicles = new ReadVehicles();
    $vehicles = $ReadVehicles->getVehiclesByUserId((int)$_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="bg-[#ebebeb] min-h-screen">

    <!-- IMAGE HEADER -->
    <div class="w-full h-56 md:h-72 lg:h-80 overflow-hidden">
        <img
            src=" /images/default-carpark-image.png"
            class="w-full h-full object-cover"
            alt="Car Park Image">
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-xl p-6 mt-6 border border-gray-200">

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                <p class="font-bold">Booking Error</p>
                <p class="text-sm"><?= htmlspecialchars(urldecode($_GET['error'])) ?></p>
            </div>
        <?php endif; ?>

        <!-- Back Button -->
        <a href="/map.php" class="text-blue-600 hover:underline text-sm mb-3 inline-block">
            ← Back to map
        </a>

        <!-- TITLE -->
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">
            <?= htmlspecialchars($carpark["carpark_name"]) ?>
        </h1>

        <!-- DESCRIPTION -->
        <p class="text-gray-600 mb-4">
            <?= htmlspecialchars($carpark["carpark_description"]) ?>
        </p>

        <!-- STATS GRID -->
        <div class="p-4 bg-gray-50 rounded-lg border">
            <p class="text-sm text-gray-500">Capacity</p>
            <p class="font-semibold text-gray-800">
                <?= htmlspecialchars($carpark["carpark_capacity"]) ?>
            </p>
        </div>


        <div class="my-4 space-y-2 ">
            <p class="font-medium text-gray-700">Parking rates</p>

            <ul class="divide-y rounded-lg border bg-white">

                <?php if ($carpark["is_monthly"] == 1): ?>

                    <li class="flex justify-between px-4 py-3 text-sm">
                        <span class="text-gray-600">
                            Monthly rate
                        </span>
                        <span class="font-medium text-gray-900">
                            £<?= number_format($rates['price'] / 100, 2) ?> / month
                        </span>
                    </li>

                <?php else: ?>

                    <?php foreach ($rates as $rate): ?>
                        <li class="flex justify-between px-4 py-3 text-sm">
                            <span class="text-gray-600">
                                <?= $rate['duration_minutes'] ?> minute(s)
                            </span>
                            <span class="font-medium text-gray-900">
                                £<?= number_format($rate['price'] / 100, 2) ?>
                            </span>
                        </li>
                    <?php endforeach; ?>

                <?php endif; ?>

            </ul>
        </div>

        <!-- FEATURES -->
        <?php if (!empty($carpark["carpark_features"])): ?>

            <?php $featuresArray = explode(',', $carpark["carpark_features"]); ?>

            <div class="flex flex-wrap gap-2 mb-6">
                <?php foreach ($featuresArray as $feature): ?>
                    <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs rounded-full border">
                        <?= htmlspecialchars(trim($feature)) ?>
                    </span>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

        <!-- BOOKING FORM -->
        <h2 class="text-xl font-semibold text-gray-800 mb-3">
            <?= $carpark["is_monthly"] == 1 ? 'Subscribe Monthly' : 'Book Your Space' ?>
        </h2>

        <form
            method="POST"
            action="/checkout.php"
            class="space-y-5"
            id="booking-form">

            <input type="hidden" name="booking_carpark_id" value="<?= $carparkID ?>">
            <input type="hidden" name="booking_is_monthly" value="<?= $carpark['is_monthly'] == 1 ? '1' : '0' ?>">

            <!-- NAME -->
            <div>
                <label class="block text-sm font-medium mb-1">Your Name</label>
                <input
                    type="text"
                    id="booking-name"
                    name="booking_name"
                    required
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
                    placeholder="John Smith">
            </div>

            <!-- EMAIL -->
            <div>
                <label class="block text-sm font-medium mb-1">Email Address</label>
                <input
                    type="email"
                    id="booking-email"
                    name="booking_email"
                    required
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
                    placeholder="you@example.com">
            </div>

            <!-- VEHICLE -->
            <div>
                <?php if ($isLoggedIn): ?>
                    <label class="block text-sm font-medium mb-1">Select Vehicle</label>
                    <?php if (empty($vehicles)): ?>
                        <div class="p-3 bg-red-50 text-red-600 text-sm rounded-lg border border-red-200">
                            You must add a vehicle before booking.
                            <a href="/account.php" class="underline ml-1">Add vehicle</a>
                        </div>
                    <?php else: ?>
                        <select
                            id="booking-vehicle"
                            name="booking_vehicle_id"
                            required
                            class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500">

                            <option value="">Select your vehicle</option>

                            <?php foreach ($vehicles as $vehicle): ?>
                                <option value="<?= $vehicle['vehicle_id'] ?>">
                                    <?= htmlspecialchars($vehicle['registration_plate']) ?>
                                    —
                                    <?= htmlspecialchars($vehicle['make']) ?>
                                    <?= htmlspecialchars($vehicle['model']) ?>
                                    (<?= htmlspecialchars($vehicle['colour']) ?>)
                                </option>
                            <?php endforeach; ?>

                        </select>
                    <?php endif; ?>
                <?php else: ?>
                    <label class="block text-sm font-medium mb-1">Vehicle Registration</label>
                    <input
                        type="text"
                        id="booking-registration"
                        name="booking_registration"
                        required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-green-500"
                        placeholder="e.g. AB12 CDE">
                <?php endif; ?>
            </div>

            <?php if ($carpark["is_monthly"] == 1): ?>

                <!-- MONTHLY: just a start date -->
                <div>
                    <label class="block text-sm font-medium mb-1">Subscription Start Date</label>
                    <div class="flex items-center bg-gray-100 rounded-xl border border-gray-200 overflow-hidden">
                        <button type="button" id="book-start-trigger"
                            class="flex-1 flex items-center gap-2 px-4 py-3 min-w-0 hover:bg-black/5 transition">
                            <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                            <span id="book-start-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Today</span>
                        </button>
                    </div>
                    <input type="hidden" id="book-start-date" name="booking_start_date" />
                </div>

                <p class="text-sm text-gray-500">
                    Your subscription renews automatically each month. You can cancel at any time.
                </p>

            <?php else: ?>

                <!-- Restrictions info -->
                <?php if (!empty($carpark['min_booking_minutes']) || empty($carpark['weekend_available'])): ?>
                    <div class="flex flex-wrap gap-2 mb-1">
                        <?php if (!empty($carpark['min_booking_minutes'])): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full border border-blue-100">
                                <i class="fa-solid fa-clock"></i>
                                Min. <?= (int)$carpark['min_booking_minutes'] ?> min booking
                            </span>
                        <?php endif; ?>
                        <?php if (empty($carpark['weekend_available'])): ?>
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-100">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                Weekdays only
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- From / Until pickers -->
                <div class="flex flex-col sm:flex-row gap-2">

                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Arrive</label>
                        <div class="flex items-center bg-gray-100 rounded-xl border border-gray-200 overflow-hidden">
                            <button type="button" id="book-from-trigger"
                                class="flex-1 flex items-center gap-2 px-4 py-3 min-w-0 hover:bg-black/5 transition">
                                <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                                <span id="book-from-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Today</span>
                            </button>
                            <div class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                            <button type="button" id="booking-from-time-btn"
                                class="flex items-center gap-1 pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap">
                                <span id="booking-from-time-label">--:--</span>
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-1"></i>
                            </button>
                            <input type="hidden" id="booking-from-date" name="booking_start_date" />
                            <input type="hidden" id="booking-from-time" name="booking_start_time" />
                        </div>
                    </div>

                    <div class="flex-1">
                        <label class="block text-sm font-medium mb-1">Leave by</label>
                        <div class="flex items-center bg-gray-100 rounded-xl border border-gray-200 overflow-hidden">
                            <button type="button" id="book-until-trigger"
                                class="flex-1 flex items-center gap-2 px-4 py-3 min-w-0 hover:bg-black/5 transition">
                                <i class="fa-solid fa-flag-checkered text-[#6ae6fc] flex-shrink-0"></i>
                                <span id="book-until-label" class="flex-1 text-sm font-medium text-gray-700 truncate">Tomorrow</span>
                            </button>
                            <div class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                            <button type="button" id="booking-until-time-btn"
                                class="flex items-center gap-1 pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap">
                                <span id="booking-until-time-label">--:--</span>
                                <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-1"></i>
                            </button>
                            <input type="hidden" id="booking-until-date" name="booking_end_date" />
                            <input type="hidden" id="booking-until-time" name="booking_end_time" />
                        </div>
                    </div>

                </div>

                <!-- Client-side validation message -->
                <div id="booking-time-error" class="hidden p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200"></div>

            <?php endif; ?>

            <!-- SUBMIT BUTTON -->
            <button
                type="submit"
                id="booking-submit"
                <?= ($isLoggedIn && empty($vehicles)) ? 'disabled class="w-full bg-gray-400 text-white font-medium py-3 rounded-lg cursor-not-allowed"' :
                    'class="w-full bg-[#6ae6fc] hover:bg-cyan-400 text-gray-900 font-bold py-3 rounded-xl transition cursor-pointer shadow-sm"' ?>>
                <?= $carpark["is_monthly"] == 1 ? 'Proceed to Subscription' : 'Proceed to Payment' ?>
            </button>
        </form>

        <script src="/js/datePicker.js"></script>

        <?php if ($carpark["is_monthly"] == 1): ?>
            <script>
                (function() {
                    const pad     = n => String(n).padStart(2, '0');
                    const today   = new Date(); today.setHours(0, 0, 0, 0);
                    const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
                    const picker  = makeDatePicker('book-start-trigger', 'book-start-label', 'book-start-date', null, 'above');
                    if (picker) picker.select(fmtDate(today));
                })();
            </script>
        <?php else: ?>
            <script>
                const WEEKEND_AVAILABLE = <?= (int)(!empty($carpark['weekend_available'])) ?>;
                const MIN_BOOKING_MINS = <?= (int)($carpark['min_booking_minutes'] ?? 0) ?>;

                const pad = n => String(n).padStart(2, '0');
                const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtTime = d => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

                const LS_KEY = 'desparking_booking_<?= (int)$carparkID ?>';

                function saveBookingForm() {
                    try {
                        localStorage.setItem(LS_KEY, JSON.stringify({
                            name: document.getElementById('booking-name')?.value,
                            email: document.getElementById('booking-email')?.value,
                            vehicleId: document.getElementById('booking-vehicle')?.value,
                            registration: document.getElementById('booking-registration')?.value,
                            fromDate: document.getElementById('booking-from-date').value,
                            fromTime: document.getElementById('booking-from-time').value,
                            untilDate: document.getElementById('booking-until-date').value,
                            untilTime: document.getElementById('booking-until-time').value,
                        }));
                    } catch {}
                }

                (function() {
                    const now = new Date();
                    const mins = now.getMinutes();
                    const fromDT = new Date(now);
                    fromDT.setMinutes(mins <= 30 ? 30 : 0, 0, 0);
                    if (mins > 30) fromDT.setHours(fromDT.getHours() + 1);
                    const untilDT = new Date(fromDT.getTime() + 60 * 60 * 1000);

                    let saved = null;
                    try {
                        saved = JSON.parse(localStorage.getItem(LS_KEY) || 'null');
                    } catch {}

                    const fromDatePicker = makeDatePicker('book-from-trigger', 'book-from-label', 'booking-from-date', saveBookingForm, 'above');
                    const untilDatePicker = makeDatePicker('book-until-trigger', 'book-until-label', 'booking-until-date', saveBookingForm, 'above');
                    fromDatePicker.select(saved?.fromDate || fmtDate(fromDT));
                    untilDatePicker.select(saved?.untilDate || fmtDate(untilDT));

                    const fromTimePicker = makeTimePicker('booking-from-time-btn', 'booking-from-time-label', 'booking-from-time', saveBookingForm, 'above');
                    const untilTimePicker = makeTimePicker('booking-until-time-btn', 'booking-until-time-label', 'booking-until-time', saveBookingForm, 'above');
                    fromTimePicker.setValue(saved?.fromTime || fmtTime(fromDT));
                    untilTimePicker.setValue(saved?.untilTime || fmtTime(untilDT));

                    if (saved?.name) {
                        const el = document.getElementById('booking-name');
                        if (el) el.value = saved.name;
                    }
                    if (saved?.email) {
                        const el = document.getElementById('booking-email');
                        if (el) el.value = saved.email;
                    }
                    if (saved?.vehicleId) {
                        const el = document.getElementById('booking-vehicle');
                        if (el) el.value = saved.vehicleId;
                    }
                    if (saved?.registration) {
                        const el = document.getElementById('booking-registration');
                        if (el) el.value = saved.registration;
                    }

                    document.getElementById('booking-form').addEventListener('change', saveBookingForm);
                })();

                function windowIncludesWeekend(startDT, endDT) {
                    const current = new Date(startDT);
                    current.setHours(0, 0, 0, 0);
                    const end = new Date(endDT);
                    end.setHours(0, 0, 0, 0);
                    let steps = 0;
                    while (current <= end && steps <= 7) {
                        const dow = current.getDay(); // 0 = Sun, 6 = Sat
                        if (dow === 0 || dow === 6) return true;
                        current.setDate(current.getDate() + 1);
                        steps++;
                    }
                    return false;
                }

                document.getElementById('booking-form').addEventListener('submit', function(e) {
                    const errorBox = document.getElementById('booking-time-error');
                    errorBox.classList.add('hidden');
                    errorBox.textContent = '';

                    const fromDate = document.getElementById('booking-from-date').value;
                    const fromTime = document.getElementById('booking-from-time').value;
                    const untilDate = document.getElementById('booking-until-date').value;
                    const untilTime = document.getElementById('booking-until-time').value;

                    if (!fromDate || !fromTime || !untilDate || !untilTime) return;

                    const startDT = new Date(`${fromDate}T${fromTime}`);
                    const endDT = new Date(`${untilDate}T${untilTime}`);

                    if (endDT <= startDT) {
                        e.preventDefault();
                        errorBox.textContent = 'End time must be after start time.';
                        errorBox.classList.remove('hidden');
                        return;
                    }

                    if (!WEEKEND_AVAILABLE && windowIncludesWeekend(startDT, endDT)) {
                        e.preventDefault();
                        errorBox.textContent = 'This car park is not available on weekends. Please choose weekday dates.';
                        errorBox.classList.remove('hidden');
                        return;
                    }

                    if (MIN_BOOKING_MINS > 0) {
                        const durationMins = (endDT - startDT) / 60000;
                        if (durationMins < MIN_BOOKING_MINS) {
                            e.preventDefault();
                            errorBox.textContent = `Minimum booking duration is ${MIN_BOOKING_MINS} minutes. Your selection is only ${Math.round(durationMins)} minutes.`;
                            errorBox.classList.remove('hidden');
                            return;
                        }
                    }
                });
            </script>
        <?php endif; ?>


    </div>

    <br><br>
</body>

</html>