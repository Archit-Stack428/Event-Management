<?php
/* =============================================================
   EVENTHUB PRO — Events marketplace AJAX search / filter / sort
   Supporting endpoint (not a user-facing page).
   Uses prepared statements. Returns premium event card HTML.
   Backend logic / schema unchanged.
   ============================================================= */
include('dbconnect.php');

$q          = trim($_GET['q'] ?? '');
$category   = trim($_GET['category'] ?? '');
$type       = trim($_GET['type'] ?? '');       // single | team
$price      = trim($_GET['price'] ?? '');      // free | paid
$status     = trim($_GET['status'] ?? '');     // open | closed
$when       = trim($_GET['when'] ?? '');       // upcoming | ongoing | completed
$sort       = trim($_GET['sort'] ?? 'latest');
$offset     = max(0, (int)($_GET['offset'] ?? 0));
$limit      = 6;

$where = ["publish_event='yes'"];
$params = array();
$types = '';

// ---- Search (title, category, organizer, venue, description) ----
if ($q !== '') {
    $where[] = "(event_title LIKE ? OR category LIKE ? OR organizer_name LIKE ? OR event_venue LIKE ? OR event_desc LIKE ?)";
    $likeQ   = '%' . $q . '%';
    array_push($params, $likeQ, $likeQ, $likeQ, $likeQ, $likeQ);
    $types .= 'sssss';
}

// ---- Category filter ----
if ($category !== '') {
    $where[] = "category = ?";
    $params[] = $category;
    $types .= 's';
}

// ---- Event type (single vs team) ----
if ($type === 'single') {
    $where[] = "(min_team = 0 OR min_team IS NULL)";
} elseif ($type === 'team') {
    $where[] = "min_team > 0";
}

// ---- Free / Paid ----
if ($price === 'free') {
    $where[] = "(event_price = 0 OR event_price IS NULL)";
} elseif ($price === 'paid') {
    $where[] = "event_price > 0";
}

// ---- Registration status (open/closed) ----
if ($status === 'open') {
    $where[] = "open_closed = 'open'";
} elseif ($status === 'closed') {
    $where[] = "open_closed = 'closed'";
}

// ---- When (upcoming / ongoing / completed) ----
if ($when === 'upcoming') {
    $where[] = "startdate > CURDATE()";
} elseif ($when === 'ongoing') {
    $where[] = "startdate <= CURDATE() AND (enddate IS NULL OR enddate >= CURDATE())";
} elseif ($when === 'completed') {
    $where[] = "enddate IS NOT NULL AND enddate < CURDATE()";
}

$sql = "SELECT Event_ID, event_title, organizer_name, category, eventtype, min_team, max_team,
               startdate, enddate, time, event_venue, event_price, event_thumbnail, event_desc, open_closed
        FROM create_event WHERE " . implode(' AND ', $where);

// ---- Sorting ----
switch ($sort) {
    case 'oldest':      $sql .= " ORDER BY startdate ASC"; break;
    case 'popular':     $sql .= " ORDER BY Event_ID ASC"; break;
    case 'price_asc':   $sql .= " ORDER BY event_price ASC"; break;
    case 'price_desc':  $sql .= " ORDER BY event_price DESC"; break;
    case 'closing':     $sql .= " ORDER BY startdate ASC"; break;
    case 'latest':
    default:            $sql .= " ORDER BY startdate DESC"; break;
}

$sql .= " LIMIT $limit OFFSET $offset";

$res = null;
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = mysqli_query($conn, $sql);
}

$out = '';
$count = 0;
if ($res && $res->num_rows > 0) {
    while ($ev = $res->fetch_assoc()) {
        $count++;
        $out .= renderCard($ev);
    }
}

// ---- Load-more flag ----
$hasMore = $count == $limit;

echo json_encode([
    'html'    => $out,
    'count'   => $count,
    'hasMore' => $hasMore
]);

if (isset($stmt)) $stmt->close();
$conn->close();

function renderCard($ev) {
    $id    = (int)$ev['Event_ID'];
    $img   = trim($ev['event_thumbnail'] ?? '');
    $title = htmlspecialchars($ev['event_title']);
    $cat   = htmlspecialchars($ev['category'] ?: 'General');
    $org   = htmlspecialchars($ev['organizer_name'] ?: 'Organizer');
    $venue = htmlspecialchars($ev['event_venue'] ?: 'TBA');
    $type  = $ev['eventtype'] ?: ((int)$ev['min_team'] > 0 ? 'Team' : 'Individual');
    $price = (int)$ev['event_price'];
    $desc  = htmlspecialchars(mb_strimwidth($ev['event_desc'] ?? '', 0, 110, '…'));

    $dateStr = '';
    if (!empty($ev['startdate'])) {
        $dateStr = date('M j, Y', strtotime($ev['startdate']));
    }
    $timeStr = !empty($ev['time']) ? date('g:i A', strtotime($ev['time'])) : '';

    $priceLabel = $price > 0 ? '₹' . number_format($price) : 'Free';
    $statusTxt  = ($ev['open_closed'] === 'open') ? 'Open' : 'Closed';
    $statusCls  = ($ev['open_closed'] === 'open') ? 'open' : 'closed';

    $imgHtml = '';
    if ($img !== '') {
        $imgHtml = '<img src="images/' . htmlspecialchars($img) . '" alt="' . $title . '" loading="lazy" onerror="this.onerror=null;this.src=\'assets/img/band-playing-on-stage-2747446.jpg\';">';
    } else {
        $imgHtml = '<div class="eh-card-placeholder"><i class="fas fa-calendar-alt"></i></div>';
    }

    return '<article class="eh-card" data-reveal>
      <a href="eventpage.php?id=' . $id . '">
        <div class="eh-card-img">' . $imgHtml . '
          <span class="eh-card-cat">' . $cat . '</span>
          <span class="eh-card-status ' . $statusCls . '">' . $statusTxt . '</span>
        </div>
        <div class="eh-card-body">
          <h3>' . $title . '</h3>
          <div class="eh-card-meta">
            <span><i class="fas fa-user-tie"></i> ' . $org . '</span>
            <span><i class="fas fa-users"></i> ' . htmlspecialchars($type) . '</span>
          </div>
          <div class="eh-card-meta">
            <span><i class="far fa-calendar"></i> ' . $dateStr . '</span>
            ' . ($timeStr !== '' ? '<span><i class="far fa-clock"></i> ' . $timeStr . '</span>' : '') . '
          </div>
          <div class="eh-card-meta"><span><i class="fas fa-map-marker-alt"></i> ' . $venue . '</span></div>
          <p class="eh-card-desc">' . $desc . '</p>
          <div class="eh-card-foot">
            <span class="eh-price">' . $priceLabel . '</span>
            <span class="eh-btn eh-btn-primary eh-btn-sm">Register <i class="fas fa-arrow-right"></i></span>
          </div>
        </div>
      </a>
    </article>';
}
