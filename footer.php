<?php
/* =============================================================
   EVENTHUB PRO — Reusable Footer (Phase 1)
   ============================================================= */
?>
<!-- ===== Footer ===== -->
<footer class="eh-footer">
  <div class="container-max">
    <div class="eh-footer-grid">
      <div>
        <a href="index.php" class="eh-logo" style="margin-bottom:16px;">
          <span class="eh-logo-badge"><i class="fas fa-bolt"></i></span>
          Event<b>Hub</b> Pro
        </a>
        <p style="color:var(--eh-muted);font-size:14px;max-width:260px;">Organize extraordinary events without the stress. Create, manage, promote and sell tickets.</p>
        <div class="eh-news">
          <input type="email" placeholder="Email for newsletter">
          <button aria-label="Subscribe"><i class="fas fa-paper-plane"></i></button>
        </div>
      </div>
<div>
<h5>Quick Links</h5>
        <a href="index.php">Home</a>
        <a href="events.php">Events</a>
        <a href="events.php">Categories</a>
        <a href="gallery.php">Gallery</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </div>
      <div>
        <h5>Categories</h5>
        <a href="events.php">Technical</a>
        <a href="events.php">Cultural</a>
        <a href="events.php">Sports</a>
        <a href="events.php">Workshops</a>
      </div>
      <div>
<h5>Support</h5>
        <a href="feedback.php">Feedback</a>
        <a href="contact.php">Contact</a>
        <a href="about.php">Help Center</a>
      </div>
      <div>
        <h5>Get In Touch</h5>
        <a href="#">Dr. Virendra Swaroop Institute of Professional Studies</a>
        <a href="mailto:hello@eventhubpro.com">hello@eventhubpro.com</a>
        <a href="#" style="display:flex;gap:14px;margin-top:10px;">
          <i class="fab fa-facebook"></i><i class="fab fa-twitter"></i><i class="fab fa-instagram"></i>
        </a>
      </div>
    </div>
    <div class="eh-footer-bottom">
      <span>© <?php echo date('Y'); ?> EventHub Pro. All rights reserved.</span>
    </div>
  </div>
</footer>

<!-- jQuery + Bootstrap -->
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>

<!-- AOS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js" defer></script>

<!-- GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js" defer></script>

<!-- Lenis -->
<script src="https://unpkg.com/lenis@1.1.13/dist/lenis.min.js" defer></script>

<!-- tsParticles -->
<script src="https://cdn.jsdelivr.net/npm/tsparticles@2.12.0/tsparticles.bundle.min.js" defer></script>

<!-- Swiper -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>

<!-- EVENTHUB PRO scripts -->
<script src="assets/js/eventhub-pro.js" defer></script>

<?php if (!empty($load_dashboard_assets)): ?>
<!-- Chart.js (dashboard analytics only) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
<!-- Dashboard scripts -->
<script src="assets/js/dashboard-pro.js" defer></script>
<?php endif; ?>
</body>
</html>
