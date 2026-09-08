<?php
/* =============================================================
   EVENTHUB PRO — Event Details (Phase 3)
   Premium single-event page. Real DB data only.
   Registration backend (eventregistration.php / 1) is UNTOUCHED,
   only the modal UI is restyled. Stripe flow preserved.
   ============================================================= */
session_start();
include('dbconnect.php');
$page_title = 'Event Details — EventHub Pro';

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $page_title = 'Event Not Found — EventHub Pro';
    include('header.php');
    ?>
    <section class="eh-section" style="padding-top:160px; min-height:80vh; display:flex; align-items:center; text-align:center;">
      <div class="container-max" style="width:100%;">
        <div style="max-width:500px; margin:0 auto;" data-reveal>
          <div style="font-size:72px; color:var(--eh-accent); margin-bottom:20px;"><i class="fas fa-calendar-times"></i></div>
          <h1 style="font-size:32px; color:#fff; margin-bottom:12px;">Invalid Event ID</h1>
          <p style="color:var(--eh-muted); margin-bottom:30px;">The event identifier provided is invalid or has expired.</p>
          <a href="events.php" class="eh-btn eh-btn-primary"><i class="fas fa-arrow-left"></i> Back to Events</a>
        </div>
      </div>
    </section>
    <?php
    include('footer.php');
    $conn->close();
    exit;
}

// ---- Main event data ----
$sql = "SELECT event_title, organizer_name, event_desc, event_thumbnail, category, eventtype,
               min_team, max_team, event_rules, startdate, enddate, time, event_venue,
               event_price, event_sponsors, event_prizes, publish_event, open_closed
        FROM create_event WHERE Event_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $stmt->close();
    $page_title = 'Event Not Found — EventHub Pro';
    include('header.php');
    ?>
    <section class="eh-section" style="padding-top:160px; min-height:80vh; display:flex; align-items:center; text-align:center;">
      <div class="container-max" style="width:100%;">
        <div style="max-width:500px; margin:0 auto;" data-reveal>
          <div style="font-size:72px; color:var(--eh-accent); margin-bottom:20px;"><i class="fas fa-calendar-times"></i></div>
          <h1 style="font-size:32px; color:#fff; margin-bottom:12px;">Event Not Found</h1>
          <p style="color:var(--eh-muted); margin-bottom:30px;">The event you are looking for does not exist, has been removed, or is not yet published by the organizer.</p>
          <a href="events.php" class="eh-btn eh-btn-primary"><i class="fas fa-arrow-left"></i> Back to Events</a>
        </div>
      </div>
    </section>
    <?php
    include('footer.php');
    $conn->close();
    exit;
}

$ev = $res->fetch_assoc();
$stmt->close();

$event_title = $ev['event_title'];
$page_title  = $event_title . ' — EventHub Pro';
$org         = $ev['organizer_name'] ?: 'EventHub Pro';
$desc        = $ev['event_desc'] ?: '';
$thumb       = trim($ev['event_thumbnail'] ?? '');
$category    = $ev['category'] ?: 'General';
$eventtype   = $ev['eventtype'] ?: ((int)$ev['min_team'] > 0 ? 'Team' : 'Individual');
$min_team    = (int)$ev['min_team'];
$max_team    = (int)$ev['max_team'];
$rulesRaw    = $ev['event_rules'] ?: '';
$startdate   = $ev['startdate'];
$enddate     = $ev['enddate'];
$time        = $ev['time'];
$venue       = $ev['event_venue'] ?: '';
$price       = (int)$ev['event_price'];
$sponsors    = $ev['event_sponsors'] ?: '';
$prizes      = $ev['event_prizes'] ?: '';
$publish_event = $ev['publish_event'] ?: 'no';
$open_closed = $ev['open_closed'] ?: 'open';

// Determine event timeline state
$today = date('Y-m-d');
$event_state = 'upcoming'; // default
if (!empty($enddate) && $today > $enddate) {
    $event_state = 'ended';
} elseif (!empty($startdate) && $today >= $startdate) {
    $event_state = 'live';
}

// Rules parser (splits on comma or newline)
$rulesList = [];
if ($rulesRaw !== '') {
    $rulesList = preg_split('/[,\n\r]+/', $rulesRaw);
    $rulesList = array_map('trim', $rulesList);
    $rulesList = array_filter($rulesList);
}

// Prizes & sponsors parsers
$prizeList = array_filter(array_map('trim', explode(",", $prizes)));
$sponsorList = array_filter(array_map('trim', explode(",", $sponsors)));

// ---- Related events ----
$related = [];
$r_sql = "SELECT Event_ID, event_title, event_thumbnail, startdate, event_price, category
          FROM create_event WHERE publish_event='yes' AND Event_ID != ? LIMIT 6";
$r_stmt = $conn->prepare($r_sql);
$r_stmt->bind_param('i', $id);
$r_stmt->execute();
$r_res = $r_stmt->get_result();
if ($r_res) { while ($r = $r_res->fetch_assoc()) { $related[] = $r; } }
$r_stmt->close();

// ---- Registration counts ----
$reg_count = 0;
$rc_sql = "SELECT (SELECT COUNT(*) FROM singleevent_registration WHERE event_id = ?) + (SELECT COUNT(*) FROM teamevent_registration WHERE event_id = ?) AS c";
$rc_stmt = $conn->prepare($rc_sql);
$rc_stmt->bind_param('ii', $id, $id);
$rc_stmt->execute();
$rc_res = $rc_stmt->get_result();
if ($rc_res && $rr = $rc_res->fetch_assoc()) { $reg_count = (int)$rr['c']; }
$rc_stmt->close();

$priceLabel = $price > 0 ? '₹' . number_format($price) : 'Free';

// Determine CTA Status
$can_register = ($publish_event === 'yes' && $open_closed === 'open' && $event_state !== 'ended');
$register_btn_text = '';
if ($publish_event !== 'yes') {
    $register_btn_text = 'Event Unpublished';
} elseif ($event_state === 'ended') {
    $register_btn_text = 'Event Completed';
} elseif ($open_closed !== 'open') {
    $register_btn_text = 'Registration Closed';
} else {
    $register_btn_text = ($price > 0) ? 'Register & Pay' : 'Register Free';
}

$statusLabel = ($open_closed === 'open') ? 'Open Registration' : 'Registration Closed';
if ($event_state === 'ended') {
    $statusLabel = 'Event Completed';
} elseif ($publish_event !== 'yes') {
    $statusLabel = 'Event Unpublished';
}

// Date formatting
$dateLabel = '';
if (!empty($startdate)) {
    $dateLabel = date('M j, Y', strtotime($startdate));
    if (!empty($enddate) && $enddate != $startdate) {
        $dateLabel .= ' — ' . date('M j, Y', strtotime($enddate));
    }
}
$timeLabel = !empty($time) ? date('g:i A', strtotime($time)) : '';

// Share URL
$shareUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$shareTitle = urlencode($event_title);
$shareEnc = urlencode($shareUrl);
?>
<?php include('header.php'); ?>

<!-- ===== Event Hero ===== -->
<section class="eh-event-hero">
  <div class="eh-event-cover">
    <?php if ($thumb !== ''): ?>
      <img src="images/<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($event_title); ?>" onerror="this.onerror=null;this.src='assets/img/band-playing-on-stage-2747446.jpg';">
    <?php else: ?>
      <div class="eh-card-placeholder" style="height:100%;"><i class="fas fa-calendar-alt"></i></div>
    <?php endif; ?>
    <div class="eh-event-cover-shade"></div>
  </div>

  <div class="container-max eh-event-hero-inner">
    <div class="eh-event-breadcrumb">
      <a href="events.php" style="color:var(--eh-accent); font-weight:600;"><i class="fas fa-arrow-left"></i> Back to Events</a> 
      <span style="margin: 0 10px; opacity: 0.3;">|</span> 
      <a href="events.php">Events</a> <i class="fas fa-chevron-right"></i> <span><?php echo htmlspecialchars($category); ?></span>
    </div>

    <div class="eh-event-header">
      <div class="eh-event-head-main">
        <span class="eh-card-cat"><?php echo htmlspecialchars($category); ?></span>
        <h1><?php echo htmlspecialchars($event_title); ?></h1>
        <p class="eh-event-org"><i class="fas fa-user-tie"></i> Organized by <b><?php echo htmlspecialchars($org); ?></b></p>
        <div class="eh-event-quick">
          <span><i class="far fa-calendar"></i> <?php echo $dateLabel; ?></span>
          <?php if ($timeLabel !== ''): ?><span><i class="far fa-clock"></i> <?php echo $timeLabel; ?></span><?php endif; ?>
          <?php if ($venue !== ''): ?><span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($venue); ?></span><?php endif; ?>
          <span><i class="fas fa-users"></i> <?php echo htmlspecialchars($eventtype); ?></span>
        </div>
        <div class="eh-event-share">
          <span>Share:</span>
          <a href="https://wa.me/?text=<?php echo $shareTitle . '%20' . $shareEnc; ?>" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $shareEnc; ?>" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fab fa-facebook"></i></a>
          <a href="https://twitter.com/intent/tweet?url=<?php echo $shareEnc; ?>&text=<?php echo $shareTitle; ?>" target="_blank" rel="noopener" aria-label="Share on X"><i class="fab fa-twitter"></i></a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $shareEnc; ?>" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fab fa-linkedin"></i></a>
          <button id="ehCopyLink" aria-label="Copy link"><i class="fas fa-link"></i></button>
        </div>
      </div>

      <!-- Sticky register card -->
      <div class="eh-register-card" data-reveal>
        <div class="eh-register-price">
          <span class="eh-price"><?php echo $priceLabel; ?></span>
          <?php if ($price > 0): ?><span class="eh-reg-currency">per registration</span><?php endif; ?>
        </div>
        <div class="eh-reg-status <?php echo $can_register ? 'open' : 'closed'; ?>">
          <i class="fas fa-circle"></i> <?php echo htmlspecialchars($statusLabel); ?>
        </div>
        <?php if ($max_team > 0): ?>
          <div class="eh-reg-team">Team size: <?php echo $min_team; ?><?php echo $max_team > $min_team ? '–' . $max_team : ''; ?></div>
        <?php endif; ?>
        <div class="eh-reg-progress">
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--eh-muted); margin-bottom: 4px;"><span>Registered</span><span><?php echo $reg_count; ?></span></div>
          <div class="eh-progress"><span style="width:<?php echo min(100, $reg_count); ?>%;"></span></div>
        </div>
        <button class="eh-btn eh-btn-primary eh-register-btn" <?php echo !$can_register ? 'disabled style="opacity:.5;cursor:not-allowed;"' : ''; ?> data-toggle="modal" data-target="<?php echo $can_register ? '#registerModal' : ''; ?>">
          <i class="fas fa-ticket-alt"></i> <?php echo htmlspecialchars($register_btn_text); ?>
        </button>
        <p class="eh-reg-note"><i class="fas fa-shield-alt"></i> Secure transaction processing</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== Body: tabs ===== -->
<section class="eh-section" style="padding-top:0;">
  <div class="container-max">
    
    <!-- Countdown strip -->
    <div class="eh-countdown" id="ehCountdown" data-start="<?php echo htmlspecialchars($startdate); ?>" data-state="<?php echo $event_state; ?>">
      <div class="eh-cd-label">
        <i class="fas fa-hourglass-half"></i> 
        <span id="ehCdStatusText">
          <?php 
            if ($event_state === 'ended') {
                echo 'Event Completed';
            } elseif ($event_state === 'live') {
                echo 'Event is Live';
            } else {
                echo 'Time until the event';
            }
          ?>
        </span>
      </div>
      <div class="eh-cd-cells" style="<?php echo ($event_state !== 'upcoming') ? 'display:none;' : ''; ?>">
        <div class="eh-cd-cell"><div class="eh-cd-num" id="ehCdDays">--</div><div class="eh-cd-lbl">Days</div></div>
        <div class="eh-cd-cell"><div class="eh-cd-num" id="ehCdHours">--</div><div class="eh-cd-lbl">Hours</div></div>
        <div class="eh-cd-cell"><div class="eh-cd-num" id="ehCdMins">--</div><div class="eh-cd-lbl">Minutes</div></div>
        <div class="eh-cd-cell"><div class="eh-cd-num" id="ehCdSecs">--</div><div class="eh-cd-lbl">Seconds</div></div>
      </div>
    </div>

    <div class="eh-tabs" data-reveal>
      <div class="eh-tab-nav">
        <button class="eh-tab active" data-tab="overview">Overview</button>
        <?php if (!empty($rulesList)): ?>
          <button class="eh-tab" data-tab="rules">Rules</button>
        <?php endif; ?>
        <button class="eh-tab" data-tab="schedule">Schedule</button>
        <?php if (!empty($prizeList)): ?>
          <button class="eh-tab" data-tab="prizes">Prizes</button>
        <?php endif; ?>
        <?php if (!empty($sponsorList)): ?>
          <button class="eh-tab" data-tab="sponsors">Sponsors</button>
        <?php endif; ?>
        <?php if ($venue !== ''): ?>
          <button class="eh-tab" data-tab="map">Location</button>
        <?php endif; ?>
      </div>

      <div class="eh-tab-panel active" id="tab-overview">
        <h2>About this event</h2>
        <div class="eh-prose"><?php echo nl2br(htmlspecialchars($desc ?: 'No description provided yet.')); ?></div>
        <div class="eh-event-facts">
          <div class="eh-fact"><i class="fas fa-tag"></i><div><div class="eh-fact-lbl">Category</div><div class="eh-fact-val"><?php echo htmlspecialchars($category); ?></div></div></div>
          <div class="eh-fact"><i class="fas fa-users"></i><div><div class="eh-fact-lbl">Type</div><div class="eh-fact-val"><?php echo htmlspecialchars($eventtype); ?></div></div></div>
          <?php if ($max_team > 0): ?>
            <div class="eh-fact"><i class="fas fa-user-friends"></i><div><div class="eh-fact-lbl">Team Size</div><div class="eh-fact-val"><?php echo $min_team . ($max_team > $min_team ? '–' . $max_team : ' members'); ?></div></div></div>
          <?php endif; ?>
          <div class="eh-fact"><i class="fas fa-ticket-alt"></i><div><div class="eh-fact-lbl">Price</div><div class="eh-fact-val"><?php echo $priceLabel; ?></div></div></div>
        </div>
      </div>

      <?php if (!empty($rulesList)): ?>
      <div class="eh-tab-panel" id="tab-rules">
        <h2>Rules & Guidelines</h2>
        <ol class="eh-rules">
          <?php foreach ($rulesList as $rule): ?>
            <li><?php echo htmlspecialchars($rule); ?></li>
          <?php endforeach; ?>
        </ol>
      </div>
      <?php endif; ?>

      <div class="eh-tab-panel" id="tab-schedule">
        <h2>Schedule</h2>
        <div class="eh-schedule-item">
          <div class="eh-sched-date"><div class="eh-sched-dd"><?php echo !empty($startdate) ? date('d', strtotime($startdate)) : '--'; ?></div><div class="eh-sched-mm"><?php echo !empty($startdate) ? date('M', strtotime($startdate)) : ''; ?></div></div>
          <div class="eh-sched-body">
            <h4><?php echo htmlspecialchars($event_title); ?></h4>
            <p><i class="far fa-clock"></i> <?php echo $timeLabel !== '' ? $timeLabel : 'All day'; ?></p>
            <?php if ($venue !== ''): ?><p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($venue); ?></p><?php endif; ?>
          </div>
        </div>
        <?php if (!empty($enddate) && $enddate != $startdate): ?>
          <div class="eh-schedule-item">
            <div class="eh-sched-date"><div class="eh-sched-dd"><?php echo date('d', strtotime($enddate)); ?></div><div class="eh-sched-mm"><?php echo date('M', strtotime($enddate)); ?></div></div>
            <div class="eh-sched-body">
              <h4>Event Ends</h4>
              <p><i class="fas fa-flag-checkered"></i> <?php echo date('M j, Y', strtotime($enddate)); ?></p>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php if (!empty($prizeList)): ?>
      <div class="eh-tab-panel" id="tab-prizes">
        <h2>Prizes & Rewards</h2>
        <ul class="eh-prize-list">
          <?php foreach ($prizeList as $idx => $prize): ?>
            <li><span class="eh-prize-rank"><?php echo ['1st', '2nd', '3rd'][$idx] ?? ($idx + 1) . 'th'; ?></span> <span><?php echo htmlspecialchars($prize); ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if (!empty($sponsorList)): ?>
      <div class="eh-tab-panel" id="tab-sponsors">
        <h2>Sponsors & Partners</h2>
        <div class="eh-sponsor-grid">
          <?php foreach ($sponsorList as $sp): ?>
            <div class="eh-sponsor"><i class="fas fa-handshake"></i> <?php echo htmlspecialchars($sp); ?></div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($venue !== ''): ?>
      <div class="eh-tab-panel" id="tab-map">
        <h2>Event Location</h2>
        <div class="eh-map">
          <iframe title="Event venue map" loading="lazy" src="https://www.google.com/maps?q=<?php echo urlencode($venue); ?>&output=embed" width="100%" height="420" style="border:0;border-radius:16px;" allowfullscreen></iframe>
        </div>
        <p style="color:var(--eh-muted);margin-top:14px;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($venue); ?></p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== Organizer ===== -->
<section class="eh-section" style="padding-top:0;">
  <div class="container-max">
    <div class="eh-org-card" data-reveal>
      <div class="eh-avatar" style="width:64px;height:64px;font-size:26px;"><?php echo strtoupper(substr(trim($org), 0, 1)); ?></div>
      <div>
        <h3><?php echo htmlspecialchars($org); ?></h3>
        <p>This event is organized by <?php echo htmlspecialchars($org); ?>. Contact the organizer for any specific questions about the event.</p>
      </div>
      <a href="contact.php" class="eh-btn eh-btn-ghost"><i class="fas fa-envelope"></i> Contact Organizer</a>
    </div>
  </div>
</section>

<!-- ===== Related events ===== -->
<?php if (!empty($related)): ?>
<section class="eh-section" id="related">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">More Events</span>
      <h2>You might also like</h2>
    </div>
    <div class="eh-events">
      <?php foreach ($related as $i => $r): ?>
        <div class="eh-card" data-aos="fade-up" data-aos-delay="<?php echo ($i % 3) * 80; ?>">
          <a href="eventpage.php?id=<?php echo (int)$r['Event_ID']; ?>">
            <div class="eh-card-img">
              <?php if (trim($r['event_thumbnail'] ?? '') !== ''): ?>
                <img src="images/<?php echo htmlspecialchars($r['event_thumbnail']); ?>" alt="<?php echo htmlspecialchars($r['event_title']); ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/img/band-playing-on-stage-2747446.jpg';">
              <?php else: ?>
                <div class="eh-card-placeholder"><i class="fas fa-calendar-alt"></i></div>
              <?php endif; ?>
              <span class="eh-card-cat"><?php echo htmlspecialchars($r['category']); ?></span>
              <span class="eh-card-date"><i class="far fa-calendar"></i> <?php echo date('d M Y', strtotime($r['startdate'])); ?></span>
            </div>
            <div class="eh-card-body">
              <h3><?php echo htmlspecialchars($r['event_title']); ?></h3>
              <div class="eh-card-foot">
                <?php $rp = (int)$r['event_price']; ?>
                <span class="eh-price"><?php echo $rp > 0 ? '₹' . number_format($rp) : 'Free'; ?></span>
                <span class="eh-btn eh-btn-primary eh-btn-sm">View</span>
              </div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== Registration Modal ===== -->
<div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content eh-modal">
      <div class="modal-header eh-modal-head">
        <h5 class="modal-title" id="registerModalLabel"><i class="fas fa-ticket-alt"></i> Register — <?php echo htmlspecialchars($event_title); ?></h5>
        <button type="button" class="close eh-modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body eh-modal-body">
        <?php if ($price != 0): ?>
          <form method="post" id="paymentFrm" class="eh-reg-form" onsubmit="openUpiQrPaymentModal(event);">
        <?php else: ?>
          <form action="eventregistration1.php?id1=<?php echo $id; ?>" method="post" enctype="multipart/form-data" class="eh-reg-form">
        <?php endif; ?>

        <div class="eh-pay-display">
          <span class="eh-price"><?php echo $priceLabel; ?></span>
          <span class="eh-reg-currency"><?php echo $price > 0 ? 'Secure UPI Payment' : 'No payment required'; ?></span>
        </div>

        <div id="registerform" class="eh-reg-fields"></div>
        <div id="teamdetails3" class="eh-reg-fields"></div>
        <div id="teamdetails" class="eh-reg-fields"></div>
        <div id="teamdetails1" class="eh-reg-fields"></div>
        <div id="payment" class="eh-payment-fields">
          <?php if ($price > 0): ?>
            <div class="eh-upi-card" onclick="openUpiQrPaymentModal(event);" role="button" tabindex="0" style="background:rgba(124,58,237,0.1); border:1px solid rgba(124,58,237,0.3); border-radius:12px; padding:14px 16px; margin:14px 0; cursor:pointer;">
              <div style="display:flex; align-items:center; gap:12px;">
                <div style="font-size:26px; color:var(--eh-accent);"><i class="fas fa-qrcode"></i></div>
                <div>
                  <div style="font-weight:700; color:#fff; font-size:15px;">Instant UPI Payment</div>
                  <div style="font-size:13px; color:var(--eh-muted); margin-top:2px;">Pay securely using any UPI app</div>
                </div>
              </div>
              <div style="font-size:12px; color:var(--eh-muted); margin-top:8px; padding-top:8px; border-top:1px solid rgba(255,255,255,0.08);">
                Google Pay • PhonePe • Paytm • BHIM • Other UPI Apps
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="eh-modal-foot">
          <?php if ($price != 0): ?>
            <button class="eh-btn eh-btn-primary" type="button" id="viewUpiQrBtn" onclick="openUpiQrPaymentModal(event);" style="width:100%;justify-content:center;">
              <i class="fas fa-qrcode"></i> View UPI QR &amp; Pay ₹<?php echo number_format($price); ?>
            </button>
            <div id="regValidationMsg" style="margin-top:12px; font-size:13px; text-align:center; display:none;"></div>
          <?php else: ?>
            <button class="eh-btn eh-btn-primary" type="submit" name="submit" style="width:100%;justify-content:center;">
              <i class="fas fa-check"></i> Confirm Registration
            </button>
          <?php endif; ?>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===== Centered UPI QR Payment Modal ===== -->
<div class="eh-upi-modal-backdrop" id="upiQrModal" role="dialog" aria-modal="true" aria-labelledby="upiModalTitle" style="display:none;">
  <div class="eh-upi-modal-dialog">
    <div class="eh-upi-modal-content">
      <button type="button" class="eh-upi-modal-close" onclick="closeUpiQrModal();" aria-label="Close">&times;</button>
      
      <!-- Modal Header -->
      <div class="eh-upi-modal-head">
        <div class="eh-upi-tag"><i class="fas fa-shield-alt"></i> UPI PAYMENT</div>
        <h3 id="upiModalTitle"><?php echo htmlspecialchars($event_title); ?></h3>
        <div class="eh-upi-amount">₹<?php echo number_format($price); ?></div>
      </div>

      <!-- Step 1: Scan & Pay -->
      <div id="upiStep1" class="eh-upi-step">
        <div class="eh-qr-frame">
          <img src="assets/images/upi-qr.jpg" alt="UPI Payment QR Code" class="eh-qr-image" onerror="this.src='assets/images/upi-qr.jpg';">
        </div>

        <div class="eh-qr-scan-text">
          <span>Scan this QR code using</span>
          <strong>Google Pay / PhonePe / Paytm / BHIM</strong>
        </div>

        <div class="eh-upi-id-pill">
          <span class="lbl">UPI ID:</span>
          <code id="upiIdVal">9580197216@nyes</code>
          <button type="button" class="eh-copy-btn" id="copyUpiBtn" onclick="copyUpiId();" title="Copy UPI ID">
            <i class="far fa-copy"></i> <span id="copyTxt">Copy</span>
          </button>
        </div>

        <div class="eh-upi-foot-btns">
          <button type="button" class="eh-btn eh-btn-primary" id="btnCompletedPayment" onclick="showUtrStep();" style="width:100%; justify-content:center;">
            <i class="fas fa-check-circle"></i> I Have Completed Payment
          </button>
          <button type="button" class="eh-btn eh-btn-ghost" onclick="closeUpiQrModal();" style="width:100%; justify-content:center; margin-top:8px;">
            Close
          </button>
        </div>
      </div>

      <!-- Step 2: UTR Submission -->
      <div id="upiStep2" class="eh-upi-step" style="display:none;">
        <div class="eh-utr-banner">
          <i class="fas fa-info-circle"></i>
          <div>Your payment confirmation will be verified before the registration is confirmed.</div>
        </div>

        <div class="eh-utr-group">
          <label for="upiUtrField">UPI Transaction ID / UTR <span style="color:#ef4444;">*</span></label>
          <input type="text" 
                 id="upiUtrField" 
                 name="utr_number" 
                 class="eh-utr-input" 
                 placeholder="Enter 12-digit UPI Ref / UTR / Txn ID" 
                 maxlength="50" 
                 autocomplete="off" 
                 spellcheck="false" 
                 tabindex="0"
                 onclick="event.stopPropagation(); this.focus();"
                 onkeydown="event.stopPropagation();"
                 onkeyup="event.stopPropagation();"
                 onkeypress="event.stopPropagation();"
                 oninput="event.stopPropagation();"
                 onpaste="event.stopPropagation();">
          <small>Find this 12-digit number in your UPI app payment receipt (PhonePe / GPay / Paytm / BHIM).</small>
        </div>

        <div id="utrErrMsg" style="display:none; color:#ef4444; font-size:13px; margin:8px 0; text-align:center;"></div>

        <div class="eh-upi-foot-btns">
          <button type="button" class="eh-btn eh-btn-primary" id="btnSubmitUtr" onclick="submitUpiRegistration();" style="width:100%; justify-content:center;">
            <i class="fas fa-paper-plane"></i> Submit Registration (Pending Verification)
          </button>
          <button type="button" class="eh-btn eh-btn-ghost" onclick="backToQrStep();" style="width:100%; justify-content:center; margin-top:8px;">
            &larr; Back to QR Code
          </button>
        </div>
      </div>

    </div>
  </div>
</div>

<style>
.eh-upi-modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(8, 10, 20, 0.85);
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  z-index: 10600;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  overflow-y: auto;
  opacity: 0;
  transition: opacity 0.25s ease;
}
.eh-upi-modal-backdrop.eh-show {
  opacity: 1;
}
.eh-upi-modal-dialog {
  max-width: 440px;
  width: 100%;
  margin: auto;
}
.eh-upi-modal-content {
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 20px;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.7), 0 0 40px rgba(124, 58, 237, 0.15);
  padding: 24px;
  position: relative;
  text-align: center;
  color: #fff;
}
.eh-upi-modal-close {
  position: absolute;
  top: 14px;
  right: 14px;
  background: rgba(255, 255, 255, 0.08);
  border: none;
  color: #94a3b8;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  font-size: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}
.eh-upi-modal-close:hover {
  background: rgba(255, 255, 255, 0.18);
  color: #fff;
}
.eh-upi-modal-head {
  margin-bottom: 16px;
}
.eh-upi-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(124, 58, 237, 0.15);
  color: var(--eh-accent, #7c3aed);
  border: 1px solid rgba(124, 58, 237, 0.3);
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.eh-upi-modal-head h3 {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0 0 6px 0;
  line-height: 1.3;
}
.eh-upi-amount {
  font-size: 28px;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}
.eh-qr-frame {
  background: #ffffff;
  padding: 14px;
  border-radius: 16px;
  display: inline-block;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  margin: 8px auto 14px auto;
  max-width: 310px;
  width: 100%;
}
.eh-qr-image {
  display: block;
  width: 100%;
  height: auto;
  aspect-ratio: 1/1;
  object-fit: contain;
  border-radius: 6px;
}
.eh-qr-scan-text {
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.4;
  margin-bottom: 12px;
}
.eh-qr-scan-text strong {
  display: block;
  color: #e2e8f0;
  margin-top: 2px;
  font-size: 13px;
}
.eh-upi-id-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 6px 12px;
  border-radius: 10px;
  font-size: 13px;
  margin-bottom: 18px;
  max-width: 100%;
  flex-wrap: wrap;
  justify-content: center;
}
.eh-upi-id-pill .lbl {
  color: #94a3b8;
  font-weight: 500;
}
.eh-upi-id-pill code {
  color: #a78bfa;
  font-weight: 600;
  font-family: monospace;
}
.eh-copy-btn {
  background: rgba(124, 58, 237, 0.2);
  border: 1px solid rgba(124, 58, 237, 0.4);
  color: #fff;
  border-radius: 6px;
  padding: 3px 8px;
  font-size: 11px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.eh-copy-btn:hover {
  background: var(--eh-accent, #7c3aed);
}
.eh-utr-banner {
  background: rgba(59, 130, 246, 0.12);
  border: 1px solid rgba(59, 130, 246, 0.3);
  color: #93c5fd;
  border-radius: 12px;
  padding: 12px 14px;
  font-size: 13px;
  text-align: left;
  display: flex;
  gap: 10px;
  align-items: flex-start;
  margin-bottom: 16px;
  line-height: 1.4;
}
.eh-utr-group {
  text-align: left;
  margin-bottom: 16px;
  position: relative;
  z-index: 10610;
}
.eh-utr-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #e2e8f0;
  margin-bottom: 6px;
}
.eh-utr-group input,
.eh-utr-input {
  width: 100% !important;
  background: rgba(255, 255, 255, 0.08) !important;
  border: 1.5px solid rgba(255, 255, 255, 0.25) !important;
  border-radius: 10px !important;
  padding: 12px 14px !important;
  color: #ffffff !important;
  font-size: 15px !important;
  outline: none !important;
  transition: border-color 0.2s, box-shadow 0.2s !important;
  font-family: monospace !important;
  pointer-events: auto !important;
  user-select: text !important;
  -webkit-user-select: text !important;
  cursor: text !important;
  position: relative !important;
  z-index: 10620 !important;
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
}
.eh-utr-group input:focus,
.eh-utr-input:focus {
  border-color: #a78bfa !important;
  background: rgba(255, 255, 255, 0.14) !important;
  box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.35) !important;
  color: #ffffff !important;
}
.eh-utr-group small {
  display: block;
  font-size: 11px;
  color: #94a3b8;
  margin-top: 6px;
  line-height: 1.3;
}
@media (max-width: 480px) {
  .eh-qr-frame {
    max-width: calc(100vw - 70px);
    padding: 10px;
  }
  .eh-upi-modal-content {
    padding: 18px 14px;
  }
}
</style>

<script>
window.EH_EVENT = {
  minTeam: <?php echo $min_team; ?>,
  maxTeam: <?php echo $max_team; ?>,
  price: <?php echo $price; ?>,
  startDate: '<?php echo htmlspecialchars($startdate); ?>'
};

function validateRegistrationForm() {
  var form = document.getElementById('paymentFrm');
  var msgEl = document.getElementById('regValidationMsg');
  if (!form) return true;

  var inputs = form.querySelectorAll('input:not([type="hidden"]), select');
  for (var i = 0; i < inputs.length; i++) {
    var inp = inputs[i];
    if (inp.name === 'dept_name' && inp.value === 'None') {
      showRegValidation('Please select your department.', inp);
      return false;
    }
    if (inp.name && (inp.name === 'name' || inp.name === 'email' || inp.name === 'mobile' || inp.name === 'teamname') && !inp.value.trim()) {
      showRegValidation('Please fill in your ' + (inp.placeholder || inp.name) + '.', inp);
      return false;
    }
  }
  if (msgEl) msgEl.style.display = 'none';
  return true;
}

function showRegValidation(msg, focusEl) {
  var msgEl = document.getElementById('regValidationMsg');
  if (msgEl) {
    msgEl.style.display = 'block';
    msgEl.style.color = '#ef4444';
    msgEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg;
  }
  if (focusEl && typeof focusEl.focus === 'function') {
    focusEl.focus();
  }
}

// Disable Bootstrap modal focus stealing so inputs in secondary/UPI modal can be focused and typed into
function disableBootstrapFocusEnforcement() {
  if (window.jQuery) {
    try {
      $(document).off('focusin.bs.modal');
      if ($.fn && $.fn.modal && $.fn.modal.Constructor) {
        $.fn.modal.Constructor.prototype._enforceFocus = function() {};
        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
      }
    } catch(err) {}
  }
}
disableBootstrapFocusEnforcement();
document.addEventListener('DOMContentLoaded', disableBootstrapFocusEnforcement);
window.addEventListener('load', disableBootstrapFocusEnforcement);

function openUpiQrPaymentModal(e) {
  if (e && typeof e.preventDefault === 'function') e.preventDefault();
  if (!validateRegistrationForm()) {
    return false;
  }

  disableBootstrapFocusEnforcement();
  if (window.jQuery && typeof $('#registerModal').modal === 'function') {
    $('#registerModal').modal('hide');
  }

  var modal = document.getElementById('upiQrModal');
  if (modal) {
    modal.style.display = 'flex';
    setTimeout(function() {
      modal.classList.add('eh-show');
    }, 10);
    backToQrStep();
  }
  return false;
}

function closeUpiQrModal() {
  var modal = document.getElementById('upiQrModal');
  if (modal) {
    modal.classList.remove('eh-show');
    setTimeout(function() {
      modal.style.display = 'none';
      if (window.jQuery && typeof $('#registerModal').modal === 'function') {
        $('#registerModal').modal('show');
      }
    }, 200);
  }
}

function copyUpiId() {
  var el = document.getElementById('upiIdVal');
  if (!el) return;
  var txt = el.innerText.trim();
  navigator.clipboard.writeText(txt).then(function() {
    var cBtn = document.getElementById('copyTxt');
    if (cBtn) {
      cBtn.innerHTML = 'Copied!';
      setTimeout(function() { cBtn.innerHTML = 'Copy'; }, 2000);
    }
  });
}

function showUtrStep() {
  var s1 = document.getElementById('upiStep1');
  var s2 = document.getElementById('upiStep2');
  if (s1 && s2) {
    s1.style.display = 'none';
    s2.style.display = 'block';
    disableBootstrapFocusEnforcement();
    var inp = document.getElementById('upiUtrField');
    if (inp) {
      inp.removeAttribute('disabled');
      inp.removeAttribute('readonly');
      inp.removeAttribute('aria-disabled');
      setTimeout(function() {
        disableBootstrapFocusEnforcement();
        inp.focus();
        inp.select();
      }, 50);
    }
  }
}

function backToQrStep() {
  var s1 = document.getElementById('upiStep1');
  var s2 = document.getElementById('upiStep2');
  var err = document.getElementById('utrErrMsg');
  if (s1 && s2) {
    s2.style.display = 'none';
    s1.style.display = 'block';
  }
  if (err) err.style.display = 'none';
}

function submitUpiRegistration() {
  var utrInput = document.getElementById('upiUtrField');
  var utrVal = utrInput ? utrInput.value.trim() : '';
  var errEl = document.getElementById('utrErrMsg');
  var submitBtn = document.getElementById('btnSubmitUtr');

  if (!utrVal || utrVal.length < 6) {
    if (errEl) {
      errEl.style.display = 'block';
      errEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Please enter a valid UPI Reference / UTR Number (at least 6 characters).';
    }
    if (utrInput) utrInput.focus();
    return;
  }

  var form = document.getElementById('paymentFrm');
  var formData = new FormData(form);
  formData.append('event_id', '<?php echo $id; ?>');
  formData.append('utr_number', utrVal);

  if (submitBtn) {
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
  }
  if (errEl) errEl.style.display = 'none';

  fetch('submit_upi_registration.php', {
    method: 'POST',
    body: formData
  })
  .then(function(res) {
    return res.json().then(function(data) {
      return { status: res.status, data: data };
    });
  })
  .then(function(result) {
    if (result.status === 200 && result.data.success) {
      if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Submitted!';
      }
      window.location.href = result.data.redirect_url;
    } else {
      throw new Error(result.data.error || 'Failed to submit registration.');
    }
  })
  .catch(function(err) {
    if (submitBtn) {
      submitBtn.disabled = false;
      submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Registration (Pending Verification)';
    }
    if (errEl) {
      errEl.style.display = 'block';
      errEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + err.message;
    }
  });
}

document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeUpiQrModal();
  }
});
document.addEventListener('click', function(e) {
  var modal = document.getElementById('upiQrModal');
  if (modal && e.target === modal) {
    closeUpiQrModal();
  }
});
</script>

<?php include('footer.php'); ?>
<script>
// Post-Bootstrap initialization focus override
if (window.jQuery) {
  try {
    $(document).off('focusin.bs.modal');
    if ($.fn && $.fn.modal && $.fn.modal.Constructor) {
      $.fn.modal.Constructor.prototype._enforceFocus = function() {};
      $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    }
  } catch(e) {}
}
</script>
<?php $conn->close(); ?>
