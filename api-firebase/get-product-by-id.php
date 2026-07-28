<?php
    header('Access-Control-Allow-Origin: *');
	include_once('../includes/variables.php');
	include_once('../includes/crud.php');
	include_once('verify-token.php');
    $db = new Database();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    
  	/* accesskey:90336
  	 product_id:230 */
  	 if(!verify_token()){
		return false;
	  }
  $config = $fn->get_configurations();
	if(isset($_POST['accesskey']) && isset($_POST['product_id'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		$product_id = $db->escapeString($fn->xss_clean($_POST['product_id']));
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
		
		if($access_key_received == $access_key){

			if(!empty($product_id)){ 
                    $sql="SELECT *,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p WHERE id = '".$product_id."' ";
         
                }else{
                    // $sql="SELECT *,(SELECT MIN(price) FROM product_variant pv WHERE pv.product_id=p.id) as price FROM products p ".$sort."";
                    $sql = "SELECT *,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name FROM products p";
                 
                }
                
            $db->sql($sql);
            $res = $db->getResult();
            // return $res;
            $product = array();
            $i = 0;
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
                $sql = "SELECT * FROM product_rating WHERE product_id=".$row['id'];
                $db->sql($sql);
                $ratings = $db->getResult();
                $row['ratings']=$ratings;
                $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id']." ";
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
                
                $row['image'] = DOMAIN_URL.$row['image'];
                $product[$i] = $row;
                $product[$i]['variants'] = $variants;
                
                $i++;
            }
			// create json output
			if(!empty($product)){
			    $output = json_encode(array('error' => false,
			    'data' => $product));
			}else{
			    $output = json_encode(array('error' => true,
			    'data' => 'No products available'));
			}
		}else{
			$output = json_encode(array('error' => true,
			    'data' => 'accesskey is incorrect'));
		}
	} else {
	    $output = json_encode(array('error' => true,
			    'data' => 'accesskey and product id are required.'));
	}
 
	//Output the output.
	echo $output;

	$db->disconnect(); 
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>