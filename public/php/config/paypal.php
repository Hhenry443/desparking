<?php

/**
 * PayPal configuration
 *
 * Keys are read from environment variables so nothing is hardcoded.
 *
 * Local dev (DDEV): set values in .ddev/config.yaml → web_environment
 * Production:       set as server environment variables (Apache SetEnv,
 *                   Nginx fastcgi_param, or your hosting panel's env section)
 */

$isProd = getenv('ENVIRONMENT') === 'production';

define('PAYPAL_CLIENT_ID', getenv($isProd ? 'PAYPAL_CLIENT_ID' : 'PAYPAL_CLIENT_ID_TEST'));
define('PAYPAL_SECRET', getenv($isProd ? 'PAYPAL_SECRET' : 'PAYPAL_SECRET_TEST'));
define('PAYPAL_WEBHOOK_ID', getenv($isProd ? 'PAYPAL_WEBHOOK_ID' : 'PAYPAL_WEBHOOK_ID_TEST'));
define('PAYPAL_PRODUCT_ID', getenv($isProd ? 'PAYPAL_PRODUCT_ID' : 'PAYPAL_PRODUCT_ID_TEST'));
define('PAYPAL_API_BASE', $isProd ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com');
