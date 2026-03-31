<?php

class WritePayments extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function insertPayment(array $data): int
    {
        $sql = "
            INSERT INTO payments (
                booking_id,
                user_id,
                stripe_payment_intent_id,
                stripe_subscription_id,
                stripe_customer_id,
                amount,
                owner_amount,
                currency,
                type,
                status
            ) VALUES (
                :booking_id,
                :user_id,
                :stripe_payment_intent_id,
                :stripe_subscription_id,
                :stripe_customer_id,
                :amount,
                :owner_amount,
                :currency,
                :type,
                :status
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_id'               => $data['booking_id'],
            ':user_id'                  => $data['user_id'],
            ':stripe_payment_intent_id' => $data['stripe_payment_intent_id'] ?? null,
            ':stripe_subscription_id'   => $data['stripe_subscription_id'] ?? null,
            ':stripe_customer_id'       => $data['stripe_customer_id'] ?? null,
            ':amount'                   => $data['amount'],
            ':owner_amount'             => $data['owner_amount'] ?? null,
            ':currency'                 => $data['currency'] ?? 'gbp',
            ':type'                     => $data['type'],
            ':status'                   => $data['status'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function insertPayout(int $ownerId, string $payoutMonth, int $amount, ?string $notes): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO owner_payouts (owner_id, payout_month, amount, notes)
            VALUES (:owner_id, :payout_month, :amount, :notes)
        ");
        $stmt->execute([
            ':owner_id'     => $ownerId,
            ':payout_month' => $payoutMonth,
            ':amount'       => $amount,
            ':notes'        => $notes,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function handleMarkPayoutPaid(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] !== true) {
            header('Location: /');
            exit();
        }

        $ownerId     = (int) ($_POST['owner_id'] ?? 0);
        $payoutMonth = trim($_POST['payout_month'] ?? '');
        $amount      = (int) ($_POST['amount'] ?? 0);
        $notes       = trim($_POST['notes'] ?? '') ?: null;

        if (!$ownerId || !preg_match('/^\d{4}-\d{2}$/', $payoutMonth) || $amount <= 0) {
            header('Location: /admin.php?error=' . urlencode('Invalid payout parameters'));
            exit();
        }

        try {
            $this->insertPayout($ownerId, $payoutMonth, $amount, $notes);
            header('Location: /admin.php?success=payout_recorded');
        } catch (PDOException $e) {
            header('Location: /admin.php?error=' . urlencode('Payout already recorded for this month'));
        }
        exit();
    }

    /** Idempotency check for one-time payments */
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

    /** Idempotency check for subscriptions */
    public function subscriptionPaymentExists(string $subscriptionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM payments
            WHERE stripe_subscription_id = :sub_id
            LIMIT 1
        ");
        $stmt->execute([':sub_id' => $subscriptionId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Look up booking_id from a payment intent (used in return.php) */
    public function getBookingIdByPaymentIntent(string $paymentIntentId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT booking_id FROM payments
            WHERE stripe_payment_intent_id = :pi
            LIMIT 1
        ");
        $stmt->execute([':pi' => $paymentIntentId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['booking_id'] : null;
    }

    /** Look up booking_id from a subscription ID (used in return.php) */
    public function getBookingIdBySubscriptionId(string $subscriptionId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT booking_id FROM payments
            WHERE stripe_subscription_id = :sub_id
            LIMIT 1
        ");
        $stmt->execute([':sub_id' => $subscriptionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['booking_id'] : null;
    }
}
