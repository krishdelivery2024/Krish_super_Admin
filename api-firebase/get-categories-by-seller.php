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
        $seller_id = (isset($_POST['seller_id'])) ? $db->escapeString($fn->xss_clean($_POST['seller_id'])) : "0";

		if ($access_key_received == $access_key) {

			// Step 1: Get unique category_ids where seller has active products
			$sql = "SELECT DISTINCT category_id FROM products WHERE seller_id = '$seller_id' AND is_active = '1' GROUP BY category_id";
			$db->sql($sql);
			$res = $db->getResult();

			if (!empty($res)) {
				$category_ids = array_column($res, 'category_id');
				$category_ids_str = implode(',', $category_ids);

				// Step 2: Get category details ordered by seller's custom order (Mobile User App)
				$db->sql("SHOW COLUMNS FROM seller_categories LIKE 'row_order'");
				$sc_col = $db->getResult();
				$has_sc_order = !empty($sc_col);

				if ($has_sc_order) {
					$sql = "SELECT c.id, c.main_cat, c.row_order, c.name, c.subtitle, c.image, c.status, c.cat_priority 
							FROM category c
							LEFT JOIN seller_categories sc ON sc.category_id = c.id AND sc.seller_id = '$seller_id'
							WHERE c.id IN ($category_ids_str) AND c.main_cat IN (SELECT id FROM main_category WHERE status = '1') 
							ORDER BY CASE WHEN sc.row_order IS NOT NULL AND sc.row_order > 0 THEN 0 ELSE 1 END ASC, sc.row_order ASC, c.cat_priority ASC, c.id ASC";
				} else {
					$sql = "SELECT id, main_cat, row_order, name, subtitle, image, status, cat_priority 
							FROM category 
							WHERE id IN ($category_ids_str) AND main_cat IN (SELECT id FROM main_category WHERE status = '1') 
							ORDER BY cat_priority ASC, id ASC";
				}
				$db->sql($sql);
				$categories = $db->getResult();
                for ($i = 0; $i < count($categories); $i++) {
                    $image_path = trim($categories[$i]['image'], '/'); // remove leading/trailing slashes
                    $categories[$i]['image'] = (!empty($image_path)) ? rtrim(DOMAIN_URL, '/') . '/' . $image_path : '';
                }
				$response['error'] = false;
				$response['data'] = $categories;

			} else {
				$response['error'] = true;
				$response['message'] = "No categories found for this seller.";
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
