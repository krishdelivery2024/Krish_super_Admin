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
	$mobile = $db->escapeString($_POST['mobile']);
    $country_code  	= (isset($_POST['country_code']))?$db->escapeString($_POST['country_code']):"";
// 	$fcm_id  	= (isset($_POST['fcm_id']))?$db->escapeString($_POST['fcm_id']):"";
	$fcm_id = '';
	$api_key 	= (isset($_POST['api_key']))?$db->escapeString($_POST['api_key']):"";
	$latitude 	= (isset($_POST['latitude']))?$db->escapeString($_POST['latitude']):"0";
	$longitude 	= (isset($_POST['longitude']))?$db->escapeString($_POST['longitude']):"0";
	if (!empty($mobile)) {
		//$sql = 'select id from users where mobile ='.$mobile.' AND user_type="'.$user_type.'"';
		$sql = 'select id,status from users where mobile ='.$mobile;
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		
		if ($mobile == '7708922414') {
            $otpno = 111222;
        } else {
            $otpno = rand(111111,999999);
        }
		$recipients="91".trim($mobile);

		
		$messagetext = "Thank You for signing up with Krish Delivery . Your OTP for login to Krish Delivery is " . $otpno . ".";

		$template_id = "1207178351325943342";

        $sms_limit_query = "SELECT value FROM settings WHERE variable = 'sms_count'";
        $db->sql($sms_limit_query);
        $sms_result = $db->getResult();
        $sms_count = !empty($sms_result) ? intval($sms_result[0]['value']) : 0;
        $sms_max_limit_count = sms_max_limit_count;

        if ($sms_count >= $sms_max_limit_count) {
            echo json_encode(["error" => true, "message" => "SMS limit reached. Please try again later."]);           
            exit;
        }
		
		if($num_rows > 0){
		    if($res[0]['status'] == 1){
		        $sql = 'UPDATE `users` SET `otp`="'.$otpno.'" WHERE `mobile`="'.$mobile.'"';
		        $db->sql($sql);
    		    sendSmsCommon($recipients, $messagetext, $template_id);

    		    
    		    $sms_count++;
    		    $update_sms_count_sql = "UPDATE settings SET value = '" . intval($sms_count) . "' WHERE variable = 'sms_count'";
    		    $db->sql($update_sms_count_sql);
    		    $db->getResult();

    	        $response["error"]   = false;
    			$response["message"] = "success";
    			echo json_encode($response);
    	    }else{
    	        $response["error"]   = true;
        		$response["message"] = "Account suspended";
        		echo json_encode($response);die();
    	    }
		}else{
		    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		    $referral_code  = "";
        	for ($i = 0; $i < 10; $i++) {
        	    $referral_code .= $chars[mt_rand(0, strlen($chars)-1)];
        	}
        	
		    $data = array(
			    'mobile' => $mobile,
			    'country_code' => $country_code,
			    'fcm_id' => $fcm_id,
			    'apikey' => $api_key,
			    'referral_code' => $referral_code,
			    'otp' => $otpno,
			    'latitude' => $latitude,
			    'longitude' => $longitude,
			    'status' => 1
			);
			$db->insert('users',$data);
			$resu = $db->getResult();
		    $sql = 'select id,status from users where mobile ='.$mobile;
    		$db->sql($sql);
    		$res = $db->getResult();
    		$num_rows = $db->numRows($res);
    		if($num_rows > 0){
    		    sendSmsCommon($recipients, $messagetext, $template_id);

    		    // FIXED: increment the counter here too (new-user signup path).
    		    $sms_count++;
    		    $update_sms_count_sql = "UPDATE settings SET value = '" . intval($sms_count) . "' WHERE variable = 'sms_count'";
    		    $db->sql($update_sms_count_sql);
    		    $db->getResult();

    		    $response["error"]   = false;
    			$response["message"] = "success";
    			echo json_encode($response);die();
    		}else{
    		    $response['error'] = true;
            	$response['message'] = "Something went wrong. Please try again";
            	echo json_encode($response);die();
    		}
	    }
	}else{
    	$response['error'] = true;
    	$response['message'] = "mobile is required.";
    	echo json_encode($response);
	}
}

if((isset($_POST['type'])) && ($_POST['type'] == 'login-user')) {
   
    // get mobile
    $mobile    = $db->escapeString($fn->xss_clean($_POST['mobile']));
	$response = array();
	
    // if mobile is not empty, check in database
    if (!empty($mobile)) {
        if(empty($_POST['otp'])){
            $response['error']     = true;
			$response['message']   = "OTP rquired";
			print_r(json_encode($response));exit;
        }
        // get data from user table
        $sql_query = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name,(SELECT name FROM state s WHERE s.id=u.state) as state_name FROM `users` u WHERE `mobile` = '".$mobile."'";
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
			 
			$fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id']))?$db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
// 			$fcm_id = $fn->generateBeamsToken($result[0]['id']);
			$last_logged=date('Y-m-d H:i:s');
		    $sql = "UPDATE users SET `is_logged_in` = 1, `fcm_id` = '$fcm_id', `last_logged` = '$last_logged' WHERE id = ".$result[0]['id'];
            $db->sql($sql);

			//echo $sql;die;
			foreach($result as $row) {
				$response['error']     = false;
				$response['user_id'] =  $row['id'];
				$response['fcm_id'] =  $fcm_id;
				$response['name']  = $row['name'];
				$response['email']  = $row['email'];
				$response['mobile']  = $row['mobile'];
				$response['country_code'] = $row['country_code'];
				$response['dob'] = $row['dob'];
				$response['balance'] = $row['balance'];
				$response['state_id'] = !empty($row['state'])?$row['state']:'';
				$response['state_name'] = !empty($row['state_name'])?$row['state_name']:'';
				$response['city_id'] = !empty($row['city'])?$row['city']:'';
				$response['city_name'] = !empty($row['city_name'])?$row['city_name']:'';
				$response['area_id'] = !empty($row['area'])?$row['area']:'';
				$response['area_name'] = !empty($row['area_name'])?$row['area_name']:'';
				$response['street']     = $row['street'];
				$response['address']     = $row['address'];
				$response['pincode']     = $row['pincode'];
				$response['referral_code']     = $row['referral_code'];
				$response['friends_code']     = $row['friends_code'];
				$response['latitude']     = (!empty($row['latitude']))?$row['latitude']:'0';
				$response['longitude']     = (!empty($row['longitude']))?$row['longitude']:'0';
				$response['apikey']     = $row['apikey'];
				$response['status']     = $row['status'];
				$response['created_at']     = $row['created_at'];
				// $_SESSION['timeout'] = $currentTime + $expired;
            }
			$response['message'] = "Successfully logged in.";
			// echo json_encode($response);
		}else{
			$response['error']     = true;
			$response['message']   = "Invalid mobile!";
			// echo json_encode($response);
		}
    }
    print_r(json_encode($response));
}
?>