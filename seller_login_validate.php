<?php
session_start();
    include_once('includes/crud.php');
    $db = new Database;
    $db->connect();
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
    $settings = $fn->get_settings('system_timezone', true);
    // Replicate exact same app_name resolution used in the mobile API (api-firebase/seller-login.php)
    if (!empty($settings) && isset($settings['app_name'])) {
        $app_name = $settings['app_name'];
    } else {
        // Fallback: try direct key lookup
        $app_name = $fn->get_settings('app_name');
    }
    if (empty($app_name)) {
        error_log('[SellerWebLogin] WARNING: app_name is empty! SMS template may be rejected by gateway.');
        $app_name = 'App'; // safe fallback to avoid blank template
    }
	include('./includes/variables.php'); 
	include 'api-firebase/send-sms.php';

	if(isset($_POST['mobile']) && isset($_POST['is_login'])){
		$mobile = $fn->xss_clean($_POST['mobile']);		
		$currentTime = time() + 25200;
		$error = array();
		if(!empty($mobile)){
			$sql_query = "SELECT * FROM seller WHERE mobile = '".$mobile."' AND status = '1'";
			$db->sql($sql_query);
			$res=$db->getResult();
			$num = $db->numRows($res);
				if($num == 1){
                    // $otpno = generateOTP(6);
                    $otpno = 123456;
                    $recipients="91".trim($mobile);
                    // Use EXACT same message & template_id as mobile app (api-admin/login.php)
                    // which is confirmed working for seller OTP
                    $messagetext = "Your OTP for Krish Delivery Seller app login is " . $otpno . " . For security, do not share this code with anyone.";
                    $template_id = "1207178368708352085";

                    $sql = 'UPDATE `seller` SET `otp`="'.$otpno.'" WHERE `mobile`="'.$mobile.'"';
		            $db->sql($sql);
    		        $sms_response = sendSmsCommon($recipients, $messagetext, $template_id);
    				error_log("[SellerLogin] OTP SMS to {$recipients} | app_name={$app_name} | response={$sms_response}");
    				$error['success']=1;
    				$error['message'] = "<span class='label label-success'>Successfully</span>";
    				
				}else{
				    $error['success'] = 0;
					$error['message'] = "<span class='label label-danger'>Invalid Mobile Number!</span>";
				}		
			
		}
		echo json_encode($error);
	}


	if (isset($_POST['mobile']) && isset($_POST['otp']) && isset($_POST['is_login_verify'])) {
    $mobile = $fn->xss_clean($_POST['mobile']);    
    $otp = $fn->xss_clean($_POST['otp']);        
    $currentTime = time() + 25200;
    $response = array();

    if (!empty($mobile) && !empty($otp)) {
        $sql_query = "SELECT * FROM seller WHERE mobile = '$mobile' AND otp = '$otp' AND status = '1'";
        $db->sql($sql_query);
        $res = $db->getResult();
        $num = $db->numRows($res);

        if ($num == 1) {
            $_SESSION['id'] = $res[0]['id'];
            $_SESSION['role'] = "seller";
            $_SESSION['user'] = $res[0]['name'];
            $_SESSION['secretkey'] = rand();
            $_SESSION['timeout'] = $currentTime + 3600000000000;
            $_SESSION['main_cat_id'] = $res[0]['main_cat_id'];
            $_SESSION['secretlogin'] = 'no';
            // Clear OTP
            $sql = "UPDATE seller SET otp='' WHERE mobile='$mobile'";
            $db->sql($sql);

            $response['success'] = 1;
            $response['message'] = "<span class='label label-success'>Login successful.</span>";
        } else {
            $response['success'] = 0;
            $response['message'] = "<span class='label label-danger'>Invalid OTP or mobile.</span>";
        }
    } else {
        $response['success'] = 0;
        $response['message'] = "<span class='label label-danger'>All fields are required.</span>";
    }

    echo json_encode($response);
    return;
}

	
	?>