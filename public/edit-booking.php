<?php
session_start();

$title = "Edit Booking";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingID = $_GET['id'] ?? null;
$isAdminOverride = isset($_GET['admin']) && $_GET['admin'] == '1' && $_SESSION['is_admin'] === true;

if (!$bookingID || !ctype_digit($bookingID)) {
    header("Location: /account.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$booking = $ReadBookings->getBookingByBookingId((int)$bookingID);

if (!$booking) {
    header("Location: /account.php");
    exit;
}

// Permission check
if (
    !$isAdminOverride &&
    $_SESSION['user_id'] != $booking['booking_user_id'] &&
    $_SESSION['user_id'] != $booking['carpark_owner']
) {
    header("Location: /account.php");
    exit;
}
?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>


<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 mb-12">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Edit Booking</h1>
            <p class="text-gray-500 text-sm mt-1">
                Update the booking times. Any price difference will be calculated before confirmation.
            </p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
                <?= htmlspecialchars(urldecode($_GET['error'])) ?>
            </div>
        <?php endif; ?>

        <form action="/php/api/bookings/PreviewEditBooking.php" method="POST" class="space-y-6">

            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">

            <!-- Booking name (readonly) -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Booking Name</label>
                <input type="text" value="<?= htmlspecialchars($booking['booking_name']) ?>" disabled
                    class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-500 text-sm border border-gray-200">
            </div>

            <!-- Start time -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Start Time *</label>
                <div class="flex items-center bg-gray-100 rounded-xl border border-gray-200 overflow-hidden">
                    <button type="button" id="edit-from-trigger"
                        class="flex-1 flex items-center gap-2 px-4 py-3 min-w-0 hover:bg-black/5 transition">
                        <i class="fa-regular fa-calendar text-[#6ae6fc] flex-shrink-0"></i>
                        <span id="edit-from-label" class="flex-1 text-sm font-medium text-gray-700 truncate"></span>
                    </button>
                    <div class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                    <button type="button" id="edit-from-time-btn"
                        class="flex items-center gap-1 pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap">
                        <span id="edit-from-time-label">--:--</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-1"></i>
                    </button>
                    <input type="hidden" id="edit-from-date" />
                    <input type="hidden" id="edit-from-time" />
                </div>
            </div>

            <!-- End time -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">End Time *</label>
                <div class="flex items-center bg-gray-100 rounded-xl border border-gray-200 overflow-hidden">
                    <button type="button" id="edit-until-trigger"
                        class="flex-1 flex items-center gap-2 px-4 py-3 min-w-0 hover:bg-black/5 transition">
                        <i class="fa-solid fa-flag-checkered text-[#6ae6fc] flex-shrink-0"></i>
                        <span id="edit-until-label" class="flex-1 text-sm font-medium text-gray-700 truncate"></span>
                    </button>
                    <div class="w-px h-5 bg-gray-300 flex-shrink-0"></div>
                    <button type="button" id="edit-until-time-btn"
                        class="flex items-center gap-1 pl-3 pr-4 py-3 text-sm text-gray-700 hover:bg-black/5 transition whitespace-nowrap">
                        <span id="edit-until-time-label">--:--</span>
                        <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-1"></i>
                    </button>
                    <input type="hidden" id="edit-until-date" />
                    <input type="hidden" id="edit-until-time" />
                </div>
            </div>

            <hr class="my-6">

            <!-- Submit -->
            <div class="flex gap-4">
                <button type="submit"
                    class="flex-1 py-3 rounded-lg bg-[#6ae6fc] text-gray-900 text-sm font-bold
                       hover:bg-cyan-400 transition shadow-md">
                    Review Changes
                </button>

                <a href="/booking.php?id=<?= $booking['booking_id'] ?>"
                    class="flex-1 py-3 rounded-lg bg-gray-200 text-gray-700 text-sm font-semibold
                       hover:bg-gray-300 transition text-center shadow-sm">
                    Cancel
                </a>
            </div>

        </form>

        <div id="edit-time-error" class="hidden mt-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200"></div>

        <script src="/js/datePicker.js"></script>
        <script>
            (function () {
                const pad = n => String(n).padStart(2, '0');
                const fmtDate = d => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                const fmtTime = d => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

                // Pre-fill from existing booking values
                const initStart = new Date('<?= date('Y-m-d\TH:i:s', strtotime($booking['booking_start'])) ?>');
                const initEnd   = new Date('<?= date('Y-m-d\TH:i:s', strtotime($booking['booking_end'])) ?>');

                const fromDatePicker  = makeDatePicker('edit-from-trigger',  'edit-from-label',  'edit-from-date',  null, 'below');
                const untilDatePicker = makeDatePicker('edit-until-trigger', 'edit-until-label', 'edit-until-date', null, 'below');
                fromDatePicker.select(fmtDate(initStart));
                untilDatePicker.select(fmtDate(initEnd));

                const fromTimePicker  = makeTimePicker('edit-from-time-btn',  'edit-from-time-label',  'edit-from-time',  null, 'below');
                const untilTimePicker = makeTimePicker('edit-until-time-btn', 'edit-until-time-label', 'edit-until-time', null, 'below');
                fromTimePicker.setValue(fmtTime(initStart));
                untilTimePicker.setValue(fmtTime(initEnd));

                // On submit, combine date + time into the fields the backend expects
                document.querySelector('form').addEventListener('submit', function (e) {
                    const fromDate  = document.getElementById('edit-from-date').value;
                    const fromTime  = document.getElementById('edit-from-time').value;
                    const untilDate = document.getElementById('edit-until-date').value;
                    const untilTime = document.getElementById('edit-until-time').value;

                    const errorBox = document.getElementById('edit-time-error');
                    errorBox.classList.add('hidden');

                    if (!fromDate || !fromTime || !untilDate || !untilTime) {
                        e.preventDefault();
                        errorBox.textContent = 'Please select both a date and time for start and end.';
                        errorBox.classList.remove('hidden');
                        return;
                    }

                    const startDT = new Date(`${fromDate}T${fromTime}`);
                    const endDT   = new Date(`${untilDate}T${untilTime}`);

                    if (endDT <= startDT) {
                        e.preventDefault();
                        errorBox.textContent = 'End time must be after start time.';
                        errorBox.classList.remove('hidden');
                        return;
                    }

                    // Inject combined datetime fields
                    const addHidden = (name, value) => {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = name;
                        el.value = value;
                        document.querySelector('form').appendChild(el);
                    };
                    addHidden('start_time', `${fromDate} ${fromTime}`);
                    addHidden('end_time',   `${untilDate} ${untilTime}`);
                });
            })();
        </script>
    </div>
</body>

</html>