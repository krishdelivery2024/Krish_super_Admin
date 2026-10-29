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

// Check if passenger service is enabled
$sql_check = "SELECT is_service_available, is_passenger_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
$db->sql($sql_check);
$check_res = $db->getResult();
if (!empty($check_res)) {
    if (isset($check_res[0]['is_service_available']) && $check_res[0]['is_service_available'] == '0') {
        $response['error'] = true;
        $response['message'] = "Service not available now. Please try again later.";
        echo json_encode($response);
        exit();
    }
    if (isset($check_res[0]['is_passenger_available']) && $check_res[0]['is_passenger_available'] == '0') {
        $response['error'] = true;
        $response['message'] = "Passenger ride service is currently disabled.";
        echo json_encode($response);
        exit();
    }
}

if (!isset($_POST['accesskey']) || $_POST['accesskey'] != $access_key) {
    $response['error'] = true;
    $response['message'] = "Invalid access key.";
    echo json_encode($response);
    exit();
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id <= 0) {
    $response['error'] = true;
    $response['message'] = "Valid user ID is required.";
    echo json_encode($response);
    exit();
}

$pickup_location = isset($_POST['pickup_location']) ? $db->escapeString($fn->xss_clean($_POST['pickup_location'])) : '';
$pickup_lat = isset($_POST['pickup_lat']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lat'])) : '';
$pickup_lng = isset($_POST['pickup_lng']) ? $db->escapeString($fn->xss_clean($_POST['pickup_lng'])) : '';
$drop_location = isset($_POST['drop_location']) ? $db->escapeString($fn->xss_clean($_POST['drop_location'])) : '';
$drop_lat = isset($_POST['drop_lat']) ? $db->escapeString($fn->xss_clean($_POST['drop_lat'])) : '';
$drop_lng = isset($_POST['drop_lng']) ? $db->escapeString($fn->xss_clean($_POST['drop_lng'])) : '';

$sender_name = isset($_POST['sender_name']) ? $db->escapeString($fn->xss_clean($_POST['sender_name'])) : 'Passenger';
$sender_phone = isset($_POST['sender_phone']) ? $db->escapeString($fn->xss_clean($_POST['sender_phone'])) : '';
$passenger_vehicle = isset($_POST['passenger_vehicle']) ? $db->escapeString($fn->xss_clean($_POST['passenger_vehicle'])) : 'auto';
$passenger_count = isset($_POST['passenger_count']) ? max(1, (int)$_POST['passenger_count']) : 1;

$distance_km = isset($_POST['distance_km']) ? (float)$_POST['distance_km'] : 0.0;
$per_km_price = isset($_POST['per_km_price']) ? (float)$_POST['per_km_price'] : 0.0;
$base_price = isset($_POST['base_price']) ? (float)$_POST['base_price'] : 0.0;
$total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0.0;
$platform_fee = isset($_POST['platform_fee']) ? (float)$_POST['platform_fee'] : 0.0;
$convenience_fee = isset($_POST['convenience_fee']) ? (float)$_POST['convenience_fee'] : 0.0;
$gst = isset($_POST['gst']) ? (float)$_POST['gst'] : 0.0;
$grand_total = isset($_POST['grand_total']) ? (float)$_POST['grand_total'] : 0.0;

if (empty($pickup_location) || empty($drop_location) || empty($pickup_lat) || empty($pickup_lng) || empty($drop_lat) || empty($drop_lng)) {
    $response['error'] = true;
    $response['message'] = "Pickup and drop locations with coordinates are required.";
    echo json_encode($response);
    exit();
}

$pickup_zone = $fn->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
if ($pickup_zone === null) {
    $db->sql("SELECT id FROM zone WHERE status = 1 ORDER BY id ASC LIMIT 1");
    $fallback_z = $db->getResult();
    if (!empty($fallback_z)) {
        $pickup_zone = (int)$fallback_z[0]['id'];
    } else {
        $response['error'] = true;
        $response['message'] = "Pickup is only available inside our service zones.";
        echo json_encode($response);
        exit();
    }
}

// Clean up any stale searching requests for this user
$db->sql("DELETE FROM parcel_requests WHERE user_id = '$user_id' AND service_type = 'passenger' AND status = 'searching'");

// Clean up globally expired searching rides
$db->sql("DELETE FROM parcel_requests WHERE service_type = 'passenger' AND status = 'searching' AND dispatch_expires_at < NOW()");

// Insert new searching ride dispatch (valid for 60 seconds)
$data = array(
    'user_id' => $user_id,
    'item_type_id' => 0,
    'item_type_name' => 'Passenger Ride (' . ucfirst($passenger_vehicle) . ')',
    'distance_km' => $distance_km,
    'per_km_price' => $per_km_price,
    'base_price' => $base_price,
    'total_price' => $total_price,
    'platform_fee' => $platform_fee,
    'convenience_fee' => $convenience_fee,
    'gst' => $gst,
    'grand_total' => $grand_total,
    'payment_status' => 'pending',
    'driver_payment_status' => 'pending',
    'payment_method' => '',
    'pickup_location' => $pickup_location,
    'pickup_lat' => $pickup_lat,
    'pickup_lng' => $pickup_lng,
    'sender_name' => $sender_name,
    'sender_phone' => $sender_phone,
    'pickup_time' => 'Immediate',
    'drop_location' => $drop_location,
    'drop_lat' => $drop_lat,
    'drop_lng' => $drop_lng,
    'recipient_name' => "Passenger ($sender_name)",
    'recipient_phone' => $sender_phone,
    'service_type' => 'passenger',
    'passenger_vehicle' => $passenger_vehicle,
    'passenger_count' => $passenger_count,
    'status' => 'searching',
    'otp' => '', // Will be generated upon driver acceptance
    'zone_id' => $pickup_zone,
    'created_at' => date('Y-m-d H:i:s'),
    'dispatch_expires_at' => date('Y-m-d H:i:s', time() + 60)
);

$db->insert('parcel_requests', $data);
$res = $db->getResult();
$ride_id = !empty($res) ? $res[0] : 0;

if (!$ride_id) {
    $response['error'] = true;
    $response['message'] = "Failed to initiate ride request.";
    echo json_encode($response);
    exit();
}

// Broadcast push notification to delivery boys who handle passenger rides
$notif_title = "🚗 New " . ucfirst($passenger_vehicle) . " Ride Request!";
$notif_body = "Pickup: " . mb_substr($pickup_location, 0, 40) . "... | ₹" . number_format($grand_total, 2) . " | Tap to Accept!";
$_SERVER['REQUEST_METHOD'] = 'POST'; // Ensure push handler accepts
$extra_data = array(
    'order_id' => (string)$ride_id,
    'sender_name' => $sender_name,
    'pickup_location' => $pickup_location,
    'drop_location' => $drop_location,
    'grand_total' => (string)$grand_total,
    'distance_km' => (string)$distance_km,
    'passenger_vehicle' => $passenger_vehicle
);
$fn->send_notification_to_delivery_boy(0, $notif_title, $notif_body, 'passenger_ride', $ride_id, 'passenger', $extra_data);

$response['error'] = false;
$response['message'] = "Searching for nearby drivers...";
$response['ride_id'] = (int)$ride_id;
$response['expires_in'] = 60;
echo json_encode($response);
?>
