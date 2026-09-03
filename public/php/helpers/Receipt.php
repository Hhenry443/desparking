<?php

/**
 * Builds the data behind a customer receipt.
 *
 * Two things about the payments table shape this:
 *
 *  - A booking can carry several rows. Extending a booking inserts a second
 *    charge (ConfirmEditBooking.php), so a receipt sums them all.
 *    selectBookingFullDetails() deliberately takes only the latest row for the
 *    admin modal; that shortcut would under-report an extended booking here.
 *
 *  - Refunds are their own rows, with type 'refund' and a *positive* amount
 *    (CancelBooking.php, ApproveCancelBooking.php). They have to be subtracted,
 *    not added.
 */
class Receipt
{
    private const SYMBOLS = [
        'gbp' => '£',
        'eur' => '€',
        'usd' => '$',
        'aud' => '$',
    ];

    // Company details as published in terms-and-conditions.php.
    public const COMPANY_NAME    = 'ONE PERFECT STAY HOLDINGS LIMITED';
    public const COMPANY_TRADING = 'Everyonesparking';
    public const COMPANY_NUMBER  = '16143127';
    public const COMPANY_ADDRESS = 'Flat 71 Discovery Dock Apartments East, South Quay Square, London, England, E14 9RU';

    /**
     * Returns null when the booking doesn't exist or nothing has ever been
     * charged against it — there is nothing to receipt in either case.
     */
    public static function build(PDO $conn, int $bookingId): ?array
    {
        $stmt = $conn->prepare("
            SELECT b.booking_id, b.booking_user_id, b.booking_name, b.booking_email,
                   b.booking_start, b.booking_end, b.booking_status, b.is_monthly,
                   b.booking_access_token,
                   c.carpark_name, c.carpark_address, c.carpark_owner
            FROM bookings b
            INNER JOIN carparks c ON c.carpark_id = b.booking_carpark_id
            WHERE b.booking_id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$booking) return null;

        // 'succeeded' covers refunds too — they are written as settled rows of
        // their own rather than by restatusing the original charge.
        $stmt = $conn->prepare("
            SELECT id, amount, currency, type, paypal_order_id, paypal_capture_id,
                   paypal_subscription_id, created_at
            FROM payments
            WHERE booking_id = :id AND status = 'succeeded'
            ORDER BY created_at ASC, id ASC
        ");
        $stmt->execute([':id' => $bookingId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lines       = [];
        $charged     = 0;
        $refunded    = 0;
        $chargeCount = 0;

        foreach ($rows as $row) {
            $amount   = (int) $row['amount'];
            $isRefund = $row['type'] === 'refund';

            if ($isRefund) {
                $refunded += $amount;
            } else {
                $charged += $amount;
                $chargeCount++;
            }

            $row['is_refund'] = $isRefund;
            $row['signed']    = $isRefund ? -$amount : $amount;
            $row['label']     = self::lineLabel($row, $isRefund ? 0 : $chargeCount - 1);
            $row['reference'] = $row['paypal_capture_id']
                ?: ($row['paypal_subscription_id'] ?: $row['paypal_order_id']);

            $lines[] = $row;
        }

        // A booking with only refund rows shouldn't happen, but it would produce
        // a nonsensical receipt, so treat "never charged" as nothing to issue.
        if ($chargeCount === 0) return null;

        return [
            'booking'  => $booking,
            'payments' => $lines,
            'charged'  => $charged,
            'refunded' => $refunded,
            'total'    => $charged - $refunded,
            'currency' => $lines[0]['currency'] ?: 'gbp',
            'number'   => 'EP-' . str_pad((string) $bookingId, 6, '0', STR_PAD_LEFT),
            'issued'   => date('d M Y'),
        ];
    }

    /** Handles negatives as "-£12.34" rather than "£-12.34". */
    public static function money(int $pence, string $currency = 'gbp'): string
    {
        $symbol = self::SYMBOLS[strtolower($currency)] ?? '';
        $sign   = $pence < 0 ? '-' : '';
        $amount = number_format(abs($pence) / 100, 2);

        return $symbol !== ''
            ? $sign . $symbol . $amount
            : $sign . $amount . ' ' . strtoupper($currency);
    }

    /**
     * $chargeIndex is the row's position among charges only, so a refund sitting
     * between two charges doesn't turn the later one into the "original".
     * Extensions reuse type 'initial' (ConfirmEditBooking.php), so position is
     * the only thing separating the original charge from a later one.
     */
    private static function lineLabel(array $payment, int $chargeIndex): string
    {
        return match ($payment['type']) {
            'refund'       => 'Refund',
            'adjustment'   => 'Adjustment',
            'subscription' => 'Monthly parking subscription',
            default        => $chargeIndex === 0 ? 'Parking booking' : 'Booking extension',
        };
    }

    /** Table rows for the emailed copy — the page builds its own markup. */
    public static function rowsHtml(array $receipt): string
    {
        $html = '';
        foreach ($receipt['payments'] as $payment) {
            $date   = date('d M Y', strtotime($payment['created_at']));
            $label  = htmlspecialchars($payment['label'], ENT_QUOTES);
            $amount = htmlspecialchars(self::money((int) $payment['signed'], $receipt['currency']), ENT_QUOTES);
            $colour = $payment['is_refund'] ? '#b45309' : '#333';
            $ref    = $payment['reference']
                ? "<br><span style='color:#999;font-size:12px'>Ref " . htmlspecialchars($payment['reference'], ENT_QUOTES) . "</span>"
                : '';

            $html .= "<tr>
                <td style='padding:10px 0;border-bottom:1px solid #eee;color:{$colour}'>{$label}<br><span style='color:#999;font-size:12px'>{$date}</span>{$ref}</td>
                <td style='padding:10px 0;border-bottom:1px solid #eee;text-align:right;white-space:nowrap;color:{$colour}'>{$amount}</td>
            </tr>";
        }

        return $html;
    }
}
