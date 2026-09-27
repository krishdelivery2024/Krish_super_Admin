<?php
if (PHP_SAPI === 'cli') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    $_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $_SERVER['HTTPS'] = '';
}

include_once('includes/crud.php');
include_once('includes/custom-functions.php');
include_once('includes/variables.php');

$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();

$config = $function->get_configurations();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$response = array();
$response['error'] = false;
$response['message'] = 'No pending notifications.';
$response['sent'] = 0;
$response['skipped'] = 0;

$sql = "SELECT * FROM scheduled_delivery_boy_notifications WHERE status = 'pending' AND scheduled_for <= NOW() ORDER BY id ASC";
$db->sql($sql);
$pending = $db->getResult();

foreach ($pending as $row) {
    $sched_id = $row['id'];
    $order_id = (int)$row['order_id'];
    $title = $row['title'];
    $message = $row['message'];
    $order_type = $row['order_type'];

    $sql_order = "SELECT active_status, delivery_boy_id, delivery_method, delivery_time FROM orders WHERE id = $order_id";
    $db->sql($sql_order);
    $res_order = $db->getResult();
    if (empty($res_order) || empty($res_order[0]) || strtolower(trim($res_order[0]['active_status'])) != 'processed') {
        $db->update('scheduled_delivery_boy_notifications', array('status' => 'cancelled'), "id = $sched_id");
        $db->getResult();
        $response['skipped']++;
        continue;
    }
    $order = $res_order[0];
    $delivery_boy_id = (int)$order['delivery_boy_id'];
    $delivery_method = strtolower(trim(isset($order['delivery_method']) ? $order['delivery_method'] : ''));

    if ($delivery_method == 'storepickup' || $delivery_boy_id != 0) {
        $db->update('scheduled_delivery_boy_notifications', array('status' => 'cancelled'), "id = $sched_id");
        $db->getResult();
        $response['skipped']++;
        continue;
    }

    $function->send_notification_to_delivery_boy(0, $title, $message, 'delivery_boys', $order_id, $order_type);
    $function->store_delivery_boy_notification(0, $order_id, $title, $message, 'order_status');
    $db->update('scheduled_delivery_boy_notifications', array('status' => 'sent'), "id = $sched_id");
    $db->getResult();
    $response['sent']++;
    $response['message'] = "Sent for order #$order_id.";
}

echo json_encode($response);