<?php
/* =============================================================
   Database connection (single source of truth)
   Credentials are loaded from config.php — do not hardcode
   credentials in any other file.
   ============================================================= */
require_once __DIR__ . '/config.php';

// Create connection with SSL support for Cloud databases like TiDB
$port = defined('DB_PORT') ? (int)DB_PORT : (getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306);
$conn = mysqli_init();

if ((defined('MYSQL_SSL') && MYSQL_SSL) || strpos(DB_HOST, 'tidbcloud.com') !== false) {
    // Cloud SSL connection (TiDB Cloud / Aiven)
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    // Standard connection
    $conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
}

// Check connection
if ($conn->connect_error) {
    // In production, log the real error and show a generic message.
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die('Connection failed: ' . $conn->connect_error);
    }
    die('Database connection could not be established.');
}

// Ensure all queries use UTF-8
$conn->set_charset('utf8mb4');
