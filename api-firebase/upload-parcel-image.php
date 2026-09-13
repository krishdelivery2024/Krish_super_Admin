<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions();
include_once('../includes/variables.php');
include_once('verify-token.php');
date_default_timezone_set('Asia/Kolkata');

$response = array();

if (!isset($_POST['accesskey']) || $access_key != $db->escapeString($fn->xss_clean($_POST['accesskey']))) {
    $response['error'] = true;
    $response['message'] = "invalid accesskey";
    print_r(json_encode($response));
    return false;
}

if (!verify_token()) {
    return false;
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
    $response['error'] = true;
    $response['message'] = "Please select an image file to upload";
    print_r(json_encode($response));
    return false;
}

// 200KB size validation
$max_size = 200 * 1024;
if ($_FILES['image']['size'] > $max_size) {
    $response['error'] = true;
    $response['message'] = "Image size must be less than 200KB";
    print_r(json_encode($response));
    return false;
}

$allowed_extensions = array("jpg", "jpeg", "png", "gif");
$file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

if (!in_array($file_extension, $allowed_extensions)) {
    $response['error'] = true;
    $response['message'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed";
    print_r(json_encode($response));
    return false;
}

// Target folder relative to api-firebase
$target_dir = "../upload/parcel/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Generate unique name
$new_filename = "parcel_" . time() . "_" . rand(100, 999) . "." . $file_extension;
$target_filepath = $target_dir . $new_filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $target_filepath)) {
    $db_path = "upload/parcel/" . $new_filename;
    $response['error'] = false;
    $response['message'] = "Parcel image uploaded successfully";
    $response['data'] = array(
        'image' => $db_path
    );
    $response['image_url'] = DOMAIN_URL . $db_path;
} else {
    $response['error'] = true;
    $response['message'] = "Failed to save uploaded file";
}

print_r(json_encode($response));
$db->disconnect();
?>