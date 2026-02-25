<?php
session_start();

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

// get users car
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/vehicles/ReadVehicles.php';
$ReadVehicles = new ReadVehicles();
$vehicles = $ReadVehicles->getVehiclesByUserId($userId);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Account · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="/css/output.css" rel="stylesheet">

    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>
<body class="bg-[#f3f3f3] pt-20 min-h-screen">

<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-6xl mx-auto px-6 py-12">

    <div class="flex gap-12">

        <!-- Sidebar -->
        <aside class="w-56 text-sm text-[#1e1e4b]">

            <nav class="space-y-4 pb-48">

                <button data-target="dashboard"
                    class="nav-link font-semibold block text-left w-full">
                    Dashboard
                </button>

                <button data-target="bookings"
                    class="nav-link block text-left w-full ">
                    My bookings
                </button>

                <button data-target="payments"
                    class="nav-link block text-left w-full ">
                    Payment Methods
                </button>

                <button data-target="profile"
                    class="nav-link block text-left w-full ">
                    Profile Settings
                </button>

                <button data-target="vehicle"
                    class="nav-link block text-left w-full ">
                    My vehicle
                </button>

                <button data-target="rent"
                    class="nav-link block text-left w-full ">
                    Rent My Space
                </button>

                <button data-target="listings"
                    class="nav-link block text-left w-full ">
                    My listings
                </button>

                <a href="/logout.php"
                    class="block hover:underline mt-6">
                    Log Out
                </a>

            </nav>

        </aside>

        <!-- Content -->
        <main class="flex-1">

            <!-- Dashboard Panel -->
            <section data-section="dashboard">

                <div class="bg-white border border-gray-300 p-8">

                    <p class="text-gray-700 mb-4">
                        Hello <strong>Henry</strong> (not Henry?
                        <a href="/logout.php" class="text-[#1e1e4b] font-semibold hover:underline">
                            Log out
                        </a>)
                    </p>

                    <p class="text-gray-600">
                        From your account dashboard you can view your
                        <a href="#" class="text-[#1e1e4b] font-semibold hover:underline">
                            shipping and billing addresses
                        </a>,
                        and
                        <a href="#" class="text-[#1e1e4b] font-semibold hover:underline">
                            edit your password and account details
                        </a>.
                    </p>

                </div>

            </section>

            <!-- Other Sections -->
            <section data-section="bookings" class="hidden">
                <div class="bg-white border border-gray-300 p-8">

                <h2 class="text-lg font-semibold text-[#1e1e4b] mb-6">
                    My bookings
                </h2>

                <?php if (empty($bookings)): ?>

                    <p class="text-gray-600 text-sm">
                        You have no bookings yet.
                    </p>

                <?php else: ?>

                    <div class="divide-y divide-gray-200">

                        <?php foreach ($bookings as $booking): ?>

                            <?php
                                $now = new DateTime();
                                $bookingEnd = new DateTime($booking['booking_end']);
                                $isExpired = $bookingEnd < $now;
                            ?>

                            <div class="py-6 flex justify-between items-start">

                                <!-- Left Info -->
                                <div class="space-y-1">

                                    <p class="font-semibold text-gray-800">
                                        <?= htmlspecialchars($booking['carpark_name']) ?>
                                    </p>

                                    <p class="text-sm text-gray-500">
                                        <?= htmlspecialchars($booking['carpark_address']) ?>
                                    </p>

                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Start:</span>
                                        <?= htmlspecialchars($booking['booking_start']) ?>
                                    </p>

                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">End:</span>
                                        <?= htmlspecialchars($booking['booking_end']) ?>
                                    </p>

                                    <p class="text-xs text-gray-400">
                                        Booking ID:
                                        <?= htmlspecialchars($booking['booking_id']) ?>
                                    </p>

                                </div>

                                <!-- Right Side -->
                                <div class="text-right space-y-3">

                                    <?php if ($isExpired): ?>
                                        <span class="inline-block text-xs font-semibold bg-red-100 text-red-600 px-3 py-1 rounded-full">
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
                    Profile settings content here.
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

        <section data-section="rent" class="hidden">

        <div class="bg-white border border-gray-300 p-8">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-semibold text-[#1e1e4b]">
                    My Car Parks
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

            <section data-section="listings" class="hidden">
                <div class="bg-white border border-gray-300 p-8">
                    Listings content here.
                </div>
            </section>

        </main>

    </div>

</div>

<script>
document.querySelectorAll('.nav-link').forEach(button => {
    button.addEventListener('click', () => {

        const target = button.dataset.target;

        document.querySelectorAll('[data-section]').forEach(section => {
            section.classList.add('hidden');
        });

        document.querySelector(`[data-section="${target}"]`)
            .classList.remove('hidden');

        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('font-semibold');
        });

        button.classList.add('font-semibold');
    });
});
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
