<?php
/* =============================================================
   EVENTHUB PRO — Closed event publish re-opener (Phase 4)
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

if (isset($_POST['publish'])) { 
    $today = date('Y-m-d');
    $startdate = $event['startdate'];
    
    if (!empty($startdate) && $startdate < $today) {
        echo "Please Edit the Event Time Before opening it Again. Go back to <a href='dashboard.php'>Dashboard</a>";
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE create_event SET publish_event = 'yes', open_closed = 'open' WHERE Event_ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header('Location: dashboard.php');
exit;
?>