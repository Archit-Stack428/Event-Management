<?php
/* =============================================================
   Category event filter handler (AJAX)
   Uses prepared statements to prevent SQL injection.
   ============================================================= */
include('dbconnect.php');

$id = trim($_GET['id'] ?? '');

$stmt = $conn->prepare("SELECT event_title, event_venue, event_thumbnail, event_id, startdate
                        FROM create_event
                        WHERE publish_event='yes' AND category=? AND open_closed='open'");
$stmt->bind_param('s', $id);
$stmt->execute();
$res = $stmt->get_result();

$cards = '';
while ($row = $res->fetch_assoc()) {
    $startdate = date('d', strtotime($row['startdate']));
    $date = strtotime($row['startdate']);
    $cards .= "<article class='eventCard__item item-2'>
        <div class='item-2-img'>
            <img src='images/" . htmlspecialchars($row['event_thumbnail']) . "'>
            <div class='date-1 bottom'><span>" . $startdate . "</span>" . date('M', $date) . "</div>
        </div>
        <div class='item-2-box pl'>
            <h3 class='name'><a href='eventpage.php?id=" . (int)$row['event_id'] . "'>" . htmlspecialchars($row['event_title']) . "</a></h3>
            <p class='address'>" . htmlspecialchars($row['event_venue']) . "</p>
        </div>
    </article>";
}
$stmt->close();

echo "<div class='container cards'><div class='eventCard grid'>";
if ($cards !== '') {
    echo $cards;
} else {
    echo "Sorry, No Event Found For These Category";
}
echo "</div></div>";
$conn->close();
