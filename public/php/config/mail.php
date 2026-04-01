<?php

/**
 * Mail configuration
 *
 * Values are read from environment variables so nothing is hardcoded.
 *
 * Local dev (DDEV): set values in .ddev/config.yaml → web_environment
 *   MAIL_HOST=localhost, MAIL_PORT=1025 (Mailpit — no auth required)
 *   Mailpit UI: https://desparking.ddev.site:8026
 *
 * Production: set as server environment variables (Apache SetEnv or hosting panel)
 *   MAIL_HOST=smtp.yourprovider.com
 *   MAIL_PORT=587
 *   MAIL_USERNAME=your-smtp-user
 *   MAIL_PASSWORD=your-smtp-password
 *   MAIL_ENCRYPTION=tls
 *   MAIL_FROM_ADDRESS=noreply@desparking.co.uk
 *   MAIL_FROM_NAME=Desparking
 *   ADMIN_EMAIL=admin@desparking.co.uk  ← MUST be set in production
 */

define('MAIL_HOST',         getenv('MAIL_HOST')         ?: 'localhost');
define('MAIL_PORT',         (int)(getenv('MAIL_PORT')   ?: 1025));
define('MAIL_USERNAME',     getenv('MAIL_USERNAME')     ?: '');
define('MAIL_PASSWORD',     getenv('MAIL_PASSWORD')     ?: '');
define('MAIL_ENCRYPTION',   getenv('MAIL_ENCRYPTION')   ?: '');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@desparking.ddev.site');
define('MAIL_FROM_NAME',    getenv('MAIL_FROM_NAME')    ?: 'Desparking');
define('ADMIN_EMAIL',       getenv('ADMIN_EMAIL')       ?: 'admin@desparking.ddev.site');
