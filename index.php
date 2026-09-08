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
  <button onclick="toggleChatbot()" id="ehChatBtn" style="width:60px; height:60px; border-radius:50%; background:var(--eh-accent, #7C3AED); border:none; color:#fff; font-size:24px; cursor:pointer; box-shadow:0 8px 24px rgba(124,58,237,0.4); display:flex; align-items:center; justify-content:center; transition:transform 0.3s;">
    <i class="fas fa-comments"></i>
  </button>
  
  <!-- Chat Box Panel -->
  <div id="ehChatBox" class="eh-panel" style="display:none; width:360px; height:450px; position:absolute; bottom:80px; right:0; padding:0; border-radius:20px; border:1px solid rgba(255,255,255,0.08); background:rgba(9,9,11,0.95); backdrop-filter:blur(15px); flex-direction:column; overflow:hidden; box-shadow:0 15px 35px rgba(0,0,0,0.4);">
    <!-- Chat Header -->
    <div style="background:rgba(255,255,255,0.02); padding:16px 20px; border-bottom:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
      <div style="display:flex; align-items:center; gap:10px;">
        <div style="width:10px; height:10px; border-radius:50%; background:#10b981;"></div>
        <span style="font-weight:700; color:#fff; font-size:15px;">EventHub AI Assistant</span>
      </div>
      <button onclick="toggleChatbot()" style="background:none; border:none; color:var(--eh-muted); cursor:pointer;"><i class="fas fa-times"></i></button>
    </div>
    
    <!-- Chat Messages -->
    <div id="ehChatMessages" style="flex:1; padding:20px; overflow-y:auto; display:flex; flex-direction:column; gap:12px; font-size:13px; color:#fff; text-align:left;">
      <div style="background:rgba(255,255,255,0.04); padding:10px 14px; border-radius:14px 14px 14px 0; align-self:flex-start; max-width:80%;">
        Hello! I'm your EventHub Pro assistant. How can I help you today?
      </div>
    </div>
    
    <!-- Chat Input -->
    <form onsubmit="sendChatMessage(event)" style="padding:15px; border-top:1px solid rgba(255,255,255,0.05); display:flex; gap:10px; margin:0;">
      <input type="text" id="ehChatInput" placeholder="Type a message..." required style="flex:1; background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); padding:10px 14px; border-radius:10px; color:#fff; font-size:13px; outline:none;">
      <button type="submit" style="background:var(--eh-accent, #7C3AED); border:none; color:#fff; width:36px; height:36px; border-radius:10px; cursor:pointer; display:flex; align-items:center; justify-content:center;"><i class="fas fa-paper-plane"></i></button>
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

function sendChatMessage(e) {
  e.preventDefault();
  var input = document.getElementById('ehChatInput');
  var msg = input.value.trim();
  if (msg === '') return;
  
  addChatMessage(msg, 'user');
  input.value = '';
  
  setTimeout(function() {
    var response = getChatbotResponse(msg);
    addChatMessage(response, 'bot');
  }, 600);
}

function addChatMessage(text, sender) {
  var msgsContainer = document.getElementById('ehChatMessages');
  var msgDiv = document.createElement('div');
  
  if (sender === 'user') {
    msgDiv.style.background = 'var(--eh-accent, #7C3AED)';
    msgDiv.style.borderRadius = '14px 14px 0 14px';
    msgDiv.style.alignSelf = 'flex-end';
  } else {
    msgDiv.style.background = 'rgba(255, 255, 255, 0.04)';
    msgDiv.style.borderRadius = '14px 14px 14px 0';
    msgDiv.style.alignSelf = 'flex-start';
  }
  
  msgDiv.style.padding = '10px 14px';
  msgDiv.style.maxWidth = '80%';
  msgDiv.innerText = text;
  
  msgsContainer.appendChild(msgDiv);
  msgsContainer.scrollTop = msgsContainer.scrollHeight;
}

function getChatbotResponse(query) {
  query = query.toLowerCase();
  if (query.includes('hackathon') || query.includes('coding')) {
    return 'The MMDU National Hackathon 2026 starts on Oct 15. It is a 24-hour team coding event with an entry fee of ₹199 and cash prizes up to ₹50,000!';
  }
  if (query.includes('music') || query.includes('symphony') || query.includes('cultural')) {
    return 'Symphony Music Fest 2026 is scheduled for Nov 20 at the Open Air Theater. Entry is free!';
  }
  if (query.includes('register') || query.includes('how to register')) {
    return 'To register for an event, explore the Events list, click on any event card, and select the Register button!';
  }
  if (query.includes('payment') || query.includes('upi') || query.includes('pay')) {
    return 'We accept instant, secure payments via UPI! You can pay using your preferred UPI app (PhonePe, Google Pay, Paytm, BHIM) or by scanning the UPI QR code during checkout.';
  }
  if (query.includes('ticket') || query.includes('qr')) {
    return 'Once registered, a dynamic QR ticket will be shown to you. Take a screenshot and bring it to the venue for scanning.';
  }
  if (query.includes('sports') || query.includes('spardha')) {
    return 'The Spardha Annual Sports Meet starts on Dec 5 at the University Sports Complex. Tickets are ₹99.';
  }
  if (query.includes('hello') || query.includes('hi')) {
    return 'Hello! How can I help you find or register for events today?';
  }
  return "I'm here to help! You can ask me about events, tickets, UPI payments, or hackathon details. Feel free to ask!";
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
