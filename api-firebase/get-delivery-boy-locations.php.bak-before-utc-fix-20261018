
<?php
/**
 * Live delivery-boy locations for authorized admin users.
 */
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

function location_error($status, $message) {
    http_response_code($status);
    echo json_encode(array(
        'error' => true,
        'message' => $message
    ));
    exit;
}

if (
    empty($_SESSION['user']) ||
    empty($_SESSION['id']) ||
    empty($_SESSION['role'])
) {
    location_error(401, 'Not authenticated.');
}

$currentTime = time() + 25200;

if (isset($_SESSION['timeout']) && $currentTime > $_SESSION['timeout']) {
    session_destroy();
    location_error(401, 'Session expired.');
}

$_SESSION['timeout'] = $currentTime + 3600;

$role = (string) $_SESSION['role'];

if ($role === 'seller') {
    location_error(403, 'Access denied.');
}

require_once('../includes/crud.php');
require_once('../includes/custom-functions.php');
require_once('../includes/variables.php');


$fn = new custom_functions;
$db = new Database();
$db->connect();

/* Validate the active admin session against the database. */
if (
    !isset($_SESSION['secretkey']) ||
    (int) $_SESSION['secretkey'] <= 0
) {
    $db->disconnect();
    location_error(401, 'Invalid session. Please log in again.');
}

$sessionId = (int) $_SESSION['id'];
$sessionUser = $db->escapeString((string) $_SESSION['user']);
$sessionRole = $db->escapeString((string) $_SESSION['role']);
$sessionKey = (int) $_SESSION['secretkey'];

$db->sql(
    "SELECT id FROM admin
     WHERE id = " . $sessionId . "
       AND username = '" . $sessionUser . "'
       AND role = '" . $sessionRole . "'
       AND web_login = " . $sessionKey . "
     LIMIT 1"
);

$validSession = $db->getResult();

if (empty($validSession)) {
    $db->disconnect();
    location_error(401, 'Session is no longer valid. Please log in again.');
}

$permissions = $fn->get_permissions($sessionId);


/*
 * Super admins retain access.
 * Other admin roles must explicitly have delivery_boys.read permission.
 */
if (
    $role !== 'super admin' &&
    (
        !isset($permissions['delivery_boys']['read']) ||
        (int) $permissions['delivery_boys']['read'] !== 1
    )
) {
    $db->disconnect();
    location_error(403, 'You do not have permission to view delivery-boy locations.');
}

$config = $fn->get_configurations();

if (isset($config['system_timezone'], $config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" .
        $db->escapeString($config['system_timezone_gmt']) . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$fresh_seconds = 120;

$where = " WHERE d.active_status = 'true'
    AND d.status = 1
    AND d.latitude IS NOT NULL
    AND d.longitude IS NOT NULL
    AND d.location_updated_at IS NOT NULL
    AND d.location_updated_at >= (NOW() - INTERVAL " .
    (int) $fresh_seconds . " SECOND)";

/*
 * A sub-admin may see only delivery boys in assigned zones.
 * An empty or invalid zone assignment must never mean "all zones".
 */

if ($role === 'sub admin') {
    $zoneIds = $fn->get_zone_scope((int) $_SESSION['id']);
    $zoneIds = is_array($zoneIds)
        ? array_values(array_unique(array_filter(
            array_map('intval', $zoneIds),
            function ($zoneId) {
                return $zoneId > 0;
            }
        )))
        : array();

    if (empty($zoneIds)) {
        $where .= " AND 1 = 0";
    } else {
        $where .= " AND d.zone_id IN (" . implode(',', $zoneIds) . ")";
    }
}


$sql = "SELECT d.id, d.name, d.mobile, d.address, d.balance, d.bonus, d.service_type, d.profile, d.zone_id,
        d.latitude, d.longitude, d.location_updated_at,
        z.name AS zone_name,
        TIMESTAMPDIFF(SECOND, d.location_updated_at, NOW()) AS age_seconds
        FROM delivery_boys d
        LEFT JOIN zone z ON z.id = d.zone_id" . $where . "
        ORDER BY d.location_updated_at DESC";

$db->sql($sql);
$res = $db->getResult();

$data = array();

if (!empty($res)) {
    foreach ($res as $row) {
        $profile_img = '';
        if (!empty($row['profile'])) {
            $profile_img = (strpos($row['profile'], 'http') === 0) ? $row['profile'] : DOMAIN_URL . $row['profile'];
        }
        $data[] = array(
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'mobile' => $row['mobile'],
            'address' => isset($row['address']) ? $row['address'] : '',
            'balance' => isset($row['balance']) ? (float)$row['balance'] : 0,
            'bonus' => isset($row['bonus']) ? (float)$row['bonus'] : 0,
            'service_type' => isset($row['service_type']) ? $row['service_type'] : 'both',
            'profile' => $profile_img,
            'zone_id' => ($row['zone_id'] === null ? null : (int) $row['zone_id']),
            'zone_name' => (!empty($row['zone_name']) ? $row['zone_name'] : 'All Zones'),
            'latitude' => (float) $row['latitude'],
            'longitude' => (float) $row['longitude'],
            'location_updated_at' => $row['location_updated_at'],
            'age_seconds' => (int) $row['age_seconds']
        );
    }
}

$db->disconnect();

echo json_encode(array(
    'error' => false,
    'count' => count($data),
    'data' => $data
));
