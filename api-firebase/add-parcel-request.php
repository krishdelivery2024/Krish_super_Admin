<?php 
header('Access-Control-Allow-Origin: *');
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db=new Database();
$db->connect();
$response = array();

// Set system timezone so pickup time is validated in the correct timezone
$config = $fn->get_configurations();
if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
	date_default_timezone_set($config['system_timezone']);
}else{
	date_default_timezone_set('Asia/Kolkata');
}

// Check pickup service availability before accepting any request
$sql_check = "SELECT is_service_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
$db->sql($sql_check);
$check_res = $db->getResult();
if(!empty($check_res) && $check_res[0]['is_service_available'] == '0'){
	$response['error'] = true;
	$response['message'] = "Service not available now. Please try again later.";
	print_r(json_encode($response));
	return false;
}

if(isset($_POST['accesskey'])){
	$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
}else{
	$response['error'] = true;
	$response['message'] = "accesskey is require.";
	print_r(json_encode($response));
	return false;
}

if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}

$user_id 		= (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"0";
$item_type_id 	= (isset($_POST['item_type_id']))?$db->escapeString($fn->xss_clean($_POST['item_type_id'])):"0";
$item_type_name = (isset($_POST['item_type_name']))?$db->escapeString($fn->xss_clean($_POST['item_type_name'])):"";
$parcel_image 	= (isset($_POST['parcel_image']))?$db->escapeString($fn->xss_clean($_POST['parcel_image'])):"";
$weight_kg 		= (isset($_POST['weight_kg']))?$db->escapeString($fn->xss_clean($_POST['weight_kg'])):"";
$distance_km 	= (isset($_POST['distance_km']))?$db->escapeString($fn->xss_clean($_POST['distance_km'])):"0";
$per_km_price 	= (isset($_POST['per_km_price']))?$db->escapeString($fn->xss_clean($_POST['per_km_price'])):"0";
$base_price 	= (isset($_POST['base_price']))?$db->escapeString($fn->xss_clean($_POST['base_price'])):"0";
$total_price 	= (isset($_POST['total_price']))?$db->escapeString($fn->xss_clean($_POST['total_price'])):"0";
$payment_status = (isset($_POST['payment_status']))?$db->escapeString($fn->xss_clean($_POST['payment_status'])):"pending";
$payment_method = (isset($_POST['payment_method']))?$db->escapeString($fn->xss_clean($_POST['payment_method'])):"razorpay";
$payment_id 	= (isset($_POST['payment_id']))?$db->escapeString($fn->xss_clean($_POST['payment_id'])):"";
$pickup_location = (isset($_POST['pickup_location']))?$db->escapeString($fn->xss_clean($_POST['pickup_location'])):"";
$pickup_lat 	= (isset($_POST['pickup_lat']))?$db->escapeString($fn->xss_clean($_POST['pickup_lat'])):"";
$pickup_lng 	= (isset($_POST['pickup_lng']))?$db->escapeString($fn->xss_clean($_POST['pickup_lng'])):"";
$sender_name 	= (isset($_POST['sender_name']))?$db->escapeString($fn->xss_clean($_POST['sender_name'])):"";
$sender_phone 	= (isset($_POST['sender_phone']))?$db->escapeString($fn->xss_clean($_POST['sender_phone'])):"";
$pickup_time 	= (isset($_POST['pickup_time']))?$db->escapeString($fn->xss_clean($_POST['pickup_time'])):"";
$drop_location 	= (isset($_POST['drop_location']))?$db->escapeString($fn->xss_clean($_POST['drop_location'])):"";
$drop_lat 		= (isset($_POST['drop_lat']))?$db->escapeString($fn->xss_clean($_POST['drop_lat'])):"";
$drop_lng 		= (isset($_POST['drop_lng']))?$db->escapeString($fn->xss_clean($_POST['drop_lng'])):"";
$recipient_name = (isset($_POST['recipient_name']))?$db->escapeString($fn->xss_clean($_POST['recipient_name'])):"";
$recipient_phone = (isset($_POST['recipient_phone']))?$db->escapeString($fn->xss_clean($_POST['recipient_phone'])):"";

$service_type 			= (isset($_POST['service_type']) && in_array($_POST['service_type'], array('shipping', 'passenger'))) ? $_POST['service_type'] : 'parcel';
$passenger_vehicle 		= (isset($_POST['passenger_vehicle'])) ? $db->escapeString($fn->xss_clean($_POST['passenger_vehicle'])) : null;
$passenger_count 		= (isset($_POST['passenger_count'])) ? max(1, (int)$_POST['passenger_count']) : 1;
$shipping_vehicle_id 	= (isset($_POST['shipping_vehicle_id']) && !empty($_POST['shipping_vehicle_id'])) ? (int)$_POST['shipping_vehicle_id'] : null;
$shipping_vehicle_name 	= (isset($_POST['shipping_vehicle_name'])) ? $db->escapeString($fn->xss_clean($_POST['shipping_vehicle_name'])) : null;
$goods_type 			= (isset($_POST['goods_type'])) ? $db->escapeString($fn->xss_clean($_POST['goods_type'])) : null;
$helper_count 			= (isset($_POST['helper_count'])) ? max(0, (int)$_POST['helper_count']) : 0;
$helper_charge 			= (isset($_POST['helper_charge'])) ? (float)$_POST['helper_charge'] : 0.00;

if ($service_type == 'shipping') {
    $settings_sql = "SELECT is_shipping_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
    $db->sql($settings_sql);
    $s_res = $db->getResult();
    if (!empty($s_res) && isset($s_res[0]['is_shipping_available']) && (int)$s_res[0]['is_shipping_available'] === 0) {
        $response['error'] = true;
        $response['message'] = "Shipping service is currently disabled.";
        print_r(json_encode($response));
        return false;
    }
}
if ($service_type == 'passenger') {
    $settings_sql = "SELECT is_passenger_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
    $db->sql($settings_sql);
    $p_res = $db->getResult();
    if (!empty($p_res) && isset($p_res[0]['is_passenger_available']) && (int)$p_res[0]['is_passenger_available'] === 0) {
        $response['error'] = true;
        $response['message'] = "Passenger ride service is currently disabled.";
        print_r(json_encode($response));
        return false;
    }
}

if(empty($pickup_location) || empty($drop_location)){
	$response['error'] = true;
	$response['message'] = "Pickup and drop location are required.";
	print_r(json_encode($response));
	return false;
}

// Zone-wise service: pickup must be inside an active zone, and the whole
// service area is defined by that pickup zone.
if(empty($pickup_lat) || empty($pickup_lng)){
	$response['error'] = true;
	$response['message'] = "Pickup location coordinates are required. Please select the pickup point on the map.";
	print_r(json_encode($response));
	return false;
}
$pickup_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
if($pickup_zone === null){
	$response['error'] = true;
	$response['message'] = "Pickup is only available inside our service zones. Please move the pickup point inside a zone.";
	print_r(json_encode($response));
	return false;
}

// Per-zone parcel tariff & availability. A NULL zone column inherits the
// global parcel_settings value, so zones with no override are unaffected.
$effective_parcel = $fn->get_effective_parcel_settings($pickup_zone);
if((int)$effective_parcel['is_service_available'] !== 1){
	$response['error'] = true;
	$response['message'] = "Parcel service is not available in this pickup zone right now. Please try again later.";
	print_r(json_encode($response));
	return false;
}

// Daily operating window: a pickup can only be booked between the configured
// opening and closing times (store timezone). No window configured = 24 hours.
if(!$fn->is_parcel_service_open_now($effective_parcel['opening_time'], $effective_parcel['closing_time'])){
	$hours_label = $fn->get_parcel_operating_hours_label();
	$response['error'] = true;
	$response['message'] = ($hours_label !== '')
		? ("Parcel service is currently closed. Pickup is available daily from " . $hours_label . ". Please try again during operating hours.")
		: "Parcel service is currently closed. Please try again later.";
	print_r(json_encode($response));
	return false;
}

// Parcel drop validation: the drop is judged against the pickup's zone only.
// It may sit inside that zone or just outside its boundary, but it must be
// within the admin-configured maximum km of the pickup zone's edge. Measuring
// against "any zone" instead would accept a drop in a far-away zone simply
// because that other zone has a polygon of its own.
if(!empty($drop_lat) && !empty($drop_lng) && is_numeric($drop_lat) && is_numeric($drop_lng)){
	$drop_dist = $fn->get_zone_boundary_distance($drop_lat, $drop_lng, $pickup_zone);
	// Fetch configured max allowed distance (default 5 km)
	$max_km_setting = $fn->get_settings('system_timezone', true);
	$max_km = (isset($max_km_setting['parcel_zone_drop_max_km']) && is_numeric($max_km_setting['parcel_zone_drop_max_km']))
		? (float)$max_km_setting['parcel_zone_drop_max_km']
		: 5.0;
	if($drop_dist > $max_km){
		$pickup_zone_name = $fn->get_zone_name($pickup_zone);
		$zone_label = ($pickup_zone_name !== '') ? $pickup_zone_name : ('zone #' . (int)$pickup_zone);
		$response['error'] = true;
		$response['message'] = "Drop location is too far from the service area of " . $zone_label . ". Maximum allowed distance from that zone's boundary is " . $max_km . " km. Your drop is approximately " . round($drop_dist, 1) . " km away.";
		print_r(json_encode($response));
		return false;
	}
}


if(!empty($pickup_time)){
	$pickup_ts = strtotime($pickup_time);
	if($pickup_ts !== false){
		$pickup_min = (int)date('G', $pickup_ts) * 60 + (int)date('i', $pickup_ts);
		$now_min = (int)date('G') * 60 + (int)date('i');
		if($pickup_min <= $now_min){
			$response['error'] = true;
			$response['message'] = "Please select a future pickup time. Past times are not allowed.";
			print_r(json_encode($response));
			return false;
		}
	}
}

$otp = str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

// Authoritative pricing.
// The client sends distance_km / per_km_price / base_price / total_price only so
// the checkout screen can show a live preview. Everything below is recomputed
// here from the parcel_settings table and the routed driving distance, so a
// tampered request cannot change what the customer is actually charged.
if(empty($drop_lat) || empty($drop_lng) || !is_numeric($drop_lat) || !is_numeric($drop_lng)){
	$response['error'] = true;
	$response['message'] = "Drop location coordinates are required. Please select the drop point on the map.";
	print_r(json_encode($response));
	return false;
}

// Driving distance, not straight line. Falls back to haversine if Google is
// unreachable, which is why distance_source is kept for auditing.
$road_distance = $fn->get_road_distance_km($pickup_lat, $pickup_lng, $drop_lat, $drop_lng);
$distance_km_db = (float)$road_distance['km'];
$distance_source = $road_distance['source'];

// Base price and per km price per service type
if ($service_type == 'passenger') {
    $vtype = !empty($passenger_vehicle) ? $passenger_vehicle : 'bike';
    $db->sql("SELECT base_price, base_km, per_km_price FROM service_pricing WHERE service_type = 'passenger' AND vehicle_type = '$vtype' LIMIT 1");
    $p_res = $db->getResult();
    if (!empty($p_res)) {
        $base_price_db = (float)$p_res[0]['base_price'];
        $base_km_db    = max(0.0, (float)$p_res[0]['base_km']);
        $per_km_price_db = (float)$p_res[0]['per_km_price'];
    } else {
        $base_price_db = 25.0;
        $base_km_db    = 1.0;
        $per_km_price_db = 8.0;
    }
    $billable_km = max(0.0, $distance_km_db - $base_km_db);
    $km_charge = round($billable_km * $per_km_price_db, 2);
    $total_price_db = round($base_price_db + $km_charge, 2);
} else if ($service_type == 'shipping') {
    $sv_res = array();
    if (!empty($shipping_vehicle_id)) {
        $db->sql("SELECT base_price, base_km, per_km_price, helper_price FROM shipping_vehicles WHERE id = " . (int)$shipping_vehicle_id . " AND status = 1 LIMIT 1");
        $sv_res = $db->getResult();
    }
    if (empty($sv_res)) {
        $db->sql("SELECT base_price, base_km, per_km_price, helper_price FROM shipping_vehicles WHERE status = 1 ORDER BY sort_order ASC LIMIT 1");
        $sv_res = $db->getResult();
    }
    if (!empty($sv_res)) {
        $base_price_db   = (float)$sv_res[0]['base_price'];
        $base_km_db      = max(0.0, (float)$sv_res[0]['base_km']);
        $per_km_price_db = (float)$sv_res[0]['per_km_price'];
        $helper_price_db = (float)$sv_res[0]['helper_price'];
    } else {
        $base_price_db   = 150.0;
        $base_km_db      = 2.0;
        $per_km_price_db = 25.0;
        $helper_price_db = 100.0;
    }
    $billable_km = max(0.0, $distance_km_db - $base_km_db);
    $km_charge = round($billable_km * $per_km_price_db, 2);
    $total_helper_charge = round($helper_count * $helper_price_db, 2);
    $total_price_db = round($base_price_db + $km_charge + $total_helper_charge, 2);
} else {
    // Standard parcel service
    $per_km_price_db = $effective_parcel['per_km_price'];
    $base_price_db = $effective_parcel['base_price'];
    $km_charge = ($distance_km_db <= 1.0) ? 0.0 : ($distance_km_db * $per_km_price_db);
    $total_price_db = round($base_price_db + $km_charge, 2);
}

// Discard whatever the client claimed and use the server figures.
$distance_km 	= $distance_km_db;
$per_km_price 	= $per_km_price_db;
$base_price 	= $base_price_db;
$total_price 	= $total_price_db;

// Server-side computation of platform fee, GST & grand total (authoritative, not trusted from client)
$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
$db->sql($sql_settings);
$settings_res = $db->getResult();
$tax_value = 0;
$sys_settings = array();
if (!empty($settings_res)) {
	$sys_settings = json_decode($settings_res[0]['value'], true);
	if (!is_array($sys_settings)) { $sys_settings = array(); }
	$tax_value = isset($sys_settings['tax']) ? floatval($sys_settings['tax']) : 0;
}

// Fees come from the pickup zone, the same zone stored on this request and the
// same zone the quote used, so the preview and the charge cannot disagree. A
// zone with no override falls back to the global setting.
$zone_fees = $fn->split_zone_fees($total_price, $pickup_zone, $sys_settings);
$platform_fee = $zone_fees['platform_fee'];
$convenience_fee = $zone_fees['convenience_fee'];
$gst = $zone_fees['gst'];
$grand_total = $zone_fees['grand_total'];

$data = array(
	'user_id' 		=> $user_id,
	'item_type_id' 	=> $item_type_id,
	'item_type_name'=> $item_type_name,
	'parcel_image' 	=> $parcel_image,
	'weight_kg' 	=> $weight_kg,
	'distance_km' 	=> $distance_km,
	'per_km_price' 	=> $per_km_price,
	'base_price' 	=> $base_price,
	'total_price' 	=> $total_price,
	'platform_fee' 	=> $platform_fee,
	'convenience_fee' => $convenience_fee,
	'gst' 			=> $gst,
	'grand_total' 	=> $grand_total,
	'payment_status'=> $payment_status,
	'payment_method'=> $payment_method,
	'payment_id' 	=> $payment_id,
	'pickup_location' => $pickup_location,
	'pickup_lat' 	=> $pickup_lat,
	'pickup_lng' 	=> $pickup_lng,
	'sender_name' 	=> $sender_name,
	'sender_phone' 	=> $sender_phone,
	'pickup_time' 	=> $pickup_time,
	'drop_location' => $drop_location,
	'drop_lat' 		=> $drop_lat,
	'drop_lng' 		=> $drop_lng,
	'recipient_name'=> $recipient_name,
	'recipient_phone'=> $recipient_phone,
	'service_type'	=> $service_type,
	'shipping_vehicle_id' => $shipping_vehicle_id,
	'shipping_vehicle_name' => $shipping_vehicle_name,
	'goods_type'	=> $goods_type,
	'helper_count'	=> $helper_count,
	'helper_charge'	=> $helper_charge,
	'passenger_vehicle' => $passenger_vehicle,
	'passenger_count' => $passenger_count,
	'status' 		=> 'pending',
	'otp' 			=> $otp,
	'zone_id'		=> $pickup_zone,
	'created_at' 	=> date('Y-m-d H:i:s')
);
$db->insert('parcel_requests',$data);
$insert_result = $db->getResult();
$last_id = !empty($insert_result) ? $insert_result[0] : 0;

if (!empty($last_id) && $last_id > 0 && $payment_status == 'paid' && !empty($payment_id)) {
	// Record the online payment against this parcel request
	$txn_data = array(
		'user_id' 			=> $user_id,
		'order_id' 			=> $last_id,
		'type' 				=> $payment_method,
		'txn_id' 			=> $payment_id,
		'amount' 			=> $grand_total,
		'status' 			=> 'success',
		'message' 			=> 'Parcel Payment Success',
		'transaction_date' 	=> date('Y-m-d H:i:s')
	);
	$db->insert('transactions',$txn_data);
}

if (!empty($last_id) && $last_id > 0) {
	// Two-stage dispatch: Phase-1 notifies only online boys in this pickup
	// zone within 4 km of the pickup point; Phase-2 broadcasts to all online
	// boys across the service area after 60 seconds.
	$message = "New parcel delivery request #" . $last_id . " is ready. Open the app to view the details.";
	$fn->dispatch_new_order('parcel', $last_id, 'New Parcel Order', $message);
}

$response["error"] 	= false;
$response["message"] = "Parcel pickup request submitted successfully";
$response["data"] 	= array(
	'id' => $last_id,
	'otp' => $otp,
	// Echoed back so the app and the audit trail both see the distance that was
	// actually charged, which may differ from the preview if the pin moved.
	'distance_km' => $distance_km,
	'total_price' => $total_price,
	'grand_total' => $grand_total
);
print_r(json_encode($response));
return false;
?>