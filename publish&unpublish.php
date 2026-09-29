<?php
/* =============================================================
   EVENTHUB PRO — Publish / Unpublish handler (Phase 4)
   Uses prepared statement, CSRF validation, and ownership verification.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$token = $_POST['csrf_token'] ?? '';

// CSRF token validation
if ($id <= 0 || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    die('CSRF token validation failed or invalid parameters.');
}

// Verify event ownership with case and whitespace normalization
require_once __DIR__ . '/auth_helper.php';
$event = verify_event_ownership($id, $_SESSION['username'], $conn);
if (!$event) {
    die('Unauthorized access. You do not own this event.');
}

// Execute state update
if (isset($_POST['publish'])) { 
    $stmt = $conn->prepare("UPDATE create_event SET publish_event = 'yes' WHERE Event_ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_POST['unpublish'])) { 
    $stmt = $conn->prepare("UPDATE create_event SET publish_event = 'no' WHERE Event_ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header('Location: dashboard.php');
exit;
?>