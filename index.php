<?php
/* =============================================================
   EVENTHUB PRO — Premium Homepage (Phase 1 + QA)
   Database-driven: Featured Events, Categories, Statistics,
   Gallery. Uses reusable header.php / footer.php.
   Backend logic (auth, registration, Stripe) untouched.
   ============================================================= */
session_start();
include('dbconnect.php');
$page_title = 'EventHub Pro — Organize Extraordinary Events';
$page_desc = 'EventHub Pro helps you create, manage, promote and sell tickets for conferences, workshops, hackathons, college fests and corporate events.';

// ---- Statistics (DB-driven counts) ----
$total_events = 0; $total_participants = 0; $total_organizers = 0; $success_rate = 99;
$res_ev = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event");
if ($res_ev) { $r = mysqli_fetch_assoc($res_ev); $total_events = (int)$r['c']; }
$res_reg = mysqli_query($conn, "(SELECT COUNT(*) AS c FROM singleevent_registration) UNION ALL (SELECT COUNT(*) AS c FROM teamevent_registration)");
if ($res_reg) { while ($rr = mysqli_fetch_assoc($res_reg)) { $total_participants += (int)$rr['c']; } }
$res_org = mysqli_query($conn, "SELECT COUNT(*) AS c FROM sign_up");
if ($res_org) { $r = mysqli_fetch_assoc($res_org); $total_organizers = (int)$r['c']; }
// Success rate = events that ran (published) vs total created, floored at 0
if ($total_events > 0) {
  $res_open = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes'");
  if ($res_open) { $ro = mysqli_fetch_assoc($res_open); $success_rate = (int)round(($ro['c'] / $total_events) * 100); }
  $success_rate = max(0, min(100, $success_rate));
}

// ---- Categories (DB-driven with event counts) ----
$categories = [];
$res_cat = mysqli_query($conn, "SELECT category, COUNT(*) AS cnt FROM create_event WHERE category != '' GROUP BY category ORDER BY cnt DESC LIMIT 8");
if ($res_cat) { while ($c = mysqli_fetch_assoc($res_cat)) { $categories[] = $c; } }
$cat_icons = ['fa-laptop-code','fa-users','fa-gamepad','fa-trophy','fa-music','fa-briefcase','fa-microchip','fa-chart-line'];

// ---- Featured Events (real DB data only) ----
$featured = [];
$res_feat = mysqli_query($conn, "SELECT event_id, event_title, event_venue, event_thumbnail, startdate, event_price, category FROM create_event WHERE publish_event='yes' AND open_closed='open' ORDER BY startdate ASC LIMIT 6");
if ($res_feat) { while ($e = mysqli_fetch_assoc($res_feat)) { $featured[] = $e; } }

// ---- Gallery (real DB data only) ----
$gallery = [];
$res_gal = mysqli_query($conn, "SELECT image FROM gallery ORDER BY id DESC LIMIT 6");
if ($res_gal) { while ($g = mysqli_fetch_assoc($res_gal)) { $img = trim($g['image'] ?? ''); if ($img !== '') $gallery[] = $img; } }

// ---- Testimonials (static marketing copy — part of landing design) ----
$testimonials = [
  ['n'=>'Rohit Sharma','r'=>'Organizer','q'=>'Amazing platform! EventHub Pro made our college fest so easy to manage. Tickets, payments, everything in one place.'],
  ['n'=>'Priya Verma','r'=>'Student','q'=>'Best experience. Discovered so many events and registered in seconds. The UI is stunning!'],
  ['n'=>'Ananya Gupta','r'=>'Organizer','q'=>'The dashboard is a game changer. Registrations and analytics at my fingertips. Highly recommended.'],
  ['n'=>'Archit Prajapati','r'=>'Developer','q'=>'Clean, fast and premium. A beautiful platform for event management at any scale.'],
];
?>
<?php include('header.php'); ?>

<!-- ===================== HERO ===================== -->
<section class="eh-hero">
  <div class="container-max eh-hero-grid">
    <div>
      <span class="eh-badge eh-reveal"><i class="fas fa-star"></i> World's #1 Event Platform</span>
      <h1 class="eh-reveal">Organize Extraordinary Events<br><span class="grad">Without the Stress.</span></h1>
      <p class="eh-reveal">Create, Manage, Promote and Sell Tickets for Conferences, Workshops, Hackathons, College Fests and Corporate Events.</p>
      <div class="eh-hero-actions eh-reveal">
<a href="events.php" class="eh-btn eh-btn-primary"><i class="fas fa-rocket"></i> Explore Events</a>
        <a href="login.php" class="eh-btn eh-btn-ghost"><i class="fas fa-plus"></i> Create Event</a>
      </div>
      <div class="eh-rating eh-reveal">
        <span class="eh-stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span>
        <span><b>4.9</b> Rating</span>
      </div>
    </div>

    <!-- Floating dashboard mockup -->
    <div class="eh-dash">
      <div class="eh-dash-card">
        <div class="eh-dash-head"><h3>Dashboard</h3><span style="color:var(--eh-accent);"><i class="fas fa-ellipsis-v"></i></span></div>
        <div style="color:var(--eh-muted);font-size:13px;">Today's Revenue</div>
        <div class="eh-dash-rev">₹52,800 <span class="eh-dash-up"><i class="fas fa-arrow-up"></i> 12%</span></div>
        <div class="eh-dash-row">
          <div class="eh-dash-mini"><div class="lbl">Registrations</div><div class="val">245</div></div>
          <div class="eh-dash-mini"><div class="lbl">Upcoming</div><div class="val">Hackathon</div></div>
        </div>
        <div style="margin-top:16px;">
          <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--eh-muted);"><span>Tickets Sold</span><span>95%</span></div>
          <div class="eh-progress"><span></span></div>
        </div>
      </div>
      <div class="eh-dash-float f1"><div class="t">Events</div><div class="v"><?php echo number_format($total_events); ?>+</div></div>
      <div class="eh-dash-float f2"><div class="t">Participants</div><div class="v"><?php echo number_format($total_participants); ?>+</div></div>
    </div>
  </div>
</section>

<!-- ===================== TRUSTED ===================== -->
<section class="eh-trusted">
  <div class="container-max">
    <h4>Trusted by leading organizations</h4>
    <div class="eh-trusted-track">
      <span>Google</span><span>Microsoft</span><span>IBM</span><span>Infosys</span><span>Oracle</span><span>Amazon</span><span>Adobe</span>
      <span>Google</span><span>Microsoft</span><span>IBM</span><span>Infosys</span><span>Oracle</span><span>Amazon</span><span>Adobe</span>
    </div>
  </div>
</section>

<!-- ===================== STATISTICS ===================== -->
<section class="eh-section" id="statistics">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Our Impact</span>
      <h2>Numbers that speak for themselves</h2>
    </div>
<div class="eh-stats">
      <div class="eh-stat" data-aos="fade-up"><div class="num" data-target="<?php echo $total_events; ?>" data-suffix="+">0</div><div class="lbl">Events Hosted</div></div>
      <div class="eh-stat" data-aos="fade-up" data-aos-delay="100"><div class="num" data-target="<?php echo $total_participants; ?>" data-suffix="+">0</div><div class="lbl">Participants</div></div>
      <div class="eh-stat" data-aos="fade-up" data-aos-delay="200"><div class="num" data-target="<?php echo $total_organizers; ?>" data-suffix="+">0</div><div class="lbl">Organizers</div></div>
      <div class="eh-stat" data-aos="fade-up" data-aos-delay="300"><div class="num" data-target="<?php echo $success_rate; ?>" data-suffix="%">0</div><div class="lbl">Success Rate</div></div>
    </div>
  </div>
</section>

<!-- ===================== CATEGORIES ===================== -->
<section class="eh-section" id="categories">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Browse</span>
      <h2>Explore by Category</h2>
      <p>Find the perfect event for you.</p>
    </div>
<div class="eh-cats">
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $i => $cat): $ic = $cat_icons[$i % count($cat_icons)]; ?>
        <a href="events.php?category=<?php echo urlencode($cat['category']); ?>" class="eh-cat" data-aos="fade-up" data-aos-delay="<?php echo $i*60; ?>">
          <i class="fas <?php echo $ic; ?>"></i>
          <h4><?php echo htmlspecialchars($cat['category']); ?></h4>
          <span class="cnt"><?php echo $cat['cnt']; ?> events</span>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="eh-empty" data-aos="fade-up">
          <i class="fas fa-tags"></i>
          <h3>No categories yet</h3>
          <p>Categories will appear here once events are published.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===================== FEATURED EVENTS ===================== -->
<section class="eh-section" id="events">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Featured</span>
      <h2>Featured Events</h2>
      <p>Handpicked events you don't want to miss.</p>
    </div>
<div class="eh-events">
      <?php if (!empty($featured)): ?>
        <?php foreach ($featured as $i => $ev): ?>
        <div class="eh-card" data-aos="fade-up" data-aos-delay="<?php echo ($i%3)*80; ?>">
          <div class="eh-card-img">
            <img src="images/<?php echo htmlspecialchars($ev['event_thumbnail']); ?>" alt="<?php echo htmlspecialchars($ev['event_title']); ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/img/band-playing-on-stage-2747446.jpg';">
            <span class="eh-card-cat"><?php echo htmlspecialchars($ev['category']); ?></span>
            <span class="eh-card-date"><i class="far fa-calendar"></i> <?php echo date('d M Y', strtotime($ev['startdate'])); ?></span>
          </div>
          <div class="eh-card-body">
            <h3><?php echo htmlspecialchars($ev['event_title']); ?></h3>
            <div class="eh-card-meta"><span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ev['event_venue']); ?></span></div>
            <div class="eh-card-foot">
              <?php $price = (int)$ev['event_price']; ?>
              <span class="eh-price"><?php echo $price > 0 ? '₹' . number_format($price) : 'Free'; ?></span>
              <a href="eventpage.php?id=<?php echo (int)$ev['event_id']; ?>" class="eh-btn eh-btn-primary eh-btn-sm">Register</a>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="eh-empty" data-aos="fade-up">
          <i class="fas fa-calendar-times"></i>
          <h3>No events available yet</h3>
          <p>Check back soon — organizers are preparing amazing events.</p>
          <a href="login.php" class="eh-btn eh-btn-primary">Create an Event</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===================== PROCESS ===================== -->
<section class="eh-section" id="how">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">How it works</span>
      <h2>From idea to unforgettable event</h2>
    </div>
    <div class="eh-process">
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">1</div><div><h4>Create Event</h4><p>Set up your event with details, schedule, venue and pricing.</p></div></div>
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">2</div><div><h4>Publish</h4><p>Go live and start attracting participants instantly.</p></div></div>
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">3</div><div><h4>Registrations</h4><p>Users register in seconds with a smooth, modern flow.</p></div></div>
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">4</div><div><h4>Payments</h4><p>Secure online payments via Stripe, fully automated.</p></div></div>
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">5</div><div><h4>QR Check-In</h4><p>Scan tickets instantly at the venue.</p></div></div>
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">6</div><div><h4>Analytics</h4><p>Track registrations, revenue and engagement in real time.</p></div></div>
    </div>
  </div>
</section>

<!-- ===================== WHY CHOOSE US (bento) ===================== -->
<section class="eh-section" id="why">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Why choose us</span>
      <h2>Everything you need, built-in</h2>
    </div>
    <div class="eh-bento">
      <div class="eh-bento-item big" data-aos="fade-up"><i class="fas fa-robot"></i><h4>AI Recommendations</h4><p>Smart suggestions to boost registrations and engagement.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="80"><i class="fas fa-qrcode"></i><h4>QR Tickets</h4><p>Secure digital tickets with instant QR check-in.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="120"><i class="fas fa-chart-line"></i><h4>Analytics</h4><p>Detailed insights on every aspect of your event.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="160"><i class="fas fa-credit-card"></i><h4>Payments</h4><p>Accept payments securely with Stripe integration.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="200"><i class="fas fa-award"></i><h4>Certificates</h4><p>Auto-generate certificates for participants.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="240"><i class="fas fa-calendar-check"></i><h4>Event Calendar</h4><p>FullCalendar integration for easy scheduling.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="280"><i class="fas fa-robot"></i><h4>Chatbot</h4><p>24/7 AI assistant to answer participant queries.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="320"><i class="fas fa-envelope"></i><h4>Email Reminders</h4><p>Automated reminders to reduce no-shows.</p></div>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section class="eh-section eh-testi" id="testimonials">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Testimonials</span>
      <h2>Loved by organizers & participants</h2>
    </div>
    <div class="swiper eh-testi-swiper" data-aos="fade-up">
      <div class="swiper-wrapper">
        <?php foreach ($testimonials as $t): ?>
        <div class="swiper-slide">
          <div class="eh-testi-card">
            <div class="eh-stars" style="margin-bottom:12px;"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
            <p class="q">"<?php echo $t['q']; ?>"</p>
            <div class="eh-testi-who"><div class="eh-avatar"><?php echo strtoupper(substr($t['n'],0,1)); ?></div><div><div class="n"><?php echo $t['n']; ?></div><div class="r"><?php echo $t['r']; ?></div></div></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="swiper-pagination eh-testi-pagination"></div>
    </div>
  </div>
</section>

<!-- ===================== GALLERY ===================== -->
<section class="eh-section" id="gallery">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Gallery</span>
      <h2>Moments from our events</h2>
    </div>
<div class="eh-gal">
      <?php if (!empty($gallery)): ?>
        <?php foreach ($gallery as $g): ?>
        <div class="eh-gal-item" data-aos="fade-up"><img src="images/<?php echo htmlspecialchars($g); ?>" alt="Event gallery photo" loading="lazy" onerror="this.onerror=null;this.src='assets/img/ammunation-2019.jpg';"></div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="eh-empty" data-aos="fade-up">
          <i class="fas fa-images"></i>
          <h3>Gallery will be updated soon</h3>
          <p>Moments from our past events will be showcased here.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===================== PRICING ===================== -->
<section class="eh-section" id="pricing">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Pricing</span>
      <h2>Simple, transparent pricing</h2>
    </div>
    <div class="eh-price-grid">
      <div class="eh-plan" data-aos="fade-up"><div class="pname">Free</div><div class="pamount">₹0</div><ul><li><i class="fas fa-check"></i>Upto 50 registrations</li><li><i class="fas fa-check"></i>Basic analytics</li><li><i class="fas fa-check"></i>Community support</li></ul><a href="login.php" class="eh-btn eh-btn-ghost" style="width:100%;justify-content:center;">Get Started</a></div>
<div class="eh-plan popular" data-aos="fade-up" data-aos-delay="100"><div class="pname">Pro</div><div class="pamount">₹499</div><ul><li><i class="fas fa-check"></i>Unlimited registrations</li><li><i class="fas fa-check"></i>Advanced analytics</li><li><i class="fas fa-check"></i>Stripe payments</li><li><i class="fas fa-check"></i>QR tickets</li></ul><a href="login.php" class="eh-btn eh-btn-primary" style="width:100%;justify-content:center;">Get Started</a></div>
      <div class="eh-plan" data-aos="fade-up" data-aos-delay="200"><div class="pname">Enterprise</div><div class="pamount">Custom</div><ul><li><i class="fas fa-check"></i>Everything in Pro</li><li><i class="fas fa-check"></i>AI features</li><li><i class="fas fa-check"></i>Dedicated support</li></ul><a href="aboutus.php" class="eh-btn eh-btn-ghost" style="width:100%;justify-content:center;">Contact Us</a></div>
    </div>
  </div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="eh-section" id="faq">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">FAQ</span>
      <h2>Frequently asked questions</h2>
    </div>
    <div class="eh-faq">
      <div class="eh-faq-item open" data-aos="fade-up"><button class="eh-faq-q">How do I create an event? <i class="fas fa-chevron-down"></i></button><div class="eh-faq-a"><p>Log in, click "Create Event", fill in the details and publish. It's that simple.</p></div></div>
      <div class="eh-faq-item" data-aos="fade-up"><button class="eh-faq-q">Can I sell tickets? <i class="fas fa-chevron-down"></i></button><div class="eh-faq-a"><p>Yes! Set a price for your event and we handle secure payments via Stripe.</p></div></div>
      <div class="eh-faq-item" data-aos="fade-up"><button class="eh-faq-q">How do participants register? <i class="fas fa-chevron-down"></i></button><div class="eh-faq-a"><p>Participants browse events, click Register, and complete the form with optional payment.</p></div></div>
      <div class="eh-faq-item" data-aos="fade-up"><button class="eh-faq-q">Is my data secure? <i class="fas fa-chevron-down"></i></button><div class="eh-faq-a"><p>Yes, we follow security best practices and use secure payment processing.</p></div></div>
    </div>
  </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="eh-cta-section">
  <div class="container-max">
    <div class="eh-cta-box" data-aos="zoom-in">
      <h2>Ready to organize something amazing?</h2>
      <p>Join thousands of organizers creating unforgettable events.</p>
      <a href="login.php" class="eh-btn"><i class="fas fa-rocket"></i> Get Started Free</a>
    </div>
  </div>
</section>

<?php include('footer.php'); ?>
<?php $conn->close(); ?>
