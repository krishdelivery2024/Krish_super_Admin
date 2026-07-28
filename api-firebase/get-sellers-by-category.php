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
        $category_id = (isset($_POST['category_id'])) ? $db->escapeString($fn->xss_clean($_POST['category_id'])) : "0";
		$user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
		if ($access_key_received == $access_key) {

			// Step 1: Get unique category_ids where seller has active products
			$sql = "SELECT DISTINCT seller_id FROM products WHERE category_id = '$category_id' AND is_active = '1' GROUP BY seller_id";
			$db->sql($sql);
			$res = $db->getResult();
            // print_r($res);die;
			if (!empty($res)) {
				$seller_ids = array_column($res, 'seller_id');
				$seller_ids_str = implode(',', $seller_ids);

				// Step 2: Get category details
				$sql = "SELECT *
						FROM seller 
						WHERE id IN ($seller_ids_str) AND status ='1' ORDER BY id ASC";
				$db->sql($sql);
				$sellers = $db->getResult();
                for ($i = 0; $i < count($sellers); $i++) {
				$seller_id = $sellers[$i]['id'];
				
				// Check if this seller is in the user's wishlist
				$sql_query = "SELECT seller_id FROM wishlists WHERE user_id = '$user_id' AND seller_id = '$seller_id'";
				$db->sql($sql_query);
				$wishlist_result = $db->getResult();

				// Add wishlist status to each seller
				$sellers[$i]['wishlist'] = !empty($wishlist_result);

				// Add image and banner URLs
				$sellers[$i]['image'] = (!empty($sellers[$i]['image'])) ? DOMAIN_URL . 'upload/sellers/' . $sellers[$i]['image'] : '';
				$sellers[$i]['banner'] = (!empty($sellers[$i]['banner'])) ? DOMAIN_URL . 'upload/sellers/' . $sellers[$i]['banner'] : '';
			}
				$response['error'] = false;
				$response['data'] = $sellers;

			} else {
				$response['error'] = true;
				$response['message'] = "No Seller found for this category.";
			}
			$output = json_encode($response);

		} else {
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}

	echo $output;
	$db->disconnect(); 
?>
