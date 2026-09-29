<?php
/* =============================================================
   Admin User Creation Handler
   Inserts new organizer into `sign_up` table securely.
   Note: `username` and `full_name` are generated columns in TiDB/MySQL.
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require admin/organizer authentication
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Optional CSRF check
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!empty($_SESSION['csrf_token']) && !empty($csrfToken) && !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $_SESSION['error'] = 'Invalid session token. Please try again.';
        header('Location: createuser.php');
        exit;
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';

    // Legacy fallback if single 'name' field was provided
    if ($firstName === '' && !empty($_POST['name'])) {
        $parts     = preg_split('/\s+/', trim($_POST['name']), 2);
        $firstName = $parts[0] ?? '';
        $lastName  = $parts[1] ?? '';
    }

    if ($firstName === '') {
        $_SESSION['error'] = 'First name is required.';
        header('Location: createuser.php');
        exit;
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please enter a valid email address.';
        header('Location: createuser.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header('Location: createuser.php');
        exit;
    }

    // Check if account with email already exists
    $checkStmt = $conn->prepare("SELECT id FROM sign_up WHERE email = ? LIMIT 1");
    if ($checkStmt) {
        $checkStmt->bind_param('s', $email);
        $checkStmt->execute();
        $checkRes = $checkStmt->get_result();
        if ($checkRes && $checkRes->num_rows > 0) {
            $checkStmt->close();
            $_SESSION['error'] = 'An account with email ' . htmlspecialchars($email) . ' already exists.';
            header('Location: createuser.php');
            exit;
        }
        $checkStmt->close();
    }

    // Hash password securely using BCRYPT
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert only writable columns: first_name, last_name, email, password
    // TiDB/MySQL automatically computes `username` (as email) and `full_name` (as concat)
    $stmt = $conn->prepare("INSERT INTO sign_up (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['error'] = 'Database preparation error: ' . $conn->error;
        header('Location: createuser.php');
        exit;
    }

    $stmt->bind_param('ssss', $firstName, $lastName, $email, $hashedPassword);
    $execResult = $stmt->execute();

    if ($execResult) {
        $stmt->close();
        $_SESSION['success'] = 'User account (' . htmlspecialchars($email) . ') created successfully!';
        header('Location: createuser.php');
        exit;
    } else {
        $errorMsg = $stmt->error ?: 'Unknown database error';
        $stmt->close();
        $_SESSION['error'] = 'Failed to create user: ' . $errorMsg;
        header('Location: createuser.php');
        exit;
    }
} else {
    header('Location: createuser.php');
    exit;
}
