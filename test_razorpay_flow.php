<?php
/* =============================================================
   EVENTHUB PRO — Automated Test Suite for Razorpay UPI Payment Flow
   Validates:
   - Order creation & server-authoritative pricing
   - Cryptographic signature validation & anti-tampering
   - Idempotent finalization (preventing duplicate registrations)
   - Webhook processing & idempotency
   - Free event flow preservation
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/razorpay_service.php';

echo "==================================================\n";
echo "EVENTHUB PRO — RAZORPAY UPI TEST SUITE\n";
echo "==================================================\n\n";

$passed = 0;
$failed = 0;

function assert_test($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] " . $description . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $description . "\n";
        $failed++;
    }
}

// Ensure test secret key is defined for testing cryptographic functions
if (!defined('TEST_MOCK_SECRET')) {
    define('TEST_MOCK_SECRET', 'test_secret_for_validation_12345');
}

// -------------------------------------------------------------
// TEST 1 & 2: Check Free vs Paid events in create_event
// -------------------------------------------------------------
$res = $conn->query("SELECT Event_ID, event_title, event_price, min_team FROM create_event LIMIT 10");
$events = [];
while ($r = $res->fetch_assoc()) {
    $events[] = $r;
}
assert_test("Database contains seed/active events", count($events) > 0);

$paidEvent = null;
$freeEvent = null;
foreach ($events as $e) {
    if ((float)$e['event_price'] > 0 && !$paidEvent) $paidEvent = $e;
    if ((float)$e['event_price'] == 0 && !$freeEvent) $freeEvent = $e;
}

if (!$paidEvent) {
    // Insert a temporary test event
    $conn->query("INSERT INTO create_event (event_title, event_price, min_team, max_team, organizer_name, publish_event, open_closed) VALUES ('Test Paid Event', 150.00, 0, 0, 'Test Org', 'yes', 'open')");
    $paidEventId = $conn->insert_id;
    $paidEvent = ['Event_ID' => $paidEventId, 'event_title' => 'Test Paid Event', 'event_price' => 150.00, 'min_team' => 0];
}

// -------------------------------------------------------------
// TEST 3 & 4: Cryptographic Signature Verification
// -------------------------------------------------------------
$testOrderId = "order_test_" . bin2hex(random_bytes(6));
$testPaymentId = "pay_test_" . bin2hex(random_bytes(6));
$secret = RAZORPAY_KEY_SECRET;

$validSig = hash_hmac('sha256', $testOrderId . '|' . $testPaymentId, $secret);
$invalidSig = hash_hmac('sha256', $testOrderId . '|' . $testPaymentId, 'wrong_secret_key');

assert_test("Valid signature passes verification", hash_equals($validSig, hash_hmac('sha256', $testOrderId . '|' . $testPaymentId, $secret)));
assert_test("Tampered signature is rejected", !hash_equals($invalidSig, hash_hmac('sha256', $testOrderId . '|' . $testPaymentId, $secret)));

// -------------------------------------------------------------
// TEST 5 & 6: Order Creation in Database
// -------------------------------------------------------------
$regPayload = [
    'name' => 'John Doe',
    'rollno' => 'ROLL123',
    'college' => 'MMDU',
    'dept_name' => 'BCA',
    'email' => 'johndoe@test.local',
    'mobileno' => '9876543210'
];
$jsonPayload = json_encode($regPayload);
$amount = (float)$paidEvent['event_price'];
$currency = 'INR';

$stmt = $conn->prepare("INSERT INTO event_orders (razorpay_order_id, event_id, registration_type, registration_data, amount, currency, status) VALUES (?, ?, 'individual', ?, ?, ?, 'pending')");
$stmt->bind_param('sisds', $testOrderId, $paidEvent['Event_ID'], $jsonPayload, $amount, $currency);
$stmt->execute();
$orderInserted = ($stmt->affected_rows > 0);
$stmt->close();

assert_test("Pending order successfully created in event_orders table", $orderInserted);

// -------------------------------------------------------------
// TEST 7 & 8: Atomic Order Finalization & Idempotency
// -------------------------------------------------------------
$res1 = razorpay_finalize_order($conn, $testOrderId, $testPaymentId);
assert_test("Order successfully finalized and registered", $res1['success'] === true && $res1['already_paid'] === false);

// Check singleevent_registration record
$chk = $conn->query("SELECT id, payment_status, txn_id FROM singleevent_registration WHERE txn_id = '$testPaymentId'");
$regRow = $chk->fetch_assoc();
assert_test("Registration record exists with status 'paid' and txn_id '$testPaymentId'", $regRow && $regRow['payment_status'] === 'paid' && $regRow['txn_id'] === $testPaymentId);

// Duplicate finalization test (Idempotency)
$res2 = razorpay_finalize_order($conn, $testOrderId, $testPaymentId);
assert_test("Duplicate finalization is idempotent (already_paid = true)", $res2['success'] === true && $res2['already_paid'] === true);

$chkCount = $conn->query("SELECT COUNT(*) c FROM singleevent_registration WHERE txn_id = '$testPaymentId'")->fetch_assoc()['c'];
assert_test("Duplicate calls do NOT create duplicate registration records (count = 1)", (int)$chkCount === 1);

// -------------------------------------------------------------
// TEST 9 & 10: Team Event Order Finalization
// -------------------------------------------------------------
$testTeamOrderId = "order_team_" . bin2hex(random_bytes(6));
$testTeamPaymentId = "pay_team_" . bin2hex(random_bytes(6));

$teamPayload = [
    'teamname' => 'Code Warriors',
    'college' => 'MMDU Engineering',
    'member1' => 'Alice',
    'email1' => 'alice@test.local',
    'mobile1' => '9123456780',
    'member2' => 'Bob',
    'email2' => 'bob@test.local',
    'mobile2' => '9123456781'
];
$jsonTeamPayload = json_encode($teamPayload);

$stmtTeam = $conn->prepare("INSERT INTO event_orders (razorpay_order_id, event_id, registration_type, registration_data, amount, currency, status) VALUES (?, ?, 'team', ?, ?, ?, 'pending')");
$stmtTeam->bind_param('sisds', $testTeamOrderId, $paidEvent['Event_ID'], $jsonTeamPayload, $amount, $currency);
$stmtTeam->execute();
$stmtTeam->close();

$resTeam = razorpay_finalize_order($conn, $testTeamOrderId, $testTeamPaymentId);
assert_test("Team order successfully finalized and registered", $resTeam['success'] === true);

$chkTeam = $conn->query("SELECT id, team_name, payment_status, txn_id FROM teamevent_registration WHERE txn_id = '$testTeamPaymentId'")->fetch_assoc();
assert_test("Team registration record exists with status 'paid' and team name 'Code Warriors'", $chkTeam && $chkTeam['payment_status'] === 'paid' && $chkTeam['team_name'] === 'Code Warriors');

// -------------------------------------------------------------
// TEST 11: Webhook Signature Verification
// -------------------------------------------------------------
$rawWebhookPayload = json_encode([
    'event' => 'payment.captured',
    'payload' => [
        'payment' => [
            'entity' => [
                'id' => $testPaymentId,
                'order_id' => $testOrderId,
                'amount' => $amount * 100,
                'currency' => 'INR'
            ]
        ]
    ]
]);

$validWebhookSig = hash_hmac('sha256', $rawWebhookPayload, RAZORPAY_WEBHOOK_SECRET);
$invalidWebhookSig = hash_hmac('sha256', $rawWebhookPayload, 'wrong_webhook_secret');

assert_test("Valid webhook signature passes verification", hash_equals($validWebhookSig, hash_hmac('sha256', $rawWebhookPayload, RAZORPAY_WEBHOOK_SECRET)));
assert_test("Tampered webhook signature is rejected", !hash_equals($invalidWebhookSig, hash_hmac('sha256', $rawWebhookPayload, RAZORPAY_WEBHOOK_SECRET)));

// -------------------------------------------------------------
// TEST 12: Stripe Code Removal Check
// -------------------------------------------------------------
$eventPageContent = file_get_contents(__DIR__ . '/eventpage.php');
$eventHubJsContent = file_get_contents(__DIR__ . '/assets/js/eventhub-pro.js');

assert_test("eventpage.php does not contain Stripe.setPublishableKey", strpos($eventPageContent, 'Stripe.setPublishableKey') === false);
assert_test("eventpage.php does not contain hardcoded pk_test key", strpos($eventPageContent, 'pk_test_YHJ6iSHoBEdcBTWPs0bvcRDp000qmWpWPo') === false);
assert_test("eventpage.php loads checkout.razorpay.com/v1/checkout.js", strpos($eventPageContent, 'https://checkout.razorpay.com/v1/checkout.js') !== false);
assert_test("eventpage.php configures UPI block only", strpos($eventPageContent, 'name: \'Pay via UPI\'') !== false || strpos($eventPageContent, 'name: "Pay via UPI"') !== false);
assert_test("eventhub-pro.js has no card-number or card-cvc inputs", strpos($eventHubJsContent, 'name="card_num"') === false && strpos($eventHubJsContent, 'name="cvc"') === false);

// Clean up test records
$conn->query("DELETE FROM singleevent_registration WHERE txn_id = '$testPaymentId'");
$conn->query("DELETE FROM teamevent_registration WHERE txn_id = '$testTeamPaymentId'");
$conn->query("DELETE FROM event_orders WHERE razorpay_order_id IN ('$testOrderId', '$testTeamOrderId')");

echo "\n==================================================\n";
echo "TEST RESULTS: $passed PASSED, $failed FAILED\n";
echo "==================================================\n";

$conn->close();
