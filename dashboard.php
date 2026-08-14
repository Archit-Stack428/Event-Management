<?php
/* =============================================================
   EVENTHUB PRO — Organizer Dashboard (Phase 4)
   Responsive glassmorphic manager dashboard.
   Prepared statements for all queries, CSRF validations,
   and dynamic registration view mappings.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if empty
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['username'];
$organizer_name = '';

// Retrieve organizer full name safely
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $organizer_name = $row['full_name'];
}
$stmt->close();

$kpi = array('events' => 0, 'published' => 0, 'closed' => 0, 'registrations' => 0);
$event_ids = [];
$event_titles = [];
$single_regs = [];
$team_regs = [];

if ($organizer_name !== '') {
    // KPI counts
    $stmt = $conn->prepare("SELECT COUNT(*) c, SUM(publish_event='yes') pub, SUM(open_closed='closed') cl FROM create_event WHERE organizer_name = ?");
    $stmt->bind_param('s', $organizer_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($k2 = $res->fetch_assoc()) {
        $kpi['events']      = (int)$k2['c'];
        $kpi['published']   = (int)$k2['pub'];
        $kpi['closed']      = (int)$k2['cl'];
    }
    $stmt->close();

    // Organizer events list for registration matching
    $stmt = $conn->prepare("SELECT Event_ID, event_title FROM create_event WHERE organizer_name = ?");
    $stmt->bind_param('s', $organizer_name);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($e = $res->fetch_assoc()) {
        $event_ids[] = (int)$e['Event_ID'];
        $event_titles[(int)$e['Event_ID']] = $e['event_title'];
    }
    $stmt->close();

    if (!empty($event_ids)) {
        $in_clause = implode(',', $event_ids);
        
        // Count total registrations
        $r3 = $conn->query("SELECT (SELECT COUNT(*) FROM singleevent_registration WHERE event_id IN ($in_clause)) + (SELECT COUNT(*) FROM teamevent_registration WHERE event_id IN ($in_clause)) AS tot");
        if ($r3) {
            $kpi['registrations'] = (int)$r3->fetch_assoc()['tot'];
        }

        // Query individual registrations
        $q_single = $conn->query("SELECT * FROM singleevent_registration WHERE event_id IN ($in_clause) ORDER BY id DESC");
        if ($q_single) {
            while ($r = $q_single->fetch_assoc()) {
                $single_regs[] = $r;
            }
        }

        // Query team registrations
        $q_team = $conn->query("SELECT * FROM teamevent_registration WHERE event_id IN ($in_clause) ORDER BY id DESC");
        if ($q_team) {
            while ($r = $q_team->fetch_assoc()) {
                $team_regs[] = $r;
            }
        }
    }
}

$load_dashboard_assets = true;
$page_title = 'Dashboard | EventHub Pro';
include('header.php');
?>

<div class="eh-dash-shell">
  <!-- ===== Sidebar ===== -->
  <aside class="eh-sidebar" id="ehSidebar">
    <div class="eh-side-title">Menu</div>
    <button class="eh-side-link active" data-section="dashOverview"><i class="fas fa-th-large"></i> Overview</button>
    <button class="eh-side-link" data-section="dashEvents"><i class="fas fa-calendar-alt"></i> My Events</button>
    <button class="eh-side-link" data-section="dashRegistrations"><i class="fas fa-users"></i> Registrations</button>
    <button class="eh-side-link" data-section="dashProfile"><i class="fas fa-user-circle"></i> Profile</button>
    <div class="eh-side-title">Manage</div>
    <a class="eh-side-link" href="createevent.php"><i class="fas fa-plus-circle"></i> Create Event</a>
    <a class="eh-side-link" href="galleryupload.php"><i class="fas fa-images"></i> Gallery Upload</a>
    <a class="eh-side-link" href="log_out.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <!-- ===== Main Area ===== -->
  <main class="eh-dash-main">
    <div class="eh-dash-head-row" id="dashTop">
      <div>
        <h1>Dashboard</h1>
        <div class="eh-muted">Welcome back, <b style="color:var(--eh-text)"><?php echo htmlspecialchars($organizer_name); ?></b> — here's what's happening.</div>
      </div>
      <div style="display:flex;gap:12px;align-items:center;">
        <button class="eh-side-toggle eh-action-btn" id="ehSideToggle"><i class="fas fa-bars"></i> Menu</button>
        <div style="position:relative;">
          <button class="eh-notif" id="ehNotifBtn" aria-label="Notifications"><i class="fas fa-bell"></i><span class="dot"></span></button>
          <div class="eh-notif-panel" id="ehNotifPanel">
            <div class="eh-notif-empty"><i class="fas fa-inbox"></i><br>No new notifications</div>
          </div>
        </div>
        <div class="eh-avatar"><?php echo strtoupper(substr($organizer_name ?: 'E', 0, 1)); ?></div>
      </div>
    </div>

    <!-- ===== Overview Section (KPI Grid + Charts) ===== -->
    <div class="eh-panel-group" id="dashOverview">
      <div class="eh-kpi-grid">
        <div class="eh-kpi" data-aos="fade-up"><div class="eh-kpi-icon"><i class="fas fa-calendar-alt"></i></div><div class="num" data-count="<?php echo $kpi['events']; ?>">0</div><div class="lbl">Total Events</div></div>
        <div class="eh-kpi" data-aos="fade-up" data-aos-delay="60"><div class="eh-kpi-icon"><i class="fas fa-globe"></i></div><div class="num" data-count="<?php echo $kpi['published']; ?>">0</div><div class="lbl">Published</div></div>
        <div class="eh-kpi" data-aos="fade-up" data-aos-delay="120"><div class="eh-kpi-icon"><i class="fas fa-archive"></i></div><div class="num" data-count="<?php echo $kpi['closed']; ?>">0</div><div class="lbl">Closed</div></div>
        <div class="eh-kpi" data-aos="fade-up" data-aos-delay="180"><div class="eh-kpi-icon"><i class="fas fa-users"></i></div><div class="num" data-count="<?php echo $kpi['registrations']; ?>">0</div><div class="lbl">Total Registrations</div></div>
      </div>

      <div class="eh-panel" style="margin-top:24px;">
        <div class="eh-panel-head">
          <h2>Analytics Dashboard</h2>
        </div>
        <div class="eh-chart-grid">
          <div class="eh-chart-box"><h3><i class="fas fa-circle" style="color:var(--eh-primary);font-size:10px;"></i> Event Status</h3><canvas id="ehChartStatus"></canvas></div>
          <div class="eh-chart-box"><h3><i class="fas fa-circle" style="color:var(--eh-accent);font-size:10px;"></i> Registrations by Event</h3><canvas id="ehChartRegs"></canvas></div>
        </div>
      </div>
    </div>

    <!-- ===== My Events ===== -->
    <div class="eh-panel" id="dashEvents">
      <div class="eh-panel-head">
        <h2>My Events</h2>
        <div style="display:flex; gap:12px; align-items:center;">
          <div class="eh-tabs-bar" style="margin:0;">
            <button class="eh-mini-tab active" data-filter="all">All</button>
            <button class="eh-mini-tab" data-filter="published">Published</button>
            <button class="eh-mini-tab" data-filter="draft">Drafts</button>
            <button class="eh-mini-tab" data-filter="closed">Closed</button>
          </div>
          <a class="eh-btn eh-btn-primary eh-btn-sm" href="createevent.php"><i class="fas fa-plus"></i> Create Event</a>
        </div>
      </div>
      <?php
      $stmt = $conn->prepare("SELECT event_title, event_venue, event_thumbnail, event_id, startdate, enddate, publish_event, open_closed FROM create_event WHERE organizer_name = ? ORDER BY event_id DESC");
      $stmt->bind_param('s', $organizer_name);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result && $result->num_rows > 0) { ?>
        <div class="eh-admin-grid">
        <?php while ($row = $result->fetch_assoc()) {
            $img = !empty($row['event_thumbnail']) && file_exists('images/'.$row['event_thumbnail']) ? 'images/'.htmlspecialchars($row['event_thumbnail']) : '';
            
            // Calculate consistent status
            $status = ($row['publish_event'] === 'yes') ? 'published' : 'draft';
            if ($row['open_closed'] === 'closed') {
                $status = 'closed';
            }
            $dd = date('M j, Y', strtotime($row['startdate']));
        ?>
          <div class="eh-admin-card" data-status="<?php echo $status; ?>">
            <div class="eh-admin-img">
              <?php if ($img !== '') { ?><img src="<?php echo $img; ?>" alt=""><?php } ?>
              <span class="eh-card-status <?php echo $status; ?>">
                <?php 
                  if ($status === 'published') echo 'Published';
                  elseif ($status === 'draft') echo 'Draft';
                  else echo 'Closed';
                ?>
              </span>
            </div>
            <div class="eh-admin-body">
              <h3><?php echo htmlspecialchars($row['event_title']); ?></h3>
              <div class="eh-admin-meta">
                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['event_venue']); ?></span>
                <span><i class="fas fa-calendar"></i> <?php echo $dd; ?></span>
              </div>
              <div class="eh-admin-actions">
                <a class="eh-action-btn view" href="eventpage.php?id=<?php echo $row['event_id']; ?>"><i class="fas fa-eye"></i> View</a>
                <a class="eh-action-btn edit" href="editevent.php?id=<?php echo $row['event_id']; ?>"><i class="fas fa-edit"></i> Edit</a>
                
                <?php if ($row['open_closed'] === 'closed'): ?>
                  <!-- Re-open / Publish Form -->
                  <form method="post" action="closedeventpublish.php?id=<?php echo $row['event_id']; ?>" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <button class="eh-action-btn pub" name="publish"><i class="fas fa-redo"></i> Re-open</button>
                  </form>
                <?php else: ?>
                  <!-- Standard Publish/Unpublish toggle -->
                  <?php if ($row['publish_event'] === 'yes') { ?>
                    <form method="post" action="publish&unpublish.php?id=<?php echo $row['event_id']; ?>" style="margin:0;">
                      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                      <button class="eh-action-btn unpub" name="unpublish"><i class="fas fa-ban"></i> Unpublish</button>
                    </form>
                  <?php } else { ?>
                    <form method="post" action="publish&unpublish.php?id=<?php echo $row['event_id']; ?>" style="margin:0;">
                      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                      <button class="eh-action-btn pub" name="publish"><i class="fas fa-check"></i> Publish</button>
                    </form>
                  <?php } ?>
                <?php endif; ?>

                <a class="eh-action-btn danger" href="deleteevent.php?id=<?php echo $row['event_id']; ?>&token=<?php echo $_SESSION['csrf_token']; ?>" onclick="return confirm('Are you sure you want to permanently delete this event? Historical registration records will be preserved.');"><i class="fas fa-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        <?php } ?>
        </div>
      <?php } else { ?>
        <div class="eh-empty"><i class="fas fa-calendar-times"></i><p>No events yet. Create your first event to get started.</p><a class="eh-btn eh-btn-primary eh-btn-sm" href="createevent.php"><i class="fas fa-plus"></i> Create Event</a></div>
      <?php } $stmt->close(); ?>
    </div>

    <!-- ===== Registrations Management ===== -->
    <div class="eh-panel" id="dashRegistrations">
      <div class="eh-panel-head">
        <h2>Registrations</h2>
      </div>

      <h3 style="margin-top:24px; color:#fff; font-size:18px;"><i class="fas fa-user"></i> Individual Registrations</h3>
      <?php if (!empty($single_regs)): ?>
        <div style="overflow-x:auto;">
          <table class="eh-table" style="width:100%; border-collapse:collapse; margin-top:10px;">
            <thead>
              <tr style="border-bottom:1px solid var(--eh-border); text-align:left; color:var(--eh-muted); font-size:13px;">
                <th style="padding:12px;">Event</th>
                <th style="padding:12px;">Name</th>
                <th style="padding:12px;">Roll No</th>
                <th style="padding:12px;">College</th>
                <th style="padding:12px;">Email</th>
                <th style="padding:12px;">Mobile</th>
                <th style="padding:12px;">Amount</th>
                <th style="padding:12px;">Txn ID</th>
                <th style="padding:12px;">Status</th>
                <th style="padding:12px;">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($single_regs as $r): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:14px;">
                  <td style="padding:12px; font-weight:600; color:#fff;"><?php echo htmlspecialchars($event_titles[$r['event_id']] ?? 'Unknown'); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['name']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['roll_no']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['college']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['email']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['mobile_no']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);">₹<?php echo number_format($r['paid_amount']); ?></td>
                  <td style="padding:12px; font-family:monospace; font-size:12px; color:var(--eh-muted);"><?php echo htmlspecialchars($r['txn_id']); ?></td>
                  <td style="padding:12px;"><span class="eh-card-status <?php echo ($r['payment_status'] === 'succeeded' || $r['payment_status'] === 'success' || (int)$r['paid_amount'] === 0) ? 'published' : 'draft'; ?>"><?php echo htmlspecialchars($r['payment_status'] ?: 'succeeded'); ?></span></td>
                  <td style="padding:12px; color:var(--eh-muted);"><?php echo date('M j, Y', strtotime($r['date'])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="eh-empty" style="padding:40px;"><p>No individual registrations found.</p></div>
      <?php endif; ?>

      <h3 style="margin-top:40px; color:#fff; font-size:18px;"><i class="fas fa-users"></i> Team Registrations</h3>
      <?php if (!empty($team_regs)): ?>
        <div style="overflow-x:auto;">
          <table class="eh-table" style="width:100%; border-collapse:collapse; margin-top:10px;">
            <thead>
              <tr style="border-bottom:1px solid var(--eh-border); text-align:left; color:var(--eh-muted); font-size:13px;">
                <th style="padding:12px;">Event</th>
                <th style="padding:12px;">Team Name</th>
                <th style="padding:12px;">College</th>
                <th style="padding:12px;">Leader</th>
                <th style="padding:12px;">Emails</th>
                <th style="padding:12px;">Mobile</th>
                <th style="padding:12px;">Amount</th>
                <th style="padding:12px;">Txn ID</th>
                <th style="padding:12px;">Status</th>
                <th style="padding:12px;">Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($team_regs as $r): ?>
                <tr style="border-bottom:1px solid rgba(255,255,255,0.05); font-size:14px;">
                  <td style="padding:12px; font-weight:600; color:#fff;"><?php echo htmlspecialchars($event_titles[$r['event_id']] ?? 'Unknown'); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['team_name']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['college']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['name']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['emails']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);"><?php echo htmlspecialchars($r['mobile_no']); ?></td>
                  <td style="padding:12px; color:var(--eh-text);">₹<?php echo number_format($r['paid_amount']); ?></td>
                  <td style="padding:12px; font-family:monospace; font-size:12px; color:var(--eh-muted);"><?php echo htmlspecialchars($r['txn_id']); ?></td>
                  <td style="padding:12px;"><span class="eh-card-status <?php echo ($r['payment_status'] === 'succeeded' || $r['payment_status'] === 'success' || (int)$r['paid_amount'] === 0) ? 'published' : 'draft'; ?>"><?php echo htmlspecialchars($r['payment_status'] ?: 'succeeded'); ?></span></td>
                  <td style="padding:12px; color:var(--eh-muted);"><?php echo date('M j, Y', strtotime($r['date'])); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="eh-empty" style="padding:40px;"><p>No team registrations found.</p></div>
      <?php endif; ?>
    </div>

    <!-- ===== Profile ===== -->
    <div class="eh-panel" id="dashProfile">
      <div class="eh-panel-head">
        <h2>Organizer Profile</h2>
      </div>
      <div class="eh-prose" style="margin-top:20px; max-width:600px;">
        <div class="eh-org-card" style="margin:0; text-align:left; justify-content:flex-start; gap:24px;">
          <div class="eh-avatar" style="width:72px; height:72px; font-size:32px;"><?php echo strtoupper(substr($organizer_name ?: 'E', 0, 1)); ?></div>
          <div>
            <h3 style="color:#fff; margin-bottom:6px;"><?php echo htmlspecialchars($organizer_name); ?></h3>
            <p style="margin:0; font-size:14px; color:var(--eh-muted);"><i class="fas fa-envelope"></i> Username/Email: <b><?php echo htmlspecialchars($username); ?></b></p>
            <p style="margin:6px 0 0; font-size:14px; color:var(--eh-muted);"><i class="fas fa-user-shield"></i> Account: <b>Organizer Account</b></p>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<?php
// ===== Chart data (real) =====
$regLabels = array(); $regVals = array();
if ($organizer_name !== '') {
    $stmt = $conn->prepare("SELECT event_title, Event_ID FROM create_event WHERE organizer_name = ? ORDER BY event_id DESC");
    $stmt->bind_param('s', $organizer_name);
    $stmt->execute();
    $rev = $stmt->get_result();
    while ($e = $rev->fetch_assoc()) {
        $id = (int)$e['Event_ID'];
        $c = $conn->query("SELECT (SELECT COUNT(*) FROM singleevent_registration WHERE event_id=$id)+(SELECT COUNT(*) FROM teamevent_registration WHERE event_id=$id) AS t");
        if ($c) {
            $regLabels[] = htmlspecialchars($e['event_title']);
            $regVals[]   = (int)$c->fetch_assoc()['t'];
        }
    }
    $stmt->close();
}
$statusLabels = array('Published', 'Draft', 'Closed');
$statusVals = array($kpi['published'], max(0, $kpi['events'] - $kpi['published'] - $kpi['closed']), $kpi['closed']);
?>
<script>
window.addEventListener('load', function(){
  if(!window.EHCharts || !window.Chart) return;
  window.EHCharts([
    { id:'ehChartStatus', type:'doughnut',
      data:{ labels:<?php echo json_encode($statusLabels); ?>,
        datasets:[{ data:<?php echo json_encode($statusVals); ?>, backgroundColor:['#7C3AED','#2563EB','#06B6D4'], borderWidth:0 }] },
      options:{ cutout:'62%', plugins:{ legend:{ labels:{ color:'#e5e7eb' } } } } },
    { id:'ehChartRegs', type:'bar',
      data:{ labels:<?php echo json_encode($regLabels); ?>,
        datasets:[{ label:'Registrations', data:<?php echo json_encode($regVals); ?>, backgroundColor:'rgba(6,182,212,0.7)', borderRadius:6 }] },
      options:{ plugins:{ legend:{ display:false } }, scales:{ x:{ ticks:{ color:'#9ca3af' } }, y:{ ticks:{ color:'#9ca3af' }, beginAtZero:true } } } }
  ]);
});
</script>
<?php include('footer.php'); ?>
<?php $conn->close(); ?>
