<?php
session_start();
$title   = "Resend Booking Confirmation";
$noIndex = true;

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId    = (int) ($_POST['booking_id'] ?? 0);
    $overrideEmail = trim($_POST['email'] ?? '');

    if (!$bookingId || !$overrideEmail || !filter_var($overrideEmail, FILTER_VALIDATE_EMAIL)) {
        $result = ['success' => false, 'message' => 'Please enter a valid booking number and email address.'];
    } else {
        require $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';

        $conn = Dbh::getConnection();

        // Fetch the booking name so we have something to address the email to
        $stmt = $conn->prepare("
            SELECT booking_name, is_monthly FROM bookings WHERE booking_id = :id LIMIT 1
        ");
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            $result = ['success' => false, 'message' => "Booking #$bookingId not found."];
        } else {
            try {
                $guestName = $booking['booking_name'] ?: 'Customer';
                $notifier  = new Notifier($conn);
                if ($booking['is_monthly']) {
                    $notifier->subscriptionCreatedGuest($bookingId, $guestName, $overrideEmail);
                } else {
                    $notifier->bookingConfirmedGuest($bookingId, $guestName, $overrideEmail);
                }
                $result = ['success' => true, 'message' => "Confirmation email sent to $overrideEmail for booking #$bookingId."];
            } catch (Throwable $e) {
                error_log("resend-confirmation.php error: " . $e->getMessage());
                $result = ['success' => false, 'message' => 'Failed to send email. Check server logs for details.'];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<?php include_once __DIR__ . '/partials/header.php'; ?>

<body class="min-h-screen bg-[#ebebeb] pt-24">
    <?php include_once __DIR__ . '/partials/navbar.php'; ?>

    <div class="max-w-2xl mx-auto px-6 py-10">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Resend Confirmation</h1>
                <p class="text-sm text-gray-500 mt-1">Send a booking confirmation email to any address — useful for guests who didn't receive theirs.</p>
            </div>
            <a href="/admin.php" class="text-sm text-gray-500 hover:text-gray-800 transition">
                <i class="fa-solid fa-chevron-left text-xs"></i> Admin
            </a>
        </div>

        <?php if ($result): ?>
            <div class="mb-6 p-4 rounded-xl text-sm <?= $result['success'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' ?>">
                <?= htmlspecialchars($result['message']) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-[0_0_16px_rgba(0,0,0,0.08)] p-6">
            <form method="POST" class="space-y-5">

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Booking Number *</label>
                    <input
                        type="number"
                        name="booking_id"
                        min="1"
                        required
                        placeholder="e.g. 42"
                        value="<?= isset($_POST['booking_id']) ? (int) $_POST['booking_id'] : '' ?>"
                        class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Send To Email *</label>
                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="customer@example.com"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                        class="w-full py-3 px-4 rounded-lg bg-gray-100 text-gray-800 text-sm border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#6ae6fc]">
                    <p class="text-xs text-gray-400 mt-1">The confirmation will be addressed to the name on the booking, but sent to this address.</p>
                </div>

                <button type="submit"
                    class="px-6 py-2.5 bg-[#6ae6fc] text-gray-900 text-sm font-bold rounded-xl hover:bg-cyan-400 transition shadow-sm">
                    Send Confirmation
                </button>

            </form>
        </div>

    </div>
</body>

</html>