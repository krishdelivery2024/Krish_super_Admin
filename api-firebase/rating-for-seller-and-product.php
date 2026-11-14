<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db = new Database();
$db->connect(); 
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
include_once('../includes/variables.php');
include_once('verify-token.php');

if (!verify_token()) {
    return false;
}

if (isset($_POST['accesskey'])) {
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    $user_id = isset($_POST['user_id']) ? $db->escapeString($fn->xss_clean($_POST['user_id'])) : "";
    $order_id = isset($_POST['order_id']) ? $db->escapeString($fn->xss_clean($_POST['order_id'])) : "";
    $rating_desc = isset($_POST['rating_desc']) ? $db->escapeString($fn->xss_clean($_POST['rating_desc'])) : "";
    $seller_id = isset($_POST['seller_id']) ? $db->escapeString($fn->xss_clean($_POST['seller_id'])) : "";
    $seller_rating = isset($_POST['seller_rating']) ? $db->escapeString($fn->xss_clean($_POST['seller_rating'])) : "";
    if ($access_key_received == $access_key) {
        $cdate = date("Y-m-d H:i:s");
        // Rating for products
        // $product_ids = json_decode($_POST['product_id'], true);
        // $ratings = json_decode($_POST['rating'], true);
        
        $product_ids = $_POST['product_id'];
        $ratings = $_POST['rating'];
        
       

    $sql = "SELECT id FROM orders WHERE id='$order_id' AND rated='true'";
    $db->sql($sql);
    if ($db->numRows() > 0) {
        $res['error'] = true;
        $res['message'] = "This order already rated!";
    }else{
        if (!empty($seller_id) && !empty($seller_rating) && floatval($seller_rating) > 0) {
            $seller_rating_val = floatval($seller_rating);
            $sql_check = "SELECT id FROM seller_rating WHERE seller_id='$seller_id' AND order_id='$order_id' AND user_id='$user_id'";
            $db->sql($sql_check);
            if ($db->numRows() > 0) {
                $sql = "UPDATE seller_rating SET rating='$seller_rating_val', rating_desc='$rating_desc', created_at='$cdate' WHERE seller_id='$seller_id' AND order_id='$order_id' AND user_id='$user_id'";
            } else {
                $sql = "INSERT INTO seller_rating (seller_id,user_id,order_id,rating,rating_desc,created_at) 
                        VALUES('$seller_id','$user_id','$order_id','$seller_rating_val','$rating_desc','$cdate')";
            }
            $db->sql($sql);

            $sql = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS user_count FROM seller_rating WHERE seller_id='$seller_id' AND rating > 0";
            $db->sql($sql);			
            $seller_result = $db->getResult();
            if(isset($seller_result[0]) && !empty($seller_result[0]['avg_rating'])){
                $avg_rating = strval(round(floatval($seller_result[0]['avg_rating']), 1));
                $user_count = strval($seller_result[0]['user_count']);

                $sql = "UPDATE seller SET rating='$avg_rating', rating_count='$user_count' WHERE id='$seller_id'";
                $db->sql($sql);
            }
        }

        if (!empty($product_ids) && is_array($product_ids) && !empty($ratings) && is_array($ratings)) {
            foreach ($product_ids as $index => $product_id) {
                $product_id = $db->escapeString($fn->xss_clean($product_id));
                $rating_val = isset($ratings[$index]) ? floatval($ratings[$index]) : 0;
                // Only save real ratings (> 0 stars)
                if ($rating_val <= 0) {
                    continue;
                }
                $sql_check = "SELECT id FROM product_rating WHERE product_id='$product_id' AND order_id='$order_id' AND user_id='$user_id'";
                $db->sql($sql_check);
                if ($db->numRows() > 0) {
                    $sql = "UPDATE product_rating SET rating='$rating_val', rating_desc='$rating_desc', created_at='$cdate' WHERE product_id='$product_id' AND order_id='$order_id' AND user_id='$user_id'";
                } else {
                    $sql = "INSERT INTO product_rating (product_id,user_id,order_id,rating,rating_desc,created_at) 
                            VALUES('$product_id','$user_id','$order_id','$rating_val','$rating_desc','$cdate')";
                }
                $db->sql($sql);
            }

            $sql = "UPDATE orders SET rated='true' WHERE id='$order_id'";
            $db->sql($sql);

            $res['error'] = false;
            $res['message'] = "Ratings saved successfully";
        } else {
            $sql = "UPDATE orders SET rated='true' WHERE id='$order_id'";
            $db->sql($sql);

            $res['error'] = false;
            $res['message'] = "Ratings saved successfully";
        }

    }

        echo json_encode($res);

    } else {
        die('accesskey is incorrect.');
    }
} else {
    die('accesskey is required.');
}

$db->disconnect();
?>
