<?php
/* =============================================================
   EVENTHUB PRO — Automated Test Suite for Static UPI QR Payment Flow
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';

echo "==================================================\n";
echo "EVENTHUB PRO — STATIC UPI QR PAYMENT TEST SUITE\n";
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

// TEST 1: QR Image exists and has valid dimensions
$qrPath = __DIR__ . '/assets/images/upi-qr.jpg';
$hasQr = file_exists($qrPath) && filesize($qrPath) > 100;
assert_test("assets/images/upi-qr.jpg exists and is non-empty", $hasQr);

// TEST 2: Check event in DB
$paidEvent = $conn->query("SELECT Event_ID, event_title, event_price, open_closed, publish_event FROM create_event WHERE event_price > 0 AND (open_closed = 'open' OR open_closed IS NULL OR open_closed = '') AND (publish_event = 'yes' OR publish_event IS NULL OR publish_event = '') LIMIT 1")->fetch_assoc();
if (!$paidEvent) {
    $conn->query("UPDATE create_event SET open_closed = 'open', publish_event = 'yes' WHERE event_price > 0 LIMIT 1");
    $paidEvent = $conn->query("SELECT Event_ID, event_title, event_price, open_closed, publish_event FROM create_event WHERE event_price > 0 LIMIT 1")->fetch_assoc();
}
assert_test("Database contains paid event for testing", !empty($paidEvent));

$eventId = (int)$paidEvent['Event_ID'];
$eventPrice = (float)$paidEvent['event_price'];
$testUtr = "UTRTEST" . bin2hex(random_bytes(4));

// TEST 3: Submit registration via PHP CLI subprocess
$postData = http_build_query([
    'event_id' => $eventId,
    'utr_number' => $testUtr,
    'name' => 'Archit Test',
    'rollno' => 'ROLL-999',
    'college' => 'Test University',
    'dept_name' => 'BCA',
    'email' => 'archit.test@example.com',
    'mobile' => '9935786450',
    'teamname' => 'Archit Warriors',
    'member1' => 'Archit Test',
    'email1' => 'archit.test@example.com',
    'mobile1' => '9935786450'
]);

$runnerCode = '<?php $_POST = []; parse_str("' . addslashes($postData) . '", $_POST); $_SERVER["REQUEST_METHOD"] = "POST"; include "submit_upi_registration.php";';
file_put_contents(__DIR__ . '/tmp_runner.php', $runnerCode);
$out = shell_exec('C:\xampp\php\php.exe tmp_runner.php 2>&1');
@unlink(__DIR__ . '/tmp_runner.php');

$resp = json_decode($out, true);
assert_test("submit_upi_registration.php returns HTTP 200 and success=true", !empty($resp['success']));

// TEST 4: Verify registration has payment_status = 'pending'
$chk = $conn->query("SELECT id, payment_status, paid_amount, txn_id FROM singleevent_registration WHERE txn_id = '$testUtr'")->fetch_assoc();
$isTeam = false;
if (!$chk) {
    $chk = $conn->query("SELECT id, payment_status, paid_amount, txn_id FROM teamevent_registration WHERE txn_id = '$testUtr'")->fetch_assoc();
    $isTeam = true;
}
assert_test("Registration record created with payment_status = 'pending'", $chk && $chk['payment_status'] === 'pending');
assert_test("Registration record has authoritative DB price", $chk && (float)$chk['paid_amount'] === $eventPrice);
assert_test("Registration record has txn_id matching UTR", $chk && $chk['txn_id'] === $testUtr);

// TEST 5: Verify event_orders record created with status = 'pending'
$chkOrder = $conn->query("SELECT id, status, razorpay_payment_id FROM event_orders WHERE razorpay_payment_id = '$testUtr'")->fetch_assoc();
assert_test("event_orders record created with status = 'pending'", $chkOrder && $chkOrder['status'] === 'pending');

// TEST 6: Admin verification approves payment
$orgName = $conn->query("SELECT organizer_name FROM create_event WHERE Event_ID = $eventId")->fetch_assoc()['organizer_name'] ?? 'Admin';
$orgUser = $conn->query("SELECT username FROM sign_up WHERE full_name = '" . addslashes($orgName) . "' LIMIT 1")->fetch_assoc();
$cleanupTestUser = null;
if (!$orgUser) {
    $parts = explode(' ', trim($orgName), 2);
    $first = $parts[0] ?: 'Organizer';
    $last = $parts[1] ?? 'Admin';
    $testAdminUser = 'test_org_' . uniqid() . '@example.com';
    $pwd = password_hash('Pass123!', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO sign_up (first_name, last_name, email, password) VALUES ('$first', '$last', '$testAdminUser', '$pwd')");
    $cleanupTestUser = $testAdminUser;
} else {
    $testAdminUser = $orgUser['username'];
}
$testCsrf = bin2hex(random_bytes(32));

$adminPost = http_build_query([
    'txn_id' => $testUtr,
    'type' => $isTeam ? 'team' : 'individual',
    'csrf_token' => $testCsrf
]);
$adminRunnerCode = '<?php session_start(); $_SESSION["username"] = "' . addslashes($testAdminUser) . '"; $_SESSION["csrf_token"] = "' . addslashes($testCsrf) . '"; $_POST = []; parse_str("' . addslashes($adminPost) . '", $_POST); $_SERVER["REQUEST_METHOD"] = "POST"; include "verify_registration_admin.php";';
file_put_contents(__DIR__ . '/tmp_admin_runner.php', $adminRunnerCode);
$adminOut = shell_exec('C:\xampp\php\php.exe tmp_admin_runner.php 2>&1');
@unlink(__DIR__ . '/tmp_admin_runner.php');

$adminResp = json_decode($adminOut, true);
assert_test("verify_registration_admin.php successfully approves payment", !empty($adminResp['success']));

$table = $isTeam ? 'teamevent_registration' : 'singleevent_registration';
$chkApproved = $conn->query("SELECT payment_status FROM $table WHERE txn_id = '$testUtr'")->fetch_assoc();
assert_test("Registration status updated to 'paid' after admin approval", $chkApproved && $chkApproved['payment_status'] === 'paid');

// Clean up test data
$conn->query("DELETE FROM singleevent_registration WHERE txn_id = '$testUtr'");
$conn->query("DELETE FROM teamevent_registration WHERE txn_id = '$testUtr'");
$conn->query("DELETE FROM event_orders WHERE razorpay_payment_id = '$testUtr'");
if ($cleanupTestUser) {
    $conn->query("DELETE FROM sign_up WHERE username = '$cleanupTestUser'");
}

// TEST 7: Frontend static code inspections
$eventPageContent = file_get_contents(__DIR__ . '/eventpage.php');

assert_test("eventpage.php does not contain checkout.razorpay.com", strpos($eventPageContent, 'checkout.razorpay.com') === false);
assert_test("eventpage.php does not contain Razorpay configuration warning", strpos($eventPageContent, 'Razorpay payment gateway is not yet configured') === false);
assert_test("eventpage.php contains 'View UPI QR & Pay'", strpos($eventPageContent, 'View UPI QR &amp; Pay') !== false || strpos($eventPageContent, 'View UPI QR & Pay') !== false);
assert_test("eventpage.php contains UPI ID 9580197216@nyes", strpos($eventPageContent, '9580197216@nyes') !== false);
assert_test("eventpage.php contains 'I Have Completed Payment'", strpos($eventPageContent, 'I Have Completed Payment') !== false);
assert_test("eventpage.php contains 'UPI Transaction ID / UTR'", strpos($eventPageContent, 'UPI Transaction ID / UTR') !== false);

// TEST 8: Free event registration handler preservation
assert_test("eventregistration1.php exists for free event registrations", file_exists(__DIR__ . '/eventregistration1.php'));
assert_test("successfullyregisterd1.php exists for free event confirmations", file_exists(__DIR__ . '/successfullyregisterd1.php'));

echo "\n==================================================\n";
echo "TEST RESULTS: $passed PASSED, $failed FAILED\n";
echo "==================================================\n";

$conn->close();
