<?php
/* =============================================================
   EVENTHUB PRO — Razorpay Create Order Endpoint (UPI-Only)
   Responsibilities:
   - Validates POST request and event ID
   - Reads authoritative event price from create_event (never trusts client amount)
   - Converts INR to paise server-side
   - Creates Razorpay order via REST API
   - Saves pending order into event_orders table
   - Returns client payload with public key only
   ============================================================= */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/razorpay_service.php';

// Check if Razorpay is configured
if (!razorpay_is_configured()) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Razorpay payment gateway is not yet configured. Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in config.php (Test Mode).'
    ]);
    exit;
}

$eventId = (int)($_POST['event_id'] ?? ($_GET['id1'] ?? ($_POST['id1'] ?? 0)));

if ($eventId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing Event ID.']);
    exit;
}

// Fetch event details with prepared statement
$stmt = $conn->prepare("SELECT Event_ID, event_title, event_price, min_team, max_team, publish_event, open_closed FROM create_event WHERE Event_ID = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error preparing event query.']);
    exit;
}
$stmt->bind_param('i', $eventId);
$stmt->execute();
$res = $stmt->get_result();
$event = $res->fetch_assoc();
$stmt->close();

if (!$event) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Event not found.']);
    exit;
}

if (($event['open_closed'] ?? 'open') === 'closed') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Registrations are currently closed for this event.']);
    exit;
}

$price = (float)$event['event_price'];
if ($price <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'This is a free event. Please use the free registration form.']);
    exit;
}

$minTeam = (int)$event['min_team'];
$regType = ($minTeam > 0) ? 'team' : 'individual';

$customerName = '';
$customerEmail = '';
$customerContact = '';

// Validate registration data based on type
if ($regType === 'individual') {
    $name    = trim($_POST['name'] ?? '');
    $rollNo  = trim($_POST['rollno'] ?? '');
    $college = trim($_POST['college'] ?? '');
    $dept    = trim($_POST['dept_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $mobile  = trim($_POST['mobile'] ?? ($_POST['mobileno'] ?? ''));

    if ($name === '' || $rollNo === '' || $college === '' || $email === '' || $mobile === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please fill all required individual registration fields.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please provide a valid email address.']);
        exit;
    }

    $customerName    = $name;
    $customerEmail   = $email;
    $customerContact = $mobile;

    $registrationPayload = [
        'name'      => $name,
        'rollno'    => $rollNo,
        'college'   => $college,
        'dept_name' => $dept,
        'email'     => $email,
        'mobileno'  => $mobile
    ];
} else {
    $teamName = trim($_POST['teamname'] ?? ($_POST['team_name'] ?? ''));
    $college  = trim($_POST['college'] ?? '');
    $m1Name   = trim($_POST['member1'] ?? '');
    $m1Email  = trim($_POST['email1'] ?? '');
    $m1Mobile = trim($_POST['mobile1'] ?? '');

    if ($teamName === '' || $college === '' || $m1Name === '' || $m1Email === '' || $m1Mobile === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please provide team name, college, and Leader (Member 1) details.']);
        exit;
    }

    if (!filter_var($m1Email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Please provide a valid email for Member 1.']);
        exit;
    }

    $customerName    = $m1Name . " (" . $teamName . ")";
    $customerEmail   = $m1Email;
    $customerContact = $m1Mobile;

    $registrationPayload = $_POST;
    $registrationPayload['teamname'] = $teamName;
    $registrationPayload['college']  = $college;
}

// Convert INR price to paise
$amountPaise = (int)round($price * 100);
$receipt = 'rcpt_ev' . $eventId . '_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 6);

$notes = [
    'event_id'          => (string)$eventId,
    'event_title'       => substr($event['event_title'], 0, 40),
    'registration_type' => $regType
];

// Call Razorpay API to create order
$orderResult = razorpay_create_order_api($amountPaise, $receipt, $notes);
if (!$orderResult['success']) {
    http_response_code(502);
    echo json_encode(['success' => false, 'error' => $orderResult['error']]);
    exit;
}

$razorpayOrder = $orderResult['order'];
$razorpayOrderId = $razorpayOrder['id'];

// Save order into event_orders table
$jsonData = json_encode($registrationPayload, JSON_UNESCAPED_UNICODE);
$statusPending = 'pending';
$currency = 'INR';

$stmtIns = $conn->prepare("INSERT INTO event_orders (razorpay_order_id, event_id, registration_type, registration_data, amount, currency, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
if (!$stmtIns) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to initialize order record: ' . $conn->error]);
    exit;
}

$stmtIns->bind_param('sissdss', $razorpayOrderId, $eventId, $regType, $jsonData, $price, $currency, $statusPending);
if (!$stmtIns->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save pending order: ' . $stmtIns->error]);
    exit;
}
$stmtIns->close();
$conn->close();

// Return response to frontend
echo json_encode([
    'success'           => true,
    'razorpay_order_id' => $razorpayOrderId,
    'amount_paise'      => $amountPaise,
    'amount_formatted'  => number_format($price, 2),
    'currency'          => 'INR',
    'razorpay_key_id'   => RAZORPAY_KEY_ID,
    'event_title'       => $event['event_title'],
    'customer_name'     => $customerName,
    'customer_email'    => $customerEmail,
    'customer_contact'  => $customerContact
]);
exit;
