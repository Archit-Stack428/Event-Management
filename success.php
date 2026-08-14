<?php
/* =============================================================
   EVENTHUB PRO — Secure payment verification success table
   Uses prepared statements to prevent SQL injection.
   All database outputs are escaped using htmlspecialchars.
   ============================================================= */
include('dbconnect.php');
$txnid = trim($_GET['txnid'] ?? '');
$eventid = (int)($_GET['eventid'] ?? 0);

$table = '';

$stmt = $conn->prepare("SELECT event_title, event_id, min_team FROM create_event WHERE event_id = ?");
$stmt->bind_param('i', $eventid);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $min_team = (int)$row['min_team'];
    if ($min_team == 0) {
        $table = '<table border="1">
         <tr>
            <th>EVENT ID</th>
            <td>' . htmlspecialchars($row['event_id']) . '</td>
         </tr>
         <tr>
            <th>EVENT Name</th>
            <td>' . htmlspecialchars($row['event_title']) . '</td>
         </tr>';
         
        $stmt1 = $conn->prepare("SELECT name, roll_no, mobile_no, email, college_name, dept_name, payment_status FROM singleevent_registration WHERE txn_id = ?");
        $stmt1->bind_param('s', $txnid);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        if ($row1 = $result1->fetch_assoc()) {
            $table .= '<tr>
                    <th>Student Name</th>
                    <td>' . htmlspecialchars($row1['name']) . '</td>
                 </tr>
                 <tr>
                    <th>Roll No</th>
                    <td>' . htmlspecialchars($row1['roll_no']) . '</td>
                 </tr>
                 <tr>
                    <th>College Name</th>
                    <td>' . htmlspecialchars($row1['college_name']) . '</td>
                 </tr>
                 <tr>
                    <th>Department Name</th>
                    <td>' . htmlspecialchars($row1['dept_name']) . '</td>
                 </tr>
                 <tr>
                    <th>Email</th>
                    <td>' . htmlspecialchars($row1['email']) . '</td>
                 </tr>
                 <tr>
                    <th>Mobile No</th>
                    <td>' . htmlspecialchars($row1['mobile_no']) . '</td>
                 </tr>
                 <tr>
                    <th>payment Status</th>
                    <td>' . htmlspecialchars($row1['payment_status']) . '</td>
                 </tr>
                 </table>';
        }
        $stmt1->close();
    } else {
        $table = '<table border="1">
         <tr>
            <th>EVENT ID</th>
            <td>' . htmlspecialchars($row['event_id']) . '</td>
         </tr>
         <tr>
            <th>EVENT Name</th>
            <td>' . htmlspecialchars($row['event_title']) . '</td>
         </tr>';
         
        $stmt1 = $conn->prepare("SELECT team_name, student_name, mobile_no, emails, college_name, payment_status FROM teamevent_registration WHERE txn_id = ?");
        $stmt1->bind_param('s', $txnid);
        $stmt1->execute();
        $result1 = $stmt1->get_result();

        if ($row1 = $result1->fetch_assoc()) {
            $table .= '<tr>
                    <th>Team Name</th>
                    <td>' . htmlspecialchars($row1['team_name']) . '</td>
                 </tr>
                 <tr>
                    <th>College Name</th>
                    <td>' . htmlspecialchars($row1['college_name']) . '</td>
                 </tr>
                 <tr>
                    <th>Team Members</th>
                    <td>' . htmlspecialchars($row1['student_name']) . '</td>
                 </tr>
                 <tr>
                    <th>Emails of Team Members </th>
                    <td>' . htmlspecialchars($row1['emails']) . '</td>
                 </tr>
                 <tr>
                    <th>Mobile No of Team Members</th>
                    <td>' . htmlspecialchars($row1['mobile_no']) . '</td>
                 </tr>
                 <tr>
                    <th>payment Status</th>
                    <td>' . htmlspecialchars($row1['payment_status']) . '</td>
                 </tr>
                 </table>';
        }
        $stmt1->close();
    }
}
$stmt->close();
$conn->close();

echo $table;
?>