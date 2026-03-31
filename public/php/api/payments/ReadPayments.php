<?php

class ReadPayments extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    /**
     * Returns per-payment earnings for a carpark owner,
     * with paid/unpaid status derived from owner_payouts.
     */
    public function getEarningsByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.booking_id,
                p.owner_amount,
                p.created_at,
                DATE_FORMAT(p.created_at, '%Y-%m') AS payout_month,
                op.id      AS payout_id,
                op.paid_at
            FROM payments p
            INNER JOIN bookings b   ON b.booking_id      = p.booking_id
            INNER JOIN carparks cp  ON cp.carpark_id     = b.booking_carpark_id
            LEFT  JOIN owner_payouts op
                    ON op.owner_id    = cp.carpark_owner
                   AND op.payout_month = DATE_FORMAT(p.created_at, '%Y-%m')
            WHERE cp.carpark_owner = :owner_id
              AND p.status         = 'succeeded'
              AND p.owner_amount   IS NOT NULL
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([':owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns one row per owner per unpaid month — for the admin payout table.
     */
    public function getMonthlyOwingSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.user_name,
                u.user_email,
                DATE_FORMAT(p.created_at, '%Y-%m') AS payout_month,
                SUM(p.owner_amount)                AS total_owed,
                COUNT(p.id)                        AS payment_count
            FROM payments p
            INNER JOIN bookings b  ON b.booking_id     = p.booking_id
            INNER JOIN carparks cp ON cp.carpark_id    = b.booking_carpark_id
            INNER JOIN users u     ON u.user_id        = cp.carpark_owner
            LEFT  JOIN owner_payouts op
                    ON op.owner_id    = u.user_id
                   AND op.payout_month = DATE_FORMAT(p.created_at, '%Y-%m')
            WHERE p.status       = 'succeeded'
              AND p.owner_amount IS NOT NULL
              AND op.id          IS NULL
            GROUP BY u.user_id, u.user_name, u.user_email, payout_month
            ORDER BY payout_month ASC, u.user_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
