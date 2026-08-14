<?php
/* =============================================================
   EVENTHUB PRO — Secure SignUp Handler (Phase 5)
   Prepared statements, password hashing, and duplicate account checks.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (isset($_POST['submit'])) {
    $first_name = trim($_POST['firstname'] ?? '');
    $last_name  = trim($_POST['lastname'] ?? '');
    $email      = trim($_POST['mail'] ?? '');
    $password   = $_POST['password'] ?? '';

    // Field check validations
    if ($first_name === '' || $last_name === '' || $email === '' || $password === '') {
        $_SESSION['error'] = "All registration fields are required.";
        header('Location: signup.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header('Location: signup.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = "Password must be at least 8 characters long.";
        header('Location: signup.php');
        exit;
    }

    // Check for duplicate account/email
    $stmt = $conn->prepare("SELECT id FROM sign_up WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $stmt->close();
        $_SESSION['error'] = "An account with this email address already exists.";
        header('Location: signup.php');
        exit;
    }
    $stmt->close();

    // Hashing password securely via BCRYPT (PASSWORD_DEFAULT)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO sign_up (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashedPassword);
    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $_SESSION['success'] = "Account created successfully! You can now log in.";
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header('Location: signup.php');
        exit;
    }
}

$conn->close();
header('Location: signup.php');
exit;
?>
