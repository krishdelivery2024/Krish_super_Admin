<?php
/**
 * One-off migration: per-zone parcel service settings.
 *
 * Each zone can override the global parcel tariff (per km price, base price),
 * maximum weight and whether pick-up service is offered in that zone. A NULL
 * column means "inherit the global parcel_settings value", matching how the
 * per-zone platform/convenience fees already work.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'parcel_per_km_price'          => 'DECIMAL(10,2) NULL DEFAULT NULL',
    'parcel_base_price'            => 'DECIMAL(10,2) NULL DEFAULT NULL',
    'parcel_max_weight_kg'         => 'DECIMAL(10,2) NULL DEFAULT NULL',
    'parcel_is_service_available'  => 'TINYINT(1) NULL DEFAULT NULL',
);

$db->sql("SHOW COLUMNS FROM `zone`");
$existing = array();
foreach ($db->getResult() as $row) {
    $existing[$row['Field']] = true;
}

foreach ($wanted as $column => $definition) {
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `zone` ADD COLUMN `$column` $definition");
    $db->sql("SHOW COLUMNS FROM `zone`");
    $now = array();
    foreach ($db->getResult() as $row) {
        $now[$row['Field']] = true;
    }
    echo isset($now[$column]) ? "  added $column $definition\n" : "  FAILED to add $column\n";
}

$db->sql("SHOW COLUMNS FROM `zone`");
echo "\nzone columns now: ";
$names = array();
foreach ($db->getResult() as $row) {
    $names[] = $row['Field'] . ' ' . $row['Type'];
}
echo implode(', ', $names) . "\n";

$db->sql("SELECT id, name, parcel_per_km_price, parcel_base_price, parcel_max_weight_kg, parcel_is_service_available FROM `zone` ORDER BY id");
echo "\ncurrent per-zone parcel settings:\n";
foreach ($db->getResult() as $row) {
    printf(
        "  id=%-3d %-20s per_km=%-8s base=%-8s max_kg=%-8s service=%s\n",
        $row['id'],
        $row['name'],
        $row['parcel_per_km_price'] === null ? 'NULL' : $row['parcel_per_km_price'],
        $row['parcel_base_price'] === null ? 'NULL' : $row['parcel_base_price'],
        $row['parcel_max_weight_kg'] === null ? 'NULL' : $row['parcel_max_weight_kg'],
        $row['parcel_is_service_available'] === null ? 'NULL' : $row['parcel_is_service_available']
    );
}