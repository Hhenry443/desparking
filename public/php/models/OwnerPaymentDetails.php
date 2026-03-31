<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class OwnerPaymentDetails extends Dbh
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function savePaymentDetails(int $userId, string $type, array $fields): array
    {
        try {
            $existing = $this->db->prepare("
                SELECT id FROM owner_payment_details WHERE user_id = :user_id LIMIT 1
            ");
            $existing->execute([':user_id' => $userId]);

            if ($existing->fetchColumn()) {
                $stmt = $this->db->prepare("
                    UPDATE owner_payment_details
                    SET payment_type    = :type,
                        account_name   = :account_name,
                        sort_code      = :sort_code,
                        account_number = :account_number,
                        paypal_email   = :paypal_email
                    WHERE user_id = :user_id
                ");
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO owner_payment_details
                        (user_id, payment_type, account_name, sort_code, account_number, paypal_email)
                    VALUES
                        (:user_id, :type, :account_name, :sort_code, :account_number, :paypal_email)
                ");
            }

            $stmt->execute([
                ':user_id'        => $userId,
                ':type'           => $type,
                ':account_name'   => $fields['account_name']   ?? null,
                ':sort_code'      => $fields['sort_code']       ?? null,
                ':account_number' => $fields['account_number']  ?? null,
                ':paypal_email'   => $fields['paypal_email']    ?? null,
            ]);

            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removePaymentDetails(int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM owner_payment_details WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
    }
}
