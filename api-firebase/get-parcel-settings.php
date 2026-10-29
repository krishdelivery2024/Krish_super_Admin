<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db=new Database();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db->connect(); 
include_once('../includes/variables.php');
include_once('verify-token.php');
/* accesskey:90336 */
if(!verify_token()){
    return false;
}
if(isset($_POST['accesskey'])) {
	$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));		
	if($access_key_received == $access_key){
		// Optional: when the app knows the pickup point, resolve its zone and
		// return that zone's effective tariff/availability. Without coords the
		// global parcel settings are returned (unchanged behaviour).
		$zone_id = null;
		$pickup_lat = isset($_POST['pickup_lat']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lat'])) : '';
		$pickup_lng = isset($_POST['pickup_lng']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lng'])) : '';
		if(!empty($pickup_lat) && !empty($pickup_lng) && is_numeric($pickup_lat) && is_numeric($pickup_lng)){
			$zone_id = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
		}
		$effective = $fn->get_effective_parcel_settings($zone_id);
		// The service is only bookable inside the configured operating window,
		// so availability reported to the app combines the manual switch with
		// the daily hours. The raw switch is echoed separately for clarity.
		$is_open_now = $fn->is_parcel_service_open_now($effective['opening_time'], $effective['closing_time']);
		$hours_label = $fn->get_parcel_operating_hours_label();
		$data = array(
			'per_km_price'         => $effective['per_km_price'],
			'base_price'           => $effective['base_price'],
			'max_weight_kg'        => $effective['max_weight_kg'],
			'terms_conditions'     => $effective['terms_conditions'],
			'is_service_available' => ((int)$effective['is_service_available'] === 1 && $is_open_now) ? 1 : 0,
			'is_enabled'           => (int)$effective['is_service_available'],
			'is_open_now'          => $is_open_now ? 1 : 0,
			'opening_time'         => ($effective['opening_time'] !== null) ? substr($effective['opening_time'], 0, 5) : '',
			'closing_time'         => ($effective['closing_time'] !== null) ? substr($effective['closing_time'], 0, 5) : '',
			'operating_hours'      => $hours_label,
			'zone_id'              => $effective['zone_id'],
			'zone_overrides'       => $effective['overrides'],
			'cod_payment_method'   => (string)$effective['cod_payment_method'],
			'razorpay_payment_method' => (string)$effective['razorpay_payment_method'],
			'is_shipping_available' => (string)(isset($effective['is_shipping_available']) ? $effective['is_shipping_available'] : '1'),
			'is_passenger_available' => (string)(isset($effective['is_passenger_available']) ? $effective['is_passenger_available'] : '1'),
		);
		{
			// append platform fee & tax from store settings (system_timezone row)
			$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
			$db->sql($sql_settings);
			$settings_res = $db->getResult();
			$platform_fee = 0;
			$tax = 0;
			$convenience_fee = 0;
			$sys_settings = array();
			if (!empty($settings_res)) {
				$sys_settings = json_decode($settings_res[0]['value'], true);
				$platform_fee = isset($sys_settings['platform_fee']) ? floatval($sys_settings['platform_fee']) : 0;
				$tax = isset($sys_settings['tax']) ? floatval($sys_settings['tax']) : 0;
				$convenience_fee = isset($sys_settings['convenience_fee']) ? floatval($sys_settings['convenience_fee']) : 0;
			}
			$response['error'] = "false";
			$response['message'] = "Parcel settings retrieved successfully";
			$response['data'] = $data;
			$response['data']['platform_fee'] = $platform_fee;
			$response['data']['tax'] = $tax;
			$response['data']['convenience_fee'] = $convenience_fee;
			// Max parcel drop distance from zone boundary (admin-configurable, default 5 km)
			$parcel_zone_drop_max_km = isset($sys_settings['parcel_zone_drop_max_km']) ? floatval($sys_settings['parcel_zone_drop_max_km']) : 5.0;
			$response['data']['parcel_zone_drop_max_km'] = $parcel_zone_drop_max_km;
		}
	print_r(json_encode($response));
	}else{
		die('accesskey is incorrect.');
	}
} else {
	die('accesskey is require.');
}
$db->disconnect(); 
?>