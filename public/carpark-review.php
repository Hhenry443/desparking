<?php
session_start();
$title = "Review Carpark Changes";

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

$carparkId = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;
if (!$carparkId) {
    header("Location: /admin.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/carparks/ReadCarparks.php';
$ReadCarparks = new ReadCarparks();
$carpark = $ReadCarparks->getCarparkById($carparkId);

if (!$carpark) {
    header("Location: /admin.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Carparks.php';
$carparkModel = new Carparks();
$pending = $carparkModel->getPendingChanges($carparkId);

if (!$pending) {
    // No pending changes — redirect to admin (maybe it's a new submission, not an edit)
    header("Location: /admin.php");
    exit;
}

$proposed = $pending['proposed_data'];

// Load current photos and unavailable dates for context
$currentPhotos       = $carparkModel->getCarparkPhotos($carparkId);
$currentUnavailable  = $carparkModel->getUnavailableDates($carparkId);

// Field definitions: [json_key => label, live_key_in_carparks_table]
// Where live_key defaults to json_key if not specified
$fields = [
    ['key' => 'carpark_name',          'label' => 'Name'],
    ['key' => 'carpark_description',   'label' => 'Description'],
    ['key' => 'carpark_address',       'label' => 'Address'],
    ['key' => 'carpark_capacity',      'label' => 'Capacity'],
    ['key' => 'access_instructions',   'label' => 'Access Instructions'],
    ['key' => 'carpark_features',      'label' => 'Features'],
    ['key' => 'space_size',            'label' => 'Space Size'],
    ['key' => 'space_type',            'label' => 'Space Type'],
    ['key' => 'requires_key',          'label' => 'Requires Key',         'bool' => true],
    ['key' => 'weekend_available',     'label' => 'Weekend Available',    'bool' => true],
    ['key' => 'min_booking_minutes',   'label' => 'Min Booking (mins)'],
    ['key' => 'is_monthly',            'label' => 'Monthly Booking',      'bool' => true],
    ['key' => 'is_allocated',          'label' => 'Allocated',            'bool' => true],
    ['key' => 'available_from',        'label' => 'Available From'],
    ['key' => 'time_restrictions',     'label' => 'Time Restrictions'],
    ['key' => 'carpark_affiliate_url', 'label' => 'Affiliate URL'],
];

function displayVal($val, bool $isBool = false): string
{
    if ($isBool) return ($val ? 'Yes' : 'No');
    if ($val === null || $val === '') return '<span class="text-gray-400 italic">—</span>';
    return nl2br(htmlspecialchars((string)$val));
}

function valuesMatch($live, $proposed, bool $isBool): bool
{
    if ($isBool) return (bool)$live === (bool)$proposed;
    return (string)$live === (string)$proposed;
}
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>
<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex items-center gap-3 mb-2">
            <a href="/admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Back to Admin
            </a>
        </div>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Review Proposed Changes</h1>
                <p class="text-sm text-gray-500 mt-1">
                    <span class="font-semibold text-gray-700"><?= htmlspecialchars($carpark['carpark_name']) ?></span>
                    &nbsp;·&nbsp; ID #<?= $carparkId ?>
                    &nbsp;·&nbsp; Submitted <?= date('d M Y H:i', strtotime($pending['submitted_at'])) ?>
                </p>
            </div>
            <div class="flex gap-3">
                <form method="POST" action="/php/api/index.php?id=approveCarpark">
                    <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                    <button type="submit"
                        class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                        Approve Changes
                    </button>
                </form>
                <form method="POST" action="/php/api/index.php?id=rejectCarparkChanges"
                    onsubmit="return confirm('Discard these changes? The carpark will stay live with its current content.')">
                    <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                    <button type="submit"
                        class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold rounded-xl transition shadow-sm border border-red-200">
                        Reject Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Legend -->
        <div class="flex gap-4 mb-6 text-xs font-semibold">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-100 border border-amber-300 inline-block"></span> Changed</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-white border border-gray-200 inline-block"></span> Unchanged</span>
        </div>

        <!-- Diff table -->
        <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-hidden mb-8">
            <div class="grid grid-cols-3 text-xs font-bold text-gray-500 uppercase tracking-wide bg-gray-50 border-b border-gray-100 px-4 py-3">
                <div>Field</div>
                <div>Current (live)</div>
                <div>Proposed</div>
            </div>

            <?php foreach ($fields as $f):
                $isBool   = !empty($f['bool']);
                $liveVal  = $carpark[$f['key']] ?? null;
                $propVal  = $proposed[$f['key']] ?? null;
                $changed  = !valuesMatch($liveVal, $propVal, $isBool);
            ?>
                <div class="grid grid-cols-3 border-b border-gray-50 px-4 py-3 <?= $changed ? 'bg-amber-50' : '' ?>">
                    <div class="text-sm font-semibold text-gray-700 pr-4 pt-0.5">
                        <?= htmlspecialchars($f['label']) ?>
                        <?php if ($changed): ?>
                            <span class="ml-1 text-xs text-amber-600 font-bold">●</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-600 pr-4 whitespace-pre-wrap break-words">
                        <?= displayVal($liveVal, $isBool) ?>
                    </div>
                    <div class="text-sm pr-4 whitespace-pre-wrap break-words <?= $changed ? 'text-gray-900 font-medium' : 'text-gray-600' ?>">
                        <?= displayVal($propVal, $isBool) ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Unavailable dates -->
            <?php
            $propDates    = $proposed['unavailable_dates'] ?? [];
            $datesChanged = $currentUnavailable !== $propDates;
            ?>
            <div class="grid grid-cols-3 border-b border-gray-50 px-4 py-3 <?= $datesChanged ? 'bg-amber-50' : '' ?>">
                <div class="text-sm font-semibold text-gray-700 pr-4 pt-0.5">
                    Unavailable Dates
                    <?php if ($datesChanged): ?><span class="ml-1 text-xs text-amber-600 font-bold">●</span><?php endif; ?>
                </div>
                <div class="text-sm text-gray-600 pr-4">
                    <?= $currentUnavailable ? implode(', ', array_map('htmlspecialchars', $currentUnavailable)) : '<span class="text-gray-400 italic">—</span>' ?>
                </div>
                <div class="text-sm pr-4 <?= $datesChanged ? 'text-gray-900 font-medium' : 'text-gray-600' ?>">
                    <?= $propDates ? implode(', ', array_map('htmlspecialchars', $propDates)) : '<span class="text-gray-400 italic">—</span>' ?>
                </div>
            </div>
        </div>

        <!-- Photos -->
        <?php if (!empty($currentPhotos)): ?>
        <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6 mb-8">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Current Photos</h2>
            <div class="flex flex-wrap gap-3">
                <?php foreach ($currentPhotos as $photo): ?>
                    <img src="<?= htmlspecialchars($photo['photo_path']) ?>"
                         class="h-28 w-40 object-cover rounded-xl border border-gray-100">
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Bottom actions -->
        <div class="flex gap-3">
            <form method="POST" action="/php/api/index.php?id=approveCarpark">
                <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                <button type="submit"
                    class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                    Approve Changes
                </button>
            </form>
            <form method="POST" action="/php/api/index.php?id=rejectCarparkChanges"
                onsubmit="return confirm('Discard these changes? The carpark will stay live with its current content.')">
                <input type="hidden" name="carpark_id" value="<?= $carparkId ?>">
                <button type="submit"
                    class="px-6 py-3 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-bold rounded-xl transition border border-red-200">
                    Reject Changes
                </button>
            </form>
            <a href="/carpark.php?id=<?= $carparkId ?>&admin=1"
               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-bold rounded-xl transition">
                Full Edit View
            </a>
        </div>

    </div>
</body>
</html>
