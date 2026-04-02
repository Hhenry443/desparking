CREATE TABLE IF NOT EXISTS news_stories (
    story_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title      VARCHAR(255) NOT NULL,
    slug       VARCHAR(255) NOT NULL UNIQUE,
    summary    TEXT NULL,
    cover_image VARCHAR(500) NULL,
    status     ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS news_story_sections (
    section_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    story_id   INT UNSIGNED NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    heading    VARCHAR(255) NULL,
    body       TEXT NULL,
    image_path VARCHAR(500) NULL,
    CONSTRAINT fk_section_story FOREIGN KEY (story_id) REFERENCES news_stories(story_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
