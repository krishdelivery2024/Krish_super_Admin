<?php
header('Content-Type: application/json');
include_once('../includes/variables.php');
include_once('../includes/crud.php');

$db = new Database();
$db->connect();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     if (ob_get_length()) ob_clean();
    echo json_encode(['error' => true, 'message' => 'Invalid request method']);
    exit;
}

// Required fields for update
$required_fields = ['id', 'name', 'email', 'mobile', 'company_name', 'personal_address',
        'company_address', 'dob', 'account_details', 'gst_no', 'pan_no','opening_time', 'closing_time',  'city_id', 'area_id', 'main_cat_id', 'status'];

foreach ($required_fields as $field) {
    if (!isset($_POST[$field])) {
        if (ob_get_length()) ob_clean();
        echo json_encode(['error' => true, 'message' => "Missing field: $field"]);
        exit;
    }
}

// Sanitize inputs
$id              = $db->escapeString($_POST['id']);
$name            = $db->escapeString($_POST['name']);
$email           = $db->escapeString($_POST['email']);
$mobile          = $db->escapeString($_POST['mobile']);
$company_name    = $db->escapeString($_POST['company_name']);
$company_legal_name = isset($_POST['company_legal_name']) ? $db->escapeString($_POST['company_legal_name']) : '';
$personal_addr   = $db->escapeString($_POST['personal_address']);
$company_addr    = $db->escapeString($_POST['company_address']);

$latitude        = isset($_POST['latitude']) ? $db->escapeString($_POST['latitude']) : '';
$longitude       = isset($_POST['longitude']) ? $db->escapeString($_POST['longitude']) : '';

$dob             = $db->escapeString($_POST['dob']);
$account_details = $db->escapeString($_POST['account_details']);
$gst_no          = $db->escapeString($_POST['gst_no']);
$pan_no          = $db->escapeString($_POST['pan_no']);
$opening_time    = $db->escapeString($_POST['opening_time']);
$closing_time    = $db->escapeString($_POST['closing_time']);
$city_id         = $db->escapeString($_POST['city_id']);
$area_id         = $db->escapeString($_POST['area_id']);
$main_cat_id     = $db->escapeString($_POST['main_cat_id']);
$status          = $db->escapeString($_POST['status']);
$store_status    = $db->escapeString($_POST['store_status']);

// Update query
$sql = "UPDATE seller SET 
    name = '$name',
    email = '$email',
    mobile = '$mobile',
    company_name = '$company_name',
     company_legal_name = '$company_legal_name',
    personal_address = '$personal_addr',
    company_address = '$company_addr',

    latitude = '$latitude',
    longitude = '$longitude',
    
    dob = '$dob',
    account_details = '$account_details',
    gst_no = '$gst_no',
    pan_no = '$pan_no',
      opening_time = '$opening_time',
    closing_time = '$closing_time',
    city_id = '$city_id',
    area_id = '$area_id',
    main_cat_id = '$main_cat_id',
    store_status = '$store_status',
    status = '$status',
    last_updated = NOW()
    WHERE id = '$id'";

$db->sql($sql);

if($status == 0){$is_active = 0;}elseif($status == 1){$is_active = 1;}else{$is_active = 0;}
$sql = "UPDATE products SET 
    is_active = '$is_active'
    WHERE seller_id = '$id'";
$db->sql($sql);
if($status == 0){
$sql =  "DELETE FROM `wishlists` WHERE `seller_id`='$id'";
$db->sql($sql);
$sql =  "DELETE FROM `carts` WHERE `seller_id`='$id'";
$db->sql($sql);
}

if (ob_get_length()) ob_clean();
echo json_encode(['error' => false, 'message' => 'Seller updated successfully']);

?>
