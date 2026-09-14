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
		// get parcel settings (first row)
		$sql_query = "SELECT per_km_price,base_price,max_weight_kg,terms_conditions 
			FROM parcel_settings 
			ORDER BY id ASC LIMIT 1 ";
		$db->sql($sql_query);
		$res=$db->getResult();
		if (!empty($res)) {
			// append platform fee & tax from store settings (system_timezone row)
			$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
			$db->sql($sql_settings);
			$settings_res = $db->getResult();
			$platform_fee = 0;
			$tax = 0;
			if (!empty($settings_res)) {
				$sys_settings = json_decode($settings_res[0]['value'], true);
				$platform_fee = isset($sys_settings['platform_fee']) ? floatval($sys_settings['platform_fee']) : 0;
				$tax = isset($sys_settings['tax']) ? floatval($sys_settings['tax']) : 0;
			}
			$response['error'] = "false";
			$response['message'] = "Parcel settings retrieved successfully";
			$response['data'] = $res[0];
			$response['data']['platform_fee'] = $platform_fee;
			$response['data']['tax'] = $tax;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
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