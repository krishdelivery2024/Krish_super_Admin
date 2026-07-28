<?php 
/*login*/
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
    $db = new Database();
    $db->connect();
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    date_default_timezone_set('Asia/Kolkata');
   /* accesskey:90336
    mobile:9974692496
    password:36652
    status:1   // 1 - Active & 0 Deactive */

if(isset($_POST['type'])){
    if($_POST['type']=="product-list"){
        $where = '';
		
		$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))? $db->escapeString($fn->xss_clean($_POST['limit'])) : 100000;
		    $offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))? $db->escapeString($fn->xss_clean($_POST['offset'])):0;
        if(isset($_POST['search']) AND $_POST['search']!=''){
			$search = $_POST['search'];
			$where = " where (p.`id` like '%".$search."%' OR p.`name` like '%".$search."%' OR pv.`measurement` like '%".$search."%' OR u.`short_code` like '%".$search."%' )";
		}
		$join = "JOIN `product_variant` pv ON pv.product_id = p.id LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
		$sql = "SELECT p.id AS id,pv.id AS variant_id, p.name, p.image,p.category_id,p.subcategory_id, p.brand_id,p.description, pv.price, pv.discounted_price, pv.measurement, pv.measurement_unit_id,pv.serve_for, pv.stock,pv.barcode_data, u.short_code 
            FROM `products` p
            $join 
            $where LIMIT $offset, $limit";
    $db->sql($sql);
    $res = $db->getResult();
        
        if(!empty($res)){
            $response['error'] = false;
            $response['product_count']=count($res);
            $response['data'] = ($res);
          
        }else{
            $response['error'] = true;
            $response['product_count']=0;
            $response['data']=array();
            $response['message'] = "No Categories found!";
            // return $res;
        }
    }else if($_POST['type']=="subcategory"){
		if(isset($_POST['category_id'])){
		    $sql = "select id,name,category_id FROM subcategory WHERE category_id=".$_POST['category_id'];
		}else{
		    $sql = "select id,name,category_id FROM subcategory";
		}
		
    $db->sql($sql);
    $res = $db->getResult();
        if(!empty($res)){
            $response['error'] = false;
            $response['data'] = ($res);
          
        }else{
            $response['error'] = true;
            $response['data']=array();
            $response['message'] = "No Sub Categories found!";
            // return $res;
        }
    }else if($_POST['type']=="brand"){
    	$sql="SELECT id,name FROM brand";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="category"){
    	$sql="SELECT id,name FROM category";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="unit"){
    	$sql="SELECT id,short_code AS name FROM unit";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
}else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
echo json_encode($response);
$db->disconnect();
?>