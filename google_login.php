<?php
/* =============================================================
   EVENTHUB PRO — Secure Google Sign-In Handler
   Validates Google GIS identity credentials server-side via
   tokeninfo, links existing local sign_up accounts safely,
   creates secure random BCRYPT password hashes for new logins,
   and creates the authenticated PHP session state.
   ============================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/dbconnect.php';

header('Content-Type: application/json');

// 1. CSRF validation
$csrf_token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    echo json_encode(['error' => 'CSRF validation failed. Refresh the page and try again.']);
    exit;
}

// 2. Google credential extraction
$credential = $_POST['credential'] ?? '';
if (empty($credential)) {
    echo json_encode(['error' => 'Google ID Token is missing.']);
    exit;
}

// 3. Server-side token verification with Google tokeninfo
$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200 || !$response) {
    echo json_encode(['error' => 'Google authentication token signature or verification failed.']);
    exit;
}

$payload = json_decode($response, true);
if (!$payload) {
    echo json_encode(['error' => 'Invalid token info payload.']);
    exit;
}

// 4. Audience, Issuer, Expiration and Verification validation
if (!defined('GOOGLE_CLIENT_ID')) {
    echo json_encode(['error' => 'Server Google configuration missing. Contact administrator.']);
    exit;
}

if ($payload['aud'] !== GOOGLE_CLIENT_ID) {
    echo json_encode(['error' => 'OAuth client ID audience mismatch.']);
    exit;
}

if ($payload['iss'] !== 'accounts.google.com' && $payload['iss'] !== 'https://accounts.google.com') {
    echo json_encode(['error' => 'Invalid Google token identity issuer.']);
    exit;
}

if (!isset($payload['email_verified']) || ($payload['email_verified'] !== true && $payload['email_verified'] !== 'true')) {
    echo json_encode(['error' => 'Your Google email address is unverified.']);
    exit;
}

if (($payload['exp'] ?? 0) < time()) {
    echo json_encode(['error' => 'Google authentication session expired.']);
    exit;
}

// 5. Account matching and registration
$email = trim($payload['email'] ?? '');
$first_name = trim($payload['given_name'] ?? '');
$last_name = trim($payload['family_name'] ?? '');

if ($email === '') {
    echo json_encode(['error' => 'Google identity lacks an email profile.']);
    exit;
}

// Search existing sign_up account
$stmt = $conn->prepare("SELECT email FROM sign_up WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$existing_user = $res->fetch_assoc();
$stmt->close();

if ($existing_user) {
    // CASE A: Link / Login Existing Account
    // Regenerate session to block session fixation attacks
    session_regenerate_id(true);
    $_SESSION['username'] = $email;
    $_SESSION['success'] = "Logged in successfully via Google!";
    echo json_encode(['success' => true]);
    exit;
} else {
    // CASE B: Create a local account automatically for a new Google user
    // Store a cryptographically secure random password hash locally so they have no predictable plaintext credentials
    $random_password = bin2hex(random_bytes(32));
    $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO sign_up (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);
    $insert_result = $stmt->execute();
    $stmt->close();

    if ($insert_result) {
        session_regenerate_id(true);
        $_SESSION['username'] = $email;
        $_SESSION['success'] = "Account created and logged in via Google!";
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['error' => 'Account automatic registration failed. Please try again.']);
        exit;
    }
}
?>
