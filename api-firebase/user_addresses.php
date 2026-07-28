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


if ((isset($_POST['type'])) && ($_POST['type'] == 'list_address')) {
    $user_id = $db->escapeString($_POST['user_id']);
    
    $sql = "select u.*, (SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name from user_address u where u.user_id ='$user_id' order by u.is_default desc" ;
    
   // $sql = 'select u.*, (SELECT name FROM city c WHERE c.id=u.city) AS city_name, IFNULL((SELECT name FROM area a WHERE a.id=u.area) , "")  AS area_name from user_address u where user_id ='.$user_id;
    
    $db->sql($sql);
    $res = $db->getResult();
	if($res){
		$response["error"]   = false;
		$response['data'] = $res;
		print_r(json_encode($response));
	    return false;
	}else{
	    $response["error"]   = true;
		$response['data'] = array();
		print_r(json_encode($response));
	    return false;
	}
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'add_address')) {
    $user_id = $db->escapeString($_POST['user_id']);
	$address_type 		= (isset($_POST['address_type']))?$db->escapeString($_POST['address_type']):"";
    $name 		= (isset($_POST['name']))?$db->escapeString($_POST['name']):"";
    $mobile 		= (isset($_POST['mobile']))?$db->escapeString($_POST['mobile']):"";
    $state 		= (isset($_POST['state_id']))?$db->escapeString($_POST['state_id']):"";
    $city 		= (isset($_POST['city_id']))?$db->escapeString($_POST['city_id']):"";
    $area 		= (isset($_POST['area_id']))?$db->escapeString($_POST['area_id']):"";
    $landmark 		= (isset($_POST['landmark']))?$db->escapeString($_POST['landmark']):"";
    $flat_no 		= (isset($_POST['flat_no']))?$db->escapeString($_POST['flat_no']):"";
	$street 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$address 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$pincode 	= (isset($_POST['pincode']))?$db->escapeString($_POST['pincode']):"";
	$latitude 	= (isset($_POST['latitude']))?$db->escapeString($_POST['latitude']):"0";
	$longitude 	= (isset($_POST['longitude']))?$db->escapeString($_POST['longitude']):"0";
	$email  	= ( isset($_POST['email']) && !empty($_POST['email']) )?$db->escapeString($_POST['email']):"";
	if(isset($_POST['friends_code']) && $_POST['friends_code'] != ''){
		$sql = "SELECT id FROM users WHERE referral_code='".$_POST['friends_code']."'";
		$db->sql($sql);
		$result = $db->getResult();
		$num_rows = $db->numRows($result);
		if($num_rows > 0){
			$friends_code = $_POST['friends_code'];
		}else{
			$response["error"]   = true;
			$response["message"] = "Invalid friends code!";
			echo json_encode($response);
			return false;
		}
	}else{
		$friends_code = '';
	}
	$is_default = 0;
	$sql = 'select * from user_address where user_id ='.$user_id;
	$db->sql($sql);
	$resu = $db->getResult();
	$num_rows = $db->numRows($resu);

	if($num_rows == 0){
    	$data1 = array(
			'address_type' => $address_type,
    	    'name' => $name,
    	    'email' => $email,
    	    'state' => $state,
    	    'city' => $city,
    	    'area' => $area,
    	    'street' => $street,
    	    'address' => $street,
    	    'pincode' => $pincode,
    	    'friends_code' => $friends_code,
    	    'latitude' => $latitude,
    	    'longitude' => $longitude
    	);
    	$db->update('users',$data1,'id='.$user_id);
    	$is_default = 1;
	}	
	$data = array(
		'address_type' => $address_type,
		'name' => $name,
	    'mobile' => $mobile,
	    'email' => $email,
	    'user_id' => $user_id,
	    'state' => $state,
	    'city' => $city,
	    'area' => $area,
	    'landmark' => $landmark,
	    'flat_no' => $flat_no,
	    'street' => $street,
		'address' => $address,
	    'pincode' => $pincode,
	    'latitude' => $latitude,
	    'longitude' => $longitude,
	    'is_default' => $is_default,
	    'status' => 1
	);
	$db->insert('user_address',$data);
	$response["error"]   = false;
	$response["message"] = "Address Added successfully";
	print_r(json_encode($response));
    return false;
}
if ((isset($_POST['type'])) && ($_POST['type'] == 'edit_address')) {
    $user_id = $db->escapeString($_POST['user_id']);
    $id = $db->escapeString($_POST['id']);
    $sql = 'select u.*,(SELECT name FROM city c WHERE c.id=u.city) AS city_name, (SELECT name FROM area a WHERE a.id=u.area) AS area_name from user_address u where u.user_id ='.$user_id.' AND u.id='.$id;
    $db->sql($sql);
    $res = $db->getResult();
	if($res){
		$response["error"]   = false;
		//$response["message"] = "Invitation sent successfully";
		$response['data'] = $res[0];
		print_r(json_encode($response));
	    return false;
	}
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'update_address')) {
    $user_id = $db->escapeString($_POST['user_id']);
    $id = $db->escapeString($_POST['id']);
	$address_type 		= (isset($_POST['address_type']))?$db->escapeString($_POST['address_type']):"";
    $name 		= (isset($_POST['name']))?$db->escapeString($_POST['name']):"";
    $mobile 		= (isset($_POST['mobile']))?$db->escapeString($_POST['mobile']):"";
    $email 		= (isset($_POST['email']))?$db->escapeString($_POST['email']):"";
    $state 		= (isset($_POST['state_id']))?$db->escapeString($_POST['state_id']):"";
    $city 		= (isset($_POST['city_id']))?$db->escapeString($_POST['city_id']):"";
    $area 		= (isset($_POST['area_id']))?$db->escapeString($_POST['area_id']):"";
    $landmark 		= (isset($_POST['landmark']))?$db->escapeString($_POST['landmark']):"";
    $flat_no 		= (isset($_POST['flat_no']))?$db->escapeString($_POST['flat_no']):"";
	$street 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$address 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$pincode 	= (isset($_POST['pincode']))?$db->escapeString($_POST['pincode']):"";
	$latitude 	= (isset($_POST['latitude']))?$db->escapeString($_POST['latitude']):"0";
	$longitude 	= (isset($_POST['longitude']))?$db->escapeString($_POST['longitude']):"0";
	$data = array(
		'address_type' => $address_type,
	    'name' => $name,
	    'mobile' => $mobile,
	    'email'=>$email,
	    'state' => $state,
	    'city' => $city,
	    'area' => $area,
	    'landmark' => $landmark,
	    'flat_no' => $flat_no,
	    'street' => $street,
	    'address' => $address,
	    'pincode' => $pincode,
	    'latitude' => $latitude,
	    'longitude' => $longitude,
	    'is_default' => 0,
	    'status' => 1
	);
//	$fp = fopen('g.txt', 'a');fwrite($fp,json_encode($_POST).PHP_EOL);fclose($fp);
	$db->update('user_address',$data,'id='.$id);
	$response["error"]   = false;
	$response["message"] = "Address Updated successfully";
	print_r(json_encode($response));
    return false;
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'delete_address')) {
    $user_id = $db->escapeString($_POST['user_id']);
    $id = $db->escapeString($_POST['id']);
    $sql = 'DELETE FROM user_address WHERE user_id ='.$user_id.' AND id='.$id;
    $db->sql($sql);
    $res = $db->getResult();
	if($res){
		$response["error"]   = false;
		$response["message"] = "Address Deleted successfully";
		print_r(json_encode($response));
	    return false;
	}else{
	    $response["error"]   = false;
		$response["message"] = "Address Deleted successfully";
		print_r(json_encode($response));
	    return false;
	}
}

?>