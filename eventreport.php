<?php
session_start();
include('dbconnect.php');
$category = trim($_GET['category'] ?? '');
$year     = trim($_GET['year'] ?? '');
$username = $_SESSION['username'] ?? '';

$name = '';
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $name = $row['full_name'];
}
$stmt->close();

$event = '<select id="eventcategory" onchange="eventcategory1()">
<option value="select the event name">Select the event name</option>';

$stmt = $conn->prepare("SELECT event_title FROM create_event WHERE category = ? AND startdate LIKE ? AND organizer_name = ?");
$likeYear = '%' . $year . '%';
$stmt->bind_param('sss', $category, $likeYear, $name);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $event .= '<option value="' . htmlspecialchars($row['event_title']) . '">' . htmlspecialchars($row['event_title']) . '</option>';
}
$stmt->close();
$event .= '</select>';

echo $event;
