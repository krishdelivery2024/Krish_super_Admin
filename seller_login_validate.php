<?php
session_start();
    include_once('includes/crud.php');
    $db = new Database;
    $db->connect();
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
    $settings = $fn->get_settings('system_timezone',true);
    $app_name = $settings['app_name'];
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
                    $otpno = generateOTP(6);
                    $otpno ="555555";

                    $recipients="91".trim($mobile);
                    $messagetext="Your OTP for $app_name is ".$otpno.". Please do not share this OTP.";
                    $template_id="1407168862906996721";
                    $sql = 'UPDATE `seller` SET `otp`="'.$otpno.'" WHERE `mobile`="'.$mobile.'"';
		            $db->sql($sql);
    		        // sendSmsCommon($recipients, $messagetext, $template_id);
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