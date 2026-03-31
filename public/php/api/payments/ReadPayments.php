<?php

class ReadPayments extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    /**
     * Returns per-payment earnings for a carpark owner.
     * Paid status is derived from payout_id on the payment row itself.
     */
    public function getEarningsByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                p.booking_id,
                p.owner_amount,
                p.created_at,
                p.payout_id,
                op.paid_at,
                op.payout_month
            FROM payments p
            INNER JOIN bookings b   ON b.booking_id  = p.booking_id
            INNER JOIN carparks cp  ON cp.carpark_id = b.booking_carpark_id
            LEFT  JOIN owner_payouts op ON op.id = p.payout_id
            WHERE cp.carpark_owner = :owner_id
              AND p.status         = 'succeeded'
              AND p.owner_amount   IS NOT NULL
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([':owner_id' => $ownerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns one row per owner with all unpaid earnings — for the admin payout table.
     * Unpaid = payout_id IS NULL. No month grouping, so mid-month payouts work correctly.
     */
    public function getMonthlyOwingSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.user_name,
                u.user_email,
                SUM(p.owner_amount) AS total_owed,
                COUNT(p.id)         AS payment_count
            FROM payments p
            INNER JOIN bookings b  ON b.booking_id  = p.booking_id
            INNER JOIN carparks cp ON cp.carpark_id = b.booking_carpark_id
            INNER JOIN users u     ON u.user_id     = cp.carpark_owner
            WHERE p.status       = 'succeeded'
              AND p.owner_amount IS NOT NULL
              AND p.payout_id    IS NULL
            GROUP BY u.user_id, u.user_name, u.user_email
            ORDER BY u.user_name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
