<?php
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');


include_once('../../api-firebase/send-email.php');
include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
$fn = new custom_functions();
$db=new Database();
$db->connect(); 
include_once('../../includes/variables.php');
$config = $fn->get_configurations();

/* 
-------------------------------------------
APIs for Delivery Boys
-------------------------------------------
1. login
2. get_delivery_boy_by_id  
3. get_orders_by_delivery_boy_id
4. get_fund_transfers 
5. update_delivery_boy_profile
6. update_order_status
7. delivery_boy_forgot_password
8. get_notifications
9. update_delivery_boy_fcm_id
10. check_delivery_boy_by_mobile
-------------------------------------------

-------------------------------------------

*/


//print_r($_POST);die;
if(!$_POST['accesskey'] AND  $db->escapeString($fn->xss_clean($_POST['accesskey'])) == $access_key){
    $response['error'] = true;
	$response['message'] = "No Accsess key found!";
	print_r(json_encode($response));
	return false;
	exit();
}

if(isset($_POST['login'])){
     /* 
    1.Login
        accesskey:90336
        mobile:9876543210
        password:12345678
        fcm_id:YOUR_FCM_ID
        Login:1
    */
    if(empty(trim($_POST['mobile']))){
        $response['error'] = true;
    	$response['message'] = "Mobile should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }

    
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($_POST['password']);
    $sql = "SELECT * FROM delivery_boys	WHERE mobile = '".$mobile."' AND password = '".$password."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	
	
	if($num == 1){
	    if($res[0]['status'] == 0){
	        $response['error'] = true;
	        $response['message'] = "It seems your acount is not active please contact admin for more info!"; 
	        $response['data'] = array();
		}else{
		    /* update fcm_id in delivery boy table */
		    $delivery_boy_id = $res[0]['id'];
		    $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
			if(!empty($fcm_id)){
			    $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$delivery_boy_id."'";
			    $db->sql($sql1);
			    $db->sql($sql);
			    $res=$db->getResult();
			    $db->disconnect(); 
			}
			$response['error'] = false;
            $response['message'] = "Delivery Boy Login Susseccfully";
            $response['data'] = $res;
		}
	}else{
	    if($res[0]['mobile'] != $mobile){
	        $response['error'] = true;
		    $response['message'] = "Phone Number is not registered!";
	    }
	    if($res[0]['mobile'] != $mobile && $res[0]['password'] != $password){
	        $response['error'] = true;
		    $response['message'] = "Invalid number or password, Try again.";
	    }
	   
	}
	print_r(json_encode($response));die;
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}


/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['get_delivery_boy_by_id'])){
    
    /* 
    2.get_delivery_boy_by_id
        accesskey:90336
        id:78
        get_delivery_boy_by_id:1
    */
     if(empty($_POST['id'])){
        $response['error'] = true;
    	$response['message'] = "Id of Delivery boy should be Passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $sql = "SELECT * FROM delivery_boys	WHERE id = '".$id."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	$db->disconnect(); 
	if($num == 1){
    	$response['error'] = false;
        $response['message'] = "Delivery Boy Data Fetched Susseccfully";
        $response['data'] = $res;
	}else{
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));die;
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_orders_by_delivery_boy_id'])){
    
    /* 
    3.get_orders_by_delivery_boy_id
        accesskey:90336
        id:40        // {optional}          
        order_id:1001        // {optional}  
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id / user_id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        filter_order:filter_order_status         // {optional} 
        get_orders_by_delivery_boy_id:1
    */
    $response_data = array();
    
    $id = ( isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';
    $order_id = ( isset($_POST['order_id']) && !empty(trim($_POST['order_id'])) && is_numeric($_POST['order_id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order_id']))) : '';
    $where = '';
    $where1 = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty(trim($_POST['search']))){
        $search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
        $where .= " where (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
    }
    
    if(isset($_POST['filter_order']) && $_POST['filter_order']!=''){
        $filter_order=$db->escapeString($fn->xss_clean($_POST['filter_order']));
        if(isset($_POST['search']) && $_POST['search']!='' ){
            $where .=" and `active_status`='".$filter_order."'";
        }else{
            $where .=" where `active_status`='".$filter_order."'";
        }
    }
    
    if(empty($where)){
        if(empty($id)){
           $where .= (!empty($order_id))?" WHERE o.id = $order_id":""; 
        }else{
           $where .= " WHERE delivery_boy_id = ".$id; 
           $where .= (!empty($order_id))?" AND o.id = $order_id":""; 
        }   
    }else{
        $where .= (!empty($id))?" AND delivery_boy_id = ".$id:""; 
        $where .= (!empty($order_id))?" AND o.id = $order_id":"";
    }
    
    if(isset($_POST['status'])){
        $where1=$where;
        if($_POST['status']=='active'){
            $where .=" AND active_status!= 'delivered' AND active_status!= 'cancelled' AND active_status!= 'returned'";
        }else if($_POST['status']=='past'){
            $where .=" AND active_status!= 'received' AND active_status!= 'processed' AND active_status!= 'shipped'";
        }
    }else{
        $where1=$where;
    }
    $orders_join = " JOIN users u ON u.id=o.user_id ";
    
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o ".$orders_join." ".$where1;
    $db->sql($sql);
    $res = $db->getResult();
 
    foreach($res as $row){
        $total = $row['total'];
    }
    $sql="select o.*,u.name as name FROM orders o ".$orders_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
  
    $db->sql($sql);
    $res = $db->getResult();
 
    for($i=0;$i<count($res);$i++) {
       $sql="select oi.*,p.name as name, v.measurement,p.image, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name from `order_items` oi 
            join product_variant v on oi.product_variant_id=v.id 
            join products p on p.id=v.product_id 
            where oi.order_id=".$res[$i]['id'];
          
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
   
    $rows1 = $tempRow = array();
    $response_data['total'] = $total;
      
    foreach($res as $row){
        
        $items = $row['items'];
        $items1 = $temp = array();
        $total_amt = 0;
        
        foreach($items as $item){  
         
            if($item['discounted_price']!=0){
                $price = $item['discounted_price'];
               
            }else{
                $price = $item['price'];
            } 
            
           
            
            $temp = array(
                'id' => $item['id'], 
                'product_variant_id' => $item['product_variant_id'], 
                'name' => $item['name'], 
                'unit' => $item['measurement']." ".$item['mesurement_unit_name'], 
                'product_image' => DOMAIN_URL.$item['image'],
                'price' => $price, 
                'quantity' => $item['quantity'], 
                'subtotal' => $item['sub_total'],
                'active_status' => $item['active_status']
            ); 
            $total_amt += $item['sub_total'];
            $items1[] = $temp;
        }
        
        if($row['active_status'] == 'received'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'processed'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'shipped'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='delivered'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
            $active_status = $row['active_status'];
        }
        
        $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
        $final_total = $row['total'] - $discounted_amount;
        
        $discount_in_rupees = $row['total']-$final_total;
        $discount_in_rupees = floor($discount_in_rupees);
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['mobile'] = $row['mobile'];
        $tempRow['delivery_charge'] = $row['delivery_charge'];
        $tempRow['items'] = $items1;
        $tempRow['total'] = $row['total'];
        $tempRow['tax'] = $row['tax_amount'].'('.$row['tax_percentage'].'%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
        $tempRow['qty'] = $row['items'][0]['quantity'];
        $tempRow['final_total'] = ceil($row['final_total']);
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['deliver_by'] = $row['items'][0]['deliver_by'];
        $tempRow['payment_method'] = $row['payment_method'];
        $tempRow['payment_request'] = $row['payment_request'];
        $tempRow['address'] = $row['address'];
        $tempRow['latitude'] = $row['latitude'];
        $tempRow['longitude'] = $row['longitude'];
        $tempRow['delivery_time'] = $row['delivery_time'];
        $tempRow['active_status'] = $active_status;
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
        $rows1[] = $tempRow;
        
    }
    $response_data['error'] = false;
    $response_data['data'] = $rows1;

    print_r(json_encode($response_data));die;
}

/*
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_fund_transfers'])){
    
    /* 
    4. get_fund_transfers
        accesskey:90336
        id:82
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_fund_transfers:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where f.`id` like '%".$search."%' OR d.`name` like '%".$search."%' OR f.`message` like '%".$search."%' OR d.`mobile` like '%".$search."%' OR d.`address` like '%".$search."%' OR f.`opening_balance` like '%".$search."%' OR f.`closing_balance` like '%".$search."%' OR d.`balance` like '%".$search."%' OR f.`date_created` like '%".$search."%'" ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(f.id) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['address'] = $row['address'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['type'] = $row['type'];
		$tempRow['amount'] = $row['amount'];
		$tempRow['opening_balance'] = $row['opening_balance'];
		$tempRow['closing_balance'] = $row['closing_balance'];
		$tempRow['status'] = $row['status'];
		$tempRow['message'] = $row['message'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}
/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['update_delivery_boy_profile'])){
    
    /* 
    5.update_delivery_boy_profile
        accesskey:90336
        id:87
        name:any value       
        address:Jl Komplek Polri   
        old_password:        // {optional}
        update_password:        // {optional}
        confirm_password:        // {optional}
        update_delivery_boy_profile:1
    */
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $name=$db->escapeString(trim($fn->xss_clean($_POST['name'])));
    $address =$db->escapeString(trim($_POST['address']));
    $old_password = (isset($_POST['old_password']) || !empty(trim($_POST['old_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['old_password']))):"";
    $update_password = (isset($_POST['update_password']) || !empty(trim($_POST['update_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['update_password']))):"";
    $confirm_password = (isset($_POST['confirm_password']) || !empty(trim($_POST['confirm_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['confirm_password']))):"";
    $change_password = false;
    
    /* check if id is not empty and there is valid data in it */
    if(!isset($_POST['id']) || empty(trim($_POST['id'])) || !is_numeric($_POST['id'])){
        $json_response['error'] = true;
        $json_response['message'] = "Invalid Id of Delivery Boy";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    $sql="SELECT * from delivery_boys where id='$id'";
    $db->sql($sql);
    $res_id = $db->getResult();
    $num = $db->numRows($res);
    if($num != 1){
        $json_response['error'] = true;
        $json_response['message'] = "Delivery Boy is not Registered.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* if any of the password field is set and old password is not set */
    if(( !empty($confirm_password) || !empty($update_password)) && empty($old_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Please enter old password.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* either of the password field is not empty and is they don't match */
    if(( !empty($confirm_password) || !empty($update_password)) && ($update_password != $confirm_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Password and Confirm Password mismatched.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* when all conditions are met check for old password in database */
    if( !empty($confirm_password) && !empty($update_password) && !empty($old_password)){
        $old_password = md5($old_password);
        $sql = "Select password from `delivery_boys` where id = '$id' and password = '$old_password' ";
        $db->sql($sql);
        $res = $db->getResult();
     
        if(empty($res)){
            $json_response['error'] = true;
            $json_response['message'] = "Old password mismatched.";
            print_r(json_encode($json_response));
            return false;
            exit();
        }
        $change_password = true;
        $confirm_password = md5($confirm_password);
    }
    
    $sql = "Update delivery_boys set `name`='".$name."',`address`='".$address."' ";
    $sql .= ( $change_password )?", `password`='".$confirm_password."' ":"";
    $sql .= " where `id` = '$id' ";
    
    if($db->sql($sql)){
        $json_response['error'] = false;
        $json_response['message'] = "Information Updated Successfully.";
        $json_response['message'] .= ( $change_password )?" and password also updated successfully.":"";
    } else {
        $json_response['error'] = true;
        $json_response['message'] = "Some Error Occurred! Please Try Again.";
    }
    print_r(json_encode($json_response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_order_status']) && isset($_POST['id'])) { 
    
    /* 
    6.update_order_status
        accesskey:90336
		update_order_status:1
		id:26
		status:cancelled
		delivery_boy_id:40        // {optional}
    */
	$id = $db->escapeString($fn->xss_clean($_POST['id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	$delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
	
	$sql = "SELECT user_id,delivery_boy_id,status FROM `orders` where id=$id";
        $db->sql($sql); 
        $res_delivery_boy_id = $db->getResult();
        
	if( ($res_delivery_boy_id[0]['delivery_boy_id'] == 0) || ( $res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')){
		$sql="UPDATE orders SET `delivery_boy_id`='".$delivery_boy_id."' WHERE id=".$id;
		$db->sql($sql);	
		$sql_get_name="select name from delivery_boys where id='$delivery_boy_id'";
		$db->sql($sql_get_name);
		$delivery_boy_name = $db->getResult();
		$message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
		$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
       // $fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
	}
    $sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id='".$id."' && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if($res_cancelled[0]['cancelled']>0){
    	$response['error'] = true;
		$response['message'] = 'Could not update order status!';
		print_r(json_encode($response));
		return false;
	}
    $sql="select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status from orders where id=".$id;
	$db->sql($sql);
	$res = $db->getResult();
	$order_user_id=$res[0]['user_id'];
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
    			$response['message'] = 'Delivery Boy updated, But order status not due to duplicate status!';
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
        	        if($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']){
        	            $stock = $fn->convert_to_parent($res_oi[$i]['measurement'],$res_oi[$i]['measurement_unit_id']);
        	            $stock = $stock * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
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
                $user_id = $res[0]['user_id'];
                $total = $res[0]['total']+$res[0]['delivery_charge']+$res[0]['tax_amount'];
                $user_wallet_balance = $fn->get_wallet_balance($user_id);
                $new_balance = $user_wallet_balance + $total;
                $fn->update_wallet_balance($new_balance,$user_id);
        	    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
            }else{
                if($res[0]['wallet_balance']!=0){
                    $user_id = $res[0]['user_id'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $fn->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }
            }
        }
        
    	if($postStatus=='delivered'){
    		$sql = "SELECT delivery_boy_id,final_total FROM orders WHERE id=".$id;
    		$db->sql($sql);
    		$res_boy = $db->getResult();
    		if($res_boy[0]['delivery_boy_id']!=0){
    			$sql = "SELECT bonus FROM delivery_boys WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$res_bonus = $db->getResult();
    		    $reward = $res_boy[0]['final_total']/100*$res_bonus[0]['bonus'];
    			$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$comission=$fn->add_delivery_boy_commission($delivery_boy_id,'credit',$reward,$message='Order Delivery Boy Commission.');
    			
    			$sql = "SELECT value FROM `settings` WHERE variable='currency'";
    			$db->sql($sql);
    			$currency = $db->getResult();
    		    $message_delivery_boy = "Hello, Dear ".ucwords($res_bonus[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your Commission of".$reward." is credited. Please take a note of it.";
    			$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your commission ".$reward." ".$currency[0]['value']." has been credited","$message_delivery_boy",'delivery_boys',$id);
    			//$fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');

    		}
    		
    		if($config['is-refer-earn-on']==1){
    			if($res_boy[0]['final_total']>=$config['min-refer-earn-order-amount']){
    				if($res_count[0]['total']==0){
    					if($res_user[0]['friends_code'] != ''){
    						if($config['refer-earn-method']=='percentage'){
    							$percentage = $config['refer-earn-bonus'];
    							$bonus_amount = $res_boy[0]['final_total']/100*$percentage;
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
    						    $fn->add_wallet_transaction($friend_user[0]['id'],'credit',floor($bonus_amount),'Refer & Earn Bonus on first order by '.ucwords($res_data[0]['name']));
    						
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
    	$i = sizeof($status);
        $currentStatus = $status[$i-1][0];
        $final_status = array(
        	'active_status' => $currentStatus
    	);
     	if($db->update('orders',$final_status,'id='.$id)){
    		$response['error'] = false;
    		$response['message'] = $postStatus=='cancelled'?"Order has been cancelled!":"Order updated successfully.";
    		$res = $db->getResult();
    		$sql = "select name,email,mobile,country_code from `users` where id=".$user_id;
    		$db->sql($sql);
    		$res_user = $db->getResult();
    		
    		$to = $res_user[0]['email'];
    		$mobile = $res_user[0]['mobile'];
    		$country_code = $res_user[0]['country_code'];
    		$subject = "Your order has been ".ucwords($postStatus);
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
    		$fn->send_order_update_notification($user_id,"Your order has been ".ucwords($postStatus),$message,'order');
    		send_email($to,$subject,$message);
    		
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services! Contact us for more information";
    		//send_email($to,$subject,$message);
    		print_r(json_encode($response));
    	} else {
    		$response['error'] = true;
    		$response['message'] = "Delivery Boy updated, But Could not update order status. Try again!";
    		print_r(json_encode($response));
    	}
    }else{
		$response['error'] = true;
		$response['message'] = "Sorry Invalid order ID";
		print_r(json_encode($response));
	}
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['delivery_boy_forgot_password']) && isset($_POST['mobile'])) {
    
    /* 
    7.delivery_boy_forgot_password
        accesskey:90336
		mobile:8989898989
		password:1234567
		delivery_boy_forgot_password:1
    */
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
	    $sql_update = "UPDATE `delivery_boys` SET `password`='$password' WHERE `mobile`='$mobile'";	
	    $db->sql($sql_update);
		$response["error"]   = false;
		$response["message"] = "Password updated successfully";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number id not Registered!";
	}
	print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_notifications'])){
    
    /* 
    8. get_notifications
        accesskey:90336
        id:114
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_notifications:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `message` like '%".$search."%' OR `type` like '%".$search."%' OR `date_created` like '%".$search."%'  " ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(id) as total FROM `delivery_boy_notifications` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT * FROM `delivery_boy_notifications` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['order_id'] = $row['order_id'];
		$tempRow['title'] = $row['title'];
		$tempRow['message'] = $row['message'];
		$tempRow['type'] = $row['type'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_delivery_boy_fcm_id'])){
     /* 
    9.update_delivery_boy_fcm_id
        accesskey:90336
        id:114
        fcm_id:YOUR_FCM_ID
        update_delivery_boy_fcm_id:1
    */
    
    if(empty($_POST['fcm_id'])){
        $response['error'] = true;
    	$response['message'] = "Please pass the fcm_id!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    if(isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])){
        $fcm_id = $db->escapeString($$fn->xss_clean($_POST['fcm_id']));
        $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$id."'";
	    if($db->sql($sql1)){
	        $response['error'] = false;
            $response['message'] = "Delivery Boy fcm_id Updeted Susseccfully.";
            print_r(json_encode($response));
	    } else {
	        $response['error'] = true;
            $response['message'] = "Can not update fcm_id of delivery boy.";
            print_r(json_encode($response));
	    }
   }
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['check_delivery_boy_by_mobile']) && isset($_POST['mobile'])) {
    
    /* 
    10.check_delivery_boy_by_mobile
        accesskey:90336
		mobile:8989898989
		check_delivery_boy_by_mobile:1
    */
  
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
		$response["error"]   = false;
		$response["message"] = "Mobile number is Registered.";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number is not Registered!";
	}
	print_r(json_encode($response));
}


if(isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
	$order_item_id = $db->escapeString($fn->xss_clean($_POST['order_item_id']));
	$order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	
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
    	if($active_status=='delivered'){
    	    $response['error'] = true;
        	$response['message'] = 'Order cancel Not Allowed After delivered';
        	$response['subtotal'] = 0;
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'cancelled'){
    	    $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id='.$order_id;
    	    $db->sql($sql);
    	    $res_order = $db->getResult();
    	    //print_r($res_order[0]['total']);die;
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`='.$order_item_id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	    $price = $res_oi[0]['discounted_price']==0?$res_oi[0]['price']:$res_oi[0]['discounted_price'];
    	    $total = $res_order[0]['total'];
    	    $final_total = $res_order[0]['final_total'];
    	    $delivery_charge = $res_order[0]['delivery_charge'];
    	   // echo $total - $price;die;
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
    	            $stock = $fn->convert_to_parent($res_oi[0]['measurement'],$res_oi[0]['measurement_unit_id']);
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
        	//print_r($cancelled." - ".$total);die;
        	if($cancelled==$total){
        	   // print_r($res_order);
        	    if($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] !='COD'){
                	/* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $res_order[0]['total']+$res_order[0]['delivery_charge']+$res_order[0]['tax_amount'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    // return false;
                    $fn->update_wallet_balance($new_balance,$user_id);
                    /* add wallet transaction */
            	    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$total_amount,'Balance credited against item cancellation.');
                }else{
                if($res_order[0]['wallet_balance']!=0){
                    /* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    // $total = $res[0]['total'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                    // echo $new_balance;die;
                    $fn->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$res_order[0]['wallet_balance'],'Balance credited against item cancellation.');
                }
                    
           }
        	    
            	$data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => $currentStatus
        	        );
                $db->update('orders',$data_order,'id='.$order_id);
        	}else{
        	    $user_id = $res_order[0]['user_id'];
        	    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $result[0]['sub_total']);
                    // echo $new_balance." - ".$user_id;die;
                    $fn->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$result[0]['sub_total'],'Balance credited against item cancellation.');
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
        	$fn->store_return_request($result[0]['user_id'],$order_id,$order_item_id);
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

// if(isset($_POST['enable_pay_button'])){
//     $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
//     $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
//     $sql = "SELECT payment_method, active_status, final_total FROM orders WHERE id =".$order_id." AND delivery_boy_id=".$delivery_boy_id;
// 	$db->sql($sql);
// 	$res = $db->getResult();
// 	if(!empty($res)){
// 	    $order=$res[0];
// 	    if($order['payment_method']=='cod' && $order['active_status']!='cancelled'){
// 	        $sql = "UPDATE orders SET payment_method='pending' WHERE id =".$order_id;
// 	        $db->sql($sql);
// 	        $response['error'] = false;
//         	$response['message'] = 'Payment Request enabled For this Order.';
//         	$response['payable_amt'] = $$order['final_total'];
//         	print_r(json_encode($response));
//         	return false;
// 	    }else{
// 	        $response['error'] = true;
//         	$response['message'] = 'Something Wrong';
//         	print_r(json_encode($response));
//         	return false;
// 	    }
// 	}
    
// }

/*if(isset($_POST['test'])) {
    // echo $fn->add_delivery_boy_commission('114','credit',12,'Test Order commission');
    $a = $fn->send_notification_to_delivery_boy('114','test notification ','Sample notification goes here','order_status',612);
    echo $a;
}*/



?><?php
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');


include_once('../../api-firebase/send-email.php');
include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
$fn = new custom_functions();
$db=new Database();
$db->connect(); 
include_once('../../includes/variables.php');


/* 
-------------------------------------------
APIs for Delivery Boys
-------------------------------------------
1. login
2. get_delivery_boy_by_id  
3. get_orders_by_delivery_boy_id
4. get_fund_transfers 
5. update_delivery_boy_profile
6. update_order_status
7. delivery_boy_forgot_password
8. get_notifications
9. update_delivery_boy_fcm_id
10. check_delivery_boy_by_mobile
-------------------------------------------

-------------------------------------------

*/


//print_r($_POST);die;
if(!$_POST['accesskey'] AND  $db->escapeString($fn->xss_clean($_POST['accesskey'])) == $access_key){
    $response['error'] = true;
	$response['message'] = "No Accsess key found!";
	print_r(json_encode($response));
	return false;
	exit();
}

if(isset($_POST['login'])){
     /* 
    1.Login
        accesskey:90336
        mobile:9876543210
        password:12345678
        fcm_id:YOUR_FCM_ID
        Login:1
    */
    if(empty(trim($_POST['mobile']))){
        $response['error'] = true;
    	$response['message'] = "Mobile should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }

    
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($_POST['password']);
    $sql = "SELECT * FROM delivery_boys	WHERE mobile = '".$mobile."' AND password = '".$password."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	
	
	if($num == 1){
	    if($res[0]['status'] == 0){
	        $response['error'] = true;
	        $response['message'] = "It seems your acount is not active please contact admin for more info!"; 
	        $response['data'] = array();
		}else{
		    /* update fcm_id in delivery boy table */
		    $delivery_boy_id = $res[0]['id'];
		    $fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])):"";
			if(!empty($fcm_id)){
			    $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$delivery_boy_id."'";
			    $db->sql($sql1);
			    $db->sql($sql);
			    $res=$db->getResult();
			    $db->disconnect(); 
			}
			$response['error'] = false;
            $response['message'] = "Delivery Boy Login Susseccfully";
            $response['data'] = $res;
		}
	}else{
	    if($res[0]['mobile'] != $mobile){
	        $response['error'] = true;
		    $response['message'] = "Phone Number is not registered!";
	    }
	    if($res[0]['mobile'] != $mobile && $res[0]['password'] != $password){
	        $response['error'] = true;
		    $response['message'] = "Invalid number or password, Try again.";
	    }
	   
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}


/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['get_delivery_boy_by_id'])){
    
    /* 
    2.get_delivery_boy_by_id
        accesskey:90336
        id:78
        get_delivery_boy_by_id:1
    */
     if(empty($_POST['id'])){
        $response['error'] = true;
    	$response['message'] = "Id of Delivery boy should be Passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $sql = "SELECT * FROM delivery_boys	WHERE id = '".$id."'";
	$db->sql($sql);
	$res=$db->getResult();
	$num = $db->numRows($res);
	$db->disconnect(); 
	if($num == 1){
    	$response['error'] = false;
        $response['message'] = "Delivery Boy Data Fetched Susseccfully";
        $response['data'] = $res;
	}else{
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
}else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_orders_by_delivery_boy_id'])){
    
    /* 
    3.get_orders_by_delivery_boy_id
        accesskey:90336
        id:40        // {optional}          
        order_id:1001        // {optional}  
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id / user_id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        filter_order:filter_order_status         // {optional} 
        get_orders_by_delivery_boy_id:1
    */
    $response_data = array();
    
    $id = ( isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';
    $order_id = ( isset($_POST['order_id']) && !empty(trim($_POST['order_id'])) && is_numeric($_POST['order_id']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order_id']))) : '';
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty(trim($_POST['search']))){
        $search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
        $where .= " where (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
    }
    
    if(isset($_POST['filter_order']) && $_POST['filter_order']!=''){
        $filter_order=$db->escapeString($fn->xss_clean($_POST['filter_order']));
        if(isset($_POST['search']) && $_POST['search']!='' ){
            $where .=" and `active_status`='".$filter_order."'";
        }else{
            $where .=" where `active_status`='".$filter_order."'";
        }
    }
    
    if(empty($where)){
        if(empty($id)){
           $where .= (!empty($order_id))?" WHERE o.id = $order_id":""; 
        }else{
           $where .= " WHERE delivery_boy_id = ".$id; 
           $where .= (!empty($order_id))?" AND o.id = $order_id":""; 
        }   
    }else{
        $where .= (!empty($id))?" AND delivery_boy_id = ".$id:""; 
        $where .= (!empty($order_id))?" AND o.id = $order_id":"";
    }
    
    $orders_join = " JOIN users u ON u.id=o.user_id ";
    
    $sql = "SELECT COUNT(o.id) as total FROM `orders` o ".$orders_join." ".$where;
    $db->sql($sql);
    $res = $db->getResult();
    foreach($res as $row){
        $total = $row['total'];
    }
    $sql="select o.*,u.name as name FROM orders o ".$orders_join." ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
    $db->sql($sql);
    $res = $db->getResult();
    
    for($i=0;$i<count($res);$i++) {
       $sql="select oi.*,p.name as name, v.measurement,p.image, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name from `order_items` oi 
            join product_variant v on oi.product_variant_id=v.id 
            join products p on p.id=v.product_id 
            where oi.order_id=".$res[$i]['id'];
            
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
    }
    $rows1 = $tempRow = array();
    $response_data['total'] = $total;
    
    foreach($res as $row){
        $items = $row['items'];
        $items1 = $temp = array();
        $total_amt = 0;
        
        foreach($items as $item){ 
               if($item['discounted_price']!=0){
                $price = $item['discounted_price'];
               
            }else{
                $price = $item['price'];
            } 
            
            $temp = array(
                'id' => $item['id'], 
                'product_variant_id' => $item['product_variant_id'], 
                'name' => $item['name'], 
                'unit' => $item['measurement']." ".$item['mesurement_unit_name'], 
                'product_image' => DOMAIN_URL.$item['image'],
                'price' => $price, 
                'quantity' => $item['quantity'], 
                'subtotal' => $item['quantity'] * $item['price'],
                'active_status'=>$item['active_status']
            ); 
            $total_amt += $item['sub_total'];
            $items1[] = $temp;
        }
        
        if($row['active_status'] == 'received'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'processed'){
            $active_status = $row['active_status'];
        }
        if($row['active_status'] == 'shipped'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='delivered'){
            $active_status = $row['active_status'];
        }
        if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
            $active_status = $row['active_status'];
        }
        
        $discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
        $final_total = $row['total'] - $discounted_amount;
        
        $discount_in_rupees = $row['total']-$final_total;
        $discount_in_rupees = floor($discount_in_rupees);
        $tempRow['id'] = $row['id'];
        $tempRow['user_id'] = $row['user_id'];
        $tempRow['name'] = $row['name'];
        $tempRow['mobile'] = $row['mobile'];
        $tempRow['delivery_charge'] = $row['delivery_charge'];
        $tempRow['items'] = $items1;
        $tempRow['total'] = $row['total'];
        $tempRow['tax'] = $row['tax_amount'].'('.$row['tax_percentage'].'%)';
        $tempRow['promo_discount'] = $row['promo_discount'];
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
        $tempRow['qty'] = $row['items'][0]['quantity'];
        $tempRow['final_total'] = ceil($row['final_total']);
        $tempRow['promo_code'] = $row['promo_code'];
        $tempRow['deliver_by'] = $row['items']['deliver_by'];
        $tempRow['payment_method'] = $row['payment_method'];
        $tempRow['address'] = $row['address'];
        $tempRow['latitude'] = $row['latitude'];
        $tempRow['longitude'] = $row['longitude'];
        $tempRow['delivery_time'] = $row['delivery_time'];
        $tempRow['active_status'] = $active_status;
        $tempRow['wallet_balance'] = $row['wallet_balance'];
        $tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
        $rows1[] = $tempRow;
    }
    $response_data['error'] = false;
    $response_data['data'] = $rows1;
    print_r(json_encode($response_data));
}

/*
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_fund_transfers'])){
    
    /* 
    4. get_fund_transfers
        accesskey:90336
        id:82
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_fund_transfers:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where f.`id` like '%".$search."%' OR d.`name` like '%".$search."%' OR f.`message` like '%".$search."%' OR d.`mobile` like '%".$search."%' OR d.`address` like '%".$search."%' OR f.`opening_balance` like '%".$search."%' OR f.`closing_balance` like '%".$search."%' OR d.`balance` like '%".$search."%' OR f.`date_created` like '%".$search."%'" ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(f.id) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['address'] = $row['address'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['type'] = $row['type'];
		$tempRow['amount'] = $row['amount'];
		$tempRow['opening_balance'] = $row['opening_balance'];
		$tempRow['closing_balance'] = $row['closing_balance'];
		$tempRow['status'] = $row['status'];
		$tempRow['message'] = $row['message'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}
/* 
---------------------------------------------------------------------------------------------------------
*/
	
if(isset($_POST['update_delivery_boy_profile'])){
    
    /* 
    5.update_delivery_boy_profile
        accesskey:90336
        id:87
        name:any value       
        address:Jl Komplek Polri   
        old_password:        // {optional}
        update_password:        // {optional}
        confirm_password:        // {optional}
        update_delivery_boy_profile:1
    */
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $name=$db->escapeString(trim($fn->xss_clean($_POST['name'])));
    $address =$db->escapeString(trim($_POST['address']));
    $old_password = (isset($_POST['old_password']) || !empty(trim($_POST['old_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['old_password']))):"";
    $update_password = (isset($_POST['update_password']) || !empty(trim($_POST['update_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['update_password']))):"";
    $confirm_password = (isset($_POST['confirm_password']) || !empty(trim($_POST['confirm_password'])))?$db->escapeString(trim($fn->xss_clean($_POST['confirm_password']))):"";
    $change_password = false;
    
    /* check if id is not empty and there is valid data in it */
    if(!isset($_POST['id']) || empty(trim($_POST['id'])) || !is_numeric($_POST['id'])){
        $json_response['error'] = true;
        $json_response['message'] = "Invalid Id of Delivery Boy";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    $sql="SELECT * from delivery_boys where id='$id'";
    $db->sql($sql);
    $res_id = $db->getResult();
    $num = $db->numRows($res);
    if($num != 1){
        $json_response['error'] = true;
        $json_response['message'] = "Delivery Boy is not Registered.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* if any of the password field is set and old password is not set */
    if(( !empty($confirm_password) || !empty($update_password)) && empty($old_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Please enter old password.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* either of the password field is not empty and is they don't match */
    if(( !empty($confirm_password) || !empty($update_password)) && ($update_password != $confirm_password)){
        $json_response['error'] = true;
        $json_response['message'] = "Password and Confirm Password mismatched.";
        print_r(json_encode($json_response));
        return false;
        exit();
    }
    
    /* when all conditions are met check for old password in database */
    if( !empty($confirm_password) && !empty($update_password) && !empty($old_password)){
        $old_password = md5($old_password);
        $sql = "Select password from `delivery_boys` where id = '$id' and password = '$old_password' ";
        $db->sql($sql);
        $res = $db->getResult();
     
        if(empty($res)){
            $json_response['error'] = true;
            $json_response['message'] = "Old password mismatched.";
            print_r(json_encode($json_response));
            return false;
            exit();
        }
        $change_password = true;
        $confirm_password = md5($confirm_password);
    }
    
    $sql = "Update delivery_boys set `name`='".$name."',`address`='".$address."' ";
    $sql .= ( $change_password )?", `password`='".$confirm_password."' ":"";
    $sql .= " where `id` = '$id' ";
    
    if($db->sql($sql)){
        $json_response['error'] = false;
        $json_response['message'] = "Information Updated Successfully.";
        $json_response['message'] .= ( $change_password )?" and password also updated successfully.":"";
    } else {
        $json_response['error'] = true;
        $json_response['message'] = "Some Error Occurred! Please Try Again.";
    }
    print_r(json_encode($json_response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_order_status']) && isset($_POST['id'])) {
    
    /* 
    6.update_order_status
        accesskey:90336
		update_order_status:1
		id:26
		status:cancelled
		delivery_boy_id:40        // {optional}
    */
	$id = $db->escapeString($fn->xss_clean($_POST['id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	$delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
	
	if(isset($_POST['delivery_boy_id']) && $_POST['delivery_boy_id'] != ''){
		$sql="UPDATE orders SET `delivery_boy_id`='".$delivery_boy_id."' WHERE id=".$id;
		$db->sql($sql);	
		$sql_get_name="select name from delivery_boys where id='$delivery_boy_id'";
		$db->sql($sql_get_name);
		$delivery_boy_name = $db->getResult();
		$message_delivery_boy = "Hello, Dear ".ucwords($delivery_boy_name[0]['name']).", You have new order to deliver. Here is your order ID : #".$id.". Please take a note of it.";
	//	$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'delivery_boys',$id); 
       // $fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
	}
    $sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id='".$id."' && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if($res_cancelled[0]['cancelled']>0){
    	$response['error'] = true;
		$response['message'] = 'Could not update order status!';
		print_r(json_encode($response));
		return false;
	}
    $sql="select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status from orders where id=".$id;
	$db->sql($sql);
	$res = $db->getResult();
	
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
    			$response['message'] = 'Delivery Boy updated, But order status not due to duplicate status!';
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
        	        if($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']){
        	            $stock = $fn->convert_to_parent($res_oi[$i]['measurement'],$res_oi[$i]['measurement_unit_id']);
        	            $stock = $stock * $res_oi[$i]['quantity'];
        	            $sql = "UPDATE product_variant SET stock = stock + ".$stock." WHERE product_id='".$res_oi[$i]['product_id']."'";
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
                $user_id = $res[0]['user_id'];
                $total = $res[0]['total']+$res[0]['delivery_charge']+$res[0]['tax_amount'];
                $user_wallet_balance = $fn->get_wallet_balance($user_id);
                $new_balance = $user_wallet_balance + $total;
                $fn->update_wallet_balance($new_balance,$user_id);
        	    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
            }else{
                if($res[0]['wallet_balance']!=0){
                    $user_id = $res[0]['user_id'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
                    $fn->update_wallet_balance($new_balance,$user_id);
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$sub_total,'Balance credited against item cancellation.');
                }
            }
        }
    	if($postStatus=='delivered'){
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
                            $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                        $message .= "<tr><td style='font-weight:bold' colspan='4' align='right'>Final Total (₹)</td>
                        <td align='right'></td>
                                                <td align='right'>&nbsp;= ".ceil($final_total)."</td>
                        </tr></table>";
			send_email($email,$subject,$message);
}
    		$sql = "SELECT delivery_boy_id,final_total FROM orders WHERE id=".$id;
    		$db->sql($sql);
    		$res_boy = $db->getResult();
    		if($res_boy[0]['delivery_boy_id']!=0){
    			$sql = "SELECT bonus FROM delivery_boys WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$res_bonus = $db->getResult();
    		    $reward = $res_boy[0]['final_total']/100*$res_bonus[0]['bonus'];
    			$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id=".$res_boy[0]['delivery_boy_id'];
    			$db->sql($sql);
    			$comission=$fn->add_delivery_boy_commission($delivery_boy_id,'credit',$reward,$message='Order Delivery Boy Commission.');
    			
    			$sql = "SELECT value FROM `settings` WHERE variable='currency'";
    			$db->sql($sql);
    			$currency = $db->getResult();
    		    $message_delivery_boy = "Hello, Dear ".ucwords($res_bonus[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your Commission of".$reward." is credited. Please take a note of it.";
    			$fn->send_notification_to_delivery_boy($delivery_boy_id,"Your commission ".$reward." ".$currency[0]['value']." has been credited","$message_delivery_boy",'delivery_boys',$id);
    			//$fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');

    		}
    		if($config['is-refer-earn-on']==1){
    			if($res_boy[0]['final_total']>=$config['min-refer-earn-order-amount']){
    				if($res_count[0]['total']==0){
    					if($res_user[0]['friends_code'] != ''){
    						if($config['refer-earn-method']=='percentage'){
    							$percentage = $config['refer-earn-bonus'];
    							$bonus_amount = $res_boy[0]['final_total']/100*$percentage;
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
    						    $fn->add_wallet_transaction($friend_user[0]['id'],'credit',floor($bonus_amount),'Refer & Earn Bonus on first order by '.ucwords($res_data[0]['name']));
    						
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
    	$i = sizeof($status);
        $currentStatus = $status[$i-1][0];
        $final_status = array(
        	'active_status' => $currentStatus
    	);
     	if($db->update('orders',$final_status,'id='.$id)){
    		$response['error'] = false;
    		$response['message'] = $postStatus=='cancelled'?"Order has been cancelled!":"Order updated successfully.";
    		$res = $db->getResult();
    		$sql = "select name,email,mobile,country_code from `users` where id=".$user_id;
    		$db->sql($sql);
    		$res_user = $db->getResult();
    		
    		$to = $res_user[0]['email'];
    		$mobile = $res_user[0]['mobile'];
    		$country_code = $res_user[0]['country_code'];
    		$subject = "Your order has been ".ucwords($postStatus);
    		if($postStatus=='Delivered' && $res['payment_method']=='cod'){
    		    $message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Cash Payment Received for this order. Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		}else{
    		    $message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";    
    		}
    		
    		$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
    		$fn->send_order_update_notification($user_id,"Your order has been ".ucwords($postStatus),$message,'order');
    		send_email($to,$subject,$message);
    		
    		$message = "Hello, Dear ".ucwords($res_user[0]['name']).", Here is the new update on your order for the order ID : #".$id.". Your order has been ".ucwords($postStatus).". Please take a note of it.";
    		$message .= "Thank you for using our services! Contact us for more information";
    		//send_email($to,$subject,$message);
    		print_r(json_encode($response));
    	} else {
    		$response['error'] = true;
    		$response['message'] = "Delivery Boy updated, But Could not update order status. Try again!";
    		print_r(json_encode($response));
    	}
    }else{
		$response['error'] = true;
		$response['message'] = "Sorry Invalid order ID";
		print_r(json_encode($response));
	}
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['delivery_boy_forgot_password']) && isset($_POST['mobile'])) {
    
    /* 
    7.delivery_boy_forgot_password
        accesskey:90336
		mobile:8989898989
		password:1234567
		delivery_boy_forgot_password:1
    */
    if(empty($_POST['password'])){
        $response['error'] = true;
    	$response['message'] = "Password should be filled!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    $password = md5($db->escapeString($fn->xss_clean($_POST['password'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
	    $sql_update = "UPDATE `delivery_boys` SET `password`='$password' WHERE `mobile`='$mobile'";	
	    $db->sql($sql_update);
		$response["error"]   = false;
		$response["message"] = "Password updated successfully";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number id not Registered!";
	}
	print_r(json_encode($response));
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['get_notifications'])){
    
    /* 
    8. get_notifications
        accesskey:90336
        id:114
        offset:0        // {optional}
        limit:10        // {optional}
        
        sort:id           // {optional}
        order:DESC / ASC            // {optional}
        
        search:search_value         // {optional}
        get_notifications:1
        
    */
    
    $json_response=array();
    $id =  $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    $where = '';
    $offset = ( isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
    $limit = ( isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit']) ) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
    
    $sort = ( isset($_POST['sort']) && !empty(trim($_POST['sort'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
    $order = ( isset($_POST['order']) && !empty(trim($_POST['order'])) ) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
    if(isset($_POST['search']) && !empty($_POST['search'])){
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `message` like '%".$search."%' OR `type` like '%".$search."%' OR `date_created` like '%".$search."%'  " ;
	}
	
    if(empty($where)){
		$where .= " WHERE delivery_boy_id = ".$id;
	}else{
		$where .= " AND delivery_boy_id = ".$id;
	}
	
	$sql = "SELECT COUNT(id) as total FROM `delivery_boy_notifications` ".$where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach($res as $row)
		$total = $row['total'];
 	$sql = "SELECT * FROM `delivery_boy_notifications` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
	$db->sql($sql);
	$res = $db->getResult();
	
	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach($res as $row){
		$tempRow['id'] = $row['id'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['order_id'] = $row['order_id'];
		$tempRow['title'] = $row['title'];
		$tempRow['message'] = $row['message'];
		$tempRow['type'] = $row['type'];
		$tempRow['date_created'] = $row['date_created'];
		
		$rows[] = $tempRow;
	}
	$json_response['error'] = false;
	$json_response['data'] = $rows;
	print_r(json_encode($json_response));

}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['update_delivery_boy_fcm_id'])){
     /* 
    9.update_delivery_boy_fcm_id
        accesskey:90336
        id:114
        fcm_id:YOUR_FCM_ID
        update_delivery_boy_fcm_id:1
    */
    
    if(empty($_POST['fcm_id'])){
        $response['error'] = true;
    	$response['message'] = "Please pass the fcm_id!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    
    $id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
    if(isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])){
        $fcm_id = $db->escapeString($$fn->xss_clean($_POST['fcm_id']));
        $sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '".$id."'";
	    if($db->sql($sql1)){
	        $response['error'] = false;
            $response['message'] = "Delivery Boy fcm_id Updeted Susseccfully.";
            print_r(json_encode($response));
	    } else {
	        $response['error'] = true;
            $response['message'] = "Can not update fcm_id of delivery boy.";
            print_r(json_encode($response));
	    }
   }
}

/* 
---------------------------------------------------------------------------------------------------------
*/

if(isset($_POST['check_delivery_boy_by_mobile']) && isset($_POST['mobile'])) {
    
    /* 
    10.check_delivery_boy_by_mobile
        accesskey:90336
		mobile:8989898989
		check_delivery_boy_by_mobile:1
    */
  
    if(empty($_POST['mobile'])){
        $response['error'] = true;
    	$response['message'] = "Mobile Number id not passed!";
    	print_r(json_encode($response));
    	return false;
    	exit();
    }
    //$id = $db->escapeString($_POST['id']);
    $mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
    
    $sql="SELECT mobile from delivery_boys where mobile='$mobile'";
    $db->sql($sql);
    $res_mobile = $db->getResult();
    
	if($res_mobile[0]['mobile'] == $mobile){
		$response["error"]   = false;
		$response["message"] = "Mobile number is Registered.";
	}else{
		$response["error"]   = true;
		$response["message"] = "Mobile number is not Registered!";
	}
	print_r(json_encode($response));
}

if(isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
	$order_item_id = $db->escapeString($fn->xss_clean($_POST['order_item_id']));
	$order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	
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
    	if($active_status=='delivered'){
    	    $response['error'] = true;
        	$response['message'] = 'Order cancel Not Allowed After delivered';
        	$response['subtotal'] = 0;
        	print_r(json_encode($response));
        	return false;
    	}
    	if($postStatus == 'cancelled'){
    	    $sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id='.$order_id;
    	    $db->sql($sql);
    	    $res_order = $db->getResult();
    	    //print_r($res_order[0]['total']);die;
    	    $sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`='.$order_item_id;
    	    $db->sql($sql);
    	    $res_oi = $db->getResult();
    	    $price = $res_oi[0]['discounted_price']==0?$res_oi[0]['price']:$res_oi[0]['discounted_price'];
    	    $total = $res_order[0]['total'];
    	    $final_total = $res_order[0]['final_total'];
    	    $delivery_charge = $res_order[0]['delivery_charge'];
    	   // echo $total - $price;die;
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
    	            $stock = $fn->convert_to_parent($res_oi[0]['measurement'],$res_oi[0]['measurement_unit_id']);
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
            
        	$sql = "SELECT id FROM order_items WHERE order_id=".$order_id;
        	$db->sql($sql);
        	$total = $db->numRows();
        	$sql = "SELECT id FROM `order_items` WHERE order_id=".$order_id." && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
        	$db->sql($sql);
        	$cancelled = $db->numRows();
        	//print_r($cancelled." - ".$total);die;
        	if($cancelled==$total){
        	   // print_r($res_order);
        	    if($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] !='COD'){
                	/* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    $total_amount = $res_order[0]['total']+$res_order[0]['delivery_charge']+$res_order[0]['tax_amount'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = $user_wallet_balance + $total_amount;
                    // return false;
                    $fn->update_wallet_balance($new_balance,$user_id);
                    /* add wallet transaction */
            	    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$total_amount,'Balance credited against item cancellation.');
                }else{
                if($res_order[0]['wallet_balance']!=0){
                    /* update user's wallet */
                    $user_id = $res_order[0]['user_id'];
                    // $total = $res[0]['total'];
                    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
                    // echo $new_balance;die;
                    $fn->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$res_order[0]['wallet_balance'],'Balance credited against item cancellation.');
                }
                    
           }
        	    
            	$data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => $currentStatus
        	        );
                $db->update('orders',$data_order,'id='.$order_id);
        	}else{
        	    $user_id = $res_order[0]['user_id'];
        	    $user_wallet_balance = $fn->get_wallet_balance($user_id);
                    $new_balance = ($user_wallet_balance + $result[0]['sub_total']);
                    // echo $new_balance." - ".$user_id;die;
                    $fn->update_wallet_balance($new_balance,$user_id);
        	        /* add wallet transaction */
        		    $wallet_txn_id = $fn->add_wallet_transaction($user_id,'credit',$result[0]['sub_total'],'Balance credited against item cancellation.');
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
        	$fn->store_return_request($result[0]['user_id'],$order_id,$order_item_id);
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


if(isset($_POST['enable_pay_button'])){
    $order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
    $delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
    $sql = "SELECT payment_method, active_status, final_total FROM orders WHERE id =".$order_id." AND delivery_boy_id=".$delivery_boy_id;
	$db->sql($sql);
	$res = $db->getResult();
	if(!empty($res)){
	    $order=$res[0];
	    if($order['payment_method']=='cod' && $order['active_status']!='cancelled'){
	        $sql = "UPDATE orders SET payment_request='1' WHERE id =".$order_id;
	        $db->sql($sql);
	        $response['error'] = false;
        	$response['message'] = 'Payment Request enabled For this Order.';
        	$response['payable_amt'] = $order['final_total'];
        	print_r(json_encode($response));
        	return false;
	    }else{
	        $response['error'] = true;
        	$response['message'] = 'Something Wrong';
        	print_r(json_encode($response));
        	return false;
	    }
	}
    
}

/*if(isset($_POST['test'])) {
    // echo $fn->add_delivery_boy_commission('114','credit',12,'Test Order commission');
    $a = $fn->send_notification_to_delivery_boy('114','test notification ','Sample notification goes here','order_status',612);
    echo $a;
}*/



?>