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

$isProd = getenv('ENVIRONMENT') === 'production';

define('STRIPE_SECRET_KEY',     getenv($isProd ? 'STRIPE_SECRET_KEY'  : 'STRIPE_SECRET_TEST_KEY'));
define('STRIPE_PUBLIC_KEY',     getenv($isProd ? 'STRIPE_PUBLIC_KEY'  : 'STRIPE_PUBLIC_TEST_KEY'));
define('STRIPE_WEBHOOK_SECRET', getenv($isProd ? 'STRIPE_WEBHOOK_SECRET'  : 'STRIPE_WEBHOOK_TEST_SECRET'));
