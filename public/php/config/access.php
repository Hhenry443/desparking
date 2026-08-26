<?php

/**
 * Per-page admin access.
 *
 * Full admins (users.user_is_admin = 1) reach every admin page as before.
 * The list below additionally grants specific non-admin users access to the
 * content pages only — used for the contracted writer, who needs the News CMS
 * and SEO Manager but none of the rest of the admin panel.
 *
 * To grant access: add the person's users.user_id to CONTENT_EDITOR_USER_IDS.
 * To revoke it:    remove the id. No other change is needed.
 *
 * The per-page helpers below are deliberately separate: if someone ever needs
 * one page but not the other, change that helper rather than this list.
 */

// users.user_id values allowed into the content admin pages
// (/news-admin.php, /news-edit.php, /seo-admin.php) without being a full admin.
define('CONTENT_EDITOR_USER_IDS', [
    88, // blog/news writer
]);

/** True if the logged-in user is a full admin. */
function isFullAdmin(): bool
{
    return isset($_SESSION['user_id']) && ($_SESSION['is_admin'] ?? false) === true;
}

/** True if the logged-in user is on the content editor list above. */
function isContentEditor(): bool
{
    return isset($_SESSION['user_id'])
        && in_array((int) $_SESSION['user_id'], CONTENT_EDITOR_USER_IDS, true);
}

/** True if the logged-in user may use the News CMS (full admin, or content editor). */
function canUseNewsCms(): bool
{
    return isFullAdmin() || isContentEditor();
}

/** True if the logged-in user may use the SEO Manager (full admin, or content editor). */
function canUseSeoAdmin(): bool
{
    return isFullAdmin() || isContentEditor();
}
