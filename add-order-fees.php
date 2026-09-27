<?php
/**
 * One-off migration: record the platform and convenience fee actually charged on
 * a food order.
 *
 * The fee charged comes from the zone the order was delivered to, so storing it
 * per order keeps the charge auditable after the fact even if the zone's fee is
 * changed later.
 *
 * Safe to run more than once.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$wanted = array(
    'platform_fee'    => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
    'convenience_fee' => 'DECIMAL(10,2) NOT NULL DEFAULT 0.00',
);

foreach ($wanted as $column => $definition) {
    $db->sql("SHOW COLUMNS FROM `orders`");
    $existing = array();
    foreach ($db->getResult() as $row) {
        $existing[$row['Field']] = true;
    }
    if (isset($existing[$column])) {
        echo "  $column already exists, skipped\n";
        continue;
    }
    $db->sql("ALTER TABLE `orders` ADD COLUMN `$column` $definition");
    $db->sql("SHOW COLUMNS FROM `orders`");
    $now = array();
    foreach ($db->getResult() as $row) {
        $now[$row['Field']] = true;
    }
    echo isset($now[$column])
        ? "  added $column $definition\n"
        : "  FAILED to add $column\n";
}

$db->sql("SHOW COLUMNS FROM `orders`");
$names = array();
foreach ($db->getResult() as $row) {
    if (stripos($row['Field'], 'fee') !== false) {
        $names[] = $row['Field'] . ' ' . $row['Type'];
    }
}
echo "\nfee columns on orders: " . implode(', ', $names) . "\n";
