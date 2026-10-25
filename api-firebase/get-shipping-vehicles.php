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
    $db->disconnect();
    return false;
}

if (!verify_token()) {
    $db->disconnect();
    return false;
}

$settings_sql = "SELECT is_shipping_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
$db->sql($settings_sql);
$s_res = $db->getResult();
if (!empty($s_res) && isset($s_res[0]['is_shipping_available']) && (int)$s_res[0]['is_shipping_available'] === 0) {
    $response['error'] = "true";
    $response['message'] = "Shipping service is currently disabled.";
    $response['data'] = array();
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

$sql = "SELECT * FROM `shipping_vehicles` WHERE `status` = 1 ORDER BY `sort_order` ASC, `id` ASC";
$db->sql($sql);
$res = $db->getResult();

if (!empty($res)) {
    for ($i = 0; $i < count($res); $i++) {
        if (!empty($res[$i]['image'])) {
            if (strpos($res[$i]['image'], 'http://') === 0 || strpos($res[$i]['image'], 'https://') === 0) {
                // already absolute URL
            } else {
                $res[$i]['image'] = DOMAIN_URL . ltrim($res[$i]['image'], '/');
            }
        } else {
            $res[$i]['image'] = '';
        }
        $res[$i]['base_price'] = (float)$res[$i]['base_price'];
        $res[$i]['base_km'] = (float)$res[$i]['base_km'];
        $res[$i]['per_km_price'] = (float)$res[$i]['per_km_price'];
        $res[$i]['helper_price'] = (float)$res[$i]['helper_price'];
        $res[$i]['capacity_kg'] = (float)$res[$i]['capacity_kg'];
    }

    $response['error'] = "false";
    $response['message'] = "Shipping vehicles retrieved successfully";
    $response['data'] = $res;
} else {
    $response['error'] = "true";
    $response['message'] = "No active shipping vehicles found.";
    $response['data'] = array();
}

print_r(json_encode($response));
$db->disconnect();
return false;
?>
