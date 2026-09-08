<?php
/* =============================================================
   Database connection (single source of truth)
   Credentials are loaded from config.php — do not hardcode
   credentials in any other file.
   ============================================================= */
require_once __DIR__ . '/config.php';

// Turn off exception throwing so we can handle connection gracefully
mysqli_report(MYSQLI_REPORT_OFF);

$port = defined('DB_PORT') ? (int)DB_PORT : (getenv('DB_PORT') ? (int)getenv('DB_PORT') : 3306);
$conn = mysqli_init();

// Set 5-second connection timeout for cloud serverless environments
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

$isCloud = ((defined('MYSQL_SSL') && MYSQL_SSL) || strpos(DB_HOST, 'tidbcloud.com') !== false);

if ($isCloud) {
    // Cloud SSL connection (TiDB Cloud / Aiven)
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    @$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port, NULL, MYSQLI_CLIENT_SSL);
    
    // Fallback if SSL flag failed
    if ($conn->connect_error) {
        $conn = mysqli_init();
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        @$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
    }
} else {
    // Standard connection
    @$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);
}

// Check connection
if ($conn->connect_error) {
    if (defined('APP_DEBUG') && APP_DEBUG) {
        die('Database Connection Failed: ' . $conn->connect_error . ' (Host: ' . DB_HOST . ', Port: ' . $port . ')');
    }
    die('Database connection could not be established.');
}

// Ensure all queries use UTF-8
$conn->set_charset('utf8mb4');
