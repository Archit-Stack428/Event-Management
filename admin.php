<?php
/* =============================================================
   Admin Route
   Redirects to the EventHub Pro Admin / Organizer Dashboard.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

header('Location: dashboard.php');
exit;
