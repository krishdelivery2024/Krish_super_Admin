<?php
/**
 * One-off migration: daily operating hours for the parcel pickup service.
 *
 * The parcel service can be limited to a daily window. Both columns are
 * nullable: when either is NULL/empty the service has no time restriction and
 * behaves exactly as before (open 24 hours). When both are set, a pickup can
 * only be booked between opening_time and closing_time in the store timezone.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'opening_time' => 'TIME NULL DEFAULT NULL',
    'closing_time' => 'TIME NULL DEFAULT NULL',
);

$db->sql("SHOW COLUMNS FROM `parcel_settings`");
$existing = array();
foreach ($db->getResult() as $row) {
    $existing[$row['Field']] = true;
}

foreach ($wanted as $column => $definition) {
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `parcel_settings` ADD COLUMN `$column` $definition");
    $db->sql("SHOW COLUMNS FROM `parcel_settings`");
    $now = array();
    foreach ($db->getResult() as $row) {
        $now[$row['Field']] = true;
    }
    echo isset($now[$column]) ? "  added $column $definition\n" : "  FAILED to add $column\n";
}

$db->sql("SHOW COLUMNS FROM `parcel_settings`");
echo "\nparcel_settings columns now: ";
$names = array();
foreach ($db->getResult() as $row) {
    $names[] = $row['Field'] . ' ' . $row['Type'];
}
echo implode(', ', $names) . "\n";

$db->sql("SELECT id, opening_time, closing_time FROM `parcel_settings` ORDER BY id");
echo "\ncurrent operating hours:\n";
foreach ($db->getResult() as $row) {
    printf(
        "  id=%-3d opening=%-10s closing=%-10s\n",
        $row['id'],
        $row['opening_time'] === null ? 'NULL' : $row['opening_time'],
        $row['closing_time'] === null ? 'NULL' : $row['closing_time']
    );
}
