<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

class News extends Dbh
{
    private PDO $db;

    function __construct()
    {
        $this->db = Dbh::getConnection();
    }

    public function getAllStories(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM news_stories ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function getPublishedStories(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM news_stories WHERE status = 'published' ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    public function getStoryBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM news_stories WHERE slug = :slug AND status = 'published'
        ");
        $stmt->execute([':slug' => $slug]);
        return $stmt->fetch() ?: null;
    }

    public function getStoryById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM news_stories WHERE story_id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function getSections(int $storyId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM news_story_sections WHERE story_id = :id ORDER BY sort_order ASC
        ");
        $stmt->execute([':id' => $storyId]);
        return $stmt->fetchAll() ?: [];
    }

    public function insertStory(string $title, string $slug, string $summary, ?string $coverImage, string $status): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO news_stories (title, slug, summary, cover_image, status)
            VALUES (:title, :slug, :summary, :cover_image, :status)
        ");
        $stmt->execute([
            ':title'       => $title,
            ':slug'        => $slug,
            ':summary'     => $summary,
            ':cover_image' => $coverImage,
            ':status'      => $status,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updateStory(int $id, string $title, string $slug, string $summary, ?string $coverImage, string $status): void
    {
        $stmt = $this->db->prepare("
            UPDATE news_stories SET title = :title, slug = :slug, summary = :summary,
                cover_image = :cover_image, status = :status WHERE story_id = :id
        ");
        $stmt->execute([
            ':title'       => $title,
            ':slug'        => $slug,
            ':summary'     => $summary,
            ':cover_image' => $coverImage,
            ':status'      => $status,
            ':id'          => $id,
        ]);
    }

    public function replaceSections(int $storyId, array $sections): void
    {
        $this->db->prepare("DELETE FROM news_story_sections WHERE story_id = :id")
            ->execute([':id' => $storyId]);

        $stmt = $this->db->prepare("
            INSERT INTO news_story_sections (story_id, sort_order, heading, body, image_path)
            VALUES (:story_id, :sort_order, :heading, :body, :image_path)
        ");
        foreach ($sections as $i => $s) {
            $stmt->execute([
                ':story_id'   => $storyId,
                ':sort_order' => $i,
                ':heading'    => $s['heading'] ?? null,
                ':body'       => $s['body'] ?? null,
                ':image_path' => $s['image_path'] ?? null,
            ]);
        }
    }

    public function deleteStory(int $id): void
    {
        $this->db->prepare("DELETE FROM news_stories WHERE story_id = :id")
            ->execute([':id' => $id]);
    }

    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT 1 FROM news_stories WHERE slug = :slug";
        $params = [':slug' => $slug];
        if ($excludeId !== null) {
            $sql .= " AND story_id != :id";
            $params[':id'] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
