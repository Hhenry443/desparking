<?php

/**
 * Per-page admin access.
 *
 * Full admins (users.user_is_admin = 1) reach every admin page as before.
 * The list below additionally grants one specific non-admin user access to a
 * single admin page and nothing else — used for the contracted writer, who
 * needs the News CMS but none of the rest of the admin panel.
 *
 * To grant access: add the person's users.user_id to NEWS_CMS_USER_IDS.
 * To revoke it:    remove the id. No other change is needed.
 */

// users.user_id values allowed into /news-admin.php and /news-edit.php
// without being a full admin.
define('NEWS_CMS_USER_IDS', [
    88
]);

/** True if the logged-in user is a full admin. */
function isFullAdmin(): bool
{
    return isset($_SESSION['user_id']) && ($_SESSION['is_admin'] ?? false) === true;
}

/** True if the logged-in user may use the News CMS (full admin, or whitelisted above). */
function canUseNewsCms(): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    return isFullAdmin() || in_array((int) $_SESSION['user_id'], NEWS_CMS_USER_IDS, true);
}
