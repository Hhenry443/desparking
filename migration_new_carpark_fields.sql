-- Migration: new carpark listing fields
-- Run once against the production database.

ALTER TABLE carparks
    ADD COLUMN space_type        ENUM('car','garage','motorbike','multiple') NOT NULL DEFAULT 'car'  AFTER space_size,
    ADD COLUMN is_allocated      TINYINT(1) NOT NULL DEFAULT 0                                        AFTER space_type,
    ADD COLUMN available_from    DATE NULL DEFAULT NULL                                               AFTER is_allocated,
    ADD COLUMN time_restrictions VARCHAR(500) NULL DEFAULT NULL                                       AFTER available_from;

CREATE TABLE IF NOT EXISTS carpark_unavailable_dates (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carpark_id    INT          NOT NULL,
    unavailable_date DATE      NOT NULL,
    UNIQUE KEY uk_carpark_date (carpark_id, unavailable_date),
    CONSTRAINT fk_unavail_carpark FOREIGN KEY (carpark_id)
        REFERENCES carparks(carpark_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
