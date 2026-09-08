<?php
/* =============================================================
   EVENTHUB PRO — Razorpay Payment Verification Endpoint
   Responsibilities:
   - Accepts razorpay_order_id, razorpay_payment_id, razorpay_signature
   - Retrieves authoritative order from event_orders table
   - Verifies HMAC-SHA256 signature using database-stored order ID
   - Executes atomic transaction to confirm registration
   - Returns redirect URL for confirmed attendance QR ticket
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

$orderId   = trim($_POST['razorpay_order_id'] ?? '');
$paymentId = trim($_POST['razorpay_payment_id'] ?? '');
$signature = trim($_POST['razorpay_signature'] ?? '');

if (empty($orderId) || empty($paymentId) || empty($signature)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required payment verification parameters.']);
    exit;
}

// 1. Verify existence of the order in event_orders
$stmt = $conn->prepare("SELECT id, razorpay_order_id, status, event_id FROM event_orders WHERE razorpay_order_id = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error preparing verification query.']);
    exit;
}
$stmt->bind_param('s', $orderId);
$stmt->execute();
$res = $stmt->get_result();
$orderRow = $res->fetch_assoc();
$stmt->close();

if (!$orderRow) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Order record not found in system.']);
    exit;
}

$storedOrderId = $orderRow['razorpay_order_id'];

// 2. Cryptographic signature verification using database-stored order ID
$isValid = razorpay_verify_payment_signature($storedOrderId, $paymentId, $signature);
if (!$isValid) {
    // Record failed status if still pending
    if ($orderRow['status'] === 'pending') {
        $stmtFail = $conn->prepare("UPDATE event_orders SET status = 'failed' WHERE razorpay_order_id = ?");
        if ($stmtFail) {
            $stmtFail->bind_param('s', $storedOrderId);
            $stmtFail->execute();
            $stmtFail->close();
        }
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => 'Payment signature verification failed. Registration cannot be confirmed.'
    ]);
    exit;
}

// 3. Atomically finalize the order and create registration record
$finalResult = razorpay_finalize_order($conn, $storedOrderId, $paymentId);

if (!$finalResult['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $finalResult['error'] ?? 'Could not finalize registration record.'
    ]);
    exit;
}

$eventId = (int)$finalResult['event_id'];
$txnId   = $finalResult['txn_id'];

$redirectUrl = "successfullyregisterd.php?txnid=" . urlencode($txnId) . "&eventid=" . $eventId;

echo json_encode([
    'success'      => true,
    'message'      => 'Payment verified and registration confirmed!',
    'redirect_url' => $redirectUrl,
    'event_id'     => $eventId,
    'txn_id'       => $txnId
]);
$conn->close();
exit;
