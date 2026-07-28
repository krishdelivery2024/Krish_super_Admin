<?php
header('Access-Control-Allow-Origin: *');
include_once('../api-firebase/send-email.php');
include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$settings = $function->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
$support_email = $settings['support_email'];
	$config = $function->get_configurations();
		if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
			date_default_timezone_set($config['system_timezone']);
			$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
		}else{
	date_default_timezone_set('Asia/Kolkata');
	$db->sql("SET `time_zone` = '+05:30'");
}
 
$response = array();


if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
   // $function->send_order_update_notification($res[0]['id'],"Your order has been ","Delivery Boy Login Susseccfully",'delivery_boys');
	$accesskey="90336";	
}else{
	if(isset($_POST['accesskey']) && $_POST['accesskey'] != ''){
		$accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));
	}else{
		$response['error']= true;
		$response['message']="accesskey required";
		print_r(json_encode($response));
		return false;
	}
	
}

if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}

if(isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
    if(!verify_token()){
        return false;
    }
	$order_item_id = $db->escapeString($function->xss_clean($_POST['order_item_id']));
	$order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
	$postStatus = $db->escapeString($function->xss_clean($_POST['status']));
	
	$sql = "SELECT COUNT(id) as cancelled FROM `order_items` WHERE id=".$order_item_id." && status LIKE '%$postStatus%'";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if($res_cancelled[0]['cancelled']>0){
    	$response['error'] = true;
		$response['message'] = 'Could not update order status. Item is already '.ucwords($postStatus).'!';
		print_r(json_encode($response));
		return false;
	}
	
	$sql = "SELECT user_id,status,active_status,sub_total FROM order_items WHERE id =".$order_item_id;
	$db->sql($sql);
	$result=$db->getResult();
	
    if(!empty($result)){
    	$status = json_decode($result[0]['status']);
    	$active_status=$result[0]['active_status'];
    	if($active_status=='shipped' || $active_status=='delivered'){
    	    $response['error'] = true;
        	$response['message'] = 'Order cancel Not Allowed After Shipped';
        	$response['subtotal'] = 0;
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'cancelled'){
    	    $sql = 'SELECT final_total,total,user_id,payment_method,payment_status,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id='.$order_id;
    	    $db->sql($sql);
    	    $res_order = $db->getResult();
    	   // print_r($res_order[0]['total']);
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`='.$order_item_id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	    $price = $res_oi[0]['discounted_price']==0?$res_oi[0]['price']:$res_oi[0]['discounted_price'];
    	    $price = $price*$res_oi[0]['quantity'];
    	    $total = $res_order[0]['total'];
    	    $final_total = $res_order[0]['final_total'];
    	    $delivery_charge = $res_order[0]['delivery_charge'];
    	   // echo $total - $price;
    	    if($total - $price >= 0){
    	        $sql_total = "update orders set total=$total-$price where id=".$order_id;
    	        $db->sql($sql_total);
    	    }
    	    $sql = "select total from orders where id=".$order_id;
    	    $db->sql($sql);
    	    $res_total=$db->getResult();
    	    $total = $res_total[0]['total'];
    	    
    	    if($total<$config['min_amount']){
    	        if($delivery_charge==0){
    	            $dchrg = $config['delivery_charge'];
    	            $sql_delivery_chrg = "update orders set delivery_charge=$dchrg where id=".$order_id;
            	   // echo $sql_delivery_chrg;
            	    $db->sql($sql_delivery_chrg);
            	    $sql_final_total = "update orders set final_total=$final_total-$price+$dchrg where id=".$order_id;
    	        }else{
    	            $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
    	        }
    	        $db->sql($sql_final_total);
   
	        }else{
	            $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
	        }
	        $db->sql($sql_final_total);
	        if($total==0){
    	        $sql = "update orders set delivery_charge=0,tax_amount=0,tax_percentage=0,final_total=0 where id=".$order_id;
    	        $db->sql($sql);
    	    }



    	    if($res_oi[0]['type']=='packet'){
    	        $sql = "UPDATE product_variant SET stock = stock + ".$res_oi[0]['quantity']." WHERE id='".$res_oi[0]['product_variant_id']."'";
    			$db->sql($sql);
    			
    			$sql = "select stock from product_variant where id=".$res_oi[0]['product_variant_id'];
    			$db->sql($sql);
    			$res_stock = $db->getResult();
    			if($res_stock[0]['stock']>0){
        			$sql = "UPDATE product_variant set serve_for='Available' WHERE id='".$res_oi[0]['product_variant_id']."'";
        			$db->sql($sql);
    			}
    			    
    	    }else{
    	        /* When product type is loose */
    	        if($res_oi[0]['measurement_unit_id'] != $res_oi[0]['stock_unit_id']){
    	            $stock = $function->convert_to_parent($res_oi[0]['measurement'],$res_oi[0]['measurement_unit_id']);
    	            $stock = $stock * $res_oi[0]['quantity'];
    	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[0]['product_id']."'";
    			    $db->sql($sql);
    	        }else{
    	            $stock = $res_oi[0]['measurement'] * $res_oi[0]['quantity'];
    	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[0]['product_id']."'";
    			    $db->sql($sql);
    	        }
    	        $sql = "select stock from product_variant where product_id=".$res_oi[0]['product_id'];
                $db->sql($sql);
                $res_stck= $db->getResult();
                if($res_stck[0]['stock']>0){
                    $sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='".$res_oi[0]['product_id']."'";
        			$db->sql($sql);
                }
    	    }
        	$status[] = array($postStatus,date("d-m-Y h:i:sa") );
            $currentStatus = $postStatus;
            $data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => $currentStatus
            );
            $db->update('order_items',$data,'id='.$order_item_id);
            $db->getResult();
            
        	$sql = "SELECT id FROM order_items WHERE order_id=".$order_id;
        	$db->sql($sql);
        	$total = $db->numRows();
        	$sql = "SELECT id FROM `order_items` WHERE order_id=".$order_id." && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
        	$db->sql($sql);
        	$cancelled = $db->numRows();
        	if($cancelled==$total){
        	   // print_r($res_order);
        	    if($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] !='COD'){
                	/* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $res_order[0]['total']+$res_order[0]['delivery_charge']+$res_order[0]['tax_amount'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    // return false;
                    if($res_order[0]['payment_method']=='wallet' OR $res_order[0]['payment_status']==1){
                        $function->update_wallet_balance($new_balance,$user_id);
                        /* add wallet transaction */
                	    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$total_amount,'Balance credited against item cancellation.');
                    }
                        
                }else{
                if($res_order[0]['wallet_balance']!=0){
                    /* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    // $total = $res[0]['total'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                    // echo $new_balance;
                    $function->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$res_order[0]['wallet_balance'],'Balance credited against item cancellation.');
                }
                    
           }
        	    
            	$data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => $currentStatus
        	        );
                $db->update('orders',$data_order,'id='.$order_id);
        	}else{
        	    $user_id = $res_order[0]['user_id'];
        	    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $result[0]['sub_total']);
                    // echo $new_balance." - ".$user_id;die;
                    if($res_order[0]['payment_method']=='wallet' OR $res_order[0]['payment_status']==1){
                        $function->update_wallet_balance($new_balance,$user_id);
            	        /* add wallet transaction */
            		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$result[0]['sub_total'],'Balance credited against item cancellation.');
                    }
                    
                    }
		    
        	$response['error'] = false;
        	$response['message'] = 'Order cancelled successfully!';
        	$response['subtotal'] = $result[0]['sub_total'];
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'returned'){
    	    $is_item_delivered = 0;
    	    foreach($status as $each_status){
        		if (in_array('delivered', $each_status)) {
        			$is_item_delivered = 1;
        			$config['max-product-return-days'];
        			$now = time(); // or your date as well
                    $status_date = strtotime($each_status[1]);
                    $datediff = $now - $status_date;
                    
                    $no_of_days = round($datediff / (60 * 60 * 24));
                    if($no_of_days > $config['max-product-return-days']){
                        $response['error'] = true;
            			$response['message'] = 'Oops! Sorry you cannot return the item now. You have crossed product\'s maximum return period';
            			print_r(json_encode($response));
            			return false;
                    }
        		}
        	}
        	if(!$is_item_delivered){
        	    $response['error'] = true;
    			$response['message'] = 'Cannot return item unless it is delivered!';
    			print_r(json_encode($response));
    			return false;
        	}
        	/* store return request */
        	$function->store_return_request($result[0]['user_id'],$order_id,$order_item_id);
        	/* if delivered take the item as returned */
        	$status[] = array($postStatus,date("d-m-Y h:i:sa") );
            $data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => $postStatus
            );
            $db->update('order_items',$data,'id='.$order_item_id);

		    /* check for other item status and summery of order */
		    $sql = "SELECT id FROM order_items WHERE order_id=".$order_id;
        	$db->sql($sql);
        	$total = $db->numRows();
        	$sql = "SELECT id FROM `order_items` WHERE order_id=".$order_id." && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
        	$db->sql($sql);
        	$returned = $db->numRows();
        	if($returned == $total){
        	    $sql = "SELECT status FROM orders WHERE id =".$order_id;
            	$db->sql($sql);
            	$res = $db->getResult();
            	$status_order=json_decode($res[0]['status']);
            	$status_order[] = array($postStatus,date("d-m-Y h:i:sa") );
            	$data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => $postStatus
            	);
                $db->update('orders',$data_order,'id='.$order_id);
        	}
        	$response['error'] = false;
        	$response['message'] = 'Order item returned request received successfully! Amount will credited to your wallet once approved.';
        	$response['subtotal'] = $result[0]['sub_total'];
        	print_r(json_encode($response));
        	return false;
    	}
    }else{
	    $response['error'] = true;
    	$response['message'] = 'Order item not found!';
    	print_r(json_encode($response));
    	return false;
	}
}

?>