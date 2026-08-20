<?php
session_start();
$title   = "Booking Flow Log";
$noIndex = true;

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

$conn = Dbh::getConnection();

$bookingFilter = (int) ($_GET['booking'] ?? 0);

if ($bookingFilter) {
    $stmt = $conn->prepare("SELECT * FROM flow_log WHERE booking_id = :id ORDER BY id DESC LIMIT 500");
    $stmt->execute([':id' => $bookingFilter]);
} else {
    $stmt = $conn->query("SELECT * FROM flow_log ORDER BY id DESC LIMIT 500");
}
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Events that mean a paying customer was left without a confirmation.
$bad = [
    'signature_failed',
    'abort_missing_metadata',
    'abort_carpark_full',
    'abort_no_session_data',
    'booking_failed',
    'notify_SKIPPED',
    'notify_threw',
    'send_failed',
    'send_skipped_no_recipient',
];
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>

<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-6xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Booking Flow Log</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Every decision point in the booking payment flow. Red rows mean a customer paid
                    and did not get a confirmation.
                </p>
            </div>
            <a href="/admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Admin
            </a>
        </div>

        <form method="GET" class="mb-6 flex gap-3">
            <input type="number" name="booking" min="1" placeholder="Filter by booking ID"
                value="<?= $bookingFilter ?: '' ?>"
                class="py-2 px-4 rounded-lg bg-white text-gray-800 text-sm border border-gray-200">
            <button type="submit" class="px-5 py-2 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition">
                Filter
            </button>
            <?php if ($bookingFilter): ?>
                <a href="/flow-log.php" class="px-5 py-2 text-sm text-gray-500 hover:text-gray-800 self-center">Clear</a>
            <?php endif; ?>
        </form>

        <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">When</th>
                        <th class="text-left px-4 py-3">Source</th>
                        <th class="text-left px-4 py-3">Event</th>
                        <th class="text-left px-4 py-3">Booking</th>
                        <th class="text-left px-4 py-3">Payment ref</th>
                        <th class="text-left px-4 py-3">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!$rows): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Nothing logged yet. Make a test booking.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($rows as $r): ?>
                        <?php $isBad = in_array($r['event'], $bad, true); ?>
                        <tr class="<?= $isBad ? 'bg-red-50' : '' ?>">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap"><?= htmlspecialchars($r['created_at']) ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= htmlspecialchars($r['source']) ?></td>
                            <td class="px-4 py-3 font-semibold <?= $isBad ? 'text-red-700' : 'text-gray-900' ?>">
                                <?= htmlspecialchars($r['event']) ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($r['booking_id']): ?>
                                    <a href="/flow-log.php?booking=<?= (int) $r['booking_id'] ?>" class="text-cyan-600 hover:underline">
                                        #<?= (int) $r['booking_id'] ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300">–</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs font-mono"><?= htmlspecialchars((string) $r['stripe_ref']) ?></td>
                            <td class="px-4 py-3 text-gray-600 text-xs"><?= htmlspecialchars((string) $r['detail']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>

</html>
