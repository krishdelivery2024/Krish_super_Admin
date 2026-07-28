<?php
header('Access-Control-Allow-Origin: *');
include_once('../api-firebase/send-email.php');
//include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$settings = $function->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
$support_email = $settings['support_email'];
	$config = $function->get_configurations();
		if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
			date_default_timezone_set($config['system_timezone']);
			$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
		}else{
	date_default_timezone_set('Asia/Kolkata');
	$db->sql("SET `time_zone` = '+05:30'");
}
 
$response = array();

if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
   // $function->send_order_update_notification($res[0]['id'],"Your order has been ","Delivery Boy Login Susseccfully",'delivery_boys');
	$accesskey="90336";	
}else{
	if(isset($_POST['accesskey']) && $_POST['accesskey'] != ''){
		$accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));
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

if(isset($_POST['id'])) {
    $id=$_POST['id'];
    	$sql="UPDATE orders SET `payment_status`=1 WHERE id=".$id;
		if($db->sql($sql)){
		    $response['error'] = false;
        	$response['message'] = "Payment Status Updated Successfully";
        	print_r(json_encode($response)); 
		}else{
		    $response['error'] = true;
        	$response['message'] = "Some Error Occured Try Again";
        	print_r(json_encode($response));
		}
}
else{
	$response['error'] = true;
	$response['message'] = "Sorry Invalid order ID";
	print_r(json_encode($response));
}