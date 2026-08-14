<?php
/* =============================================================
   Admin user creation handler
   Uses prepared statements to prevent SQL injection.
   ============================================================= */
session_start();
include('dbconnect.php');

if (isset($_POST['submit'])) {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $role     = trim($_POST['role'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name !== '' && $email !== '' && $role !== '' && $username !== '' && $password !== '' &&
        filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO sign_up (role, username, full_name, email, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $role, $username, $name, $email, $hashedPassword);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            echo "<br/><br/><span>Data Inserted successfully...!!</span>";
        } else {
            echo "<p>Insertion Failed <br/> Some Fields are Blank....!!</p>";
        }
    } else {
        echo "<p>Please fill all fields with valid data.</p>";
    }
}

$conn->close();
