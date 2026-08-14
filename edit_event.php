<?php
/* =============================================================
   EVENTHUB PRO — Edit event handler (Phase 4)
   Uses prepared statements, CSRF checks, secure upload validations,
   and redirects to dashboard.php upon successful execution.
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
$organizer_name = '';
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $organizer_name = $row['full_name'];
}
$stmt->close();

if ($organizer_name === '') {
    die('Unauthorized access. Invalid account.');
}

if (isset($_POST['submit'])) {
    // CSRF token validation
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        die('CSRF token validation failed.');
    }

    $id         = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        die('Invalid Event ID.');
    }

    // Verify event ownership before doing anything
    $stmt = $conn->prepare("SELECT organizer_name FROM create_event WHERE Event_ID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        die('Event not found.');
    }
    $event = $res->fetch_assoc();
    $stmt->close();

    if ($event['organizer_name'] !== $organizer_name) {
        die('Unauthorized access. You do not own this event.');
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

    // Server-side validation
    if ($eventtitle === '' || strlen($eventtitle) > 150) {
        die('Invalid event title length.');
    }
    if ($desc === '') {
        die('Event description is required.');
    }
    if ($venue === '') {
        die('Venue is required.');
    }
    if (!in_array($category, ['music','dance','buisness','fine arts','theatre','health','hospitality','fashion','sports','law','technical','literary','others'])) {
        die('Invalid category selected.');
    }
    if (!in_array($eventtype, ['Single Participant', 'Team Event'])) {
        die('Invalid event type.');
    }

    // Parse date range "start - end"
    $startdate = $enddate = '0000-00-00';
    if ($eventdate === '' || strpos($eventdate, ' - ') === false) {
        die("Please enter the event date range in the format YYYY-MM-DD - YYYY-MM-DD.");
    }
    $parts = explode(' - ', $eventdate, 2);
    $s = strtotime(trim($parts[0]));
    $e = strtotime(trim($parts[1]));
    if ($s === false || $e === false) {
        die("Please enter a valid event date range.");
    }
    if ($s > $e) {
        die("Start date cannot be after end date.");
    }
    $startdate = date('Y-m-d', $s);
    $enddate   = date('Y-m-d', $e);

    // Dynamic field lists
    $rule  = implode(" , ", array_map('trim', (array)($_POST['rules'] ?? [])));
    $prize = implode(" , ", array_map('trim', (array)($_POST['prize'] ?? [])));

    $image = null;
    $target_dir = "images/";

    // Secure thumbnail file upload validation
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        $check = getimagesize($_FILES['thumbnail']['tmp_name']);
        if ($check === false) {
            die("File is not a valid image.");
        }
        if ($_FILES['thumbnail']['size'] > 5000000) {
            die("Sorry, your file is too large.");
        }

        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            die("Only JPG, JPEG, PNG, GIF, and WEBP files are allowed.");
        }

        $safeTitle = preg_replace('/[^A-Za-z0-9_-]/', '_', $eventtitle);
        $image = $safeTitle . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_dir . $image)) {
            die("Sorry, there was an error uploading your thumbnail.");
        }
    }

    $min_team = 0;
    $max_team = 0;
    if ($eventtype === 'Team Event') {
        $min_team = max(0, (int)($_POST['min_team_size'] ?? 0));
        $max_team = max(0, (int)($_POST['max_team_size'] ?? 0));
        if ($min_team > $max_team) {
            die("Minimum team size cannot exceed maximum team size.");
        }
    }

    if ($image) {
        $sql = "UPDATE create_event SET Event_TITLE=?, EVENT_DESC=?, Category=?, eventtype=?, min_team=?, max_team=?, EVENT_RULES=?, startdate=?, enddate=?, EVENT_VENUE=?, time=?, event_price=?, event_thumbnail=?, EVENT_SPONSORS=?, EVENT_PRIZES=? WHERE Event_ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssisssi', $eventtitle, $desc, $category, $eventtype, $min_team, $max_team, $rule, $startdate, $enddate, $venue, $time, $price, $image, $sponsors, $prize, $id);
    } else {
        $sql = "UPDATE create_event SET Event_TITLE=?, EVENT_DESC=?, Category=?, eventtype=?, min_team=?, max_team=?, EVENT_RULES=?, startdate=?, enddate=?, EVENT_VENUE=?, time=?, event_price=?, EVENT_SPONSORS=?, EVENT_PRIZES=? WHERE Event_ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssissi', $eventtitle, $desc, $category, $eventtype, $min_team, $max_team, $rule, $startdate, $enddate, $venue, $time, $price, $sponsors, $prize, $id);
    }

    $result = $stmt->execute();
    $stmt->close();

    if ($result) {
        $conn->close();
        header('Location: dashboard.php');
        exit;
    } else {
        $conn->close();
        die("Update Failed. Please check database configuration.");
    }
}
$conn->close();
?>
