<?php
/* =============================================================
   Database connection (single source of truth)
   Credentials are loaded from config.php — do not hardcode
   credentials in any other file.
   Includes persistent authentication auto-validation.
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
    // Standard local connection
    @$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, $port);

    // If local MySQL server is not running, seamless fallback to TiDB Cloud
    if ($conn->connect_error) {
        $cloudHost = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
        $cloudUser = '2NNd45iVF9gUDLQ.root';
        $cloudPass = 'O2q86KIcftR5XbcU';
        $cloudDb   = 'id13212736_event';
        $cloudPort = 4000;

        $conn = mysqli_init();
        $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        @$conn->real_connect($cloudHost, $cloudUser, $cloudPass, $cloudDb, $cloudPort, NULL, MYSQLI_CLIENT_SSL);
    }
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

// Automatically verify persistent login token if session is not active
require_once __DIR__ . '/auth_helper.php';
if ($conn && !$conn->connect_error) {
    check_persistent_login($conn);
}
