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
    if(!verify_token()){
    	return false;
    }
	if(!isset($_POST['accesskey'])) {
	$output = json_encode(array('error' => true,
	'message' => 'accesskey are required.'));
    echo $output;
	$db->disconnect();
    die;
	}
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if($access_key_received != $access_key){
	$output = json_encode(array('error' => true,
	'message' => 'accesskey is incorrect.'));
    echo $output;
	$db->disconnect();
    die;
	}
    
	if(isset($_POST['type'])  && $_POST['type'] == "add-wishlist"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $seller_id = (isset($_POST['seller_id']))?$db->escapeString($fn->xss_clean($_POST['seller_id'])):"";

        if(empty($user_id) || empty($seller_id)){
            	$output = json_encode(array('error' => true,
                'message' => 'user_id or seller_id is empty.'));
                echo $output;
                $db->disconnect();
                die;
        }

        $sql="SELECT id FROM `seller` WHERE id='$seller_id' AND status='1'";  
        $db->sql($sql);
        $result=$db->getResult();
        $num_rows = $db->numRows($result);
        if($num_rows <= 0){
                $output = json_encode(array('error' => true,
                'message' => 'Invalid Seller Details'));
                echo $output;
                $db->disconnect();
                die;            
        }

        $sql="SELECT id FROM `wishlists` WHERE user_id='$user_id' AND seller_id='$seller_id'";  
        $db->sql($sql);
        $result=$db->getResult();
        $num_rows = $db->numRows($result);
        if($num_rows > 0){

            $sql =  "DELETE FROM `wishlists` WHERE `user_id`='$user_id' AND `seller_id`='$seller_id'";
            $db->sql($sql);

            $output = json_encode(array('error' => false,
            'message' => "wishlist removed successfully!"));
            echo $output;	
            $db->disconnect();
            die; 

        }else{

            $sql="INSERT INTO `wishlists`(`user_id`, `seller_id`) VALUES ('$user_id','$seller_id')";  
            $db->sql($sql);
        
        
        $output = json_encode(array('error' => false,
        'message' => "wishlist added successfully!"));
        echo $output;	
        $db->disconnect();
        die; 

        }     
            
             

    }

    

    if(isset($_POST['type'])  && $_POST['type'] == "list-wishlist"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        if(empty($user_id)){
            $response['error'] = false;
			$response['message'] = "user_id is empty!";
        }   

			$sql = "SELECT DISTINCT seller_id FROM wishlists WHERE user_id = '$user_id' GROUP BY seller_id LIMIT 10";
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
				$response['message'] = "No Seller found for this wishlist.";
			}
            $output = json_encode($response);
            echo $output;
            $db->disconnect(); 
    }
	
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>