<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/api/bookings/WriteBookings.php';

/**
 * Attaching guest bookings to an account.
 *
 * Guest bookings carry a long-lived access token, emailed with the
 * confirmation. Holding it proves control of the address the booking was made
 * with, so it is what we accept as authorisation to move those bookings onto a
 * user account — reached either by following the emailed link while logged in,
 * or by passing ?claim=<token> through registration or login.
 */
class GuestClaim
{
    /**
     * Claim whatever the token unlocks for this user.
     *
     * Returns a message to show the customer, or null when there was nothing to
     * claim — an empty or unrecognised token, or a booking that already belongs
     * to someone. Callers treat null as "carry on quietly": a stale link in an
     * old email should not produce an error on an otherwise successful signup.
     */
    public static function run(?string $token, int $userID): ?string
    {
        $token = trim((string) $token);

        if ($token === '') {
            return null;
        }

        $claimed = (new WriteBookings())->claimGuestBookings($token, $userID);

        if ($claimed < 1) {
            return null;
        }

        return $claimed > 1
            ? "{$claimed} bookings you made as a guest have been added to your account."
            : "The booking you made as a guest has been added to your account.";
    }
}
