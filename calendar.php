<?php
/* =============================================================
   EVENTHUB PRO — Event Calendar Page (Phase 6+)
   Interactive, dynamic calendar listing all published events.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

$page_title = 'Event Calendar — EventHub Pro';
include('header.php');

// Get selected month/year or default to current
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

if ($month < 1 || $month > 12) $month = (int)date('m');
if ($year < 2000 || $year > 2100) $year = (int)date('Y');

// Fetch events for this month/year
$events = [];
$start_date_limit = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$end_date_limit = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-31";

$stmt = $conn->prepare("SELECT Event_ID, event_title, startdate, category, event_price FROM create_event WHERE publish_event='yes' AND startdate BETWEEN ? AND ?");
$stmt->bind_param('ss', $start_date_limit, $end_date_limit);
$stmt->execute();
$res = $stmt->get_result();
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $day = (int)date('d', strtotime($row['startdate']));
        $events[$day][] = $row;
    }
}
$stmt->close();

// Days in month calculation
$num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first_day_of_week = date('w', strtotime("$year-$month-01"));

// Navigation calculation
$prev_month = $month - 1;
$prev_year = $year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$month_name = date('F', strtotime("$year-$month-01"));
?>

<style>
.calendar-container {
  max-width: 1000px;
  margin: 160px auto 80px;
  padding: 0 20px;
}
.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}
.calendar-nav {
  display: flex;
  gap: 10px;
}
.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 8px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 16px;
  backdrop-filter: blur(12px);
}
.calendar-day-label {
  text-align: center;
  font-weight: 600;
  color: var(--eh-accent, #7C3AED);
  padding: 10px 0;
  text-transform: uppercase;
  font-size: 13px;
  letter-spacing: 1px;
}
.calendar-cell {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  border-radius: 10px;
  min-height: 110px;
  padding: 8px;
  display: flex;
  flex-direction: column;
  transition: all 0.3s ease;
}
.calendar-cell.empty {
  background: transparent;
  border-color: transparent;
}
.calendar-cell:not(.empty):hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(255, 255, 255, 0.12);
}
.day-num {
  font-size: 14px;
  font-weight: 700;
  color: var(--eh-muted);
  margin-bottom: 8px;
}
.calendar-cell.today .day-num {
  color: #fff;
  background: var(--eh-accent, #7C3AED);
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}
.cal-event-link {
  display: block;
  font-size: 11px;
  font-weight: 500;
  background: rgba(124, 58, 237, 0.15);
  border-left: 3px solid var(--eh-accent, #7C3AED);
  color: #d8b4fe !important;
  padding: 4px 6px;
  border-radius: 4px;
  margin-bottom: 4px;
  text-decoration: none !important;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: background 0.2s;
}
.cal-event-link:hover {
  background: rgba(124, 58, 237, 0.3);
}
</style>

<div class="calendar-container">
  <div class="calendar-header" data-reveal>
    <div>
      <h1 style="font-size:32px; font-weight:700; margin-bottom:4px; color:#fff;">Event Calendar</h1>
      <p style="color:var(--eh-muted); margin:0;"><?php echo "$month_name $year"; ?></p>
    </div>
    <div class="calendar-nav">
      <a class="eh-btn eh-btn-ghost eh-btn-sm" href="calendar.php?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>"><i class="fas fa-chevron-left"></i> Prev</a>
      <a class="eh-btn eh-btn-ghost eh-btn-sm" href="calendar.php?month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>">Today</a>
      <a class="eh-btn eh-btn-ghost eh-btn-sm" href="calendar.php?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>">Next <i class="fas fa-chevron-right"></i></a>
    </div>
  </div>

  <div class="calendar-grid" data-reveal>
    <!-- Day Labels -->
    <div class="calendar-day-label">Sun</div>
    <div class="calendar-day-label">Mon</div>
    <div class="calendar-day-label">Tue</div>
    <div class="calendar-day-label">Wed</div>
    <div class="calendar-day-label">Thu</div>
    <div class="calendar-day-label">Fri</div>
    <div class="calendar-day-label">Sat</div>

    <!-- Empty cells before start of month -->
    <?php for ($i = 0; $i < $first_day_of_week; $i++): ?>
      <div class="calendar-cell empty"></div>
    <?php endfor; ?>

    <!-- Days in month -->
    <?php 
    $today_day = (int)date('d');
    $today_month = (int)date('m');
    $today_year = (int)date('Y');
    
    for ($day = 1; $day <= $num_days; $day++): 
      $is_today = ($day === $today_day && $month === $today_month && $year === $today_year);
    ?>
      <div class="calendar-cell <?php echo $is_today ? 'today' : ''; ?>">
        <span class="day-num"><?php echo $day; ?></span>
        <?php if (isset($events[$day])): ?>
          <?php foreach ($events[$day] as $ev): ?>
            <a href="eventpage.php?id=<?php echo $ev['Event_ID']; ?>" class="cal-event-link" title="<?php echo htmlspecialchars($ev['event_title']); ?>">
              <?php echo htmlspecialchars($ev['event_title']); ?>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endfor; ?>

    <!-- Empty cells after end of month to balance grid -->
    <?php 
    $total_cells = $first_day_of_week + $num_days;
    $remaining = (7 - ($total_cells % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++): 
    ?>
      <div class="calendar-cell empty"></div>
    <?php endfor; ?>
  </div>
</div>

<?php include('footer.php'); ?>
<?php $conn->close(); ?>
