<?php

class WritePayments extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    /**
     * Insert a new payment record
     */
    public function insertPayment(array $data): int
    {
        $sql = "
            INSERT INTO payments (
                booking_id,
                user_id,
                stripe_payment_intent_id,
                stripe_customer_id,
                amount,
                currency,
                type,
                status
            ) VALUES (
                :booking_id,
                :user_id,
                :stripe_payment_intent_id,
                :stripe_customer_id,
                :amount,
                :currency,
                :type,
                :status
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':booking_id' => $data['booking_id'],
            ':user_id' => $data['user_id'],
            ':stripe_payment_intent_id' => $data['stripe_payment_intent_id'],
            ':stripe_customer_id' => $data['stripe_customer_id'] ?? null,
            ':amount' => $data['amount'],
            ':currency' => $data['currency'] ?? 'gbp',
            ':type' => $data['type'],
            ':status' => $data['status']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Prevent double-inserts (idempotency)
     */
    public function paymentExists(string $paymentIntentId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM payments
            WHERE stripe_payment_intent_id = :pi
            LIMIT 1
        ");

        $stmt->execute([':pi' => $paymentIntentId]);

        return (bool) $stmt->fetchColumn();
    }
}
