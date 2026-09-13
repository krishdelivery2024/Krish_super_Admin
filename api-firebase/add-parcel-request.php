<?php 
header('Access-Control-Allow-Origin: *');
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db=new Database();
$db->connect();

$response = array();

if(isset($_POST['accesskey'])){
	$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
}else{
	$response['error'] = true;
	$response['message'] = "accesskey is require.";
	print_r(json_encode($response));
	return false;
}

if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}

$user_id 		= (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"0";
$item_type_id 	= (isset($_POST['item_type_id']))?$db->escapeString($fn->xss_clean($_POST['item_type_id'])):"0";
$item_type_name = (isset($_POST['item_type_name']))?$db->escapeString($fn->xss_clean($_POST['item_type_name'])):"";
$parcel_image 	= (isset($_POST['parcel_image']))?$db->escapeString($fn->xss_clean($_POST['parcel_image'])):"";
$weight_kg 		= (isset($_POST['weight_kg']))?$db->escapeString($fn->xss_clean($_POST['weight_kg'])):"";
$distance_km 	= (isset($_POST['distance_km']))?$db->escapeString($fn->xss_clean($_POST['distance_km'])):"0";
$per_km_price 	= (isset($_POST['per_km_price']))?$db->escapeString($fn->xss_clean($_POST['per_km_price'])):"0";
$base_price 	= (isset($_POST['base_price']))?$db->escapeString($fn->xss_clean($_POST['base_price'])):"0";
$total_price 	= (isset($_POST['total_price']))?$db->escapeString($fn->xss_clean($_POST['total_price'])):"0";
$payment_status = (isset($_POST['payment_status']))?$db->escapeString($fn->xss_clean($_POST['payment_status'])):"pending";
$payment_method = (isset($_POST['payment_method']))?$db->escapeString($fn->xss_clean($_POST['payment_method'])):"razorpay";
$payment_id 	= (isset($_POST['payment_id']))?$db->escapeString($fn->xss_clean($_POST['payment_id'])):"";
$pickup_location = (isset($_POST['pickup_location']))?$db->escapeString($fn->xss_clean($_POST['pickup_location'])):"";
$pickup_lat 	= (isset($_POST['pickup_lat']))?$db->escapeString($fn->xss_clean($_POST['pickup_lat'])):"";
$pickup_lng 	= (isset($_POST['pickup_lng']))?$db->escapeString($fn->xss_clean($_POST['pickup_lng'])):"";
$sender_name 	= (isset($_POST['sender_name']))?$db->escapeString($fn->xss_clean($_POST['sender_name'])):"";
$sender_phone 	= (isset($_POST['sender_phone']))?$db->escapeString($fn->xss_clean($_POST['sender_phone'])):"";
$pickup_time 	= (isset($_POST['pickup_time']))?$db->escapeString($fn->xss_clean($_POST['pickup_time'])):"";
$drop_location 	= (isset($_POST['drop_location']))?$db->escapeString($fn->xss_clean($_POST['drop_location'])):"";
$drop_lat 		= (isset($_POST['drop_lat']))?$db->escapeString($fn->xss_clean($_POST['drop_lat'])):"";
$drop_lng 		= (isset($_POST['drop_lng']))?$db->escapeString($fn->xss_clean($_POST['drop_lng'])):"";
$recipient_name = (isset($_POST['recipient_name']))?$db->escapeString($fn->xss_clean($_POST['recipient_name'])):"";
$recipient_phone = (isset($_POST['recipient_phone']))?$db->escapeString($fn->xss_clean($_POST['recipient_phone'])):"";

if(empty($pickup_location) || empty($drop_location)){
	$response['error'] = true;
	$response['message'] = "Pickup and drop location are required.";
	print_r(json_encode($response));
	return false;
}

$otp = str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

$data = array(
	'user_id' 		=> $user_id,
	'item_type_id' 	=> $item_type_id,
	'item_type_name'=> $item_type_name,
	'parcel_image' 	=> $parcel_image,
	'weight_kg' 	=> $weight_kg,
	'distance_km' 	=> $distance_km,
	'per_km_price' 	=> $per_km_price,
	'base_price' 	=> $base_price,
	'total_price' 	=> $total_price,
	'payment_status'=> $payment_status,
	'payment_method'=> $payment_method,
	'payment_id' 	=> $payment_id,
	'pickup_location' => $pickup_location,
	'pickup_lat' 	=> $pickup_lat,
	'pickup_lng' 	=> $pickup_lng,
	'sender_name' 	=> $sender_name,
	'sender_phone' 	=> $sender_phone,
	'pickup_time' 	=> $pickup_time,
	'drop_location' => $drop_location,
	'drop_lat' 		=> $drop_lat,
	'drop_lng' 		=> $drop_lng,
	'recipient_name'=> $recipient_name,
	'recipient_phone'=> $recipient_phone,
	'status' 		=> 'pending',
	'otp' 			=> $otp,
	'created_at' 	=> date('Y-m-d H:i:s')
);
$db->insert('parcel_requests',$data);
$insert_result = $db->getResult();
$last_id = !empty($insert_result) ? $insert_result[0] : 0;

if (!empty($last_id) && $last_id > 0 && $payment_status == 'paid' && !empty($payment_id)) {
	// Record the online payment against this parcel request
	$txn_data = array(
		'user_id' 			=> $user_id,
		'order_id' 			=> $last_id,
		'type' 				=> $payment_method,
		'txn_id' 			=> $payment_id,
		'amount' 			=> $total_price,
		'status' 			=> 'success',
		'message' 			=> 'Parcel Payment Success',
		'transaction_date' 	=> date('Y-m-d H:i:s')
	);
	$db->insert('transactions',$txn_data);
}

if (!empty($last_id) && $last_id > 0) {
	// Notify all online delivery boys about the new parcel request
	$message = "New parcel delivery request #" . $last_id . " is ready. Open the app to view the details.";
	$fn->send_notification_to_delivery_boy(0, "New Parcel Order", $message, 'delivery_boys', $last_id, 'parcel');
	$fn->store_delivery_boy_notification(0, $last_id, "New Parcel Order", $message, 'parcel');
}

$response["error"] 	= false;
$response["message"] = "Parcel pickup request submitted successfully";
$response["data"] 	= array('id' => $last_id, 'otp' => $otp);
print_r(json_encode($response));
return false;
?>