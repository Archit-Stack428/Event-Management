<?php
/* =============================================================
   EVENTHUB PRO — Gallery media AJAX (masonry) handler
   Supporting endpoint. Uses prepared statements.
   Returns premium masonry gallery markup.
   ============================================================= */
include('dbconnect.php');

$category       = trim($_GET['category'] ?? '');
$year           = trim($_GET['year'] ?? '');
$event_name     = trim($_GET['event_name'] ?? '');
$organizer_name = trim($_GET['organizer_name'] ?? '');
$q              = trim($_GET['q'] ?? '');

$sql = "SELECT organizer_name, event_name, image, category, date FROM gallery WHERE 1=1";
$params = array();
$types = '';

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}
if ($year !== '') {
    $sql .= " AND date LIKE ?";
    $params[] = '%' . $year . '%';
    $types .= 's';
}
if ($event_name !== '') {
    $sql .= " AND event_name = ?";
    $params[] = $event_name;
    $types .= 's';
}
if ($organizer_name !== '') {
    $sql .= " AND organizer_name = ?";
    $params[] = $organizer_name;
    $types .= 's';
}
if ($q !== '') {
    $sql .= " AND (event_name LIKE ? OR organizer_name LIKE ? OR category LIKE ?)";
    $likeQ = '%' . $q . '%';
    array_push($params, $likeQ, $likeQ, $likeQ);
    $types .= 'sss';
}
$sql .= " ORDER BY id DESC";

$out = '';
$count = 0;

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = mysqli_query($conn, $sql);
}

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $count++;
        $img   = trim($row['image'] ?? '');
        if ($img === '') continue;
        $name  = htmlspecialchars($row['event_name'] ?: 'Event Photo');
        $org   = htmlspecialchars($row['organizer_name'] ?: '');
        $cat   = htmlspecialchars($row['category'] ?: '');
        $y     = '';
        if (!empty($row['date'])) { $y = date('Y', strtotime($row['date'])); }

        $out .= '<figure class="eh-gal-item eh-gal-loadable" data-cat="' . $cat . '" data-year="' . $y . '" data-reveal>
          <img src="gallery/' . htmlspecialchars($img) . '" alt="' . $name . '" loading="lazy" onerror="this.onerror=null;this.src=\'assets/img/ammunation-2019.jpg\';">
          <figcaption>
            <span class="eh-gal-name">' . $name . '</span>
            ' . ($org !== '' ? '<span class="eh-gal-org">' . $org . '</span>' : '') . '
            ' . ($cat !== '' ? '<span class="eh-gal-cat">' . $cat . '</span>' : '') . '
          </figcaption>
        </figure>';
    }
}

if (isset($stmt)) $stmt->close();

echo json_encode(['html' => $out, 'count' => $count]);
$conn->close();
?>
