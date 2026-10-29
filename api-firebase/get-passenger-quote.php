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

$pickup_lat = isset($_POST['pickup_lat']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lat'])) : '';
$pickup_lng = isset($_POST['pickup_lng']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lng'])) : '';
$drop_lat   = isset($_POST['drop_lat'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lat']))   : '';
$drop_lng   = isset($_POST['drop_lng'])   ? $db->escapeString($fn->xss_clean($_POST['drop_lng']))   : '';
$requested_vehicle = isset($_POST['vehicle_type']) ? $db->escapeString($fn->xss_clean($_POST['vehicle_type'])) : '';

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

// Identify pickup zone for zone-based fees
$quote_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);

// Check if passenger ride service is enabled globally
$sql_check = "SELECT is_passenger_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
$db->sql($sql_check);
$chk_res = $db->getResult();
if (!empty($chk_res) && isset($chk_res[0]['is_passenger_available']) && (int)$chk_res[0]['is_passenger_available'] === 0) {
    $response['error'] = true;
    $response['message'] = "Passenger ride service is currently disabled.";
    print_r(json_encode($response));
    $db->disconnect();
    return false;
}

// System settings for global fallback fees and tax
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
$distance_km = max(0.1, (float)$road['km']);

// Fetch passenger vehicle pricing rows
$sql = "SELECT * FROM `service_pricing` WHERE `service_type` = 'passenger' AND `status` = 1 ORDER BY id ASC";
$db->sql($sql);
$pricing_rows = $db->getResult();

if (empty($pricing_rows)) {
    // Fallback defaults if table was empty
    $pricing_rows = array(
        array('vehicle_type' => 'bike', 'display_name' => 'Passenger Bike Taxi', 'base_price' => 25.00, 'base_km' => 1.0, 'per_km_price' => 8.00),
        array('vehicle_type' => 'auto', 'display_name' => 'Passenger Auto Rickshaw', 'base_price' => 40.00, 'base_km' => 1.0, 'per_km_price' => 12.00),
        array('vehicle_type' => 'cab',  'display_name' => 'Passenger Cab / Car', 'base_price' => 70.00, 'base_km' => 1.0, 'per_km_price' => 16.00),
    );
}

$quotes = array();
$selected_quote = null;

foreach ($pricing_rows as $row) {
    $vtype = $row['vehicle_type'];
    $base_price = (float)$row['base_price'];
    $base_km = max(0.0, (float)$row['base_km']);
    $per_km_price = (float)$row['per_km_price'];

    $billable_km = max(0.0, $distance_km - $base_km);
    $km_charge = round($billable_km * $per_km_price, 2);
    $ride_fare = round($base_price + $km_charge, 2);

    // Platform fee, convenience fee and tax inherited directly from Zone Settings
    $zone_fees = $fn->split_zone_fees($ride_fare, $quote_zone, $sys_settings);
    $platform_fee = $zone_fees['platform_fee'];
    $conv_fee = $zone_fees['convenience_fee'];
    $gst = $zone_fees['gst'];
    $grand_total = $zone_fees['grand_total'];

    // Approximate speed & ETA
    $avg_speed_kmh = ($vtype == 'bike') ? 28 : (($vtype == 'auto') ? 22 : 25);
    $eta_minutes = max(3, ceil(($distance_km / $avg_speed_kmh) * 60));

    $item = array(
        'vehicle_type'          => $vtype,
        'display_name'          => $row['display_name'],
        'distance_km'           => round($distance_km, 2),
        'distance_source'       => isset($road['source']) ? $road['source'] : 'fallback',
        'base_price'            => round($base_price, 2),
        'base_km'               => round($base_km, 2),
        'per_km_price'          => round($per_km_price, 2),
        'km_charge'             => round($km_charge, 2),
        'ride_fare'             => round($ride_fare, 2),
        'total_price'           => round($ride_fare, 2),
        'platform_fee'          => round($platform_fee, 2),
        'convenience_fee'       => round($conv_fee, 2),
        'convenience_fee_rate'  => isset($zone_fees['convenience_fee_percent']) ? $zone_fees['convenience_fee_percent'] : 0,
        'gst'                   => round($gst, 2),
        'grand_total'           => round($grand_total, 2),
        'eta_minutes'           => $eta_minutes,
        'capacity_passengers'   => ($vtype == 'bike') ? 1 : (($vtype == 'auto') ? 3 : 4),
        'zone_id'               => $zone_fees['zone_id'],
        'fees_inherited'        => $zone_fees['fees_inherited']
    );

    $quotes[] = $item;
    if ($requested_vehicle !== '' && $vtype === $requested_vehicle) {
        $selected_quote = $item;
    }
}

if ($selected_quote === null && !empty($quotes)) {
    $selected_quote = $quotes[0];
}

$response['error'] = false;
$response['message'] = "Passenger quote calculated successfully";
$response['distance_km'] = round($distance_km, 2);
$response['distance_source'] = isset($road['source']) ? $road['source'] : 'fallback';
$response['data'] = $selected_quote;
$response['vehicles'] = $quotes;

print_r(json_encode($response));
$db->disconnect();
?>
