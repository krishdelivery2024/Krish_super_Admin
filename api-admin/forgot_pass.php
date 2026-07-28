<?php 
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);
include '../includes/variables.php';
include_once('../api-firebase/verify-token.php');
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

if ((isset($_POST['type'])) && ($_POST['type'] == 'verify-user')) {
    if(!verify_token()){
        return false;
    }
	$mobile = $db->escapeString($_POST['mobile']);
// 	$country_code = $db->escapeString($_POST['country_code']);
	if (!empty($mobile)) {
		$sql = 'select id from admin where mobile ='.$mobile;
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if($num_rows > 0){
			$response["error"]   = true;
			$response["id"]   = $res[0]['id'];
			$response["message"] = "This mobile is already registered. Please login!";
			echo json_encode($response);
		}else if($num_rows == 0){
		    $response["error"]   = false;
			$response["message"] = "Ready to sent firebase OTP request!";
			
			echo json_encode($response);
		}
	}
	else{
	$response['error'] = true;
	$response['message'] = "mobile is required.";
	echo json_encode($response);
	}
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'change-password') {
    if(!verify_token()){
        return false;
    }
    
    $id   	= $db->escapeString($_POST['mobile']);
    $password = $db->escapeString($_POST['password']);
    // $password = 'test1234';
    $password = md5($password);
    
    // if(!empty($password)) {
    	$sql = 'UPDATE `admin` SET `password`="'.$password.'" WHERE `mobile`="'.$id.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Password updated successfully";
		}else{
			$response["error"]   = true;
			$response["message"] = "Something went wrong! Try Again!";
		}
	echo json_encode($response);
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-email') {
    if(!verify_token()){
        return false;
    }
    $email  = $db->escapeString($_POST['email']);
    $password = rand(10000,99999);
	$encrypted_password = md5($password);
	
	$sql = "select `id`,`username` from `admin` where `email`='".$email."'";
	$db->sql($sql);
	$result = $db->getResult();
	if($db->numRows($result)){
		//send email
		$to = $email;
		$subject = "Password Recovery Mail - Password is reset ( $email )";
		$message = "Hi, <b>".$row['username']." - ".$email."</b>, \t\r\n Your Password has been reset. Please Login with the new password. \r\nYour new Password is : ".$password."\r\n Thank you";
		
		if(!send_email($to,$subject,$message)){
			$response["error"]   = true;
			$response["message"] = "Password could not be reset!Try Again";
			echo json_encode($response);
			return false;
		}
		$sql = 'UPDATE `admin` SET `password`="'.$encrypted_password.'" WHERE `email`="'.$email.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Password updated successfully! Please check the mail!";
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Email ID does not exist!";
	}
	echo json_encode($response);
}


if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-mobile') {
    if(!verify_token()){
        return false;
    }
    $mobile  = $db->escapeString($_POST['mobile']);
    $password = rand(10000,99999);
    //$password = 'test1234';
	$encrypted_password = md5($password);
	$sql = "select `id`,`username`,`country_code` from `admin` where `mobile`='".$mobile."'";
// 	echo $sql;
	$db->sql($sql);
	$result = $db->getResult();
	
	if($db->numRows($result) > 0){
	    $country_code = $result[0]['country_code'];
		//send sms
	//	$message = 'Your Password for '.$app_name.' is Reset. Please login using new Password : '.$password.'.';
	//	$sql = 'UPDATE `admin` SET `password`="'.$encrypted_password.'" WHERE `mobile`="'.$mobile.'"';
		if($db->sql($sql)){
		        // sendSms($mobile,$message,$country_code);    
		        $response["error"]   = false;
			    $response["message"] = "Password is sent successfully! Please login via the OTP sent to your mobile number!";
			
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number does not exist! Please Register";
	}
	echo json_encode($response);
}


if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'delete-notification') {
	if($permissions['notifications']['delete']==0){
		echo 2;
		return false;
	}
    $id		= $_POST['id'];
    $image 	= $_POST['image'];
	
	if(!empty($image))
		unlink('../'.$image);
	
	$sql = 'DELETE FROM `notifications` WHERE `id`='.$id;
	if($db->sql($sql)){
		echo 1;
	}else{
		echo 0;
	}
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'register-device') {
    if(!verify_token()){
        return false;
    }
    $user_id  = $db->escapeString($_POST['user_id']);
    $token  = $db->escapeString($_POST['token']);
    
    $sql = "select `id` from `users` where `id`='".$user_id."'";
	$db->sql($sql);
	$result=$db->getResult();
	if($db->numRows($result) > 0){
		// Update the Device ID
		$sql = 'UPDATE `users` SET `fcm_id`="'.$token.'" WHERE `id`="'.$user_id.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Device updated successfully";
		}
	}else{
	    // $sql = "INSERT INTO devices (user_id, token) VALUES ('$user_id','$token')";
	    // $db->sql($sql);
		$response["error"]   = true;
		$response["message"] = "User does't exists.";
	}
	echo json_encode($response);
}

if(isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'send-invitation') {
    if(!verify_token()){
        return false;
    }
	$referral_code = $db->escapeString($_POST['referral_code']);
    $friend_id  = $db->escapeString($_POST['friend_id']);
    $sql = "select * from `users` where `referral_code`='".$referral_code."'";
	$db->sql($sql);
	$result=$db->getResult();
	if($db->numRows($result) > 0){
		// Update the Device ID
		$sql = 'UPDATE `users` SET `friends_code`="'.$referral_code.'" WHERE `id`="'.$friend_id.'"';
		if($db->sql($sql)){
			$response["error"]   = false;
			$response["message"] = "Invitation sent successfully";
			$response['data'] = $result;
		}
	}else{
		$response["error"]   = true;
		$response["message"] = "Invalid referral code.";
	}
	echo json_encode($response);
}


?>