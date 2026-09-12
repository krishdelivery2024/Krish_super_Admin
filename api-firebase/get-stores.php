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
	$user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";	
	if($access_key_received == $access_key){
		// get all category data from category table
		$sql_query = "SELECT * 
			FROM seller 
            WHERE main_cat_id ='2' AND status='1'
			ORDER BY id ASC ";
		$db->sql($sql_query);
		$res=$db->getResult();
		if (!empty($res)) {
			for ($i = 0; $i < count($res); $i++) {
				$seller_id = $res[$i]['id'];
				
				// Check if this seller is in the user's wishlist
				$sql_query = "SELECT seller_id FROM wishlists WHERE user_id = '$user_id' AND seller_id = '$seller_id'";
				$db->sql($sql_query);
				$wishlist_result = $db->getResult();

				// Add wishlist status to each seller
				$res[$i]['wishlist'] = !empty($wishlist_result);

				// Add image and banner URLs
				$res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . 'upload/sellers/' . $res[$i]['image'] : '';
				$res[$i]['banner'] = (!empty($res[$i]['banner'])) ? DOMAIN_URL . 'upload/sellers/' . $res[$i]['banner'] : '';
			}

			// Fetch global delivery_time_per_km from settings
			$delivery_time_per_km = 5; // default
			$db->sql("SELECT value FROM settings WHERE variable = 'system_timezone'");
			$settings_res = $db->getResult();
			if (!empty($settings_res)) {
				$sys_settings = json_decode($settings_res[0]['value'], true);
				if (isset($sys_settings['delivery_time_per_km'])) {
					$delivery_time_per_km = intval($sys_settings['delivery_time_per_km']);
				}
			}
			$response['delivery_time_per_km'] = $delivery_time_per_km;
			$response['error'] = "false";
			$response['data'] = $res;
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
//Output the output.
// echo $output;
$db->disconnect(); 
?>