<?php
/* =============================================================
   EVENTHUB PRO — Gallery image upload handler (Phase 4)
   Uses prepared statements, CSRF validation, session checks,
   and restricts file uploads to secure image extensions.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

if (isset($_POST['submit_image'])) {
    // CSRF Validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('CSRF token validation failed.');
    }

    $eventtitle = trim($_POST['eventtitle'] ?? '');
    if ($eventtitle === '') {
        die('Event selection is required.');
    }

    // Get logged-in organizer full name to verify ownership
    $username = $_SESSION['username'];
    $name = '';
    $stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $name = $row['full_name'];
    }
    $stmt->close();

    if ($name === '') {
        die('Unauthorized access. Invalid account.');
    }

    // Fetch event details to populate gallery item metadata
    $stmt = $conn->prepare("SELECT category, organizer_name, startdate FROM create_event WHERE event_title = ?");
    $stmt->bind_param('s', $eventtitle);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        die('Selected event not found.');
    }
    $row = $res->fetch_assoc();
    $category  = $row['category'] ?? 'General';
    $startdate = $row['startdate'] ?? '';
    $orgname   = $row['organizer_name'] ?? '';
    $stmt->close();

    // Verify organizer owns the event before uploading photos to it!
    if ($orgname !== $name) {
        die('Unauthorized access. You do not own this event.');
    }

    if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
        $count = count($_FILES['files']['name']);
        $galleryDir = __DIR__ . '/gallery/';

        $ins = $conn->prepare("INSERT INTO gallery (event_name, organizer_name, image, date, category) VALUES (?, ?, ?, ?, ?)");

        for ($i = 0; $i < $count; $i++) {
            $tmpName = $_FILES['files']['tmp_name'][$i] ?? '';
            $origName = $_FILES['files']['name'][$i] ?? '';
            
            if ($tmpName === '' || $origName === '') {
                continue;
            }

            // Image verification check
            $check = getimagesize($tmpName);
            if ($check === false) {
                continue; // Skip non-images
            }

            // Restrict file size (limit 5MB per image)
            if ($_FILES['files']['size'][$i] > 5000000) {
                continue; // Skip oversize
            }

            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_exts)) {
                continue; // Skip forbidden extensions
            }

            // Generate safe filename to prevent code injection/execution
            $safeEvent = preg_replace('/[^A-Za-z0-9_-]/', '_', $eventtitle);
            $image = $safeEvent . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;

            if (move_uploaded_file($tmpName, $galleryDir . $image)) {
                $ins->bind_param('sssss', $eventtitle, $orgname, $image, $startdate, $category);
                $ins->execute();
            }
        }
        $ins->close();
    }

    header('Location: dashboard.php');
    exit;
}

$conn->close();
?>
