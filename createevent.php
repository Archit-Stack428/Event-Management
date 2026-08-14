<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}
$load_dashboard_assets = true;
$page_title = 'Create Event | EventHub Pro';
include('header.php');
?>

<div class="eh-dash-shell">
  <aside class="eh-sidebar" id="ehSidebar">
    <div class="eh-side-title">Menu</div>
    <a class="eh-side-link" href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a class="eh-side-link active" href="createevent.php"><i class="fas fa-plus-circle"></i> Create Event</a>
    <a class="eh-side-link" href="galleryupload.php"><i class="fas fa-images"></i> Gallery Upload</a>
    <a class="eh-side-link" href="createuser.php"><i class="fas fa-user-plus"></i> Create User</a>
    <a class="eh-side-link" href="log_out.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="eh-dash-main">
    <div class="eh-dash-head-row">
      <div><h1>Create Event</h1><div class="eh-muted">Build a new event step by step.</div>
    </div>

    <div class="eh-wizard" id="ehWizard">
      <div class="eh-steps">
        <div class="eh-step-pill active"><span class="n">1</span> Basics</div>
        <div class="eh-step-pill"><span class="n">2</span> Schedule &amp; Venue</div>
        <div class="eh-step-pill"><span class="n">3</span> Rules &amp; Prizes</div>
        <div class="eh-step-pill"><span class="n">4</span> Media</div>
      </div>

      <form action="createeventsave.php" method="post" enctype="multipart/form-data" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <!-- Step 1 -->
        <div class="eh-step-pane active">
          <div class="eh-panel">
            <div class="eh-form-grid">
              <div class="eh-field full"><label>Event Name <span class="req">*</span></label><input type="text" name="eventtitle" placeholder="e.g. Annual Tech Fest" required></div>
              <div class="eh-field full"><label>Description <span class="req">*</span></label><textarea name="desc" placeholder="Describe your event..." rows="6" required></textarea></div>
              <div class="eh-field"><label>Category <span class="req">*</span></label>
                <select name="category" required>
                  <option value="">Choose the Category</option>
                  <option value="music">Music</option><option value="dance">Dance</option><option value="buisness">Business</option>
                  <option value="fine arts">Fine Arts</option><option value="theatre">Theatre</option><option value="health">Health</option>
                  <option value="hospitality">Hospitality</option><option value="fashion">Fashion</option><option value="sports">Sports</option>
                  <option value="law">Law</option><option value="technical">Technical</option><option value="literary">Literary</option>
                  <option value="others">Others</option>
                </select>
              </div>
              <div class="eh-field"><label>Event Type <span class="req">*</span></label>
                <select name="eventtype" required>
                  <option value="">Choose the Event Type</option>
                  <option value="Single Participant">Single Participant</option>
                  <option value="Team Event">Team Event</option>
                </select>
              </div>
              <div class="eh-field"><label>Min Team Size</label><input type="number" name="min_team_size" placeholder="0"></div>
              <div class="eh-field"><label>Max Team Size</label><input type="number" name="max_team_size" placeholder="0"></div>
            </div>
          </div>
        </div>

        <!-- Step 2 -->
        <div class="eh-step-pane">
          <div class="eh-panel">
            <div class="eh-form-grid">
              <div class="eh-field full"><label>Event Date (Start - End) <span class="req">*</span></label><input type="text" name="eventdate" placeholder="Select date range (YYYY-MM-DD - YYYY-MM-DD)" required></div>
              <div class="eh-field"><label>Venue <span class="req">*</span></label><input type="text" name="venue" placeholder="Venue" required></div>
              <div class="eh-field"><label>Time <span class="req">*</span></label><input type="time" name="time" value="00:00:00" required></div>
              <div class="eh-field full"><label>Registration Price (₹)</label><input type="number" name="price" placeholder="0 for free" value="0"></div>
            </div>
          </div>
        </div>

        <!-- Step 3 -->
        <div class="eh-step-pane">
          <div class="eh-panel">
            <div class="eh-field full"><label>Rules</label>
              <div id="ehRuleWrap">
                <div class="eh-dynamic-row">
                  <input type="text" name="rules[]" placeholder="Rule 1" class="eh-input" required>
                  <button type="button" class="eh-action-btn danger" aria-label="Remove"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <button type="button" class="eh-dynamic-add" data-dynamic-add="ehRuleWrap" data-name="rules[]" data-ph="Rule"><i class="fas fa-plus"></i> Add Rule</button>
            </div>
            <div class="eh-field full" style="margin-top:20px;"><label>Prizes</label>
              <div id="ehPrizeWrap">
                <div class="eh-dynamic-row">
                  <input type="text" name="prize[]" placeholder="Prize 1" class="eh-input">
                  <button type="button" class="eh-action-btn danger" aria-label="Remove"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <button type="button" class="eh-dynamic-add" data-dynamic-add="ehPrizeWrap" data-name="prize[]" data-ph="Prize"><i class="fas fa-plus"></i> Add Prize</button>
            </div>
            <div class="eh-field full" style="margin-top:20px;"><label>Sponsors</label><input type="text" name="sponsors" placeholder="Sponsors (comma separated)"></div>
          </div>
        </div>

        <!-- Step 4 -->
        <div class="eh-step-pane">
          <div class="eh-panel">
            <div class="eh-field full"><label>Event Thumbnail <span class="req">*</span></label>
              <input type="file" name="thumbnail" id="ehThumbInput" accept="image/*" required>
              <img id="ehThumbPreview" class="eh-thumb-preview" alt="Preview">
            </div>
          </div>
        </div>

        <div class="eh-wiz-nav">
          <button type="button" class="eh-btn eh-btn-ghost" id="ehWizPrev" style="visibility:hidden;"><i class="fas fa-arrow-left"></i> Back</button>
          <button type="button" class="eh-btn eh-btn-primary" id="ehWizNext">Next <i class="fas fa-arrow-right"></i></button>
          <button type="submit" class="eh-btn eh-btn-primary" id="ehWizSubmit" name="submit" style="display:none;"><i class="fas fa-check"></i> Save Event</button>
        </div>
      </form>
    </div>
  </main>
</div>

<script>
(function () {
  'use strict';
  var dateInput = document.querySelector('input[name="eventdate"]');
  if (!dateInput) return;

  function isValidDateRange(val) {
    var m = String(val).trim().match(/^(\d{4})-(\d{2})-(\d{2}) - (\d{4})-(\d{2})-(\d{2})$/);
    if (!m) return false;
    var s = new Date(+m[1], +m[2] - 1, +m[3]);
    var e = new Date(+m[4], +m[5] - 1, +m[6]);
    if (s.getFullYear() !== +m[1] || s.getMonth() !== +m[2] - 1 || s.getDate() !== +m[3]) return false;
    if (e.getFullYear() !== +m[4] || e.getMonth() !== +m[5] - 1 || e.getDate() !== +m[6]) return false;
    return true;
  }

  function showError(msg) {
    var field = dateInput.closest('.eh-field');
    if (!field) return;
    field.classList.add('invalid');
    var err = field.querySelector('.eh-field-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'eh-field-error';
      err.style.cssText = 'color:#ff5a5a;font-size:12px;margin-top:6px;';
      field.appendChild(err);
    }
    err.textContent = msg;
  }

  function clearError() {
    var field = dateInput.closest('.eh-field');
    if (!field) return;
    field.classList.remove('invalid');
    var err = field.querySelector('.eh-field-error');
    if (err) err.textContent = '';
  }

  dateInput.addEventListener('input', clearError);

  // Validate before the wizard advances out of Step 2 (Schedule & Venue)
  var nextBtn = document.getElementById('ehWizNext');
  if (nextBtn) {
    nextBtn.addEventListener('click', function (e) {
      var step2 = document.querySelectorAll('.eh-step-pane')[1];
      if (step2 && step2.classList.contains('active')) {
        if (!isValidDateRange(dateInput.value)) {
          showError('Please enter the date range as YYYY-MM-DD - YYYY-MM-DD (e.g. 2026-08-09 - 2026-08-10).');
          e.stopImmediatePropagation();
          return;
        }
        clearError();
      }
    });
  }

  // Final guard on submit in case Step 2 was skipped/auto-advanced
  var form = dateInput.closest('form');
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!isValidDateRange(dateInput.value)) {
        e.preventDefault();
        showError('Please enter the date range as YYYY-MM-DD - YYYY-MM-DD (e.g. 2026-08-09 - 2026-08-10).');
        if (typeof window.ehShowStep === 'function') {
          window.ehShowStep(1);
        }
      }
    });
  }
})();
</script>
<?php include('footer.php'); ?>
