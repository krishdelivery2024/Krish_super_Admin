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



if (isset($_POST['type']) && $_POST['type'] == 'register') {
    if (!verify_token()) {
        return false;
    }

    $name = isset($_POST['name']) ? $db->escapeString($_POST['name']) : "";
    $mobile = isset($_POST['mobile']) ? $db->escapeString($_POST['mobile']) : "";
    $email = isset($_POST['email']) ? $db->escapeString($_POST['email']) : "";
    $company_name = isset($_POST['company_name']) ? $db->escapeString($_POST['company_name']) : "";
    $personal_address = isset($_POST['personal_address']) ? $db->escapeString($_POST['personal_address']) : "";
    $company_address = isset($_POST['company_address']) ? $db->escapeString($_POST['company_address']) : "";
    $state_id 		= (isset($_POST['state_id']))?$db->escapeString($_POST['state_id']):"";
    $city_id = isset($_POST['city_id']) ? $db->escapeString($_POST['city_id']) : "";
    $area_id = isset($_POST['area_id']) ? $db->escapeString($_POST['area_id']) : "";
    $dob = isset($_POST['dob']) ? $db->escapeString($_POST['dob']) : "";
    $account_details = isset($_POST['account_details']) ? $db->escapeString($_POST['account_details']) : "";
    $gst_no = isset($_POST['gst_no']) ? $db->escapeString($_POST['gst_no']) : "";
    $pan_no = isset($_POST['pan_no']) ? $db->escapeString($_POST['pan_no']) : "";
     $opening_time = isset($_POST['opening_time']) ? $db->escapeString($_POST['opening_time']) : "09:00";
    $closing_time = isset($_POST['closing_time']) ? $db->escapeString($_POST['closing_time']) : "21:00";
    $status = 0;

    if (empty($name) || empty($mobile) || empty($email)) {
        $response = [
            "error" => true,
            "message" => empty($name) ? "Name is required" :
                        (empty($mobile) ? "Mobile number is required" : "Email is required")
        ];
        echo json_encode($response);
        exit;
    }

    // Check for existing mobile
    $db->sql("SELECT id FROM seller WHERE mobile = '$mobile'");
    if ($db->numRows($db->getResult()) > 0) {
        echo json_encode([
            "error" => true,
            "message" => "This $mobile is already registered. Please login!"
        ]);
        exit;
    }

    // Check for existing email
    $db->sql("SELECT id FROM seller WHERE email = '$email'");
    if ($db->numRows($db->getResult()) > 0) {
        echo json_encode([
            "error" => true,
            "message" => "This $email is already registered. Please login!"
        ]);
        exit;
    }

    // Insert new seller
    $seller_data = [
        'name' => $name,
        'mobile' => $mobile,
        'email' => $email,
        'company_name' => $company_name,
        'personal_address' => $personal_address,
        'company_address' => $company_address,
        'state_id' => $state_id,
        'city_id' => $city_id,
        'area_id' => $area_id,
        'dob' => $dob,
        'account_details' => $account_details,
        'gst_no' => $gst_no,
        'pan_no' => $pan_no,
        'status' => $status,
                'opening_time' => $opening_time,
        'closing_time' => $closing_time,
        'date_created' => date('Y-m-d H:i:s')
    ];

    $db->insert('seller', $seller_data);
    $res = $db->getResult();
    $user_id = $res[0] ?? 0;

    // Fetch city and area names
    $db->sql("SELECT name FROM state WHERE id = '$state_id'");
    $state_name = $db->getResult()[0]['name'] ?? '';

    $db->sql("SELECT name FROM city WHERE id = '$city_id'");
    $city_name = $db->getResult()[0]['name'] ?? '';

    $db->sql("SELECT name FROM area WHERE id = '$area_id'");
    $area_name = $db->getResult()[0]['name'] ?? '';

    $response = [
        "error" => false,
        "message" => "Vendor registered successfully",
        "seller_id" => $user_id,
        "name" => $name,
        "email" => $email,
        "mobile" => $mobile,
        "state_id" => $state_id,
        "state_name" => $state_name,
        "city_id" => $city_id,
        "city_name" => $city_name,
        "area_id" => $area_id,
        "area_name" => $area_name,
        "status" => $status,
        "created_at" => date('Y-m-d h:i:s a')
    ];

    echo json_encode($response);
    exit;
}











?>