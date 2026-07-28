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
	$db->connect(); 
	include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	/* accesskey:90336 
		city_id:24 */
	if(!verify_token()){
		return false;
	}
	if(isset($_POST['accesskey'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		$user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
		$product_id = (isset($_POST['product_id']))?$db->escapeString($fn->xss_clean($_POST['product_id'])):"";
		$variant_id = (isset($_POST['product_variant_id']))?$db->escapeString($fn->xss_clean($_POST['product_variant_id'])):"";
		//$store_id = (isset($_POST['store_id']))?$db->escapeString($fn->xss_clean($_POST['store_id'])):"";
		if($access_key_received == $access_key){
			if (!empty($user_id) && !empty($product_id)) {
			   // $sql="SELECT id FROM stock_notify WHERE user_id=".$user_id." AND variant_id=".$variant_id." AND store_id=".$store_id;
			    $sql="SELECT id FROM stock_notify WHERE user_id=".$user_id." AND variant_id=".$variant_id;
			    $db->sql($sql);
			    if($db->numRows()==0){
			        $sql="SELECT name FROM products WHERE id=".$product_id;
			        $db->sql($sql);
			        $res = $db->getResult();
			        $product_name=$res[0]['name'];
			        $sql="SELECT fcm_id FROM users WHERE id=".$user_id;
			        $db->sql($sql);
			        $res = $db->getResult();
			        $fcm_id=$res[0]['fcm_id'];
			        $data=array(
			            'user_id'=>$user_id,
			            'product_id'=>$product_id,
			            //'store_id'=>$store_id,
			            'variant_id'=>$variant_id,
			            'fcm_id'=>$fcm_id,
			            'product_name'=>$product_name,
			            );
    				$res=$db->insert('stock_notify',$data);
    				if($res){
    				    $response['error'] = false;
        	            $response['message'] = "Stock Notification Applied";
    				}else{
    				    $response['error'] = true;
    	                $response['message'] = "Notification Failed";
    				}
			    }else{
			        $response['error'] = true;
    	            $response['message'] = "Notification Already Applied";
			    }
			    
	            
			}else{
    			$response['error'] = true;
				$response['message'] = "Required Fields";
			}
			$output = json_encode($response);
			// print_r(json_encode($res));

		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}
	//Output the output.
	echo $output;
	$db->disconnect(); 
?>