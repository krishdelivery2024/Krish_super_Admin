<?php
/**
 * One-off migration: per-zone home slider images.
 *
 * NULL means "this slider applies to every zone", which is exactly how the
 * existing rows should behave. Nothing is backfilled, so the home carousel
 * keeps rendering the same images it does today until an admin deliberately
 * assigns a slider to a zone.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'zone_id' => 'INT(11) NULL DEFAULT NULL',
);

$db->sql("SHOW COLUMNS FROM `slider`");
$existing = array();
foreach ($db->getResult() as $row) {
    $existing[$row['Field']] = true;
}

foreach ($wanted as $column => $definition) {
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `slider` ADD COLUMN `$column` $definition");
    // Re-read rather than trusting a return value: the crud helper does not
    // report ALTER success, so confirm the column is actually there.
    $db->sql("SHOW COLUMNS FROM `slider`");
    $now = array();
    foreach ($db->getResult() as $row) {
        $now[$row['Field']] = true;
    }
    if (isset($now[$column])) {
        echo "  added $column $definition\n";
    } else {
        echo "  FAILED to add $column\n";
    }
}

$db->sql("SHOW COLUMNS FROM `slider`");
echo "\nslider columns now: ";
$names = array();
foreach ($db->getResult() as $row) {
    $names[] = $row['Field'] . ' ' . $row['Type'];
}
echo implode(', ', $names) . "\n";

$db->sql(
    "SELECT s.id, s.section_type, s.zone_id, z.name AS zone_name, s.image
     FROM `slider` s
     LEFT JOIN `zone` z ON z.id = s.zone_id
     ORDER BY s.id"
);
echo "\nslider rows (zone_id NULL = shows in every zone):\n";
foreach ($db->getResult() as $row) {
    printf(
        "  id=%-3d section=%-2d zone_id=%-6s %-16s %s\n",
        $row['id'],
        $row['section_type'],
        $row['zone_id'] === null ? 'NULL' : $row['zone_id'],
        $row['zone_name'] === null ? '(all zones)' : $row['zone_name'],
        $row['image']
    );
}
