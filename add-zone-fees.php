<?php
/**
 * One-off migration: per-zone platform and convenience fees.
 *
 * Both columns are nullable on purpose. NULL means "this zone has no opinion,
 * use the global setting", which keeps every existing zone charging exactly
 * what it charges today without anyone having to edit it.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'platform_fee'    => 'DECIMAL(10,2) NULL DEFAULT NULL',
    'convenience_fee' => 'DECIMAL(10,2) NULL DEFAULT NULL',
);

$sql = "SHOW COLUMNS FROM `zone`";
$db->sql($sql);
foreach ($db->getResult() as $row) {
    $existing[$row['Field']] = true;
}

foreach ($wanted as $column => $definition) {
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `zone` ADD COLUMN `$column` $definition");
    // Re-read rather than trusting a return value: the crud helper does not
    // report ALTER success, so confirm the column is actually there.
    $db->sql("SHOW COLUMNS FROM `zone`");
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

$db->sql("SHOW COLUMNS FROM `zone`");
echo "\nzone columns now: ";
$names = array();
foreach ($db->getResult() as $row) {
    $names[] = $row['Field'] . ' ' . $row['Type'];
}
echo implode(', ', $names) . "\n";

$db->sql("SELECT id, name, platform_fee, convenience_fee FROM `zone` ORDER BY id");
echo "\nper-zone fees (NULL = inherit global):\n";
foreach ($db->getResult() as $row) {
    printf(
        "  id=%-3d %-16s platform_fee=%-8s convenience_fee=%s\n",
        $row['id'],
        $row['name'],
        $row['platform_fee'] === null ? 'NULL' : $row['platform_fee'],
        $row['convenience_fee'] === null ? 'NULL' : $row['convenience_fee']
    );
}
