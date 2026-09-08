<?php
/* =============================================================
   EVENTHUB PRO — Razorpay Webhook Endpoint
   Responsibilities:
   - Reads raw HTTP request body before JSON decoding
   - Verifies X-Razorpay-Signature using RAZORPAY_WEBHOOK_SECRET
   - Idempotently processes 'order.paid' and 'payment.captured'
   - Triggers shared order finalization if frontend callback was missed
   ============================================================= */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
    exit;
}

require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/razorpay_service.php';

// 1. Read raw request body
$rawPayload = file_get_contents('php://input');
$signature  = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (empty($rawPayload) || empty($signature)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing payload or signature header.']);
    exit;
}

// 2. Validate webhook signature
if (!razorpay_verify_webhook_signature($rawPayload, $signature)) {
    http_response_code(400);
    error_log("Razorpay Webhook: Invalid signature received.");
    echo json_encode(['error' => 'Invalid signature']);
    exit;
}

// 3. Decode JSON after signature verification
$event = json_decode($rawPayload, true);
if (!is_array($event) || empty($event['event'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON structure']);
    exit;
}

$eventType = $event['event'];
error_log("Razorpay Webhook: Received event '{$eventType}'");

// 4. Process payment.captured or order.paid
if ($eventType === 'payment.captured' || $eventType === 'order.paid') {
    $paymentEntity = $event['payload']['payment']['entity'] ?? [];
    $orderEntity   = $event['payload']['order']['entity'] ?? [];

    $orderId   = $paymentEntity['order_id'] ?? ($orderEntity['id'] ?? '');
    $paymentId = $paymentEntity['id'] ?? '';

    if (!empty($orderId) && !empty($paymentId)) {
        $finalResult = razorpay_finalize_order($conn, $orderId, $paymentId);
        if ($finalResult['success']) {
            http_response_code(200);
            echo json_encode([
                'status'       => 'success',
                'already_paid' => $finalResult['already_paid'] ?? false,
                'order_id'     => $orderId
            ]);
            $conn->close();
            exit;
        } else {
            error_log("Razorpay Webhook finalization error for order {$orderId}: " . ($finalResult['error'] ?? 'unknown'));
        }
    }
}

// Acknowledge receipt of other webhook events
http_response_code(200);
echo json_encode(['status' => 'ignored']);
$conn->close();
exit;
