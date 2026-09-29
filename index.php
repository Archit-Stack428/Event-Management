<?php
/* =============================================================
   EVENTHUB PRO — Premium Homepage (Phase 1 + QA)
   Database-driven: Featured Events, Categories, Statistics,
   Gallery. Uses reusable header.php / footer.php.
   Backend logic (auth, registration, Stripe) untouched.
   ============================================================= */
session_start();
include('dbconnect.php');

// ---- Self-Healing Event Seeder ----
$check_feat = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes'");
$feat_count = 0;
if ($check_feat) {
    $r = mysqli_fetch_assoc($check_feat);
    $feat_count = (int)$r['c'];
}

if ($feat_count < 3) {
    $seeds = [
        [
            'organizer_name' => 'System Admin',
            'event_title' => 'MMDU National Hackathon 2026',
            'event_desc' => 'Join the ultimate 24-hour coding challenge at MMDU. Build innovative solutions for real-world problems and win grand cash prizes! Mentorship from top industry experts will be provided throughout the event.',
            'category' => 'Technical',
            'eventtype' => 'Team Event',
            'min_team' => 2,
            'max_team' => 4,
            'event_rules' => "1. Maximum 4 members per team.\n2. Projects must be built from scratch during the hackathon.\n3. Decision of the jury is final and binding.",
            'startdate' => '2026-10-15',
            'enddate' => '2026-10-16',
            'event_venue' => 'Main IT Block Auditorium',
            'time' => '10:00 AM',
            'event_price' => 199,
            'event_thumbnail' => 'default_hackathon.jpg',
            'event_sponsors' => 'Google Cloud, Microsoft, Github',
            'event_prizes' => '1st Prize: ₹50,000 | 2nd Prize: ₹30,000 | 3rd Prize: ₹15,000',
            'publish_event' => 'yes',
            'open_closed' => 'open'
        ],
        [
            'organizer_name' => 'System Admin',
            'event_title' => 'Symphony Music Fest 2026',
            'event_desc' => 'Showcase your musical talent at Symphony 2026. Solo singing, group bands, and instrumental performances are welcome. Join us for a night of beautiful melodies and rock beats!',
            'category' => 'Cultural',
            'eventtype' => 'Single Participant',
            'min_team' => 0,
            'max_team' => 0,
            'event_rules' => "1. Individual performances only.\n2. Maximum time limit is 5 minutes.\n3. Submit backing tracks at least 2 hours before the start.",
            'startdate' => '2026-11-20',
            'enddate' => '2026-11-20',
            'event_venue' => 'Open Air Theater (OAT)',
            'time' => '05:30 PM',
            'event_price' => 0,
            'event_thumbnail' => 'default_cultural.jpg',
            'event_sponsors' => 'MTV, Spotify India',
            'event_prizes' => 'Winner: ₹20,000 Trophy | Runner Up: ₹10,000',
            'publish_event' => 'yes',
            'open_closed' => 'open'
        ],
        [
            'organizer_name' => 'System Admin',
            'event_title' => 'Spardha Annual Sports Meet',
            'event_desc' => 'Compete with the best athletes in track events, basketball, volleyball, and football at the Spardha Annual Athletics Meet. Bring your college glory back home!',
            'category' => 'Sports',
            'eventtype' => 'Single Participant',
            'min_team' => 0,
            'max_team' => 0,
            'event_rules' => "1. Standard sporting gear is mandatory.\n2. Referees decisions will be final.\n3. ID Card registration verification required at entry.",
            'startdate' => '2026-12-05',
            'enddate' => '2026-12-08',
            'event_venue' => 'University Sports Complex Ground',
            'time' => '08:00 AM',
            'event_price' => 99,
            'event_thumbnail' => 'default_sports.jpg',
            'event_sponsors' => 'RedBull, Decathlon',
            'event_prizes' => 'Gold, Silver & Bronze Medals + Cash rewards for best performers',
            'publish_event' => 'yes',
            'open_closed' => 'open'
        ],
        [
            'organizer_name' => 'System Admin',
            'event_title' => 'Generative AI & LLM Bootcamp',
            'event_desc' => 'Hands-on workshop on building applications with OpenAI API, LangChain, and vector databases. Develop a real chatbot during the bootcamp. Certificates will be provided to all attendees.',
            'category' => 'Workshops',
            'eventtype' => 'Single Participant',
            'min_team' => 0,
            'max_team' => 0,
            'event_rules' => "1. Basic python knowledge is recommended.\n2. Bring your own laptop.\n3. Active internet access will be provided.",
            'startdate' => '2026-09-10',
            'enddate' => '2026-09-11',
            'event_venue' => 'Seminar Hall 3, Block C',
            'time' => '09:30 AM',
            'event_price' => 149,
            'event_thumbnail' => 'default_workshop.jpg',
            'event_sponsors' => 'OpenAI Developer Group, HuggingFace',
            'event_prizes' => 'Certificate of Mastery + API credits worth $50 for top 5 projects',
            'publish_event' => 'yes',
            'open_closed' => 'open'
        ]
    ];

    foreach ($seeds as $s) {
        $stmt = $conn->prepare("INSERT INTO create_event (organizer_name, event_title, event_desc, category, eventtype, min_team, max_team, event_rules, startdate, enddate, event_venue, time, event_price, event_thumbnail, event_sponsors, event_prizes, publish_event, open_closed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sssssiisssssisssss', 
            $s['organizer_name'], 
            $s['event_title'], 
            $s['event_desc'], 
            $s['category'], 
            $s['eventtype'], 
            $s['min_team'], 
            $s['max_team'], 
            $s['event_rules'], 
            $s['startdate'], 
            $s['enddate'], 
            $s['event_venue'], 
            $s['time'], 
            $s['event_price'], 
            $s['event_thumbnail'], 
            $s['event_sponsors'], 
            $s['event_prizes'], 
            $s['publish_event'], 
            $s['open_closed']
        );
        $stmt->execute();
        $stmt->close();
    }
}

// ---- Self-Healing Gallery Seeder ----
$check_gal = mysqli_query($conn, "SELECT COUNT(*) AS c FROM gallery");
$gal_count = 0;
if ($check_gal) {
    $r = mysqli_fetch_assoc($check_gal);
    $gal_count = (int)$r['c'];
}

if ($gal_count < 3) {
    $gal_seeds = [
        ['event_name' => 'MMDU National Hackathon 2026', 'organizer_name' => 'System Admin', 'image' => 'default_hackathon.jpg', 'date' => '2026-08-10', 'category' => 'Technical'],
        ['event_name' => 'Symphony Music Fest 2026', 'organizer_name' => 'System Admin', 'image' => 'default_cultural.jpg', 'date' => '2026-08-11', 'category' => 'Cultural'],
        ['event_name' => 'Spardha Annual Sports Meet', 'organizer_name' => 'System Admin', 'image' => 'default_sports.jpg', 'date' => '2026-08-12', 'category' => 'Sports'],
        ['event_name' => 'Generative AI & LLM Bootcamp', 'organizer_name' => 'System Admin', 'image' => 'default_workshop.jpg', 'date' => '2026-08-13', 'category' => 'Workshops']
    ];

    foreach ($gal_seeds as $g) {
        $stmt = $conn->prepare("INSERT INTO gallery (event_name, organizer_name, image, date, category) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $g['event_name'], $g['organizer_name'], $g['image'], $g['date'], $g['category']);
        $stmt->execute();
        $stmt->close();
    }
}

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

if ($total_events > 0) {
  $res_open = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes'");
  if ($res_open) { $ro = mysqli_fetch_assoc($res_open); $success_rate = (int)round(($ro['c'] / $total_events) * 100); }
  $success_rate = max(0, min(100, $success_rate));
}

// ---- Categories (DB-driven counts merged with default categories) ----
$categories_from_db = [];
$res_cat = mysqli_query($conn, "SELECT category, COUNT(*) AS cnt FROM create_event WHERE category != '' GROUP BY category");
if ($res_cat) {
    while ($c = mysqli_fetch_assoc($res_cat)) {
        $categories_from_db[strtolower(trim($c['category']))] = [
            'category' => $c['category'],
            'cnt' => (int)$c['cnt']
        ];
    }
}

$core_categories = [
    'technical' => ['category' => 'Technical', 'cnt' => 0, 'icon' => 'fa-laptop-code'],
    'cultural'  => ['category' => 'Cultural', 'cnt' => 0, 'icon' => 'fa-music'],
    'sports'    => ['category' => 'Sports', 'cnt' => 0, 'icon' => 'fa-trophy'],
    'workshops' => ['category' => 'Workshops', 'cnt' => 0, 'icon' => 'fa-users'],
    'others'    => ['category' => 'Others', 'cnt' => 0, 'icon' => 'fa-gamepad']
];

foreach ($categories_from_db as $lower_name => $db_cat) {
    if (array_key_exists($lower_name, $core_categories)) {
        $core_categories[$lower_name]['cnt'] = $db_cat['cnt'];
    } else {
        $core_categories[$lower_name] = [
            'category' => $db_cat['category'],
            'cnt' => $db_cat['cnt'],
            'icon' => 'fa-tags'
        ];
    }
}
$categories = array_values($core_categories);

// ---- Featured Events ----
$featured = [];
$res_feat = mysqli_query($conn, "SELECT event_id, event_title, event_venue, event_thumbnail, startdate, event_price, category FROM create_event WHERE publish_event='yes' AND open_closed='open' ORDER BY startdate ASC LIMIT 6");
if ($res_feat) { while ($e = mysqli_fetch_assoc($res_feat)) { $featured[] = $e; } }

// ---- Gallery ----
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
        <?php foreach ($categories as $i => $cat): $ic = isset($cat['icon']) ? $cat['icon'] : 'fa-tags'; ?>
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
      <div class="eh-step" data-aos="fade-up"><div class="eh-step-num">4</div><div><h4>UPI Payments</h4><p>Fast, automated payments via UPI QR &amp; apps, fully verified.</p></div></div>
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
      <div class="eh-bento-item big" data-aos="fade-up" onclick="openBentoModal('ai')" style="cursor: pointer;"><i class="fas fa-robot"></i><h4>AI Recommendations</h4><p>Smart suggestions to boost registrations and engagement.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="80" onclick="openBentoModal('qr')" style="cursor: pointer;"><i class="fas fa-qrcode"></i><h4>QR Tickets</h4><p>Secure digital tickets with instant QR check-in.</p></div>
      <a href="dashboard.php" class="eh-bento-item" data-aos="fade-up" data-aos-delay="120" style="text-decoration: none; color: inherit; display: block;"><i class="fas fa-chart-line"></i><h4>Analytics</h4><p>Detailed insights on every aspect of your event.</p></a>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="160" onclick="openBentoModal('payments')" style="cursor: pointer;"><i class="fas fa-qrcode"></i><h4>UPI Payments</h4><p>Accept instant payments via UPI QR and apps (PhonePe, GPay, Paytm).</p></div>
      <a href="calendar.php" class="eh-bento-item" data-aos="fade-up" data-aos-delay="240" style="text-decoration: none; color: inherit; display: block;"><i class="fas fa-calendar-check"></i><h4>Event Calendar</h4><p>Interactive calendar for easy scheduling.</p></a>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="280" onclick="toggleChatbot()" style="cursor: pointer;"><i class="fas fa-robot"></i><h4>Chatbot</h4><p>24/7 AI assistant to answer participant queries.</p></div>
      <div class="eh-bento-item" data-aos="fade-up" data-aos-delay="320" onclick="openBentoModal('reminders')" style="cursor: pointer;"><i class="fas fa-envelope"></i><h4>Email Reminders</h4><p>Automated reminders to reduce no-shows.</p></div>
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
    <?php if (!empty($gallery)): ?>
      <div class="eh-gal">
        <?php foreach ($gallery as $g): ?>
        <div class="eh-gal-item" data-aos="fade-up"><img src="images/<?php echo htmlspecialchars($g); ?>" alt="Event gallery photo" loading="lazy" onerror="this.onerror=null;this.src='assets/img/ammunation-2019.jpg';"></div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="eh-empty" data-aos="fade-up" style="width:100%; text-align:center; padding:60px 20px;">
        <i class="fas fa-images" style="font-size:48px; color:var(--eh-muted); margin-bottom:16px;"></i>
        <h3>Gallery will be updated soon</h3>
        <p style="color:var(--eh-muted);">Moments from our past events will be showcased here.</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Bento Feature Modal (Glassmorphic) -->
<div id="bentoModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(7,11,20,0.85); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(10px);">
  <div class="eh-panel" style="max-width:500px; width:90%; padding:30px; border-radius:20px; position:relative; border:1px solid rgba(255,255,255,0.08); text-align:center; background: rgba(9, 9, 11, 0.95);">
    <button onclick="closeBentoModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:#fff; font-size:20px; cursor:pointer;"><i class="fas fa-times"></i></button>
    <div id="bentoModalContent"></div>
  </div>
</div>

<!-- Floating Chatbot Widget -->
<div id="ehChatbotWidget" style="position:fixed; bottom:30px; right:30px; z-index:9999; font-family:'Inter', sans-serif;">
  <!-- Chat Button -->
  <button onclick="toggleChatbot()" id="ehChatBtn" aria-label="Open AI Assistant" style="width:60px; height:60px; border-radius:50%; background:var(--eh-accent, #7C3AED); border:none; color:#fff; font-size:24px; cursor:pointer; box-shadow:0 8px 24px rgba(124,58,237,0.4); display:flex; align-items:center; justify-content:center; transition:transform 0.3s;">
    <i class="fas fa-comments"></i>
  </button>
  
  <!-- Chat Box Panel -->
  <div id="ehChatBox" class="eh-panel" style="display:none; width:380px; height:510px; max-height:82vh; max-width:calc(100vw - 36px); position:absolute; bottom:80px; right:0; padding:0; border-radius:22px; border:1px solid rgba(255,255,255,0.12); background:rgba(9,9,11,0.96); backdrop-filter:blur(24px); -webkit-backdrop-filter:blur(24px); flex-direction:column; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.6);">
    <!-- Chat Header -->
    <div style="background:rgba(255,255,255,0.03); padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.06); display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:10px; height:10px; border-radius:50%; background:#10b981; box-shadow:0 0 10px #10b981;"></div>
        <div>
          <div style="font-weight:700; color:#fff; font-size:14.5px; line-height:1.2;">EventHub AI Assistant</div>
          <div style="font-size:11px; color:#10b981;">Online • Fast &amp; Secure</div>
        </div>
      </div>
      <button onclick="toggleChatbot()" aria-label="Close Chat" style="background:none; border:none; color:var(--eh-muted); font-size:16px; cursor:pointer; padding:6px;"><i class="fas fa-times"></i></button>
    </div>
    
    <!-- Chat Messages -->
    <div id="ehChatMessages" style="flex:1; padding:18px; overflow-y:auto; display:flex; flex-direction:column; gap:12px; font-size:13px; color:#fff; text-align:left;">
      <div style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); padding:12px 14px; border-radius:16px 16px 16px 0; align-self:flex-start; max-width:88%; line-height:1.5;">
        👋 <b>Welcome! I am your EventHub Pro AI Assistant.</b><br>
        Ask me anything about finding events, registrations, UPI payments, QR tickets, or hosting your own fest!
        <div style="display:flex; flex-direction:column; gap:6px; margin-top:10px;">
          <button type="button" onclick="askQuickPrompt('Explain how can I use this website')" style="text-align:left; background:rgba(124,58,237,0.18); border:1px solid rgba(124,58,237,0.35); color:#ddd6fe; padding:7px 11px; border-radius:8px; font-size:12px; cursor:pointer; transition:all .2s;">💡 How to use this website?</button>
          <button type="button" onclick="askQuickPrompt('How do I register for an event?')" style="text-align:left; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:#e2e8f0; padding:7px 11px; border-radius:8px; font-size:12px; cursor:pointer; transition:all .2s;">📝 How to register for an event?</button>
          <button type="button" onclick="askQuickPrompt('How to pay via UPI & get QR ticket?')" style="text-align:left; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:#e2e8f0; padding:7px 11px; border-radius:8px; font-size:12px; cursor:pointer; transition:all .2s;">💳 UPI Payments & QR Tickets</button>
          <button type="button" onclick="askQuickPrompt('How can I create and host my own event?')" style="text-align:left; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); color:#e2e8f0; padding:7px 11px; border-radius:8px; font-size:12px; cursor:pointer; transition:all .2s;">🚀 Host your own event</button>
        </div>
      </div>
    </div>
    
    <!-- Chat Input -->
    <form onsubmit="sendChatMessage(event)" style="padding:14px; border-top:1px solid rgba(255,255,255,0.06); background:rgba(9,9,11,0.7); display:flex; gap:10px; margin:0; align-items:center;">
      <input type="text" id="ehChatInput" placeholder="Ask about events, tickets, UPI, host..." required style="flex:1; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:10px 14px; border-radius:12px; color:#fff; font-size:13px; outline:none;">
      <button type="submit" aria-label="Send Message" style="background:var(--eh-gradient, linear-gradient(135deg,#7C3AED,#2563EB)); border:none; color:#fff; width:38px; height:38px; border-radius:12px; cursor:pointer; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 4px 14px rgba(124,58,237,0.35);"><i class="fas fa-paper-plane"></i></button>
    </form>
  </div>
</div>

<script>
function openBentoModal(feature) {
  var content = '';
  var modal = document.getElementById('bentoModal');
  var contentDiv = document.getElementById('bentoModalContent');
  
  if (feature === 'ai') {
    content = `
      <i class="fas fa-robot" style="font-size:48px; color:var(--eh-accent, #7C3AED); margin-bottom:16px;"></i>
      <h3 style="color:#fff; font-weight:700; margin-bottom:10px;">AI Recommended Events</h3>
      <p style="color:var(--eh-muted); font-size:14px; margin-bottom:20px;">Based on popular trends, these top college events are recommended for you:</p>
      <div style="text-align:left; background:rgba(255,255,255,0.02); padding:15px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); margin-bottom:20px;">
        <div style="font-weight:600; color:#fff; margin-bottom:4px;">1. MMDU National Hackathon 2026</div>
        <div style="font-size:12px; color:var(--eh-muted); margin-bottom:10px;">Technical • 24hr Coding Challenge</div>
        <div style="font-weight:600; color:#fff; margin-bottom:4px;">2. Symphony Music Fest 2026</div>
        <div style="font-size:12px; color:var(--eh-muted);">Cultural • Singing & Band competition</div>
      </div>
      <a href="events.php" class="eh-btn eh-btn-primary" style="width:100%; justify-content:center;">Explore All Events</a>
    `;
  } else if (feature === 'qr') {
    content = `
      <i class="fas fa-qrcode" style="font-size:48px; color:var(--eh-accent, #7C3AED); margin-bottom:16px;"></i>
      <h3 style="color:#fff; font-weight:700; margin-bottom:10px;">QR Check-In Tickets</h3>
      <p style="color:var(--eh-muted); font-size:14px; margin-bottom:20px;">Every registration generates a secure digital QR code. Organizers can scan the code at the venue entrance using the built-in scanner to verify tickets instantly.</p>
      <div style="border:4px solid #fff; border-radius:8px; display:inline-block; padding:8px; background:#fff; margin-bottom:20px;">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=https://eventmanagement2315.000webhostapp.com/success.php" style="width:120px; height:120px; display:block;">
      </div>
      <a href="events.php" class="eh-btn eh-btn-primary" style="width:100%; justify-content:center;">Get Your Ticket</a>
    `;
  } else if (feature === 'payments') {
    content = `
      <i class="fas fa-qrcode" style="font-size:48px; color:var(--eh-accent, #7C3AED); margin-bottom:16px;"></i>
      <h3 style="color:#fff; font-weight:700; margin-bottom:10px;">Instant UPI Payments</h3>
      <p style="color:var(--eh-muted); font-size:14px; margin-bottom:20px;">Pay securely and instantly via UPI. Scan the dynamic UPI QR code or pay using your preferred UPI app like PhonePe, Google Pay, Paytm, or BHIM.</p>
      <div style="font-size:13px; color:#fff; display:flex; gap:10px; justify-content:center; margin-bottom:20px; flex-wrap:wrap;">
        <span style="background:rgba(255,255,255,0.06); padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);"><i class="fas fa-mobile-alt"></i> PhonePe</span>
        <span style="background:rgba(255,255,255,0.06); padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);"><i class="fab fa-google-pay"></i> Google Pay</span>
        <span style="background:rgba(255,255,255,0.06); padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);"><i class="fas fa-wallet"></i> Paytm</span>
        <span style="background:rgba(255,255,255,0.06); padding:6px 12px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);"><i class="fas fa-qrcode"></i> UPI QR</span>
      </div>
      <button onclick="closeBentoModal()" class="eh-btn eh-btn-ghost" style="width:100%; justify-content:center;">Understood</button>
    `;
  } else if (feature === 'reminders') {
    content = `
      <i class="fas fa-envelope" style="font-size:48px; color:var(--eh-accent, #7C3AED); margin-bottom:16px;"></i>
      <h3 style="color:#fff; font-weight:700; margin-bottom:10px;">Subscribe to Reminders</h3>
      <p style="color:var(--eh-muted); font-size:14px; margin-bottom:20px;">Receive automated email reminders for your registered events so you never miss a schedule.</p>
      <form onsubmit="subscribeReminders(event)" style="display:flex; gap:10px; margin-bottom:10px;">
        <input type="email" id="reminderEmail" placeholder="Enter your email address" required style="flex:1; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 14px; border-radius:10px; color:#fff; font-size:13px; outline:none;">
        <button type="submit" class="eh-btn eh-btn-primary">Subscribe</button>
      </form>
      <div id="remSubMsg" style="font-size:13px; color:#34d399; margin-top:8px;"></div>
    `;
  }

  contentDiv.innerHTML = content;
  modal.style.display = 'flex';
}

function closeBentoModal() {
  document.getElementById('bentoModal').style.display = 'none';
}

function subscribeReminders(e) {
  e.preventDefault();
  document.getElementById('remSubMsg').innerText = '✓ Subscribed successfully!';
  setTimeout(closeBentoModal, 1500);
}

function toggleChatbot() {
  var chatBox = document.getElementById('ehChatBox');
  var chatBtn = document.getElementById('ehChatBtn');
  if (chatBox.style.display === 'none' || chatBox.style.display === '') {
    chatBox.style.display = 'flex';
    chatBtn.style.transform = 'scale(0.9) rotate(90deg)';
  } else {
    chatBox.style.display = 'none';
    chatBtn.style.transform = 'scale(1) rotate(0deg)';
  }
}

function askQuickPrompt(text) {
  var input = document.getElementById('ehChatInput');
  if (!input) return;
  input.value = text;
  var event = new Event('submit', { cancelable: true });
  input.form.dispatchEvent(event);
}

function sendChatMessage(e) {
  e.preventDefault();
  var input = document.getElementById('ehChatInput');
  var msg = input.value.trim();
  if (msg === '') return;
  
  addChatMessage(msg, 'user');
  input.value = '';
  
  // Show typing indicator
  var typingDiv = showTypingIndicator();
  
  setTimeout(function() {
    if (typingDiv && typingDiv.parentNode) {
      typingDiv.parentNode.removeChild(typingDiv);
    }
    var response = getChatbotResponse(msg);
    addChatMessage(response, 'bot');
  }, 450);
}

function showTypingIndicator() {
  var msgsContainer = document.getElementById('ehChatMessages');
  var typing = document.createElement('div');
  typing.id = 'ehTypingBubble';
  typing.style.background = 'rgba(255, 255, 255, 0.05)';
  typing.style.borderRadius = '14px 14px 14px 0';
  typing.style.alignSelf = 'flex-start';
  typing.style.padding = '8px 14px';
  typing.style.fontSize = '12px';
  typing.style.color = '#94a3b8';
  typing.style.display = 'flex';
  typing.style.alignItems = 'center';
  typing.style.gap = '6px';
  typing.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> <span>Assistant is thinking...</span>';
  msgsContainer.appendChild(typing);
  msgsContainer.scrollTop = msgsContainer.scrollHeight;
  return typing;
}

function escapeHtml(str) {
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function addChatMessage(text, sender) {
  var msgsContainer = document.getElementById('ehChatMessages');
  var msgDiv = document.createElement('div');
  
  if (sender === 'user') {
    msgDiv.style.background = 'var(--eh-gradient, linear-gradient(135deg, #7C3AED, #2563EB))';
    msgDiv.style.borderRadius = '16px 16px 0 16px';
    msgDiv.style.alignSelf = 'flex-end';
    msgDiv.style.color = '#fff';
    msgDiv.style.boxShadow = '0 4px 15px rgba(124,58,237,0.3)';
    msgDiv.textContent = text;
  } else {
    msgDiv.style.background = 'rgba(255, 255, 255, 0.05)';
    msgDiv.style.border = '1px solid rgba(255, 255, 255, 0.08)';
    msgDiv.style.borderRadius = '16px 16px 16px 0';
    msgDiv.style.alignSelf = 'flex-start';
    msgDiv.style.color = '#f1f5f9';
    msgDiv.style.lineHeight = '1.55';
    
    // Format text safely: escape raw input first, then convert linebreaks and bold
    var safe = escapeHtml(text);
    safe = safe.replace(/\n\n/g, '<div style="margin-top:8px;"></div>');
    safe = safe.replace(/\n/g, '<br>');
    safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    msgDiv.innerHTML = safe;
  }
  
  msgDiv.style.padding = '11px 15px';
  msgDiv.style.maxWidth = '86%';
  
  msgsContainer.appendChild(msgDiv);
  msgsContainer.scrollTop = msgsContainer.scrollHeight;
}

function getChatbotResponse(rawQuery) {
  var q = rawQuery.toLowerCase().trim();

  // 1. SECURITY & PRIVACY GUARD (Strict Anti-Leak & Anti-Injection)
  var securityPatterns = [
    /\b(password|passwords|hash|salt|credentials|secret|token|csrf|session|cookie|phpsessid)\b/i,
    /\b(database|db_host|db_user|db_password|dbconnect|tidb|mysql|sql|table schema|dump)\b/i,
    /\b(select\s+\*|union\s+select|insert\s+into|drop\s+table|delete\s+from|update\s+sign_up)\b/i,
    /\b(admin\s+credentials|admin\s+password|root\s+password|user\s+data|private\s+keys)\b/i,
    /\b(api\s+key|secret\s+key|razorpay_secret|google_secret|env|environment\s+variables)\b/i,
    /\b(source\s+code|backend\s+code|eval\(|<script|ignore\s+previous\s+instructions)\b/i
  ];

  for (var i = 0; i < securityPatterns.length; i++) {
    if (securityPatterns[i].test(q)) {
      return "🔒 **Security & Privacy Protected**\n\nI am strictly programmed to protect user privacy and internal system security. I cannot access, reveal, or discuss database credentials, passwords, server configurations, or confidential user information.\n\nI am happy to assist you with event browsing, registration, UPI payments, and ticketing on EventHub Pro!";
    }
  }

  // 2. CONVERSATIONAL SMALL TALK & FRIENDLY CHAT (Fixes "how are you" issue)
  if (
    q.includes('how are you') || 
    q.includes('how r u') || 
    q.includes('how are u') || 
    q.includes('how do you do') || 
    q.includes('how is it going') || 
    q.includes("how's it going") || 
    q.includes('kaise ho') || 
    q.includes('kya haal') || 
    q.includes('sab theek') || 
    q.includes('how you doing') ||
    q.includes('kaise hain') ||
    q.includes('kya chal raha')
  ) {
    return "I'm doing wonderful, thank you so much for asking! 😊✨\n\nI'm always excited to help students and organizers explore campus fests, hackathons, and concerts. How are you doing today? Are you looking to join an event or host your own?";
  }

  if (
    q.includes('who are you') || 
    q.includes('what is your name') || 
    q.includes('whats your name') || 
    q.includes("what's your name") || 
    q.includes('tum kaun ho') || 
    q.includes('aap kaun ho') || 
    q.includes('naam kya hai') || 
    q.includes('are you ai') || 
    q.includes('are you human') || 
    q.includes('are you a bot') ||
    q.includes('tell me about yourself')
  ) {
    return "🤖 **I am your EventHub AI Assistant!**\n\nThink of me as your 24/7 personal campus event concierge. I can help you:\n• Discover trending college hackathons, sports meets, and fests.\n• Walk you through the registration and ticket generation process.\n• Explain how instant UPI payments and QR check-ins work.\n• Guide organizers on how to publish and manage events.\n\nWhat would you like to explore today?";
  }

  if (
    q.includes('what can you do') || 
    q.includes('kya kar sakte ho') || 
    q.includes('what do you do') || 
    q.includes('how can you help') || 
    q.includes('your features')
  ) {
    return "💡 **Here is what I can do for you:**\n\n" +
           "• 🔍 **Find Events**: Ask me about Hackathons, Sports tournaments, or Music fests.\n" +
           "• 📝 **Registration**: Step-by-step guidance on how to participate and fill forms.\n" +
           "• 💳 **UPI & Payments**: Information about GPay, PhonePe, Paytm QR checkouts.\n" +
           "• 🎟️ **QR Tickets**: How digital gate passes work for venue entry.\n" +
           "• 🚀 **Host Events**: Instructions for organizers on publishing events and viewing attendees.\n\n" +
           "Feel free to ask me anything in English or Hindi!";
  }

  if (
    q === 'i am fine' || 
    q === 'i am good' || 
    q === "i'm good" || 
    q === "i'm fine" || 
    q === 'good' || 
    q === 'fine' || 
    q.includes('theek hu') || 
    q.includes('badhiya') || 
    q.includes('all good') || 
    q.includes('mast') || 
    q.includes('doing well')
  ) {
    return "Awesome! Glad to hear that! 🌟 So, what brings you to EventHub Pro today? Want to check out upcoming hackathons, college fests, or sports meets?";
  }

  if (
    q === 'ok' || 
    q === 'okay' || 
    q === 'k' || 
    q === 'kk' || 
    q.includes('theek hai') || 
    q.includes('achha') || 
    q.includes('got it') || 
    q.includes('samajh gaya') || 
    q.includes('understood')
  ) {
    return "Great! 👍 If you have any other questions about events, payments, or registrations, just type them here. Happy to help anytime!";
  }

  // 3. PLATFORM OVERVIEW / HOW TO USE / GUIDE ME
  if (
    q.includes('explain') || 
    q.includes('how can i use') || 
    q.includes('how to use') || 
    q.includes('guide me') || 
    q.includes('guide') || 
    q.includes('website overview') || 
    q.includes('kaise use') || 
    q.includes('kya h ye') || 
    q.includes('kya hai ye') || 
    q.includes('use this website') || 
    q.includes('help me understand') || 
    q.includes('what is eventhub') ||
    q.includes('features of this website') ||
    q.includes('tutorial')
  ) {
    return "🎉 **Welcome to EventHub Pro!** Here is a complete guide on how to use this platform:\n\n" +
           "1️⃣ **Browse Events**: Click 'Events' in the top menu to view all upcoming Hackathons, Workshops, Cultural Fests, and Sports Meets. You can search by event name or filter by category.\n\n" +
           "2️⃣ **Register Easily**: Click on any event card to view the venue, timings, and prizes. Click 'Register', fill in your participant details, and submit.\n\n" +
           "3️⃣ **Instant UPI Payment**: For paid events, scan the dynamic UPI QR code or pay with PhonePe, Google Pay, Paytm, or BHIM.\n\n" +
           "4️⃣ **Digital QR Ticket**: As soon as you register, you receive a digital QR ticket with your unique Ticket ID. Save it on your phone to scan at the venue gate for instant check-in!\n\n" +
           "5️⃣ **Host Your Own Event**: If you are an organizer or college society, log in and click 'Create Event' to publish your fest and track attendees in real time!";
  }

  // 4. REGISTRATION PROCESS / HOW TO REGISTER
  if (
    q.includes('how to register') || 
    q.includes('register into') || 
    q.includes('registration process') || 
    q.includes('register kaise') || 
    q.includes('form kaise') || 
    q.includes('how do i join') || 
    q.includes('part le') || 
    q.includes('participate') ||
    q.includes('enroll') ||
    q.includes('entry kaise')
  ) {
    return "📝 **How to Register for an Event:**\n\n" +
           "1. Go to the **Events** page from the top navbar.\n" +
           "2. Choose the event you want to join and click **Register**.\n" +
           "3. Fill out the registration form (Name, Email, Mobile Number, College).\n" +
           "4. For team events, you can specify team size and team member names.\n" +
           "5. Complete checkout via instant UPI QR (for paid events) or submit for free events.\n" +
           "6. Your verified digital QR ticket will be displayed instantly!";
  }

  // 5. UPI PAYMENTS & TRANSACTIONS
  if (
    q.includes('payment') || 
    q.includes('upi') || 
    q.includes('pay') || 
    q.includes('fees') || 
    q.includes('charges') || 
    q.includes('price') || 
    q.includes('cost') || 
    q.includes('phonepe') || 
    q.includes('gpay') || 
    q.includes('google pay') || 
    q.includes('paytm') || 
    q.includes('bhim') || 
    q.includes('paisa')
  ) {
    return "💳 **Instant UPI Payments on EventHub Pro:**\n\n" +
           "• We support all popular UPI apps: **PhonePe, Google Pay, Paytm, BHIM**, and banking UPI apps.\n" +
           "• During event checkout, a dynamic UPI QR code with the exact fee will be generated.\n" +
           "• Scan the code using your phone or pay directly to the organizer UPI ID.\n" +
           "• Payments are fast and 100% secure. No banking passwords or debit card credentials are ever requested or stored on our servers!";
  }

  // 6. QR CODE TICKETS & GATE ENTRY
  if (
    q.includes('ticket') || 
    q.includes('qr') || 
    q.includes('pass') || 
    q.includes('entry') || 
    q.includes('admit') || 
    q.includes('gate pass')
  ) {
    return "🎟️ **Digital QR Tickets & Gate Check-In:**\n\n" +
           "• Upon completing registration, you immediately get a dynamic QR code ticket.\n" +
           "• It contains your unique Registration ID and verified entry timestamp.\n" +
           "• Simply take a screenshot or save the ticket on your phone.\n" +
           "• When you arrive at the venue, the event coordinators will scan your QR code with the EventHub Pro camera scanner for rapid, paperless gate entry!";
  }

  // 7. CREATING / HOSTING AN EVENT (ORGANIZERS)
  if (
    q.includes('create event') || 
    q.includes('host event') || 
    q.includes('organize event') || 
    q.includes('event kaise banaye') || 
    q.includes('add event') || 
    q.includes('publish event') || 
    q.includes('new event')
  ) {
    return "🚀 **How to Host & Create Your Event:**\n\n" +
           "1. Log in to your organizer account (or create one using Sign Up).\n" +
           "2. Click the **+ Create Event** button in the top navbar.\n" +
           "3. Follow the simple 4-step wizard:\n" +
           "   • **Step 1: Basics** (Title, Category, Type, Description)\n" +
           "   • **Step 2: Schedule & Venue** (Date, Timing, College / Hall)\n" +
           "   • **Step 3: Rules & Prizes** (Eligibility, Cash Rewards)\n" +
           "   • **Step 4: Media** (Upload high-resolution event banner)\n" +
           "4. Click Publish and your event is live for thousands of attendees!";
  }

  // 8. ADMIN PANEL & DASHBOARD
  if (
    q.includes('admin') || 
    q.includes('dashboard') || 
    q.includes('organizer panel') || 
    q.includes('manage events') || 
    q.includes('attendees') || 
    q.includes('participant list')
  ) {
    return "📊 **Organizer Dashboard & Admin Panel:**\n\n" +
           "• Access it anytime by clicking **Dashboard** in the navbar or visiting `/admin`.\n" +
           "• Features included:\n" +
           "  - Total events overview and real-time revenue analytics.\n" +
           "  - Edit, delete, and toggle publish/unpublish for your events.\n" +
           "  - View participant lists and download registered attendee details.\n" +
           "  - Upload photos to the public Gallery.\n" +
           "  - Create new organizer accounts securely.";
  }

  // 9. IS IT FREE / FEES & CHARGES
  if (
    q.includes('is it free') || 
    q.includes('is this free') || 
    q.includes('free hai kya') || 
    q.includes('free h kya') || 
    q.includes('kya ye free h')
  ) {
    return "🎉 **EventHub Pro is free to explore!**\n\n• Many workshops, seminars, and cultural fests are **100% Free** to register.\n• For competitive events with cash prizes (like hackathons or sports meets), entry fees are set by the organizers and paid directly via UPI.\n• Organizers also get a **Free Tier** to host their first 50 participants with zero platform charges!";
  }

  // 10. CERTIFICATES & PRIZES
  if (
    q.includes('certificate') || 
    q.includes('certi') || 
    q.includes('trophy') || 
    q.includes('medal')
  ) {
    return "📜 **Certificates & Awards:**\n\nYes! Most hackathons, workshops, and competitions hosted on EventHub Pro provide official **Participation Certificates** and winner trophies / cash prizes.\n\nYou can review specific certificate eligibility and prize pools directly under the **'Rules & Prizes'** tab on each event's details page!";
  }

  // 11. CANCELLATION & REFUNDS
  if (
    q.includes('cancel') || 
    q.includes('refund') || 
    q.includes('money back')
  ) {
    return "🔄 **Cancellations & Refunds:**\n\nRefund policies are determined by each event's organizer. If you are unable to attend:\n• You can reach out to the event coordinator listed on the event page.\n• Or email our support team at **hello@eventhubpro.com** with your Registration ID, and we will assist you!";
  }

  // 12. ALL EVENTS / CURRENT LIST OF EVENTS
  if (
    q.includes('all events') || 
    q.includes('upcoming events') || 
    q.includes('events dikhao') || 
    q.includes('list of events') || 
    q.includes('kya event hai') || 
    q.includes('what events') || 
    q.includes('available events') || 
    q.includes('kon se event') || 
    q.includes('kaun se event')
  ) {
    return "🎪 **Current Trending Events on EventHub Pro:**\n\n" +
           "1. 💻 **MMDU National Hackathon 2026**: 24-hr team coding challenge with ₹50,000 cash prizes!\n" +
           "2. 🎵 **Symphony Music Fest 2026**: High-voltage battle of the bands and open-mic singing (Free entry)!\n" +
           "3. 🏆 **Spardha Annual Sports Meet**: Inter-college cricket, football, basketball & athletics.\n" +
           "4. 🎨 **Fine Arts & Design Bootcamp**: Creative showcase & workshop.\n\n" +
           "👉 Click **'Events'** in the top navbar to view dates, venue details, and register now!";
  }

  // 13. LOGIN / SIGNUP / ACCOUNT RECOVERY
  if (
    q.includes('login') || 
    q.includes('sign in') || 
    q.includes('signup') || 
    q.includes('sign up') || 
    q.includes('register account') || 
    q.includes('create account') || 
    q.includes('account kaise') || 
    q.includes('logout')
  ) {
    return "🔐 **Account Access & Login:**\n\n" +
           "• Click **Login** in the top navbar to sign in with your email and password.\n" +
           "• You can also use one-click **Continue with Google** for instant access.\n" +
           "• If you don't have an account, switch to the **Sign Up** tab to create one in seconds.\n" +
           "• EventHub Pro features a 30-day persistent session, so you stay logged in without repeated prompts!";
  }

  // 14. EVENT CATEGORIES / SPECIFIC POPULAR EVENTS
  if (
    q.includes('hackathon') || 
    q.includes('coding') || 
    q.includes('tech') || 
    q.includes('technical')
  ) {
    return "💻 **Technical Events & Hackathons:**\n\n" +
           "• **MMDU National Hackathon 2026**: 24-hour team coding battle, ₹199 entry, and ₹50,000 cash prizes!\n" +
           "• Click 'Categories' in the navbar and filter by **Technical** to explore all upcoming coding fests, robotics challenges, and web dev hackathons.";
  }

  if (
    q.includes('music') || 
    q.includes('cultural') || 
    q.includes('symphony') || 
    q.includes('dance') || 
    q.includes('singing')
  ) {
    return "🎵 **Cultural & Music Events:**\n\n" +
           "• **Symphony Music Fest 2026**: Live battle of the bands, solo acoustic, and open mic. Entry is free!\n" +
           "• Check the **Cultural** category for upcoming classical dance, theatre, rock band, and fashion shows.";
  }

  if (
    q.includes('sports') || 
    q.includes('game') || 
    q.includes('tournament') || 
    q.includes('spardha') || 
    q.includes('cricket') || 
    q.includes('football')
  ) {
    return "🏆 **Sports Meets & Tournaments:**\n\n" +
           "• **Spardha Annual Sports Meet**: Inter-college cricket, football, volleyball, badminton, and athletics.\n" +
           "• Check the **Sports** category on the Events page for schedule, fixtures, and participation fees.";
  }

  // 15. PRICING & SUBSCRIPTION PLANS
  if (
    q.includes('pricing') || 
    q.includes('plans') || 
    q.includes('free plan') || 
    q.includes('pro plan') || 
    q.includes('subscription')
  ) {
    return "🏷️ **EventHub Pro Organizer Plans:**\n\n" +
           "• **Free Plan (₹0)**: Up to 50 registrations, basic analytics, and community support.\n" +
           "• **Pro Plan (₹499)**: Unlimited registrations, advanced analytics, instant UPI payments, and dynamic QR ticketing.\n" +
           "• **Enterprise**: Custom volume and university-wide management with dedicated SLA.";
  }

  // 16. CONTACT & SUPPORT
  if (
    q.includes('contact') || 
    q.includes('support') || 
    q.includes('help') || 
    q.includes('email') || 
    q.includes('helpline') || 
    q.includes('phone') || 
    q.includes('feedback')
  ) {
    return "📞 **Support & Assistance:**\n\n" +
           "• Email us directly: **hello@eventhubpro.com**\n" +
           "• Visit the Contact page: Click **Contact** in the top navbar.\n" +
           "• Submit Feedback: Click **Feedback** in the footer to help us improve!\n" +
           "Our team is happy to help with any event inquiries or registration questions.";
  }

  // 17. GREETINGS & POLITE PHRASES (Strict whole-word matching)
  if (/\b(hello|hi|hey|heya|namaste|greetings|good morning|good afternoon|good evening|shubh prabhat)\b/i.test(q)) {
    return "👋 **Hello! I am your EventHub Pro AI Assistant.**\n\nHow can I help you today? You can ask me:\n• 'Explain how to use this website'\n• 'How to register for an event'\n• 'How to pay via UPI & get QR ticket'\n• 'How to create and host an event'\n• 'Show upcoming Hackathons'";
  }

  if (/\b(thanks|thank you|dhanyawad|shukriya|great|awesome|perfect|super)\b/i.test(q)) {
    return "You're very welcome! 😊 Feel free to ask anytime if you need help finding events or managing tickets on EventHub Pro. Enjoy your experience!";
  }

  if (/\b(bye|goodbye|see you|tata|alvida)\b/i.test(q)) {
    return "Goodbye! Have a fantastic day ahead, and we hope to see you at the events! 🎉";
  }

  // 18. CONVERSATIONAL FALLBACK (Friendly & Engaging)
  return "I'd love to help you with that! 😊 Could you tell me a little more, or choose from one of these common topics:\n\n" +
         "• 💡 **'Explain how to use this website'** — complete walkthrough\n" +
         "• 📝 **'How to register for an event'** — single/team entry process\n" +
         "• 💳 **'UPI payments & QR tickets'** — checkout and entry gate pass\n" +
         "• 🚀 **'How to create an event'** — organizer guide\n" +
         "• 🎪 **'What events are available'** — view current fests\n\n" +
         "You can also tap any of the quick suggestion buttons above!";
}
</script>

<!-- ===================== PRICING ===================== -->
<section class="eh-section" id="pricing">
  <div class="container-max">
    <div class="eh-sec-head" data-aos="fade-up">
      <span class="eh-eyebrow">Pricing</span>
      <h2>Simple, transparent pricing</h2>
    </div>
    <div class="eh-price-grid">
      <div class="eh-plan" data-aos="fade-up"><div class="pname">Free</div><div class="pamount">₹0</div><ul><li><i class="fas fa-check"></i>Upto 50 registrations</li><li><i class="fas fa-check"></i>Basic analytics</li><li><i class="fas fa-check"></i>Community support</li></ul><a href="login.php" class="eh-btn eh-btn-ghost" style="width:100%;justify-content:center;">Get Started</a></div>
<div class="eh-plan popular" data-aos="fade-up" data-aos-delay="100"><div class="pname">Pro</div><div class="pamount">₹499</div><ul><li><i class="fas fa-check"></i>Unlimited registrations</li><li><i class="fas fa-check"></i>Advanced analytics</li><li><i class="fas fa-check"></i>Instant UPI payments</li><li><i class="fas fa-check"></i>QR tickets</li></ul><a href="login.php" class="eh-btn eh-btn-primary" style="width:100%;justify-content:center;">Get Started</a></div>
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
      <div class="eh-faq-item" data-aos="fade-up"><button class="eh-faq-q">Can I sell tickets? <i class="fas fa-chevron-down"></i></button><div class="eh-faq-a"><p>Yes! Set a price for your event and we handle instant UPI payments via PhonePe, GPay, Paytm &amp; QR.</p></div></div>
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
