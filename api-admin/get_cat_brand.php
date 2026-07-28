<?php 
/*login*/
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

if(isset($_POST['type'])){
    if($_POST['type']=="category"){
        
		
		$sql = "select id,name FROM category";
    $db->sql($sql);
    $res = $db->getResult();
        
        if(!empty($res)){
            $response['error'] = false;
            $response['data'] = ($res);
          
        }else{
            $response['error'] = true;
            $response['data']=array();
            $response['message'] = "No Categories found!";
            // return $res;
        }
    }else if($_POST['type']=="subcategory"){
		if(isset($_POST['category_id'])){
		    
		}else{
		    $sql = "select id,name FROM subcategory WHERE category_id=".$_POST['category_id'];
		}
		
    $db->sql($sql);
    $res = $db->getResult();
        if(!empty($res)){
            $response['error'] = false;
            $response['data'] = ($res);
          
        }else{
            $response['error'] = true;
            $response['data']=array();
            $response['message'] = "No Sub Categories found!";
            // return $res;
        }
    }else if($_POST['type']=="brand"){
    	$sql="SELECT id,name FROM brand";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="unit"){
    	$sql="SELECT id,short_code AS name FROM unit";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
}else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
echo json_encode($response);
$db->disconnect();
?>