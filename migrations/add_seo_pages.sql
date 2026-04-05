-- SEO page overrides table
-- Run this once against your database to enable the SEO Manager admin page.

CREATE TABLE IF NOT EXISTS seo_pages (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    page_slug       VARCHAR(255)  NOT NULL UNIQUE COMMENT 'URL path e.g. /about.php or /news-story.php?slug=foo',
    seo_title       VARCHAR(120)  NOT NULL,
    seo_description VARCHAR(320)  NOT NULL,
    og_image        VARCHAR(500)  NOT NULL DEFAULT '',
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
