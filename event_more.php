<?php
/* =============================================================
   "Load more" events handler (AJAX)
   Uses prepared statements to prevent SQL injection.
   ============================================================= */
include('dbconnect.php');

$last_event_id = isset($_POST['last_event_id']) ? (int)$_POST['last_event_id'] : 0;

$stmt = $conn->prepare("SELECT event_title, event_venue, event_thumbnail, event_id, startdate
                        FROM create_event
                        WHERE publish_event='yes' AND open_closed='open' AND startdate > ?
                        ORDER BY startdate ASC LIMIT 3");
$stmt->bind_param('s', $last_event_id);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $startdate = date('d', strtotime($row['startdate']));
        $date = strtotime($row['startdate']);
        $output .= "<article class='eventCard__item item-2'>
            <div class='item-2-img'>
                <img src='images/" . htmlspecialchars($row['event_thumbnail']) . "'>
                <div class='date-1 bottom'>
                    <span>" . $startdate . "</span>" . date('M', $date) . "
                </div>
            </div>
            <div class='item-2-box pl'>
                <h3 class='name'><a href='eventpage.php?id=" . (int)$row['event_id'] . "'>" . htmlspecialchars($row['event_title']) . "</a></h3>
                <p class='address'>" . htmlspecialchars($row['event_venue']) . "</p>
            </div>
        </article>";
    }
    $eventid = $result->fetch_assoc()['startdate'] ?? $row['startdate'];
    $output .= '<button style="margin-left:505px; margin-top:20px" type="button" name="btn_more" data-vid="' . htmlspecialchars($eventid) . '" id="btn_more">Load More</button>';
}
$stmt->close();

echo $output;
$conn->close();
