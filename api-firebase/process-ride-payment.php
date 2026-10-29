<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();

if (!isset($_POST['accesskey']) || $_POST['accesskey'] != $access_key) {
    $response['error'] = true;
    $response['message'] = "Invalid access key.";
    echo json_encode($response);
    exit();
}

$ride_id = isset($_POST['ride_id']) ? (int)$_POST['ride_id'] : 0;
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$payment_method = isset($_POST['payment_method']) ? strtolower(trim($db->escapeString($fn->xss_clean($_POST['payment_method'])))) : 'cash';
$payment_id = isset($_POST['payment_id']) ? $db->escapeString($fn->xss_clean($_POST['payment_id'])) : '';

if ($ride_id <= 0 || $user_id <= 0) {
    $response['error'] = true;
    $response['message'] = "Ride ID and User ID are required.";
    echo json_encode($response);
    exit();
}

$sql = "SELECT * FROM parcel_requests WHERE id = '$ride_id' AND user_id = '$user_id'";
$db->sql($sql);
$res = $db->getResult();

if (empty($res)) {
    $response['error'] = true;
    $response['message'] = "Ride not found.";
    echo json_encode($response);
    exit();
}

$ride = $res[0];

if ($payment_method == 'cash') {
    $sql_up = "UPDATE parcel_requests SET payment_method = 'cash', payment_status = 'pending_cash' WHERE id = '$ride_id'";
    $db->sql($sql_up);

    $response['error'] = false;
    $response['message'] = "Cash payment selected. Please hand over ₹" . number_format($ride['grand_total'], 2) . " to your driver.";
    $response['payment_status'] = 'pending_cash';
    $response['payment_method'] = 'cash';
    echo json_encode($response);
    exit();
} else {
    // Online payment
    $sql_up = "UPDATE parcel_requests SET payment_method = '$payment_method', payment_status = 'paid', payment_id = '$payment_id', driver_payment_status = 'collected' WHERE id = '$ride_id'";
    $db->sql($sql_up);

    // Record transaction
    $txn_data = array(
        'user_id' => $user_id,
        'order_id' => $ride_id,
        'type' => $payment_method,
        'txn_id' => !empty($payment_id) ? $payment_id : ('TXN_' . time()),
        'amount' => $ride['grand_total'],
        'status' => 'success',
        'message' => 'Passenger Ride Payment Success',
        'transaction_date' => date('Y-m-d H:i:s')
    );
    $db->insert('transactions', $txn_data);

    $response['error'] = false;
    $response['message'] = "Payment successful! Thank you for riding with Krish Delivery.";
    $response['payment_status'] = 'paid';
    $response['payment_method'] = $payment_method;
    echo json_encode($response);
    exit();
}
?>
