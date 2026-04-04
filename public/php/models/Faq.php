<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Faq extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function getAllFaqs(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM faqs ORDER BY category, sort_order ASC, faq_id ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function getFaqById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM faqs WHERE faq_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function insertFaq(string $question, string $answer, string $category, int $sortOrder): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO faqs (question, answer, category, sort_order)
            VALUES (:question, :answer, :category, :sort_order)
        ");
        $stmt->execute([
            ':question'   => $question,
            ':answer'     => $answer,
            ':category'   => $category,
            ':sort_order' => $sortOrder,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateFaq(int $id, string $question, string $answer, string $category, int $sortOrder): void
    {
        $stmt = $this->db->prepare("
            UPDATE faqs SET question = :question, answer = :answer,
                category = :category, sort_order = :sort_order
            WHERE faq_id = :id
        ");
        $stmt->execute([
            ':question'   => $question,
            ':answer'     => $answer,
            ':category'   => $category,
            ':sort_order' => $sortOrder,
            ':id'         => $id,
        ]);
    }

    public function deleteFaq(int $id): void
    {
        $this->db->prepare("DELETE FROM faqs WHERE faq_id = :id")
            ->execute([':id' => $id]);
    }
}
