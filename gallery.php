<?php
/* =============================================================
   EVENTHUB PRO — Gallery (Phase 3)
   Dedicated photo/media gallery. Real DB data only.
   Uses reusable header.php / footer.php + eventhub-pro design.
   ============================================================= */
session_start();
include('dbconnect.php');
$page_title = 'Gallery — EventHub Pro';
$page_desc = 'Browse photos and media from our events — conferences, workshops, fests and more.';

// ---- Categories (DB-driven from gallery) ----
$cats = [];
$res_cat = mysqli_query($conn, "SELECT DISTINCT category FROM gallery WHERE category != '' ORDER BY category ASC");
if ($res_cat) { while ($c = mysqli_fetch_assoc($res_cat)) { $cats[] = $c['category']; } }

// ---- Years (DB-driven) ----
$years = [];
$res_year = mysqli_query($conn, "SELECT DISTINCT YEAR(date) AS y FROM gallery WHERE date IS NOT NULL ORDER BY y DESC");
if ($res_year) { while ($yr = mysqli_fetch_assoc($res_year)) { if ($yr['y']) $years[] = $yr['y']; } }

// ---- Events (DB-driven from gallery) ----
$gal_events = [];
$res_evt = mysqli_query($conn, "SELECT DISTINCT event_name FROM gallery WHERE event_name != '' ORDER BY event_name ASC");
if ($res_evt) { while ($e = mysqli_fetch_assoc($res_evt)) { $gal_events[] = $e['event_name']; } }

// ---- Organizers (DB-driven from gallery) ----
$gal_orgs = [];
$res_org = mysqli_query($conn, "SELECT DISTINCT organizer_name FROM gallery WHERE organizer_name != '' ORDER BY organizer_name ASC");
if ($res_org) { while ($o = mysqli_fetch_assoc($res_org)) { $gal_orgs[] = $o['organizer_name']; } }
?>
<?php include('header.php'); ?>

<!-- ===== Gallery Hero ===== -->
<section class="eh-hero" style="padding-bottom:50px;">
  <div class="container-max">
    <div style="text-align:center;max-width:720px;margin:0 auto;">
      <span class="eh-badge"><i class="fas fa-images"></i> Event Gallery</span>
      <h1 style="font-size:clamp(34px,5vw,58px);">Moments we've<br><span class="grad">brought to life</span></h1>
      <p>Browse photos and highlights from our events across every category.</p>
      <form id="ehGalSearch" class="eh-market-search" autocomplete="off" style="max-width:520px;">
        <input type="text" id="ehGalSearchInput" placeholder="Search by event, organizer or category..." aria-label="Search gallery">
        <button type="submit" class="eh-btn eh-btn-primary"><i class="fas fa-search"></i> Search</button>
      </form>
    </div>
  </div>
</section>

<!-- ===== Filter bar ===== -->
<section style="padding:10px 0 0;">
  <div class="container-max">
    <div class="eh-gallery-filters" data-reveal>
      <!-- Categories -->
      <div class="eh-filter-pills" id="ehGalCatPills">
        <button class="eh-pill active" data-cat="">All Categories</button>
        <?php foreach ($cats as $cat): ?>
          <button class="eh-pill" data-cat="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></button>
        <?php endforeach; ?>
      </div>
      
      <!-- Years -->
      <div class="eh-filter-pills" id="ehGalYearPills" style="margin-top:10px;">
        <button class="eh-pill active" data-year="">All Years</button>
        <?php foreach ($years as $yr): ?>
          <button class="eh-pill" data-year="<?php echo (int)$yr; ?>"><?php echo (int)$yr; ?></button>
        <?php endforeach; ?>
      </div>

      <!-- Events & Organizers Dropdowns -->
      <div style="display:flex; gap:16px; margin-top:20px; flex-wrap:wrap;">
        <?php if (!empty($gal_events)): ?>
          <div class="eh-filter-group" style="margin:0;">
            <select id="ehGalFilterEvent" style="background:var(--eh-surface); border:1px solid var(--eh-border); color:var(--eh-text); padding:8px 16px; border-radius:10px; outline:none; font-size:14px; cursor:pointer;">
              <option value="">All Events</option>
              <?php foreach ($gal_events as $evt): ?>
                <option value="<?php echo htmlspecialchars($evt); ?>"><?php echo htmlspecialchars($evt); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>

        <?php if (!empty($gal_orgs)): ?>
          <div class="eh-filter-group" style="margin:0;">
            <select id="ehGalFilterOrganizer" style="background:var(--eh-surface); border:1px solid var(--eh-border); color:var(--eh-text); padding:8px 16px; border-radius:10px; outline:none; font-size:14px; cursor:pointer;">
              <option value="">All Organizers</option>
              <?php foreach ($gal_orgs as $org): ?>
                <option value="<?php echo htmlspecialchars($org); ?>"><?php echo htmlspecialchars($org); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- ===== Masonry grid ===== -->
<section class="eh-section" id="gallery-main">
  <div class="container-max">
    <div class="eh-result-count" id="ehGalCount" style="margin-bottom:24px;">Loading photos…</div>
    <div class="eh-gal" id="ehGalGrid">
      <div class="eh-empty" id="ehGalEmpty" style="display:none;">
        <i class="fas fa-image"></i>
        <h3>No photos found</h3>
        <p>Try a different search or filter.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== Lightbox ===== -->
<div class="eh-lightbox" id="ehLightbox" aria-hidden="true">
  <button class="eh-lightbox-close" id="ehLightboxClose" aria-label="Close"><i class="fas fa-times"></i></button>
  <button class="eh-lightbox-nav prev" id="ehLightboxPrev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
  <div class="eh-lightbox-body">
    <img id="ehLightboxImg" src="" alt="Gallery photo">
    <div class="eh-lightbox-caption" id="ehLightboxCaption"></div>
  </div>
  <button class="eh-lightbox-nav next" id="ehLightboxNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
</div>

<script>
window.EH_GALLERY = {
  gridEl: 'ehGalGrid',
  countEl: 'ehGalCount',
  emptyEl: 'ehGalEmpty',
  searchInputEl: 'ehGalSearchInput',
  lightboxEl: 'ehLightbox'
};
</script>

<?php include('footer.php'); ?>
<?php $conn->close(); ?>
