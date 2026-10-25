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
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db->connect(); 
include_once('../includes/variables.php');
include_once('verify-token.php');

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if ($access_key_received == $access_key) {
        $sql_query = "SELECT id, name, status 
            FROM shipping_goods_types 
            WHERE status = 1 
            ORDER BY id ASC";
        $db->sql($sql_query);
        $res = $db->getResult();
        if (!empty($res)) {
            for ($i = 0; $i < count($res); $i++) {
                $res[$i]['id'] = (int)$res[$i]['id'];
                $res[$i]['status'] = (int)$res[$i]['status'];
            }
            $response['error'] = "false";
            $response['message'] = "Shipping goods types retrieved successfully";
            $response['data'] = $res;
        } else {
            $response['error'] = "true";
            $response['message'] = "No goods types found!";
            $response['data'] = array();
        }
        print_r(json_encode($response));
    } else {
        $response['error'] = "true";
        $response['message'] = "accesskey is incorrect.";
        print_r(json_encode($response));
    }
} else {
    $response['error'] = "true";
    $response['message'] = "accesskey is required.";
    print_r(json_encode($response));
}
$db->disconnect();
?>
