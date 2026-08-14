<?php
/* =============================================================
   EVENTHUB PRO — Secure Authentication Handler (Phase 5)
   Prepared statements, password hash validation, and dynamic
   legacy password upgrade migrations.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (isset($_POST['submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['error'] = "Please enter both username and password.";
        header('Location: login.php');
        exit;
    }

    // Retrieve user and stored password hash/plaintext safely
    $stmt = $conn->prepare("SELECT password, full_name FROM sign_up WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $storedPassword = $row['password'];
        $fullName = $row['full_name'];
        $stmt->close();

        $authenticated = false;
        $needs_upgrade = false;

        // Check if verified by password_hash
        if (password_verify($password, $storedPassword)) {
            $authenticated = true;
        } elseif ($password === $storedPassword) {
            // Legacy plaintext account password match
            $authenticated = true;
            $needs_upgrade = true;
        }

        if ($authenticated) {
            // Upgrade legacy plaintext account on successful verification
            if ($needs_upgrade) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $up_stmt = $conn->prepare("UPDATE sign_up SET password = ? WHERE username = ?");
                $up_stmt->bind_param('ss', $newHash, $username);
                $up_stmt->execute();
                $up_stmt->close();
            }

            // Regenerate session ID to block session fixation attacks
            session_regenerate_id(true);

            $_SESSION['username'] = $username;
            $_SESSION['success'] = "Welcome back!";
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $stmt->close();
    }

    // Generic warning statement to avoid account enumeration
    $_SESSION['error'] = "Invalid username or password.";
    header('Location: login.php');
    exit;
}

$conn->close();
header('Location: login.php');
exit;
?>
