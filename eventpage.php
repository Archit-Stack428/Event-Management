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
          <form action="eventregistration.php?id1=<?php echo $id; ?>" method="post" enctype="multipart/form-data" id="paymentFrm" class="eh-reg-form">
        <?php else: ?>
          <form action="eventregistration1.php?id1=<?php echo $id; ?>" method="post" enctype="multipart/form-data" class="eh-reg-form">
        <?php endif; ?>

        <div class="eh-pay-display">
          <span class="eh-price"><?php echo $priceLabel; ?></span>
          <span class="eh-reg-currency"><?php echo $price > 0 ? 'Secure Stripe payment' : 'No payment required'; ?></span>
        </div>

        <div id="registerform" class="eh-reg-fields"></div>
        <div id="teamdetails3" class="eh-reg-fields"></div>
        <div id="teamdetails" class="eh-reg-fields"></div>
        <div id="teamdetails1" class="eh-reg-fields"></div>
        <div id="payment" class="eh-payment-fields"></div>

        <div class="eh-modal-foot">
          <?php if ($price != 0): ?>
            <button class="eh-btn eh-btn-primary" type="submit" id="payBtn" style="width:100%;justify-content:center;"><i class="fas fa-lock"></i> Register &amp; Pay ₹<?php echo $price; ?></button>
          <?php else: ?>
            <button class="eh-btn eh-btn-primary" type="submit" name="submit" style="width:100%;justify-content:center;"><i class="fas fa-check"></i> Confirm Registration</button>
          <?php endif; ?>
        </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
window.EH_EVENT = {
  minTeam: <?php echo $min_team; ?>,
  maxTeam: <?php echo $max_team; ?>,
  price: <?php echo $price; ?>,
  startDate: '<?php echo htmlspecialchars($startdate); ?>'
};
</script>

<?php include('footer.php'); ?>
<?php $conn->close(); ?>

<!-- Stripe -->
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
Stripe.setPublishableKey('pk_test_YHJ6iSHoBEdcBTWPs0bvcRDp000qmWpWPo');
function stripeResponseHandler(status, response) {
  if (response.error) {
    $('#payBtn').removeAttr('disabled').html('<i class="fas fa-lock"></i> Register & Pay ₹<?php echo $price; ?>');
    alert(response.error.message);
  } else {
    var form$ = $('#paymentFrm');
    var token = response['id'];
    form$.append("<input type='hidden' name='stripeToken' value='" + token + "' />");
    form$.get(0).submit();
  }
}
$(document).ready(function() {
  $('#paymentFrm').submit(function(event) {
    if (<?php echo $price; ?> == 0) return true;
    $('#payBtn').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> Processing…');
    Stripe.createToken({
      number: $('.card-number').val(),
      cvc: $('.card-cvc').val(),
      exp_month: $('.card-expiry-month').val(),
      exp_year: $('.card-expiry-year').val()
    }, stripeResponseHandler);
    return false;
  });
});
</script>
