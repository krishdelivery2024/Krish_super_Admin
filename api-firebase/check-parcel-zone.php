<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include('../includes/crud.php');
include('../includes/variables.php');
include_once('../includes/custom-functions.php');

$db = new Database();
$db->connect();
$fn = new custom_functions;

$response = array();

if(!isset($_POST['pickup_lat']) || !isset($_POST['pickup_lng']) || !isset($_POST['drop_lat']) || !isset($_POST['drop_lng'])) {
	$response['error'] = true;
	$response['message'] = "Please provide pickup and drop coordinates.";
	print_r(json_encode($response));
	return false;
}

$pickup_lat = $db->escapeString($fn->xss_clean($_POST['pickup_lat']));
$pickup_lng = $db->escapeString($fn->xss_clean($_POST['pickup_lng']));
$drop_lat = $db->escapeString($fn->xss_clean($_POST['drop_lat']));
$drop_lng = $db->escapeString($fn->xss_clean($_POST['drop_lng']));

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

if(!empty($drop_lat) && !empty($drop_lng) && is_numeric($drop_lat) && is_numeric($drop_lng)){
	$drop_zone = $fn->get_zone_id_from_latlng($drop_lat, $drop_lng);
	if($drop_zone === null){
		// Outside all zones – check distance from nearest zone boundary
		$drop_dist = $fn->get_nearest_zone_distance($drop_lat, $drop_lng);
		// Fetch configured max allowed distance (default 5 km)
		$max_km_setting = $fn->get_settings('system_timezone', true);
		$max_km = (isset($max_km_setting['parcel_zone_drop_max_km']) && is_numeric($max_km_setting['parcel_zone_drop_max_km']))
			? (float)$max_km_setting['parcel_zone_drop_max_km']
			: 5.0;
		if($drop_dist > $max_km){
			$response['error'] = true;
			$response['message'] = "Drop location is too far from our service area. Maximum allowed distance from zone boundary is " . $max_km . " km. Your drop is approximately " . round($drop_dist, 1) . " km away.";
			print_r(json_encode($response));
			return false;
		}
	}
}

$response['error'] = false;
$response['message'] = "Locations are within valid service zones.";
print_r(json_encode($response));
return false;
?>
