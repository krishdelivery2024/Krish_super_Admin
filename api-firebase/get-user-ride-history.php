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

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id <= 0) {
    $response['error'] = true;
    $response['message'] = "User ID is required.";
    echo json_encode($response);
    exit();
}

$sql = "SELECT pr.*, 
        db.name AS driver_name, 
        db.mobile AS driver_mobile, 
        db.profile AS driver_image 
        FROM parcel_requests pr 
        LEFT JOIN delivery_boys db ON db.id = pr.delivery_boy_id 
        WHERE pr.user_id = '$user_id' 
        AND pr.service_type = 'passenger' 
        AND pr.status != 'searching' 
        ORDER BY pr.id DESC";
$db->sql($sql);
$res = $db->getResult();

$data = array();
if (!empty($res)) {
    foreach ($res as $row) {
        $data[] = array(
            'id' => (int)$row['id'],
            'status' => $row['status'],
            'otp' => $row['otp'],
            'passenger_vehicle' => $row['passenger_vehicle'],
            'passenger_count' => (int)$row['passenger_count'],
            'pickup_location' => $row['pickup_location'],
            'pickup_lat' => $row['pickup_lat'],
            'pickup_lng' => $row['pickup_lng'],
            'drop_location' => $row['drop_location'],
            'drop_lat' => $row['drop_lat'],
            'drop_lng' => $row['drop_lng'],
            'distance_km' => (float)$row['distance_km'],
            'per_km_price' => (float)$row['per_km_price'],
            'base_price' => (float)$row['base_price'],
            'total_price' => (float)$row['total_price'],
            'platform_fee' => (float)$row['platform_fee'],
            'convenience_fee' => (float)$row['convenience_fee'],
            'gst' => (float)$row['gst'],
            'grand_total' => (float)$row['grand_total'],
            'payment_status' => $row['payment_status'],
            'driver_payment_status' => $row['driver_payment_status'],
            'payment_method' => $row['payment_method'],
            'driver_name' => $row['driver_name'] ?? 'Driver',
            'driver_mobile' => $row['driver_mobile'] ?? '',
            'driver_image' => $row['driver_image'] ?? '',
            'created_at' => $row['created_at'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time']
        );
    }
}

$response['error'] = false;
$response['message'] = "Ride history fetched successfully.";
$response['total'] = count($data);
$response['data'] = $data;
echo json_encode($response);
?>
