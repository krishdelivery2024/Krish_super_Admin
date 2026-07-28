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

	if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
		$request_type = 'webrequest';
		$accesskey="90336";	
	}else{
		$request_type = 'apprequest';
		if(isset($_POST['accesskey']) && $_POST['accesskey'] != ''){
			$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		}else{
			$response['error']= true;
			$response['message']="accesskey required";
			print_r(json_encode($response));
			return false;
		}
		
	}
	
	if($access_key != $accesskey){
		$response['error']= true;
		$response['message']="invalid accesskey";
		print_r(json_encode($response));
		return false;
	}
	
	/* accesskey:90336 
		city_id:24 */
	if(!verify_token()){
		return false;
	}
	if(isset($_POST['accesskey'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		if($access_key_received == $access_key){
			$sql = "SELECT * FROM delivery_method WHERE id=1";
            $db->sql($sql);
            $res = $db->getResult();  
            
            $delivery_method = new stdClass();
            $delivery_method->in_persion_delivery =$res[0]['in_persion_delivery'];
            $delivery_method->Delivery_by_courier =$res[0]['Delivery_by_courier'];
            $delivery_method->storepickup =$res[0]['storepickup'];
            $delivery_method->dunzo =$res[0]['dunzo'];

            $myJSON = json_encode($delivery_method);
            
            echo $myJSON;

		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}
	$db->disconnect(); 
?>