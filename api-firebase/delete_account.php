<?php 
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db=new Database();
$db->connect();
$fn = new custom_functions();
$settings = $fn->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
include 'send-email.php';
include 'send-sms.php';

$response = array();
$accesskey = $_POST['accesskey'];
// echo $access_key;

if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'delete_user')) {
    if(!verify_token()){
        return false;
    }
    $user_id = $db->escapeString($_POST['user_id']);
    $sql = "select * from `users` where `id`='".$user_id."'";
	$db->sql($sql);
	$result = $db->getResult();
	if($db->numRows($result)){
        $sql = 'DELETE FROM user_address WHERE user_id ='.$user_id;
        $db->sql($sql);
        $res = $db->getResult();
        
        $sql = 'DELETE FROM users WHERE id ='.$user_id;
        $db->sql($sql);
        $res = $db->getResult();
    
    	$response["error"]   = false;
    	$response["message"] = "User Deleted successfully";
	}else{
		$response["error"]   = true;
		$response["message"] = "Account does not exist!";
	}
	echo json_encode($response);exit;
	
}


?>