<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db = new Database();
$db->connect();
$response = array();

if (!isset($_POST['accesskey']) || $_POST['accesskey'] != $access_key) {
    $response['error'] = true;
    $response['message'] = "Invalid access key.";
    echo json_encode($response);
    exit();
}

$ride_id = isset($_POST['ride_id']) ? (int)$_POST['ride_id'] : 0;
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($ride_id <= 0 || $user_id <= 0) {
    $response['error'] = true;
    $response['message'] = "Ride ID and User ID are required.";
    echo json_encode($response);
    exit();
}

$sql = "SELECT * FROM parcel_requests WHERE id = '$ride_id' AND user_id = '$user_id'";
$db->sql($sql);
$res = $db->getResult();

if (empty($res)) {
    $response['error'] = true;
    $response['message'] = "Ride not found.";
    echo json_encode($response);
    exit();
}

$ride = $res[0];

if ($ride['status'] == 'searching') {
    // Delete searching record completely - booking not created
    $db->sql("DELETE FROM parcel_requests WHERE id = '$ride_id'");
    $response['error'] = false;
    $response['message'] = "Ride request cancelled.";
    echo json_encode($response);
    exit();
} else if (in_array($ride['status'], array('accepted', 'pending'))) {
    $db->sql("UPDATE parcel_requests SET status = 'cancelled' WHERE id = '$ride_id'");
    $response['error'] = false;
    $response['message'] = "Ride cancelled successfully.";
    echo json_encode($response);
    exit();
} else {
    $response['error'] = true;
    $response['message'] = "Ride cannot be cancelled at this stage (" . $ride['status'] . ").";
    echo json_encode($response);
    exit();
}
?>
