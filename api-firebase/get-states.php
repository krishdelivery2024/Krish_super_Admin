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
	$db->connect(); 
	include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	/* accesskey:90336 
		city_id:24 */
	if(!verify_token()){
		return false;
	}
	if(isset($_POST['accesskey'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		$state_id = (isset($_POST['v']))?$db->escapeString($fn->xss_clean($_POST['state_id'])):"";
		if($access_key_received == $access_key){
			if (empty($state_id)) {
				$sql = "SELECT * FROM state ORDER BY name ASC";
	
			}else{
			// get all category data from category table
				$sql = "SELECT * FROM state WHERE id = '".$state_id."' ORDER BY name ASC";	
			}
			$db->sql($sql);
			$res=$db->getResult();			
			// // create json output
			if(!empty($res)){
				$response['error'] = false;
				$response['data'] = $res;
			}else{
				$response['error'] = true;
				$response['message'] = "No data found!";
			}
			$output = json_encode($response);
			// print_r(json_encode($res));

		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}
	//Output the output.
	echo $output;
	$db->disconnect(); 
?>