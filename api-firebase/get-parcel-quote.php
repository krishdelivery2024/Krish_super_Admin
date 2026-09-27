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
/* accesskey:90336 */
if (!verify_token()) {
    return false;
}
if (!isset($_POST['accesskey'])) {
    die('accesskey is require.');
}
if ($db->escapeString($fn->xss_clean($_POST['accesskey'])) != $access_key) {
    die('accesskey is incorrect.');
}

$pickup_lat = isset($_POST['pickup_lat']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lat'])) : '';
$pickup_lng = isset($_POST['pickup_lng']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lng'])) : '';
$drop_lat   = isset($_POST['drop_lat'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lat']))   : '';
$drop_lng   = isset($_POST['drop_lng'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lng']))   : '';

if (empty($pickup_lat) || empty($pickup_lng) || !is_numeric($pickup_lat) || !is_numeric($pickup_lng)) {
    $response['error'] = "true";
    $response['message'] = "Pickup coordinates are required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}
if (empty($drop_lat) || empty($drop_lng) || !is_numeric($drop_lat) || !is_numeric($drop_lng)) {
    $response['error'] = "true";
    $response['message'] = "Drop coordinates are required.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

$sql_query = "SELECT per_km_price, base_price FROM parcel_settings ORDER BY id ASC LIMIT 1";
$db->sql($sql_query);
$res = $db->getResult();
$per_km_price = (isset($res[0]['per_km_price']) && is_numeric($res[0]['per_km_price'])) ? (float)$res[0]['per_km_price'] : 0;
$base_price   = (isset($res[0]['base_price'])   && is_numeric($res[0]['base_price']))   ? (float)$res[0]['base_price']   : 0;

$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
$db->sql($sql_settings);
$settings_res = $db->getResult();
$tax = 0;
$sys_settings = array();
if (!empty($settings_res)) {
    $sys_settings = json_decode($settings_res[0]['value'], true);
    if (!is_array($sys_settings)) { $sys_settings = array(); }
    $tax = isset($sys_settings['tax']) ? floatval($sys_settings['tax']) : 0;
}

// Platform and convenience fees belong to the zone the parcel is picked up in,
// the same zone that is stored on the parcel request. A zone with no override
// falls back to the global setting.
$quote_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);

// Driving distance. Identical call to the one used when the parcel is finally
// booked, so the preview and the charge are produced by the same code path.
$road = $fn->get_road_distance_km($pickup_lat, $pickup_lng, $drop_lat, $drop_lng);
$distance_km = (float)$road['km'];

// First 1 km free, then the whole distance is charged
$km_charge = ($distance_km <= 1.0) ? 0.0 : ($distance_km * $per_km_price);
$total_price = round($base_price + $km_charge, 2);

$zone_fees = $fn->split_zone_fees($total_price, $quote_zone, $sys_settings);
$convenience_fee_amount = $zone_fees['convenience_fee'];
$platform_fee_amount = $zone_fees['platform_fee'];
$gst = $zone_fees['gst'];
$grand_total = $zone_fees['grand_total'];

$response['error'] = "false";
$response['message'] = "Parcel quote retrieved successfully";
$response['data'] = array(
    'distance_km'        => round($distance_km, 2),
    // Lets the app show a small notice if Google was unreachable and the quote
    // fell back to a straight line, so the price is never silently wrong.
    'distance_source'    => $road['source'],
    'per_km_price'       => round($per_km_price, 2),
    'base_price'         => round($base_price, 2),
    'km_charge'          => round($km_charge, 2),
    'total_price'        => $total_price,
    'platform_fee'       => $platform_fee_amount,
    'convenience_fee'    => $convenience_fee_amount,
    'zone_id'            => $zone_fees['zone_id'],
    'convenience_fee_percent' => $zone_fees['convenience_fee_percent'],
    'gst'                => $gst,
    'grand_total'        => $grand_total
);

// Diagnostics, enabled with store_map_debug set in system_timezone. Off by
// default so internal Google errors are not exposed to the mobile app.
$debug_settings = $fn->get_settings('system_timezone', true);
if (is_array($debug_settings) && !empty($debug_settings['store_map_debug'])) {
    $response['debug'] = array(
        'distance_source' => $road['source'],
        'maps_error'      => $fn->get_last_maps_error()
    );
}
print_r(json_encode($response));
$db->disconnect();
return false;
?>
