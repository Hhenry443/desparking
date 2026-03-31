ALTER TABLE `carparks`
    ADD COLUMN `carpark_status` ENUM('pending', 'approved') NOT NULL DEFAULT 'pending'
    AFTER `carpark_type`;

-- Approve all existing carparks so they remain live
UPDATE `carparks` SET `carpark_status` = 'approved';
