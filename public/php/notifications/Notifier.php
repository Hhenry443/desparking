<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/mail.php';

$possiblePaths = [
    $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php',
    $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
];

$autoloadPath = null;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        $autoloadPath = $path;
        break;
    }
}

require_once $autoloadPath;

class Notifier
{
    private PDO $db;

    public function __construct(PDO $conn)
    {
        $this->db = $conn;
    }

    // =========================================================================
    // PUBLIC NOTIFICATION METHODS
    // =========================================================================

    /** New one-time booking confirmed — customer + owner */
    public function bookingConfirmed(int $bookingId, int $userId): void
    {
        $booking = $this->fetchBookingWithCarpark($bookingId);
        $customer = $this->fetchUser($userId);
        if (!$booking || !$customer) return;

        $owner = $this->fetchUser((int) $booking['carpark_owner']);

        $start = date('D d M Y, H:i', strtotime($booking['booking_start']));
        $end   = date('D d M Y, H:i', strtotime($booking['booking_end']));

        // → Customer
        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your parking booking is confirmed. Here are your details:</p>

            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$booking['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Address</td><td style='padding:8px 0'>{$booking['carpark_address']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Arrive</td><td style='padding:8px 0;font-weight:600'>{$start}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Leave by</td><td style='padding:8px 0;font-weight:600'>{$end}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
            </table>

            <div style='margin-top:20px;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #eee;'>
                <p style='margin:0 0 8px 0;font-weight:600;'>Access instructions</p>
                <p style='margin:0;color:#555;line-height:1.5;'>
                    {$booking['access_instructions']}
                </p>
            </div>

            <p style='margin-top:20px'>
                You can view and manage your booking at any time from your 
                <a href='https://everyonesparking.com/account' style='color:#6ae6fc'>account page</a>.
            </p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Booking confirmed – ' . $booking['carpark_name'], $this->htmlWrap('Booking Confirmed', $body));

        // → Owner
        if ($owner) {
            $ownerBody = "
                <p>Hi {$owner['user_name']},</p>
                <p>A new booking has been made at <strong>{$booking['carpark_name']}</strong>.</p>
                <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                    <tr><td style='padding:8px 0;color:#666;width:40%'>Customer</td><td style='padding:8px 0;font-weight:600'>{$customer['user_name']}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Arrive</td><td style='padding:8px 0;font-weight:600'>{$start}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Leave by</td><td style='padding:8px 0;font-weight:600'>{$end}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
                </table>
            ";
            $this->send($owner['user_email'], $owner['user_name'], 'New booking at ' . $booking['carpark_name'], $this->htmlWrap('New Booking', $ownerBody));
        }
    }

    /** New monthly subscription created — customer + owner */
    public function subscriptionCreated(int $bookingId, int $userId): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        $customer = $this->fetchUser($userId);
        if (!$booking || !$customer) return;

        $owner = $this->fetchUser((int) $booking['carpark_owner']);
        $from  = date('D d M Y', strtotime($booking['booking_start']));

        // → Customer
        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your monthly parking subscription at <strong>{$booking['carpark_name']}</strong> is now active.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$booking['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Address</td><td style='padding:8px 0'>{$booking['carpark_address']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Active from</td><td style='padding:8px 0;font-weight:600'>{$from}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
            </table>

            <div style='margin-top:20px;padding:16px;background:#f9fafb;border-radius:8px;border:1px solid #eee;'>
                <p style='margin:0 0 8px 0;font-weight:600;'>Access instructions</p>
                <p style='margin:0;color:#555;line-height:1.5;'>
                    {$booking['access_instructions']}
                </p>
            </div>

            <p style='margin-top:20px'>Your subscription renews automatically each month. You can cancel at any time from your <a href='https://desparking.co.uk/account.php' style='color:#6ae6fc'>account page</a>.</p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Monthly subscription confirmed – ' . $booking['carpark_name'], $this->htmlWrap('Subscription Active', $body));

        // → Owner
        if ($owner) {
            $ownerBody = "
                <p>Hi {$owner['user_name']},</p>
                <p>A new monthly subscriber has joined <strong>{$booking['carpark_name']}</strong>.</p>
                <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                    <tr><td style='padding:8px 0;color:#666;width:40%'>Customer</td><td style='padding:8px 0;font-weight:600'>{$customer['user_name']}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Active from</td><td style='padding:8px 0;font-weight:600'>{$from}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
                </table>
            ";
            $this->send($owner['user_email'], $owner['user_name'], 'New monthly subscriber at ' . $booking['carpark_name'], $this->htmlWrap('New Subscriber', $ownerBody));
        }
    }

    /** Subscription payment failed — customer */
    public function subscriptionPaymentFailed(string $subscriptionId): void
    {
        $stmt = $this->db->prepare("
            SELECT p.user_id, p.booking_id, b.booking_carpark_id,
                   c.carpark_name
            FROM payments p
            INNER JOIN bookings b ON b.booking_id = p.booking_id
            INNER JOIN carparks c ON c.carpark_id = b.booking_carpark_id
            WHERE p.stripe_subscription_id = :sub_id
            LIMIT 1
        ");
        $stmt->execute([':sub_id' => $subscriptionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return;

        $customer = $this->fetchUser((int) $row['user_id']);
        if (!$customer) return;

        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>We were unable to collect the monthly payment for your parking subscription at <strong>{$row['carpark_name']}</strong>.</p>
            <p>Stripe will automatically retry the payment. To avoid losing access, please ensure your payment method is up to date.</p>
            <p style='margin-top:20px'><a href='https://desparking.co.uk/account.php' style='color:#6ae6fc'>Manage your account</a></p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Payment failed – action required', $this->htmlWrap('Payment Failed', $body));
    }

    /** Monthly subscription cancelled — customer + owner */
    public function subscriptionCancelled(int $bookingId, int $userId, string $accessUntil): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        $customer = $this->fetchUser($userId);
        if (!$booking || !$customer) return;

        $owner = $this->fetchUser((int) $booking['carpark_owner']);

        // → Customer
        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your monthly subscription at <strong>{$booking['carpark_name']}</strong> has been cancelled.</p>
            <p>You will keep access to the car park until <strong>{$accessUntil}</strong>, after which no further charges will be made.</p>
            <p style='margin-top:20px'>If you change your mind, you can start a new subscription from the <a href='https://desparking.co.uk/map.php' style='color:#6ae6fc'>map page</a>.</p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Subscription cancelled – ' . $booking['carpark_name'], $this->htmlWrap('Subscription Cancelled', $body));

        // → Owner
        if ($owner) {
            $ownerBody = "
                <p>Hi {$owner['user_name']},</p>
                <p>A monthly subscriber at <strong>{$booking['carpark_name']}</strong> has cancelled their subscription.</p>
                <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                    <tr><td style='padding:8px 0;color:#666;width:40%'>Customer</td><td style='padding:8px 0;font-weight:600'>{$customer['user_name']}</td></tr>
                    <tr><td style='padding:8px 0;color:#666'>Access until</td><td style='padding:8px 0;font-weight:600'>{$accessUntil}</td></tr>
                </table>
                <p style='margin-top:16px;font-size:13px;color:#666'>The parking space will be available again after {$accessUntil}.</p>
            ";
            $this->send($owner['user_email'], $owner['user_name'], 'Subscription cancellation at ' . $booking['carpark_name'], $this->htmlWrap('Subscriber Leaving', $ownerBody));
        }
    }

    /** Cancellation request submitted — owner */
    public function cancellationRequested(int $bookingId): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        if (!$booking) return;

        $owner    = $this->fetchUser((int) $booking['carpark_owner']);
        $customer = $this->fetchUser((int) $booking['booking_user_id']);
        if (!$owner) return;

        $start = date('D d M Y, H:i', strtotime($booking['booking_start']));
        $end   = date('D d M Y, H:i', strtotime($booking['booking_end']));

        $body = "
            <p>Hi {$owner['user_name']},</p>
            <p>A customer has requested to cancel a booking at <strong>{$booking['carpark_name']}</strong>. Your approval is required.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Customer</td><td style='padding:8px 0;font-weight:600'>" . ($customer['user_name'] ?? 'Unknown') . "</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Arrive</td><td style='padding:8px 0'>{$start}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Leave by</td><td style='padding:8px 0'>{$end}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
            </table>
            <p style='margin-top:20px'>
                <a href='https://desparking.co.uk/booking.php?id={$bookingId}' style='display:inline-block;background:#6ae6fc;color:#111;font-weight:700;padding:10px 20px;border-radius:8px;text-decoration:none;'>Review Request</a>
            </p>
        ";
        $this->send($owner['user_email'], $owner['user_name'], 'Cancellation request – action required', $this->htmlWrap('Cancellation Request', $body));
    }

    /** Cancellation approved — customer */
    public function cancellationApproved(int $bookingId, int $refundAmountPence = 0): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        if (!$booking) return;

        $customer = $this->fetchUser((int) $booking['booking_user_id']);
        if (!$customer) return;

        if ($refundAmountPence > 0) {
            $refundStr = '£' . number_format($refundAmountPence / 100, 2);
            $refundLine = "<p>A refund of <strong>{$refundStr}</strong> has been issued to your original payment method. It typically appears within 5–10 business days.</p>";
        } else {
            $refundLine = "<p>No refund is due under our <a href='https://desparking.co.uk/parking-contract.php' style='color:#6ae6fc'>cancellation policy</a>.</p>";
        }

        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your cancellation request for the booking at <strong>{$booking['carpark_name']}</strong> has been approved.</p>
            {$refundLine}
            <p style='margin-top:16px;font-size:13px;color:#666'>Booking ref: #{$bookingId}</p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Cancellation approved – ' . $booking['carpark_name'], $this->htmlWrap('Cancellation Approved', $body));
    }

    /** Cancellation denied — customer */
    public function cancellationDenied(int $bookingId): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        if (!$booking) return;

        $customer = $this->fetchUser((int) $booking['booking_user_id']);
        if (!$customer) return;

        $start = date('D d M Y, H:i', strtotime($booking['booking_start']));
        $end   = date('D d M Y, H:i', strtotime($booking['booking_end']));

        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your cancellation request for the booking at <strong>{$booking['carpark_name']}</strong> has been denied. Your booking remains active.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$booking['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Arrive</td><td style='padding:8px 0'>{$start}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Leave by</td><td style='padding:8px 0'>{$end}</td></tr>
            </table>
            <p style='margin-top:20px'>If you have questions, please contact us at <a href='mailto:" . MAIL_FROM_ADDRESS . "' style='color:#6ae6fc'>" . MAIL_FROM_ADDRESS . "</a>.</p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Cancellation request declined – ' . $booking['carpark_name'], $this->htmlWrap('Cancellation Declined', $body));
    }

    /** Booking times edited — customer */
    public function bookingEdited(int $bookingId, int $userId): void
    {
        $booking  = $this->fetchBookingWithCarpark($bookingId);
        $customer = $this->fetchUser($userId);
        if (!$booking || !$customer) return;

        $start = date('D d M Y, H:i', strtotime($booking['booking_start']));
        $end   = date('D d M Y, H:i', strtotime($booking['booking_end']));

        $body = "
            <p>Hi {$customer['user_name']},</p>
            <p>Your booking at <strong>{$booking['carpark_name']}</strong> has been updated with new times.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$booking['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>New arrival</td><td style='padding:8px 0;font-weight:600'>{$start}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>New departure</td><td style='padding:8px 0;font-weight:600'>{$end}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Booking ref</td><td style='padding:8px 0'>#" . $bookingId . "</td></tr>
            </table>
            <p style='margin-top:20px'>View your booking: <a href='https://desparking.co.uk/booking.php?id={$bookingId}' style='color:#6ae6fc'>booking #{$bookingId}</a></p>
        ";
        $this->send($customer['user_email'], $customer['user_name'], 'Booking updated – ' . $booking['carpark_name'], $this->htmlWrap('Booking Updated', $body));
    }

    /** New carpark pending approval — admin */
    public function carparkPendingApproval(int $carparkId): void
    {
        $stmt = $this->db->prepare("
            SELECT c.carpark_name, c.carpark_address, u.user_name, u.user_email
            FROM carparks c
            INNER JOIN users u ON u.user_id = c.carpark_owner
            WHERE c.carpark_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $carparkId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return;

        $body = "
            <p>A new car park listing has been submitted and requires your review.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$row['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Address</td><td style='padding:8px 0'>{$row['carpark_address']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Owner</td><td style='padding:8px 0'>{$row['user_name']} ({$row['user_email']})</td></tr>
            </table>
            <p style='margin-top:20px'>
                <a href='https://desparking.co.uk/admin.php' style='display:inline-block;background:#6ae6fc;color:#111;font-weight:700;padding:10px 20px;border-radius:8px;text-decoration:none;'>Review in Admin Panel</a>
            </p>
        ";
        $this->send(ADMIN_EMAIL, 'Admin', 'New car park pending approval – ' . $row['carpark_name'], $this->htmlWrap('New Listing Pending Review', $body));
    }

    /** Carpark approved — owner */
    public function carparkApproved(int $carparkId): void
    {
        $stmt = $this->db->prepare("
            SELECT c.carpark_name, c.carpark_address, u.user_id, u.user_name, u.user_email
            FROM carparks c
            INNER JOIN users u ON u.user_id = c.carpark_owner
            WHERE c.carpark_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $carparkId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return;

        $body = "
            <p>Hi {$row['user_name']},</p>
            <p>Great news — your car park listing has been approved and is now live on Desparking.</p>
            <table style='border-collapse:collapse;width:100%;font-size:14px;'>
                <tr><td style='padding:8px 0;color:#666;width:40%'>Car park</td><td style='padding:8px 0;font-weight:600'>{$row['carpark_name']}</td></tr>
                <tr><td style='padding:8px 0;color:#666'>Address</td><td style='padding:8px 0'>{$row['carpark_address']}</td></tr>
            </table>
            <p style='margin-top:20px'>Drivers can now find and book your space. You can manage your listing from your <a href='https://desparking.co.uk/account.php' style='color:#6ae6fc'>account page</a>.</p>
        ";
        $this->send($row['user_email'], $row['user_name'], 'Your car park is live – ' . $row['carpark_name'], $this->htmlWrap('Car Park Approved', $body));
    }

    /** Payout recorded — owner */
    public function payoutRecorded(int $ownerId, int $amountPence): void
    {
        $owner = $this->fetchUser($ownerId);
        if (!$owner) return;

        $amount = '£' . number_format($amountPence / 100, 2);
        $month  = date('F Y');

        $body = "
            <p>Hi {$owner['user_name']},</p>
            <p>A payout of <strong>{$amount}</strong> has been recorded for {$month} and is being processed to your registered payment details.</p>
            <p style='font-size:13px;color:#666;margin-top:16px'>If you have questions about this payout, please contact us at <a href='mailto:" . MAIL_FROM_ADDRESS . "' style='color:#6ae6fc'>" . MAIL_FROM_ADDRESS . "</a>.</p>
        ";
        $this->send($owner['user_email'], $owner['user_name'], "Payout of {$amount} processed", $this->htmlWrap('Payout Processed', $body));
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function fetchUser(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT user_id, user_name, user_email FROM users WHERE user_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($row && !empty($row['user_email'])) ? $row : null;
    }

    private function fetchBookingWithCarpark(int $bookingId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT b.booking_id, b.booking_user_id, b.booking_start, b.booking_end,
                   b.booking_name, b.is_monthly,
                   c.carpark_name, c.carpark_address, c.carpark_owner, c.access_instructions
            FROM bookings b
            INNER JOIN carparks c ON c.carpark_id = b.booking_carpark_id
            WHERE b.booking_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $bookingId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function htmlWrap(string $title, string $body): string
    {
        $fromName = MAIL_FROM_NAME;
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
        <body style="margin:0;padding:0;background:#f0f0f0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f0f0;padding:32px 16px;">
                <tr><td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                        <!-- Header -->
                        <tr><td style="background:#060745;padding:24px 32px;">
                            <span style="font-size:22px;font-weight:800;color:#6ae6fc;letter-spacing:-0.5px;">{$fromName}</span>
                        </td></tr>
                        <!-- Title -->
                        <tr><td style="padding:28px 32px 0;font-size:20px;font-weight:700;color:#060745;">{$title}</td></tr>
                        <!-- Body -->
                        <tr><td style="padding:16px 32px 32px;font-size:15px;line-height:1.6;color:#333;">
                            {$body}
                        </td></tr>
                        <!-- Footer -->
                        <tr><td style="background:#f8f8f8;padding:16px 32px;border-top:1px solid #eee;font-size:12px;color:#999;">
                            &copy; {$fromName} &nbsp;·&nbsp; This email was sent automatically. Please do not reply.
                        </td></tr>
                    </table>
                </td></tr>
            </table>
        </body>
        </html>
        HTML;
    }

    private function mailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->Port       = MAIL_PORT;
        $mail->SMTPDebug  = 0;

        if (MAIL_USERNAME !== '') {
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
        }

        if (MAIL_ENCRYPTION !== '') {
            $mail->SMTPSecure = MAIL_ENCRYPTION;
        }

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        return $mail;
    }

    private function send(string $to, string $toName, string $subject, string $html): void
    {
        if (empty($to)) {
            error_log("Notifier: skipping send — empty recipient for subject: {$subject}");
            return;
        }

        try {
            $mail = $this->mailer();
            $mail->addAddress($to, $toName);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<p>'], "\n", $html));

            $mail->send();

            error_log("MAIL SENT → {$to} | {$subject}");
        } catch (\Throwable $e) {
            error_log("MAIL FAILED → {$to} | {$subject} | " . $e->getMessage());
        }
    }
}
