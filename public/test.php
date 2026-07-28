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

$seller_id = $_SESSION['id'];

// ================= DATE FORMAT =================

$newmonth = $_GET['start'];

$monthformate = explode("/", trim($newmonth));

$month = trim($monthformate[2])."-".$monthformate[1]."-".$monthformate[0];

$newdaysago = $_GET['end'];

$daysformate = explode("/", trim($newdaysago));

$daysago = trim($daysformate[2])."-".$daysformate[1]."-".$daysformate[0];

// ================= SEARCH =================

if (isset($_GET['search'])) {

    $keyword = trim($_GET['search']);

} else {

    $keyword = "";
}

// ================= QUERY =================

if (empty($keyword)) {

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

    LEFT JOIN order_items oi
        ON oi.order_id = o.id

    LEFT JOIN product_variant pv
        ON pv.id = oi.product_variant_id

    LEFT JOIN products p
        ON p.id = pv.product_id

    WHERE DATE(o.date_added) <= '".$daysago."'
    AND DATE(o.date_added) >= '".$month."'

    AND o.seller_id = '".$seller_id."'

    GROUP BY o.id

    ORDER BY o.id DESC";

} else {

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

    LEFT JOIN order_items oi
        ON oi.order_id = o.id

    LEFT JOIN product_variant pv
        ON pv.id = oi.product_variant_id

    LEFT JOIN products p
        ON p.id = pv.product_id

    WHERE DATE(o.date_added) <= '".$daysago."'
    AND DATE(o.date_added) >= '".$month."'

    AND (
        o.mobile LIKE '%".$keyword."%'
        OR p.name LIKE '%".$keyword."%'
    )

    AND o.seller_id = '".$seller_id."'

    GROUP BY o.id

    ORDER BY o.id DESC";
}

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

        $row['date_added'],

        $row['final_total'],

        ucfirst($row['payment_method'])

    ));
}

fclose($output);

exit;
?>