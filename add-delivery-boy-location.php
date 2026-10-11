<?php
/**
 * One-off migration: live location columns for delivery boys.
 *
 * The delivery boy app (while Online) posts its position every 20 seconds so
 * the admin panel can plot it on the OpenStreetMap. latitude/longitude keep the
 * last known point and location_updated_at records when it arrived, which lets
 * the admin map drop a boy whose updates have stopped.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'latitude'            => 'DECIMAL(10,7) NULL DEFAULT NULL',
    'longitude'           => 'DECIMAL(10,7) NULL DEFAULT NULL',
    'location_updated_at' => 'DATETIME NULL DEFAULT NULL',
);

$db->sql("SHOW COLUMNS FROM `delivery_boys`");
$existing = array();
foreach ($db->getResult() as $row) {
    $existing[$row['Field']] = true;
}

foreach ($wanted as $column => $definition) {
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `delivery_boys` ADD COLUMN `$column` $definition");
    $db->sql("SHOW COLUMNS FROM `delivery_boys`");
    $now = array();
    foreach ($db->getResult() as $row) {
        $now[$row['Field']] = true;
    }
    echo isset($now[$column]) ? "  added $column $definition\n" : "  FAILED to add $column\n";
}

$db->sql("SHOW COLUMNS FROM `delivery_boys`");
echo "\ndelivery_boys location columns now: ";
$names = array();
foreach ($db->getResult() as $row) {
    if (in_array($row['Field'], array('latitude', 'longitude', 'location_updated_at', 'active_status'))) {
        $names[] = $row['Field'] . ' ' . $row['Type'];
    }
}
echo implode(', ', $names) . "\n";
