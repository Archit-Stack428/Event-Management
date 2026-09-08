<?php
/* =============================================================
   EVENTHUB PRO — Admin / Organizer Payment Verification Endpoint
   Allows authenticated event organizers to manually approve pending UPI payments.
   Enforces:
   - POST method only (direct URL access protected).
   - Session authentication via $_SESSION['username'].
   - CSRF token validation against $_SESSION['csrf_token'].
   - Role & Event ownership authorization (only the event creator/organizer can approve).
   - Atomic database update keeping registration and event_orders consistent.
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=UTF-8');

// 1. Direct URL access protection (Must be POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed. Direct URL access is prohibited.']);
    exit;
}

// 2. Authentication check using project's canonical session key
if (!isset($_SESSION['username']) || trim($_SESSION['username']) === '') {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required. Please log in as an organizer.']);
    exit;
}

// 3. CSRF token validation
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Security check failed (invalid or missing CSRF token).']);
    exit;
}

// 4. Validate organizer account in database
$username = trim($_SESSION['username']);
$stmt = $conn->prepare("SELECT full_name FROM sign_up WHERE username = ?");
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
$userRow = $res->fetch_assoc();
$stmt->close();

if (!$userRow || empty($userRow['full_name'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized account.']);
    exit;
}
$organizerName = $userRow['full_name'];

// 5. Sanitize and validate request parameters
$txnId = trim($_POST['txn_id'] ?? '');
$regType = trim($_POST['type'] ?? 'individual');

if ($txnId === '' || strlen($txnId) > 50) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing transaction ID.']);
    exit;
}

// Clean txnId
$cleanTxnId = preg_replace('/[^a-zA-Z0-9\-_]/', '', $txnId);

// 6. Check registration existence and retrieve event_id
$eventId = 0;
if ($regType === 'team') {
    $stmt = $conn->prepare("SELECT event_id, payment_status FROM teamevent_registration WHERE txn_id = ?");
    $stmt->bind_param('s', $cleanTxnId);
    $stmt->execute();
    $reg = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    $stmt = $conn->prepare("SELECT event_id, payment_status FROM singleevent_registration WHERE txn_id = ?");
    $stmt->bind_param('s', $cleanTxnId);
    $stmt->execute();
    $reg = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$reg) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Registration record not found for the provided UTR.']);
    exit;
}

$eventId = (int)$reg['event_id'];

// 7. Verify event ownership (Organizers can ONLY approve payments for their own events)
$stmt = $conn->prepare("SELECT organizer_name FROM create_event WHERE Event_ID = ?");
$stmt->bind_param('i', $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Associated event not found.']);
    exit;
}

if ($event['organizer_name'] !== $organizerName) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: You do not have permission to approve registrations for this event.']);
    exit;
}

// 8. Atomic Database Update
$conn->begin_transaction();
try {
    if ($regType === 'team') {
        $stmt = $conn->prepare("UPDATE teamevent_registration SET payment_status = 'paid' WHERE txn_id = ? AND event_id = ?");
        $stmt->bind_param('si', $cleanTxnId, $eventId);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conn->prepare("UPDATE singleevent_registration SET payment_status = 'paid' WHERE txn_id = ? AND event_id = ?");
        $stmt->bind_param('si', $cleanTxnId, $eventId);
        $stmt->execute();
        $stmt->close();
    }

    // Keep event_orders consistent
    $stmt2 = $conn->prepare("UPDATE event_orders SET status = 'paid', paid_at = NOW() WHERE (razorpay_payment_id = ? OR razorpay_order_id = ?) AND event_id = ?");
    $stmt2->bind_param('ssi', $cleanTxnId, $cleanTxnId, $eventId);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Payment verified and registration confirmed!']);
    exit;
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
