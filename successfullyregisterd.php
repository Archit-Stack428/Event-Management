<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Successfully Registered</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
      <script type="text/javascript" src="qrcode.js">
</script>
    <script type="text/javascript" src="html5-qrcode.js">
</script>
</head>

<body>
    <div class="text-center" style="height: 450px;background-image: url(&quot;assets/img/pencil.jpeg&quot;);">
        <div style="height: 350px;">
            <nav class="navbar navbar-light navbar-expand-md navigation-clean-button" style="background-color: rgba(0,0,0,0.94);color: rgb(255,255,255);">
                <div class="container"><a class="navbar-brand" href="index.php" style="color: rgb(255,255,255);">MMDU EVENT</a><button data-toggle="collapse" class="navbar-toggler" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="navbar-toggler-icon"></span></button>
                    <div
                        class="collapse navbar-collapse" id="navcol-1">
                        <ul class="nav navbar-nav mr-auto">
                            <li class="nav-item" role="presentation"><a class="nav-link active" href="index.php" style="color: rgb(255,255,255);">Home</a></li>
                            <li class="nav-item" role="presentation"><a class="nav-link" href="gallery.php" style="color: rgb(255,255,255);">Gallery</a></li>
                        </ul><span class="navbar-text actions"> <a class="login" href="#" style="color: rgb(255,255,255);margin-right: 10px;">Log In</a><a class="btn btn-light action-button" role="button" href="#">Sign Up</a></span></div>
        </div>
        </nav>
    </div>
    <div style="max-width: 600px; margin: 0 auto 80px auto; padding: 0 16px;">
        <div class="card text-center border rounded shadow-lg" style="width: 100%; border-radius: 16px; overflow: hidden; background: #fff;">
            <div class="card-body text-center" style="padding: 30px 24px;">
                <h3 class="text-center card-title" style="font-weight: 700; color: #1e293b; margin-bottom: 16px;">Event Ticket &amp; QR Code</h3>
         <?php
include('dbconnect.php');
$txnid = trim($_GET['txnid'] ?? '');
$eventid = (int)($_GET['eventid'] ?? 0);

$proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$url = $proto . $host . $dir . "/success.php?txnid=" . urlencode($txnid) . "&eventid=" . $eventid;

$minimum = 0;
$time = '';
$venue = '';
$event_title = '';

$stmt = $conn->prepare("SELECT event_title, min_team, time, event_venue FROM create_event WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $minimum = $row['min_team'];
    $time = $row['time'];
    $venue = $row['event_venue'];
    $event_title = $row['event_title'];
}
$stmt->close();

$name = '';
$email = '';
$payment_status = 'pending';

if($minimum == 0){
    $stmt = $conn->prepare("SELECT email, name, payment_status FROM singleevent_registration WHERE txn_id = ?");
    $stmt->bind_param('s', $txnid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];
        $payment_status = strtolower($row['payment_status'] ?? 'pending');
    }
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT emails, team_name, payment_status FROM teamevent_registration WHERE txn_id = ?");
    $stmt->bind_param('s', $txnid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row['team_name'];
        $email = $row['emails'];
        $payment_status = strtolower($row['payment_status'] ?? 'pending');
    }
    $stmt->close();
}
?>

        <?php if ($payment_status === 'pending'): ?>
          <div style="margin: 12px auto 20px auto; background: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 14px; text-align: left;">
            <div style="font-weight: 700; color: #854d0e; font-size: 15px; margin-bottom: 4px;">
              <i class="fas fa-clock"></i> Payment Status: Pending Verification
            </div>
            <div style="font-size: 13px; color: #713f12; line-height: 1.4;">
              Your registration has been submitted with UPI Reference / UTR: <strong style="font-family:monospace;"><?php echo htmlspecialchars($txnid); ?></strong>. The organizer will verify your payment before final confirmation.
            </div>
          </div>
        <?php else: ?>
          <div style="margin: 12px auto 20px auto; background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 14px; text-align: left;">
            <div style="font-weight: 700; color: #166534; font-size: 15px; margin-bottom: 4px;">
              <i class="fas fa-check-circle"></i> Payment Status: Confirmed &amp; Paid
            </div>
            <div style="font-size: 13px; color: #14532d; line-height: 1.4;">
              Your payment has been verified! Below is your venue check-in QR code.
            </div>
          </div>
        <?php endif; ?>

        <?php if ($event_title): ?>
          <h5 style="color:#334155; font-weight:600; margin-bottom:4px;"><?php echo htmlspecialchars($event_title); ?></h5>
          <p style="color:#64748b; font-size:14px; margin-bottom:14px;"><strong>Participant:</strong> <?php echo htmlspecialchars($name); ?></p>
        <?php endif; ?>

        <center>
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?php echo urlencode($url); ?>" alt="Registration Attendance QR Code" style="margin:10px auto; display:block; border:8px solid #f8fafc; border-radius:12px; box-shadow:0 8px 24px rgba(0,0,0,0.1); max-width:220px; width:100%;">
        </center>
          
        <div class="text-center" style="margin-top:16px;">
          <p style="font-size: 14px; color:#475569; margin: 0 auto; max-width: 380px;">Take a screenshot of this QR code to present at the venue for instant check-in.</p>
          <a href="index.php" class="btn btn-primary" style="margin-top:16px; border-radius:8px; padding:8px 24px;">Return to Home</a>
        </div>
      </div>
    </div>
  </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>