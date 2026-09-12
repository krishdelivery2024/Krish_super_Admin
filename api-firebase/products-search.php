<?php 
header('Access-Control-Allow-Origin: *');
include_once('../includes/crud.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
$db = new Database();
$db->connect();
include_once('../includes/custom-functions.php');

$fn = new custom_functions;
$meal_filter = $fn->get_meal_availability_filter();
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
if(isset($_POST['type']) && $_POST['type'] == 'products-search'){
	$offset = 0; $limit = 10;
	$sort = 'id'; $order = 'DESC';
	$where = '';
	if(isset($_POST['offset']))
		$offset = $db->escapeString($fn->xss_clean($_POST['offset']));
	if(isset($_POST['limit']))
		$limit = $db->escapeString($fn->xss_clean($_POST['limit']));
		
	if(isset($_POST['sort']))
		$sort = $db->escapeString($fn->xss_clean($_POST['sort']));
	if(isset($_POST['order']))
		$order = $db->escapeString($fn->xss_clean($_POST['order']));
		
	if(isset($_POST['search']) && $_POST['search']!=''){
		$search = $db->escapeString($fn->xss_clean($_POST['search']));
		$where = "and  status='1' and ( `id` like '%".$search."%' OR `name` like '%".$search."%' OR `image` like '%".$search."%' OR `subcategory_id` like '%".$search."%' OR `slug` like '%".$search."%' OR `description` like '%".$search."%')";
	}
	$main_cat_id = (isset($_POST['main_cat_id']))?$db->escapeString($fn->xss_clean($_POST['main_cat_id'])):"";
	if(isset($main_cat_id) && !empty($main_cat_id)){

		$sql1="SELECT * FROM seller WHERE main_cat_id = '$main_cat_id' AND status='1'";  
		$db->sql($sql1);
		$res1 = $db->getResult();
		$seller_ids = array_column($res1, 'id');  

			if (!empty($seller_ids)) {
				$ids = implode(",", array_map('intval', $seller_ids));
				$where .= " and seller_id IN ($ids)";
			}

	}
		$sql = "SELECT COUNT(id) as total FROM `products` WHERE is_active='1'".$where.(!empty($meal_filter)?' AND '.$meal_filter:'');
		$db->sql($sql);
		$res = $db->getResult();

	foreach($res as $row){
		$total = $row['total'];
	}

	$sql = "SELECT *,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name,(SELECT count(id) FROM product_variant pv WHERE pv.product_id=p.id AND pv.stock!=0 AND pv.serve_for='Available') as stock_check FROM products p  WHERE is_active='1'".$where.(!empty($meal_filter)?' AND '.$meal_filter:'');
		
	$db->sql($sql);
	$res = $db->getResult();
	$product = array();
	$i=0;
	
	foreach($res as $row){
	    $sql = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS user_count FROM product_rating WHERE product_id=".$row['id']." GROUP BY product_id";
                $db->sql($sql);
                $rating = $db->getResult();
                if(isset($rating[0])){
                    $row['rating'] = $rating[0]['avg_rating'] ? $rating[0]['avg_rating'] :0;
                    $row['rating_count'] = $rating[0]['user_count'] ? $rating[0]['user_count'] :0;
                }else{
                    
                $row['rating'] = 0;
                $row['rating_count'] = 0;
                }
		$sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id']." ORDER BY pv.serve_for, pv.stock DESC";
        $db->sql($sql);
        $variants = $db->getResult();
        if($row['brand_name']==null){
                        $row['brand_name']="";
                    }
        $row['other_images'] = json_decode($row['other_images'],1);
		$row['other_images'] = (empty($row['other_images']))?array():$row['other_images'];
		for($j=0;$j<count($row['other_images']);$j++){
		    $row['other_images'][$j] = DOMAIN_URL.$row['other_images'][$j];
		}
	    
	    $row['image'] = DOMAIN_URL.$row['image'];
	    $product[$i] = $row;
	    for($k=0;$k<count($variants);$k++){
		    if($variants[$k]['serve_for']=='Available'){
				if($variants[$k]['stock']<=0){
					$variants[$k]['serve_for']='Sold Out';
				}
				if($variants[$k]['stock']>0){
					$variants[$k]['serve_for']='Available';	
				}
		    } 	        
        }
	    $product[$i]['variants'] = $variants;
        $i++;
    
	}
	if(empty($product)){
    	$bulkData['error'] = true;
    	$bulkData['message'] = 'No Products';
    	print_r(json_encode($bulkData));
	}else{
    	$bulkData['error'] = false;
    	$bulkData['data'] = array_values($product);
    	print_r(json_encode($bulkData));
	}
	// $bulkData['rows'] = $rows;

}
function isJSON($string){
	return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
}
?>