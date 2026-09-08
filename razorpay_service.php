<?php
/* =============================================================
   EVENTHUB PRO — Razorpay Service Layer (UPI-Only Flow)
   Encapsulates:
   - Order creation via Razorpay REST API
   - Cryptographic signature validation (HMAC-SHA256)
   - Atomic database transaction for order finalization
   - Shared between frontend callback and server-to-server webhook
   ============================================================= */
require_once __DIR__ . '/config.php';

/**
 * Check if Razorpay keys are configured properly.
 */
function razorpay_is_configured() {
    $key = defined('RAZORPAY_KEY_ID') ? trim(RAZORPAY_KEY_ID) : '';
    $secret = defined('RAZORPAY_KEY_SECRET') ? trim(RAZORPAY_KEY_SECRET) : '';
    return ($key !== '' && $secret !== '' && strpos($key, 'YOUR_KEY_ID') === false);
}

/**
 * Create an order via Razorpay REST API.
 * 
 * @param int $amount_paise Amount in paise (e.g. 10000 for ₹100.00)
 * @param string $receipt Unique receipt identifier
 * @param array $notes Optional metadata
 * @return array ['success' => bool, 'order' => array, 'error' => string]
 */
function razorpay_create_order_api($amount_paise, $receipt, $notes = []) {
    if (!razorpay_is_configured()) {
        return [
            'success' => false,
            'error' => 'Razorpay credentials are not configured in config.php. Please add your RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET.'
        ];
    }

    $url = 'https://api.razorpay.com/v1/orders';
    $payload = [
        'amount'   => (int)$amount_paise,
        'currency' => 'INR',
        'receipt'  => (string)$receipt,
        'notes'    => $notes
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'User-Agent: EventHub-Pro-Razorpay/1.0'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        error_log("Razorpay cURL error: " . $curlErr);
        return [
            'success' => false,
            'error' => 'Could not connect to payment gateway: ' . $curlErr
        ];
    }

    $data = json_decode($response, true);
    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['id'])) {
        return [
            'success' => true,
            'order'   => $data
        ];
    }

    $msg = $data['error']['description'] ?? 'Failed to create payment order.';
    error_log("Razorpay API error (" . $httpCode . "): " . $response);
    return [
        'success' => false,
        'error'   => $msg
    ];
}

/**
 * Verify Razorpay payment signature using HMAC-SHA256 and timing-safe hash_equals.
 *
 * @param string $order_id Authoritative Razorpay Order ID
 * @param string $payment_id Razorpay Payment ID
 * @param string $signature Signature received from frontend
 * @return bool
 */
function razorpay_verify_payment_signature($order_id, $payment_id, $signature) {
    if (empty($order_id) || empty($payment_id) || empty($signature)) {
        return false;
    }
    $secret = defined('RAZORPAY_KEY_SECRET') ? RAZORPAY_KEY_SECRET : '';
    if (empty($secret)) {
        return false;
    }
    $expected = hash_hmac('sha256', $order_id . '|' . $payment_id, $secret);
    return hash_equals($expected, $signature);
}

/**
 * Verify Razorpay Webhook signature using raw payload and timing-safe hash_equals.
 *
 * @param string $raw_body Raw HTTP body
 * @param string $signature Signature from X-Razorpay-Signature header
 * @return bool
 */
function razorpay_verify_webhook_signature($raw_body, $signature) {
    if (empty($raw_body) || empty($signature)) {
        return false;
    }
    $secret = defined('RAZORPAY_WEBHOOK_SECRET') ? RAZORPAY_WEBHOOK_SECRET : '';
    if (empty($secret)) {
        return false;
    }
    $expected = hash_hmac('sha256', $raw_body, $secret);
    return hash_equals($expected, $signature);
}

/**
 * Atomically finalize an event order and insert the confirmed registration record.
 * Safe, transactional, and idempotent.
 *
 * @param mysqli $conn Active database connection
 * @param string $order_id Authoritative Razorpay Order ID
 * @param string $payment_id Razorpay Payment ID
 * @return array ['success' => bool, 'event_id' => int, 'txn_id' => string, 'already_paid' => bool, 'error' => string]
 */
function razorpay_finalize_order($conn, $order_id, $payment_id) {
    if (empty($order_id) || empty($payment_id)) {
        return ['success' => false, 'error' => 'Missing order ID or payment ID.'];
    }

    // Start atomic transaction
    $conn->begin_transaction();

    try {
        // Lock and read the order row
        $stmt = $conn->prepare("SELECT id, razorpay_order_id, razorpay_payment_id, event_id, registration_type, registration_data, amount, status FROM event_orders WHERE razorpay_order_id = ? FOR UPDATE");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $conn->error);
        }
        $stmt->bind_param('s', $order_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $order = $res->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Order not found in database.'];
        }

        $eventId = (int)$order['event_id'];

        // If order was already confirmed, do not insert duplicate registrations
        if ($order['status'] === 'paid') {
            $conn->commit();
            return [
                'success'      => true,
                'already_paid' => true,
                'event_id'     => $eventId,
                'txn_id'       => $order['razorpay_payment_id'] ?: $payment_id
            ];
        }

        // Verify that event exists and read event details
        $stmtEv = $conn->prepare("SELECT event_title, event_price, time, event_venue FROM create_event WHERE event_id = ?");
        $stmtEv->bind_param('i', $eventId);
        $stmtEv->execute();
        $resEv = $stmtEv->get_result();
        $evRow = $resEv->fetch_assoc();
        $stmtEv->close();

        if (!$evRow) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Associated event does not exist.'];
        }

        $verifiedPrice = (float)$evRow['event_price'];
        $orderAmount   = (float)$order['amount'];

        // Verify amount integrity
        if (abs($verifiedPrice - $orderAmount) > 0.01) {
            $conn->rollback();
            error_log("Price mismatch for order {$order_id}: DB price {$verifiedPrice} vs Order amount {$orderAmount}");
            return ['success' => false, 'error' => 'Price verification mismatch.'];
        }

        $regData = json_decode($order['registration_data'], true);
        if (!is_array($regData)) {
            $conn->rollback();
            return ['success' => false, 'error' => 'Invalid registration data payload.'];
        }

        $regType = $order['registration_type'];
        $participantEmail = '';
        $participantName  = '';

        if ($regType === 'individual') {
            $name     = trim($regData['name'] ?? '');
            $rollNo   = trim($regData['rollno'] ?? '');
            $college  = trim($regData['college'] ?? '');
            $dept     = trim($regData['dept_name'] ?? '');
            $email    = trim($regData['email'] ?? '');
            $mobile   = trim($regData['mobileno'] ?? ($regData['mobile'] ?? ''));
            $amtStr   = (string)$orderAmount;

            $participantEmail = $email;
            $participantName  = $name;

            // Check if already registered under this payment ID to guarantee idempotency
            $stmtCheck = $conn->prepare("SELECT id FROM singleevent_registration WHERE txn_id = ?");
            $stmtCheck->bind_param('s', $payment_id);
            $stmtCheck->execute();
            $hasDuplicate = $stmtCheck->get_result()->num_rows > 0;
            $stmtCheck->close();

            if (!$hasDuplicate) {
                $statusPaid = 'paid';
                $currency   = 'INR';
                $stmtIns = $conn->prepare("INSERT INTO singleevent_registration (event_id, name, roll_no, college_name, dept_name, email, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtIns->bind_param('issssssssss', $eventId, $name, $rollNo, $college, $dept, $email, $mobile, $amtStr, $currency, $payment_id, $statusPaid);
                if (!$stmtIns->execute()) {
                    throw new Exception("Error saving individual registration: " . $stmtIns->error);
                }
                $stmtIns->close();
            }
        } elseif ($regType === 'team') {
            $teamName = trim($regData['teamname'] ?? ($regData['team_name'] ?? ''));
            $college  = trim($regData['college'] ?? '');
            $amtStr   = (string)$orderAmount;

            // Parse student names, emails, mobiles from registration payload
            $namesArr   = [];
            $emailsArr  = [];
            $mobilesArr = [];

            if (!empty($regData['member1'])) $namesArr[] = trim($regData['member1']);
            if (!empty($regData['email1']))   $emailsArr[] = trim($regData['email1']);
            if (!empty($regData['mobile1']))  $mobilesArr[] = trim($regData['mobile1']);

            for ($m = 2; $m <= 5; $m++) {
                if (!empty($regData['member' . $m])) $namesArr[] = trim($regData['member' . $m]);
                if (!empty($regData['email' . $m]))   $emailsArr[] = trim($regData['email' . $m]);
                if (!empty($regData['mobile' . $m]))  $mobilesArr[] = trim($regData['mobile' . $m]);
            }

            // Fallback if submitted as arrays
            if (empty($namesArr) && !empty($regData['name']) && is_array($regData['name'])) {
                $namesArr = array_map('trim', $regData['name']);
            }
            if (empty($emailsArr) && !empty($regData['email']) && is_array($regData['email'])) {
                $emailsArr = array_map('trim', $regData['email']);
            }
            if (empty($mobilesArr) && !empty($regData['mobile']) && is_array($regData['mobile'])) {
                $mobilesArr = array_map('trim', $regData['mobile']);
            }

            $studentsStr = implode(',', $namesArr);
            $emailsStr   = implode(',', $emailsArr);
            $mobilesStr  = implode(',', $mobilesArr);

            $participantEmail = $emailsArr[0] ?? '';
            $participantName  = $teamName;

            // Check if already registered under this payment ID to guarantee idempotency
            $stmtCheck = $conn->prepare("SELECT id FROM teamevent_registration WHERE txn_id = ?");
            $stmtCheck->bind_param('s', $payment_id);
            $stmtCheck->execute();
            $hasDuplicate = $stmtCheck->get_result()->num_rows > 0;
            $stmtCheck->close();

            if (!$hasDuplicate) {
                $statusPaid = 'paid';
                $currency   = 'INR';
                $stmtIns = $conn->prepare("INSERT INTO teamevent_registration (event_id, team_name, college_name, student_name, emails, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmtIns->bind_param('isssssssss', $eventId, $teamName, $college, $studentsStr, $emailsStr, $mobilesStr, $amtStr, $currency, $payment_id, $statusPaid);
                if (!$stmtIns->execute()) {
                    throw new Exception("Error saving team registration: " . $stmtIns->error);
                }
                $stmtIns->close();
            }
        } else {
            $conn->rollback();
            return ['success' => false, 'error' => 'Unknown registration type.'];
        }

        // Update event_orders status to 'paid'
        $stmtUpd = $conn->prepare("UPDATE event_orders SET status = 'paid', razorpay_payment_id = ?, paid_at = CURRENT_TIMESTAMP WHERE razorpay_order_id = ?");
        $stmtUpd->bind_param('ss', $payment_id, $order_id);
        $stmtUpd->execute();
        $stmtUpd->close();

        // Commit transaction
        $conn->commit();

        // Send confirmation email if supported
        if ($participantEmail !== '' && filter_var($participantEmail, FILTER_VALIDATE_EMAIL)) {
            $subject = "Registration Confirmed — " . ($evRow['event_title'] ?? 'Event');
            $msg = "Dear " . htmlspecialchars($participantName) . ",\n\n"
                 . "Your registration has been successfully confirmed!\n\n"
                 . "Event: " . ($evRow['event_title'] ?? '') . "\n"
                 . "Venue: " . ($evRow['event_venue'] ?? '') . "\n"
                 . "Time: " . ($evRow['time'] ?? '') . "\n"
                 . "Amount Paid: ₹" . number_format($orderAmount) . "\n"
                 . "Transaction ID: " . $payment_id . "\n\n"
                 . "Please show your QR ticket at the venue for instant entry.\n\n"
                 . "Thank you,\nEventHub Pro Team";
            $headers = "From: EventHub Pro <no-reply@eventhubpro.local>\r\n";
            @mail($participantEmail, $subject, $msg, $headers);
        }

        return [
            'success'      => true,
            'already_paid' => false,
            'event_id'     => $eventId,
            'txn_id'       => $payment_id
        ];

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in razorpay_finalize_order: " . $e->getMessage());
        return ['success' => false, 'error' => 'Internal server error finalizing order.'];
    }
}
