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
    // ==============================
    // 1️⃣ High Selling Products
    // ==============================
    case 'high_selling_products':
        $filter = dateFilter($_POST['start_date'] ?? '', $_POST['end_date'] ?? '', 'oi.date_added');
        $sql = "SELECT p.name, v.product_id, SUM(oi.quantity) AS qty, 
                    CONCAT(v.measurement, ' ', (SELECT short_code FROM unit un WHERE un.id = v.measurement_unit_id)) AS measurement,
                    ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(v.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS amount,
                    COUNT(DISTINCT o.id) AS order_count,
                    (SELECT short_code FROM unit un WHERE un.id = v.measurement_unit_id) AS mesurement_unit_name
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                JOIN product_variant v ON oi.product_variant_id = v.id
                JOIN products p ON p.id = v.product_id
                WHERE o.seller_id = $seller_id AND oi.active_status!='cancelled' AND o.active_status!='cancelled' $filter
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
                        COUNT(DISTINCT orders.id) AS orders_count,
                        ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS amt,
                        FORMAT(ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2), 2) AS amount
                FROM users
                INNER JOIN orders ON users.id = orders.user_id
                INNER JOIN order_items oi ON oi.order_id = orders.id
                LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                WHERE orders.seller_id = $seller_id AND orders.active_status!='cancelled' AND oi.active_status!='cancelled' $filter
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

        // Today Sales (vendor price)
        $sql_today = "SELECT ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS total 
                      FROM orders o
                      JOIN order_items oi ON oi.order_id = o.id
                      LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                      WHERE o.seller_id = $seller_id 
                      AND o.active_status!='cancelled' 
                      AND oi.active_status!='cancelled' 
                      AND o.date_added BETWEEN '$today' AND '$tomorrow'";
        $db->sql($sql_today);
        $reports['today_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Weekly Sales (vendor price)
        $sql_week = "SELECT ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS total 
                     FROM orders o
                     JOIN order_items oi ON oi.order_id = o.id
                     LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                     WHERE o.seller_id = $seller_id 
                     AND o.active_status!='cancelled' 
                     AND oi.active_status!='cancelled' 
                     AND o.date_added >= '$week_start'";
        $db->sql($sql_week);
        $reports['weekly_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Monthly Sales (vendor price)
        $sql_month = "SELECT ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS total 
                      FROM orders o
                      JOIN order_items oi ON oi.order_id = o.id
                      LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                      WHERE o.seller_id = $seller_id 
                      AND o.active_status!='cancelled' 
                      AND oi.active_status!='cancelled' 
                      AND o.date_added >= '$month_start'";
        $db->sql($sql_month);
        $reports['monthly_sales'] = $db->getResult()[0]['total'] ?? 0;

        // Total Sales (vendor price)
        $sql_total = "SELECT ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS total 
                      FROM orders o
                      JOIN order_items oi ON oi.order_id = o.id
                      LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                      WHERE o.seller_id = $seller_id 
                      AND o.active_status!='cancelled' 
                      AND oi.active_status!='cancelled'";
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

    // ==============================
    // 4️⃣ Total Order Details
    // ==============================
    case 'total_order_details':
        $filter = dateFilter($_POST['start_date'] ?? '', $_POST['end_date'] ?? '', 'o.date_added');
        $sql = "SELECT
                    o.id AS order_id,
                    DATE_FORMAT(o.date_added, '%d-%m-%Y %H:%i') AS order_date,
                    o.active_status AS status,
                    o.payment_method,
                    ROUND(IFNULL((
                        SELECT SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity)
                        FROM order_items oi
                        LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                        WHERE oi.order_id = o.id AND oi.active_status != 'cancelled'
                    ), 0), 2) AS amount,
                    (SELECT COUNT(id) FROM order_items WHERE order_id = o.id AND active_status != 'cancelled') AS items_count
                FROM orders o
                WHERE o.seller_id = $seller_id AND o.active_status = 'delivered' $filter
                ORDER BY o.id DESC";
        break;

    // ==============================
    // 5️⃣ Settlement Report
    // ==============================
    case 'settlement_report':
        $filter = dateFilter($_POST['start_date'] ?? '', $_POST['end_date'] ?? '', 'o.date_added');
        
        $response_data = array();

        // 1. Total Orders Count
        $sql_orders = "SELECT COUNT(id) AS total_orders FROM orders o WHERE o.seller_id = $seller_id AND o.active_status = 'delivered' $filter";
        $db->sql($sql_orders);
        $total_orders = intval($db->getResult()[0]['total_orders'] ?? 0);

        // 2. Total Order Amount (includes ONLY vendor amounts of the product)
        $sql_amount = "SELECT ROUND(IFNULL(SUM(COALESCE(NULLIF(oi.vendor_price, ''), NULLIF(pv.vendor_price, ''), oi.price, 0) * oi.quantity), 0), 2) AS total_amount
                       FROM orders o
                       JOIN order_items oi ON oi.order_id = o.id
                       LEFT JOIN product_variant pv ON pv.id = oi.product_variant_id
                       WHERE o.seller_id = $seller_id 
                       AND o.active_status = 'delivered' 
                       AND oi.active_status != 'cancelled' $filter";
        $db->sql($sql_amount);
        $total_amount = floatval($db->getResult()[0]['total_amount'] ?? 0);

        // 3. Commission of the seller (seller-specific or global setting)
        $db->sql("SELECT commission FROM seller WHERE id = $seller_id");
        $seller_row = $db->getResult();
        $seller_comm = (!empty($seller_row) && isset($seller_row[0]['commission']) && $seller_row[0]['commission'] !== '') ? floatval($seller_row[0]['commission']) : 0.0;

        if ($seller_comm <= 0) {
            $db->sql("SELECT value FROM settings WHERE variable = 'system_timezone'");
            $settings_res = $db->getResult();
            if (!empty($settings_res)) {
                $sys_settings = json_decode($settings_res[0]['value'], true);
                if (isset($sys_settings['seller_commission']) && $sys_settings['seller_commission'] !== '') {
                    $seller_comm = floatval($sys_settings['seller_commission']);
                }
            }
        }

        $commission_percent = $seller_comm;
        $commission_amount = round(($total_amount * $commission_percent) / 100, 2);
        $total_payable = round($total_amount - $commission_amount, 2);

        $response_data['total_orders'] = $total_orders;
        $response_data['total_amount'] = number_format($total_amount, 2, '.', '');
        $response_data['commission_percent'] = $commission_percent;
        $response_data['commission_amount'] = number_format($commission_amount, 2, '.', '');
        $response_data['total_payable'] = number_format($total_payable, 2, '.', '');

        // Backward-compatibility defaults
        $response_data['total_cod'] = "0.00";
        $response_data['total_online'] = "0.00";
        $response_data['platform_fee_percent'] = 0;
        $response_data['platform_charges'] = "0.00";
        $response_data['gst_percent'] = 0;
        $response_data['gst_amount'] = "0.00";

        echo json_encode(["error" => false, "data" => $response_data]);
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
