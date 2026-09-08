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
    <div style="margin-left: 200px;margin-right: 200px;">
        <div class="card text-center border rounded shadow-lg" style="width: 700px;margin-left: 200px;margin-bottom: 100px;">
            <div class="card-body text-center">
                <h4 class="text-center card-title">Verification QR Code</h4>
         <?php
include('dbconnect.php');
$rollno = trim($_GET['rollno'] ?? '');
$eventid = (int)($_GET['eventid'] ?? 0);
$mobilno = trim($_GET['mobileno'] ?? '');

$url = "eventmanagement2315.000webhostapp.com/success1.php?rollno=" . urlencode($rollno) . "&eventid=" . $eventid;
$minimum = 0;
$venue = '';
$time = '';

$stmt = $conn->prepare("SELECT min_team, time, event_venue FROM create_event WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $minimum = $row['min_team'];
    $venue = $row['event_venue'];
    $time = $row['time'];
}
$stmt->close();

$name = '';
$email = '';

if($minimum == 0){
    
    $stmt = $conn->prepare("SELECT email, name FROM singleevent_registration WHERE roll_no = ? AND event_id = ?");
    $stmt->bind_param('si', $rollno, $eventid);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $name = $row['name'];
        $email = $row['email'];
    }
    $stmt->close();
    
    if ($email !== '') {
        $to_email = $email; 
        $from = 'anshul.zero@gmail.com';
        $subject = 'Successfully Registered';
        $message = "Dear " . $name . ",\nYou have successfully registered the event. Below are the details of the event:\n\nTime: " . $time . "\nVenue: " . $venue . "\nWe will be pleased to see you there...\n\nThank You";
        $headers = "FROM: Event HUB <".$from.">\r\n";
        mail($to_email,$subject,$message,$headers);
    }
}
              <center>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo urlencode($url); ?>" alt="Registration QR Code" style="margin:20px auto; display:block; border:8px solid #fff; border-radius:10px; box-shadow:0 10px 25px rgba(0,0,0,0.15); max-width:200px;">
              </center>
                
                
                <div class="text-center">
                    <p class="text-center" style="width: 300px;margin-left: 175px;font-size: 18px;">Make sure to get the Screen Shot of the QR code to verify your registration at Venue</p>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>