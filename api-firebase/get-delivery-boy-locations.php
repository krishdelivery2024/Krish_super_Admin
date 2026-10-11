<?php
/**
 * Live delivery boy locations for the admin OpenStreetMap.
 *
 * Returns every delivery boy who is currently Online (active_status='true')
 * and whose position was refreshed within the last few minutes, together with
 * the boy's name so the admin map can label each marker. Boys who go Offline,
 * or whose app stopped reporting, simply drop out of the list and their marker
 * is removed by the polling script on the next refresh.
 */
session_start();

$currentTime = time() + 25200;
$expired = 3600;

if (!isset($_SESSION['user'])) {
	header('Content-Type: application/json');
	echo json_encode(array('error' => true, 'message' => 'Not authenticated.'));
	exit;
}
if (isset($_SESSION['timeout']) && $currentTime > $_SESSION['timeout']) {
	session_destroy();
	header('Content-Type: application/json');
	echo json_encode(array('error' => true, 'message' => 'Session expired.'));
	exit;
}
unset($_SESSION['timeout']);
$_SESSION['timeout'] = $currentTime + $expired;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate');

include_once('../includes/custom-functions.php');
include_once('../includes/crud.php');
include_once('../includes/variables.php');
$fn = new custom_functions;
$db = new Database();
$db->connect();
$config = $fn->get_configurations();

if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
	date_default_timezone_set($config['system_timezone']);
	$db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
	date_default_timezone_set('Asia/Kolkata');
	$db->sql("SET `time_zone` = '+05:30'");
}

// A position older than this is treated as stopped reporting.
$fresh_seconds = 120;

// NOTE: deliberately NOT scoped to the admin's zones, matching the delivery
// boys list itself (which shows every boy to every admin). Otherwise a boy
// without a zone id (zone_id 0/NULL) would disappear from the live map for
// zone-scoped sub-admins while still appearing in the list.
$where = " WHERE active_status = 'true' AND status = 1 AND latitude IS NOT NULL AND longitude IS NOT NULL"
	. " AND location_updated_at IS NOT NULL AND location_updated_at >= (NOW() - INTERVAL " . (int) $fresh_seconds . " SECOND)";

$sql = "SELECT id, name, mobile, zone_id, latitude, longitude, location_updated_at,"
	. " TIMESTAMPDIFF(SECOND, location_updated_at, NOW()) AS age_seconds"
	. " FROM delivery_boys" . $where . " ORDER BY location_updated_at DESC";
$db->sql($sql);
$res = $db->getResult();

$data = array();
if (!empty($res)) {
	foreach ($res as $row) {
		$data[] = array(
			'id'                  => (int) $row['id'],
			'name'                => $row['name'],
			'mobile'              => $row['mobile'],
			'zone_id'             => ($row['zone_id'] === null ? null : (int) $row['zone_id']),
			'latitude'            => (float) $row['latitude'],
			'longitude'           => (float) $row['longitude'],
			'location_updated_at' => $row['location_updated_at'],
			'age_seconds'         => (int) $row['age_seconds'],
		);
	}
}

echo json_encode(array('error' => false, 'count' => count($data), 'data' => $data));
$db->disconnect();
