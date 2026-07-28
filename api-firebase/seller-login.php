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

if ((isset($_POST['type'])) && ($_POST['type'] == 'verify-user')) {
    if(!verify_token()){
        return false;
    }
	$mobile = isset($_POST['mobile']) ? $db->escapeString($_POST['mobile']) : "";
    $country_code  	= (isset($_POST['country_code']))?$db->escapeString($_POST['country_code']):"";

	if (!empty($mobile)) {
		$sql = 'select id,status from seller where mobile ='.$mobile;
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		
		$otpno = generateOTP(6);
		$recipients="91".trim($mobile);
		$messagetext="Your OTP for $app_name is ".$otpno.". Please do not share this OTP.";
		$template_id="1407168862906996721";
		
		if($num_rows > 0){
		    if($res[0]['status'] == 0){
    	        $response["error"]   = true;
        		$response["message"] = "Account Not Active";
        		echo json_encode($response);die();
    	    }else if($res[0]['status'] == 1){
		        $sql = 'UPDATE `seller` SET `otp`="'.$otpno.'" WHERE `mobile`="'.$mobile.'"';
		        $db->sql($sql);
    		    sendSmsCommon($recipients, $messagetext, $template_id);
    	        $response["error"]   = false;
    			$response["message"] = "success";
    			echo json_encode($response);
    	    }else{
    	        $response["error"]   = true;
        		$response["message"] = "Account Suspended";
        		echo json_encode($response);die();
    	    }
		}else{
    	        $response["error"]   = true;
        		$response["message"] = "Account Not Registered";
        		echo json_encode($response);die();
	    }
	}else{
    	$response['error'] = true;
    	$response['message'] = "mobile is required.";
    	echo json_encode($response);
	}
}

if((isset($_POST['type'])) && ($_POST['type'] == 'login-user')) {
   
    // get mobile
    $mobile = isset($_POST['mobile']) ? $db->escapeString($_POST['mobile']) : "";
	$response = array();
	
    // if mobile is not empty, check in database
    if (!empty($mobile)) {
        if(empty($_POST['otp'])){
            $response['error']     = true;
			$response['message']   = "OTP rquired";
			print_r(json_encode($response));exit;
        }
        // get data from user table
        $sql_query = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area_id) as area_name,(SELECT name FROM city c WHERE c.id=u.city_id) as city_name,(SELECT name FROM state s WHERE s.id=u.state_id) as state_name FROM `seller` u WHERE `mobile` = '".$mobile."'";
        $db->sql($sql_query);
        $result=$db->getResult();
       
		if ($db->numRows($result) > 0) {
			if(empty($result[0]['status'])){
			 	$response['error']     = true;
    			$response['message']   = "Account deactive";
    			 print_r(json_encode($response));exit;
    		}
    		$otp = trim($_POST['otp']);
    		if($result[0]['otp'] != $otp){
    		    $response['error']     = true;
    			$response['message']   = "Invalid OTP";
    			print_r(json_encode($response));exit;
    		}
			 
			$last_logged=date('Y-m-d H:i:s');
		    $sql = "UPDATE seller SET  `otp` = '', `last_updated` = '$last_logged' WHERE id = ".$result[0]['id'];
            $db->sql($sql);

			foreach($result as $row) {
				$response['error']     = false;
				$response['seller_id'] =  $row['id'];
				$response['name']  = $row['name'];
                $response['mobile']  = $row['mobile'];
				$response['email']  = $row['email'];				
				$response['state_id'] = !empty($row['state_id'])?$row['state_id']:'';
				$response['state_name'] = !empty($row['state_name'])?$row['state_name']:'';
				$response['city_id'] = !empty($row['city_id'])?$row['city_id']:'';
				$response['city_name'] = !empty($row['city_name'])?$row['city_name']:'';
				$response['area_id'] = !empty($row['area_id'])?$row['area_id']:'';
				$response['area_name'] = !empty($row['area_name'])?$row['area_name']:'';
				$response['status']     = $row['status'];
				$response['created_at']     = $row['date_created'];
            }
			$response['message'] = "Successfully logged in.";
		}else{
			$response['error']     = true;
			$response['message']   = "Invalid mobile!";
		}
    }else{
        $response['error'] = true;
    	$response['message'] = "mobile is required.";
    }
    print_r(json_encode($response));
}
?>