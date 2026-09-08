<?php
/* =============================================================
   EVENTHUB PRO — Configuration Example Template
   -------------------------------------------------------------
   Copy this file to config.php and replace the placeholder
   values with your local environment settings.
   ============================================================= */

// ---- Database credentials ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'id13212736_event');

// ---- Razorpay API credentials (UPI-Only Payments) ----
define('RAZORPAY_KEY_ID', 'rzp_test_...');
define('RAZORPAY_KEY_SECRET', '...');
define('RAZORPAY_WEBHOOK_SECRET', '...');

// ---- Google OAuth Client ID ----
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');

// ---- Application Debugging ----
define('APP_DEBUG', true);
?>
