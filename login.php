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

if(isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['password']) && $_POST['password'] != '') {

    // get username and password
    $username    = $db->escapeString($fn->xss_clean($_POST['username']));
    $password    = $db->escapeString($fn->xss_clean($_POST['password']));
    $secretkey    = $db->escapeString($fn->xss_clean($_POST['secretkey']));
    // set time for session timeout
    // $currentTime = time() + 25200;
    // $expired     = 3600;
	$response = array();
    // if username and password is not empty, check in database
    if (!empty($username) && !empty($password)) {
        // change username to lowercase
        // $mobile  = strtolower($mobile);
        // encript password to sha256
        $password  = md5($password);
        // get data from user table
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