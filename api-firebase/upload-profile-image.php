<?php
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
include_once('verify-token.php');

$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');
$fn = new custom_functions();
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

if (!isset($_POST['user_id']) || empty(trim($_POST['user_id']))) {
    $response['error'] = true;
    $response['message'] = "user_id is required";
    print_r(json_encode($response));
    return false;
}

$user_id = $db->escapeString($fn->xss_clean($_POST['user_id']));

// Check if user exists
$sql = "SELECT id FROM users WHERE id = '$user_id'";
$db->sql($sql);
$res = $db->getResult();
if (empty($res)) {
    $response['error'] = true;
    $response['message'] = "User does not exist";
    print_r(json_encode($response));
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
$target_dir = "../upload/profile/";
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Generate unique name
$new_filename = $user_id . "_" . time() . "." . $file_extension;
$target_filepath = $target_dir . $new_filename;

if (move_uploaded_file($_FILES['image']['tmp_name'], $target_filepath)) {
    // Delete old profile image if exists
    $sql_old = "SELECT profile FROM users WHERE id = '$user_id'";
    $db->sql($sql_old);
    $res_old = $db->getResult();
    if (!empty($res_old) && !empty($res_old[0]['profile'])) {
        $old_profile_path = "../" . $res_old[0]['profile'];
        if (file_exists($old_profile_path)) {
            unlink($old_profile_path);
        }
    }

    $db_path = "upload/profile/" . $new_filename;
    $sql_update = "UPDATE users SET profile = '$db_path' WHERE id = '$user_id'";
    if ($db->sql($sql_update)) {
        $response['error'] = false;
        $response['message'] = "Profile image uploaded successfully";
        $response['profile'] = DOMAIN_URL . $db_path;
    } else {
        $response['error'] = true;
        $response['message'] = "Failed to update profile in database";
    }
} else {
    $response['error'] = true;
    $response['message'] = "Failed to save uploaded file";
}

print_r(json_encode($response));
$db->disconnect();
?>
