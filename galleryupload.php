<?php
/* =============================================================
   EVENTHUB PRO — Gallery Upload Page (Phase 4)
   Responsive media dropzone uploader.
   Prepared statements for lookups, session checks, and CSRF token.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('dbconnect.php');

if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    header('Location: login.php');
    exit;
}

// Generate CSRF token if empty
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$username = $_SESSION['username'];
$name = '';

// Retrieve organizer full name safely
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $name = $row['full_name'];
}
$stmt->close();

$events = array();
if ($name !== '') {
    $stmt = $conn->prepare("SELECT event_title FROM create_event WHERE organizer_name = ?");
    $stmt->bind_param('s', $name);
    $stmt->execute();
    $re = $stmt->get_result();
    while ($e = $re->fetch_assoc()) {
        $events[] = $e['event_title'];
    }
    $stmt->close();
}

$load_dashboard_assets = true;
$page_title = 'Gallery Upload | EventHub Pro';
include('header.php');
?>
<div class="eh-dash-shell">
  <!-- ===== Sidebar ===== -->
  <aside class="eh-sidebar" id="ehSidebar">
    <div class="eh-side-title">Menu</div>
    <a class="eh-side-link" href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a class="eh-side-link" href="createevent.php"><i class="fas fa-plus-circle"></i> Create Event</a>
    <a class="eh-side-link active" href="galleryupload.php"><i class="fas fa-images"></i> Gallery Upload</a>
    <a class="eh-side-link" href="log_out.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </aside>

  <main class="eh-dash-main">
    <div class="eh-dash-head-row">
      <div>
        <h1>Gallery Upload</h1>
        <div class="eh-muted">Upload event photos to your gallery.</div>
      </div>
      <a class="eh-btn eh-btn-ghost" href="gallery.php"><i class="fas fa-images"></i> View Gallery</a>
    </div>

    <div class="eh-panel" style="max-width:760px;margin:0 auto;">
      <form action="upload.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        
        <div class="eh-field" style="margin-bottom:20px;">
          <label>Select Event <span class="req">*</span></label>
          <select name="eventtitle" required>
            <option value="">Choose the Event Name</option>
            <?php foreach($events as $e){ echo '<option value="'.htmlspecialchars($e).'">'.htmlspecialchars($e).'</option>'; } ?>
          </select>
        </div>

        <div class="eh-dropzone" id="ehDropzone">
          <i class="fas fa-cloud-upload-alt"></i>
          <p><span class="browse">Click to browse</span> or drag &amp; drop images here</p>
        </div>
        <input type="file" name="files[]" id="ehGalFiles" multiple accept="image/*" style="display:none;">
        <div class="eh-drop-preview" id="ehDropPreview"></div>

        <div style="text-align:center;margin-top:28px;">
          <button type="submit" name="submit_image" class="eh-btn eh-btn-primary"><i class="fas fa-upload"></i> Upload Image</button>
        </div>
      </form>
    </div>
  </main>
</div>
<?php include('footer.php'); ?>
<?php $conn->close(); ?>
