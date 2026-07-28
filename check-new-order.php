<?php
/*
    check-new-order.php
    ----------------------------------------------------------------
    Polled every 15 seconds from the browser (see the script added to
    the sidebar/header file). Returns any orders whose id is greater
    than the `last_id` the browser already knows about.

    NOTE: This queries `SELECT *` from `orders` and then picks out a
    few common-looking columns (order_number, customer_name,
    grand_total, date_added) to display in the popup. If your real
    column names differ, adjust the `$new_orders[] = [...]` block
    below to match your actual `orders` table schema.
*/

session_start();
include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");

header('Content-Type: application/json');

// Must be logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

$last_id = isset($_GET['last_id']) ? (int) $_GET['last_id'] : 0;

// Sellers only see their own orders, admins/super admins see everything
$seller_filter = '';
if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') {
    $seller_id = (int) $_SESSION['id'];
    $seller_filter = " AND seller_id = '$seller_id' ";
}

// Current highest order id (used both as the baseline and as the
// value returned to the browser so it knows where it left off)
$db->sql("SELECT MAX(id) AS max_id FROM orders WHERE 1 $seller_filter");
$max_res = $db->getResult();
$current_max_id = isset($max_res[0]['max_id']) ? (int) $max_res[0]['max_id'] : 0;

$new_orders = [];

if ($last_id > 0 && $current_max_id > $last_id) {
    $last_id_safe = (int) $last_id; // already cast above, kept explicit for the query
    $sql = "SELECT * FROM orders WHERE id > $last_id_safe $seller_filter ORDER BY id DESC";
    $db->sql($sql);
    $rows = $db->getResult();

    foreach ($rows as $row) {
        $new_orders[] = [
            'id'            => $row['id'],
            'order_number'  => isset($row['order_number']) ? $row['order_number'] : $row['id'],
            'customer_name' => isset($row['customer_name'])
                ? $row['customer_name']
                : (isset($row['name']) ? $row['name'] : 'Customer'),
            'amount'        => isset($row['grand_total'])
                ? $row['grand_total']
                : (isset($row['total']) ? $row['total'] : ''),
            'date_added'    => isset($row['date_added']) ? $row['date_added'] : '',
        ];
    }
}

echo json_encode([
    'new_orders' => $new_orders,
    'count'      => count($new_orders),
    'last_id'    => $current_max_id,
]);
exit;