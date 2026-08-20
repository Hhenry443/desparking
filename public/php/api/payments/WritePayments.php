<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/notifications/Notifier.php';

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
                paypal_order_id,
                paypal_capture_id,
                paypal_subscription_id,
                paypal_payer_id,
                amount,
                owner_amount,
                currency,
                type,
                status
            ) VALUES (
                :booking_id,
                :user_id,
                :paypal_order_id,
                :paypal_capture_id,
                :paypal_subscription_id,
                :paypal_payer_id,
                :amount,
                :owner_amount,
                :currency,
                :type,
                :status
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':booking_id'             => $data['booking_id'],
            ':user_id'                => $data['user_id'],
            ':paypal_order_id'        => $data['paypal_order_id'] ?? null,
            ':paypal_capture_id'      => $data['paypal_capture_id'] ?? null,
            ':paypal_subscription_id' => $data['paypal_subscription_id'] ?? null,
            ':paypal_payer_id'        => $data['paypal_payer_id'] ?? null,
            ':amount'                 => $data['amount'],
            ':owner_amount'           => $data['owner_amount'] ?? null,
            ':currency'               => $data['currency'] ?? 'gbp',
            ':type'                   => $data['type'],
            ':status'                 => $data['status'],
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

        $ownerId = (int) ($_POST['owner_id'] ?? 0);
        $notes   = trim($_POST['notes'] ?? '') ?: null;

        if (!$ownerId) {
            header('Location: /admin.php?error=' . urlencode('Invalid payout parameters'));
            exit();
        }

        try {
            $this->db->beginTransaction();

            // Find all currently unpaid payment IDs for this owner
            $stmt = $this->db->prepare("
                SELECT p.id, p.owner_amount
                FROM payments p
                INNER JOIN bookings b  ON b.booking_id  = p.booking_id
                INNER JOIN carparks cp ON cp.carpark_id = b.booking_carpark_id
                WHERE cp.carpark_owner = :owner_id
                  AND p.status         = 'succeeded'
                  AND p.owner_amount   IS NOT NULL
                  AND p.payout_id      IS NULL
            ");
            $stmt->execute([':owner_id' => $ownerId]);
            $unpaid = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($unpaid)) {
                $this->db->rollBack();
                header('Location: /admin.php?error=' . urlencode('No unpaid payments found for this owner'));
                exit();
            }

            $total      = array_sum(array_column($unpaid, 'owner_amount'));
            $payoutMonth = date('Y-m');

            $payoutId = $this->insertPayout($ownerId, $payoutMonth, $total, $notes);

            // Stamp each payment row with the payout ID
            $ids        = implode(',', array_map('intval', array_column($unpaid, 'id')));
            $this->db->exec("UPDATE payments SET payout_id = {$payoutId} WHERE id IN ({$ids})");

            $this->db->commit();

            try {
                (new Notifier($this->db))->payoutRecorded($ownerId, $total);
            } catch (Throwable $e) {
                error_log("Notification failed [payoutRecorded]: " . $e->getMessage());
            }

            header('Location: /admin.php?success=payout_recorded');
        } catch (PDOException $e) {
            $this->db->rollBack();
            header('Location: /admin.php?error=' . urlencode('Error recording payout: ' . $e->getMessage()));
        }
        exit();
    }

    /** Idempotency check for one-time payments */
    public function paymentExists(string $orderId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM payments
            WHERE paypal_order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Idempotency check for subscriptions */
    public function subscriptionPaymentExists(string $subscriptionId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM payments
            WHERE paypal_subscription_id = :sub_id
            LIMIT 1
        ");
        $stmt->execute([':sub_id' => $subscriptionId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Look up booking_id from a PayPal order ID (used in return.php) */
    public function getBookingIdByOrderId(string $orderId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT booking_id FROM payments
            WHERE paypal_order_id = :order_id
            LIMIT 1
        ");
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['booking_id'] : null;
    }

    /** Look up booking_id from a subscription ID (used in return.php) */
    public function getBookingIdBySubscriptionId(string $subscriptionId): ?int
    {
        $stmt = $this->db->prepare("
            SELECT booking_id FROM payments
            WHERE paypal_subscription_id = :sub_id
            LIMIT 1
        ");
        $stmt->execute([':sub_id' => $subscriptionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int) $row['booking_id'] : null;
    }

    /** Durable checkout metadata, keyed by uuid — replaces Stripe's inline `metadata` blob. */
    public function insertPendingCheckout(array $data): string
    {
        $id = PayPalClient::uuidv4();

        $stmt = $this->db->prepare("
            INSERT INTO pending_checkouts (
                id, order_id, type, carpark_id, booking_id, user_id, vehicle_id,
                registration, name, email, start, end, amount, owner_amount
            ) VALUES (
                :id, :order_id, :type, :carpark_id, :booking_id, :user_id, :vehicle_id,
                :registration, :name, :email, :start, :end, :amount, :owner_amount
            )
        ");
        $stmt->execute([
            ':id'           => $id,
            ':order_id'     => $data['order_id'] ?? null,
            ':type'         => $data['type'],
            ':carpark_id'   => $data['carpark_id'],
            ':booking_id'   => $data['booking_id'] ?? null,
            ':user_id'      => $data['user_id'] ?? null,
            ':vehicle_id'   => $data['vehicle_id'] ?? null,
            ':registration' => $data['registration'] ?? null,
            ':name'         => $data['name'],
            ':email'        => $data['email'] ?? null,
            ':start'        => $data['start'],
            ':end'          => $data['end'],
            ':amount'       => $data['amount'] ?? null,
            ':owner_amount' => $data['owner_amount'] ?? null,
        ]);

        return $id;
    }

    public function linkSubscriptionToPendingCheckout(string $checkoutRef, string $subscriptionId): void
    {
        $stmt = $this->db->prepare("
            UPDATE pending_checkouts SET subscription_id = :sub_id WHERE id = :id
        ");
        $stmt->execute([':sub_id' => $subscriptionId, ':id' => $checkoutRef]);
    }

    public function getPendingCheckoutByOrderId(string $orderId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pending_checkouts WHERE order_id = :order_id LIMIT 1");
        $stmt->execute([':order_id' => $orderId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getPendingCheckoutBySubscriptionId(string $subscriptionId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pending_checkouts WHERE subscription_id = :sub_id LIMIT 1");
        $stmt->execute([':sub_id' => $subscriptionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getPendingCheckoutById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM pending_checkouts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
