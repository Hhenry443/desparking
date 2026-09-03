<?php
session_start();

$title   = "Receipt";
$noIndex = true;

// Admin-only: receipts are issued on request, by us, through
// resend-confirmation.php. This page is the operator's copy — to read the
// figures back, or to print/PDF one when email isn't what the customer wants.
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? false) !== true) {
    header("Location: /");
    exit;
}

$bookingID = $_GET['id'] ?? null;

if (!$bookingID || !ctype_digit($bookingID)) {
    header("Location: /admin.php");
    exit;
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/helpers/Receipt.php';

$receipt = Receipt::build(Dbh::getConnection(), (int) $bookingID);

if (!$receipt) {
    header("Location: /admin.php");
    exit;
}

$booking     = $receipt['booking'];
$currency    = $receipt['currency'];
$periodLabel = !empty($booking['is_monthly']) ? 'Current period' : 'Parking period';
?>

<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>

<style>
    @media print {
        .no-print { display: none !important; }
        body { background: #fff !important; padding-top: 0 !important; }
        .receipt-sheet { box-shadow: none !important; border: none !important; margin: 0 !important; }
    }
</style>

<body class="min-h-screen bg-[#ebebeb] pt-24">
    <div class="no-print"><?php include_once __DIR__ . '/partials/navbar.php'; ?></div>

    <section class="py-12">
        <div class="max-w-3xl mx-auto px-6">

            <div class="flex items-center justify-between mb-6 no-print">
                <a href="/booking.php?id=<?= (int) $bookingID ?>&admin=1"
                    class="text-sm text-gray-500 hover:text-gray-800 transition">
                    <i class="fa-solid fa-chevron-left text-xs"></i> Back to booking
                </a>
                <button onclick="window.print()"
                    class="px-6 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                    <i class="fa-solid fa-print"></i> Print / Save as PDF
                </button>
            </div>

            <div class="receipt-sheet bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-10">

                <!-- Header -->
                <div class="flex flex-wrap items-start justify-between gap-6 pb-8 border-b border-gray-200">
                    <div>
                        <h1 class="text-3xl font-bold text-[#060745]">Receipt</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            <?= htmlspecialchars(Receipt::COMPANY_TRADING) ?>
                        </p>
                    </div>
                    <div class="text-sm text-gray-600 sm:text-right">
                        <p><span class="text-gray-400">Receipt no.</span>
                            <span class="font-semibold text-gray-900"><?= htmlspecialchars($receipt['number']) ?></span></p>
                        <p><span class="text-gray-400">Issued</span> <?= htmlspecialchars($receipt['issued']) ?></p>
                        <p><span class="text-gray-400">Booking ref</span> #<?= (int) $booking['booking_id'] ?></p>
                    </div>
                </div>

                <!-- Parties -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 py-8 border-b border-gray-200 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Billed to</p>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($booking['booking_name'] ?: 'Customer') ?></p>
                        <?php if (!empty($booking['booking_email'])): ?>
                            <p class="text-gray-600"><?= htmlspecialchars($booking['booking_email']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">Parking at</p>
                        <p class="font-semibold text-gray-900"><?= htmlspecialchars($booking['carpark_name']) ?></p>
                        <p class="text-gray-600"><?= htmlspecialchars($booking['carpark_address']) ?></p>
                        <p class="text-gray-600 mt-2">
                            <span class="text-gray-400"><?= $periodLabel ?>:</span>
                            <?= date('d M Y, H:i', strtotime($booking['booking_start'])) ?>
                            &ndash;
                            <?= date('d M Y, H:i', strtotime($booking['booking_end'])) ?>
                        </p>
                    </div>
                </div>

                <!-- Lines -->
                <table class="w-full text-sm my-8">
                    <thead>
                        <tr class="border-b-2 border-[#060745]">
                            <th class="text-left pb-2 font-semibold text-gray-500">Description</th>
                            <th class="text-right pb-2 font-semibold text-gray-500 whitespace-nowrap">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipt['payments'] as $payment): ?>
                            <tr class="border-b border-gray-100">
                                <td class="py-3">
                                    <p class="<?= $payment['is_refund'] ? 'text-amber-700' : 'text-gray-900' ?>">
                                        <?= htmlspecialchars($payment['label']) ?>
                                    </p>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        <?= $payment['is_refund'] ? 'Refunded' : 'Paid' ?>
                                        <?= date('d M Y', strtotime($payment['created_at'])) ?>
                                        <?php if (!empty($payment['reference'])): ?>
                                            &middot; Ref <?= htmlspecialchars($payment['reference']) ?>
                                        <?php endif; ?>
                                    </p>
                                </td>
                                <td class="py-3 text-right align-top whitespace-nowrap <?= $payment['is_refund'] ? 'text-amber-700' : 'text-gray-900' ?>">
                                    <?= htmlspecialchars(Receipt::money((int) $payment['signed'], $currency)) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($receipt['refunded'] > 0): ?>
                            <tr class="text-sm text-gray-600">
                                <td class="pt-4">Total charged</td>
                                <td class="pt-4 text-right whitespace-nowrap">
                                    <?= htmlspecialchars(Receipt::money((int) $receipt['charged'], $currency)) ?>
                                </td>
                            </tr>
                            <tr class="text-sm text-amber-700">
                                <td class="py-1">Total refunded</td>
                                <td class="py-1 text-right whitespace-nowrap">
                                    <?= htmlspecialchars(Receipt::money(-(int) $receipt['refunded'], $currency)) ?>
                                </td>
                            </tr>
                        <?php endif; ?>

                        <tr>
                            <td class="pt-5 text-lg font-bold text-[#060745]">
                                <?= $receipt['refunded'] > 0 ? 'Net paid' : 'Total paid' ?>
                            </td>
                            <td class="pt-5 text-lg font-bold text-[#060745] text-right whitespace-nowrap">
                                <?= htmlspecialchars(Receipt::money((int) $receipt['total'], $currency)) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Company footer -->
                <div class="pt-6 border-t border-gray-200 text-xs text-gray-500 leading-relaxed">
                    <p>
                        <?= htmlspecialchars(Receipt::COMPANY_NAME) ?>, trading as
                        <?= htmlspecialchars(Receipt::COMPANY_TRADING) ?>.
                        Registered in England and Wales, company number <?= htmlspecialchars(Receipt::COMPANY_NUMBER) ?>.
                    </p>
                    <p><?= htmlspecialchars(Receipt::COMPANY_ADDRESS) ?></p>
                </div>

            </div>

        </div>
    </section>

    <div class="no-print"><?php include_once __DIR__ . '/partials/footer.php'; ?></div>
</body>

</html>
