<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Partners.php';

class WritePartners extends Partners
{
    public function savePartner(): int
    {
        $id         = isset($_POST['partner_id']) && ctype_digit($_POST['partner_id']) ? (int)$_POST['partner_id'] : null;
        $name       = trim($_POST['partner_name'] ?? '');
        $websiteUrl = trim($_POST['website_url'] ?? '');
        $sortOrder  = isset($_POST['sort_order']) && ctype_digit($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

        if (!$name) {
            http_response_code(400);
            exit('Partner name is required');
        }

        // Handle logo upload — keep the existing logo unless a new one is provided
        $logoPath = $_POST['existing_logo'] ?? null;
        if (!empty($_FILES['logo']['tmp_name'])) {
            $logoPath = $this->uploadLogo($_FILES['logo']);
        }

        if (!$logoPath) {
            http_response_code(400);
            exit('A logo image is required');
        }

        if ($id) {
            $this->updatePartner($id, $name, $logoPath, $websiteUrl, $sortOrder);
            return $id;
        } else {
            return $this->insertPartner($name, $logoPath, $websiteUrl, $sortOrder);
        }
    }

    private function uploadLogo(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed)) return null;

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/partners/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = 'partner_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest     = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        return '/uploads/partners/' . $filename;
    }
}
