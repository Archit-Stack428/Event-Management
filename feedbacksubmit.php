<?php
/* =============================================================
   Feedback submission handler
   Uses prepared statements to prevent SQL injection.
   Validates and escapes output.
   ============================================================= */
session_start();
include('dbconnect.php');

if (isset($_POST['submit'])) {
    $eventid  = (int)($_GET['eventid'] ?? 0);
    $name     = trim($_POST['name'] ?? '');
    $feedback = trim($_POST['desc'] ?? '');
    $stars    = (int)($_POST['rating'] ?? 0);

    if ($eventid > 0 && $name !== '' && $feedback !== '' && $stars >= 1 && $stars <= 5) {
        $stmt = $conn->prepare("INSERT INTO feedback (event_id, name, feedback, stars) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('issi', $eventid, $name, $feedback, $stars);
        $stmt->execute();
        $stmt->close();

        echo "Thanks for your Feedback";
    } else {
        echo "Please provide valid feedback.";
    }
}

$conn->close();
