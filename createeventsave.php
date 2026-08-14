<?php
/* =============================================================
   EVENTHUB PRO — Create event save handler (Phase 4)
   Validates inputs, file uploads, checks CSRF, and redirects.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'];
$name = '';
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($r = $res->fetch_assoc()) {
    $name = $r['full_name'];
}
$stmt->close();

if ($name === '') {
    die('Unauthorized access. Invalid account.');
}

error_log("[EVENTHUB SAVE] POST: " . json_encode($_POST) . " | FILES: " . json_encode($_FILES));

if (isset($_POST['submit'])) {
    // CSRF Token Validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        error_log("[EVENTHUB SAVE] CSRF failed. Session token: " . ($_SESSION['csrf_token'] ?? 'none') . " | Post token: " . $token);
        die('CSRF token validation failed.');
    }

    $eventtitle = trim($_POST['eventtitle'] ?? '');
    $desc       = trim($_POST['desc'] ?? '');
    $category   = trim($_POST['category'] ?? '');
    $eventtype  = trim($_POST['eventtype'] ?? '');
    $price      = max(0, (int)($_POST['price'] ?? 0));
    $time       = trim($_POST['time'] ?? '');
    $eventdate  = trim($_POST['eventdate'] ?? '');
    $venue      = trim($_POST['venue'] ?? '');
    $sponsors   = trim($_POST['sponsors'] ?? '');

    // Server-side validations
    if ($eventtitle === '' || strlen($eventtitle) > 150) {
        error_log("[EVENTHUB SAVE] Invalid event title: " . $eventtitle);
        die('Invalid event title length.');
    }
    if ($desc === '') {
        error_log("[EVENTHUB SAVE] Description empty");
        die('Event description is required.');
    }
    if ($venue === '') {
        error_log("[EVENTHUB SAVE] Venue empty");
        die('Venue is required.');
    }
    if (!in_array($category, ['music','dance','buisness','fine arts','theatre','health','hospitality','fashion','sports','law','technical','literary','others'])) {
        error_log("[EVENTHUB SAVE] Invalid category: " . $category);
        die('Invalid category selected.');
    }
    if (!in_array($eventtype, ['Single Participant', 'Team Event'])) {
        error_log("[EVENTHUB SAVE] Invalid eventtype: " . $eventtype);
        die('Invalid event type.');
    }

    // Parse date range "start - end"
    $startdate = $enddate = '0000-00-00';
    if ($eventdate === '' || strpos($eventdate, ' - ') === false) {
        error_log("[EVENTHUB SAVE] Invalid eventdate: " . $eventdate);
        die("Please enter the event date range in the format YYYY-MM-DD - YYYY-MM-DD.");
    }
    $parts = explode(' - ', $eventdate, 2);
    $s = strtotime(trim($parts[0]));
    $e = strtotime(trim($parts[1]));
    if ($s === false || $e === false) {
        error_log("[EVENTHUB SAVE] Date parse failed: " . $eventdate);
        die("Please enter a valid event date range.");
    }
    if ($s > $e) {
        error_log("[EVENTHUB SAVE] Start date after end date: " . $eventdate);
        die("Start date cannot be after end date.");
    }
    $startdate = date('Y-m-d', $s);
    $enddate   = date('Y-m-d', $e);

    // Dynamic fields
    $rule  = implode(" , ", array_map('trim', (array)($_POST['rules'] ?? [])));
    $prize = implode(" , ", array_map('trim', (array)($_POST['prize'] ?? [])));

    $image = '';
    $target_dir = "images/";
    $uploadOk = 1;

    // Secure file upload validation
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $check = getimagesize($_FILES['thumbnail']['tmp_name']);
        if ($check === false) {
            error_log("[EVENTHUB SAVE] Upload is not a valid image");
            die("File is not a valid image.");
        }
        if ($_FILES['thumbnail']['size'] > 5000000) { // Limit size to 5MB
            error_log("[EVENTHUB SAVE] File too large");
            die("Sorry, your file is too large.");
        }
        
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            error_log("[EVENTHUB SAVE] Invalid extension: " . $ext);
            die("Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.");
        }

        $safeTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $eventtitle);
        $image = $safeTitle . '_' . time() . '.' . $ext;
        $upload_success = (PHP_SAPI === 'cli')
            ? copy($_FILES['thumbnail']['tmp_name'], $target_dir . $image)
            : move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_dir . $image);
        if (!$upload_success) {
            error_log("[EVENTHUB SAVE] move_uploaded_file failed to " . $target_dir . $image);
            die("Sorry, there was an error uploading your thumbnail.");
        }
    } else {
        error_log("[EVENTHUB SAVE] Thumbnail file empty");
        die("Thumbnail image is required to create an event.");
    }

    $min_team = 0;
    $max_team = 0;
    if ($eventtype === 'Team Event') {
        $min_team = max(0, (int)($_POST['min_team_size'] ?? 0));
        $max_team = max(0, (int)($_POST['max_team_size'] ?? 0));
        if ($min_team > $max_team) {
            error_log("[EVENTHUB SAVE] min_team > max_team");
            die("Minimum team size cannot exceed maximum team size.");
        }
    }

    // Insert new event
    $sql = "INSERT INTO create_event
            (organizer_name, event_title, event_desc, category, eventtype, min_team, max_team,
             event_rules, startdate, enddate, event_venue, time, event_price, event_thumbnail,
             event_sponsors, event_prizes, publish_event, open_closed)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'no', 'open')";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("[EVENTHUB SAVE] Prepare failed: " . $conn->error);
        die("Database error. Prepare failed.");
    }

    $stmt->bind_param(
        'ssssssssssssisss',
        $name, $eventtitle, $desc, $category, $eventtype, $min_team, $max_team,
        $rule, $startdate, $enddate, $venue, $time, $price, $image, $sponsors, $prize
    );

    if ($stmt->execute()) {
        error_log("[EVENTHUB SAVE] Event created successfully! Event ID: " . $stmt->insert_id);
        $stmt->close();
        $conn->close();
        header('Location: dashboard.php');
        exit;
    } else {
        error_log("[EVENTHUB SAVE] Execute failed: " . $stmt->error);
        $stmt->close();
        $conn->close();
        die("Database insertion failed. Try again.");
    }
} else {
    error_log("[EVENTHUB SAVE] submit button not set in POST");
}
$conn->close();
?>
