<?php
/**
 * One-off migration & auto-migration: record who cancelled an order in the database.
 * Adds `cancelled_by` column to `orders` and `order_items` tables if not already present.
 * Backfills past cancelled orders using status history analysis.
 *
 * Safe to run multiple times.
 */
include_once('includes/crud.php');
$db = new Database();
$db->connect();

$tables = array(
    'orders'      => array('cancelled_by' => 'VARCHAR(50) DEFAULT NULL'),
    'order_items' => array('cancelled_by' => 'VARCHAR(50) DEFAULT NULL')
);

header('Content-Type: text/plain; charset=utf-8');
echo "=== Migrating cancelled_by columns ===\n";

foreach ($tables as $table => $cols) {
    $db->sql("SHOW COLUMNS FROM `$table`");
    $existing = array();
    $rows = $db->getResult();
    if (is_array($rows)) {
        foreach ($rows as $row) {
            $existing[$row['Field']] = true;
        }
    }

    foreach ($cols as $column => $definition) {
        if (isset($existing[$column])) {
            echo "  [OK] `$table`.`$column` already exists, skipped.\n";
            continue;
        }
        $db->sql("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        echo "  [ADDED] `$table`.`$column` $definition\n";
    }
}

echo "\n=== Backfilling past cancelled orders ===\n";
// Find cancelled orders where cancelled_by is not set
$db->sql("SELECT id, status, active_status FROM `orders` WHERE active_status IN ('cancelled', 'canceled') AND (cancelled_by IS NULL OR cancelled_by = '')");
$cancelled_orders = $db->getResult();

$seller_count = 0;
$customer_count = 0;

if (is_array($cancelled_orders) && !empty($cancelled_orders)) {
    foreach ($cancelled_orders as $ord) {
        $oid = (int)$ord['id'];
        $actor = 'Customer';
        if (!empty($ord['status'])) {
            $status_hist = @json_decode($ord['status'], true);
            if (is_array($status_hist)) {
                // If order was processed/shipped before cancellation, customer was blocked from cancelling by policy
                foreach ($status_hist as $h) {
                    if (isset($h[0]) && in_array(strtolower(trim($h[0])), array('processed', 'processing', 'shipped', 'delivered'))) {
                        $actor = 'Seller';
                        break;
                    }
                }
            }
        }

        $db->sql("UPDATE `orders` SET `cancelled_by` = '$actor' WHERE id = $oid");
        $db->sql("UPDATE `order_items` SET `cancelled_by` = '$actor' WHERE order_id = $oid AND (cancelled_by IS NULL OR cancelled_by = '')");

        if ($actor === 'Seller') {
            $seller_count++;
        } else {
            $customer_count++;
        }
    }
    echo "  Updated $seller_count orders as 'Seller' cancelled.\n";
    echo "  Updated $customer_count orders as 'Customer' cancelled.\n";
} else {
    echo "  No unclassified cancelled orders found.\n";
}

echo "\n=== Migration completed successfully ===\n";

