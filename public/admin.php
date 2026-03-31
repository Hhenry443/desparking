<?php
session_start();

$title = "Admin Panel";

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carparks = $ReadCarparks->getAllCarparks();
$pendingCarparks = $ReadCarparks->getPendingCarparks();

?>
<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>

<body class="min-h-screen bg-[#ebebeb] pt-20">

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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-sm text-gray-500 mb-1">Total Car Parks</p>
                <p class="text-3xl font-bold text-gray-800"><?= count($carparks) ?></p>
            </div>
            <div class="bg-white rounded-xl shadow-md p-6">
                <p class="text-sm text-gray-500 mb-1">Pending Approval</p>
                <p class="text-3xl font-bold text-amber-500"><?= count($pendingCarparks) ?></p>
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

        <?php if (!empty($pendingCarparks)): ?>
        <!-- Pending Approvals -->
        <div class="mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-100 text-amber-600 text-xs font-bold">
                    <?= count($pendingCarparks) ?>
                </span>
                Pending Approval
            </h2>
            <div class="space-y-4">
                <?php foreach ($pendingCarparks as $cp): ?>
                <div class="bg-white rounded-xl shadow-md p-5 border-l-4 border-amber-400">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">

                        <!-- Carpark details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-bold text-gray-900"><?= htmlspecialchars($cp['carpark_name']) ?></p>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Pending</span>
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?= $cp['carpark_type'] === 'affiliate' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' ?>">
                                    <?= ucfirst(htmlspecialchars($cp['carpark_type'])) ?>
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($cp['carpark_address']) ?></p>

                            <?php if ($cp['carpark_description']): ?>
                                <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($cp['carpark_description']) ?></p>
                            <?php endif; ?>

                            <!-- Owner contact details -->
                            <div class="bg-gray-50 rounded-lg p-3 text-sm space-y-1">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Owner Contact</p>
                                <?php if ($cp['user_email']): ?>
                                    <p class="text-gray-700"><span class="font-medium">Email:</span>
                                        <a href="mailto:<?= htmlspecialchars($cp['user_email']) ?>" class="text-[#060745] hover:underline">
                                            <?= htmlspecialchars($cp['user_email']) ?>
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if ($cp['owner_phone']): ?>
                                    <p class="text-gray-700"><span class="font-medium">Phone:</span> <?= htmlspecialchars($cp['owner_phone']) ?></p>
                                <?php endif; ?>
                                <?php if ($cp['owner_address']): ?>
                                    <p class="text-gray-700"><span class="font-medium">Address:</span> <?= htmlspecialchars($cp['owner_address']) ?></p>
                                <?php endif; ?>
                                <?php if (!$cp['user_email'] && !$cp['owner_phone'] && !$cp['owner_address']): ?>
                                    <p class="text-gray-400 italic">No contact details provided.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex lg:flex-col gap-2 flex-shrink-0">
                            <form method="POST" action="/php/api/index.php?id=approveCarpark">
                                <input type="hidden" name="carpark_id" value="<?= $cp['carpark_id'] ?>">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg transition">
                                    Approve
                                </button>
                            </form>
                            <a href="/carpark.php?id=<?= $cp['carpark_id'] ?>&admin=1"
                                class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-lg transition text-center">
                                View / Edit
                            </a>
                            <form method="POST" action="/php/api/index.php?id=deleteCarpark"
                                onsubmit="return confirm('Delete this pending carpark?')">
                                <input type="hidden" name="carpark_id" value="<?= $cp['carpark_id'] ?>">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold rounded-lg transition">
                                    Reject
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search/Filter Bar -->
        <div class="bg-white rounded-xl shadow-md p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Search by name or address..."
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    onkeyup="filterCarparks()">
                <select
                    id="type-filter"
                    class="rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    onchange="filterCarparks()">
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
                                                class="text-green-600 hover:text-green-800 font-medium text-sm">
                                                View/Edit
                                            </a>
                                            <button
                                                type="submit"
                                                formaction="/php/api/index.php?id=deleteCarpark"
                                                formmethod="POST"
                                                name="carpark_id"
                                                value="<?= $carpark['carpark_id'] ?>"
                                                class="text-red-600 hover:text-red-800 font-medium text-sm"
                                                onclick="return confirm('Are you sure you want to delete this car park?');">
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