<?php
/* =============================================================
   EVENTHUB PRO — Comprehensive Security Audit Test Suite
   ============================================================= */
require_once __DIR__ . '/dbconnect.php';
require_once __DIR__ . '/config.php';

echo "========================================================\n";
echo "EVENTHUB PRO — SECURITY AUDIT VERIFICATION SUITE\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function assert_sec($title, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] " . $title . "\n";
        $passed++;
    } else {
        echo "[FAIL] " . $title . "\n";
        $failed++;
    }
}

// Setup a test organizer and test normal user in database
$testOrganizerUsername = 'sec_organizer_' . bin2hex(random_bytes(3)) . '@test.com';
$testNormalUsername = 'sec_normal_' . bin2hex(random_bytes(3)) . '@test.com';
$hashedPwd = password_hash('SecPass123!', PASSWORD_BCRYPT);

$conn->query("INSERT INTO sign_up (first_name, last_name, email, password) VALUES ('Alice', 'Organizer', '$testOrganizerUsername', '$hashedPwd')");
$orgRow = $conn->query("SELECT full_name FROM sign_up WHERE username = '$testOrganizerUsername'")->fetch_assoc();
$orgFullName = $orgRow['full_name'];

$conn->query("INSERT INTO sign_up (first_name, last_name, email, password) VALUES ('Bob', 'NormalUser', '$testNormalUsername', '$hashedPwd')");

// Create test event owned by Alice
$conn->query("INSERT INTO create_event (organizer_name, event_title, event_price, min_team, max_team, publish_event, open_closed) VALUES ('$orgFullName', 'Security Audit Event', 150, 0, 0, 'yes', 'open')");
$testEventId = $conn->insert_id;
$testUtr = 'SECUTR' . bin2hex(random_bytes(4));
$csrfToken = bin2hex(random_bytes(32));

// Helper to run subprocess
function run_script($file, $method, $post = [], $session = []) {
    $script = '<?php ';
    if (!empty($session)) {
        $script .= 'session_start(); ';
        foreach ($session as $k => $v) {
            $script .= '$_SESSION["' . addslashes($k) . '"] = "' . addslashes($v) . '"; ';
        }
    }
    $script .= '$_SERVER["REQUEST_METHOD"] = "' . $method . '"; ';
    $postStr = http_build_query($post);
    $script .= '$_POST = []; parse_str("' . addslashes($postStr) . '", $_POST); ';
    $script .= 'include "' . $file . '";';

    $tmp = __DIR__ . '/tmp_sec_' . uniqid() . '.php';
    file_put_contents($tmp, $script);
    $out = shell_exec('C:\xampp\php\php.exe ' . escapeshellarg($tmp) . ' 2>&1');
    @unlink($tmp);
    return $out;
}

// -------------------------------------------------------------
// SECTION 1: verify_registration_admin.php Security Audit
// -------------------------------------------------------------
echo "--- Section 1: verify_registration_admin.php ---\n";

// 1A: Direct URL access (GET) must be rejected with 405
$res1A = run_script('verify_registration_admin.php', 'GET');
$json1A = json_decode($res1A, true);
assert_sec("1A. Direct URL access (GET) rejected with Method Not Allowed", isset($json1A['error']) && strpos($json1A['error'], 'Method Not Allowed') !== false);

// 1B: Unauthenticated access rejected with 401
$res1B = run_script('verify_registration_admin.php', 'POST', ['txn_id' => 'SOMEUTR', 'type' => 'individual']);
$json1B = json_decode($res1B, true);
assert_sec("1B. Unauthenticated POST rejected (Authentication required)", isset($json1B['error']) && strpos($json1B['error'], 'Authentication required') !== false);

// 1C: Authenticated user with missing/invalid CSRF token rejected with 403
$res1C = run_script('verify_registration_admin.php', 'POST', ['txn_id' => 'SOMEUTR', 'type' => 'individual', 'csrf_token' => 'invalid_csrf'], ['username' => $testOrganizerUsername, 'csrf_token' => $csrfToken]);
$json1C = json_decode($res1C, true);
assert_sec("1C. Invalid CSRF token rejected with security error", isset($json1C['error']) && strpos($json1C['error'], 'CSRF token') !== false);

// Register a single participant for the test event
$resReg = run_script('submit_upi_registration.php', 'POST', [
    'event_id' => $testEventId,
    'utr_number' => $testUtr,
    'name' => 'Charlie Attendee',
    'rollno' => 'ROLL-SEC-1',
    'college' => 'MMDU',
    'dept_name' => 'BCA',
    'email' => 'charlie@test.local',
    'mobile' => '9988776655',
    'payment_status' => 'paid', // Attack: user attempts to set paid
    'paid_amount' => '0'       // Attack: user attempts to set 0
]);
$jsonReg = json_decode($resReg, true);

// Verify DB record after registration
$regRow = $conn->query("SELECT * FROM singleevent_registration WHERE txn_id = '$testUtr'")->fetch_assoc();
assert_sec("2A. submit_upi_registration ignores POST payment_status=paid (stored as 'pending')", $regRow && $regRow['payment_status'] === 'pending');
assert_sec("2B. submit_upi_registration ignores client price (stored DB authoritative price 150)", $regRow && (float)$regRow['paid_amount'] === 150.0);

// 1D: Normal logged-in user (Bob) who is NOT the event owner tries to approve
$res1D = run_script('verify_registration_admin.php', 'POST', [
    'txn_id' => $testUtr,
    'type' => 'individual',
    'csrf_token' => $csrfToken
], ['username' => $testNormalUsername, 'csrf_token' => $csrfToken]);
$json1D = json_decode($res1D, true);
assert_sec("1D. Normal user / Non-owner cannot approve registration (Forbidden)", isset($json1D['error']) && strpos($json1D['error'], 'Forbidden') !== false);

// Check registration remains pending after unauthorized attempt
$regStillPending = $conn->query("SELECT payment_status FROM singleevent_registration WHERE txn_id = '$testUtr'")->fetch_assoc();
assert_sec("1E. Registration remains 'pending' after unauthorized approval attempt", $regStillPending && $regStillPending['payment_status'] === 'pending');

// 1F: Legitimate event owner (Alice) approves registration
$res1F = run_script('verify_registration_admin.php', 'POST', [
    'txn_id' => $testUtr,
    'type' => 'individual',
    'csrf_token' => $csrfToken
], ['username' => $testOrganizerUsername, 'csrf_token' => $csrfToken]);
$json1F = json_decode($res1F, true);
assert_sec("1F. Legitimate event owner successfully approves registration", !empty($json1F['success']));

// -------------------------------------------------------------
// SECTION 2: Database Consistency Audit
// -------------------------------------------------------------
echo "\n--- Section 2: Database Consistency ---\n";
$regApproved = $conn->query("SELECT payment_status FROM singleevent_registration WHERE txn_id = '$testUtr'")->fetch_assoc();
$orderApproved = $conn->query("SELECT status FROM event_orders WHERE razorpay_payment_id = '$testUtr'")->fetch_assoc();

assert_sec("2C. singleevent_registration payment_status updated to 'paid'", $regApproved && $regApproved['payment_status'] === 'paid');
assert_sec("2D. event_orders status atomically updated to 'paid'", $orderApproved && $orderApproved['status'] === 'paid');

// -------------------------------------------------------------
// SECTION 3: Duplicate UTR Replay Protection Audit
// -------------------------------------------------------------
echo "\n--- Section 3: Duplicate UTR Replay Protection ---\n";
$resReplay = run_script('submit_upi_registration.php', 'POST', [
    'event_id' => $testEventId,
    'utr_number' => $testUtr,
    'name' => 'Attacker',
    'rollno' => 'ROLL-ATTACK',
    'college' => 'Fake College',
    'dept_name' => 'BCA',
    'email' => 'attacker@test.local',
    'mobile' => '9988776655'
]);
$jsonReplay = json_decode($resReplay, true);
assert_sec("3A. Submitting duplicate UTR rejected (Replay protection)", isset($jsonReplay['error']) && strpos($jsonReplay['error'], 'already been submitted') !== false);

// -------------------------------------------------------------
// SECTION 4: Dashboard Authorization & CSRF Audit
// -------------------------------------------------------------
echo "\n--- Section 4: Dashboard Authorization & CSRF ---\n";
$dashContent = file_get_contents(__DIR__ . '/dashboard.php');
assert_sec("4A. dashboard.php requires valid session login", strpos($dashContent, '!isset($_SESSION[\'username\'])') !== false);
assert_sec("4B. dashboard.php queries registrations only for owned events", strpos($dashContent, 'WHERE organizer_name = ?') !== false);
assert_sec("4C. dashboard.php passes CSRF token to approvePayment()", strpos($dashContent, "fd.append('csrf_token',") !== false);

// Clean up test data
$conn->query("DELETE FROM singleevent_registration WHERE txn_id = '$testUtr'");
$conn->query("DELETE FROM event_orders WHERE razorpay_payment_id = '$testUtr'");
$conn->query("DELETE FROM create_event WHERE Event_ID = $testEventId");
$conn->query("DELETE FROM sign_up WHERE username IN ('$testOrganizerUsername', '$testNormalUsername')");

echo "\n========================================================\n";
echo "SECURITY AUDIT TOTAL: $passed PASSED, $failed FAILED\n";
echo "========================================================\n";

$conn->close();
