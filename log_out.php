<?php
/* =============================================================
   EVENTHUB PRO — Secure Logout (Phase 5)
   Clears all session variables, invalidates cookies, destroys the
   session, revokes persistent auth token from DB, and redirects.
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/auth_helper.php';

clear_persistent_login($conn);

header("Location: index.php");
exit;