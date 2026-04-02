<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/News.php';

class WriteNews extends News
{
    public function saveStory(): int
    {
        $id      = isset($_POST['story_id']) && ctype_digit($_POST['story_id']) ? (int)$_POST['story_id'] : null;
        $title   = trim($_POST['title'] ?? '');
        $summary = trim($_POST['summary'] ?? '');
        $status  = in_array($_POST['status'] ?? '', ['draft', 'published']) ? $_POST['status'] : 'draft';

        if (!$title) {
            http_response_code(400);
            exit('Title is required');
        }

        $slug = $this->makeSlug($title, $id);

        // Handle cover image upload
        $coverImage = $_POST['existing_cover'] ?? null;
        if (!empty($_FILES['cover_image']['tmp_name'])) {
            $coverImage = $this->uploadImage($_FILES['cover_image'], 'cover');
        }

        if ($id) {
            $this->updateStory($id, $title, $slug, $summary, $coverImage, $status);
        } else {
            $id = $this->insertStory($title, $slug, $summary, $coverImage, $status);
        }

        // Build sections array from POST
        $headings   = $_POST['section_heading'] ?? [];
        $bodies     = $_POST['section_body']    ?? [];
        $existingImgs = $_POST['section_existing_image'] ?? [];
        $sectionFiles = $_FILES['section_image'] ?? [];

        $sections = [];
        $count = max(count($headings), count($bodies));
        for ($i = 0; $i < $count; $i++) {
            $heading = trim($headings[$i] ?? '');
            $body    = trim($bodies[$i] ?? '');
            $imagePath = $existingImgs[$i] ?? null;

            // Check if a new image is being uploaded for this section
            $hasNewImage = !empty($sectionFiles['tmp_name'][$i]);
            if ($heading === '' && $body === '' && !$imagePath && !$hasNewImage) continue;

            // Handle per-section image upload
            if ($hasNewImage) {
                $file = [
                    'name'     => $sectionFiles['name'][$i],
                    'type'     => $sectionFiles['type'][$i],
                    'tmp_name' => $sectionFiles['tmp_name'][$i],
                    'error'    => $sectionFiles['error'][$i],
                    'size'     => $sectionFiles['size'][$i],
                ];
                $imagePath = $this->uploadImage($file, 'section');
            }

            $sections[] = [
                'heading'    => $heading ?: null,
                'body'       => $body ?: null,
                'image_path' => $imagePath,
            ];
        }

        $this->replaceSections($id, $sections);
        return $id;
    }

    private function makeSlug(string $title, ?int $excludeId): string
    {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $title), '-'));
        $slug = $base;
        $n    = 2;
        while ($this->slugExists($slug, $excludeId)) {
            $slug = $base . '-' . $n++;
        }
        return $slug;
    }

    private function uploadImage(array $file, string $prefix): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed)) return null;

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest     = $_SERVER['DOCUMENT_ROOT'] . '/uploads/news/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        return '/uploads/news/' . $filename;
    }
}
