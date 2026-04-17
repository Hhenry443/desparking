-- Add per-carpark owner contact details columns
-- Previously contact details were stored in the owner_details table (per user),
-- causing all carparks by the same owner to share the same contact details.
-- These new columns store them per carpark instead.

ALTER TABLE carparks
    ADD COLUMN owner_name    VARCHAR(255) DEFAULT NULL AFTER access_instructions,
    ADD COLUMN owner_phone   VARCHAR(50)  DEFAULT NULL AFTER owner_name,
    ADD COLUMN owner_address VARCHAR(500) DEFAULT NULL AFTER owner_phone;
