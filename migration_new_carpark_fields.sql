-- Migration: new carpark fields + owner_details + carpark_photos
-- Run this against the desparking database

-- Add new columns to carparks table
ALTER TABLE carparks
    ADD COLUMN space_size ENUM('small','medium','large') NOT NULL DEFAULT 'medium' AFTER carpark_features,
    ADD COLUMN requires_key TINYINT(1) NOT NULL DEFAULT 0 AFTER space_size,
    ADD COLUMN weekend_available TINYINT(1) NOT NULL DEFAULT 1 AFTER requires_key,
    ADD COLUMN min_booking_minutes INT NOT NULL DEFAULT 30 AFTER weekend_available;

-- Owner contact details (one row per user, upserted on carpark creation)
CREATE TABLE IF NOT EXISTS owner_details (
    owner_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL UNIQUE,
    phone_number    VARCHAR(30) NOT NULL DEFAULT '',
    owner_address   TEXT NOT NULL DEFAULT '',
    CONSTRAINT fk_owner_details_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Photos for each carpark (multiple allowed)
CREATE TABLE IF NOT EXISTS carpark_photos (
    photo_id    INT AUTO_INCREMENT PRIMARY KEY,
    carpark_id  INT NOT NULL,
    photo_path  VARCHAR(255) NOT NULL,
    CONSTRAINT fk_carpark_photos_carpark FOREIGN KEY (carpark_id) REFERENCES carparks(carpark_id) ON DELETE CASCADE
);
