<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions();
include_once('../includes/variables.php');
include_once('verify-token.php');

$response = array();

if (!isset($_POST['accesskey']) || $access_key != $db->escapeString($fn->xss_clean($_POST['accesskey']))) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}

$order_id = (isset($_POST['id'])) ? $db->escapeString($fn->xss_clean($_POST['id'])) : "";
$user_id  = (isset($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

if (empty($order_id)) {
    $response['error'] = true;
    $response['message'] = "Order id is required.";
    print_r(json_encode($response));
    return false;
}

$where_user = "";
if (!empty($user_id)) {
    $where_user = " AND pr.user_id = '" . $user_id . "'";
}

$sql = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile, (SELECT name FROM delivery_boys db WHERE db.id = pr.delivery_boy_id) AS delivery_boy_name, (SELECT mobile FROM delivery_boys db WHERE db.id = pr.delivery_boy_id) AS delivery_boy_mobile FROM `parcel_requests` pr WHERE pr.id = '" . $order_id . "'" . $where_user;
$db->sql($sql);
$res = $db->getResult();

if (count($res) == 0) {
    $response['error'] = true;
    $response['message'] = "Parcel order not found.";
    print_r(json_encode($response));
    return false;
}

$row = $res[0];
$images = (!empty($row['parcel_image'])) ? array_filter(array_map('trim', explode(',', $row['parcel_image']))) : array();
$row['parcel_images'] = array_map(function($img){ return DOMAIN_URL . $img; }, array_values($images));

$response['error'] = false;
$response['message'] = "Parcel order details fetched successfully.";
$response['data'] = $row;
print_r(json_encode($response));
return false;
?>