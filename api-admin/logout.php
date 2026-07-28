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
if(isset($_POST['secretkey']) && isset($_POST['admin_id']) && !empty($_POST['admin_id']) && !empty($_POST['secretkey'])){
     $sql_login="select id from `admin` where id=".$_POST['admin_id']." AND app_login='".$_POST['secretkey']."'";
    // echo $sql_login;
        $db->sql($sql_login);
        $sql_login=$db->getResult();  
        if(count($sql_login)==0){
            $response['error']= true;
            $response['message']="secretkey mismatch";
            print_r(json_encode($response));
            return false;
        }else{
             $sql_login="UPDATE `admin` SET fcm_id='',app_login=0 WHERE id=".$_POST['admin_id'];
             $db->sql($sql_login);
            $response['error']= false;
            $response['message']="LoggedOut Successfully!";
            print_r(json_encode($response));
        }
}else{
    $response['error']= true;
    $response['message']="secretkey mismatch";
    print_r(json_encode($response));
    return false;
}
if(!verify_token()){
	return false;
}