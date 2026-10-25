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

$response = array();

if (!verify_token()) {
    $db->disconnect();
    return false;
}
if (!isset($_POST['accesskey']) || $access_key != $db->escapeString($fn->xss_clean($_POST['accesskey']))) {
    $response['error'] = true;
    $response['message'] = "accesskey is invalid or required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

$pickup_lat   = isset($_POST['pickup_lat']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lat'])) : '';
$pickup_lng   = isset($_POST['pickup_lng']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lng'])) : '';
$drop_lat     = isset($_POST['drop_lat'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lat']))   : '';
$drop_lng     = isset($_POST['drop_lng'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lng']))   : '';
$vehicle_id   = isset($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : 0;
$helper_count = isset($_POST['helper_count']) ? max(0, (int)$_POST['helper_count']) : 0;

if (empty($pickup_lat) || empty($pickup_lng) || !is_numeric($pickup_lat) || !is_numeric($pickup_lng)) {
    $response['error'] = true;
    $response['message'] = "Pickup coordinates are required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}
if (empty($drop_lat) || empty($drop_lng) || !is_numeric($drop_lat) || !is_numeric($drop_lng)) {
    $response['error'] = true;
    $response['message'] = "Drop coordinates are required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}
if ($vehicle_id <= 0) {
    $response['error'] = true;
    $response['message'] = "Vehicle selection is required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

// Fetch vehicle details
$sql_veh = "SELECT * FROM `shipping_vehicles` WHERE `id` = $vehicle_id AND `status` = 1 LIMIT 1";
$db->sql($sql_veh);
$veh_res = $db->getResult();
if (empty($veh_res)) {
    $response['error'] = true;
    $response['message'] = "Selected shipping vehicle not found or inactive.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}
$vehicle = $veh_res[0];

// Pickup Zone check
$quote_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
if ($quote_zone === null) {
    $response['error'] = true;
    $response['message'] = "Pickup is only available inside our service zones. Please move the pickup point inside a zone.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

// System settings for tax and fees
$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
$db->sql($sql_settings);
$settings_res = $db->getResult();
$sys_settings = array();
if (!empty($settings_res)) {
    $sys_settings = json_decode($settings_res[0]['value'], true);
    if (!is_array($sys_settings)) { $sys_settings = array(); }
}

// Calculate road distance
$road = $fn->get_road_distance_km($pickup_lat, $pickup_lng, $drop_lat, $drop_lng);
$distance_km = (float)$road['km'];

$base_price = (float)$vehicle['base_price'];
$base_km = (float)$vehicle['base_km'];
$per_km_price = (float)$vehicle['per_km_price'];
$helper_price = (float)$vehicle['helper_price'];

$billable_km = max(0.0, $distance_km - $base_km);
$km_charge = round($billable_km * $per_km_price, 2);
$helper_charge = round($helper_count * $helper_price, 2);
$total_price = round($base_price + $km_charge + $helper_charge, 2);

$zone_fees = $fn->split_zone_fees($total_price, $quote_zone, $sys_settings);
$convenience_fee_amount = $zone_fees['convenience_fee'];
$platform_fee_amount    = $zone_fees['platform_fee'];
$gst                    = $zone_fees['gst'];
$grand_total            = $zone_fees['grand_total'];

$response['error'] = "false";
$response['message'] = "Shipping quote retrieved successfully";
$response['data'] = array(
    'service_type'        => 'shipping',
    'vehicle_id'          => (int)$vehicle['id'],
    'vehicle_name'        => $vehicle['name'],
    'vehicle_type'        => $vehicle['vehicle_type'],
    'capacity_label'      => $vehicle['capacity_label'],
    'capacity_kg'         => (float)$vehicle['capacity_kg'],
    'distance_km'         => round($distance_km, 2),
    'distance_source'     => isset($road['source']) ? $road['source'] : 'fallback',
    'base_price'          => round($base_price, 2),
    'base_km'             => round($base_km, 2),
    'per_km_price'        => round($per_km_price, 2),
    'km_charge'           => round($km_charge, 2),
    'helper_count'        => $helper_count,
    'helper_price'        => round($helper_price, 2),
    'helper_charge'       => round($helper_charge, 2),
    'total_price'         => round($total_price, 2),
    'platform_fee'        => round($platform_fee_amount, 2),
    'convenience_fee'     => round($convenience_fee_amount, 2),
    'convenience_fee_percent' => isset($zone_fees['convenience_fee_percent']) ? (float)$zone_fees['convenience_fee_percent'] : 0.0,
    'gst'                 => round($gst, 2),
    'grand_total'         => round($grand_total, 2),
    'zone_id'             => $quote_zone
);

print_r(json_encode($response));
$db->disconnect();
return false;
?>
