<?php
header('Access-Control-Allow-Origin: *');
include_once('send-email2.php');
include_once('send-sms.php');
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/functions.php');
include_once('../includes/variables.php');
include_once('verify-token.php');
include_once('../includes/fcm-helper.php');
 
include_once('../api-dunzo/dunzo-api.php');
$dunzo= new dunzo();
 
$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();
$fn = new functions();
$settings = $function->get_settings('system_timezone',true);
$app_name = $settings['app_name'];
$support_email = $settings['support_email'];
	$config = $function->get_configurations();
		if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
			date_default_timezone_set($config['system_timezone']);
		//	$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
		}else{
	date_default_timezone_set('Asia/Kolkata');
	$db->sql("SET `time_zone` = '+05:30'");
}
/*place_order:1
 user_id:5
 mobile:9974692496
 product_variant_id:["1","2","3"]
 quantity:["3","3","1"]
 total:60.0
 delivery_charge:20.0
 tax_amount:10
 tax_percentage:10
 discount:10
 final_total:55
 latitude:40.1451
 longitude:-45.4545
 promo_code:NEW20 {optional}
 promo_discount:20 {optional}
 payment_method:PAYTM
 address:bhuj
 delivery_time:Today - Evening (4:00pm to 7:00pm)
 loyalty_Points=10
 status:[["received","11-06-2019 12:22:44pm"],["processed","13-06-2019 11:36:49am"],["shipped","13-06-2019 12:07:29pm"],["delivered","13-06-2019 01:57:00pm"]]
 */
 
$response = array();
 
 
if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
    $request_type = 'webrequest';
	$accesskey="90336";	
}else{
    $request_type = 'apprequest';
	if(isset($_POST['accesskey']) && $_POST['accesskey'] != ''){
		$accesskey = $db->escapeString($function->xss_clean($_POST['accesskey']));
	}else{
		// $response['error']= true;
		// $response['message']="accesskey required";
		// print_r(json_encode($response));
		// return false;
	}
	
}
 
if($access_key != $accesskey){
	$response['error']= true;
	$response['message']="invalid accesskey";
	print_r(json_encode($response));
	return false;
}
// if(!isset($_POST['ajaxCall']) && empty($_POST['ajaxCall'])){
// 	if(!verify_token()){
//         return false;
//     }
// }
$myfile = fopen("logs.txt", "a") or die("Unable to open file!");
$txt = json_encode($_POST);
fwrite($myfile, "\n". $txt);
fclose($myfile);
 
// if(isset($_POST['testing'])){
//     $function->send_order_confirmation(1);
// }
 
 
if(isset($_POST['place_order']) && isset($_POST['user_id']) && !empty($_POST['product_variant_id'])){
    if(!verify_token()){
        return false;
    }
	// echo "test";
 
    // $user_name = $db->escapeString($_POST['user_name']);
	$user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
	$seller_id = $db->escapeString($function->xss_clean($_POST['seller_id']));
	$mobile = $db->escapeString($function->xss_clean($_POST['mobile']));
	$name = $db->escapeString($function->xss_clean($_POST['name']));
	$email = $db->escapeString($function->xss_clean($_POST['email']));
    //	$store_id = $db->escapeString($function->xss_clean($_POST['store_id']));
	$wallet_balance = (isset($_POST['wallet_balance']) && is_numeric($_POST['wallet_balance']))?$db->escapeString($function->xss_clean($_POST['wallet_balance'])):0;
	$wallet_used = (isset($_POST['wallet_used']) && $function->xss_clean($_POST['wallet_used']) == 'true')?'true':'false';
	$items = $db->escapeString(stripslashes($function->xss_clean($_POST['product_variant_id'])));
	$total = $db->escapeString($function->xss_clean($_POST['total']));
	$delivery_method = $db->escapeString($function->xss_clean($_POST['delivery_method']));
	$delivery_charge = $db->escapeString($function->xss_clean($_POST['delivery_charge']));
	$discount = (isset($_POST['discount']))?$db->escapeString($function->xss_clean($_POST['discount'])):0;
	//$tax_percentage = (isset($_POST['tax_percentage']) && is_numeric($_POST['tax_percentage']))?$db->escapeString($function->xss_clean($_POST['tax_percentage'])):'';
	$tax_amount = (isset($_POST['tax_amount']) && is_numeric($_POST['tax_amount']))?$db->escapeString($function->xss_clean($_POST['tax_amount'])):'';
	$wallet_balance = (isset($_POST['wallet_balance']) && is_numeric($_POST['wallet_balance']))?$db->escapeString($function->xss_clean($_POST['wallet_balance'])):0;
	$final_total = $db->escapeString($function->xss_clean($_POST['final_total']));
	$payment_method = $db->escapeString($function->xss_clean($_POST['payment_method']));
	$address = $db->escapeString($function->xss_clean($_POST['address']));
	$delivery_city = (isset($_POST['delivery_city']) && is_numeric($_POST['delivery_city']))?$db->escapeString($function->xss_clean($_POST['delivery_city'])):'';
    $delivery_state = $db->escapeString($function->xss_clean($_POST['delivery_state']));
    $delivery_zone = (isset($_POST['delivery_zone']) && is_numeric($_POST['delivery_zone']))?$db->escapeString($function->xss_clean($_POST['delivery_zone'])):'';
	$gst_no = (isset($_POST['gst_no']))?$db->escapeString($function->xss_clean($_POST['gst_no'])):"";
	$delivery_time = (isset($_POST['delivery_time']))?$db->escapeString($function->xss_clean($_POST['delivery_time'])):"";
	$latitude = $db->escapeString($function->xss_clean($_POST['latitude']));
	$longitude = $db->escapeString($function->xss_clean($_POST['longitude']));
	$promo_code = (isset($_POST['promo_code'])&& !empty($_POST['promo_code']) )?$db->escapeString($function->xss_clean($_POST['promo_code'])):"-";
	$promo_discount = (isset($_POST['promo_discount'])&& !empty($_POST['promo_discount']) )?$db->escapeString($function->xss_clean($_POST['promo_discount'])):0;
	$status[] = array( 'received',date("d-m-Y h:i:sa") );
	$quantity_arr=json_decode($function->xss_clean($_POST['quantity']),1);
	$notes 	= (isset($_POST['notes']))?$db->escapeString($_POST['notes']):"";
	
	$item_details=$function->get_product_by_variant_id($items);
	if($delivery_charge == 'Free'){
	    $delivery_charge=0;
	}
	$total_amount=$total+$delivery_charge-$discount;
	
	if (isset($_POST['promo_code']) && $_POST['promo_code'] != '') {
	    $promo_code = $db->escapeString($function->xss_clean($_POST['promo_code']));
	    $response=$function->validate_promo_code($user_id,$promo_code,$total,$seller_id);
	    if($response['error']==true){
			echo json_encode($response);
			exit();
		}
	    
	}
	
	$user_wallet_balance = $function->get_wallet_balance($user_id);
	
	if($user_wallet_balance >= $wallet_balance && $user_wallet_balance != 0 && $wallet_used=='true'){
	    
	   // $new_balance = ($user_wallet_balance - $final_total);
	    $new_balance = $total_amount>$user_wallet_balance?0:$user_wallet_balance - $total_amount;
	    $function->update_wallet_balance($new_balance,$user_id);
		$wallet_txn_id = $function->add_wallet_transaction($user_id,'debit',$wallet_balance,'Used against Order Placement');
	}else{
	    $wallet_used = false;
	}
	
	
	$data = array(
		'user_id'=>$user_id,
		'seller_id'=>$seller_id,
		'mobile'=>$mobile,
		'name'=>$name,
		'email'=>$email,
		'delivery_method'=>$delivery_method,
		'delivery_charge'=>$delivery_charge,
		'wallet_balance' => ($wallet_used)?$wallet_balance:0,
		'total' => $total,
		//'tax_percentage' => $tax_percentage,
		'tax_amount' => $tax_amount,
		'final_total' => $final_total,
		'payment_method'=>$payment_method,
		'payment_status'=>1,
		'address'=>$address,
		'delivery_city'=>$delivery_city,
        'delivery_state'=>$delivery_state,
        'delivery_zone'=>$delivery_zone,
		'gst_no'=>$gst_no,
		'delivery_time'=>$delivery_time,
		'status' => $db->escapeString(json_encode($status)),
		'latitude' => $latitude,
		'longitude' => $longitude,
		'promo_code' => $promo_code,
		'promo_discount' => $promo_discount,
		'discount' => $discount,
		'notes' => $notes,
		'active_status' => 'received',
		'date_added' => date("Y-m-d H:i:s")
	);
	$db->insert('orders',$data);
	$order_id = $db->getResult()[0];
	
	$sub_total = 0;
	
	for($i=0;$i<count($item_details);$i++){
		$product_id = $item_details[$i]['product_id'];
 
		$measurement = $item_details[$i]['measurement'];
		$product_variant_id = $item_details[$i]['id'];
	
		$measurement_unit_id = $item_details[$i]['measurement_unit_id'];
		$stock_unit_id = $item_details[$i]['stock_unit_id'];
		$price = $item_details[$i]['price'];
		$discounted_price = $item_details[$i]['discounted_price'];
		$vendor_price = $item_details[$i]['vendor_price'];
		
		$sgstp = $item_details[$i]['sgst'];
		$cgstp = $item_details[$i]['cgst'];
		$igstp = $item_details[$i]['igst'];
	
		$type = $item_details[$i]['type'];
		$total_stock = $item_details[$i]['stock'];
		$quantity = $quantity_arr[$i];
		
		$sub_total=$discounted_price != 0?$discounted_price * $quantity:$price * $quantity;
 
		$data = array(
		    'user_id'=>$user_id,
		    'order_id'=>$db->escapeString($order_id),
		    'product_variant_id'=>$db->escapeString($product_variant_id),
		    'quantity'=>$db->escapeString($quantity),
		    'price'=>$db->escapeString($price),
		    'discounted_price'=>$db->escapeString($discounted_price),
		    'vendor_price'=>$db->escapeString($vendor_price),
		    'discount'=>$discount,
		    'sgst'=>$sgstp,
		    'cgst'=>$cgstp,
		    'igst'=>$igstp,
		    'sub_total'=>$db->escapeString($sub_total),
		    'status'=>$db->escapeString(json_encode($status)),
		    'active_status' => 'received'
 
		);
		$db->insert('order_items',$data);
		$res = $db->getResult();
        
		$balance = $final_total/10;
	
			 if($type=='packet'){
				 $stock = $total_stock-$quantity;
			
				 $sql = "update product_variant set stock = $stock where id = $product_variant_id";
			
				 $db->sql($sql);
				 $res = $db->getResult();
				 $sql = "select pv.stock,pv.product_id,p.name,p.min_stock,p.min_stock_notified from product_variant pv LEFT JOIN products p ON p.id=pv.product_id where pv.id=".$product_variant_id;
				$db->sql($sql);
			
				 $variant_qty = $db->getResult();
				 if($variant_qty[0]['min_stock_notified']==0){
				     if($variant_qty[0]['stock']<=$variant_qty[0]['min_stock']){
				         $subject = "Min Stock Reached!!!";
            			$message = "Product Name:".$variant_qty[0]['name']." Has Reached min Stock. Please take note of it...";
            			send_email($support_email,$subject,$message);
            			$sql = "update products set min_stock_notified=1 where id=".$variant_qty[0]['product_id'];
					   $db->sql($sql);
				     }
				 }
				 if($variant_qty[0]['stock']<=0){
					$data = array(
						"serve_for"=> "Sold Out",
					);
					$db->update("product_variant",$data,"id=$product_variant_id");
					$res = $db->getResult();
				 }
			 }elseif($type=='loose'){
				 if($measurement_unit_id==$stock_unit_id){
					 $stock = $quantity*$measurement;
					}else{
						$db->select('unit','*',null,'id='.$measurement_unit_id);
						$unit = $db->getResult();
					
							$stock=$function->convert_to_parent(($measurement*$quantity),$unit[0]['id']);
					}
 
					$sql = "update product_variant set stock = stock - $stock where product_id = $product_id AND type='loose'";
				
					$db->sql($sql);
					$res = $db->getResult();
			
				 $sql = "select pv.stock,pv.product_id,p.name,p.min_stock,p.min_stock_notified from product_variant pv LEFT JOIN products p ON p.id=pv.product_id where pv.product_id=".$product_id;
					$db->sql($sql);
					$res_stck= $db->getResult();
					 $variant_qty = $res_stck;
    				 if($variant_qty[0]['min_stock_notified']==0){
    				     if($variant_qty[0]['stock']<=$variant_qty[0]['min_stock']){
    				         $subject = "Min Stock Reached!!!";
                			$message = "Product Name:".$variant_qty[0]['name']." Has Reached min Stock. Please take note of it...";
                			send_email($support_email,$subject,$message);
                			$sql = "update products set min_stock_notified=1 where id=".$variant_qty[0]['product_id'];
					        $db->sql($sql);
    				     }
    				 }
				 
					if($res_stck[0]['stock']<=0){
					    $sql = "update product_variant set serve_for='Sold Out' where product_id=".$product_id;
					    $db->sql($sql);
					}
 
			 }
		}
		
		
		$data = array(
	        'final_total'=>$final_total
		);
 
		if($db->update('orders',$data,'id='.$order_id)){
			$res = $db->getResult();
			
			
			$sql = "delete from carts where user_id=".$user_id;
			$db->sql($sql);
			$response['error'] = "false";
			$response['message'] = "Order placed successfully.";
			$response['order_id'] = $order_id;
 
			$_SESSION['checkout']=$function->xss_clean_array($_POST);
			$_SESSION['checkout']['order_id'] = $order_id;
 
			error_log('Order #' . $order_id . ' saved successfully. Raw payment_method received=' . var_export($payment_method, true));
 
			
 
			error_log('FCM push block reached for order #' . $order_id
				. ' | payment_method=' . var_export($payment_method, true)
				. ' | user_id=' . var_export($user_id, true)
				. ' | seller_id=' . var_export($seller_id, true));
 
			$fcm_service_account = get_firebase_service_account();
			$fcm_access_token = get_fcm_access_token($fcm_service_account);
 
			if ($fcm_access_token !== null) {
				list($cust_ok, $cust_detail) = fcm_push_to_table(
					$db,
					'users',
					$user_id,
					'Order Placed',
					'Your order #' . $order_id . ' has been placed successfully.',
					$fcm_service_account,
					$fcm_access_token
				);
				if (!$cust_ok) {
					error_log('Customer order-placed push not delivered (user_id=' . $user_id . '): ' . $cust_detail);
				}
 
				list($seller_ok, $seller_detail) = fcm_push_to_table(
					$db,
					'seller',
					$seller_id,
					'New Order Received',
					'You have received a new order #' . $order_id . '.',
					$fcm_service_account,
					$fcm_access_token
				);
				if (!$seller_ok) {
					error_log('Seller new-order push not delivered (seller_id=' . $seller_id . '): ' . $seller_detail);
				}
			} else {
				error_log('FCM push skipped for order #' . $order_id . ': could not obtain access token (check firebase-service-account.json).');
			}
			print_r(json_encode($response));
		}else{
			$response['error'] = "true";
			$response['message'] = "Could not place order. Try again!";
			$response['order_id'] = 0;
			$_SESSION['checkout']['order_id'] = 0;
			print_r(json_encode($response));
		}
 
}elseif(isset($_POST['place_order']) && isset($_POST['user_id']) && empty(json_decode($function->xss_clean($_POST['product_variant_id'])))){
	$response['error'] = "true";
	$response['message'] = "Order without items in cart can not be placed!";
	$response['order_id'] = 0;
	$_SESSION['checkout']['order_id'] = 0;
	print_r(json_encode($response));
}

if(isset($_POST['product_rating']) && isset($_POST['user_id']) && isset($_POST['order_id']) && isset($_POST['product_id'])){
    if(!verify_token()){
        return false;
    }
    if(isset($_POST['rating']) && isset($_POST['rating_desc'])){
        $sql = "SELECT * FROM product_rating WHERE user_id=".$_POST['user_id']." AND order_id=".$_POST['order_id']." AND product_id=".$_POST['product_id'];
        $db->sql($sql);
        if($db->numRows()>0){
            if($_POST['rating']==0 || $_POST['rating']==''){
                $res1 = $db->getResult();
                $rating=$res1[0]['rating'];
            }else{
                $rating=$_POST['rating'];
            }
            $sql = "update product_rating set rating='".$rating."',rating_desc='".$_POST['rating_desc']."' WHERE user_id=".$_POST['user_id']." AND order_id=".$_POST['order_id']." AND product_id=".$_POST['product_id'];
		  //  echo $sql;
		    $db->sql($sql);
        }else{
            $cdate=date("Y-m-d H:i:s");
            $sql = "INSERT INTO product_rating (product_id,user_id,order_id,rating,rating_desc,created_at) VALUES('".$_POST['product_id']."','".$_POST['user_id']."','".$_POST['order_id']."','".$_POST['rating']."','".$_POST['rating_desc']."','".$cdate."')";
		    $db->sql($sql);
        }
        $res['error'] = false;
            $res['message'] = "Success";
            print_r(json_encode($res));
    }else{
         $res['error'] = true;
            $res['message'] = "Required All Parameters";
            print_r(json_encode($res));
    }
}

if(isset($_POST['get_orders']) && isset($_POST['user_id'])) {
    if(!verify_token()){
        return false;
    }
	$user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
	$limit = (isset($_POST['limit']) && !empty($_POST['limit']) && is_numeric($_POST['limit']))?$db->escapeString($function->xss_clean($_POST['limit'])):10;
	$offset = (isset($_POST['offset']) && !empty($_POST['offset']) && is_numeric($_POST['offset']))?$db->escapeString($function->xss_clean($_POST['offset'])):0;
	$where1="";
	if(isset($_POST['status'])){
	        $where1="AND o.active_status='".$_POST['status']."'";
	}
    $sql = "select *,(select name from users u where u.id=o.user_id) as user_name,(select email from users u where u.id=o.user_id) as user_email, (SELECT s.company_name FROM seller s WHERE s.id = o.seller_id) AS company_name,(SELECT s.image FROM seller s WHERE s.id = o.seller_id) AS company_image from orders o where user_id=".$user_id." ".$where1." ORDER BY date_added DESC LIMIT $offset,$limit";
    $db->sql($sql);
    $res = $db->getResult();
    $i=0; $j=0;
    foreach($res as $row){
        if($row['discount']>0){
            $discounted_amount = $row['total'] * $row['discount'] / 100; /*  */
    	    $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total']-$final_total;
            // echo $discount_in_rupees;
        } else {
            $discount_in_rupees = 0;
        }
		$res[$i]['company_image'] = (!empty($res[$i]['company_image'])) ? DOMAIN_URL . 'upload/sellers/' . $res[$i]['company_image'] : '';
        
        $res[$i]['discount_rupees'] = "$discount_in_rupees";
        $final_total = ($res[$i]['final_total']);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $sql = "select oi.*,v.product_id AS product_id,p.name,p.image,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi join product_variant v on oi.product_variant_id=v.id join products p on p.id=v.product_id where order_id=".$row['id'];
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        $res[$i]['status'] = json_decode($res[$i]['status']);
            
            for($j=0; $j < count($res[$i]['items']); $j++){
                $sql = "select rating,rating_desc FROM product_rating WHERE order_id=".$row['id']." AND product_id=".$res[$i]['items'][$j]['product_id'];
                $db->sql($sql);
                $rating=$db->getResult();
                $res[$i]['items'][$j]['rating']=!empty($rating[0]['rating']) ? $rating[0]['rating'] : '0';
                $res[$i]['items'][$j]['rating_desc']=!empty($rating[0]['rating_desc']) ? $rating[0]['rating_desc'] : "";
                $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status']))?json_decode($res[$i]['items'][$j]['status']):array();
                // unset($res[$i]['items'][$j]['status']);
                $res[$i]['items'][$j]['image'] = DOMAIN_URL.$res[$i]['items'][$j]['image'];
            }
        $i++;
    }
        $orders = $order = array();
        
        if(!empty($res)){
            $orders['error'] = false;
            $orders['message'] = "Orders found!";
            $orders['data'] = array_values($res);
            print_r(json_encode($orders));
        }else{
            $res['error'] = true;
            $res['message'] = "No orders found!";
            $res['data'] = array(); 
            print_r(json_encode($res));
            // return $res;
        }
}

if(isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
    if(!verify_token()){
        return false;
    }
    $sql1 = "select value from `settings` where variable= 'system_timezone'";
		    $db->sql($sql1);
    $res1 = $db->getResult(); 
    $settings =  json_decode($res1[0]['value'],true);
    $store_state = $settings['store_state'];
    
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
    	
        if($request_type == 'apprequest' && $postStatus == 'cancelled' && ($active_status == 'shipped' || $active_status=='delivered')){
    	       $response['error'] = true;
        	   $response['message'] = 'Item cancel Not Allowed After Shipped';
        	   $response['subtotal'] = 0;
        	   print_r(json_encode($response));
        	   return false;
    	}
    	
    	if($postStatus == 'cancelled'){
    	    
    	    $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id='.$order_id;
    	    $db->sql($sql);
    	    $res_order = $db->getResult();
    	    
    	   // print_r($res_order[0]['total']);
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,oi.`sgst`,oi.`cgst`,oi.`igst`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id`,pv.`product_price` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`='.$order_item_id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	   //echo '<pre>';print_r($res_oi);die;
    	    $price = $res_oi[0]['discounted_price']==0?$res_oi[0]['price']:$res_oi[0]['discounted_price'];
    	    $price = $price*$res_oi[0]['quantity'];
    	    $total = $res_order[0]['total'];
    	    $final_total = $res_order[0]['final_total'];
    	    $tax_amount = $res_order[0]['tax_amount'];
    	    $delivery_charge = $res_order[0]['delivery_charge'];
    	    $product_id = $res_oi[0]['product_id'];
    	    
    	    $pro_sql = "select * from products where id=".$product_id;
            $db->sql($pro_sql);
            $products= $db->getResult();
    	    
    	   // echo $total - $price;
    	    if($total - $price >= 0){
    	        $sql_total = "update orders set total=$total-$price where id=".$order_id;
    	        $db->sql($sql_total);
    	    }
    	    
    	    $sql = "select total,delivery_state from orders where id=".$order_id;
    	    $db->sql($sql);
    	    $res_total=$db->getResult();
    	    
    	    $total = $res_total[0]['total'];
    	    
    	   // if($total<$config['min_amount']){
    	   //     if($delivery_charge==0){
    	   //         $dchrg = $config['delivery_charge'];
    	   //         $sql_delivery_chrg = "update orders set delivery_charge=$dchrg where id=".$order_id;
        //     	   // echo $sql_delivery_chrg;
        //     	    $db->sql($sql_delivery_chrg);
        //     	    $sql_final_total = "update orders set final_total=$final_total-$price+$dchrg where id=".$order_id;
    	   //     }else{
    	   //         $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
    	   //     }
	       // }else{
	       //     $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
	       // }
	       
	        $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
	        
	       if($final_total>0){
    	       $db->sql($sql_final_total);
    	    }
    	    
    	    $product_price=!empty($res_oi[0]['product_price'])?$res_oi[0]['product_price']:0;
            $sgstp=!empty($res_oi[0]['sgst'])?$res_oi[0]['sgst']:0;
            $cgstp=!empty($res_oi[0]['cgst'])?$res_oi[0]['cgst']:0;
            if($store_state == $res_total[0]['delivery_state']){
                $gst=$sgstp+$cgstp;
                $taxable_amount=round(($product_price*$res_oi[0]['quantity']),2);
                $sgst=!empty($res_oi[0]['sgst'])?round(($taxable_amount*$res_oi[0]['sgst'])/100,2):0;
                $cgst=!empty($res_oi[0]['cgst'])?round(($taxable_amount*$res_oi[0]['cgst'])/100,2):0;
            }else{
                $gst = $igstp;
                $taxable_amount=round(($product_price*$res_oi[0]['quantity']),2);
                $igst=!empty($res_oi[0]['igst'])?round(($taxable_amount*$res_oi[0]['igst'])/100,2):0;
            }
            $sgst=!empty($sgst)?$sgst:0;
            $cgst=!empty($cgst)?$cgst:0;
            $igst=!empty($igst)?$igst:0;

            $tol_tax = $sgst+$cgst+$igst;
            if(!empty($tol_tax)){
				$sql_final_tax = "update orders set tax_amount=$tax_amount-$tol_tax where id=".$order_id;
				$db->sql($sql_final_tax);
				$db->getResult();
            }
            
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
        
        	//echo $cancelled." - ".$total;die;
        	
        	if($cancelled == $total){
        
        	    if($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] !='COD'){
                	/* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $result[0]['sub_total']+$res_order[0]['delivery_charge'];
	                if(!empty($products)){
	                	if($products[0]['price_type'] != 'including'){
		                	$total_amount = $total_amount+$res_order[0]['tax_amount'];
		                }
	                }
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    // return false;
                     
                    $function->update_wallet_balance($new_balance,$user_id);
                    
            	    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$total_amount,'Balance credited against item cancellation.');
                }elseif($res_order[0]['wallet_balance']!=0){
                    $user_id = $res_order[0]['user_id'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                    $function->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$res_order[0]['wallet_balance'],'Balance credited against item cancellation.');
                }
        	    
            	$data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => $currentStatus
        	        );
                $db->update('orders',$data_order,'id='.$order_id);
                $db->getResult();
                
        	}else{
        	    
        	    if($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] !='COD'){
        	        $user_id = $res_order[0]['user_id'];
        	        $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $result[0]['sub_total']);
                    $function->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$result[0]['sub_total'],'Balance credited against item cancellation.');
        	    }elseif($res_order[0]['wallet_balance']!=0){
                    $user_id = $res_order[0]['user_id'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                    $function->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$res_order[0]['wallet_balance'],'Balance credited against item cancellation.');
        	    }
        	}
		    
        	$response['error'] = false;
        	$response['message'] = 'Order item cancelled successfully!';
        	$response['subtotal'] = $result[0]['sub_total'];
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'returned'){
    	    $reason = !empty($_POST['reason'])?$_POST['reason']:'';
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
            $db->getResult();
            
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
                $db->getResult();
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

if(isset($_POST['update_order_item_status1']) && isset($_POST['order_item_id'])) {
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
    	if(!isset($_POST['ajaxCall']) || $active_status=='shipped' || $active_status=='delivered'){
    	    $response['error'] = true;
        	$response['message'] = 'Order cancel Not Allowed After Shipped';
        	$response['subtotal'] = 0;
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'cancelled'){
    	    $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id='.$order_id;
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
	        }else{
	            $sql_final_total = "update orders set final_total=$final_total-$price where id=".$order_id;
	        }
	        if($final_total>0){
    	       $db->sql($sql_final_total);
    	    }
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
                    $function->update_wallet_balance($new_balance,$user_id);
                    /* add wallet transaction */
            	    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$total_amount,'Balance credited against item cancellation.');
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
                $db->getResult();
        	}else{
        	    $user_id = $res_order[0]['user_id'];
        	    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $result[0]['sub_total']);
                    // echo $new_balance." - ".$user_id;die;
                    $function->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$result[0]['sub_total'],'Balance credited against item cancellation.');
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
            $db->getResult();
            
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
                $db->getResult();
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

if(isset($_POST['update_order_status']) && isset($_POST['id'])) {
	$id = $db->escapeString($function->xss_clean($_POST['id']));
	$postStatus = $db->escapeString($function->xss_clean($_POST['status']));
	
	$sub_total = 0;
	
	
	if(isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''){
	    $delivery_boy_id = $db->escapeString($function->xss_clean($_POST['delivery_boy_id']));
	    $sql = "SELECT user_id,delivery_boy_id,status FROM `orders` where id=$id";
    	$db->sql($sql);	
	    $res_delivery_boy_id = $db->getResult();
    	
    		if( ($res_delivery_boy_id[0]['delivery_boy_id'] == 0) || ( $res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')){
            $sql_get_name="select name,mobile from delivery_boys where id='$delivery_boy_id'";
    		$db->sql($sql_get_name);
    		$delivery_boy_name = $db->getResult();

    		
    		if($delivery_boy_name[0]['name']=='dunzo'){
    		        
    		        $result_set=$dunzo->create_task_line($id);
    		        if(!empty($result_set->task_id)){
    		            $ref_id=$result_set->drop_ids;
    		            $db->sql("INSERT INTO dunzo_task(order_id,task_id,reference_id,status,created_at)values('$id','$result_set->task_id','$ref_id[0]','$result_set->state','".date('Y-m-d H:i:s')."')");
    		            
    		        }else{
    		            $response['error'] = true;
                		$response['message'] = 'Dunzo could not create order at the movement!';
                		print_r(json_encode($response));
                		return false;
    		        }
    		        
    		}else{
    		    $message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
    		    $function->send_notification_to_delivery_boy($delivery_boy_id,"Your new order with ID : #$id has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
            
    		}
    		// $function->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order with ID : #$id  has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
			
			$sqlme = "select name,email,mobile,country_code from `users` where id=".$res_delivery_boy_id[0]['user_id'];
    		$db->sql($sqlme);
    		$res_useraa = $db->getResult();
    		
    		$to = $res_useraa[0]['email'];
			$subject = "Delivery Person Assigned for Your Order";
			if($delivery_boy_name[0]['name']=='dunzo'){
			    $message= "Hello, Dear, Your Order Is assigned to delivery Partner  ".ucwords($delivery_boy_name[0]['name']).". Here is your order ID : #".$id;
			}else{
			    $message= "Hello, Dear, Your Order Is assigned to delivery Person  ".ucwords($delivery_boy_name[0]['name']).". Here is your order ID : #".$id.". Contact Number For the Delivery Person is ".$delivery_boy_name[0]['mobile']."";
			}
			send_email($to,$subject,$message);
		}
    		
    	//	$message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
    	//	$function->send_notification_to_delivery_boy($delivery_boy_id,"Your new order with ID : #$id has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
         //   $function->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order with ID : #$id  has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
			
		//	$sqlme = "select name,email,mobile,country_code from `users` where id=".$res_delivery_boy_id[0]['user_id'];
    	//	$db->sql($sqlme);
    	//	$res_useraa = $db->getResult();
    		
    	//	$to = $res_useraa[0]['email'];
		//	$subject = "Delivery Person Assigned for Your Order";
		//	$message= "Hello, Dear, Your Order Is assigned to delivery Person  ".ucwords($delivery_boy_name[0]['name']).". Here is your order ID : #".$id.". Contact Number For the Delivery Person is ".$delivery_boy_name[0]['mobile']."";
		//	send_email($to,$subject,$message);
		//}
		$sql="UPDATE orders SET `delivery_boy_id`='".$delivery_boy_id."' WHERE id=".$id;
		$db->sql($sql);	
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
	

	$sql="select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status,active_status,delivery_boy_id from orders where id=".$id;
	$db->sql($sql); // Table name, Column Names, JOIN, WHERE conditions, ORDER BY conditions
	$res = $db->getResult();
	$order_user_id=$res[0]['user_id'];
	if($res[0]['active_status']!='delivered' && $postStatus=='returned'){
	    $response['error'] = true;
		$response['message'] = 'Cannot return order unless it is delivered!';
		print_r(json_encode($response));
		return false;
	}
	
	$sql = "SELECT sub_total FROM order_items WHERE order_id=".$id;
	$db->sql($sql);
	$res_query = $db->getResult();
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
    	    
    	    $active_status=$res[0]['active_status'];
    	    
    	    if($request_type == 'apprequest' && ( $active_status == 'processed' || $active_status == 'shipped' || $active_status=='delivered')){
               
    	       $response['error'] = true;
        	   $response['message'] = 'Order cancel Not Allowed After confirmed';
        	   $response['subtotal'] = 0;
        	   print_r(json_encode($response));
        	   return false;
    	    }
    	    
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
    	    
    	    if($res[0]['payment_method'] != 'cod' && $res[0]['payment_method'] !='COD'){
    	        if($res[0]['wallet_balance']!=0 && $res[0]['payment_status'] =='0'){
                   /* update user's wallet */
                    $user_id = $res[0]['user_id'];
                    // $total = $res[0]['total'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $function->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }else{
                	/* update user's wallet */
                    $user_id = $res[0]['user_id'];
                    $total = $res[0]['total']+$res[0]['delivery_charge']+$res[0]['tax_amount'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total;
                    // return false;
                    $function->update_wallet_balance($new_balance,$user_id);
                    /* add wallet transaction */
            	    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }
            }else{
                if($res[0]['wallet_balance']!=0){
                    /* update user's wallet */
                    $user_id = $res[0]['user_id'];
                    // $total = $res[0]['total'];
                    $user_wallet_balance = $function->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $function->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $function->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }
                
            }
        }
    	
    	if($postStatus=='delivered'){
    		$sql = "SELECT delivery_boy_id,final_total,total FROM orders WHERE id=".$id;
    		$db->sql($sql);
    		$res_boy = $db->getResult();
    		
    		
    		//invoice auto generate-start
    		$sql_outer="SELECT oi.*,u.*,p.*,v.*,o.*,u.name as uname,d.name as delivery_boy,o.status as order_status,oi.active_status as order_item_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name FROM `order_items` oi JOIN users u ON u.id=oi.user_id JOIN product_variant v ON oi.product_variant_id=v.id JOIN products p ON p.id=v.product_id JOIN orders o ON o.id=oi.order_id LEFT JOIN delivery_boys d ON o.delivery_boy_id=d.id WHERE o.id=".$id;
            // Execute query
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
            $res=$db->getResult();
            $total=$res[0]['total'];
            
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
                $res=$db->getResult();
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
            			$message .="<table style=' line-height:20px;width:100% !important ' width:'500'; align='center'  cellpadding='5' cellspacing='1'>
            					<thead><tr style='border-top:10px solid black;border-bottom:10px solid black;'><th width='10%'>Sr No.</th><th width='15%'>Product Code</th><th width='30%'>Product Name</th><th width='15%'>Unit</th><th width='15%'>Qty</th><th align='right' width='15%'>SubTotal (₹)</th></tr></thead>";
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
                                                        $final_total = $total+$delivery_charge-$discount_in_rupees-$promo_discount-$wallet;
                                                    $message .= "<tr><td style='font-weight:bold' colspan='4' align='right'>Final Total (₹)</td>
                                                    <td align='right'></td>
                                                                            <td align='right'>&nbsp;= ".($final_total)."</td>
                                                    </tr></table>";
			                                        //send_email($email,$subject,$message);
            }
            //invoice auto generate-end		
    
    		if($res_boy[0]['delivery_boy_id']!=0){
    			$sql = "SELECT bonus,name FROM delivery_boys WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$res_bonus = $db->getResult();
    			$reward = $res_boy[0]['total']/100*$res_bonus[0]['bonus'];
    	    	$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$comission = $function->add_delivery_boy_commission($delivery_boy_id,'credit',$reward,'Order Delivery Commission.');
    			
    			$sql = "SELECT value FROM `settings` WHERE variable='currency'";
    			$db->sql($sql);
    			$currency = $db->getResult();
    		    $message_delivery_boy = "Hello, Dear ".ucwords($res_bonus[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your Commission of".$reward." is credited. Please take a note of it.";
    			$function->send_notification_to_delivery_boy($delivery_boy_id,"Your commission ".$reward." ".$currency[0]['value']." has been credited","$message_delivery_boy",'delivery_boys',$id);
    			$function->store_delivery_boy_notification($delivery_boy_id,$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');
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
    						
    						$sql  = "SELECT name,friends_code FROM users WHERE id=".$order_user_id;
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
    	    
	        $result_set=$dunzo->cancel_task_line($id);
        	if(!empty($result_set)){
        	    $response['error'] = true;
        		$response['message'] = 'Dunzo could not cancel order at the movement!';
        		print_r(json_encode($response));
        		return false;
        	}
    	    if (!in_array('cancelled', $temp)) {
    	        $status[] = array('cancelled',date("d-m-Y h:i:sa") );
    	            $data = array(
    	            'status' => $db->escapeString(json_encode($status)),
    	        );
    	    }
    	    $db->update('orders',$data,'id='.$id);
    	    $db->getResult();
    
    	    foreach($result as $item){
    	        if($item['active_status'] != 'cancelled'){
    	            $item_data = array(
    	            'status' => $db->escapeString(json_encode($status)),
        	        'active_status' => 'cancelled'
    	            );
    	        $db->update('order_items',$item_data,'id='.$item['id']);
    	        $db->getResult();
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
    	             $db->getResult();
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
    	             $db->getResult();
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
    	    $db->getResult();
        	 $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'delivered'
             );
    	    foreach($result as $item){
    	        
    	        if($item['active_status'] != 'cancelled'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	             $db->getResult();
    	        }
    	    } 
    	}
    	
    	if($postStatus=='returned'){
             $status[] = array('returned',date("d-m-Y h:i:sa") );
             $data = array('status' => $db->escapeString(json_encode($status)));
    	     $db->update('orders',$data,'id='.$id);
    	     $db->getResult();
        	 $item_data = array(
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'returned'
             );
    	    foreach($result as $item){
    	        
    	        if($item['active_status'] != 'cancelled' && $item['active_status']=='delivered'){
    	             $db->update('order_items',$item_data,'id='.$item['id']);
    	             $db->getResult();
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
     	$db->getResult();
    		$response['error'] = false;
    		if ($postStatus == 'cancelled') {
                $response['message'] = "Order has been cancelled!";
                $response['order_id'] = $id;
            }

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
            // 		echo $country_code;
    		$subject = "Your order has been ".ucwords($postStatus);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
    		$function->send_order_update_notification($user_id,"Your order has been ".ucwords($postStatus),$message,'order');

        	// 	if(isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''){
            // 		$sql1 = "select name from `delivery_boys` where id=".$delivery_boy_id;
            // // 		echo $sql;
            // 		$db->sql($sql1);
            // 		$res_delivery_boy = $db->getResult();
            // 		$message_delivery_boy = "Hello, Dear ".ucwords($res_delivery_boy[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
         
            // 		$function->send_notification_to_delivery_boy($delivery_boy_id,"Your order has been ".ucwords($postStatus),"$message_delivery_boy",'delivery_boys',$id); 
            // 		$function->store_delivery_boy_notification($delivery_boy_id,$id,"Your order has been ".ucwords($postStatus),$message_delivery_boy,'order_status');
        	// 	}
    		send_email($to,$subject,$message);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services! Contact us for more information";
    		// sendSms($mobile,$message,$country_code);
    		
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

if(isset($_POST['get_settings'])) {
    if(!verify_token()){
        return false;
    }
	$sql = "select value from `settings` where variable='system_timezone'";
	$db->sql($sql);
	$res = $db->getResult();
	$sql = "select value from `settings` where variable='currency'";
	$db->sql($sql);
	$res_currency = $db->getResult();
	
	$sql = "select * from `zone`";
	$db->sql($sql);
	$res_zone = $db->getResult();
	   if(!empty($res)){
            $response['error'] = false;
            $response['settings'] = json_decode($res[0]['value'],1);
            $response['settings']['currency'] = $res_currency[0]['value'];
            $response['settings']['delivery_charge'] = empty($response['settings']['delivery_charge'])?"0":$response['settings']['delivery_charge'];
            $response['settings']['min-refer-earn-order-amount'] = empty($response['settings']['min-refer-earn-order-amount'])?"0":$response['settings']['min-refer-earn-order-amount'];
            $response['settings']['min_amount'] = empty($response['settings']['min_amount'])?"0":$response['settings']['min_amount'];
            $response['settings']['max-refer-earn-amount'] = empty($response['settings']['max-refer-earn-amount'])?"0":$response['settings']['max-refer-earn-amount'];
            $response['settings']['minimum-withdrawal-amount'] = empty($response['settings']['minimum-withdrawal-amount'])?"0":$response['settings']['minimum-withdrawal-amount'];
            $response['settings']['refer-earn-bonus'] = empty($response['settings']['refer-earn-bonus'])?"0":$response['settings']['refer-earn-bonus'];
            $response['settings']['current_version'] = empty($response['settings']['current_version'])?"0":$response['settings']['current_version'];
            $response['settings']['minimum_version_required'] = empty($response['settings']['minimum_version_required'])?"0":$response['settings']['minimum_version_required'];
        
        if(!empty($response['settings']['store_state'])){
            $sql = "SELECT * FROM state WHERE id='".$response['settings']['store_state']."'";
            $db->sql($sql);
            $res1 = $db->getResult();  
            $state_name = $res1[0]['name'];
        }
        
        if(!empty($response['settings']['store_city'])){
            $sql = "SELECT * FROM city WHERE id='".$response['settings']['store_city']."'";
            $db->sql($sql);
            $res = $db->getResult(); 
            $city_name = $res[0]['name'];
        }
        if(!empty($response['settings']['store_zone'])){
            $sql = "SELECT * FROM zone WHERE id='".$response['settings']['store_zone']."'";
            $db->sql($sql);
            $res = $db->getResult(); 
            $zone_name = $res[0]['name'];
        }
        
        $res_zone = array(
            array("id"=>"1", "name"=>$city_name),
            array("id"=>"2", "name"=>$state_name),
            array("id"=>"3", "name"=>$zone_name),
            array("id"=>"4", "name"=>'Rest of India')
        );
            
        $response['settings']['zone'] = $res_zone;
        print_r(json_encode($response));
            
        }else{
            $response['error'] = true;
            $response['settings'] = "No settings found!";
            $response['message'] = "Something went wrong!";
            print_r(json_encode($response));
            
        }
}

if(isset($_POST['update_order_total_payable']) && isset($_POST['id'])){
	
	$id = $db->escapeString($function->xss_clean($_POST['id']));
	$discount = $db->escapeString($function->xss_clean($_POST['discount']));
	$deliver_by = $db->escapeString($function->xss_clean($_POST['deliver_by']));
	$total_payble = $db->escapeString($function->xss_clean($_POST['total_payble']));
	$total_payble = round($total_payble,2);
	// echo $total_payble;
	$data = array(
		'discount' =>$discount,
		'deliver_by' => $deliver_by,
	);
	$data1 = array(
		 'discount' =>$discount,
		 'final_total' => $total_payble,
// 		 'total' => $total_payble,
	);

	
	if($discount >= 0){
	    $db->update('order_items',$data,'order_id='.$id);
	    $db->getResult();
	    
	    $db->update('orders',$data1,'id='.$id);  // Table name, column names and respective values
    	$res = $db->getResult();
    	if(!empty($res)){
    		// print_r($res);
    	    
        	$response['error'] = false;
            $response['message'] = "Order updated successfully.";
            print_r(json_encode($response));
    	}else{
            $response['error'] = true;
            $response['message'] = "Could not update order. Try again!";
            print_r(json_encode($response));
    	}
	}
}


if(isset($_POST['add_transaction']) && $_POST['add_transaction'] == true){
    if(!verify_token()){
        return false;
    }
	/*add data to transaction table*/
	
	$user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
	$order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
	$type = $db->escapeString($function->xss_clean($_POST['type']));
	$txn_id = $db->escapeString($function->xss_clean($_POST['txn_id']));
	$amount = $db->escapeString($function->xss_clean($_POST['amount']));
	$status = $db->escapeString($function->xss_clean($_POST['status']));
	$message = $db->escapeString($function->xss_clean($_POST['message']));
	$transaction_date = (isset($_POST['addedon']) && !empty($_POST['addedon']))?$db->escapeString($function->xss_clean($_POST['addedon'])):date('Y-m-d H:i:s');
	
	$data = array(
		'user_id' =>$user_id,
		'order_id' =>$order_id,
		'type' => $type,
		'txn_id' => $txn_id,
		'amount' => $amount,
		'status' => $status,
		'message' => $message,
		'transaction_date' => $transaction_date
	);	
	$db->insert('transactions',$data);  // Table name, column names and respective values
	$res = $db->getResult();
	
	#Update Payment Status
	$pdata=array(
		'payment_status'=>1
	);
	$db->update('orders',$pdata,'id='.$order_id);
	
	$function->send_order_confirmation($order_id);
	$subject = "New order placed for $app_name";
	$message = "New order ID : #".$order_id." received please take note of it and proceed further";
	$function->send_new_order_notification("New Order Received",$message,'order');
	
	$response['error'] = false;
	$response['transaction_id'] = $res[0];
	$response['message'] = "Transaction added successfully!";
	echo json_encode($response);
}

if(isset($_POST['update_payment'])){
    if(!verify_token()){
        return false;
    }
	/*add data to transaction table*/
	
	$user_id = $db->escapeString($function->xss_clean($_POST['user_id']));
	$order_id = $db->escapeString($function->xss_clean($_POST['order_id']));
	$type = $db->escapeString($function->xss_clean($_POST['type']));
	$txn_id = $db->escapeString($function->xss_clean($_POST['txn_id']));
	$amount = $db->escapeString($function->xss_clean($_POST['amount']));
	$status = $db->escapeString($function->xss_clean($_POST['status']));
	$message = $db->escapeString($function->xss_clean($_POST['message']));
	$transaction_date = (isset($_POST['addedon']) && !empty($_POST['addedon']))?$db->escapeString($function->xss_clean($_POST['addedon'])):date('Y-m-d H:i:s');
	$data = array(
		'user_id' =>$user_id,
		'order_id' =>$order_id,
		'type' => $type,
		'txn_id' => $txn_id,
		'amount' => $amount,
		'status' => $status,
		'message' => $message,
		'transaction_date' => $transaction_date
	);	
	$db->insert('transactions',$data);  // Table name, column names and respective values
	$res = $db->getResult();
	$sql = "UPDATE orders SET payment_method='".$type."' WHERE id =".$order_id;
	$db->sql($sql);
	$response['error'] = false;
	$response['transaction_id'] = $res[0];
	$response['message'] = "Transaction added successfully!";
	echo json_encode($response);
}

if(isset($_POST['order_enabled']) && isset($_POST['address_id'])){
    $address_id=$_POST['address_id'];
    $sql1 = "select value from `settings` where variable= 'system_timezone'";
    $db->sql($sql1);
    $res1 = $db->getResult(); 
    
    $settings =  json_decode($res1[0]['value'],true);
    $store_city = $settings['store_city']; 
    
    $sql = "SELECT * FROM delivery_method WHERE id=1";
    $db->sql($sql);
    $res = $db->getResult(); 
    $Delivery_by_courier = $res[0]['Delivery_by_courier'];
   

    $sql = "select * from `user_address` where id=".$address_id;
    $db->sql($sql);
    $res_add = $db->getResult(); 
    $user_city = $res_add[0]['city'];
    
    if($user_city==$store_city || $Delivery_by_courier==1){
        $response['error'] = false;
    	$response['status'] = 1;
    	$response['message'] = "Order Enabled for this Location";
    }else{
        $response['error'] = true;
    	$response['status'] = 0;
    	$response['message'] = "Order Not Enabled for this Location";
    }
	echo json_encode($response);
}


if(isset($_POST['get_order_delivery_charge']) && !empty($_POST['user_id']) && isset($_POST['address_id'])){
	
    $user_id = $_POST['user_id']; 
    $address_id=$_POST['address_id'];
    $seller_id=$_POST['seller_id'];
    
    $delivery_method=!empty($_POST['delivery_method'])?$_POST['delivery_method']:'';
    
    
    $sql2 = "select value from `settings` where variable= 'system_timezone'";
    $db->sql($sql2);
    $res2 = $db->getResult(); 
    
    $settings =  json_decode($res2[0]['value'],true);
    
    $store_zone = $settings['store_zone'];
    // $store_lat = (float)$settings['store_lattitude'];
    // $store_long = (float)$settings['store_longitude'];
    // $store_state = $settings['store_state'];
    // $store_city = $settings['store_city']; 
    
    $sql1 = "select * from `seller` where id = '".$seller_id."' limit 1";
    $db->sql($sql1);
    $res1 = $db->getResult();

    if(empty($res1)){
        echo json_encode(['status' => false, 'message' => 'Seller not found']);
        exit;
    }

    $seller = $res1[0];

    $store_lat   = (float)$seller['latitude'];
    $store_long  = (float)$seller['longitude'];
    $store_state = $seller['state_id'];
    $store_city  = $seller['city_id'];
    
    $sql = "SELECT * FROM delivery_method WHERE id=1";
    $db->sql($sql);
    $res = $db->getResult(); 
    $in_persion_delivery = $res[0]['in_persion_delivery'];
    $Delivery_by_courier = $res[0]['Delivery_by_courier'];
    $in_persion_data = $res[0]['in_persion_data'];
    $courier_data = $res[0]['courier_data'];
    $dunzo_enable = $res[0]['dunzo'];

    $sql = "select * from `user_address` where id=".$address_id;
    $db->sql($sql);
    $res = $db->getResult(); 
    
    $user_latitude = (float)trim($res[0]['latitude']);
    $user_longitude = (float)trim($res[0]['longitude']);
    $user_city = $res[0]['city'];
    $user_state = $res[0]['state'];

    if(!empty($delivery_method) && $delivery_method=='storepickup'){

			$response['error'] = false;
            $response['delivery_charge'] = 0;
            $response['message'] = "Delivery charge";
            echo json_encode($response);exit;

    // 	}elseif($in_persion_delivery == '1' && $user_city==$store_city){ 
	}elseif($in_persion_delivery == '1'){ 
	    if($dunzo_enable == '1'){  //echo 'test2';die();
	       $payload = [
             "pickup_details" => [
            		[
            			"lat" => $store_lat,
            			"lng" => $store_long,
            			"reference_id" => "pickup-ref"
            		]
            	],
            	"optimised_route" => true,
            	"drop_details" => [
            		[
            			"lat" => $user_latitude,
            			"lng" => $user_longitude,
            			"reference_id" => "drop-ref1"
            		]
            	]
             ];
             //var_dump($payload);die;
             $result_delivery_charge = $function->get_dunzo_delivery_charge($payload);
             //var_dump($result_delivery_charge);die();
             if(!empty($result_delivery_charge->estimated_price_breakup)){
             	$delivery_charge = $result_delivery_charge->estimated_price_breakup->delivery_charge_breakup->base_delivery_charge;
             	if(!empty($delivery_charge)){
	                 $response['error'] = false;
	                 $response['delivery_charge'] = $delivery_charge;
	                 $response['message'] = "Dunzo Delivery charge";
	                 echo json_encode($response);exit;
	             }else{
	                $response['error'] = true;
	                $response['message'] = "Unable to get delivery charge.";
	                echo json_encode($response);exit;
	             }
             }else{
             	$response['error'] = true;
                $response['message'] = "Unable to get delivery charge.";
                echo json_encode($response);exit;
            }
            
	   }else{
            if(!empty($in_persion_data)){
                $persion_data = json_decode($in_persion_data);
                
                $initial_distance=$persion_data->first_km;
                $amt=$persion_data->first_km_amount;
                $additional_amt=$persion_data->rest_km_amount;
            
            	$distance = $function->GetDeliveryDistance($store_lat , $user_latitude , $store_long , $user_longitude);
                //$distance = $function->GetDistanceHaversine($store_lat , $store_long, $user_latitude, $user_longitude);

            	$distance =ceil($distance);
            	
            	if($distance > $initial_distance){
            	   
             	  $additional_dist =   $distance - $initial_distance;
             	  $delivery_charge =  	$additional_amt *  $additional_dist;
            	  
             	  $total_delivery = 	$amt +  $delivery_charge;
            	  $total_delivery =sprintf('%0.2f', $total_delivery); 
            
                 $delivery_charge =   $total_delivery;
            	}else{
            	    $delivery_charge = $amt;
            	} 
            	
                $response['error'] = false;
                $response['delivery_charge'] = $delivery_charge;
                $response['distance'] = $distance.'Km';
                $response['message'] = "Delivery charge";
                echo json_encode($response);exit;
            }else{
                $response['error'] = true;
                $response['message'] = "Enable Delivery charge in delivery method";
                echo json_encode($response);exit;
            }
	    }
	
    }elseif($Delivery_by_courier == '1'){
		if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
			$items =$_POST['product_variant_id']; 
			$quantity_arr=json_decode($function->xss_clean($_POST['quantity']),1);
		}else{
			$items = $db->escapeString(stripslashes($function->xss_clean($_POST['product_variant_id']))); 
			$quantity_arr=json_decode($function->xss_clean($_POST['quantity']),1);
		}
       
        $user_zone = $db->escapeString(stripslashes($function->xss_clean($_POST['zone'])));
        $item_details=$function->get_product_by_variant_id($items);
		
        if(!empty($courier_data)){
            $zine = json_decode($courier_data);
            $zone1=$zine->zone1;
            $zone2=$zine->zone2;
            $zone3=$zine->zone3;
            $zone4=$zine->zone4;
            
            //var_dump($zine);die();
            $delivery_charges=0;
            $total_weight=0;
            
            for($i=0;$i<count($item_details);$i++){ 
            	$product_id = $item_details[$i]['product_id'];
            	$measurement = $item_details[$i]['measurement'];
            	$measurement_unit_id = $item_details[$i]['measurement_unit_id'];
            	$measurement_unit_name=$item_details[$i]['measurement_unit_name'];
            	$quantity = $quantity_arr[$i];
            	$weight = $item_details[$i]['weight'];
            	$total_weight=$total_weight+($weight*$quantity);
            }
            
            if($user_city == $store_city){
                $dchanrge = $zone1;
            }elseif($user_state == $store_state){
                $dchanrge = $zone2;
            }elseif($user_zone == $store_zone){
                $dchanrge = $zone3;
            }else{
                $dchanrge = $zone4;
            }
           
            $delivery_charges=$total_weight*$dchanrge;
            $response['error'] = false;
            $response['delivery_charge'] = round($delivery_charges,2);
            $response['package_weight'] = $total_weight;
            $response['message'] = "Delivery charge";
            echo json_encode($response);
        }
    }
	
}
?>