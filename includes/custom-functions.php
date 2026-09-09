<?php
/*
functions
---------------------------------------------
1. xss_clean_array($array)
0. xss_clean($data)
1. get_product_by_id($id=null)
2. get_product_by_variant_id($arr)
3. convert_to_parent($measurement,$measurement_unit_id)
4. rows_count($table,$field = '*',$where = '')
5. get_configurations()
6. get_balance($id)
7. get_bonus($id)
8. get_wallet_balance($id)
9. update_wallet_balance($balance,$id)
10. add_wallet_transaction($id,$type,$amount,$message,$status = 1)
11. update_order_item_status($order_item_ids,$order_id,$status)
12. validate_promo_code($user_id,$promo_code,$total)
13. get_settings($variable,$is_json = false)
14. send_order_update_notification($uid,$title,$message,$type)
15. send_notification_to_delivery_boy($uid,$title,$message,$type,$order_id)
16. get_promo_details($promo_code)
17. store_return_request($user_id,$order_id,$order_item_id)
18. get_role($id)
19. get_permissions($id)
20. add_delivery_boy_commission($id,$type,$amount,$message,$status = "SUCCESS")
21. store_delivery_boy_notification($delivery_boy_id,$order_id,$title,$message,$type)

*/
include_once('crud.php');
require_once('firebase.php');
require_once ('push.php');
require_once 'functions.php';
$fn = new functions;


class custom_functions{
    protected $db;
    function __construct(){
        $this->db = new Database();
        $this->db->connect();
        // date_default_timezone_set('Asia/Kolkata');
        } 
    
    function xss_clean_array($array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->xss_clean($value);
        }
        return $array;
    }
    
    function xss_clean($data)
    {
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    }

    function get_product_by_id($id=null){
         if(!empty($id)){
            $sql="SELECT * FROM products WHERE id=".$id;
         }else{
             $sql="SELECT * FROM products";
         }
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $product = array();
        $i=1;
        foreach($res as $row){
            $sql = "SELECT *,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv WHERE pv.product_id=".$row['id'];
            $this->db->sql($sql);
            $product[$i] = $row;
            $product[$i]['variant'] = $this->db->getResult();
            $i++;
        }
        if(!empty($product)){
            return $product;
        }
    }
    function get_product_by_variant_id($arr){
        $arr = stripslashes($arr);
        if(!empty($arr)){
            $arr = json_decode($arr,1);
            // print_r($arr);
            $i=0;
            foreach($arr as $id){
                $sql="SELECT *,pv.id,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv JOIN products p ON pv.product_id=p.id WHERE pv.id=".$id;
                $this->db->sql($sql);
                $res[$i] = $this->db->getResult()[0];
                $i++;
            }
            if(!empty($res)){
                return $res;
            }
        }
        
    }

    function convert_to_parent($measurement,$measurement_unit_id){
        $sql="SELECT * FROM unit WHERE id=".$measurement_unit_id;
        $this->db->sql($sql);
        $unit = $this->db->getResult();
        if(!empty($unit[0]['parent_id'])){
            $stock=$measurement/$unit[0]['conversion'];
        }else{
            $stock = ($measurement)*$unit[0]['conversion'];
        }
            return $stock;
    }
    function rows_count($table,$field = '*',$where = ''){
        // Total count
        if(!empty($where))$where = "Where ".$where;
        $sql = "SELECT COUNT(".$field.") as total FROM ".$table." ".$where;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        foreach($res as $row)
        return $row['total'];
    }
//     function orders_count(){
//         $where = '';
//         if($_SESSION['role'] == 'seller'){
//             $id =$_SESSION['id'];
//             $where = " WHERE o.seller_id='$id'";
//         }
//         $sql = "SELECT COUNT(o.id) as total FROM `orders` o JOIN users u ON u.id=o.user_id $where";
// 		$this->db->sql($sql);
//         $res = $this->db->getResult();
// 		$total = isset($res)?$res[0]['total']:0;
//         return $total;
//     }

    function orders_count(){
        $where = [];
        if($_SESSION['role'] == 'seller'){
            $id = $_SESSION['id'];
            $where[] = "o.seller_id='$id'";
        }
        $where[] = "o.date_added >= CURDATE()";
        $where[] = "o.date_added < CURDATE() + INTERVAL 1 DAY";
        $where_sql = '';
        if(!empty($where)){
            $where_sql = " WHERE " . implode(' AND ', $where);
        }
        $sql = "SELECT COUNT(o.id) as total FROM orders o JOIN users u ON u.id = o.user_id $where_sql";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        $total = isset($res[0]['total']) ? $res[0]['total'] : 0;
        return $total;
    }
    public function get_configurations(){
        $sql = "SELECT value FROM settings WHERE `variable`='system_timezone'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return json_decode($res[0]['value'],true);
        }else{
            return false;
        }
    }
    public function get_balance($id){
        $sql = "SELECT balance FROM delivery_boys WHERE id=".$id;
        // echo $sql;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return !empty($res[0]['balance'])?$res[0]['balance']:0;
        }else{
            return false;
        }
    }
    public function get_bonus($id){
        $sql = "SELECT bonus FROM delivery_boys WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return !empty($res[0]['bonus'])?$res[0]['bonus']:0;
        }else{
            return false;
        }
    }
    public function get_wallet_balance($id){
        $sql = "SELECT balance FROM users WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return !empty($res[0]['balance'])?$res[0]['balance']:0;
        }else{
            return 0;
        }
    }
    public function update_wallet_balance($balance,$id){
        $data = array(
            'balance'=>$balance 
        );
        if($this->db->update('users',$data,'id='.$id))
            return true;
         else
            return false;
    }
    
    public function add_wallet_transaction($id,$type,$amount,$message='Used against Order Placement',$status = 1){
        $data = array(
            'user_id'=> $id,
            'type'=> $type,
            'amount'=> $amount,
            'message'=> $message,
            'status'=> $status
        );
        $this->db->insert('wallet_transactions',$data);
        return $this->db->getResult()[0];
    }
    
    public function get_loyalty_points($id){
        $sql = "SELECT earned_loyalty_points FROM users WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return !empty($res[0]['earned_loyalty_points'])?$res[0]['earned_loyalty_points']:0;
        }else{
            return 0;
        }
    }
    
    public function update_loyalty_points($redeem_loyalty_points,$idupdate_loyalty_points){
        
        $sql = "SELECT * FROM users WHERE id=".$id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        
        $updated_balance_loyalty=($res[0]['balance_loyalty_points'])-($redeem_loyalty_points);
        $updated_redeem_loyalty=($res[0]['redeem_loyalty_points'])+($redeem_loyalty_points);
        $data = array(
            // 'balance_loyalty_points'=>$updated_balance_loyalty, 
            'redeem_loyalty_points'=>$updated_redeem_loyalty
        );
        
        // print_r($data);
        // exit;
        if($this->db->update('users',$data,'id='.$id))
            return true;
        else
            return false;
    }
    public function update_loyalty_points_after_insert($total,$redeem_loyalty_points,$user_id,$order_id){
        // print_r("ram");
        $sql = "SELECT * FROM loyalty_conversion where id=1"; 
        $this->db->sql($sql);
        $loyalty_conversion = $this->db->getResult(); 
        $loyalvalue=$loyalty_conversion[0]['loyalty_conversion'];
        
        
        $updateloyaltypoint=round(($total)/$loyalvalue);
        $sql = "SELECT * FROM users WHERE id=".$user_id;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        
        // print_r("ram");
        // print_r($res);
        // exit;
        
        $new=($res[0]['earned_loyalty_points'])+($updateloyaltypoint);
        $newupdated_balance_loyalty=($new)-($res[0]['redeem_loyalty_points']);
        
        
        $newredeem=($res[0]['redeem_loyalty_points'])+$redeem_loyalty_points;
        $new=($updateloyaltypoint)+($res[0]['earned_loyalty_points']);
        $bal=($new)-($newredeem);
        
        
        $data = array(
            'balance_loyalty_points'=>$bal, 
            'earned_loyalty_points'=>$new,
            'redeem_loyalty_points'=>$newredeem
        );
        if($redeem_loyalty_points =="" && $redeem_loyalty_points == 0){
            $msg=$res[0]['name']." Earned ".$updateloyaltypoint." Loyalty Points Against ORDER ID: ".$order_id;
        }else{
            $msg=$res[0]['name']." Earned ".$updateloyaltypoint." Loyalty Points and Redeem ".$redeem_loyalty_points." Against ORDER ID: ".$order_id;
        }
        $data1 = array(
            'user_id'=>$user_id, 
            'user_name'=>$res[0]['name'],
            'order_id'=>$order_id,
            'message'=>$msg,
            'Redeem_Loyalty_Points'=>$redeem_loyalty_points,
            'earned_loyalty_points'=>$updateloyaltypoint
        );
        $this->db->insert('Loyalty_Points_Transaction',$data1);
        $transupdate=$this->db->getResult()[0];
    
        if($this->db->update('users',$data,'id='.$user_id))
            return true;
        else
            return false;
        
    }
    public function update_order_item_status($order_item_ids,$order_id,$status){
        $order_item_ids = stripslashes($order_item_ids);
        if(!empty($order_item_ids)){
            $order_item_ids = json_decode($order_item_ids,1);
            
        }
        $order_item_ids = explode(',',$order_item_ids);
        $status[] = array( $status,date("d-m-Y h:i:sa") );
        $status = json_encode($status);
        $sql = "update order_items set status = '".$status."' WHERE id IN($order_item_ids)";
        echo $sql;
        return false;
    }
    
    public function validate_promo_code($user_id,$promo_code,$total,$seller_id){
        $seller_id = (int)$seller_id;
        $sql = "select * from promo_codes where promo_code='".$promo_code."' and FIND_IN_SET($seller_id, seller_ids)";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(empty($res)){
            $response['error'] = true;
            $response['message'] = "Invalid promo code.";
            return $response;
            exit();
        }
        if($res[0]['status']==0){
            $response['error'] = true;
            $response['message'] = "This promo code is either expired / invalid.";
            return $response;
            exit();
        }
        
        $sql = "select id from users where id='".$user_id."'";
        $this->db->sql($sql);
        $res_user = $this->db->getResult();
        if(empty($res_user)){
            $response['error'] = true;
            $response['message'] = "Invalid user data.";
            return $response;
            exit();
        }
        
        $start_date = $res[0]['start_date'];
        $end_date = $res[0]['end_date'];
        $date = date('Y-m-d h:i:s a');
        
        if($date<$start_date){
            $response['error'] = true;
            $response['message'] = "This promo code can't be used before ".date('d-m-Y',strtotime($start_date))."";
            return $response;
            exit();
        }
        if($date>$end_date){
            $response['error'] = true;
            $response['message'] = "This promo code can't be used after ".date('d-m-Y',strtotime($end_date))."";
            return $response;
            exit();
        }
        if($total<$res[0]['minimum_order_amount']){
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for order amount greater than or equal to ".$res[0]['minimum_order_amount']."";
            return $response;
            exit();
    
        }
        //check how many users have used this promo code and no of users used this promo code crossed max users or not
        $sql = "select id from orders where promo_code='".$promo_code."' GROUP BY user_id";
        $this->db->sql($sql);
        $res_order = $this->db->numRows();
        
        if($res_order>=$res[0]['no_of_users']){
            $response['error'] = true;
            $response['message'] = "This promo code is applicable only for first ".$res[0]['no_of_users']." users.";
            return $response;
            exit();
    
        }
        //check how many times user have used this promo code and count crossed max limit or not
        if($res[0]['repeat_usage']==1){
            $sql = "select id from orders where user_id=".$user_id." and promo_code='".$promo_code."'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if($total_usage>=$res[0]['no_of_repeat_usage']){
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for ".$res[0]['no_of_repeat_usage']." times.";
                return $response;
                exit();
            }
    
    
        }
        //check if repeat usage is not allowed and user have already used this promo code 
        if($res[0]['repeat_usage']==0){
            $sql = "select id from orders where user_id=".$user_id." and promo_code='".$promo_code."'";
            $this->db->sql($sql);
            $total_usage = $this->db->numRows();
            if($total_usage>=1){
                $response['error'] = true;
                $response['message'] = "This promo code is applicable only for 1 time.";
                return $response;
                exit();
            }
    
    
        }
        if($res[0]['discount_type']=='percentage'){
            $percentage = $res[0]['discount'];
            $discount = $total/100*$percentage;
            if($discount>$res[0]['max_discount_amount']){
                $discount=$res[0]['max_discount_amount'];
            }
        }else{
            $discount=$res[0]['discount'];
        }
        $discounted_amount = $total - $discount;
        $response['error'] = false;
        $response['message'] = "promo code applied successfully.";
        $response['promo_code'] = $promo_code;
        $response['promo_code_message'] = $res[0]['message'];
        $response['total'] = $total;
        $response['discount'] = "$discount";
        $response['discounted_amount'] = "$discounted_amount";
        return $response;
        exit();
    }
    public function get_settings($variable,$is_json = false){
        if($variable=='logo' || $variable=='Logo'){
            $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
        }else{
            $sql = "SELECT value FROM `settings` WHERE `variable`='$variable'";
        }
        
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['value'])){
            if($is_json)
                return json_decode($res[0]['value'],true);
            else
                return $res[0]['value'];
        }else{
            return false;
        }
    }
    
    /* send email notification for the order received */
    
     public function send_order_confirmation($order_id){
         
           $sql="SELECT oi.*,oi.id AS order_item_id,v.product_id,v.type, v.measurement,v.measurement_unit_id,v.stock_unit_id,o.*,o.total as order_total,o.wallet_balance,oi.active_status as oi_active_status,u.email,u.name as uname,u.country_code,o.status as order_status,p.name as pname,p.hsn as hsn ,oi.sgst as sgstp,oi.cgst as cgstp,oi.igst as igstp,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name 
                FROM `order_items` oi
                JOIN users u ON u.id=oi.user_id
                JOIN product_variant v ON oi.product_variant_id=v.id
                JOIN products p ON p.id=v.product_id
                JOIN orders o ON o.id=oi.order_id
            WHERE o.id=".$order_id;
            $this->db->sql($sql);
            $res= $this->db->getResult();
			
			$to = $res[0]['email'];
			$mobile = $res[0]['mobile'];
			$country_code = $res[0]['country_code'];
            $usersTimezone = new DateTimeZone('Asia/Kolkata');
            $l10nDate = new DateTime($res[0]['date_added']);
            $l10nDate->setTimeZone($usersTimezone);
            $order_date= $l10nDate->format('Y-m-d h:i A');
			$subject = "Order received successfully";
			$message = "<div style='justify-content: left;text-align: left;'><div class='card' style='display: inline-block; flex-direction: column; justify-content: left; align-items: left; padding: 40px; position: absolute; width: 596.92px;left: 422px; top: 40px; background: #FFFFFF; box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.08); border-radius: 8px;'>"; 
			$message .= "<p><h4>Hi ".ucwords($res[0]['uname']).",</h4>&nbsp;&nbsp;&nbsp; We have received your order successfully.</p>";
			$message .= "<p><b>Order Summary</b> <table><tr><th>Order ID :</th><td> #".$order_id."</td></tr><tr><th>Order Date :</th><td> ".$res[0]['date_added']."</td></tr><tr><th>Order Total :</th><td> ".$res[0]['final_total']."</td></tr></table></p>";
		
			$message .="<div style=''> <table style='width: -webkit-fill-available;'><thead><th>Items</th><th>Qty</th><th style='text-align:right'>Amount</th></thead><tbody>";
			for($i=0;$i<count($res);$i++){
				$product_id = $res[$i]['product_id'];
				// $unit = (float) filter_var( $res[$i][2], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) ; // float(55.35) 
				$measurement = $res[$i]['measurement'];
				$product_variant_id = $res[$i]['id'];
				// echo $product_variant_id;
				$measurement_unit_id = $res[$i]['measurement_unit_id'];
				$stock_unit_id = $res[$i]['stock_unit_id'];
				$price = $res[$i]['price'];
				$discounted_price = $res[$i]['discounted_price'];
				// $seller_id = $res[$i]['seller_id'];
				$type = $res[$i]['type'];
			
				$quantity = $res[$i]['quantity'];
				// print_r($res[0]['price']);
				$price = $res[$i]['discounted_price']==0?$res[$i]['price']:$res[$i]['discounted_price'];
				$message .= "<tr><td>".$res[$i]['pname']."</td><td>".$quantity."</td><td style='text-align:right'>".$price*$quantity."</td></tr>";
			}
			$message .= "<tr><td>&nbsp;</td><td style='text-align:right'><b>Total Amount : </b></td><td style='text-align:right'>".$res[0]['total']." </td></tr><tr><td>&nbsp;</td><td style='text-align:right'><b>Delivery Charge : </b></td><td style='text-align:right'>".$res[0]['delivery_charge']." </td></tr><tr><td>&nbsp;</td><td style='text-align:right'><b>Tax Amount : </b></td><td style='text-align:right'>".$res[0]['tax_amount']." </td></tr><tr><td>&nbsp;</td><td style='text-align:right'><b>Discount : </b></td><td style='text-align:right'>".$res[0]['discount']." </td></tr><tr><td>&nbsp;</td><td style='text-align:right'><b>Wallet Used : </b></td><td style='text-align:right'>".$res[0]['wallet_balance']." </td></tr><tr><td>&nbsp;</td><td style='text-align:right'><b>Final Total :</b></td><td style='text-align:right'>".$res[0]['final_total']."</td></tr>";
			$message .="</tbody></table></div><br>";
			$message .= "<br>Payment Method : ".$res[0]['payment_method'];
			$message .= "<br><br>Thank you for placing an order with us!<br><br>You will receive future updates on your order via Email!</div></div>";
		
			send_email($to,$subject,$message);
			
     }
     
    public function send_order_update_notification($uid,$title,$message,$type){
        if($_SERVER['REQUEST_METHOD']=='POST'){
        //hecking the required params 
            //creating a new push
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url).'/';
            
            $push = null;
            //first check if the push has an image with it
                //if the push don't have an image give null in place of image
                $push = new Push(
                    $title,
                    $message,
                    null,
                    $type,
                    null
                );
            //getting the push from push object
            $mPushNotification = $push->getPush();
            
            //getting the token from database object
            $sql="SELECT fcm_id FROM users WHERE id = '".$uid."'";
            $this->db->sql($sql); 
            $res=$this->db->getResult();
            $token = array(); 
            foreach($res as $row){
                if(!empty($row['fcm_id'])){
                    array_push($token, $row['fcm_id']);
                }
            }
            
            //creating firebase class object 
            $firebase = new Firebase(); 
    
            //sending push notification and displaying result 
            $firebase->send($token, $mPushNotification);
            $response['error']=false;
            $response['message']="Successfully Send";
        }else{
            $response['error']=true;
            $response['message']='Invalid request';
        }
        // echo str_replace("\\/","/",json_encode($response['message']));
        // echo(json_encode($response));
    }
    
     public function send_new_order_notification($title,$message,$type,$store_id='0'){
        if($_SERVER['REQUEST_METHOD']=='POST'){
        //hecking the required params 
            //creating a new push
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url).'/';
            
            $push = null;
            //first check if the push has an image with it
                //if the push don't have an image give null in place of image
                $push = new Push(
                    $title,
                    $message,
                    null,
                    $type,
                    null
                );
            //getting the push from push object
            $mPushNotification = $push->getPush();
            
            //getting the token from database object
            if($store_id=='0'){
                $sql="SELECT fcm_id FROM admin";
            }else{
                $sql="SELECT fcm_id FROM stores WHERE id=".$store_id;
            }
            
            $this->db->sql($sql); 
            $res=$this->db->getResult();
            $token = array(); 
            foreach($res as $row){
                if(!empty($row['fcm_id'])){
                    array_push($token, $row['fcm_id']);
                }
                
            }
            
            //creating firebase class object 
            $firebase = new Firebase(); 
    
            //sending push notification and displaying result 
            $firebase->send($token, $mPushNotification);
            $response['error']=false;
            $response['message']="Successfully Send";
        }else{
            $response['error']=true;
            $response['message']='Invalid request';
        }
        // echo str_replace("\\/","/",json_encode($response['message']));
        // echo(json_encode($response));
    }
    
    public function send_notification_to_delivery_boy($delivery_boy_id,$title,$message,$type,$order_id){
        if($_SERVER['REQUEST_METHOD']=='POST'){
        //hecking the required params 
            //creating a new push
            /*dynamically getting the domain of the app*/
            $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
            $url .= $_SERVER['SERVER_NAME'];
            $url .= $_SERVER['REQUEST_URI'];
            $server_url = dirname($url).'/';
            
            $push = null;
            //echo $order_id;
            //first check if the push has an image with it
            //if the push don't have an image give null in place of image
                $push = new Push(
                    $title,
                    $message,
                    null,
                    $type,
                    $order_id
                );
            //getting the push from push object
            $m_push_notification = $push->getPush();
            
            //getting the token from database object
                if ($delivery_boy_id == 0) {
                $sql="SELECT fcm_id FROM delivery_boys WHERE active_status = 'true'";
            } else {
                $sql="SELECT fcm_id FROM delivery_boys WHERE id = '".$delivery_boy_id."' AND active_status = 'true'";
            }
            $this->db->sql($sql); 
            $res=$this->db->getResult();
            $token = array(); 
            foreach($res as $row){
                if(!empty($row['fcm_id'])){
                    array_push($token, $row['fcm_id']);
                }
            }
            
            //creating firebase class object 
            $firebase = new Firebase(); 
    
            //sending push notification and displaying result 
            $firebase->send($token, $m_push_notification);
            $response['error']=false;
            $response['message'] = "Successfully Send";
            //print_r(json_encode($response));
        }else{
            $response['error']=true;
            $response['message']='Invalid request';
           // print_r(json_encode($response));
        }
    }
    
    public function get_promo_details($promo_code){
        $sql = "SELECT * FROM `promo_codes` WHERE `promo_code`='$promo_code'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            return $res;
        }else{
            return false;
        }
    }
    
    public function store_return_request($user_id,$order_id,$order_item_id,$reason=''){
        
        $sql = "select product_variant_id from order_items where id=".$order_item_id;
        $this->db->sql($sql);
        $res=$this->db->getResult();
        $pv_id = $res[0]['product_variant_id'];
        $sql = "select product_id from product_variant where id=".$pv_id;
        $this->db->sql($sql);
        $res=$this->db->getResult();

        $data = array(
            'user_id'=> $user_id,
            'order_id'=> $order_id,
            'order_item_id'=> $order_item_id,
            'product_id'=> $res[0]['product_id'],
            'product_variant_id'=> $pv_id,
            
        );
         //return $reason;
        $this->db->insert('return_requests',$data);
        return $this->db->getResult()[0];
    }
    
    public function get_role($id){
        $sql = "SELECT role FROM admin WHERE id=".$id;
        // echo $sql;
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['role'])){
            return $res[0]['role'];
        }else{
            return 0;
        }
    }
    
    public function get_permissions($id){
        if($_SESSION['role'] == 'seller'){
        $sql = "SELECT permissions FROM admin WHERE id=30";
        }else{
        $sql = "SELECT permissions FROM admin WHERE id=".$id;
        }
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res) && isset($res[0]['permissions'])){
            return json_decode($res[0]['permissions'],true);
        }else{
            return 0;
        }
    }
    
    public function add_delivery_boy_commission($id,$type,$amount,$message,$status = "SUCCESS"){
        $balance=$this->get_balance($id);
        $data = array(
            'delivery_boy_id'=> $id,
            'type'=> $type,
            'opening_balance'=>$balance,
            'closing_balance'=>$balance+$amount,
            'amount'=> $amount,
            'message'=> $message,
            'status'=> $status
        );
        $this->db->insert('fund_transfers',$data);
        return $this->db->getResult()[0];
    }
    
    public function store_delivery_boy_notification($delivery_boy_id,$order_id,$title,$message,$type){
              if ($delivery_boy_id == 0) {
            $sql = "SELECT id FROM delivery_boys WHERE active_status = 'true'";
            $this->db->sql($sql);
            $res = $this->db->getResult();
            foreach ($res as $row) {
                $data = array(
                    'delivery_boy_id'=> $row['id'],
                    'order_id'=> $order_id,
                    'title'=> $title,
                    'message'=> $message,
                    'type'=> $type
                );
                $this->db->insert('delivery_boy_notifications',$data);
            }
            return true;
        } else {

        $data = array(
            'delivery_boy_id'=> $delivery_boy_id,
            'order_id'=> $order_id,
            'title'=> $title,
            'message'=> $message,
            'type'=> $type
        );
        $this->db->insert('delivery_boy_notifications',$data);
        return $this->db->getResult()[0];
        }
    }
    
    public function generateEAN()
    {
        $date = new DateTime();
        $time = $date->getTimestamp();
    
        $code = '20' . str_pad($time, 10, '0');
        $weightflag = true;
        $sum = 0;
    
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag ? 3 : 1);
            $weightflag = !$weightflag;
        }
        $code .= (10 - ($sum % 10)) % 10;
        return $code;
    }
   
    function GetDeliveryDistance($lat1, $lat2, $long1, $long2)
    {
        
        
        //  $settings=$this->get_configurations();
        // // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&mode=driving&language=en&sensor=false&key=AIzaSyBRWiTFuf7Tr1mD2mGUBxqworfOuXVwYe0";
        // $url = "https://maps.googleapis.com/maps/api/distancematrix/json?units=imperial&origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&key=".$settings['store_map_api'];
        // $ch = curl_init();
        // curl_setopt($ch, CURLOPT_URL, $url);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        // $response = curl_exec($ch);
        // curl_close($ch);
        // $response_a = json_decode($response, true);
        
        // // $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
        
        // if (
        //     isset($response_a['rows'][0]['elements'][0]['distance']['value'])
        // ) {
        //     $dist = $response_a['rows'][0]['elements'][0]['distance']['value'];
        // } else {
        //     $dist = 0;
        // }
        // $k_dist =  $dist/1000;
        
        
        $earthRadius = 6371; // in KM

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($long2 - $long1);
    
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
    
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
        $dist = $earthRadius * $c;
        
        return $dist;
    }
   
   public function get_dunzo_delivery_charge($payload){ 
       $dunzo_data_query=$this->db->sql("SELECT * FROM delivery_method WHERE id='1'");
        $res=$this->db->getResult();
        $dunzo_data=$res[0]['dunzo_data'];
        $dunz = json_decode($dunzo_data);
        $dunzo_client_id=$dunz->dunzo_client_id;
        $dunzo_client_secret=$dunz->dunzo_secret_key;
        
       
        $token_data_query=$this->db->sql("SELECT value FROM settings WHERE variable='dunzo_token'");
        $token_data=$this->db->getResult();
        $dunzo_token=$token_data[0]['value'];
        
        //var_dump($payload);die;
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.dunzo.in/api/v2/quote',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($payload),
          CURLOPT_HTTPHEADER => array(
            'client-id: '.$dunzo_client_id,
            'Authorization: '.$dunzo_token,
            'Content-Type: application/json',
            'Accept-Language: en_US'
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        $result = json_decode($response);
        return $result;
    }
    
    
    public function generateBeamsToken($userId) {
        $key = PUSHER_SECRET_KEY; // This should be your secret key
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode(['user_id' => $userId, 'exp' => time() + 7200]); // 2 hours validity
    
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function getAllTokens() {
        $sql = "SELECT `fcm_id` FROM `users` WHERE `fcm_id` IS NOT NULL AND `fcm_id` != ''";
        $this->db->sql($sql); 
        
        $res = $this->db->getResult();
        
        if (empty($res)) {
            echo "No device tokens found!";
            return [];
        }
    
        $tokens = [];
        foreach ($res as $row) {
            array_push($tokens, $row['fcm_id']);
        }
    
        // print_r($tokens); // Debugging output
        return $tokens; 
    }
    
   
}

?>