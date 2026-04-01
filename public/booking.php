<?php
session_start();

$title = "Booking Details";

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$bookingID = $_GET['id'] ?? null;
$isAdminOverride = isset($_GET['admin']) && $_GET['admin'] == '1' && $_SESSION['is_admin'] === true;

if (!$bookingID || !ctype_digit($bookingID)) {
    header("Location: /");
    exit;
}

// Get booking details
include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/ReadBookings.php';
$ReadBookings = new ReadBookings();
$booking = $ReadBookings->getBookingByBookingId((int)$bookingID);

if (!$booking) {
    header("Location: /account.php");
    exit;
}

// Owner-only access (or admin override)
if (!$isAdminOverride && $_SESSION['user_id'] != $booking['booking_user_id'] && $_SESSION['user_id'] != $booking['carpark_owner']) {
    header("Location: /account.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<?php include_once __DIR__ . '/partials/header.php'; ?>

<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <section class="py-16">
        <div class="max-w-6xl mx-auto px-6">

            <!-- Header -->
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-[#060745]">Booking Management</h1>
                <p class="text-gray-600 mt-1">View and manage this booking.</p>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mt-4 p-4 bg-red-100 border border-red-300 text-red-700 rounded-xl text-sm">
                        <?= htmlspecialchars(urldecode($_GET['error'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="mt-4 p-4 bg-green-100 border border-green-300 text-green-800 rounded-xl text-sm">
                        <?= htmlspecialchars(urldecode($_GET['success'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-[0_0_20px_rgba(0,0,0,0.12)] p-8 grid grid-cols-1 lg:grid-cols-2 gap-12">

                <?php
                $now         = new DateTime();
                $bookingEnd  = new DateTime($booking['booking_end']);
                $bookingStart = new DateTime($booking['booking_start']);
                $isMonthly   = !empty($booking['is_monthly']);
                $isCancelled      = ($booking['booking_status'] ?? '') === 'cancelled';
                $isCancelPending  = ($booking['booking_status'] ?? '') === 'cancel_pending';
                $isExpired        = !$isMonthly && !$isCancelled && !$isCancelPending && $bookingEnd < $now;
                $status           = $isCancelled ? 'cancelled' : ($isCancelPending ? 'cancel_pending' : ($isExpired ? 'expired' : 'active'));

                // ── Refund preview (mirrors CancelBooking.php logic) ──────────────
                // For cancel_pending, use the cancellation_requested_at timestamp so the
                // refund type shown to the reviewer matches what ApproveCancelBooking.php will compute.
                if (!$isMonthly && in_array($status, ['active', 'cancel_pending'])) {
                    $refundBaseTime = ($isCancelPending && !empty($booking['cancellation_requested_at']))
                        ? new DateTime($booking['cancellation_requested_at'])
                        : $now;
                }
                if (!$isMonthly && in_array($status, ['active', 'cancel_pending'])) {
                    $totalDays      = (int) ceil(
                        ($bookingEnd->getTimestamp() - $bookingStart->getTimestamp()) / 86400
                    );
                    $isLongTerm     = $totalDays >= 30;
                    $secsUntilStart = $bookingStart->getTimestamp() - $refundBaseTime->getTimestamp();

                    // We don't have the payment amount here without a DB call,
                    // so we just work out the refund *type* for the label.
                    if ($secsUntilStart >= (48 * 3600)) {
                        $cancelRefundLabel = 'Full refund';
                        $cancelRefundClass = 'text-green-700';
                    } elseif ($isLongTerm) {
                        $secsIntoSession = $refundBaseTime->getTimestamp() - $bookingStart->getTimestamp();
                        if ($secsIntoSession <= (48 * 3600)) {
                            $cancelRefundLabel = 'Pro-rata refund for unused days';
                            $cancelRefundClass = 'text-yellow-700';
                        } else {
                            $cancelRefundLabel = 'Refund minus 30 days\' cost';
                            $cancelRefundClass = 'text-yellow-700';
                        }
                    } else {
                        $cancelRefundLabel = 'No refund';
                        $cancelRefundClass = 'text-red-600';
                    }
                }
                ?>

                <!-- Left: Booking Info -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Booking Details</h2>

                    <div class="space-y-3 text-sm text-gray-700">

                        <p>
                            <span class="font-semibold text-gray-900">Type:</span>
                            <?php if ($isMonthly): ?>
                                <span class="inline-block text-xs font-semibold bg-cyan-100 text-cyan-700 px-2 py-0.5 rounded-full ml-1">
                                    Monthly Subscription
                                </span>
                            <?php else: ?>
                                One-time booking
                            <?php endif; ?>
                        </p>

                        <p><span class="font-semibold text-gray-900">Booking ID:</span> #<?= htmlspecialchars($booking['booking_id']) ?></p>
                        <p><span class="font-semibold text-gray-900">Name:</span> <?= htmlspecialchars($booking['booking_name']) ?></p>
                        <p><span class="font-semibold text-gray-900">Car Park:</span> <?= htmlspecialchars($booking['carpark_address']) ?></p>

                        <?php if ($isMonthly): ?>
                            <p><span class="font-semibold text-gray-900">Subscribed:</span> <?= date('d M Y', strtotime($booking['booking_start'])) ?></p>
                            <p>
                                <span class="font-semibold text-gray-900">
                                    <?= $isCancelled ? 'Access until:' : 'Next Renewal:' ?>
                                </span>
                                <?= date('d M Y', strtotime($booking['booking_end'])) ?>
                            </p>
                        <?php else: ?>
                            <p><span class="font-semibold text-gray-900">Start:</span> <?= htmlspecialchars($booking['booking_start']) ?></p>
                            <p><span class="font-semibold text-gray-900">End:</span> <?= htmlspecialchars($booking['booking_end']) ?></p>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Right: Status + Actions -->
                <div>
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        <?= $isMonthly ? 'Subscription Actions' : 'Booking Actions' ?>
                    </h2>

                    <!-- Status -->
                    <div class="mb-6">
                        <?php
                        if ($isMonthly && $isCancelled) {
                            $statusClasses = 'bg-orange-100 text-orange-700';
                            $statusLabel   = 'Cancels ' . date('d M Y', strtotime($booking['booking_end']));
                        } elseif ($isMonthly) {
                            $statusClasses = 'bg-cyan-100 text-cyan-700';
                            $statusLabel   = 'Active subscription';
                        } else {
                            $statusClasses = match ($status) {
                                'active'         => 'bg-green-100 text-green-700',
                                'cancel_pending' => 'bg-amber-100 text-amber-700',
                                'expired'        => 'bg-gray-200 text-gray-700',
                                'cancelled'      => 'bg-red-100 text-red-700',
                                default          => 'bg-yellow-100 text-yellow-700',
                            };
                            $statusLabel = match ($status) {
                                'cancel_pending' => 'Cancellation Pending',
                                default          => ucfirst($status),
                            };
                        }
                        ?>
                        <span class="inline-block px-4 py-1 rounded-full text-sm font-semibold <?= $statusClasses ?>">
                            <?= $statusLabel ?>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <?php if (!$isMonthly && in_array($status, ['active', 'cancel_pending'])): ?>
                        <div class="text-sm bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mb-4">
                            <p class="text-gray-500 mb-1">
                                <?= $isCancelPending ? 'Refund when approved:' : 'Refund if cancelled now:' ?>
                            </p>
                            <p class="font-semibold <?= $cancelRefundClass ?>">
                                <?= htmlspecialchars($cancelRefundLabel) ?>
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Per our <a href="/parking-contract.php" class="underline hover:text-gray-600">cancellation policy</a>.
                                <?php if ($isCancelPending && !empty($booking['cancellation_requested_at'])): ?>
                                    Calculated from request time: <?= date('d M Y H:i', strtotime($booking['cancellation_requested_at'])) ?>.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php
                    $isOwner = !$isAdminOverride && $_SESSION['user_id'] == $booking['carpark_owner'];
                    $canReview = $isOwner || $isAdminOverride;
                    ?>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="/account.php"
                            class="px-6 py-2 rounded-xl bg-gray-200 text-gray-800 font-semibold hover:bg-gray-300">
                            Back
                        </a>

                        <?php if ($isMonthly && !$isCancelled): ?>
                            <form method="POST" action="/php/api/bookings/CancelBooking.php"
                                onsubmit="return confirm('Cancel your monthly subscription? You will keep access until the next renewal date.');">
                                <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                <button type="submit"
                                    class="px-6 py-2 rounded-xl bg-red-100 text-red-700 font-semibold hover:bg-red-200">
                                    Cancel Subscription
                                </button>
                            </form>

                        <?php elseif (!$isMonthly && $status === 'active'): ?>
                            <?php if ($_SESSION['user_id'] == $booking['booking_user_id']): ?>
                                <form method="POST" action="/php/api/bookings/RequestCancelBooking.php"
                                    onsubmit="return confirm('Request cancellation for this booking?\n\nExpected refund: <?= addslashes($cancelRefundLabel) ?>\n\nThe car park owner will need to approve this.');">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <button type="submit"
                                        class="px-6 py-2 rounded-xl bg-red-100 text-red-700 font-semibold hover:bg-red-200">
                                        Request Cancellation
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="/edit-booking.php?id=<?= $booking['booking_id'] ?>"
                                class="px-6 py-2 rounded-xl bg-[#6ae6fc] text-gray-900 font-semibold hover:bg-cyan-400">
                                Edit Booking
                            </a>
                        <?php elseif (!$isMonthly && $status === 'cancel_pending'): ?>
                            <?php if ($canReview): ?>
                                <!-- Owner / admin: approve or deny -->
                                <form method="POST" action="/php/api/bookings/ApproveCancelBooking.php"
                                    onsubmit="return confirm('Approve this cancellation and issue the refund?\n\nRefund: <?= addslashes($cancelRefundLabel) ?>');">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <button type="submit"
                                        class="px-6 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700">
                                        Approve Cancellation
                                    </button>
                                </form>
                                <form method="POST" action="/php/api/bookings/DenyCancelBooking.php"
                                    onsubmit="return confirm('Deny this cancellation request? The booking will remain active.');">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <button type="submit"
                                        class="px-6 py-2 rounded-xl bg-red-100 text-red-700 font-semibold hover:bg-red-200">
                                        Deny Request
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Customer: already requested, show disabled button -->
                                <button type="button" disabled
                                    class="px-6 py-2 rounded-xl bg-gray-100 text-gray-400 font-semibold cursor-not-allowed">
                                    Cancellation Requested
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </section>

</body>

</html>