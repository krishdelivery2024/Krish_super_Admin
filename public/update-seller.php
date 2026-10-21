<?php
header('Content-Type: application/json');
include_once('../includes/variables.php');
include_once('../includes/crud.php');

$db = new Database();
$db->connect();

require_once('../includes/custom-functions.php');
$fn = new custom_functions();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
     if (ob_get_length()) ob_clean();
    echo json_encode(['error' => true, 'message' => 'Invalid request method']);
    exit;
}

// Required fields for update
$required_fields = ['id', 'name', 'email', 'mobile', 'company_name', 'personal_address',
        'company_address', 'dob', 'account_details', 'gst_no', 'pan_no','opening_time', 'closing_time', 'preparation_time', 'city_id', 'area_id', 'main_cat_id', 'status'];

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
$store_address   = isset($_POST['store_address']) ? $db->escapeString($_POST['store_address']) : '';

$latitude        = isset($_POST['latitude']) ? $db->escapeString($_POST['latitude']) : '';
$longitude       = isset($_POST['longitude']) ? $db->escapeString($_POST['longitude']) : '';

$dob             = $db->escapeString($_POST['dob']);
$account_details = $db->escapeString($_POST['account_details']);
$gst_no          = $db->escapeString($_POST['gst_no']);
$pan_no          = $db->escapeString($_POST['pan_no']);
$opening_time    = $db->escapeString($_POST['opening_time']);
$closing_time    = $db->escapeString($_POST['closing_time']);
$preparation_time= $db->escapeString($_POST['preparation_time']);
$city_id         = $db->escapeString($_POST['city_id']);
$area_id         = $db->escapeString($_POST['area_id']);
$main_cat_id     = $db->escapeString($_POST['main_cat_id']);
$status          = $db->escapeString($_POST['status']);
$store_status    = $db->escapeString($_POST['store_status']);
$seller = array();
$db->sql("SELECT working_days, holidays FROM seller WHERE id='$id'");
$res_seller = $db->getResult();
if (!empty($res_seller)) { $seller = $res_seller[0]; }
$working_days    = isset($_POST['working_days']) && is_array($_POST['working_days']) ? implode(',', array_map('intval', $_POST['working_days'])) : '1,2,3,4,5,6,7';
$holidays    = ''; // Derived from unchecked days (not collected separately).

$zone_id = $fn->get_zone_id_from_latlng($latitude, $longitude);
$zone_id_sql = ($zone_id !== null) ? $zone_id : 'NULL';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$is_super_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'super admin');
$commission_sql = "";
if ($is_super_admin && isset($_POST['commission'])) {
    $commission = floatval($_POST['commission']);
    $commission_sql = "commission = '$commission',";
}

// Handle file upload (image & banner)
$target_dir = "../upload/sellers/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$image_sql = "";
$banner_sql = "";

if (isset($_FILES['image']) && $_FILES['image']['error'] === 0 && $_FILES['image']['size'] > 0) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_extensions)) {
        $image_name = uniqid("img_") . "." . $ext;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name)) {
            // Remove old image if present
            $db->sql("SELECT image FROM seller WHERE id='$id'");
            $old_img_res = $db->getResult();
            if (!empty($old_img_res[0]['image']) && file_exists($target_dir . $old_img_res[0]['image'])) {
                @unlink($target_dir . $old_img_res[0]['image']);
            }
            $image_sql = "image = '$image_name',";
        }
    }
}

if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0 && $_FILES['banner']['size'] > 0) {
    $ext = strtolower(pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $allowed_extensions)) {
        $banner_name = uniqid("banner_") . "." . $ext;
        if (move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . $banner_name)) {
            // Remove old banner if present
            $db->sql("SELECT banner FROM seller WHERE id='$id'");
            $old_ban_res = $db->getResult();
            if (!empty($old_ban_res[0]['banner']) && file_exists($target_dir . $old_ban_res[0]['banner'])) {
                @unlink($target_dir . $old_ban_res[0]['banner']);
            }
            $banner_sql = "banner = '$banner_name',";
        }
    }
}

// Update query
$sql = "UPDATE seller SET 
    name = '$name',
    email = '$email',
    mobile = '$mobile',
    company_name = '$company_name',
    company_legal_name = '$company_legal_name',
    personal_address = '$personal_addr',
    company_address = '$company_addr',
    store_address = '$store_address',
    $image_sql
    $banner_sql
    latitude = '$latitude',
    longitude = '$longitude',
    
    dob = '$dob',
    account_details = '$account_details',
    gst_no = '$gst_no',
    pan_no = '$pan_no',
      opening_time = '$opening_time',
    closing_time = '$closing_time',
    preparation_time = '$preparation_time',
    working_days = '$working_days',
    holidays = '$holidays',
    city_id = '$city_id',
    area_id = '$area_id',
    main_cat_id = '$main_cat_id',
    store_status = '$store_status',
    status = '$status',
    $commission_sql
    zone_id = $zone_id_sql,
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
