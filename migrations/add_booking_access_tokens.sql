-- Guest booking access tokens
--
-- Guest bookings are stored with booking_user_id = NULL, and every page that
-- displays a booking gates on $_SESSION['user_id'] and matches on
-- booking_user_id. That left guests with no way to reach their own booking
-- after checkout, and no way to pick it up if they registered later.
--
-- Each booking now carries a long-lived random token. It is emailed with the
-- confirmation as a ?t= link, grants read access to booking.php without a
-- session, and — because possession of it proves control of the address the
-- confirmation was sent to — lets a newly registered user claim the booking
-- onto their account.
--
-- The token lives on the booking row, so it stays valid for as long as the
-- booking exists. There is deliberately no expiry: customers often do not come
-- back to sign up until well after their stay.

ALTER TABLE bookings
    ADD COLUMN booking_access_token CHAR(64) DEFAULT NULL AFTER booking_registration,
    ADD UNIQUE INDEX idx_booking_access_token (booking_access_token);

-- Backfill existing rows so older bookings (guest ones especially) can also be
-- reached and claimed. SHA2 over UUID() + RAND() is evaluated per row.
UPDATE bookings
SET booking_access_token = SHA2(CONCAT(booking_id, '-', UUID(), '-', RAND()), 256)
WHERE booking_access_token IS NULL;
