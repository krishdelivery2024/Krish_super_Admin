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
		
		/* The state/city/area columns are supposed to hold ids, but older records
			store the plain name instead. Resolving a purely numeric value through
			the lookup tables keeps the real name, while a non numeric value is
			taken as the name itself. A numeric value with no matching row stays NULL
			so the app asks for a proper selection instead of showing a bare id. */
		$sql_query = "SELECT * ,
			CASE WHEN u.state REGEXP '^[0-9]+$' THEN (select name from state s where s.id=u.state)
				WHEN TRIM(u.state) = '' OR u.state = '0' THEN NULL
				ELSE TRIM(u.state) END as state_name ,
			CASE WHEN u.city REGEXP '^[0-9]+$' THEN (select name from city c where c.id=u.city)
				WHEN TRIM(u.city) = '' OR u.city = '0' THEN NULL
				ELSE TRIM(u.city) END as city_name ,
			CASE WHEN u.area REGEXP '^[0-9]+$' THEN (select name from area a where a.id=u.area)
				WHEN TRIM(u.area) = '' OR u.area = '0' THEN NULL
				ELSE TRIM(u.area) END as area_name
			FROM users u WHERE status = '1' AND id ='$user_id'  
			ORDER BY id ASC ";
		$db->sql($sql_query);
		$res=$db->getResult();
		if (!empty($res)) {
						for ($i = 0; $i < count($res); $i++) {
				$res[$i]['profile'] = !empty($res[$i]['profile']) ? DOMAIN_URL . $res[$i]['profile'] : '';
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