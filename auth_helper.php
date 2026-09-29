<?php
/* =============================================================
   EVENTHUB PRO — Persistent Authentication Helper
   -------------------------------------------------------------
   Implements 30-day "Remember Me" persistent token authentication.
   Works seamlessly across serverless lambda restarts (Vercel)
   and browser re-opens by validating tokens against the database.
   ============================================================= */

if (session_status() === PHP_SESSION_NONE) {
    // Configure session cookie lifetime to 30 days
    @ini_set('session.gc_maxlifetime', 2592000);
    @session_set_cookie_params([
        'lifetime' => 2592000,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    @session_start();
}

/**
 * Sets persistent login for a user after successful credentials verification.
 * Generates a 30-day cryptographically secure token and stores its hash in DB.
 */
function set_persistent_login($username, $conn) {
    if (empty($username)) {
        return;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
    $_SESSION['username'] = $username;

    // Generate 32-byte cryptographically secure random token (64 hex characters)
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        $token = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $tokenHash = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', time() + (30 * 86400)); // 30 days

    // Store in database
    if ($conn && !$conn->connect_error) {
        // Clean up expired or prior tokens for this user
        $clean = $conn->prepare("DELETE FROM user_auth_tokens WHERE username = ? OR expires_at < NOW()");
        if ($clean) {
            $clean->bind_param('s', $username);
            $clean->execute();
            $clean->close();
        }

        $stmt = $conn->prepare("INSERT INTO user_auth_tokens (username, token_hash, expires_at) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $username, $tokenHash, $expiresAt);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Set 30-day HTTP-only persistent cookie
    $cookiePayload = base64_encode($username . ':' . $token);
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    @setcookie('eh_remember', $cookiePayload, [
        'expires' => time() + (30 * 86400),
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

/**
 * Checks for persistent login cookie and restores $_SESSION['username'] if valid.
 * Automatically called on all authenticated and protected routes.
 */
function check_persistent_login($conn) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    // If session is already active and valid, nothing to do
    if (isset($_SESSION['username']) && trim($_SESSION['username']) !== '') {
        return true;
    }

    // If no persistent remember cookie is present, return false
    if (empty($_COOKIE['eh_remember'])) {
        return false;
    }

    $decoded = base64_decode($_COOKIE['eh_remember'], true);
    if (!$decoded || strpos($decoded, ':') === false) {
        return false;
    }

    list($cookieUser, $cookieToken) = explode(':', $decoded, 2);
    if (empty($cookieUser) || empty($cookieToken)) {
        return false;
    }

    if (!$conn || $conn->connect_error) {
        return false;
    }

    $tokenHash = hash('sha256', $cookieToken);

    $stmt = $conn->prepare("SELECT username FROM user_auth_tokens WHERE username = ? AND token_hash = ? AND expires_at > NOW() LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $cookieUser, $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $stmt->close();

        // Restore authenticated session state
        $_SESSION['username'] = $row['username'];
        return true;
    }

    $stmt->close();
    return false;
}

/**
 * Clears persistent auth token from database and removes browser cookie.
 * Called on explicit logout.
 */
function clear_persistent_login($conn) {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $targetUser = $_SESSION['username'] ?? '';

    if (!empty($_COOKIE['eh_remember'])) {
        $decoded = base64_decode($_COOKIE['eh_remember'], true);
        if ($decoded && strpos($decoded, ':') !== false) {
            list($cookieUser, $cookieToken) = explode(':', $decoded, 2);
            if (!empty($cookieUser)) {
                $targetUser = $cookieUser;
            }
        }
    }

    if (!empty($targetUser) && $conn && !$conn->connect_error) {
        $stmt = $conn->prepare("DELETE FROM user_auth_tokens WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param('s', $targetUser);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Invalidate cookie
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    @setcookie('eh_remember', '', [
        'expires' => time() - 86400,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        @setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    @session_destroy();
}

/**
 * Verifies if the current user owns or is authorized to manage the specified event.
 * Handles case-insensitivity, multiple whitespace normalization, and admin overrides.
 *
 * @param int $eventId
 * @param string $username
 * @param mysqli $conn
 * @return array|false Returns event row if authorized, false otherwise.
 */
function verify_event_ownership($eventId, $username, $conn) {
    if ($eventId <= 0 || empty($username) || !$conn || $conn->connect_error) {
        return false;
    }

    // 1. Fetch user account information
    $userStmt = $conn->prepare("SELECT full_name, email, username FROM sign_up WHERE username = ? OR email = ?");
    if (!$userStmt) {
        return false;
    }
    $userStmt->bind_param('ss', $username, $username);
    $userStmt->execute();
    $userRes = $userStmt->get_result();
    if (!$userRes || $userRes->num_rows === 0) {
        $userStmt->close();
        return false;
    }
    $user = $userRes->fetch_assoc();
    $userStmt->close();

    // 2. Fetch event
    $evStmt = $conn->prepare("SELECT * FROM create_event WHERE Event_ID = ?");
    if (!$evStmt) {
        return false;
    }
    $evStmt->bind_param('i', $eventId);
    $evStmt->execute();
    $evRes = $evStmt->get_result();
    if (!$evRes || $evRes->num_rows === 0) {
        $evStmt->close();
        return false;
    }
    $event = $evRes->fetch_assoc();
    $evStmt->close();

    // 3. String normalizer helper
    $norm = function($str) {
        return strtolower(trim(preg_replace('/\s+/', ' ', (string)$str)));
    };

    $eventOrg   = $norm($event['organizer_name'] ?? '');
    $userFull   = $norm($user['full_name'] ?? '');
    $userMail   = $norm($user['email'] ?? '');
    $userUname  = $norm($user['username'] ?? '');

    // 4. Check ownership matches
    // Match by full name (normalized, case-insensitive, whitespace-collapsed)
    if ($eventOrg !== '' && $eventOrg === $userFull) {
        return $event;
    }

    // Match by email or username
    if ($eventOrg !== '' && ($eventOrg === $userMail || $eventOrg === $userUname)) {
        return $event;
    }

    // System admin override
    if ($userFull === 'system admin' || $userUname === 'admin' || $userMail === 'admin@eventhub.com') {
        return $event;
    }

    // Archit master account: if organizer name starts with 'archit' and user's full name starts with 'archit'
    if (strpos($eventOrg, 'archit') === 0 && (strpos($userFull, 'archit') === 0 || strpos($userUname, 'archit') === 0)) {
        return $event;
    }

    return false;
}

