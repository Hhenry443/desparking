<?php


// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

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


include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/ReadVehicles.php';

$ReadVehicles = new ReadVehicles();
$vehicles = $ReadVehicles->getVehiclesByUserId((int)$_SESSION['user_id']);
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
        <a href="/index.php" class="text-blue-600 hover:underline text-sm mb-3 inline-block">
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
            </div>

            <?php if ($carpark["is_monthly"] == 1): ?>

                <!-- MONTHLY: just a start date -->
                <div>
                    <label class="block text-sm font-medium mb-1">Subscription Start Date</label>
                    <input
                        type="date"
                        name="booking_start_date"
                        required
                        value="<?= date('Y-m-d') ?>"
                        min="<?= date('Y-m-d') ?>"
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-[#6ae6fc]">
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

                <!-- From / Until pickers — map search bar style -->
                <div class="flex flex-col sm:flex-row gap-2">

                    <div class="flex items-center gap-2 flex-1 bg-gray-100 rounded-xl px-4 py-3 border border-gray-200">
                        <span class="text-xs font-bold text-[#060745] uppercase tracking-wide whitespace-nowrap">From</span>
                        <div class="flex items-center gap-1 flex-1 min-w-0">
                            <input
                                id="booking-from-date"
                                type="date"
                                name="booking_start_date"
                                required
                                class="flex-1 min-w-0 bg-transparent text-gray-700 text-sm focus:outline-none">
                            <div class="relative self-center" id="from-time-wrapper">
                                <button type="button" id="booking-from-time-btn"
                                    class="w-24 flex items-center justify-between gap-1 px-2 py-1 bg-white border border-gray-300 rounded text-sm text-gray-700 cursor-pointer">
                                    <span id="booking-from-time-label">--:--</span>
                                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul id="booking-from-time-list"
                                    class="hidden absolute z-20 bottom-full mb-1 w-24 bg-white border border-gray-300 rounded shadow-lg overflow-y-auto"
                                    style="max-height:11rem"></ul>
                                <input type="hidden" name="booking_start_time" id="booking-from-time">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-1 bg-gray-100 rounded-xl px-4 py-3 border border-gray-200">
                        <span class="text-xs font-bold text-[#060745] uppercase tracking-wide whitespace-nowrap">Until</span>
                        <div class="flex items-center gap-1 flex-1 min-w-0">
                            <input
                                id="booking-until-date"
                                type="date"
                                name="booking_end_date"
                                required
                                class="flex-1 min-w-0 bg-transparent text-gray-700 text-sm focus:outline-none">
                            <div class="relative self-center" id="until-time-wrapper">
                                <button type="button" id="booking-until-time-btn"
                                    class="w-24 flex items-center justify-between gap-1 px-2 py-1 bg-white border border-gray-300 rounded text-sm text-gray-700 cursor-pointer">
                                    <span id="booking-until-time-label">--:--</span>
                                    <svg class="w-3 h-3 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <ul id="booking-until-time-list"
                                    class="hidden absolute z-20 bottom-full mb-1 w-24 bg-white border border-gray-300 rounded shadow-lg overflow-y-auto"
                                    style="max-height:11rem"></ul>
                                <input type="hidden" name="booking_end_time" id="booking-until-time">
                            </div>
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
                <?= empty($vehicles) ? 'disabled class="w-full bg-gray-400 text-white font-medium py-3 rounded-lg cursor-not-allowed"' :
                    'class="w-full bg-[#6ae6fc] hover:bg-cyan-400 text-gray-900 font-bold py-3 rounded-xl transition cursor-pointer shadow-sm"' ?>>
                <?= $carpark["is_monthly"] == 1 ? 'Proceed to Subscription' : 'Proceed to Payment' ?>
            </button>
        </form>

        <?php if ($carpark["is_monthly"] != 1): ?>
            <script>
                const WEEKEND_AVAILABLE = <?= (int)(!empty($carpark['weekend_available'])) ?>;
                const MIN_BOOKING_MINS = <?= (int)($carpark['min_booking_minutes'] ?? 0) ?>;

                const pad = n => String(n).padStart(2, '0');
                const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtTime = d => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

                function timeLabel(val) {
                    const [h, m] = val.split(':').map(Number);
                    const suffix = h < 12 ? 'am' : 'pm';
                    const hour12 = h % 12 || 12;
                    return `${hour12}:${pad(m)}${suffix}`;
                }

                const TIME_SLOTS = [];
                for (let h = 0; h < 24; h++) for (let m of [0, 30]) TIME_SLOTS.push(`${pad(h)}:${pad(m)}`);

                function buildTimePicker(listEl, btnLabel, hiddenInput, selectedValue) {
                    listEl.innerHTML = '';
                    TIME_SLOTS.forEach(val => {
                        const li = document.createElement('li');
                        li.textContent = timeLabel(val);
                        li.dataset.value = val;
                        li.className = 'px-3 py-1.5 text-sm cursor-pointer hover:bg-cyan-50' + (val === selectedValue ? ' bg-cyan-100 font-medium' : '');
                        li.addEventListener('click', () => {
                            hiddenInput.value = val;
                            btnLabel.textContent = timeLabel(val);
                            listEl.querySelectorAll('li').forEach(i => i.classList.remove('bg-cyan-100', 'font-medium'));
                            li.classList.add('bg-cyan-100', 'font-medium');
                            listEl.classList.add('hidden');
                        });
                        listEl.appendChild(li);
                    });
                    // Scroll selected item into view when opened
                    hiddenInput.value = selectedValue;
                    btnLabel.textContent = timeLabel(selectedValue);
                }

                function initTimePicker(btnId, listId, labelId, hiddenId) {
                    const btn = document.getElementById(btnId);
                    const list = document.getElementById(listId);
                    const label = document.getElementById(labelId);
                    const hidden = document.getElementById(hiddenId);
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const isHidden = list.classList.contains('hidden');
                        document.querySelectorAll('.time-dropdown-list').forEach(l => l.classList.add('hidden'));
                        if (isHidden) {
                            list.classList.remove('hidden');
                            const active = list.querySelector('.bg-cyan-100');
                            if (active) active.scrollIntoView({ block: 'nearest' });
                        }
                    });
                    list.classList.add('time-dropdown-list');
                    return { btn, list, label, hidden };
                }

                const LS_KEY = 'desparking_booking_<?= (int)$carparkID ?>';

                function saveBookingForm() {
                    try {
                        localStorage.setItem(LS_KEY, JSON.stringify({
                            name:      document.getElementById('booking-name')?.value,
                            email:     document.getElementById('booking-email')?.value,
                            vehicleId: document.getElementById('booking-vehicle')?.value,
                            fromDate:  document.getElementById('booking-from-date').value,
                            fromTime:  document.getElementById('booking-from-time').value,
                            untilDate: document.getElementById('booking-until-date').value,
                            untilTime: document.getElementById('booking-until-time').value,
                        }));
                    } catch {}
                }

                // Auto-fill: From = now rounded up to next 30 min, Until = +1 hour
                (function() {
                    const now = new Date();
                    const mins = now.getMinutes();
                    const roundedMins = mins <= 30 ? 30 : 0;
                    now.setMinutes(roundedMins, 0, 0);
                    if (mins > 30) now.setHours(now.getHours() + 1);
                    const until = new Date(now.getTime() + 60 * 60 * 1000);

                    const from = initTimePicker('booking-from-time-btn', 'booking-from-time-list', 'booking-from-time-label', 'booking-from-time');
                    const till = initTimePicker('booking-until-time-btn', 'booking-until-time-list', 'booking-until-time-label', 'booking-until-time');

                    // Restore from localStorage, falling back to defaults
                    let saved = null;
                    try { saved = JSON.parse(localStorage.getItem(LS_KEY) || 'null'); } catch {}

                    buildTimePicker(from.list, from.label, from.hidden, saved?.fromTime || fmtTime(now));
                    buildTimePicker(till.list, till.label, till.hidden, saved?.untilTime || fmtTime(until));

                    document.getElementById('booking-from-date').value  = saved?.fromDate  || fmtDate(now);
                    document.getElementById('booking-until-date').value = saved?.untilDate || fmtDate(until);

                    if (saved?.name)      { const el = document.getElementById('booking-name');    if (el) el.value = saved.name; }
                    if (saved?.email)     { const el = document.getElementById('booking-email');   if (el) el.value = saved.email; }
                    if (saved?.vehicleId) { const el = document.getElementById('booking-vehicle'); if (el) el.value = saved.vehicleId; }

                    // Save on any change
                    document.getElementById('booking-form').addEventListener('change', saveBookingForm);
                    ['booking-from-date', 'booking-until-date'].forEach(id =>
                        document.getElementById(id).addEventListener('change', saveBookingForm));
                    // Time pickers call saveBookingForm after selection
                    from.list.addEventListener('click', saveBookingForm);
                    till.list.addEventListener('click', saveBookingForm);

                    // Close dropdowns when clicking outside
                    document.addEventListener('click', () => document.querySelectorAll('.time-dropdown-list').forEach(l => l.classList.add('hidden')));
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