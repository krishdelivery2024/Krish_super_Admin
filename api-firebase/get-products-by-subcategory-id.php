<?php
    header('Access-Control-Allow-Origin: *');
	include_once('../includes/crud.php');
	$db=new Database();
	$db->connect(); 
	include_once('../includes/variables.php');
	include_once('verify-token.php');
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
	/*accesskey:90336
  	 subcategory_id:32
  	 limit:10 // {optional}
  	 offset:0 // {optional}
  	 sort:new / old / high / low // {optional}
  	 */
  	 if(!verify_token()){
			return false;
	 }
    $config = $fn->get_configurations();
  	 if(isset($_POST['accesskey']) && isset($_POST['subcategory_id'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		$sort = (isset($_POST['sort']) && !empty($_POST['sort']))?$db->escapeString($fn->xss_clean($_POST['sort'])):'id';
	    $subcategory_id = (isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id'])) ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])) : "";
	    //$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($fn->xss_clean($_POST['limit'])):'10';
		$limit=500;
		$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($fn->xss_clean($_POST['offset'])):'0';
		
		if($access_key_received == $access_key){ 
		    
            if($sort=='new'){
                $sort = 'ORDER BY date_added DESC';
                $price = 'MIN(price)';
                $price_sort = 'ORDER BY pv.price ASC';
            }elseif($sort=='old'){
                $sort = 'ORDER BY date_added ASC';
                $price = 'MIN(price)';
                $price_sort = 'ORDER BY pv.price ASC';
            }elseif($sort=='high'){
                $sort = 'ORDER BY price DESC';
                $price = 'MAX(price)';
                $price_sort = 'ORDER BY pv.price DESC';
            }elseif($sort=='low'){
                $sort = 'ORDER BY price ASC';
                $price = 'MIN(price)';
                $price_sort = 'ORDER BY pv.price ASC';
            }else{
                $sort = 'ORDER BY p.row_order ASC';
                $price = 'MIN(price)';
                $price_sort = 'ORDER BY pv.serve_for, pv.stock DESC, pv.price ASC';
            }
            
            

            if(!empty($subcategory_id)){ 
                $sql = "SELECT count(id) as total FROM products WHERE status='1' and subcategory_id=".$subcategory_id;
                $db->sql($sql);
                $res = $db->getResult();
                foreach($res as $row){
    		        $total = $row['total'];
    	        }
                $sql="SELECT *,(SELECT ".$price." FROM product_variant pv WHERE pv.product_id=p.id) as price,(SELECT count(id) FROM product_variant pv WHERE pv.product_id=p.id AND pv.stock!=0 AND pv.serve_for='Available') as stock_check,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p WHERE status='1' and  subcategory_id='".$subcategory_id."' $sort LIMIT $offset, $limit";
            }else{
                $sql = "SELECT count(id) as total FROM products WHERE status='1'";
                $db->sql($sql);
                $res = $db->getResult();
                foreach($res as $row){
    		        $total = $row['total'];
    	        }
                $sql="SELECT *,(SELECT ".$price." FROM product_variant pv WHERE pv.product_id=p.id) as price,(SELECT count(id) FROM product_variant pv WHERE pv.product_id=p.id AND pv.stock!=0 AND pv.serve_for='Available') as stock_check,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p  WHERE status='1' $sort LIMIT $offset, $limit";
            }
           //  echo $sql;
            
            $db->sql($sql);
            $res = $db->getResult();
            
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
                $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id']." ".$price_sort."";
                    
                    //echo $sql;
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
                        //print_r($variants);exit;
            		    if($variants[$k]['serve_for']=='Available'){
    						if($variants[$k]['stock']<=0){
    							$variants[$k]['serve_for']='Sold Out';
    						}
    						if($variants[$k]['stock']>0){
    							$variants[$k]['serve_for']='Available';	
    						}
            		    }else{
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
            if(!empty($product)){
				
			    $output = json_encode(array('error' => false,'total' => $total,'data' => $product));
			}else{
			    $output = json_encode(array('error' => true,
			    'data'=>array(),
			    'message' => 'No products available'));
			}
		
		}else{
			$output = json_encode(array('error' => true,
										'message' => 'accesskey is incorrect.'));
		}
	} else {
		$output = json_encode(array('error' => true,
										'message' => 'accesskey and subcategory id are required.'));
	}
	//Output the output.
	echo $output;
	
	$db->disconnect();
	
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>