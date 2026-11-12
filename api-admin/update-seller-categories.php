<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

session_start();
include_once('../includes/crud.php');
include_once('../includes/variables.php');
include_once('../includes/custom-functions.php');

$db = new Database();
$db->connect();
$fn = new custom_functions();

$response = array('error' => true, 'message' => 'Invalid request');

// Authentication check: Must be logged in via session (seller or admin) or supply valid accesskey
$is_authenticated = false;
$acting_seller_id = 0;

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'seller' && !empty($_SESSION['id'])) {
        $is_authenticated = true;
        $acting_seller_id = (int)$_SESSION['id'];
    } elseif (in_array($_SESSION['role'], array('super admin', 'admin', 'manager'))) {
        $is_authenticated = true;
    }
}

if (!$is_authenticated && isset($_POST['accesskey'])) {
    $req_key = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if ($req_key == $access_key) {
        $is_authenticated = true;
    }
}

if (!$is_authenticated) {
    $response['error'] = true;
    $response['message'] = 'Unauthorized access. Please log in.';
    echo json_encode($response);
    exit;
}

$action = isset($_POST['action']) ? trim($_POST['action']) : 'save_seller_categories';

// -------------------------------------------------------------
// ACTION: SAVE SELLER CATEGORIES & ORDER
// -------------------------------------------------------------
if ($action == 'save_seller_categories') {
    $target_seller_id = 0;
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') {
        $target_seller_id = (int)$_SESSION['id'];
    } else {
        $target_seller_id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
    }

    if ($target_seller_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'Invalid seller ID.';
        echo json_encode($response);
        exit;
    }

    // Parse selected category IDs (in the exact dragged order)
    $raw_cats = isset($_POST['category_ids']) ? $_POST['category_ids'] : array();
    $cat_ids = array();
    if (is_array($raw_cats)) {
        foreach ($raw_cats as $cid) {
            $c = (int)$cid;
            if ($c > 0 && !in_array($c, $cat_ids)) $cat_ids[] = $c;
        }
    } elseif (is_string($raw_cats)) {
        $parts = explode(',', $raw_cats);
        foreach ($parts as $p) {
            $c = (int)trim($p);
            if ($c > 0 && !in_array($c, $cat_ids)) $cat_ids[] = $c;
        }
    }

    // Ensure row_order column exists in seller_categories table
    $db->sql("SHOW COLUMNS FROM seller_categories LIKE 'row_order'");
    $col_res = $db->getResult();
    if (empty($col_res)) {
        $db->sql("ALTER TABLE seller_categories ADD COLUMN row_order INT NOT NULL DEFAULT 0 AFTER category_id");
    }

    // Clear existing seller categories
    $db->sql("DELETE FROM seller_categories WHERE seller_id = $target_seller_id");

    // Insert new selected categories with preserved custom order
    if (!empty($cat_ids)) {
        $insert_values = array();
        $order_num = 1;
        foreach ($cat_ids as $cid) {
            $insert_values[] = "($target_seller_id, $cid, $order_num)";
            $order_num++;
        }
        $sql_insert = "INSERT INTO seller_categories (seller_id, category_id, row_order) VALUES " . implode(',', $insert_values);
        $db->sql($sql_insert);
    }

    // Keep seller.categories column updated with ordered comma-separated IDs
    $cats_str = implode(',', $cat_ids);
    $db->sql("UPDATE seller SET categories = '$cats_str' WHERE id = $target_seller_id");

    $response['error'] = false;
    $response['message'] = 'Categories and custom order saved successfully!';
    $response['count'] = count($cat_ids);
    $response['category_ids'] = $cat_ids;
    echo json_encode($response);
    exit;
}

// -------------------------------------------------------------
// ACTION: GET SELLER CATEGORIES
// -------------------------------------------------------------
if ($action == 'get_seller_categories') {
    $target_seller_id = 0;
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') {
        $target_seller_id = (int)$_SESSION['id'];
    } else {
        $target_seller_id = isset($_POST['seller_id']) ? (int)$_POST['seller_id'] : 0;
    }

    if ($target_seller_id <= 0) {
        $response['error'] = true;
        $response['message'] = 'Invalid seller ID.';
        echo json_encode($response);
        exit;
    }

    $db->sql("SELECT sc.category_id, sc.row_order, c.name, c.status, c.image, c.cat_priority 
              FROM seller_categories sc 
              INNER JOIN category c ON c.id = sc.category_id 
              WHERE sc.seller_id = $target_seller_id 
              ORDER BY CASE WHEN sc.row_order > 0 THEN 0 ELSE 1 END ASC, sc.row_order ASC, c.cat_priority ASC, c.name ASC");
    $selected = $db->getResult();

    $response['error'] = false;
    $response['seller_id'] = $target_seller_id;
    $response['data'] = $selected;
    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
