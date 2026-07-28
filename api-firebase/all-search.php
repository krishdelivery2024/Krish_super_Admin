<?php 
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');

$fn = new custom_functions;
date_default_timezone_set('Asia/Kolkata');
/* accesskey:90336
	type:products-search
	search:Himalaya Baby Powder
	id:227*/
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));

    $config = $fn->get_configurations();
if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}
if(!verify_token()){
        return false;
}
// data of 'PRODUCTS' table goes here
if (isset($_POST['type']) && $_POST['type'] == 'all-search') {
    $offset = 0; $limit = 10;
    $sort = 'id'; $order = 'DESC';

    if (isset($_POST['offset']))
        $offset = $db->escapeString($fn->xss_clean($_POST['offset']));
    if (isset($_POST['limit']))
        $limit = $db->escapeString($fn->xss_clean($_POST['limit']));
    if (isset($_POST['sort']))
        $sort = $db->escapeString($fn->xss_clean($_POST['sort']));
    if (isset($_POST['order']))
        $order = $db->escapeString($fn->xss_clean($_POST['order']));

    $search = isset($_POST['search']) ? $db->escapeString($fn->xss_clean($_POST['search'])) : '';
    $main_cat_id = isset($_POST['main_cat_id']) ? $db->escapeString($fn->xss_clean($_POST['main_cat_id'])) : '';
    $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
    $where = "WHERE s.status = 1 AND p.status = 1 AND c.status = 1";

    // Filter by main_cat_id if present
    if (!empty($main_cat_id)) {
        $where .= " AND s.main_cat_id = '$main_cat_id'";
    }

    // Filter by search text
    if (!empty($search)) {
        $search_condition = " AND (
            c.name LIKE '%$search%' OR 
            p.name LIKE '%$search%' OR 
            s.name LIKE '%$search%'
        )";
        $where .= $search_condition;
    }

    $sql = "SELECT DISTINCT s.* 
            FROM seller s
            JOIN products p ON s.id = p.seller_id
            JOIN category c ON p.category_id = c.id
            $where 
            ORDER BY s.$sort $order 
            LIMIT $offset, $limit";

    $db->sql($sql);
    $res = $db->getResult();
    if($user_id != "" && $search != ""){
    $sql="INSERT INTO `search_histories`(`user_id`, `search`) VALUES ('$user_id','$search')";  
    $db->sql($sql);
    }

    if (empty($res)) {
        $response['error'] = true;
        $response['message'] = 'No sellers found';
    } else {
        for ($i = 0; $i < count($res); $i++) {
				$seller_id = $res[$i]['id'];
				
				// Check if this seller is in the user's wishlist
				$sql_query = "SELECT seller_id FROM wishlists WHERE user_id = '$user_id' AND seller_id = '$seller_id'";
				$db->sql($sql_query);
				$wishlist_result = $db->getResult();

				// Add wishlist status to each seller
				$res[$i]['wishlist'] = !empty($wishlist_result);

				// Add image and banner URLs
				$res[$i]['image'] = (!empty($res[$i]['image'])) ? DOMAIN_URL . 'upload/sellers/' . $res[$i]['image'] : '';
				$res[$i]['banner'] = (!empty($res[$i]['banner'])) ? DOMAIN_URL . 'upload/sellers/' . $res[$i]['banner'] : '';
			}
        $response['error'] = false;
        $response['data'] = $res;
    }

    print_r(json_encode($response));
}

function isJSON($string){
	return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
?>