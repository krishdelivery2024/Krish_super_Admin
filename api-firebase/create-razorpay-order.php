<?php 
header('Access-Control-Allow-Origin: *');
@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);
ob_start();
require 'razorpay/Razorpay.php';
include_once('order-helper.php');

use Razorpay\Api\Api;

if(isset($_POST) && isset($_POST['accesskey']) && isset($_POST['amount']) && !empty($_POST['amount']) && is_numeric($_POST['amount'])){
    
    include_once('../includes/custom-functions.php');
    include_once('../includes/variables.php');
    $order = [];
    
    $access_key_received = isset($_POST['accesskey']) && !empty($_POST['accesskey'])?$_POST['accesskey']:'';
    if($access_key_received != $access_key){
        $order['error'] = true;
        $order['message'] = "Invalid access key passed.";
        echo json_encode($order);
        return false;
        exit(0);
    }
    
    $fn = new custom_functions();
    $db = new Database();
    $db->connect();
    init_razorpay_orders_table($db);

    $data = $fn->get_settings('payment_methods',true);
    $app_settings = $fn->get_settings('system_timezone',true);
    $app_name = isset($app_settings['app_name']) ? $app_settings['app_name'] : 'krish';
    $app_name = trim(strtolower($app_name));
    $app_name = str_replace('-', '', $app_name);
    $app_name = str_replace(' ', '-', $app_name);
    
    $key = $data['razorpay_key'];
    $razorpay_secret_key = $data['razorpay_secret_key'];
    
    if($data['razorpay_payment_method'] ){
        $api = new Api($key,$razorpay_secret_key);    /* Razorpay credentials */
        
        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
        $receipt = (isset($_POST['receipt']) && !empty($_POST['receipt'])) ? $db->escapeString($fn->xss_clean($_POST['receipt'])) : $app_name.'-'.time().'-'.rand(100,999);
        
        // Optional payload parameters sent from updated app
        $user_id = (isset($_POST['user_id']) && is_numeric($_POST['user_id'])) ? (int)$_POST['user_id'] : 0;
        $seller_id = (isset($_POST['seller_id']) && is_numeric($_POST['seller_id'])) ? (int)$_POST['seller_id'] : 0;
        $order_payload = isset($_POST['order_payload']) ? trim($_POST['order_payload']) : '';

        $notes = array(
            'receipt' => (string)$receipt
        );
        if ($user_id > 0) $notes['user_id'] = (string)$user_id;
        if ($seller_id > 0) $notes['seller_id'] = (string)$seller_id;

        $razorpay_order = $api->order->create(array( 
            'receipt' => $receipt,
            'amount' => $amount,
            'payment_capture' => 1,
            'currency' => 'INR',
            'notes' => $notes
            )
        );
        
        $order['error'] = false;
        $order['message'] = "Order initiated successfully. ";
        $order['id'] = $razorpay_order['id'];
        $order['amount'] = $razorpay_order['amount'];
        $order['entity'] = $razorpay_order['entity'];
        $order['amount_paid'] = $razorpay_order['amount_paid'];
        $order['amount_due'] = $razorpay_order['amount_due'];
        $order['currency'] = $razorpay_order['currency'];
        $order['receipt'] = $razorpay_order['receipt'];
        $order['offer_id'] = $razorpay_order['offer_id'];
        $order['status'] = $razorpay_order['status'];
        $order['attempts'] = $razorpay_order['attempts'];
        $order['created_at'] = $razorpay_order['created_at'];

        // Persist initiated order in razorpay_orders table
        $amount_rupees = ((float)$amount) / 100.0;
        $safe_rp_id = $db->escapeString($razorpay_order['id']);
        $safe_receipt = $db->escapeString($receipt);
        $safe_payload = !empty($order_payload) ? $db->escapeString($order_payload) : NULL;
        $notes_json = $db->escapeString(json_encode($notes));

        $sql_insert = "INSERT INTO razorpay_orders (razorpay_order_id, user_id, seller_id, amount, receipt, order_payload, status, notes)
                       VALUES ('$safe_rp_id', '$user_id', '$seller_id', '$amount_rupees', '$safe_receipt', " . ($safe_payload ? "'$safe_payload'" : "NULL") . ", 'initiated', '$notes_json')
                       ON DUPLICATE KEY UPDATE order_payload = VALUES(order_payload), updated_at = NOW()";
        $db->sql($sql_insert);

        ob_end_clean();
        echo json_encode($order);
    }else{
        $order['error'] = true;
        $order['message'] = "Order could not be created! Try another method";
        echo json_encode($order);
    }
}else{
    $order['error'] = true;
    $order['message'] = "Please pass all the fields.";
    echo json_encode($order);
}
?>
