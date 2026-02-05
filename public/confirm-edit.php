<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$preview = $_SESSION['booking_edit_preview'] ?? null;

if (!$preview) {
    header("Location: /account.php");
    exit;
}

// Optional: expiry so people can’t leave it open forever
if (time() - $preview['preview_created'] > 300) { // 5 minutes
    unset($_SESSION['booking_edit_preview']);
    header("Location: /booking/edit.php?id=" . $preview['booking_id'] . "&error=" . urlencode("Edit session expired"));
    exit;
}

function pounds(int $pence): string {
    return '£' . number_format($pence / 100, 2);
}

$difference = $preview['difference'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Confirm Booking Changes · DesParking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/output.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/01e87deab9.js" crossorigin="anonymous"></script>
</head>

<body class="min-h-screen bg-[#ebebeb] pt-24">
<?php include_once __DIR__ . '/partials/navbar.php'; ?>

<div class="max-w-3xl mx-auto bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 mb-12">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Confirm Booking Changes</h1>
        <p class="text-gray-500 text-sm mt-1">
            Review the changes below before confirming.
        </p>
    </div>

    <?php 
    // Check for errors in URL or session
    $errorMessage = null;
    if (isset($_GET['error'])) {
        $errorMessage = urldecode($_GET['error']);
    } elseif (isset($_SESSION['error'])) {
        $errorMessage = $_SESSION['error'];
        unset($_SESSION['error']); // Clear after displaying
    }
    
    if ($errorMessage): 
    ?>
        <div class="mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Time comparison -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-sm">
        <div class="bg-gray-50 p-4 rounded-xl">
            <p class="font-semibold text-gray-900 mb-2">Current Booking</p>
            <p><span class="text-gray-500">Start:</span> <?= htmlspecialchars($preview['old_start']) ?></p>
            <p><span class="text-gray-500">End:</span> <?= htmlspecialchars($preview['old_end']) ?></p>
            <p class="mt-2 font-semibold"><?= pounds($preview['old_price']) ?></p>
        </div>

        <div class="bg-gray-50 p-4 rounded-xl">
            <p class="font-semibold text-gray-900 mb-2">New Booking</p>
            <p><span class="text-gray-500">Start:</span> <?= htmlspecialchars($preview['new_start']) ?></p>
            <p><span class="text-gray-500">End:</span> <?= htmlspecialchars($preview['new_end']) ?></p>
            <p class="mt-2 font-semibold"><?= pounds($preview['new_price']) ?></p>
        </div>
    </div>

    <!-- Price outcome -->
    <div class="mb-8">
        <?php if ($difference > 0): ?>
            <div class="p-4 bg-amber-50 text-amber-800 rounded-xl text-sm">
                <i class="fa-solid fa-credit-card mr-2"></i>
                You will be charged <strong><?= pounds($difference) ?></strong> to confirm this change.
            </div>
        <?php elseif ($difference < 0): ?>
            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm">
                <i class="fa-solid fa-rotate-left mr-2"></i>
                You will receive a refund of <strong><?= pounds(abs($difference)) ?></strong>
                to your original payment method.
            </div>
        <?php else: ?>
            <div class="p-4 bg-gray-100 text-gray-700 rounded-xl text-sm">
                <i class="fa-solid fa-check mr-2"></i>
                The price remains the same. No payment is required.
            </div>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="flex flex-col md:flex-row gap-4">
        <form method="POST" action="/php/api/bookings/ConfirmEditBooking.php" class="flex-1">
            <button type="submit"
                class="w-full py-3 rounded-lg bg-[#6ae6fc] text-gray-900 font-bold
                       hover:bg-cyan-400 transition shadow-md">
                Confirm Changes
            </button>
        </form>

        <a href="/edit-booking.php?id=<?= $preview['booking_id'] ?>"
            class="flex-1 py-3 rounded-lg bg-gray-200 text-gray-700 font-semibold
                   hover:bg-gray-300 transition text-center shadow-sm">
            Go Back
        </a>
    </div>

    <!-- Fine print -->
    <p class="text-xs text-gray-500 mt-6">
        Refunds may take 5–10 business days to appear, depending on your bank.
    </p>

</div>
</body>
</html>