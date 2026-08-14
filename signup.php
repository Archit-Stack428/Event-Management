<?php
/* =============================================================
   EVENTHUB PRO — Signup Page (Phase 5)
   Modern glassmorphic registration page.
   Session alert states, dynamic fields, client-side matching.
   ============================================================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$page_title = 'Sign Up — EventHub Pro';
include('header.php');
?>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleCredentialResponse(response) {
  var csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'google_login.php');
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function() {
    try {
      var res = JSON.parse(xhr.responseText);
      if (res.success) {
        window.location.href = 'dashboard.php';
      } else {
        alert(res.error || 'Google Sign-In failed.');
      }
    } catch(e) {
      alert('An unexpected server error occurred.');
    }
  };
  xhr.send('credential=' + encodeURIComponent(response.credential) + '&csrf_token=' + encodeURIComponent(csrfToken));
}
</script>

<section class="eh-section" style="padding-top:160px; min-height:85vh; display:flex; align-items:center;">
  <div class="container-max" style="width:100%;">
    
    <div style="max-width:540px; margin:0 auto;" data-reveal>
      <div class="eh-panel" style="padding:40px; border-radius:20px;">
        <h2 style="font-size:28px; font-weight:700; color:#fff; text-align:center; margin-bottom:6px;">Create Account</h2>
        <p style="text-align:center; color:var(--eh-muted); margin-bottom:32px;">Join EventHub Pro as an event organizer</p>
        
        <?php if (isset($_SESSION['error'])): ?>
          <div style="background:rgba(239,68,68,0.15); border:1px solid rgba(239,68,68,0.3); color:#f87171; padding:12px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></span>
          </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
          <div style="background:rgba(16,185,129,0.15); border:1px solid rgba(16,185,129,0.3); color:#34d399; padding:12px 16px; border-radius:10px; font-size:14px; margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
          </div>
        <?php endif; ?>

        <form name="signupform" action="sign_up.php" method="post" onsubmit="return validateSignUp()" class="eh-reg-form" style="margin:0;">
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
            <div class="eh-field">
              <label for="firstname">First Name <span class="req">*</span></label>
              <input type="text" id="firstname" name="firstname" placeholder="John" required style="width:100%;">
            </div>
            <div class="eh-field">
              <label for="lastname">Last Name <span class="req">*</span></label>
              <input type="text" id="lastname" name="lastname" placeholder="Doe" required style="width:100%;">
            </div>
          </div>

          <div class="eh-field full" style="margin-bottom:20px;">
            <label for="your_email">Email Address <span class="req">*</span></label>
            <input type="email" id="your_email" name="mail" placeholder="john.doe@example.com" required style="width:100%;">
          </div>

          <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:30px;">
            <div class="eh-field">
              <label for="password">Password <span class="req">*</span></label>
              <input type="password" id="password" name="password" placeholder="At least 8 chars" required style="width:100%;">
            </div>
            <div class="eh-field">
              <label for="confirm_password">Confirm <span class="req">*</span></label>
              <input type="password" id="confirm_password" name="confirmpassword" placeholder="Verify password" required style="width:100%;">
            </div>
          </div>

          <div style="margin-top:-20px; margin-bottom:24px; text-align:center; font-size:13px;" id="matchMessage"></div>

          <button type="submit" name="submit" class="eh-btn eh-btn-primary" style="width:100%; justify-content:center; padding:12px;"><i class="fas fa-user-plus"></i> Sign Up</button>
        </form>

        <div style="margin-top:20px; display:flex; flex-direction:column; align-items:center; gap:16px; width:100%;">
          <div style="display:flex; align-items:center; width:100%;">
            <hr style="flex:1; border:none; border-top:1px solid rgba(255,255,255,0.1);">
            <span style="padding:0 10px; color:var(--eh-muted); font-size:12px; text-transform:uppercase;">or</span>
            <hr style="flex:1; border:none; border-top:1px solid rgba(255,255,255,0.1);">
          </div>
          
          <div id="g_id_onload"
               data-client_id="<?php echo GOOGLE_CLIENT_ID; ?>"
               data-context="signup"
               data-ux_mode="popup"
               data-callback="handleCredentialResponse"
               data-auto_prompt="false">
          </div>
          <div class="g_id_signin"
               data-type="standard"
               data-shape="rectangular"
               data-theme="outline"
               data-text="signup_with"
               data-size="large"
               data-logo_alignment="left"
               style="width:100%;">
          </div>
        </div>

        <div style="text-align:center; margin-top:24px; font-size:14px; color:var(--eh-muted);">
          Already have an account? <a href="login.php" style="color:var(--eh-accent); font-weight:600;">Log In</a>
        </div>
      </div>
    </div>

  </div>
</section>

<script>
function validateSignUp() {
  var p = document.getElementById('password').value;
  var c = document.getElementById('confirm_password').value;
  var msg = document.getElementById('matchMessage');
  
  if (p.length < 8) {
    msg.style.color = '#f87171';
    msg.textContent = 'Password must be at least 8 characters long.';
    return false;
  }
  
  if (p !== c) {
    msg.style.color = '#f87171';
    msg.textContent = 'Passwords do not match.';
    return false;
  }
  return true;
}

document.getElementById('confirm_password').addEventListener('keyup', function() {
  var p = document.getElementById('password').value;
  var c = document.getElementById('confirm_password').value;
  var msg = document.getElementById('matchMessage');
  if (c === '') {
    msg.textContent = '';
    return;
  }
  if (p === c) {
    msg.style.color = '#34d399';
    msg.textContent = '✓ Passwords match';
  } else {
    msg.style.color = '#f87171';
    msg.textContent = '✗ Passwords do not match';
  }
});
</script>

<?php include('footer.php'); ?>