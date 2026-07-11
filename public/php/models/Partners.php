<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Partners extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function getAllPartners(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM partners ORDER BY sort_order ASC, partner_id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function getPartnerById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM partners WHERE partner_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function insertPartner(string $name, string $logoPath, string $websiteUrl, int $sortOrder): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO partners (partner_name, logo_path, website_url, sort_order)
            VALUES (:name, :logo_path, :website_url, :sort_order)
        ");
        $stmt->execute([
            ':name'        => $name,
            ':logo_path'   => $logoPath,
            ':website_url' => $websiteUrl,
            ':sort_order'  => $sortOrder,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePartner(int $id, string $name, string $logoPath, string $websiteUrl, int $sortOrder): void
    {
        $stmt = $this->db->prepare("
            UPDATE partners SET partner_name = :name, logo_path = :logo_path,
                website_url = :website_url, sort_order = :sort_order
            WHERE partner_id = :id
        ");
        $stmt->execute([
            ':name'        => $name,
            ':logo_path'   => $logoPath,
            ':website_url' => $websiteUrl,
            ':sort_order'  => $sortOrder,
            ':id'          => $id,
        ]);
    }

    public function deletePartner(int $id): void
    {
        $this->db->prepare("DELETE FROM partners WHERE partner_id = :id")
            ->execute([':id' => $id]);
    }
}
