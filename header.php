<?php
/* =============================================================
   EVENTHUB PRO — Reusable Header
   Includes navbar + libs. DB-aware login state via $_SESSION.
   Set $load_dashboard_assets = true before include to load
   dashboard-only CSS (Phase 4).
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<title><?php echo isset($page_title) ? $page_title : 'EventHub Pro'; ?></title>
<meta name="description" content="<?php echo isset($page_desc) ? htmlspecialchars($page_desc) : 'Organize extraordinary events without the stress. Create, manage, promote and sell tickets.'; ?>">
<meta name="theme-color" content="#070b14">
<link rel="icon" href="website-logo.png" type="image/png">

<!-- Open Graph -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="EventHub Pro">
<meta property="og:title" content="<?php echo isset($page_title) ? htmlspecialchars($page_title) : 'EventHub Pro'; ?>">
<meta property="og:description" content="<?php echo isset($page_desc) ? htmlspecialchars($page_desc) : 'Organize extraordinary events without the stress. Create, manage, promote and sell tickets.'; ?>">
<meta property="og:image" content="website-logo.png">
<meta name="twitter:card" content="summary_large_image">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Bootstrap 4 -->
<link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">

<!-- AOS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">

<!-- Swiper -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

<!-- EVENTHUB PRO styles -->
<link rel="stylesheet" href="assets/css/eventhub-pro.css">
<?php if (!empty($load_dashboard_assets)): ?>
<link rel="stylesheet" href="assets/css/dashboard-pro.css">
<?php endif; ?>
</head>
<body>
<div class="eh-bg-fx">
  <div class="eh-mesh"></div>
  <div class="eh-blur-circle c1"></div>
  <div class="eh-blur-circle c2"></div>
  <div id="eh-particles"></div>
  <div class="eh-mouse-light"></div>
</div>

<!-- ===== Navbar (glass) ===== -->
<nav class="eh-nav">
  <div class="container-max eh-nav-inner">
    <a href="index.php" class="eh-logo">
      <span class="eh-logo-badge"><i class="fas fa-bolt"></i></span>
      Event<b>Hub</b> Pro
    </a>

    <div class="eh-nav-links" id="ehNavLinks">
      <a href="index.php">Home</a>
      <a href="events.php" class="eh-nav-events">Events</a>
      <a href="events.php" class="eh-nav-categories">Categories</a>
      <a href="gallery.php" class="eh-nav-gallery">Gallery</a>
      <a href="about.php" class="eh-nav-about">About</a>
      <a href="contact.php" class="eh-nav-contact">Contact</a>
    </div>

    <div class="eh-nav-actions">
      <input type="text" class="eh-search" placeholder="Search events...">
      <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== ''): ?>
        <a class="eh-btn eh-btn-ghost" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a class="eh-btn eh-btn-ghost" href="log_out.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      <?php else: ?>
        <a class="eh-btn eh-btn-ghost" href="login.php"><i class="fas fa-user"></i> Login</a>
      <?php endif; ?>
      <a class="eh-btn eh-btn-primary" href="<?php echo (isset($_SESSION['username']) && $_SESSION['username'] !== '') ? 'createevent.php' : 'login.php'; ?>"><i class="fas fa-plus"></i> Create Event</a>
      <button class="eh-nav-toggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
    </div>
</nav>
