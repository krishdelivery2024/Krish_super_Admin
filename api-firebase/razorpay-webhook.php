<?php
/**
 * Razorpay Webhook Handler
 * Endpoint: https://krishdelivery.com/api-firebase/razorpay-webhook.php
 * Listens for payment.captured and order.paid events to prevent lost orders.
 */

header('Content-Type: application/json');
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('order-helper.php');

$db = new Database();
$db->connect();
$fn = new custom_functions();

init_razorpay_orders_table($db);

$raw_payload = file_get_contents('php://input');
$signature = isset($_SERVER['HTTP_X_RAZORPAY_SIGNATURE']) ? $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] : '';

log_razorpay_webhook("Received webhook call. Payload size: " . strlen($raw_payload) . " bytes.");

if (empty($raw_payload)) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Empty payload'));
    exit;
}

// Fetch Razorpay credentials & webhook secret from settings
$payment_settings = $fn->get_settings('payment_methods', true);
$webhook_secret = isset($payment_settings['razorpay_webhook_secret']) ? trim($payment_settings['razorpay_webhook_secret']) : '';

// Verify signature if a webhook secret is configured
if (!empty($webhook_secret) && !empty($signature)) {
    $expected_signature = hash_hmac('sha256', $raw_payload, $webhook_secret);
    if (!hash_equals($expected_signature, $signature)) {
        log_razorpay_webhook("Webhook signature verification failed! Signature mismatch.");
        http_response_code(401);
        echo json_encode(array('status' => 'error', 'message' => 'Invalid signature'));
        exit;
    }
} else {
    log_razorpay_webhook("Webhook processed (Webhook secret not set in settings, skipping HMAC check).");
}

$data = json_decode($raw_payload, true);
if (!$data || !isset($data['event'])) {
    http_response_code(400);
    echo json_encode(array('status' => 'error', 'message' => 'Invalid JSON payload'));
    exit;
}

$event = $data['event'];
log_razorpay_webhook("Webhook event type: $event");

$result = array('status' => 'ignored', 'event' => $event);

if ($event === 'payment.captured') {
    if (isset($data['payload']['payment']['entity'])) {
        $payment = $data['payload']['payment']['entity'];
        $payment_id = $payment['id'];
        $rp_order_id = isset($payment['order_id']) ? $payment['order_id'] : '';
        $amount = (float)$payment['amount'] / 100.0;
        $contact = isset($payment['contact']) ? $payment['contact'] : '';
        $email = isset($payment['email']) ? $payment['email'] : '';
        $notes = isset($payment['notes']) ? $payment['notes'] : array();

        $result = process_razorpay_captured_payment(
            $payment_id,
            $rp_order_id,
            $amount,
            $contact,
            $email,
            $notes,
            $db,
            $fn
        );
    }
} elseif ($event === 'order.paid') {
    if (isset($data['payload']['order']['entity'])) {
        $order = $data['payload']['order']['entity'];
        $rp_order_id = $order['id'];
        $amount = (float)$order['amount'] / 100.0;

        // Payment entity is usually bundled under payment
        if (isset($data['payload']['payment']['entity'])) {
            $payment = $data['payload']['payment']['entity'];
            $payment_id = $payment['id'];
            $contact = isset($payment['contact']) ? $payment['contact'] : '';
            $email = isset($payment['email']) ? $payment['email'] : '';
            $notes = isset($payment['notes']) ? $payment['notes'] : array();

            $result = process_razorpay_captured_payment(
                $payment_id,
                $rp_order_id,
                $amount,
                $contact,
                $email,
                $notes,
                $db,
                $fn
            );
        } else {
            log_razorpay_webhook("order.paid received for $rp_order_id without payment entity.");
        }
    }
}

http_response_code(200);
echo json_encode(array('status' => 'success', 'result' => $result));
$db->disconnect();
?>

