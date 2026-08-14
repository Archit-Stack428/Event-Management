<?php
/* =============================================================
   EVENTHUB PRO — Events Marketplace (Phase 3)
   Discover & register for events. Real DB data only.
   Uses reusable header.php / footer.php + eventhub-pro design.
   Backend (auth, registration, Stripe) untouched.
   ============================================================= */
session_start();
include('dbconnect.php');
$page_title = 'Events — EventHub Pro';
$page_desc = 'Discover and register for conferences, workshops, hackathons, college fests and more.';

// ---- Categories (DB-driven) ----
$cats = [];
$res_cat = mysqli_query($conn, "SELECT category, COUNT(*) AS cnt FROM create_event WHERE category != '' AND publish_event='yes' GROUP BY category ORDER BY cnt DESC");
if ($res_cat) { while ($c = mysqli_fetch_assoc($res_cat)) { $cats[] = $c; } }

// ---- Organs & venues (DB-driven) ----
$venues = [];
$res_ven = mysqli_query($conn, "SELECT DISTINCT event_venue FROM create_event WHERE event_venue != '' ORDER BY event_venue ASC LIMIT 30");
if ($res_ven) { while ($v = mysqli_fetch_assoc($res_ven)) { $venues[] = $v['event_venue']; } }

$orgs = [];
$res_org = mysqli_query($conn, "SELECT DISTINCT organizer_name FROM create_event WHERE organizer_name != '' ORDER BY organizer_name ASC LIMIT 30");
if ($res_org) { while ($o = mysqli_fetch_assoc($res_org)) { $orgs[] = $o['organizer_name']; } }

// ---- Totals (for hero stats) ----
$total_events = 0; $total_upcoming = 0; $total_free = 0;
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes'");
if ($r && $rr = mysqli_fetch_assoc($r)) { $total_events = (int)$rr['c']; }
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes' AND startdate > CURDATE()");
if ($r && $rr = mysqli_fetch_assoc($r)) { $total_upcoming = (int)$rr['c']; }
$r = mysqli_query($conn, "SELECT COUNT(*) AS c FROM create_event WHERE publish_event='yes' AND (event_price = 0 OR event_price IS NULL)");
if ($r && $rr = mysqli_fetch_assoc($r)) { $total_free = (int)$rr['c']; }
?>
<?php include('header.php'); ?>

<!-- ===== Marketplace Hero ===== -->
<section class="eh-hero" style="padding-bottom:60px;">
  <div class="container-max">
    <div style="text-align:center;max-width:760px;margin:0 auto;">
      <span class="eh-badge"><i class="fas fa-calendar-check"></i> <?php echo $total_events; ?> events live</span>
      <h1 style="font-size:clamp(34px,5vw,58px);">Discover.<br><span class="grad">Register.</span> Attend.</h1>
      <p>Browse the full lineup of events across every category — from workshops and hackathons to cultural fests and corporate summits.</p>
      <form id="ehMarketSearch" class="eh-market-search" autocomplete="off">
        <input type="text" id="ehSearchInput" placeholder="Search by title, category, organizer or venue..." aria-label="Search events">
        <button type="submit" class="eh-btn eh-btn-primary"><i class="fas fa-search"></i> Search</button>
      </form>
    </div>
  </div>
</section>

<!-- ===== Stats strip ===== -->
<section style="padding:20px 0;">
  <div class="container-max">
    <div class="eh-stats" style="grid-template-columns:repeat(3,1fr);">
      <div class="eh-stat"><div class="num" data-target="<?php echo $total_events; ?>" data-suffix="+">0</div><div class="lbl">Total Events</div></div>
      <div class="eh-stat"><div class="num" data-target="<?php echo $total_upcoming; ?>" data-suffix="+">0</div><div class="lbl">Upcoming</div></div>
      <div class="eh-stat"><div class="num" data-target="<?php echo $total_free; ?>" data-suffix="+">0</div><div class="lbl">Free Events</div></div>
    </div>
  </div>
</section>

<!-- ===== Filters + listing ===== -->
<section class="eh-section" id="market">
  <div class="container-max">
    <div class="eh-market-layout">
      <!-- Sidebar filters -->
      <aside class="eh-filters" data-reveal>
        <div class="eh-filters-head">
          <span><i class="fas fa-sliders-h"></i> Filters</span>
          <button class="eh-clear-filters" id="ehClearFilters">Clear all</button>
        </div>

        <div class="eh-filter-group">
          <label>Category</label>
          <select id="ehFilterCategory">
            <option value="">All Categories</option>
            <?php foreach ($cats as $cat): ?>
              <?php $selected = (isset($_GET['category']) && $_GET['category'] === $cat['category']) ? 'selected' : ''; ?>
              <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($cat['category']); ?> (<?php echo $cat['cnt']; ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="eh-filter-group">
          <label>Event Type</label>
          <select id="ehFilterType">
            <option value="">All Types</option>
            <option value="single">Individual</option>
            <option value="team">Team</option>
          </select>
        </div>

        <div class="eh-filter-group">
          <label>Pricing</label>
          <select id="ehFilterPrice">
            <option value="">All</option>
            <option value="free">Free</option>
            <option value="paid">Paid</option>
          </select>
        </div>

        <div class="eh-filter-group">
          <label>Availability</label>
          <select id="ehFilterStatus">
            <option value="">All</option>
            <option value="open">Open Registration</option>
            <option value="closed">Closed</option>
          </select>
        </div>

        <div class="eh-filter-group">
          <label>When</label>
          <select id="ehFilterWhen">
            <option value="">Any Time</option>
            <option value="upcoming">Upcoming</option>
            <option value="ongoing">Ongoing</option>
            <option value="completed">Completed</option>
          </select>
        </div>

        <?php if (!empty($venues)): ?>
        <div class="eh-filter-group">
          <label>Venue</label>
          <select id="ehFilterVenue">
            <option value="">All Venues</option>
            <?php foreach ($venues as $v): ?>
              <?php $selected = (isset($_GET['venue']) && $_GET['venue'] === $v) ? 'selected' : ''; ?>
              <option value="<?php echo htmlspecialchars($v); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($v); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>

        <?php if (!empty($orgs)): ?>
        <div class="eh-filter-group">
          <label>Organizer</label>
          <select id="ehFilterOrganizer">
            <option value="">All Organizers</option>
            <?php foreach ($orgs as $o): ?>
              <?php $selected = (isset($_GET['organizer']) && $_GET['organizer'] === $o) ? 'selected' : ''; ?>
              <option value="<?php echo htmlspecialchars($o); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($o); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </aside>

      <!-- Results -->
      <div class="eh-market-results">
        <div class="eh-market-toolbar">
          <div class="eh-result-count" id="ehResultCount">Loading events…</div>
          <div class="eh-sort">
            <label for="ehSort">Sort</label>
            <select id="ehSort">
              <option value="latest">Newest First</option>
              <option value="oldest">Oldest First</option>
              <option value="price_asc">Price: Low → High</option>
              <option value="price_desc">Price: High → Low</option>
              <option value="closing">Closing Soon</option>
            </select>
          </div>
        </div>

        <!-- Skeleton loader -->
        <div class="eh-grid" id="ehEventsGrid">
          <?php for ($i = 0; $i < 6; $i++): ?>
            <div class="eh-card eh-skeleton">
              <div class="eh-skeleton-img"></div>
              <div class="eh-card-body">
                <div class="eh-skeleton-line w70"></div>
                <div class="eh-skeleton-line w50"></div>
                <div class="eh-skeleton-line w90"></div>
                <div class="eh-skeleton-line w40"></div>
              </div>
            </div>
          <?php endfor; ?>
        </div>

        <!-- Empty state -->
        <div class="eh-empty" id="ehEmptyState" style="display:none;">
          <i class="fas fa-search"></i>
          <h3>No events found</h3>
          <p>Try adjusting your search or clearing some filters.</p>
        </div>

        <div style="text-align:center;margin-top:40px;">
          <button id="ehLoadMore" class="eh-btn eh-btn-ghost" style="display:none;"><i class="fas fa-spinner"></i> Load More</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section class="eh-cta-section">
  <div class="container-max">
    <div class="eh-cta-box" data-aos="zoom-in">
      <h2>Organizing your own event?</h2>
      <p>Create, publish and sell tickets in minutes with EventHub Pro.</p>
      <a href="login.php" class="eh-btn"><i class="fas fa-plus"></i> Create an Event</a>
    </div>
  </div>
</section>

<script>
window.EH_MARKET = {
  searchInputEl: 'ehSearchInput',
  gridEl: 'ehEventsGrid',
  emptyEl: 'ehEmptyState',
  countEl: 'ehResultCount',
  loadMoreEl: 'ehLoadMore'
};
</script>

<?php include('footer.php'); ?>
<?php $conn->close(); ?>
