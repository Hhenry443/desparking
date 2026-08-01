<?php

/**
 * TEMPORARY booking-notification diagnostic. DELETE THIS FILE when done.
 *
 * Usage:  https://everyonesparking.com/booking-diagnostic.php?token=<TOKEN>&booking=<ID>
 *
 * Set TOKEN below to a fresh random string before uploading.
 *
 * SMTP is known good, so the fault is between "booking committed" and
 * "$mail->send()". Every error in that stretch is currently swallowed by
 * Notifier::send() and again by each caller's try/catch. This script walks the
 * same path with nothing suppressed, and redirects error_log() to a buffer so
 * the MAIL SENT / MAIL FAILED line from inside Notifier is captured too.
 *
 * Pick a booking ID that a real customer paid for and did not get an email for.
 */

const TOKEN = '12345';

header('Content-Type: text/plain; charset=utf-8');

if (!hash_equals(TOKEN, $_GET['token'] ?? '')) {
    http_response_code(404);
    exit("Not found\n");
}

$bookingId = (int) ($_GET['booking'] ?? 0);
if (!$bookingId) {
    exit("Pass ?booking=<id>\n");
}

// Capture everything error_log() emits during this request.
$logFile = tempnam(sys_get_temp_dir(), 'diag');
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/php/config/db.php';
include_once __DIR__ . '/php/notifications/Notifier.php';

$conn = Dbh::getConnection();

// ------------------------------------------------------------ 1. booking row
echo "=== RAW BOOKING ROW ===\n";
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = :id LIMIT 1");
$stmt->execute([':id' => $bookingId]);
$raw = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$raw) {
    exit("No booking #{$bookingId} exists. Nothing else to check.\n");
}
foreach ($raw as $k => $v) {
    printf("  %-22s %s\n", $k, $v === null ? '(NULL)' : $v);
}

// ------------------------------- 2. the exact join Notifier depends on
// Notifier::fetchBookingWithCarpark() INNER JOINs carparks. If that join finds
// nothing, or a selected column is missing, every notification method hits
// `if (!$booking) return;` and exits silently having sent nothing.
echo "\n=== NOTIFIER'S LOOKUP (INNER JOIN carparks) ===\n";
try {
    $stmt = $conn->prepare("
        SELECT b.booking_id, b.booking_user_id, b.booking_start, b.booking_end,
               b.booking_name, b.booking_email, b.is_monthly,
               c.carpark_name, c.carpark_address, c.carpark_owner,
               c.access_instructions, c.time_restrictions
        FROM bookings b
        INNER JOIN carparks c ON c.carpark_id = b.booking_carpark_id
        WHERE b.booking_id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $bookingId]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo "  *** RETURNED NOTHING — this is the bug. ***\n";
        echo "  The booking exists but the INNER JOIN to carparks matched no row,\n";
        echo "  so Notifier returns early and sends nothing.\n";
        echo "  booking_carpark_id = " . var_export($raw['booking_carpark_id'] ?? null, true) . "\n";

        $c = $conn->prepare("SELECT carpark_id FROM carparks WHERE carpark_id = :id");
        $c->execute([':id' => $raw['booking_carpark_id'] ?? 0]);
        echo "  carpark row exists? " . ($c->fetch() ? "yes" : "NO — orphaned booking") . "\n";
    } else {
        echo "  OK, row found.\n";
        foreach ($booking as $k => $v) {
            printf("  %-22s %s\n", $k, $v === null ? '(NULL)' : $v);
        }
    }
} catch (Throwable $e) {
    echo "  *** QUERY THREW — this is the bug. ***\n";
    echo "  " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "  A column in this SELECT is missing from your production schema.\n";
    $booking = null;
}

// --------------------------------------------- 3. who would it send to
if (!empty($booking)) {
    echo "\n=== RECIPIENT RESOLUTION ===\n";
    $userId = (int) ($booking['booking_user_id'] ?? 0);
    echo "  path: " . ($userId ? "bookingConfirmed (account holder)" : "bookingConfirmedGuest (guest)") . "\n";

    $toEmail = $booking['booking_email'] ?? '';
    echo "  booking_email: " . ($toEmail !== '' ? $toEmail : '(EMPTY)') . "\n";

    if ($userId) {
        $u = $conn->prepare("SELECT user_id, user_name, user_email FROM users WHERE user_id = :id LIMIT 1");
        $u->execute([':id' => $userId]);
        $customer = $u->fetch(PDO::FETCH_ASSOC);
        echo "  users row:     " . ($customer ? ($customer['user_email'] ?: '(user_email EMPTY)') : 'NOT FOUND') . "\n";
        if ($toEmail === '' && (!$customer || empty($customer['user_email']))) {
            echo "  *** No usable recipient — Notifier returns early. ***\n";
        }
    } elseif ($toEmail === '') {
        echo "  *** Guest booking with no email stored — Notifier returns early. ***\n";
    }

    $o = $conn->prepare("SELECT user_id, user_name, user_email FROM users WHERE user_id = :id LIMIT 1");
    $o->execute([':id' => (int) $booking['carpark_owner']]);
    $owner = $o->fetch(PDO::FETCH_ASSOC);
    echo "  owner:         " . ($owner ? ($owner['user_email'] ?: '(EMPTY)') : 'NOT FOUND (owner email skipped)') . "\n";

    // ------------------------------------------ 4. actually run it
    echo "\n=== LIVE NOTIFIER CALL ===\n";
    try {
        $notifier = new Notifier($conn);
        if ($userId) {
            $notifier->bookingConfirmed($bookingId, $userId);
        } else {
            $notifier->bookingConfirmedGuest($bookingId, $booking['booking_name'] ?? 'Customer', $toEmail);
        }
        echo "  returned without throwing\n";
    } catch (Throwable $e) {
        echo "  *** THREW: " . get_class($e) . " ***\n";
        echo "  " . $e->getMessage() . "\n";
        echo "  at " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// --------------------------------- 5. what Notifier logged internally
echo "\n=== CAPTURED error_log OUTPUT ===\n";
$captured = @file_get_contents($logFile);
echo $captured !== false && trim($captured) !== ''
    ? $captured
    : "  (nothing logged — send() was never reached)\n";
@unlink($logFile);

echo "\nDone. DELETE THIS FILE.\n";
