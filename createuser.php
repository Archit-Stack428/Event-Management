<?php
require_once __DIR__ . '/dbconnect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$load_dashboard_assets = true;
$page_title = 'Create User | EventHub Pro';
include('header.php');
?>
<div class="eh-dash-shell">
  <aside class="eh-sidebar" id="ehSidebar">
    <div class="eh-side-title">Menu</div>
    <a class="eh-side-link" href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a class="eh-side-link" href="createevent.php"><i class="fas fa-plus-circle"></i> Create Event</a>
    <a class="eh-side-link" href="galleryupload.php"><i class="fas fa-images"></i> Gallery Upload</a>
    <a class="eh-side-link active" href="createuser.php"><i class="fas fa-user-plus"></i> Create User</a>
    <a class="eh-side-link" href="log_out.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="eh-dash-main">
    <div class="eh-dash-head-row">
      <div>
        <h1>Create User</h1>
        <div class="eh-muted">Generate a new organizer account for EventHub Pro.</div>
      </div>
      <a class="eh-btn eh-btn-ghost" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="eh-panel eh-wizard" style="max-width:620px;margin:0 auto;">
      <?php if (isset($_SESSION['error'])): ?>
        <div class="eh-alert eh-alert-error" style="background:rgba(239,68,68,0.12);color:#ef4444;border:1px solid rgba(239,68,68,0.3);padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:14px;font-weight:500;">
          <i class="fas fa-exclamation-circle" style="font-size:18px;"></i>
          <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
        </div>
      <?php endif; ?>

      <?php if (isset($_SESSION['success'])): ?>
        <div class="eh-alert eh-alert-success" style="background:rgba(34,197,94,0.12);color:#22c55e;border:1px solid rgba(34,197,94,0.3);padding:14px 18px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:14px;font-weight:500;">
          <i class="fas fa-check-circle" style="font-size:18px;"></i>
          <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
        </div>
      <?php endif; ?>

      <form action="createusersave.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <div class="eh-form-grid">
          <div class="eh-field">
            <label>First Name <span class="req">*</span></label>
            <input type="text" name="first_name" placeholder="First name" required>
          </div>
          <div class="eh-field">
            <label>Last Name</label>
            <input type="text" name="last_name" placeholder="Last name">
          </div>
          <div class="eh-field full">
            <label>Email Address <span class="req">*</span></label>
            <input type="email" name="email" placeholder="user@example.com" required>
            <small style="color:#64748b;font-size:12px;margin-top:4px;display:block;">This email will also serve as their login username.</small>
          </div>
          <div class="eh-field full">
            <label>Password <span class="req">*</span></label>
            <input type="password" name="password" placeholder="At least 8 characters" required>
          </div>
          <div class="eh-field full">
            <label>Role</label>
            <select name="role">
              <option value="Organizer">Organizer</option>
            </select>
          </div>
        </div>
        <div style="text-align:center;margin-top:28px;">
          <button type="submit" name="submit" class="eh-btn eh-btn-primary" style="padding:12px 28px;font-size:15px;">
            <i class="fas fa-user-plus"></i> Create User
          </button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php include('footer.php'); ?>
