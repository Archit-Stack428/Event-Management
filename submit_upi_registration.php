<?php
/* =============================================================
   EVENTHUB PRO — Static UPI QR Payment & Registration Handler
   - Enforces server-authoritative event pricing from DB.
   - Sets payment_status = 'pending' (NEVER auto-paid).
   - Sanitizes and stores UTR / UPI transaction ID in txn_id.
   - Supports single and team registrations via prepared statements.
   - Preserves compatibility with successfullyregisterd.php.
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? 0);
$utr = trim($_POST['utr_number'] ?? '');

if ($eventId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing Event ID.']);
    exit;
}

if ($utr === '' || strlen($utr) < 6 || strlen($utr) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Please enter a valid UPI Reference / UTR Number (6-50 characters).']);
    exit;
}

// Clean UTR (alphanumeric and hyphens only)
$cleanUtr = preg_replace('/[^a-zA-Z0-9\-_]/', '', $utr);
if (strlen($cleanUtr) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid UTR format. Please check the transaction ID from your UPI app.']);
    exit;
}

// Check for duplicate UTR to prevent replay/reuse attacks
$dupStmt = $conn->prepare("SELECT id FROM singleevent_registration WHERE txn_id = ? UNION SELECT id FROM teamevent_registration WHERE txn_id = ? LIMIT 1");
$dupStmt->bind_param('ss', $cleanUtr, $cleanUtr);
$dupStmt->execute();
$dupRes = $dupStmt->get_result();
if ($dupRes && $dupRes->num_rows > 0) {
    $dupStmt->close();
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'This UPI Reference / UTR Number has already been submitted for a registration.']);
    exit;
}
$dupStmt->close();

// Check event in DB
$stmt = $conn->prepare("SELECT Event_ID, event_title, event_price, min_team, max_team, open_closed, publish_event FROM create_event WHERE Event_ID = ?");
$stmt->bind_param('i', $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Event not found.']);
    exit;
}

if (strtolower($event['open_closed'] ?? '') === 'closed' || strtolower($event['publish_event'] ?? '') === 'no') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Registration is currently closed for this event.']);
    exit;
}

$price = (float)$event['event_price'];
$minTeam = (int)$event['min_team'];
$paymentStatus = 'pending'; // STRICT: always pending for static QR flow
$txnId = $cleanUtr;

$conn->begin_transaction();

try {
    if ($minTeam === 0) {
        // Single Participant Registration
        $name = trim($_POST['name'] ?? '');
        $rollNo = trim($_POST['rollno'] ?? '');
        $college = trim($_POST['college'] ?? '');
        $deptName = trim($_POST['dept_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $mobileNo = trim($_POST['mobile'] ?? ($_POST['mobileno'] ?? ''));

        if ($name === '' || $email === '' || $mobileNo === '') {
            throw new Exception('Please fill all required personal details (Name, Email, Mobile).');
        }

        $ins = $conn->prepare("INSERT INTO singleevent_registration (name, roll_no, college_name, dept_name, email, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status, event_id) VALUES (?, ?, ?, ?, ?, ?, ?, 'inr', ?, ?, ?)");
        $ins->bind_param('ssssssdssi', $name, $rollNo, $college, $deptName, $email, $mobileNo, $price, $txnId, $paymentStatus, $eventId);
        if (!$ins->execute()) {
            throw new Exception('Failed to record registration: ' . $ins->error);
        }
        $ins->close();

    } else {
        // Team Registration
        $teamName = trim($_POST['teamname'] ?? '');
        $college = trim($_POST['college'] ?? '');
        $deptName = trim($_POST['dept_name'] ?? 'Other');

        $members = [];
        $emails = [];
        $mobiles = [];

        for ($i = 1; $i <= 10; $i++) {
            if (!empty($_POST['member' . $i])) {
                $members[] = trim($_POST['member' . $i]);
            }
            if (!empty($_POST['email' . $i])) {
                $emails[] = trim($_POST['email' . $i]);
            }
            if (!empty($_POST['mobile' . $i])) {
                $mobiles[] = trim($_POST['mobile' . $i]);
            }
        }

        if (empty($members) && !empty($_POST['name'])) {
            $members[] = trim($_POST['name']);
        }
        if (empty($emails) && !empty($_POST['email'])) {
            $emails[] = trim($_POST['email']);
        }
        if (empty($mobiles) && !empty($_POST['mobile'])) {
            $mobiles[] = trim($_POST['mobile']);
        }

        if ($teamName === '' || empty($members)) {
            throw new Exception('Please provide team name and team member details.');
        }

        $membersStr = implode(', ', $members);
        $emailsStr = implode(', ', $emails);
        $mobilesStr = implode(', ', $mobiles);

        $ins = $conn->prepare("INSERT INTO teamevent_registration (team_name, college_name, student_name, emails, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status, event_id) VALUES (?, ?, ?, ?, ?, ?, 'inr', ?, ?, ?)");
        $ins->bind_param('sssssdssi', $teamName, $college, $membersStr, $emailsStr, $mobilesStr, $price, $txnId, $paymentStatus, $eventId);
        if (!$ins->execute()) {
            throw new Exception('Failed to record team registration: ' . $ins->error);
        }
        $ins->close();
    }

    // Also record in event_orders for dashboard overview
    $orderKey = 'UPI-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
    $orderJson = json_encode([
        'utr' => $cleanUtr,
        'post_data' => array_diff_key($_POST, ['utr_number' => ''])
    ]);
    $type = ($minTeam === 0) ? 'individual' : 'team';

    $insOrder = $conn->prepare("INSERT INTO event_orders (razorpay_order_id, razorpay_payment_id, event_id, registration_type, registration_data, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?, 'INR', 'pending')");
    $insOrder->bind_param('ssissd', $orderKey, $cleanUtr, $eventId, $type, $orderJson, $price);
    $insOrder->execute();
    $insOrder->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Registration recorded! Verification pending.',
        'redirect_url' => 'successfullyregisterd.php?txnid=' . urlencode($txnId) . '&eventid=' . $eventId . '&status=pending'
    ]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
