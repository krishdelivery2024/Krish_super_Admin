<?php
    header('Access-Control-Allow-Origin: *');
	include_once('../includes/crud.php');
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	// $function = new custom_functions;
    $db = new Database();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    $meal_filter = $fn->get_meal_availability_filter();

	/* accesskey:90336
  	 category_id:28 */
    if(!verify_token()){
    	return false;
    }
	if(isset($_POST['accesskey'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
        $seller_id = (isset($_POST['seller_id']))?$db->escapeString($fn->xss_clean($_POST['seller_id'])):"";
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $indicator = (isset($_POST['indicator']))?$db->escapeString($fn->xss_clean($_POST['indicator'])):"";
        $category_id = (isset($_POST['category_id']))?$db->escapeString($fn->xss_clean($_POST['category_id'])):"";
		$sort = (isset($_POST['sort']) && !empty($_POST['sort']))? $db->escapeString($fn->xss_clean($_POST['sort'])) : 'id';
		$limit=500;
		$offset=0;
	    $limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))? $db->escapeString($fn->xss_clean($_POST['limit'])):'10';
		$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))? $db->escapeString($fn->xss_clean($_POST['offset'])):'0';
        $where = '';
        if(isset($seller_id) && !empty($seller_id)){
            $where .= " and seller_id = '$seller_id'";
        }
        if(isset($indicator) && !empty($indicator)){
            $where .= " and indicator = '$indicator'";
        }
        if(isset($category_id) && !empty($category_id)){
            $where .= " and category_id = '$category_id'";
        }
		if($access_key_received == $access_key){
		    
            if($sort=='new'){
                $sort = 'ORDER BY date_added DESC';
                $price = 'MIN(price)';
                $price_sort = 'pv.price ASC';
            }elseif($sort=='old'){
                $sort = 'ORDER BY date_added ASC';
                $price = 'MIN(price)';
                $price_sort = 'pv.price ASC';
            }elseif($sort=='high'){
                $sort = 'ORDER BY price DESC';
                $price = 'MAX(price)';
                $price_sort = 'pv.price DESC';
            }elseif($sort=='low'){
                $sort = 'ORDER BY price ASC';
                $price = 'MIN(price)';
                $price_sort = 'pv.price ASC';
            }else{
                $sort = 'ORDER BY row_order ASC, name ASC';
                $price = 'MIN(price)';
                $price_sort = 'pv.price ASC';
            }

		    $sql = "SELECT count(id) as total from products p where is_active='1' ".$where.(!empty($meal_filter)?' AND '.$meal_filter:'');
		    $db->sql($sql);
		    $total = $db->getResult();
		         
		    $sql="SELECT *,(SELECT ".$price." FROM product_variant pv WHERE pv.product_id=p.id) as price,(SELECT count(id) FROM product_variant pv WHERE pv.product_id=p.id AND pv.stock!=0 AND pv.serve_for='Available') as stock_check,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p WHERE  is_active='1' ".$where.(!empty($meal_filter)?' AND '.$meal_filter:'')." ".$sort." LIMIT $offset, $limit";  
            $db->sql($sql);
            $res = $db->getResult();
            // return $res;
            $product = array();
            $product1 = array();
            $product2 = array();
            $i = 0;
            foreach($res as $row){
                $sql = "SELECT AVG(rating) AS avg_rating, COUNT(id) AS user_count FROM product_rating WHERE product_id=".$row['id']." GROUP BY product_id";
                $db->sql($sql);
                $rating = $db->getResult();
                if(isset($rating[0])){
                    $row['rating'] = $rating[0]['avg_rating'] ? $rating[0]['avg_rating'] :'0';
                    $row['rating_count'] = $rating[0]['user_count'] ? $rating[0]['user_count'] :'0';
                }else{
                $row['rating'] = '0';
                $row['rating_count'] = '0';
                }
                $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id']." ORDER BY serve_for ASC,".$price_sort."";
                    
                    // echo $sql;
                    // die();
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
                    for($k=0;$k<count($variants);$k++){
            		    if($variants[$k]['serve_for']=='Available'){
    						if($variants[$k]['stock']<=0){
    							$variants[$k]['serve_for']='Sold Out';
    						}
    						if($variants[$k]['stock']>0){
    							$variants[$k]['serve_for']='Available';	
    						}
            		    }
                        $product_variant_id = $variants[$k]['id'];
                        $sql_query = "SELECT quantity FROM carts WHERE product_variant_id = '$product_variant_id' AND user_id = '$user_id'";
                        $db->sql($sql_query);
                        $cart_result = $db->getResult();
                        if(!empty($cart_result)){
                            $variants[$k]['cart_added'] = !empty($cart_result);
                            $variants[$k]['cart_quantity'] = (int)$cart_result[0]['quantity'];
                        }else{
                            $variants[$k]['cart_added'] = !empty($cart_result);
                            $variants[$k]['cart_quantity'] = 0;
                        }
                        
                        
                        
            		}
                if($row['stock_check']!=0){
                        if(count($variants)>0){
                    $product1[$i] = $row;
                    
                    $product1[$i]['variants'] = $variants;
                    $i++;
                        }
                }else{
                    if(count($variants)>0){
                   $product2[$i] = $row;
                    
                    $product2[$i]['variants'] = $variants;
                    $i++;
                    }
                }
                
            }
            
		    $product=array_merge($product1,$product2);
			// create json output
			if(!empty($product)){
			    $output = json_encode(array('error' => false,
			    'total' => $total[0]['total'],
			    'data' => $product));
			}else{
			    $output = json_encode(array('error' => true,
			    'total' => $total[0]['total'],
			    'data' => array(),
			    'message' => 'No products available'));
			}
		}else{
			$output = json_encode(array('error' => true,
				'message' => 'accesskey is incorrect.'));
		}
	} else {
		$output = json_encode(array('error' => true,
			'message' => 'accesskey are required.'));
	}
 
	//Output the output.
	echo $output;
	
	$db->disconnect();
	
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>