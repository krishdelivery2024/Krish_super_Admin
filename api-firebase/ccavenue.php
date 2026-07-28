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
	include('ccavenue/Crypto.php');
	
	/* accesskey:90336 
		city_id:24 */
	if(!verify_token()){
		return false;
	}

	if(isset($_POST['accesskey'])) {
	    
		$access_key_received = isset($_POST['accesskey']) && !empty($_POST['accesskey'])? $db->escapeString($fn->xss_clean($_POST['accesskey'])):'';
		if($access_key_received == $access_key){
			$data = $fn->get_settings('payment_methods',true);
			
			
		   
            if($data['ccavenue_payment_method'] ){
		        $amount = $db->escapeString($fn->xss_clean($_POST['amount']));
		        $order_id = !empty($_POST['order_id'])? $db->escapeString($fn->xss_clean($_POST['order_id'])):rand(100,1000000);
		        
		         //billing details
		         
		        $billing_name = !empty($_POST['name'])? $db->escapeString($fn->xss_clean($_POST['name'])):'';
		        $billing_address = !empty($_POST['address'])? $db->escapeString($fn->xss_clean($_POST['address'])):'';
		        $billing_zip = !empty($_POST['pincode'])? $db->escapeString($fn->xss_clean($_POST['pincode'])):'';
		        $billing_city = !empty($_POST['city'])? $db->escapeString($fn->xss_clean($_POST['city'])):'';
		        $billing_state = !empty($_POST['state'])? $db->escapeString($fn->xss_clean($_POST['state'])):'';
		        $billing_country = !empty($_POST['country'])? $db->escapeString($fn->xss_clean($_POST['country'])):'';
		        $billing_tel = !empty($_POST['mobile'])? $db->escapeString($fn->xss_clean($_POST['mobile'])):'';
		        $billing_email = !empty($_POST['email'])? $db->escapeString($fn->xss_clean($_POST['email'])):'';
		        $request_from = !empty($_POST['name'])? $db->escapeString($fn->xss_clean($_POST['request_from'])):'';
		        
		        if($request_from=='ios'){
		            $response_handler='ccaviosResponseHandler.php';
		        }else if($request_from=='android'){
		             $response_handler='ccavandroidResponseHandler.php';
		        }else{
		             $response_handler='ccavResponseHandler.php';
		        }
             
                $url='https://spiderekart.in/india_demo_new/api-firebase/ccavenue/'.$response_handler;
             
             
		        $plainText='merchant_id='.$data['ccavenue_merchant_id'].'&order_id='.$order_id.'&redirect_url='.$url.'&cancel_url='.$url.'&amount='.$amount.'&currency='.$data['ccavenue_currency'].'&billing_name='.$billing_name.'&billing_address='.$billing_address.'&billing_zip='.$billing_zip.'&billing_city='.$billing_city.'&billing_state='.$billing_state.'&billing_country='.$billing_country.'&billing_tel='.$billing_tel.'&billing_email='.$billing_email;
 		       
		        $res['enc_val']=encrypt($plainText,$data['ccavenue_working_key']);
		        $res['ccavenue_access_code']=$data['ccavenue_access_code'];
			// // create json output
			if(!empty($res)){
				$response['error'] = false;
				$response['data'] = $res;
			}else{
				$response['error'] = true;
				$response['message'] = "No data found!";
			}
			$output = json_encode($response);
			// print_r(json_encode($res));
            }
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