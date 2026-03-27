<?php
/**
 * Stripe configuration
 *
 * Keys are read from environment variables so nothing is hardcoded.
 *
 * Local dev (DDEV): set values in .ddev/config.yaml → web_environment
 * Production:       set as server environment variables (Apache SetEnv,
 *                   Nginx fastcgi_param, or your hosting panel's env section)
 */

define('STRIPE_SECRET_KEY',    getenv('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLIC_KEY',    getenv('STRIPE_PUBLIC_KEY'));
define('STRIPE_WEBHOOK_SECRET', getenv('STRIPE_WEBHOOK_SECRET'));
