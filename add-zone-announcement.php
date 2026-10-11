<?php
/**
 * One-off migration: per-zone announcement banner settings.
 *
 * Each zone gets its own announcement text, an enable/disable switch and an
 * "auto-enable on rain" toggle parsed from weather (Open-Meteo) for that zone.
 * Customers see the banner for the zone their location resolves to.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'announcement_text'         => 'TEXT NULL DEFAULT NULL',
    'announcement_enabled'      => 'TINYINT(1) NOT NULL DEFAULT 0',
    'announcement_auto_weather' => 'TINYINT(1) NOT NULL DEFAULT 0',
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

$db->sql("SELECT id, name, announcement_text, announcement_enabled, announcement_auto_weather FROM `zone` ORDER BY id");
echo "\ncurrent per-zone announcement settings:\n";
foreach ($db->getResult() as $row) {
    printf(
        "  id=%-3d %-20s text=%-12s enabled=%d auto_weather=%d\n",
        $row['id'],
        $row['name'],
        $row['announcement_text'] === null ? 'NULL' : (strlen($row['announcement_text']) > 12 ? substr($row['announcement_text'], 0, 12) . '...' : $row['announcement_text']),
        $row['announcement_enabled'],
        $row['announcement_auto_weather']
    );
}