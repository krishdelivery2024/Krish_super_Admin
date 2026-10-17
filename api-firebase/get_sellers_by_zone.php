<?php
/**
 * get_sellers_by_zone.php
 * Returns sellers for a given zone_id (or all sellers if zone_id is empty/absent).
 * Used by the Sales Report page to dynamically populate the Seller dropdown
 * when the Zone filter changes.
 *
 * GET params:
 *   zone_id  - (optional) numeric zone ID. Empty = all sellers.
 *              Special value "0" = sellers with no zone assigned.
 */
session_start();

if (!isset($_SESSION['user'])) {
    header('Content-Type: application/json');
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

include_once('../includes/custom-functions.php');
$fn = new custom_functions;
include_once('../includes/crud.php');
include_once('../includes/variables.php');
$db = new Database();
$db->connect();

// ── zone_id ───────────────────────────────────────────────────────────────────
$zone_raw = isset($_GET['zone_id']) ? trim((string)$_GET['zone_id']) : '';

$where = '';
if ($zone_raw === '0') {
    // Sellers explicitly assigned to "no zone"
    $where = " WHERE (zone_id IS NULL OR zone_id = 0) ";
} elseif ($zone_raw !== '' && ctype_digit($zone_raw)) {
    $zone_id = (int)$zone_raw;
    $where   = " WHERE zone_id = $zone_id ";
}
// else: empty → no filter (return all sellers)

$sql = "SELECT id, name FROM seller $where ORDER BY name ASC";
$db->sql($sql);
$result = $db->getResult();

echo json_encode(is_array($result) ? array_values($result) : []);
