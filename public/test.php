<?php
session_start();

include_once('../includes/variables.php');
include_once('../includes/crud.php');

header('Access-Control-Allow-Origin: *');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=salesreport.csv');

$output = fopen('php://output', 'w');

// ================= CSV HEADER =================

fputcsv($output, array(
    'ID',
    'Mobile',
    'Address',
    'Product Name',
    'Qty',
    'Order Date',
    'Final Total',
    'Payment Method'
));

$db = new Database();
$db->connect();

$is_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');
$seller_id = 0;
if ($is_seller) {
    if (!empty($_SESSION['seller_id'])) {
        $seller_id = (int)$_SESSION['seller_id'];
    } elseif (!empty($_SESSION['id'])) {
        $seller_id = (int)$_SESSION['id'];
    }
}

// ================= DATE FORMAT =================

function parse_date_param($d) {
    $d = trim((string)$d);
    if (strpos($d, '/') !== false) {
        $parts = explode('/', $d);
        if (count($parts) === 3) {
            return trim($parts[2]) . '-' . trim($parts[1]) . '-' . trim($parts[0]);
        }
    }
    return $d;
}

$month = parse_date_param($_GET['start'] ?? '');
$daysago = parse_date_param($_GET['end'] ?? '');

// ================= SEARCH =================

if (isset($_GET['search'])) {
    $keyword = trim($_GET['search']);
} else {
    $keyword = "";
}

// ================= QUERY =================

$where = " WHERE 1=1 ";
if (!empty($month) && !empty($daysago)) {
    $where .= " AND DATE(o.date_added) >= '" . $db->escapeString($month) . "' AND DATE(o.date_added) <= '" . $db->escapeString($daysago) . "' ";
} elseif (!empty($month)) {
    $where .= " AND DATE(o.date_added) >= '" . $db->escapeString($month) . "' ";
} elseif (!empty($daysago)) {
    $where .= " AND DATE(o.date_added) <= '" . $db->escapeString($daysago) . "' ";
}

if ($is_seller) {
    $where .= " AND o.seller_id = '" . $seller_id . "' ";
}

if (!empty($keyword)) {
    $kw = $db->escapeString($keyword);
    $where .= " AND (o.mobile LIKE '%" . $kw . "%' OR p.name LIKE '%" . $kw . "%') ";
}

$sql_query = "
SELECT 
    o.id,
    o.mobile,
    o.address,
    o.date_added,
    o.final_total,
    o.payment_method,
    GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS product_name,
    SUM(oi.quantity) AS qty
FROM orders o
LEFT JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
LEFT JOIN products p ON p.id = pv.product_id
" . $where . "
GROUP BY o.id
ORDER BY o.id DESC";

// ================= EXECUTE QUERY =================

$db->sql($sql_query);

$rows = $db->getResult();

// ================= CSV DATA =================

foreach ($rows as $row) {

    fputcsv($output, array(

        $row['id'],

        $row['mobile'],

        $row['address'],

        $row['product_name'],

        $row['qty'],

        !empty($row['date_added']) ? date('d-m-Y H:i', strtotime($row['date_added'])) : '-',

        $row['final_total'],

        ucfirst($row['payment_method'])

    ));
}

fclose($output);

exit;
?>