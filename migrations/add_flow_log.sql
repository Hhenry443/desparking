-- Diagnostic trace table for the Stripe booking flow.
--
-- The booking flow catches Throwable in five places and does nothing but
-- error_log(), so a confirmation email can fail after money has changed hands
-- with no visible trace. This table records each decision point durably.
--
-- Keep it after the bug is found: it doubles as the audit trail for
-- "did this customer get their confirmation?".

CREATE TABLE IF NOT EXISTS flow_log (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source     VARCHAR(32)  NOT NULL COMMENT 'webhook | return | notifier',
    event      VARCHAR(64)  NOT NULL COMMENT 'the decision point reached',
    booking_id INT UNSIGNED DEFAULT NULL,
    stripe_ref VARCHAR(255) DEFAULT NULL COMMENT 'payment intent or subscription id',
    detail     TEXT         DEFAULT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_booking (booking_id),
    INDEX idx_stripe  (stripe_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
