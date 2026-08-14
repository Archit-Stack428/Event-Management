<?php
/* =============================================================
   Paid event registration handler (Stripe)
   Uses prepared statements to prevent SQL injection.
   Stripe API keys come from config.php (not hardcoded).
   ============================================================= */
include('dbconnect.php');
require_once __DIR__ . '/config.php';

$id1 = (int)($_GET['id1'] ?? 0);

$minimum = 0;
$maximum = 0;
$price = 0;
$eventid = 0;
$eventtitle = '';

$stmt = $conn->prepare("SELECT event_title, event_id, min_team, max_team, event_price FROM create_event WHERE event_id = ?");
$stmt->bind_param('i', $id1);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $minimum    = $row['min_team'];
    $maximum    = $row['max_team'];
    $price      = $row['event_price'];
    $eventid    = $row['event_id'];
    $eventtitle = $row['event_title'];
}
$stmt->close();

if (!empty($_POST['stripeToken'])) {

    $token  = $_POST['stripeToken'];

    require_once('stripe-php/init.php');

    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $itemName   = $eventtitle;
    $itemNumber = "EV" . $eventid;
    $itemPrice  = $price;
    $currency   = "inr";
    $orderID    = "EVENT" . $eventid;

    if ($minimum == '0') {
        // ---- Single participant ----
        $name     = trim($_POST['name'] ?? '');
        $roll_no  = trim($_POST['rollno'] ?? '');
        $college  = trim($_POST['college'] ?? '');
        $dept_name= trim($_POST['dept_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $mobile_no= trim($_POST['mobileno'] ?? '');

        try {
            $customer = \Stripe\Customer::create([
                'email'  => $email,
                'source' => $token
            ]);

            $charge = \Stripe\Charge::create([
                'customer'    => $customer->id,
                'amount'      => $itemPrice,
                'currency'    => $currency,
                'description' => $itemName,
                'metadata'    => ['order_id' => $orderID]
            ]);

            $chargeJson = $charge->jsonSerialize();

            if ($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1) {
                $amount             = $chargeJson['amount'];
                $balance_transaction = $chargeJson['balance_transaction'];
                $currency_resp      = $chargeJson['currency'];
                $status             = $chargeJson['status'];

                $stmt = $conn->prepare("INSERT INTO singleevent_registration (event_id, name, roll_no, college_name, dept_name, email, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('issssssssss', $id1, $name, $roll_no, $college, $dept_name, $email, $mobile_no, $amount, $currency_resp, $balance_transaction, $status);
                $stmt->execute();
                $stmt->close();

                $url = "successfullyregisterd.php?txnid=" . urlencode($balance_transaction) . "&eventid=" . $id1;
                echo "<script>window.location='" . $url . "'</script>";
            } else {
                $statusMsg = "Transaction has been failed";
            }
        } catch (\Exception $e) {
            $statusMsg = "Payment error: " . $e->getMessage();
        }

    } else {
        // ---- Team registration ----
        $teamname  = trim($_POST['team_name'] ?? '');
        $college   = trim($_POST['college'] ?? '');
        $studentname = implode(",", array_map('trim', (array)($_POST['name'] ?? [])));
        $emails      = implode(",", array_map('trim', (array)($_POST['email'] ?? [])));
        $mobile_no   = implode(",", array_map('trim', (array)($_POST['mobile'] ?? [])));

        try {
            $emailArr = (array)($_POST['email'] ?? []);
            $firstEmail = trim($emailArr[0] ?? '');

            $customer = \Stripe\Customer::create([
                'email'  => $firstEmail,
                'source' => $token
            ]);

            $charge = \Stripe\Charge::create([
                'customer'    => $customer->id,
                'amount'      => $itemPrice,
                'currency'    => $currency,
                'description' => $itemName,
                'metadata'    => ['order_id' => $orderID]
            ]);

            $chargeJson = $charge->jsonSerialize();

            if ($chargeJson['amount_refunded'] == 0 && empty($chargeJson['failure_code']) && $chargeJson['paid'] == 1 && $chargeJson['captured'] == 1) {
                $amount             = $chargeJson['amount'];
                $balance_transaction = $chargeJson['balance_transaction'];
                $currency_resp      = $chargeJson['currency'];
                $status             = $chargeJson['status'];

                $stmt = $conn->prepare("INSERT INTO teamevent_registration (event_id, team_name, college_name, student_name, emails, mobile_no, paid_amount, paid_amount_currency, txn_id, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssssssss', $id1, $teamname, $college, $studentname, $emails, $mobile_no, $amount, $currency_resp, $balance_transaction, $status);
                $stmt->execute();
                $stmt->close();

                $url = "successfullyregisterd.php?txnid=" . urlencode($balance_transaction) . "&eventid=" . $id1;
                echo "<script>window.location='" . $url . "'</script>";
            } else {
                $statusMsg = "Transaction has been failed";
            }
        } catch (\Exception $e) {
            $statusMsg = "Payment error: " . $e->getMessage();
        }
    }
} else {
    $statusMsg = "Form submission error.......";
}

echo $statusMsg ?? '';
$conn->close();
