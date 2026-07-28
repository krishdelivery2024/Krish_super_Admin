<?php
    header('Access-Control-Allow-Origin: *');
	include_once('../includes/crud.php');
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	// $function = new custom_functions;
    $db = new Database();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    if(!verify_token()){
    	return false;
    }
	if(!isset($_POST['accesskey'])) {
	$output = json_encode(array('error' => true,
	'message' => 'accesskey are required.'));
    echo $output;
	$db->disconnect();
    die;
	}
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if($access_key_received != $access_key){
	$output = json_encode(array('error' => true,
	'message' => 'accesskey is incorrect.'));
    echo $output;
	$db->disconnect();
    die;
	}
        	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        if(empty($user_id)){
            $response['error'] = false;
			$response['message'] = "user_id is empty!";
        }   

			$sql = "SELECT * FROM search_histories WHERE user_id = '$user_id' ORDER BY id DESC";
			$db->sql($sql);
			$res = $db->getResult();
			if (!empty($res)) {
				$response['error'] = false;
				$response['data'] = $res;

			} else {
				$response['error'] = true;
				$response['message'] = "No data found.";
			}
            $output = json_encode($response);
            echo $output;
            $db->disconnect(); 
    
	
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>