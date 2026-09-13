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

$user_id = (isset($_POST['user_id'])) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";

if (empty($user_id)) {
    $response['error'] = true;
    $response['message'] = "User id is required.";
    print_r(json_encode($response));
    return false;
}

$sql = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile FROM `parcel_requests` pr WHERE pr.user_id = '" . $user_id . "' ORDER BY pr.id DESC";
$db->sql($sql);
$res = $db->getResult();

for ($i = 0; $i < count($res); $i++) {
    $images = (!empty($res[$i]['parcel_image'])) ? array_filter(array_map('trim', explode(',', $res[$i]['parcel_image']))) : array();
    $res[$i]['parcel_images'] = array_map(function ($img) {
        return DOMAIN_URL . $img;
    }, array_values($images));
}

if (!empty($res)) {
    $response['error'] = false;
    $response['message'] = "Parcel orders found!";
    $response['data'] = $res;
} else {
    $response['error'] = true;
    $response['message'] = "No parcel orders found!";
    $response['data'] = array();
}
print_r(json_encode($response));
return false;
?>