<?php
/* =============================================================
   Free event registration handler
   Uses prepared statements to prevent SQL injection.
   ============================================================= */
include('dbconnect.php');
$id1 = (int)($_GET['id1'] ?? 0);

$minimum = 0;
$stmt = $conn->prepare("SELECT min_team, max_team FROM create_event WHERE event_id = ?");
$stmt->bind_param('i', $id1);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $minimum = $row['min_team'];
}
$stmt->close();

if (isset($_POST['submit'])) {

    if ($minimum == '0') {
        $name     = trim($_POST['name'] ?? '');
        $roll_no  = trim($_POST['rollno'] ?? '');
        $college  = trim($_POST['college'] ?? '');
        $dept     = trim($_POST['dept_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $mobile   = trim($_POST['mobileno'] ?? '');

        if ($name !== '' && $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $conn->prepare("INSERT INTO singleevent_registration (event_id, name, roll_no, college_name, dept_name, email, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, '0', 'inr', '0', 'succeeded')");
            $stmt->bind_param('issssss', $id1, $name, $roll_no, $college, $dept, $email, $mobile);
            $stmt->execute();
            $stmt->close();

            $url = "successfullyregisterd1.php?rollno=" . urlencode($roll_no) . "&eventid=" . $id1;
            echo "<script>window.location='" . $url . "'</script>";
        }
    } else {
        $teamname = trim($_POST['team_name'] ?? '');
        $college  = trim($_POST['college'] ?? '');
        $studentname = implode(",", array_map('trim', (array)($_POST['name'] ?? [])));
        $emails      = implode(",", array_map('trim', (array)($_POST['email'] ?? [])));
        $mobile_no   = implode(",", array_map('trim', (array)($_POST['mobile'] ?? [])));

        if ($teamname !== '' && $emails !== '') {
            $stmt = $conn->prepare("INSERT INTO teamevent_registration (event_id, team_name, college_name, student_name, emails, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, '0', 'inr', '0', 'succeeded')");
            $stmt->bind_param('isssss', $id1, $teamname, $college, $studentname, $emails, $mobile_no);
            $stmt->execute();
            $stmt->close();

            $url = "successfullyregisterd1.php?mobileno=" . urlencode($mobile_no) . "&eventid=" . $id1;
            echo "<script>window.location='" . $url . "'</script>";
        }
    }
}

$conn->close();
