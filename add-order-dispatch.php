<?php
/**
 * One-off migration: two-stage order dispatch state.
 *
 * New orders are notified in two phases so the closest, online delivery boys
 * get first call and the rest of the network is only involved if nobody within
 * the Phase-1 radius picks the job up inside 60 seconds.
 *
 *   dispatch_stage     ''      not yet dispatched (or already assigned)
 *                      'phase1' nearby online boys were notified
 *                      'phase2' order was broadcast to all online boys
 *   dispatch_phase1_at DATETIME when Phase-1 notification went out, used to
 *                      measure the 60 second Phase-1 wait.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'dispatch_stage'     => "VARCHAR(10) NOT NULL DEFAULT ''",
    'dispatch_phase1_at' => 'DATETIME NULL DEFAULT NULL',
);

foreach (array('orders', 'parcel_requests') as $table) {
    echo "Table: $table\n";
    $db->sql("SHOW COLUMNS FROM `$table`");
    $existing = array();
    foreach ($db->getResult() as $row) {
        $existing[$row['Field']] = true;
    }

    foreach ($wanted as $column => $definition) {
        if (isset($existing[$column])) {
            echo "  $column already exists, skipped\n";
            continue;
        }
        $db->sql("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        $db->sql("SHOW COLUMNS FROM `$table`");
        $now = array();
        foreach ($db->getResult() as $row) {
            $now[$row['Field']] = true;
        }
        echo isset($now[$column]) ? "  added $column $definition\n" : "  FAILED to add $column\n";
    }

    $db->sql("SHOW COLUMNS FROM `$table`");
    $names = array();
    foreach ($db->getResult() as $row) {
        if (in_array($row['Field'], array('dispatch_stage', 'dispatch_phase1_at'))) {
            $names[] = $row['Field'] . ' ' . $row['Type'];
        }
    }
    echo "  dispatch columns on $table: " . implode(', ', $names) . "\n\n";
}