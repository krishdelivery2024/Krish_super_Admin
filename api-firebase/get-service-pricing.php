<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once(__DIR__ . '/../includes/crud.php');
$db = new Database();
$db->connect();
include_once(__DIR__ . '/../includes/custom-functions.php');
$fn = new custom_functions;
include_once(__DIR__ . '/../includes/variables.php');
include_once(__DIR__ . '/verify-token.php');

if (!verify_token()) {
    return false;
}
if (!isset($_POST['accesskey'])) {
    die('accesskey is require.');
}
if ($db->escapeString($fn->xss_clean($_POST['accesskey'])) != $access_key) {
    die('accesskey is incorrect.');
}

$service_type = isset($_POST['service_type']) ? $db->escapeString($fn->xss_clean($_POST['service_type'])) : '';
$pickup_lat   = isset($_POST['pickup_lat'])   ? $db->escapeString($fn->xss_clean($_POST['pickup_lat']))   : '';
$pickup_lng   = isset($_POST['pickup_lng'])   ? $db->escapeString($fn->xss_clean($_POST['pickup_lng']))   : '';
$zone_id      = isset($_POST['zone_id'])      ? intval($_POST['zone_id']) : 0;

if ($zone_id <= 0 && !empty($pickup_lat) && !empty($pickup_lng)) {
    $found_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
    if ($found_zone !== null) {
        $zone_id = (int)$found_zone;
    }
}

// Fetch global settings for default fees
$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
$db->sql($sql_settings);
$settings_res = $db->getResult();
$sys_settings = array();
if (!empty($settings_res)) {
    $sys_settings = json_decode($settings_res[0]['value'], true);
    if (!is_array($sys_settings)) { $sys_settings = array(); }
}

$global_platform    = isset($sys_settings['platform_fee']) ? (float)$sys_settings['platform_fee'] : 0;
$global_convenience = isset($sys_settings['convenience_fee']) ? (float)$sys_settings['convenience_fee'] : 0;
$zone_fees = $fn->get_zone_fees($zone_id, $global_platform, $global_convenience);

$results = array();

if ($service_type === 'parcel') {
    // 1. Authoritative parcel settings
    $effective_parcel = $fn->get_effective_parcel_settings($zone_id);
    $results[] = array(
        'service_type'     => 'parcel',
        'vehicle_type'     => 'bike',
        'display_name'     => 'Parcel Delivery',
        'base_price'       => (float)$effective_parcel['base_price'],
        'base_km'          => 1.0,
        'per_km_price'     => (float)$effective_parcel['per_km_price'],
        'status'           => (int)$effective_parcel['is_service_available'],
        'platform_fee'     => (float)$zone_fees['platform_fee'],
        'convenience_fee'  => (float)$zone_fees['convenience_fee'],
        'convenience_fee_type' => 'percentage',
        'zone_id'          => $zone_fees['zone_id']
    );
} else if ($service_type === 'shipping') {
    // 2. Authoritative shipping vehicles
    $db->sql("SELECT id, name, vehicle_type, base_price, base_km, per_km_price, helper_price, status FROM shipping_vehicles WHERE status = 1 ORDER BY sort_order ASC, id ASC");
    $sv_rows = $db->getResult();
    foreach ($sv_rows as $sv) {
        $results[] = array(
            'id'               => (int)$sv['id'],
            'service_type'     => 'shipping',
            'vehicle_type'     => $sv['vehicle_type'],
            'display_name'     => $sv['name'],
            'base_price'       => (float)$sv['base_price'],
            'base_km'          => (float)$sv['base_km'],
            'per_km_price'     => (float)$sv['per_km_price'],
            'helper_price'     => (float)$sv['helper_price'],
            'status'           => (int)$sv['status'],
            'platform_fee'     => (float)$zone_fees['platform_fee'],
            'convenience_fee'  => (float)$zone_fees['convenience_fee'],
            'convenience_fee_type' => 'percentage',
            'zone_id'          => $zone_fees['zone_id']
        );
    }
} else {
    // 3. Authoritative passenger rides from service_pricing
    $sql = "SELECT id, service_type, vehicle_type, display_name, base_price, base_km, per_km_price, status FROM `service_pricing` WHERE `status` = 1 AND `service_type` = 'passenger' ORDER BY id ASC";
    $db->sql($sql);
    $pr_rows = $db->getResult();
    foreach ($pr_rows as $row) {
        $results[] = array(
            'id'               => (int)$row['id'],
            'service_type'     => $row['service_type'],
            'vehicle_type'     => $row['vehicle_type'],
            'display_name'     => $row['display_name'],
            'base_price'       => (float)$row['base_price'],
            'base_km'          => (float)$row['base_km'],
            'per_km_price'     => (float)$row['per_km_price'],
            'status'           => (int)$row['status'],
            'platform_fee'     => (float)$zone_fees['platform_fee'],
            'convenience_fee'  => (float)$zone_fees['convenience_fee'],
            'convenience_fee_type' => 'percentage',
            'zone_id'          => $zone_fees['zone_id']
        );
    }
}

$response = array();
$response['error'] = false;
$response['message'] = "Service pricing retrieved successfully";
$response['zone_id'] = $zone_fees['zone_id'];
$response['platform_fee'] = (float)$zone_fees['platform_fee'];
$response['convenience_fee_percent'] = (float)$zone_fees['convenience_fee'];
$response['data'] = $results;

print_r(json_encode($response));
$db->disconnect();
?>
