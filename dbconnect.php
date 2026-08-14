<?php
/* =============================================================
   Database connection (single source of truth)
   Credentials are loaded from config.php — do not hardcode
   credentials in any other file.
   ============================================================= */
require_once __DIR__ . '/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

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
