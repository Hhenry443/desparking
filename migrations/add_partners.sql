-- Partners table
-- Run this once against your database to enable the Partners CMS admin page.

CREATE TABLE IF NOT EXISTS partners (
    partner_id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    partner_name VARCHAR(255)  NOT NULL,
    logo_path    VARCHAR(500)  NOT NULL,
    website_url  VARCHAR(500)  NOT NULL DEFAULT '',
    sort_order   INT           NOT NULL DEFAULT 0,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the partners that were previously hardcoded on partners.php, so the
-- logo marquee isn't empty after this migration runs. Manage/replace these
-- via the Partners CMS (/partners-admin.php) going forward.
INSERT INTO partners (partner_name, logo_path, website_url, sort_order) VALUES
    ('Parkso',   '/images/parkso.png',          'https://parkso.uk',           10),
    ('Keynest',  '/images/keynest.jpeg',        'https://keynest.com/',        20),
    ('QPark',    '/images/qparklogo.png',       'https://tidd.ly/4umiTOI',     30),
    ('Airparks', '/images/airparks logo.png',   'https://www.airparks.co.uk/', 40);
