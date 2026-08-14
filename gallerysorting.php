<?php
/* =============================================================
   Gallery sort/filter handler (AJAX)
   Uses prepared statements to prevent SQL injection.
   ============================================================= */
include('dbconnect.php');

$category = trim($_GET['category'] ?? '');
$year     = trim($_GET['year'] ?? '');

$sql = "SELECT organizer_name, event_name, image FROM gallery WHERE 1=1";
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

$gallery = "<link rel='stylesheet' href='css/gallery.css'>";
$gallery .= '<center><div class="demo"><ul id="lightSlider">';

$found = false;
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $found = true;
        $gallery .= '<li data-thumb="gallery/' . htmlspecialchars($row['image']) . '">
            <div class="content_img">
                <img id="yo" src="gallery/' . htmlspecialchars($row['image']) . '" alt="' . htmlspecialchars($row['event_name']) . '" />
                <div><center>Event Name: ' . htmlspecialchars($row['event_name']) . '<br>
                    Organizer Name: ' . htmlspecialchars($row['organizer_name']) . '
                </center></div>
            </div>
        </li>';
    }
    $stmt->close();
}

$gallery .= '</center></ul></div>';
$gallery .= "<script src='https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'></script>
<script src='https://sachinchoolur.github.io/lightslider/dist/js/lightslider.js'></script>
<script id='rendered-js'>
$('#lightSlider').lightSlider({
  gallery: true, item: 1, loop: true, slideMargin: 0, thumbItem: 9 });
</script>";

if ($found) {
    echo $gallery;
} else {
    echo "No Images Found For These Query";
}
