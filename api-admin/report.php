<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Cache-Control: no-store, no-cache, must-revalidate");
session_start();

include '../includes/crud.php';
include '../includes/variables.php';
include '../api-firebase/verify-token.php';
include '../includes/custom-functions.php';

$db = new Database();
$db->connect();
$fn = new custom_functions();
date_default_timezone_set('Asia/Kolkata');

// Validate Access Key
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey'] ?? ''));
if ($access_key != $accesskey) {
    exit(json_encode(["error" => true, "message" => "Invalid access key"]));
}

// Validate Seller
$seller_id = intval($_POST['seller_id'] ?? 0);
if ($seller_id <= 0) {
    exit(json_encode(["error" => true, "message" => "Invalid seller ID"]));
}

// Helper: Build date filter
function dateFilter($start, $end, $col = 'date_added') {
    if ($start && $end) {
        $start = date('Y-m-d 00:00:00', strtotime($start));
        $end = date('Y-m-d 23:59:59', strtotime($end));
        return " AND $col BETWEEN '$start' AND '$end' ";
    }
    return "";
}

$type = $_POST['type'] ?? '';

switch ($type) {
    // ==============================
    // 1️⃣ High Selling Products
    // ==============================
    case 'high_selling_products':
        $filter = dateFilter($_POST['start_date'] ?? '', $_POST['end_date'] ?? '', 'oi.date_added');
        $sql = "SELECT p.name, v.product_id, SUM(oi.quantity) AS qty, v.measurement,
                    COUNT(o.id) AS order_count,
                    (SELECT short_code FROM unit un WHERE un.id = v.measurement_unit_id) AS mesurement_unit_name
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN product_variant v ON oi.product_variant_id = v.id
                JOIN products p ON p.id = v.product_id
                WHERE o.seller_id = $seller_id AND oi.active_status!='cancelled' $filter
                GROUP BY v.id ORDER BY qty DESC";
        break;

    // ==============================
    // 2️⃣ High Buying Customers
    // ==============================
    case 'high_buying_customers':
        $filter = '';
        $cdate = date('Y-m-d');
        if (isset($_POST['filter_by'])) {
            switch ($_POST['filter_by']) {
                case 'day': $filter = " AND orders.date_added > '$cdate' "; break;
                case 'week': $filter = " AND orders.date_added > '" . date('Y-m-d', strtotime('last sunday')) . "' "; break;
                case 'month': $filter = " AND orders.date_added > '" . date('Y-m-01') . "' "; break;
            }
        } else {
            $filter = dateFilter($_POST['start_date'] ?? '', $_POST['end_date'] ?? '', 'orders.date_added');
        }

        $sql = "SELECT users.name, users.email, users.id, users.country_code, users.mobile,
                        COUNT(orders.id) AS orders_count,
                        SUM(orders.final_total) AS amt,
                        FORMAT(SUM(orders.final_total),0) AS amount
                FROM users
                INNER JOIN orders ON users.id = orders.user_id
                WHERE orders.seller_id = $seller_id AND orders.active_status!='cancelled' $filter
                GROUP BY orders.user_id ORDER BY amt DESC";
        break;

    // ==============================
    // 3️⃣ Micro Reports (Today / Week / Month / Total)
    // ==============================
    case 'micro_reports':
        $today = date('Y-m-d 00:00:00');
        $tomorrow = date('Y-m-d 23:59:59');
        $week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
        $month_start = date('Y-m-01 00:00:00');

        $reports = [];

        // Today Sales
        $sql_today = "SELECT IFNULL(SUM(final_total),0) AS total 
                      FROM orders 
                      WHERE seller_id = $seller_id 
                      AND active_status!='cancelled' 
                      AND date_added BETWEEN '$today' AND '$tomorrow'";
        $db->sql($sql_today);
        $reports['today_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Weekly Sales
        $sql_week = "SELECT IFNULL(SUM(final_total),0) AS total 
                     FROM orders 
                     WHERE seller_id = $seller_id 
                     AND active_status!='cancelled' 
                     AND date_added >= '$week_start'";
        $db->sql($sql_week);
        $reports['weekly_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Monthly Sales
        $sql_month = "SELECT IFNULL(SUM(final_total),0) AS total 
                      FROM orders 
                      WHERE seller_id = $seller_id 
                      AND active_status!='cancelled' 
                      AND date_added >= '$month_start'";
        $db->sql($sql_month);
        $reports['monthly_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Total Sales
        $sql_total = "SELECT IFNULL(SUM(final_total),0) AS total 
                      FROM orders 
                      WHERE seller_id = $seller_id 
                      AND active_status!='cancelled'";
        $db->sql($sql_total);
        $reports['total_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Add counts
        $sql_counts = "SELECT 
                          COUNT(CASE WHEN date_added BETWEEN '$today' AND '$tomorrow' THEN id END) AS today_orders,
                          COUNT(CASE WHEN date_added >= '$week_start' THEN id END) AS weekly_orders,
                          COUNT(CASE WHEN date_added >= '$month_start' THEN id END) AS monthly_orders,
                          COUNT(*) AS total_orders
                       FROM orders
                       WHERE seller_id = $seller_id AND active_status!='cancelled'";
        $db->sql($sql_counts);
        $counts = $db->getResult()[0] ?? [];

        $response = [
            "error" => false,
            "data" => [
                "sales" => $reports,
                "counts" => $counts
            ]
        ];
        echo json_encode($response);
        $db->disconnect();
        exit;

    default:
        echo json_encode(["error" => true, "message" => "Invalid request type"]);
        exit;
}

$db->sql($sql);
$res = $db->getResult();
echo json_encode(["error" => false, "data" => $res]);
$db->disconnect();
?>
