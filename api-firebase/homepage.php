<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db=new Database();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db->connect(); 
include_once('../includes/variables.php');
include_once('verify-token.php');
/* accesskey:90336 */
if(!verify_token()){
    return false;
}
if(isset($_POST['accesskey'])) {
    $data=array();
	$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));		
	if($access_key_received == $access_key){
		// get all category data from category table
		$sql_query = "SELECT * FROM category ORDER BY cat_priority ASC ";
		$db->sql($sql_query);
		$res=$db->getResult();
		for($i=0;$i<count($res);$i++){
			// $categories[] = array('category'=>$i);
			$res[$i]['image'] = (!empty($res[$i]['image']))?DOMAIN_URL.''.$res[$i]['image']:'';
			$sql_query1 = "SELECT count(id) as count FROM subcategory WHERE category_id=".$res[$i]['id']." GROUP BY category_id ";
    		$db->sql($sql_query1);
    		$res1=$db->getResult();
    		if (!empty($res1)) {
    		    $res[$i]['subcategory']="Yes";
    		}else{
    		     $res[$i]['subcategory']="No";
    		}
		}
		$data['category']=$res;
		
		$sql = 'select * from offers order by id desc';
    	$db->sql($sql);
    	$result =$db->getResult();
    	$temp = $temp1 = array();
    	foreach($result as $row){
    		$temp['image'] = DOMAIN_URL.$row['image'];
    		$temp1[] = $temp;
    	}
    	$data['offers'] = $temp1;
    	
    	$sql = 'select * from slider where section_type=1 order by id desc';
    	$db->sql($sql);
    	$result =$db->getResult();
    	$temp = $temp1 = array();
    	foreach($result as $row){
    		$name = "";
    		if($row['type'] == 'main_category'){
    		    $sql = 'select `name` from main_category where id = '.$row['type_id'].' order by id desc';
    		    $db->sql($sql);
    		    $result1 = $db->getResult();
    		    $name = (!empty($result1[0]['name']))?$result1[0]['name']:"";
    		}
    // 		if($row['type'] == 'product'){
    // 		    $sql = 'select `name` from products where id = '.$row['type_id'].' order by id desc';
    // 		    $db->sql($sql);
    // 		    $result1 = $db->getResult();
    // 		    $name = (!empty($result1[0]['name']))?$result1[0]['name']:"";
    // 		}
    		
    		$temp['type'] = $row['type'];
    		$temp['type_id'] = $row['type_id'];
    		$temp['name'] = $name;
    		$temp['image'] = DOMAIN_URL.$row['image'];
    		$temp1[] = $temp;
    	}
    	$data['slider_section_one'] = $temp1;

		$sql = 'select * from slider where section_type=2 order by id desc';
    	$db->sql($sql);
    	$result =$db->getResult();
    	$temp = $temp1 = array();
    	foreach($result as $row){
    		$name = "";
    		if($row['type'] == 'category'){
    		    $sql = 'select `name` from category where id = '.$row['type_id'].' order by id desc';
    		    $db->sql($sql);
    		    $result1 = $db->getResult();
    		    $name = (!empty($result1[0]['name']))?$result1[0]['name']:"";
    		}
    		if($row['type'] == 'product'){
    		    $sql = 'select `name` from products where id = '.$row['type_id'].' order by id desc';
    		    $db->sql($sql);
    		    $result1 = $db->getResult();
    		    $name = (!empty($result1[0]['name']))?$result1[0]['name']:"";
    		}
    		
    		$temp['type'] = $row['type'];
    		$temp['type_id'] = $row['type_id'];
    		$temp['name'] = $name;
    		$temp['image'] = DOMAIN_URL.$row['image'];
    		$temp1[] = $temp;
    	}
    	$data['slider_section_two'] = $temp1;

		$sql = 'select * from main_category order by cat_priority asc';
    	$db->sql($sql);
    	$result =$db->getResult();
    	$temp = $temp1 = array();
    	foreach($result as $row){
    		
    		$temp['id'] = $row['id'];
    // 		$temp['name'] = $row['name'];
            $temp['name'] = ucfirst(strtolower($row['name']));
    		$temp['image'] = DOMAIN_URL.$row['image'];
    		$temp1[] = $temp;
    	}
    	$data['main_category'] = $temp1;
    	
    	$sql = 'select * from `sections` order by id desc';
    	$db->sql($sql);
    	$result =$db->getResult();
    	$product_ids = $section = $variations = $temp = array();
    	foreach($result as $row){
		$product_ids = explode(',',$row['product_ids']);
		
		$section['id'] = $row['id'];
		$section['title'] = $row['title'];
		$section['short_description'] = $row['short_description'];
		$section['style'] = $row['style'];
		$section['place'] = $row['place'];
		$section['product_ids'] = array_map('trim',$product_ids);
		$product_ids = $section['product_ids'];

		$product_ids = implode(',', $product_ids);
		
		// $sql = 'SELECT * FROM `products` where id in ('.$row['product_ids'].') ORDER BY FIELD(id, '.$row['product_ids'].')';
		$sql = 'SELECT *,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p WHERE status="1" and id IN ('.$product_ids.')';
		// echo $sql;
		$db->sql($sql);
		$result1 = $db->getResult();
		$product = array();
		$i=0;
		foreach($result1 as $row){
		    $sql = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS user_count FROM product_rating WHERE product_id=".$row['id']." GROUP BY product_id";
                $db->sql($sql);
                $rating = $db->getResult();
                if(isset($rating[0])){
                    $row['rating'] = $rating[0]['avg_rating'] ? $rating[0]['avg_rating'] :"0";
                    $row['rating_count'] = $rating[0]['user_count'] ? $rating[0]['user_count'] :"0";
                }else{
                    
                $row['rating'] = "0";
                $row['rating_count'] = "0";
                }
			$sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id']."";
                // echo $sql;
                $db->sql($sql);
                $variants=$db->getResult();
                if($row['brand_name']==null){
                        $row['brand_name']="";
                    }
                $row['other_images'] = json_decode($row['other_images'],1);
        		$row['other_images'] = (empty($row['other_images']))?array():$row['other_images'];

        		for($j=0;$j<count($row['other_images']);$j++){
        		    $row['other_images'][$j] = DOMAIN_URL.$row['other_images'][$j];
        		}
        		for($k=0;$k<count($variants);$k++){
        		    if($variants[$k]['stock']<=0){
        		        $variants[$k]['serve_for']='Sold Out';
        		    }else{
        		        $variants[$k]['serve_for']='Available';
        		    }
        		     $variants[$k]['min_order_qty']=$row['min_order_qty']; 
        		}
        		
        		
        		$row['image'] = DOMAIN_URL.$row['image'];
                $product[$i] = $row;
                $product[$i]['variants'] = $variants;
                $i++;
			
		}
		$section['products'] = $product;
		$temp[] = $section;
		unset($section['products']);
	}
    	
    	$data['section'] = $temp;
		$response['error'] = "false";
		$response['data'] = $data;
		
        	print_r(json_encode($response));
	}else{
		die('accesskey is incorrect.');
	}
} else {
	die('accesskey is require.');
}
//Output the output.
// echo $output;
$db->disconnect(); 
?>