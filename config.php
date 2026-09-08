<?php
/* =============================================================
   Centralized Configuration
   -------------------------------------------------------------
   Supports both local environment overrides (config.local.php)
   and Cloud Hosting / Vercel Environment Variables (getenv).
   ============================================================= */

// Load local overrides if present (offline local development)
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// ---- Database credentials (Default: TiDB Cloud Cluster) ----
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: '2NNd45iVF9gUDLQ.root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'O2q86KIcftR5XbcU');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'id13212736_event');
if (!defined('DB_PORT')) {
    $defaultPort = (DB_HOST === 'localhost' || DB_HOST === '127.0.0.1') ? 3306 : 4000;
    define('DB_PORT', getenv('DB_PORT') ? (int)getenv('DB_PORT') : $defaultPort);
}

// ---- Razorpay API Credentials (UPI-Only Payments) ----
if (!defined('RAZORPAY_KEY_ID')) define('RAZORPAY_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: 'rzp_test_YOUR_KEY_ID_HERE');
if (!defined('RAZORPAY_KEY_SECRET')) define('RAZORPAY_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: 'YOUR_KEY_SECRET_HERE');
if (!defined('RAZORPAY_WEBHOOK_SECRET')) define('RAZORPAY_WEBHOOK_SECRET', getenv('RAZORPAY_WEBHOOK_SECRET') ?: 'YOUR_WEBHOOK_SECRET_HERE');

// ---- Google OAuth Client ID ----
if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: 'YOUR_GOOGLE_CLIENT_ID_PLACEHOLDER.apps.googleusercontent.com');

// ---- Optional: enable debugging for transparent diagnosis ----
if (!defined('APP_DEBUG')) define('APP_DEBUG', getenv('APP_DEBUG') !== false ? (bool)getenv('APP_DEBUG') : true);
