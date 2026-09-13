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
$user_id = (isset($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

if (empty($order_id)) {
    $response['error'] = true;
    $response['message'] = "Order id is required.";
    print_r(json_encode($response));
    return false;
}

if (empty($user_id)) {
    $response['error'] = true;
    $response['message'] = "User id is required.";
    print_r(json_encode($response));
    return false;
}

$sql = "SELECT * FROM `parcel_requests` WHERE id = '" . $order_id . "' AND user_id = '" . $user_id . "'";
$db->sql($sql);
$res = $db->getResult();

if (count($res) == 0) {
    $response['error'] = true;
    $response['message'] = "Parcel order not found.";
    print_r(json_encode($response));
    return false;
}

$status = strtolower($res[0]['status']);
if ($status != 'pending') {
    $response['error'] = true;
    $response['message'] = "Order cannot be cancelled because it has been already " . $status . ".";
    print_r(json_encode($response));
    return false;
}

$sql = "UPDATE `parcel_requests` SET status = 'cancelled' WHERE id = '" . $order_id . "' AND user_id = '" . $user_id . "'";
$db->sql($sql);

$response['error'] = false;
$response['message'] = "Parcel order cancelled successfully.";
$response['data'] = array('id' => $order_id);
print_r(json_encode($response));
return false;
?>