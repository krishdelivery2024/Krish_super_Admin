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
// ACTION: SAVE SELLER CATEGORIES
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

    // Parse category IDs
    $raw_cats = isset($_POST['category_ids']) ? $_POST['category_ids'] : array();
    $cat_ids = array();
    if (is_array($raw_cats)) {
        foreach ($raw_cats as $cid) {
            $c = (int)$cid;
            if ($c > 0) $cat_ids[] = $c;
        }
    } elseif (is_string($raw_cats)) {
        $parts = explode(',', $raw_cats);
        foreach ($parts as $p) {
            $c = (int)trim($p);
            if ($c > 0) $cat_ids[] = $c;
        }
    }
    $cat_ids = array_values(array_unique($cat_ids));

    // Clear existing seller categories
    $db->sql("DELETE FROM seller_categories WHERE seller_id = $target_seller_id");

    // Insert new selected categories
    if (!empty($cat_ids)) {
        $insert_values = array();
        foreach ($cat_ids as $cid) {
            $insert_values[] = "($target_seller_id, $cid)";
        }
        $sql_insert = "INSERT INTO seller_categories (seller_id, category_id) VALUES " . implode(',', $insert_values);
        $db->sql($sql_insert);
    }

    // Keep seller.categories column updated in sync
    $cats_str = implode(',', $cat_ids);
    $db->sql("UPDATE seller SET categories = '$cats_str' WHERE id = $target_seller_id");

    $response['error'] = false;
    $response['message'] = 'Categories updated successfully!';
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

    $db->sql("SELECT sc.category_id, c.name, c.status, c.image 
              FROM seller_categories sc 
              INNER JOIN category c ON c.id = sc.category_id 
              WHERE sc.seller_id = $target_seller_id 
              ORDER BY c.name ASC");
    $selected = $db->getResult();

    $response['error'] = false;
    $response['seller_id'] = $target_seller_id;
    $response['data'] = $selected;
    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
