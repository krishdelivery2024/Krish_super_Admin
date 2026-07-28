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
    
  
	
            
		    
            $sql = "SELECT name,image FROM products p";
            $db->sql($sql);
            $res = $db->getResult();
            // return $res;
            $product = array();
            $i = 0;
            foreach($res as $row){
                
                
                $product[$i] = $row;
                
                $i++;
            }
		    
			// create json output
			if(!empty($product)){
			
			    $output = json_encode(
			        array('error' => false,
			            'total' => count($product),
			            
			            'message' => "Products retrieved successfully",
			            'data' => $product
			     )
			    );
			}else{
			    $output = json_encode(array('error' => true,
    			    'total' => $total[0]['total'],
    			    'limit' => $limit,
    	            'offset' => $offset,
    	            'sort' => $sort,
    	            'order' => $order,
    			    'message' => 'No products available',
    			    'data' => array()
			    )
			  );
			}
		

 
	//Output the output.
	echo $output;

	$db->disconnect(); 
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>