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

$config = $fn->get_configurations();
if (isset($config['system_timezone'])) {
    date_default_timezone_set($config['system_timezone']);
} else {
    date_default_timezone_set('Asia/Kolkata');
}

if (!isset($_POST['accesskey']) || $_POST['accesskey'] != $access_key) {
    $response['error'] = true;
    $response['message'] = "Invalid access key.";
    echo json_encode($response);
    exit();
}

$ride_id = isset($_POST['ride_id']) ? (int)$_POST['ride_id'] : 0;
if ($ride_id <= 0) {
    $response['error'] = true;
    $response['message'] = "Valid ride ID is required.";
    echo json_encode($response);
    exit();
}

$sql = "SELECT pr.*, 
        db.name AS driver_name, 
        db.mobile AS driver_mobile, 
        db.profile AS driver_image,
        db.latitude AS driver_lat,
        db.longitude AS driver_lng
        FROM parcel_requests pr 
        LEFT JOIN delivery_boys db ON db.id = pr.delivery_boy_id 
        WHERE pr.id = '$ride_id'";
$db->sql($sql);
$res = $db->getResult();

if (empty($res)) {
    // If not found, it may have timed out and been deleted
    $response['error'] = false;
    $response['status'] = 'timeout';
    $response['message'] = "No drivers available nearby right now. Please try again.";
    echo json_encode($response);
    exit();
}

$ride = $res[0];

// If still in searching state, check if 60 seconds expired
if ($ride['status'] == 'searching') {
    $expires_at = !empty($ride['dispatch_expires_at']) ? strtotime($ride['dispatch_expires_at']) : 0;
    $now = time();
    if ($now >= $expires_at) {
        // Expired! Delete search record so booking is NOT created
        $db->sql("DELETE FROM parcel_requests WHERE id = '$ride_id' AND status = 'searching'");
        $response['error'] = false;
        $response['status'] = 'timeout';
        $response['message'] = "No drivers available nearby right now. Please try again.";
        echo json_encode($response);
        exit();
    } else {
        $response['error'] = false;
        $response['status'] = 'searching';
        $response['time_left'] = max(0, $expires_at - $now);
        $response['message'] = "Searching for nearby drivers...";
        echo json_encode($response);
        exit();
    }
}

// Driver accepted or ride progressing
$response['error'] = false;
$response['status'] = $ride['status']; // 'accepted', 'started', 'completed', 'cancelled'
$response['ride_id'] = (int)$ride['id'];
$response['otp'] = $ride['otp'];
$response['payment_status'] = $ride['payment_status'];
$response['driver_payment_status'] = $ride['driver_payment_status'];
$response['payment_method'] = $ride['payment_method'];

$response['driver'] = array(
    'id' => (int)$ride['delivery_boy_id'],
    'name' => $ride['driver_name'] ?? 'Driver',
    'mobile' => $ride['driver_mobile'] ?? '',
    'image' => $ride['driver_image'] ?? '',
    'lat' => $ride['driver_lat'] ?? '',
    'lng' => $ride['driver_lng'] ?? ''
);

$response['fare'] = array(
    'distance_km' => (float)$ride['distance_km'],
    'per_km_price' => (float)$ride['per_km_price'],
    'base_price' => (float)$ride['base_price'],
    'total_price' => (float)$ride['total_price'],
    'platform_fee' => (float)$ride['platform_fee'],
    'convenience_fee' => (float)$ride['convenience_fee'],
    'gst' => (float)$ride['gst'],
    'grand_total' => (float)$ride['grand_total']
);

$response['ride'] = array(
    'pickup_location' => $ride['pickup_location'],
    'pickup_lat' => $ride['pickup_lat'],
    'pickup_lng' => $ride['pickup_lng'],
    'drop_location' => $ride['drop_location'],
    'drop_lat' => $ride['drop_lat'],
    'drop_lng' => $ride['drop_lng'],
    'passenger_vehicle' => $ride['passenger_vehicle'],
    'passenger_count' => (int)$ride['passenger_count'],
    'created_at' => $ride['created_at'],
    'start_time' => $ride['start_time'],
    'end_time' => $ride['end_time']
);

echo json_encode($response);
?>
