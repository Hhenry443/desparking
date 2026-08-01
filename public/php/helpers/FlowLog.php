<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/config/db.php';

/**
 * Durable trace for the Stripe booking flow.
 *
 * Writes to the flow_log table so a failed confirmation is visible without
 * server log access. Never throws: a diagnostic must not be able to break the
 * flow it is observing, and it is called from inside paths where money has
 * already been taken.
 */
class FlowLog
{
    public static function write(
        string $source,
        string $event,
        ?int $bookingId = null,
        ?string $stripeRef = null,
        ?string $detail = null
    ): void {
        try {
            $stmt = Dbh::getConnection()->prepare("
                INSERT INTO flow_log (source, event, booking_id, stripe_ref, detail)
                VALUES (:source, :event, :booking_id, :stripe_ref, :detail)
            ");
            $stmt->execute([
                ':source'     => $source,
                ':event'      => $event,
                ':booking_id' => $bookingId,
                ':stripe_ref' => $stripeRef,
                ':detail'     => $detail !== null ? mb_substr($detail, 0, 2000) : null,
            ]);
        } catch (Throwable $e) {
            error_log("FlowLog write failed: " . $e->getMessage());
        }
    }
}
