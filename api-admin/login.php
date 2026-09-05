<?php 
/*login*/
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
    $db = new Database();
    $db->connect();
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    include_once('send-sms.php');
    date_default_timezone_set('Asia/Kolkata');
   /* accesskey:90336
    mobile:9974692496
    password:36652
    status:1   // 1 - Active & 0 Deactive */
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if($access_key != $accesskey){
    $response['error']= true;
    $response['message']="invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if(!verify_token()){
	return false;
}

if(isset($_POST['mobile']) && isset($_POST['seller_login'])){
		$mobile = $fn->xss_clean($_POST['mobile']);		
		$response = array();
		if(!empty($mobile)){
			$sql_query = "SELECT * FROM seller WHERE mobile = '".$mobile."' AND status = '1'";
			$db->sql($sql_query);
			$res=$db->getResult();
			$num = $db->numRows($res);
				if($num == 1){
                    
                    $otpno = rand(111111,999999);

                    $recipients="91".trim($mobile);

                   
                    $messagetext = "Your OTP for Krish Delivery Seller app login is " . $otpno . " . For security, do not share this code with anyone.";
                    $template_id = "1207178368708352085";

                    $sql = 'UPDATE `seller` SET `otp`="'.$otpno.'" WHERE `mobile`="'.$mobile.'"';
		            $db->sql($sql);

		           
    		        sendSmsCommon($recipients, $messagetext, $template_id);

    				$response['error']=false;
    				$response['message'] = "OTP Sent Successful!";
    				
				}else{
				    $response['error'] = true;
					$response['message'] = "Invalid Mobile Number!";
				}		
			
		}
		print_r(json_encode($response));
}
elseif(isset($_POST['mobile']) &&  isset($_POST['otp']) && isset($_POST['seller_otp_verify'])){
		$mobile = $fn->xss_clean($_POST['mobile']);		
        $otp = $fn->xss_clean($_POST['otp']);	
        $fcm_id = isset($_POST['fcm_id']) ? $fn->xss_clean($_POST['fcm_id']) : '';
		$response = array();
		if(!empty($mobile)){
			$sql_query = "SELECT * FROM seller WHERE mobile = '".$mobile."' AND  otp = '".$otp."' AND status = '1'";
			$db->sql($sql_query);
			$res=$db->getResult();
			$num = $db->numRows($res);
				if($num == 1){

                    $sql = "UPDATE seller SET otp='', fcm_id='$fcm_id' WHERE mobile='$mobile'";
		            $db->sql($sql);

    				$response['error']=false;
                    $response['data'] = $res;
    				$response['message'] = "Login Successful!";
    				
				}else{
				    $response['error'] = true;
                    $response['data'] = [];
					$response['message'] = "Invalid OTP!";
				}		
			
		}
		print_r(json_encode($response));
}

elseif (isset($_POST['seller_register'])) {
    $response = array();

    $name = $db->escapeString($fn->xss_clean($_POST['name'] ?? ''));
    $mobile = $db->escapeString($fn->xss_clean($_POST['mobile'] ?? ''));
    $email = $db->escapeString($fn->xss_clean($_POST['email'] ?? ''));
    $main_cat_id = $db->escapeString($fn->xss_clean($_POST['main_cat_id'] ?? ''));
    $company_name = $db->escapeString($fn->xss_clean($_POST['company_name'] ?? ''));
    $company_legal_name = $db->escapeString($fn->xss_clean($_POST['company_legal_name'] ?? ''));
    $personal_address = $db->escapeString($fn->xss_clean($_POST['personal_address'] ?? ''));
    $company_address = $db->escapeString($fn->xss_clean($_POST['company_address'] ?? ''));
    $state_id = $db->escapeString($fn->xss_clean($_POST['state_id'] ?? ''));
    $city_id = $db->escapeString($fn->xss_clean($_POST['city_id'] ?? ''));
    $area_id = $db->escapeString($fn->xss_clean($_POST['area_id'] ?? ''));
    $dob = $db->escapeString($fn->xss_clean($_POST['dob'] ?? ''));
    $account_details = $db->escapeString($fn->xss_clean($_POST['account_details'] ?? ''));
    $gst_no = $db->escapeString($fn->xss_clean($_POST['gst_no'] ?? ''));
    $pan_no = $db->escapeString($fn->xss_clean($_POST['pan_no'] ?? ''));
     $opening_time = $db->escapeString($fn->xss_clean($_POST['opening_time'] ?? '09:00'));
    $closing_time = $db->escapeString($fn->xss_clean($_POST['closing_time'] ?? '21:00'));


    $status = 0;
    $date_created = date('Y-m-d H:i:s');

    if (
        empty($name) || empty($mobile) || empty($email) || empty($main_cat_id) ||
        empty($company_name) || empty($company_legal_name) ||
        empty($personal_address) || empty($city_id) || empty($area_id)
    ) {
        echo json_encode(["error" => true, "message" => "Please fill in all required fields."]);
        exit;
    }

    $db->sql("SELECT id FROM seller WHERE mobile = '$mobile'");
    if ($db->numRows($db->getResult()) > 0) {
        echo json_encode(["error" => true, "message" => "Mobile number already registered. Please login."]);
        exit;
    }

    $db->sql("SELECT id FROM seller WHERE email = '$email'");
    if ($db->numRows($db->getResult()) > 0) {
        echo json_encode(["error" => true, "message" => "Email address already registered. Please login."]);
        exit;
    }

    $target_dir = "upload/sellers/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_extensions)) {
            $image_name = uniqid("img_") . "." . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
        }
    }

    $banner_name = '';
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed_extensions)) {
            $banner_name = uniqid("banner_") . "." . $ext;
            move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . $banner_name);
        }
    }

    $seller_data = [
        'name' => $name,
        'mobile' => $mobile,
        'email' => $email,
        'main_cat_id' => $main_cat_id,
        'company_name' => $company_name,
        'company_legal_name' => $company_legal_name,
        'personal_address' => $personal_address,
        'company_address' => $company_address,
        'state_id' => $state_id,
        'city_id' => $city_id,
        'area_id' => $area_id,
        'dob' => $dob,
        'account_details' => $account_details,
        'gst_no' => $gst_no,
        'pan_no' => $pan_no,
              'opening_time' => $opening_time,
        'closing_time' => $closing_time,
        'status' => $status,
        'image' => $image_name,
        'banner' => $banner_name,
        'date_created' => $date_created
    ];

    $db->insert('seller', $seller_data);

    echo json_encode([
        "error" => false,
        "message" => "Registration successful! Please wait for admin approval."
    ]);
}



elseif(isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['password']) && $_POST['password'] != '') {

    $username    = $db->escapeString($fn->xss_clean($_POST['username']));
    $password    = $db->escapeString($fn->xss_clean($_POST['password']));
    $secretkey    = !empty($_POST['secretkey'])?$db->escapeString($fn->xss_clean($_POST['secretkey'])):'';
    // set time for session timeout
    // $currentTime = time() + 25200;
    // $expired     = 3600;
	$response = array();
    if (!empty($username) && !empty($password)) {
        // $mobile  = strtolower($mobile);
        $password  = md5($password);
        $sql_query = "SELECT * FROM `admin` WHERE `username` = '".$username."' AND `password` ='".($password)."' AND role='super admin' AND applicable_for='app'";
        $db->sql($sql_query);
        $result=$db->getResult();
		if ($db->numRows($result) > 0) {
		    if($result[0]['app_login']!=0 && $secretkey !=$result[0]['app_login']){
		        $response['error']     = true;
		        $response['message']   = "User Already Logged in";
		       // echo json_encode($response);exit;
		    }
			$secretkey=rand();
			$sql="UPDATE admin SET app_login='".$secretkey."' WHERE id=".$result[0]['id'];
					$db->sql($sql);
			//$fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id']))?$db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
			$fcm_id = $fn->generateBeamsToken('A'.$result[0]['id']);
			if(!empty($fcm_id)){
			    $sql = "update admin set `fcm_id` ='".$fcm_id."' where id = ".$result[0]['id'];
			    $db->sql($sql);
			}
			
			foreach($result as $row) {
			    
                        
				$response['error']     = false;
				$response['admin_id'] =  $row['id'];
				$response['fcm_id'] =  $fcm_id;
				$response['name']   = $row['username'];
				$response['email']   = $row['email'];
				$response['mobile']   = $row['mobile'];
				$response['secretkey'] = $secretkey;	
				$response['country_code'] = $row['country_code'];	
				$response['created_at']     = $row['date_created'];
				$sql="SELECT * FROM settings WHERE  variable='system_timezone'";
                $db->sql($sql);
                $res_time = $db->getResult();
                    if(!empty($res_time)){
                            foreach ($res_time as $row){
                                $id = $row['id'];
                                // echo $id;
                                $data = json_decode($row['value'], true);
                            }
                            // print_r($data);
                        }
                        
				$response['store_name']=$data['app_name'];
                $response['store_state']=$data['store_state'];
                $response['store_city']=$data['store_city'];
                $response['store_zone']=$data['store_zone'];
                $response['store_address']=$data['address'];
                $response['logo'] = DOMAIN_URL.'dist/img/logo.png';
				// $_SESSION['timeout'] = $currentTime + $expired;
            }
			$response['message'] = "Successfully logged in.";
			// echo json_encode($response);
		}else{
			$response['error']     = true;
			$response['message']   = "Invalid User Name or password!";
			// echo json_encode($response);
		}
    }
    print_r(json_encode($response));
}else if(isset($_POST['check_mobile']) && $_POST['mobile'] != '') {

    // get username and password
    $mobile    = $db->escapeString($fn->xss_clean($_POST['mobile']));
    // set time for session timeout
    // $currentTime = time() + 25200;
    // $expired     = 3600;
	$response = array();
    // if username and password is not empty, check in database
    if (!empty($mobile)) {
        // change username to lowercase
        // $mobile  = strtolower($mobile);
        // encript password to sha256
        $password  = md5($password);
        // get data from user table
        $sql_query = "SELECT * FROM `admin` WHERE `mobile` = '".$mobile."' AND role='super admin'";
        $db->sql($sql_query);
        $result=$db->getResult();
		if ($db->numRows($result) > 0) {
		    if($secretkey !=$result[0]['app_login']){
		        $response['error']     = true;
		        $response['message']   = "Only one login Allowed";
		       // echo json_encode($response);exit;
		    }
			$response['error']     = false;
			$response['message'] = "Mobile Number found SMS verify.";
			// echo json_encode($response);
		}else{
			$response['error']     = true;
			$response['message']   = "Invalid Mobile!";
			// echo json_encode($response);
		}
    }
    print_r(json_encode($response));
}else if(isset($_POST['login_confirm']) && $_POST['mobile'] != '') {

    // get username and password
    $mobile    = $db->escapeString($fn->xss_clean($_POST['mobile']));
    // set time for session timeout
    // $currentTime = time() + 25200;
    // $expired     = 3600;
	$response = array();
    // if username and password is not empty, check in database
    if (!empty($mobile)) {
        // change username to lowercase
        // $mobile  = strtolower($mobile);
        // encript password to sha256
        //$password  = md5($password);
        // get data from user table
        $sql_query = "SELECT * FROM `admin` WHERE `mobile` = '".$mobile."' AND role='super admin'";
        $db->sql($sql_query);
        $result=$db->getResult();
		if ($db->numRows($result) > 0) {
		    if($secretkey !=$result[0]['app_login']){
		        $response['error']     = true;
		        $response['message']   = "Only one login Allowed";
		       // echo json_encode($response);exit;
		    }
			$secretkey=rand();
			$sql="UPDATE admin SET app_login='".$secretkey."' WHERE id=".$result[0]['id'];
					$db->sql($sql);
			$fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id']))?$db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
			if(!empty($fcm_id)){
			    $sql = "update admin set `fcm_id` ='".$fcm_id."' where id = ".$result[0]['id'];
			    $db->sql($sql);
			}
			
			foreach($result as $row) {
			    
                        
				$response['error']     = false;
				$response['admin_id'] =  $row['id'];
				$response['name']   = $row['username'];
				$response['email']   = $row['cemail'];
				$response['mobile']   = $row['cmobile'];
				$response['secretkey'] = $secretkey;	
				$response['country_code'] = $row['country_code'];	
				$response['created_at']     = $row['created_at'];
				$sql="SELECT * FROM settings WHERE  variable='system_timezone'";
                $db->sql($sql);
                $res_time = $db->getResult();
                    if(!empty($res_time)){
                            foreach ($res_time as $row){
                                $id = $row['id'];
                                // echo $id;
                                $data = json_decode($row['value'], true);
                            }
                            // print_r($data);
                        }
				$response['store_name']=$data['app_name'];
				$response['logo'] = DOMAIN_URL.'dist/img/logo.png';
				// $_SESSION['timeout'] = $currentTime + $expired;
            }
			$response['message'] = "Successfully logged in.";
			// echo json_encode($response);
		}else{
			$response['error']     = true;
			$response['message']   = "Invalid User Name or password!";
			// echo json_encode($response);
		}
    }
    print_r(json_encode($response));
}else{
    // check whether $username is empty or not
    // if (empty($username) or ($password)) {
        $response['message'] = "User Name and password should be filled";
        // echo json_encode($response);
    // }
    // check whether $password is empty or not
    // if (empty($password)) {
    //     $response['message'] = "Password should be filled";
    //     // echo json_encode($response);
    // }
        print_r(json_encode($response));

}
$db->disconnect();
?>