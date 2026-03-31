<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/OwnerPaymentDetails.php';

class ReadOwnerPaymentDetails extends OwnerPaymentDetails
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function getByUserId(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM owner_payment_details WHERE user_id = :user_id LIMIT 1
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** For admin — returns payment details keyed by user_id */
    public function getAllIndexedByUserId(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM owner_payment_details");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $indexed = [];
        foreach ($rows as $row) {
            $indexed[(int) $row['user_id']] = $row;
        }
        return $indexed;
    }
}
