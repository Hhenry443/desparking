<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class Seo extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function getAllPages(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_pages ORDER BY page_slug ASC");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function getPageBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM seo_pages WHERE page_slug = :slug");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function upsertPage(string $slug, string $title, string $description, string $ogImage = ''): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO seo_pages (page_slug, seo_title, seo_description, og_image)
            VALUES (:slug, :title, :description, :og_image)
            ON DUPLICATE KEY UPDATE
                seo_title       = VALUES(seo_title),
                seo_description = VALUES(seo_description),
                og_image        = VALUES(og_image),
                updated_at      = NOW()
        ");
        $stmt->execute([
            ':slug'        => $slug,
            ':title'       => $title,
            ':description' => $description,
            ':og_image'    => $ogImage,
        ]);
    }

    public function deletePage(int $id): void
    {
        $stmt = $this->db->prepare("DELETE FROM seo_pages WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
