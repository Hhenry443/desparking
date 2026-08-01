<?php

/**
 * TEMPORARY SMTP diagnostic. DELETE THIS FILE once the mail issue is resolved.
 *
 * Usage:  https://everyonesparking.com/mail-diagnostic.php?token=<TOKEN>&to=you@example.com
 *
 * Set TOKEN below to a random string before uploading. Without a matching
 * token the script does nothing, so it cannot be probed by strangers.
 *
 * Prints the full SMTP conversation, which the normal booking flow swallows.
 */

const TOKEN = 'fund-MOP-heavy';

header('Content-Type: text/plain; charset=utf-8');

if (!hash_equals(TOKEN, $_GET['token'] ?? '')) {
    http_response_code(404);
    exit("Not found\n");
}

require_once __DIR__ . '/../vendor/autoload.php';
include_once __DIR__ . '/php/config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;

$to = $_GET['to'] ?? '';
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    exit("Pass a valid ?to= address\n");
}

// ---------------------------------------------------------------- 1. config
echo "=== RESOLVED CONFIG ===\n";
printf("MAIL_HOST         %s\n", MAIL_HOST);
printf("MAIL_PORT         %d\n", MAIL_PORT);
printf("MAIL_USERNAME     %s\n", MAIL_USERNAME !== '' ? MAIL_USERNAME : '(empty)');
printf("MAIL_PASSWORD     %s\n", MAIL_PASSWORD !== '' ? '(set, ' . strlen(MAIL_PASSWORD) . ' chars)' : '(EMPTY)');
printf("MAIL_ENCRYPTION   %s\n", MAIL_ENCRYPTION !== '' ? MAIL_ENCRYPTION : '(none)');
printf("MAIL_FROM_ADDRESS %s\n", MAIL_FROM_ADDRESS);
printf("MAIL_FROM_NAME    %s\n", MAIL_FROM_NAME);
printf("ADMIN_EMAIL       %s\n", ADMIN_EMAIL);

// If these still show the ddev defaults, getenv() is not seeing your SetEnv vars.
if (MAIL_HOST === 'localhost' || str_contains(MAIL_FROM_ADDRESS, 'ddev.site')) {
    echo "\n*** Defaults are in use — getenv() is NOT picking up your SetEnv values. ***\n";
}

// ------------------------------------------------- 2. raw outbound TCP check
echo "\n=== RAW SOCKET TEST (detects a blocked outbound port) ===\n";
$start = microtime(true);
$sock  = @fsockopen(MAIL_HOST, MAIL_PORT, $errNo, $errStr, 10);
$ms    = round((microtime(true) - $start) * 1000);

if (!$sock) {
    echo "FAILED after {$ms}ms: [{$errNo}] {$errStr}\n";
    echo "Outbound " . MAIL_PORT . " is almost certainly blocked by the host, or DNS failed.\n";
} else {
    echo "Connected in {$ms}ms. Banner: " . trim((string) fgets($sock, 512)) . "\n";
    fclose($sock);
}

// ------------------------------------------------------ 3. full PHPMailer run
echo "\n=== PHPMAILER SMTP CONVERSATION ===\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = MAIL_HOST;
    $mail->Port       = MAIL_PORT;
    $mail->SMTPDebug  = 2;                       // full client/server dialogue
    $mail->Debugoutput = function ($str, $level) {
        echo rtrim($str) . "\n";
    };

    if (MAIL_USERNAME !== '') {
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
    }
    if (MAIL_ENCRYPTION !== '') {
        $mail->SMTPSecure = MAIL_ENCRYPTION;
    }

    $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
    $mail->addAddress($to);
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Diagnostic ' . date('H:i:s');
    $mail->Body    = '<p>If you are reading this, SMTP works and the fault is in the booking flow, not the transport.</p>';
    $mail->AltBody = 'If you are reading this, SMTP works.';

    $mail->send();
    echo "\nRESULT: SENT OK\n";
} catch (\Throwable $e) {
    echo "\nRESULT: FAILED\n";
    echo "Exception: " . get_class($e) . "\n";
    echo "Message:   " . $e->getMessage() . "\n";
    echo "PHPMailer: " . $mail->ErrorInfo . "\n";
}
