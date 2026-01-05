<?php
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Get all car parks
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getAllCarparks();

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Panel · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/output.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-gray-100 pt-20">

    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-7xl mx-auto px-4 mt-10">

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                Admin Panel
            </h1>
            <p class="text-gray-500 mt-2">
                Manage all car parks on the platform
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-sm text-gray-500 mb-1">Total Car Parks</p>
                <p class="text-3xl font-bold text-gray-800"><?= count($carparks) ?></p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-sm text-gray-500 mb-1">Bookable</p>
                <p class="text-3xl font-bold text-green-600">
                    <?= count(array_filter($carparks, fn($cp) => $cp['carpark_type'] === 'bookable')) ?>
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-sm text-gray-500 mb-1">Affiliate</p>
                <p class="text-3xl font-bold text-blue-600">
                    <?= count(array_filter($carparks, fn($cp) => $cp['carpark_type'] === 'affiliate')) ?>
                </p>
            </div>
        </div>

        <!-- Search/Filter Bar -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <div class="flex flex-col md:flex-row gap-4">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Search by name or address..."
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    onkeyup="filterCarparks()"
                >
                <select
                    id="type-filter"
                    class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    onchange="filterCarparks()"
                >
                    <option value="">All Types</option>
                    <option value="bookable">Bookable</option>
                    <option value="affiliate">Affiliate</option>
                </select>
            </div>
        </div>

        <!-- Car Parks Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left text-sm text-gray-600">
                            <th class="p-4 border-b font-semibold">ID</th>
                            <th class="p-4 border-b font-semibold">Name</th>
                            <th class="p-4 border-b font-semibold">Address</th>
                            <th class="p-4 border-b font-semibold">Type</th>
                            <th class="p-4 border-b font-semibold">Capacity</th>
                            <th class="p-4 border-b font-semibold">Owner ID</th>
                            <th class="p-4 border-b font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="carparks-table">
                        <?php if (empty($carparks)): ?>
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-500">
                                    No car parks found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($carparks as $carpark): ?>
                                <tr class="hover:bg-gray-50 transition carpark-row" 
                                    data-name="<?= strtolower(htmlspecialchars($carpark['carpark_name'])) ?>"
                                    data-address="<?= strtolower(htmlspecialchars($carpark['carpark_address'])) ?>"
                                    data-type="<?= htmlspecialchars($carpark['carpark_type']) ?>">
                                    
                                    <td class="p-4 border-b">
                                        <span class="font-mono text-sm text-gray-600">
                                            #<?= htmlspecialchars($carpark['carpark_id']) ?>
                                        </span>
                                    </td>

                                    <td class="p-4 border-b">
                                        <p class="font-medium text-gray-800">
                                            <?= htmlspecialchars($carpark['carpark_name']) ?>
                                        </p>
                                    </td>

                                    <td class="p-4 border-b">
                                        <p class="text-sm text-gray-600 max-w-xs truncate">
                                            <?= htmlspecialchars($carpark['carpark_address']) ?>
                                        </p>
                                    </td>

                                    <td class="p-4 border-b">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?= 
                                            $carpark['carpark_type'] === 'bookable' 
                                                ? 'bg-green-100 text-green-700' 
                                                : 'bg-blue-100 text-blue-700' 
                                        ?>">
                                            <?= htmlspecialchars(ucfirst($carpark['carpark_type'])) ?>
                                        </span>
                                    </td>

                                    <td class="p-4 border-b text-gray-700">
                                        <?= htmlspecialchars($carpark['carpark_capacity']) ?>
                                    </td>

                                    <td class="p-4 border-b">
                                        <span class="font-mono text-sm text-gray-600">
                                            <?= htmlspecialchars($carpark['carpark_owner']) ?>
                                        </span>
                                    </td>

                                    <td class="p-4 border-b">
                                        <div class="flex gap-2">
                                            <a
                                                href="/carpark.php?id=<?= $carpark['carpark_id'] ?>&admin=1"
                                                class="text-green-600 hover:text-green-800 font-medium text-sm"
                                            >
                                                View/Edit
                                            </a>
                                            <button
                                                type="submit"
                                                formaction="/php/api/index.php?id=deleteCarpark"
                                                formmethod="POST"
                                                name="carpark_id"
                                                value="<?= $carpark['carpark_id'] ?>"
                                                class="text-red-600 hover:text-red-800 font-medium text-sm"
                                                onclick="return confirm('Are you sure you want to delete this car park?');"
                                            >
                                                Delete
                                            </button>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        function filterCarparks() {
            const searchInput = document.getElementById('search-input').value.toLowerCase();
            const typeFilter = document.getElementById('type-filter').value;
            const rows = document.querySelectorAll('.carpark-row');

            rows.forEach(row => {
                const name = row.dataset.name;
                const address = row.dataset.address;
                const type = row.dataset.type;

                const matchesSearch = name.includes(searchInput) || address.includes(searchInput);
                const matchesType = !typeFilter || type === typeFilter;

                if (matchesSearch && matchesType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>

    <br><br>
</body>
</html>