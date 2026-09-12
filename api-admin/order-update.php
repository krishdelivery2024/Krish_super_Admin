<?php
header('Access-Control-Allow-Origin: *');
include_once('../api-firebase/send-email.php');
//include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');

include_once('../api-dunzo/dunzo-api.php');
$dunzo= new dunzo();

$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$settings = $function->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
$support_email = $settings['support_email'];
$store_city = $settings['store_city'];
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

if(isset($_POST['id'])) {
	$id = $db->escapeString($function->xss_clean($_POST['id']));
	$postStatus = $db->escapeString($function->xss_clean($_POST['status']));
//	print_r($postStatus);
//	exit;
	if(isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''){
	    $delivery_boy_id = $db->escapeString($function->xss_clean($_POST['delivery_boy_id']));
	    $sql = "SELECT user_id,delivery_boy_id,status FROM `orders` where id=$id";
    	$db->sql($sql);	
	    $res_delivery_boy_id = $db->getResult();
	
        if( ($res_delivery_boy_id[0]['delivery_boy_id'] == 0) 
			|| ( $res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')){
			    
            $sql_get_name="select name,mobile from delivery_boys where id='$delivery_boy_id'";
    		$db->sql($sql_get_name);
    		$delivery_boy_name = $db->getResult();
    		
    		
    		if($delivery_boy_name[0]['name']=='dunzo'){
    		        
    		        $result_set=$dunzo->create_task_line($id);
    		        if(!empty($result_set)){
    		            if($result_set == 'retry'){
    		                $response['error'] = true;
                    		$response['message'] = 'Dunzo could not create order at the movement! Try Again.';
                    		print_r(json_encode($response));
                    		return false;
    		            }else{
    		                if(!empty($result_set->task_id)){
            		            $ref_id=$result_set->drop_ids;
            		            $db->sql("INSERT INTO dunzo_task(order_id,task_id,reference_id,status,created_at)values('$id','$result_set->task_id','$ref_id[0]','$result_set->state','".date('Y-m-d H:i:s')."')");
            		            
            		        }else{
            		            $response['error'] = true;
                        		$response['message'] = 'Dunzo could not create order at the movement! Try Again.';
                        		print_r(json_encode($response));
                        		return false;
            		        }
    		            }
    		        } 
    		}else{
    		    
    		    $message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
    		    $function->send_notification_to_delivery_boy($delivery_boy_id,"Your new order with ID : #$id has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
    		    $function->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order with ID : #$id has been ".ucwords($postStatus),$message_delivery_boy,'order_status');
            
    		}
    // 		$message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
    // 		$function->send_notification_to_delivery_boy($delivery_boy_id,"Your new order with ID : #$id has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
    //         $function->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order with ID : #$id  has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
			
			$sqlme = "select name,email,mobile,country_code from `users` where id=".$res_delivery_boy_id[0]['user_id'];
    		$db->sql($sqlme);
    		$res_useraa = $db->getResult();
    		if(!empty($res_useraa[0]['email'])){
    		    $to = $res_useraa[0]['email'];
    			$subject = "Delivery Person Assigned for Your Order";
    			$message= "Hello, Dear, Your Order Is assigned to delivery Person  ".ucwords($delivery_boy_name[0]['name']).". Here is your order ID : #".$id.". Contact Number For the Delivery Person is ".$delivery_boy_name[0]['mobile']."";
    			send_email($to,$subject,$message);
    		}
    		
		}
		$sql="UPDATE orders SET `delivery_boy_id`='".$delivery_boy_id."' WHERE id=".$id;
		$db->sql($sql);	
	}else{
	    $sql = "SELECT * FROM delivery_method WHERE id=1";
        $db->sql($sql);
        $resut = $db->getResult();  
        $in_persion_delivery =$resut[0]['in_persion_delivery'];
        $Delivery_by_courier =$resut[0]['Delivery_by_courier'];
        $storepickup =$resut[0]['storepickup'];
        $cdunzo =$resut[0]['dunzo'];
        $courier=false;
        if(!empty($in_persion_delivery) && !empty($Delivery_by_courier)){
            $sql = "SELECT delivery_city FROM `orders` where id=$id";
        	$db->sql($sql);	
    	    $res_order = $db->getResult();
            $order_city = $res_order[0]['delivery_city'];
            if($store_city != $order_city){
                $courier=true;
            }
        }elseif(empty($in_persion_delivery) && !empty($Delivery_by_courier)){
            $courier=true;
        }
	    if($postStatus == 'processed' && !empty($courier)){
	        $sql="UPDATE orders SET `courier`='1' WHERE id=".$id;
		    $db->sql($sql);
	    }
	}
	$sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id=".$id." && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if($res_cancelled[0]['cancelled']>0){
    	$response['error'] = true;
		$response['message'] = 'Could not update order status once cancelled or returned!';
		print_r(json_encode($response));
		return false;
	}
	

	$sql="select user_id,payment_method,payment_status, wallet_balance,total,delivery_charge,tax_amount,status,active_status,delivery_boy_id from orders where id=".$id;
	$db->sql($sql); // Table name, Column Names, JOIN, WHERE conditions, ORDER BY conditions
	$res = $db->getResult();
	if($res[0]['active_status']!='delivered' && $postStatus=='returned'){
	    $response['error'] = true;
		$response['message'] = 'Cannot return order unless it is delivered!';
		print_r(json_encode($response));
		return false;
	}
$sql = "SELECT SUM(sub_total) AS sub_total FROM order_items WHERE order_id=".$id;
$db->sql($sql);
$res_query = $db->getResult();

$sub_total = (float)$res_query[0]['sub_total'];
	$sql = "SELECT COUNT(id) as total FROM `orders` WHERE user_id=".$res[0]['user_id']." && status LIKE '%delivered%'";
	$db->sql($sql);
	$res_count = $db->getResult();
	$sql = "SELECT * FROM `users` WHERE id=".$res[0]['user_id'];
	$db->sql($sql);
	$res_user = $db->getResult();
    if(!empty($res)){
    	$status = json_decode($res[0]['status']);
    	$user_id =  $res[0]['user_id'];
    	foreach($status as $each){
    		if (in_array($postStatus, $each)) {
    			$response['error'] = true;
    			$response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != '' && ($res[0]['delivery_boy_id']!=0)?'Delivery Boy updated, Order already '.$postStatus:'Order already '.$postStatus;
    			print_r(json_encode($response));
    			return false;
    		}
    	}
    	if($postStatus=='cancelled' || $postStatus=='returned'){
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE `order_id`='.$id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	    for($i=0;$i<count($res_oi);$i++){
        	    if($res_oi[$i]['type']=='packet'){
        	        $sql = "UPDATE product_variant SET stock = stock + ".$res_oi[$i]['quantity']." WHERE id='".$res_oi[$i]['product_variant_id']."'";
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
        	        if($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']){
        	            $stock = $function->convert_to_parent($res_oi[$i]['measurement'],$res_oi[$i]['measurement_unit_id']);
        	            $stock = $stock * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
        	            //echo $sql;
        			    $db->sql($sql);
        	        }else{
        	            $stock = $res_oi[$i]['measurement'] * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
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
    	    }
    	     if(
    strtolower(trim($res[0]['payment_method'])) != 'cod' &&
    strtolower(trim($res[0]['payment_method'])) != 'cash on delivery'
){
    if($res[0]['wallet_balance']!=0 && $res[0]['payment_status'] =='0'){
        /* update user's wallet */
        $user_id = $res[0]['user_id'];
        $user_wallet_balance = $function->get_wallet_balance($user_id);
        $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
        $function->update_wallet_balance($new_balance,$user_id);

        /* add wallet transaction */
        $wallet_txn_id = $function->add_wallet_transaction(
            $user_id,
            'credit',
            $res[0]['wallet_balance'],
            'Balance credited against item cancellation.'
        );

    }else{
        /* update user's wallet */
        $user_id = $res[0]['user_id'];

        $total = $res[0]['total']
               + $res[0]['delivery_charge']
               + $res[0]['tax_amount'];

        $user_wallet_balance = $function->get_wallet_balance($user_id);
        $new_balance = $user_wallet_balance + $total;

        if($res[0]['payment_method']=='wallet' OR $res[0]['payment_status']==1){
            $function->update_wallet_balance($new_balance,$user_id);

            /* add wallet transaction */
            $wallet_txn_id = $function->add_wallet_transaction(
                $user_id,
                'credit',
                $total,
                'Balance credited against item cancellation.'
            );
        }
    }
}else{
    if($res[0]['wallet_balance']!=0){
        /* update user's wallet */
        $user_id = $res[0]['user_id'];
        $user_wallet_balance = $function->get_wallet_balance($user_id);
        $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
        $function->update_wallet_balance($new_balance,$user_id);

        /* add wallet transaction */
        $wallet_txn_id = $function->add_wallet_transaction(
            $user_id,
            'credit',
            $res[0]['wallet_balance'],
            'Balance credited against item cancellation.'
        );
    }
}
    	}
    	
    	if($postStatus=='delivered'){
    		$sql = "SELECT delivery_boy_id,final_total,total FROM orders WHERE id=".$id;
    		$db->sql($sql);
    		$res_boy = $db->getResult();
    		
    		//invoice auto generate-start
    		$sql_outer="SELECT oi.*,u.*,p.*,v.*,o.*,u.name as uname,d.name as delivery_boy,o.status as order_status,oi.active_status as order_item_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name FROM `order_items` oi JOIN users u ON u.id=oi.user_id JOIN product_variant v ON oi.product_variant_id=v.id JOIN products p ON p.id=v.product_id JOIN orders o ON o.id=oi.order_id LEFT JOIN delivery_boys d ON o.delivery_boy_id=d.id WHERE o.id=".$id;
    $db->sql($sql_outer);
    // store result 
    $res_outer=$db->getResult();
     
      $items=[];
    foreach($res_outer as $row){
            $data=array($row['product_id'],$row['pname'],$row['quantity'],$row['measurement'],$row['mesurement_unit_name'],$row['discounted_price']*$row['quantity'],$row['discount'],$row['sub_total'],$row['order_item_status']);
            array_push($items, $data);
        }
         // print_r($items);
        $encoded_items=$db->escapeString(json_encode($items));
$id = $res_outer[0]['id'];
$sql = "SELECT COUNT(id) as total FROM `invoice` where order_id=".$id;
$db->sql($sql);
$res_total=$db->getResult();
$total=$res_total[0]['total'];
if ($total == 0) {

    $invoicedate = date('Y-m-d');
    $id = $res_outer[0]['id'];
    $name=$res_outer[0]['uname'];
    $email=$res_outer[0]['email'];
    $address = $res_outer[0]['address'];
    $phone = $res_outer[0]['mobile'];
    $orderdate = $res_outer[0]['date_added'];
    $order_list = $encoded_items;
    $discount = $res_outer[0]['discount'];
    $final_total=$res_outer[0]['final_total'];
    $total_payble = $res_outer[0]['price'];
    $shipping_charge=$res_outer[0]['delivery_charge'];
    $payment = $res_outer[0]['final_total'];
    $data = array(
        'invoice_date' => $invoicedate,
        'order_id' => $id,
        'name' => $name,
        'address' => $address,
        'order_date' => $orderdate,
        'phone_number' => $phone,
        'order_list' => $encoded_items,
        'email' => $email,
        'discount' => $discount,
        'total_sale' => $total_payble,
        'shipping_charge' => $shipping_charge,
        'payment' => $payment,
    );
    // print_r($data);
    $db->insert('invoice',$data);
    $subject = "Order Delivered Successfully. Your invoice is here";
    
    $sql_invoice = "SELECT * FROM invoice WHERE order_id =" . $id;

    // Execute query
    $db->sql($sql_invoice);
    // store result 
    $res_invoice=$db->getResult();
			$message = "<div><h2 style='text-align:left;float:left;'>".$settings['app_name']."</h2><h2 style='text-align:right;float:right;'>Mo. +91 ".$settings['support_number']."</h2><hr style='clear:both;'/></div>";
			$message .= "<div style='width:33%;display:inline-block;text-align:left;'>From<address><strong>".$settings['app_name']."</strong><br>Email: ".$settings['support_email']."<br>Customer Care : +91 ".$settings['support_number']."<br>Delivery By: &nbsp; ".$res_outer[0]['delivery_boy']."</address><br></div><!-- /.col -->";
			$message .="<div  style='width:33%;display:inline-block;'><br>To<address><strong>".$res_outer[0]['uname']."</strong><br>".$res_outer[0]['address']."<br><strong>".$res_outer[0]['mobile']."</strong><br><strong>".$res_outer[0]['email']."</strong><br></address></div>";
			$message .="<div style='width:33%;display:inline-block;text-align:right;'>Retail Invoice<br><b>No : </b>#".$res_invoice[0]['id']."<br><b>Date: </b>".date('d-m-Y',strtotime($res_invoice[0]['invoice_date']))."<br><b>Order ID: </b>#".$res_outer[0]['id']."<br><b>Date: </b>".date('d-m-Y h:i A',strtotime($res_outer[0]['date_added']))."<br><br></div>";
			$message .="<div style='margin:5px'></div><hr>";
			$decoded_items=json_decode(stripSlashes($encoded_items));
			$message .="<table border-bottom='1' style=' line-height:20px;width:100% !important ' width:'500'; align='center'  cellpadding='5' cellspacing='1'>
					<thead><tr><th width='10%'>Sr No.</th><th width='15%'>Product Code</th><th width='30%'>Product Name</th><th width='15%'>Unit</th><th width='15%'>Qty</th><th align='right' width='15%'>SubTotal (₹)</th></tr></thead>";
					$qty = 0;
                    $i=1;
                    $total=0;
                    foreach ($decoded_items as $item) {
                        if($item[8]!='cancelled' && $item[8]!='returned'){
                           $message .= "<tr><td width='10%'  align='center' >".$i."</td>
                    					<td width='15%' align='center'>".$item[0]."</td>
                    					<td width='30%' align='center'>".$item[1]."</td>
                    					<td width='15%' align='center'>".$item[3]." ".$item[4]."</td>
                    					<td width='15%' align='center'>".$item[2]."</td>
                    					<td width='15%' align='right'>".$item[7]."</td></tr>";
        					$qty = $qty+$item[2];
                            $i++;
                            $total+=$item[7];
                        }
                    }
                        $sql_total = 'select total from orders where id='.$id;
                                        $db->sql($sql_total);
                                        $res_total = $db->getResult();                        
					
					
					 $message .= "<tr style='line-height:50px;padding-bottom: 1em;'>
                                            <td style='font-weight:bold' colspan='4' align='right'>Total</td>
                                           <td align='center'>".$qty."</td>
                                           <td align='right'>".$res_total[0]['total']."</td>
                                        </tr>";
                                        if($res_outer[0]['discount']>0){
                                            $discounted_amount = $res_total[0]['total'] * $res_outer[0]['discount'] / 100; /*  */
                                    	    $final_total = $res_total[0]['total'] - $discounted_amount;
                                            $discount_in_rupees = $res_total[0]['total']-$final_total;
                                            $discount_in_rupees = $discount_in_rupees;
                                            // echo $discount_in_rupees;
                                        } else {
                                            $discount_in_rupees = 0;
                                        }
                                      $message .= "<tr style='padding-top:30px;border-spacing: 15px;margin-top:30px'>
                            <td style='font-weight:bold' colspan='4' align='right'>Total Order Price (₹)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;+ ".$res_total[0]['total']."</td>
                        </tr>
                        <tr>
                            <td style='font-weight:bold' colspan='4' align='right'>Delivery Charge (₹)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;+ ".$res_outer[0]['delivery_charge']."</td>
                        </tr>
                        <tr>
                            <td style='font-weight:bold' colspan='4' align='right'>Tax ₹(%)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;+ ".$res_outer[0]['tax_amount']." (".$res_outer[0]['tax_percentage']."%)</td>
                        </tr>
                        <tr>
                            <td style='font-weight:bold' colspan='4' align='right'>Discount ₹(%)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;- ".$discount_in_rupees." (".$res_outer[0]['discount']."%)</td>
                        </tr>
                        <tr>
                            <td style='font-weight:bold' colspan='4' align='right'>Promo (-) Discount (₹)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;- ".$res_outer[0]['promo_discount']."</td>
                        </tr>
                        <tr>
                            <td style='font-weight:bold' colspan='4' align='right'>Wallet Used (₹)</td>
                            <td align='right'></td>
                            <td align='right'>&nbsp;- ".$res_outer[0]['wallet_balance']."</td>
                        </tr>";
                         $total = $res_total[0]['total'];
                            $delivery_charge = $res_outer[0]['delivery_charge'];
                            $tax_amount = $res_outer[0]['tax_amount'];
                            $promo_discount = $res_outer[0]['promo_discount'];
                            $wallet = $res_outer[0]['wallet_balance'];
                            $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                        $message .= "<tr><td style='font-weight:bold' colspan='4' align='right'>Final Total (₹)</td>
                        <td align='right'></td>
                                                <td align='right'>&nbsp;= ".($final_total)."</td>
                        </tr></table>";
			send_email($email,$subject,$message);
}
    //invoice auto generate-end	
    		
    		if($res_boy[0]['delivery_boy_id']!=0){
    			$sql = "SELECT bonus,name FROM delivery_boys WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$res_bonus = $db->getResult();
    			$reward = $res_boy[0]['total']/100*$res_bonus[0]['bonus'];
    	    	$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$db->getResult();
    			$comission = $function->add_delivery_boy_commission($delivery_boy_id,'credit',$reward,'Order Delivery Commission.');
    			
    			$sql = "SELECT value FROM `settings` WHERE variable='currency'";
    			$db->sql($sql);
    			$currency = $db->getResult();
    		    $message_delivery_boy = "Hello, Dear ".ucwords($res_bonus[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your Commission of".$reward." is credited. Please take a note of it.";
    			$function->send_notification_to_delivery_boy($res_boy[0]['delivery_boy_id'],"Your commission ".$reward." ".$currency[0]['value']." has been credited","$message_delivery_boy",'delivery_boys',$id);
    			$function->store_delivery_boy_notification($res_boy[0]['delivery_boy_id'],$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');
    		}
    		if($config['is-refer-earn-on']==1){
    			if($res_boy[0]['total']>=$config['min-refer-earn-order-amount']){
    				if($res_count[0]['total']==0){
    					if($res_user[0]['friends_code'] != ''){
    						if($config['refer-earn-method']=='percentage'){
    							$percentage = $config['refer-earn-bonus'];
    							$bonus_amount = $res_boy[0]['total']/100*$percentage;
    							if($bonus_amount>$config['max-refer-earn-amount']){
    								$bonus_amount = $config['max-refer-earn-amount'];
    							}
    						}else{
    							$bonus_amount = $config['refer-earn-bonus'];
    						}
    						$sql  = "SELECT name,friends_code FROM users WHERE id=".$res[0]['user_id'];
    						$db->sql($sql);
    						$res_data = $db->getResult();
    						
    						$sql = " select id from `users` where `referral_code` = '".$res_data[0]['friends_code']."'";
    						$db->sql($sql);
    						$friend_user = $db->getResult();
    						
    						if(!empty($friend_user))
    						    $function->add_wallet_transaction($friend_user[0]['id'],'credit',floor($bonus_amount),'Refer & Earn Bonus on first order by '.ucwords($res_data[0]['name']));
    						
    						$sql = "UPDATE users SET balance = balance + floor($bonus_amount) WHERE referral_code='".$res_data[0]['friends_code']."'";
    						$db->sql($sql);
    						
    					}
    
    				}
    
    			}
    		}
    	}
    	$temp=[];
    	foreach($status as $s){
    	    array_push($temp,$s[0]);
    	}
    	$sql = "SELECT id,active_status FROM order_items WHERE order_id=".$id;
        $db->sql($sql);
        $result = $db->getResult();
    	if($postStatus=='cancelled'){
    
    	    if (!in_array('cancelled', $temp)) {
    	        $status[] = array('cancelled',date("d-m-Y h:i:sa") );
    	            $data = array(
    	            'status' => $db->escapeString(json_encode($status)),
    	        );
    	    }
    	    $db->update('orders',$data,'id='.$id);
    
    	    foreach($result as $item){
    	        if($item['active_status'] != 'cancelled'){
    	            $item_data = array(
    	            'status' => $db->escapeString(json_encode($status)),
        	        'active_status' => 'cancelled'
    	            );
    	        $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	
    	if($postStatus=='processed'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array(
    	            'status' => $db->escapeString(json_encode($status))
    	       );
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    foreach($result as $item){
    	        $item_data = array(
    	            'status' => $db->escapeString(json_encode($status)),
        	        'active_status' => 'processed'
    	            );
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	if($postStatus=='shipped'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    if (!in_array('shipped', $temp)) {
    	        $status[] = array('shipped',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    foreach($result as $item){
    	        $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
    	        'active_status' => 'shipped'
    	            );
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    }
    	}
    	if($postStatus=='delivered'){
    	    if (!in_array('processed', $temp)) {
    	        $status[] = array('processed',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    }
    	    if (!in_array('shipped', $temp)) {
    	        $status[] = array('shipped',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    
    	    }
    	    if (!in_array('delivered', $temp)) {
    	        $status[] = array('delivered',date("d-m-Y h:i:sa") );
    	        $data = array('status' => $db->escapeString(json_encode($status)));
    	    
    	    }
    	    $db->update('orders',$data,'id='.$id);
        	 $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'delivered'
             );
             
    	    foreach($result as $item){
    	        
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    } 
    	}
    	if($postStatus=='returned'){
             $status[] = array('returned',date("d-m-Y h:i:sa") );
             $data = array('status' => $db->escapeString(json_encode($status)));
    	     $db->update('orders',$data,'id='.$id);
        	 $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'returned'
             );
    	    foreach($result as $item){
    	        
    	        if($item['active_status'] != 'cancelled' && $item['active_status']=='delivered'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	        }
    	    } 
    	}
    	$i = sizeof($status);
        $currentStatus = $status[$i-1][0];
        $final_status = array(
        	'active_status' => $currentStatus
    	);
    	//$db->update('order_items',$final_status,'order_id='.$id);
     	if($db->update('orders',$final_status,'id='.$id)){// Table name, column names and respective values
    		$response['error'] = false;
    		if($postStatus=='cancelled'){$response['message'] = "Order has been cancelled!";}
    		elseif($postStatus=='returned'){$response['message'] = "Order has been returned!";}
    		else{$response['message'] = "Order updated successfully.";}
    		    
    		$res = $db->getResult();
    		/* send email notification for the order received */
    		$sql = "select name,email,mobile,country_code from `users` where id=".$user_id;
    // 		echo $sql;
    		$db->sql($sql);
    		$res_user = $db->getResult();
    		
    		$to = $res_user[0]['email'];
    		$mobile = $res_user[0]['mobile'];
    		$country_code = $res_user[0]['country_code'];
    		$subject = "Your order has been ".ucwords($postStatus);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
    		$function->send_order_update_notification($user_id,"Your order has been ".ucwords($postStatus),$message,'order');
    		send_email($to,$subject,$message);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services! Contact us for more information";
    		// sendSm  // Notify delivery boy about status update if assigned
			
            if ($res[0]['delivery_boy_id'] != 0 && (!isset($_POST['delivery_boy_id']) || empty($_POST['delivery_boy_id']))) {
                $sql_dboy = "select name from delivery_boys where id='".$res[0]['delivery_boy_id']."'";
                $db->sql($sql_dboy);
                $res_dboy = $db->getResult();
                if (!empty($res_dboy)) {
                    $message_delivery_boy = "Hello, Dear ".ucwords($res_dboy[0]['name']).", Here is the new update on your assigned order ID : #".$id.". The order status has been updated to ".ucwords($postStatus).".";
                    $function->send_notification_to_delivery_boy($res[0]['delivery_boy_id'],"Order Status Updated to ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id);
                    $function->store_delivery_boy_notification($res[0]['delivery_boy_id'],$id,"Order Status Updated to ".ucwords($postStatus),$message_delivery_boy,'order_status');
                }
            } else if ($res[0]['delivery_boy_id'] == 0 && $postStatus == 'processed') {
                $message_delivery_boy = "Hello, A new order (ID : #".$id.") is ready for delivery. Please check your app to accept it.";
                $function->send_notification_to_delivery_boy(0,"New Order Ready for Delivery",$message_delivery_boy,'delivery_boys',$id);
                $function->store_delivery_boy_notification(0,$id,"New Order Ready for Delivery",$message_delivery_boy,'order_status');
            }
// sendSms($mobile,$message,$country_code);

    	//	echo ucwords($subject);
     	//	exit;
    		print_r(json_encode($response));
    	} else {
    		$response['error'] = true;
    		$response['message'] = isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''?'Delivery Boy updated, But could not update order status try again!':'Could not update order status try again!';
    		print_r(json_encode($response));
    	}
    }else{
		$response['error'] = true;
		$response['message'] = "Sorry Invalid order ID";
		print_r(json_encode($response));
	}
}

?>