<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    echo json_encode(array('error' => true, 'message' => 'Unauthorized access.'));
    exit;
}

require_once '../includes/crud.php';
$db = new Database();
$db->connect();

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';

if ($product_id <= 0 || empty($status)) {
    echo json_encode(array('error' => true, 'message' => 'Invalid parameters.'));
    exit;
}

// Normalize status: Available vs Sold Out
if (strcasecmp($status, 'Available') === 0) {
    $status_text = 'Available';
    $prod_status = 1;
} else {
    $status_text = 'Sold Out';
    $prod_status = 0;
}

// If user is a seller (and not admin mode), verify that the product belongs to this seller
$is_admin_mode = (isset($_SESSION['secretlogin']) && $_SESSION['secretlogin'] == 'yes') || (isset($_SESSION['role']) && in_array($_SESSION['role'], array('admin', 'super admin')));
if ($_SESSION['role'] == 'seller' && !$is_admin_mode) {
    $seller_id = (int)$_SESSION['id'];
    $db->sql("SELECT id FROM products WHERE id = $product_id AND seller_id = $seller_id");
    $check = $db->getResult();
    if (empty($check)) {
        echo json_encode(array('error' => true, 'message' => 'Product does not belong to your store.'));
        exit;
    }
}

// Update variant if provided
if ($variant_id > 0) {
    $db->sql("UPDATE product_variant SET serve_for = '$status_text' WHERE id = $variant_id AND product_id = $product_id");
    
    // Check if any variant is still Available
    $db->sql("SELECT COUNT(*) as count FROM product_variant WHERE product_id = $product_id AND serve_for = 'Available'");
    $cnt_res = $db->getResult();
    $avail_count = isset($cnt_res[0]['count']) ? (int)$cnt_res[0]['count'] : 0;
    
    $final_prod_status = ($avail_count > 0) ? 1 : 0;
    $db->sql("UPDATE products SET status = $final_prod_status WHERE id = $product_id");
} else {
    // Update all variants of the product
    $db->sql("UPDATE product_variant SET serve_for = '$status_text' WHERE product_id = $product_id");
    $db->sql("UPDATE products SET status = $prod_status WHERE id = $product_id");
}

echo json_encode(array(
    'error' => false,
    'message' => 'Product availability updated to ' . ($status_text === 'Available' ? 'Available' : 'Sold') . ' successfully.',
    'status' => $status_text,
    'is_available' => ($status_text === 'Available')
));
exit;
