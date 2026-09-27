<?php
/**
 * One-off backfill: resolve user_address.zone_id from stored coordinates.
 *
 * The zone column existed but was never written, so every saved address had a
 * NULL zone. Checkout address filtering needs it, and the polygon test cannot
 * run in plain SQL.
 *
 * Usage:  php backfill-address-zones.php            (dry run, changes nothing)
 *         php backfill-address-zones.php --apply    (writes to the database)
 *
 * Addresses with zero/missing coordinates, or that fall outside every active
 * zone polygon, are left at zone_id = 0 (treated as "unresolved", which the
 * zone rule treats as allowed so customers are not locked out).
 */

$apply = in_array('--apply', $argv, true);

include 'includes/crud.php';
include 'includes/custom-functions.php';

$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");
$fn = new custom_functions();

echo "Zone backfill for user_address.zone_id" . PHP_EOL;
echo "Mode: " . ($apply ? "APPLY (will write)" : "DRY RUN (no writes)") . PHP_EOL . PHP_EOL;

$db->sql("SELECT id, user_id, name, pincode, latitude, longitude, zone_id
          FROM user_address ORDER BY id ASC");
$rows = $db->getResult();

if (empty($rows)) {
    echo "No addresses found." . PHP_EOL;
    exit;
}

$updated = 0;
$unresolved = 0;
$skipped = 0;

foreach ($rows as $row) {
    $id = (int)$row['id'];

    // Already resolved by a previous run - leave it alone.
    if (!empty($row['zone_id'])) {
        $skipped++;
        continue;
    }

    $zone = $fn->get_zone_id_from_latlng($row['latitude'], $row['longitude']);
    $zone_id = ($zone === null) ? 0 : (int)$zone;

    if ($zone_id > 0) {
        $db->sql("SELECT name FROM zone WHERE id='".$zone_id."' LIMIT 1");
        $zres = $db->getResult();
        $zname = (!empty($zres)) ? $zres[0]['name'] : '?';
        echo "  addr#{$id} user:{$row['user_id']} pin:{$row['pincode']} -> zone {$zone_id} ({$zname})";
    } else {
        $unresolved++;
        echo "  addr#{$id} user:{$row['user_id']} pin:" . ($row['pincode'] === '' ? '(none)' : $row['pincode'])
            . " -> UNRESOLVED (0,0 coords or outside all zones)";
    }

    if ($apply) {
        $db->sql("UPDATE user_address SET zone_id='".$zone_id."' WHERE id='".$id."'");
        echo "  [updated]" . PHP_EOL;
    } else {
        echo PHP_EOL;
    }
    $updated++;
}

echo PHP_EOL . "Resolvable and processed : {$updated}" . PHP_EOL;
echo "Unresolvable (left at 0) : {$unresolved}" . PHP_EOL;
echo "Already set, skipped    : {$skipped}" . PHP_EOL;

if (!$apply && $updated > 0) {
    echo PHP_EOL . "This was a dry run. Re-run with --apply to persist." . PHP_EOL;
}
