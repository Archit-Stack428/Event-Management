<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
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
      <div><h1>Create User</h1><div class="eh-muted">Generate a new organizer account.</div>
      <a class="eh-btn eh-btn-ghost" href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="eh-panel eh-wizard" style="max-width:620px;margin:0 auto;">
      <form action="createusersave.php" method="post" enctype="multipart/form-data" novalidate>
        <div class="eh-form-grid">
          <div class="eh-field full"><label>Full Name <span class="req">*</span></label><input type="text" name="name" required></div>
          <div class="eh-field full"><label>Email <span class="req">*</span></label><input type="email" name="email" required></div>
          <div class="eh-field"><label>Username <span class="req">*</span></label><input type="text" name="username" required></div>
          <div class="eh-field"><label>Password <span class="req">*</span></label><input type="password" name="password" required></div>
          <div class="eh-field full"><label>Role</label>
            <select name="role"><option value="Organizer">Organizer</option></select>
          </div>
        <div style="text-align:center;margin-top:28px;">
          <button type="submit" name="submit" class="eh-btn eh-btn-primary"><i class="fas fa-user-plus"></i> Create User</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php include('footer.php'); ?>
