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
    $main_cat = (isset($_POST['main_cat_id']))?$db->escapeString($fn->xss_clean($_POST['main_cat_id'])):"0";
	$user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
	// Optional user location for zone-based filtering
	$latitude  = (isset($_POST['latitude']))?$db->escapeString($fn->xss_clean($_POST['latitude'])):"";
	$longitude = (isset($_POST['longitude']))?$db->escapeString($fn->xss_clean($_POST['longitude'])):"";
	$user_zone_id = $fn->get_zone_id_from_latlng($latitude, $longitude);
	if($user_zone_id === null && $user_id != ''){
	    // Fallback: derive zone from the user's saved delivery address
	    $db->sql("SELECT latitude, longitude FROM user_address WHERE user_id = '$user_id' AND status = '1' ORDER BY is_default DESC, id DESC LIMIT 1");
	    $saved_addr = $db->getResult();
	    if (!empty($saved_addr)) {
	        $user_zone_id = $fn->get_zone_id_from_latlng($saved_addr[0]['latitude'], $saved_addr[0]['longitude']);
	    }
	}
	$zone_filter = ($user_zone_id > 0) ? " AND zone_id = '".(int)$user_zone_id."'" : " AND zone_id = '-1'";
	if($access_key_received == $access_key){
		// get all category data from category table
		$sql_query = "SELECT * 
			FROM seller 
            WHERE main_cat_id ='$main_cat' AND status='1' AND main_cat_id IN (SELECT id FROM main_category WHERE status = '1')".$zone_filter."
			ORDER BY sel_priority ASC ";
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