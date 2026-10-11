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
    // Why the last Google routing call failed; see get_last_maps_error()
    protected $last_maps_error = '';

    // Two-stage order dispatch: Phase-1 reaches only online delivery boys whose
    // live location is within DISPATCH_PHASE1_RADIUS_KM of the pickup and who
    // belong to the order's zone. If the job is still unclaimed after
    // DISPATCH_PHASE1_WAIT_SECONDS, Phase-2 broadcasts it to every online boy
    // across the service area. First to tap Accept keeps it.
    const DISPATCH_PHASE1_RADIUS_KM = 4.0;
    const DISPATCH_PHASE1_WAIT_SECONDS = 60;
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
        if (!is_string($data)) {
            $data = ($data === null || is_scalar($data)) ? (string) $data : '';
        }
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

    public function get_zone_scope($id){
        if(isset($_SESSION['id']) && $_SESSION['role'] == 'sub admin'){
            $this->db->sql("SELECT zone_ids FROM admin WHERE id=".(int)$id);
            $res = $this->db->getResult();
            if(!empty($res) && !empty($res[0]['zone_ids'])){
                $zones = json_decode($res[0]['zone_ids'], true);
                if(is_array($zones) && count($zones) > 0){
                    $zones = array_map('intval', $zones);
                    return array_values(array_filter($zones, function($z){ return $z > 0; }));
                }
            }
        }
        return array();
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
    public function get_meal_availability_filter(){
        /* All products are returned to the app now; availability is decided
           client-side using get_meal_time_slots() + each product's available_time. */
        return '';
    }
    public function get_meal_time_slots(){
        $config = $this->get_configurations();
        $timezone = (!empty($config) && isset($config['system_timezone']) && !empty($config['system_timezone'])) ? $config['system_timezone'] : 'Asia/Kolkata';
        date_default_timezone_set($timezone);
        $meal_slots = array(
            'anytime'   => 0,
            'breakfast' => array('from_time' => '06:00', 'to_time' => '11:00'),
            'lunch'     => array('from_time' => '11:00', 'to_time' => '16:00'),
            'dinner'    => array('from_time' => '16:00', 'to_time' => '23:00')
        );
        $sql = "SELECT value FROM settings WHERE variable='meal_time_slots'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if(!empty($res)){
            $saved = json_decode($res[0]['value'], true);
            if(is_array($saved)){
                if(isset($saved['anytime'])){
                    $meal_slots['anytime'] = (int)$saved['anytime'];
                }
                foreach(array('breakfast','lunch','dinner') as $meal){
                    if(isset($saved[$meal])){
                        foreach(array('from_time','to_time') as $field){
                            if(isset($saved[$meal][$field]) && $saved[$meal][$field] !== ''){
                                $meal_slots[$meal][$field] = $saved[$meal][$field];
                            }
                        }
                    }
                }
            }
        }
        return $meal_slots;
    }
    public function get_meal_availability_info($available_time){
        /* All timing logic lives on the server. Returns:
           available_now : boolean
           label         : display text for the app badge or null when always available */
        $meal_slots = $this->get_meal_time_slots();
        $current = date('H:i');
        $meals = array_filter(array_map('trim', explode(',', (string)$available_time)));
        if(empty($meals)){
            return array('available_now' => true, 'label' => null);
        }
        foreach($meals as $m){
            if(strcasecmp($m, 'anytime') == 0){
                return array('available_now' => true, 'label' => null);
            }
        }
        $specified = array();
        foreach(array('breakfast','lunch','dinner') as $meal){
            foreach($meals as $m){
                if(strcasecmp($m, $meal) == 0){
                    $specified[$meal] = array($meal_slots[$meal]['from_time'], $meal_slots[$meal]['to_time']);
                    break;
                }
            }
        }
        if(empty($specified)){
            return array('available_now' => true, 'label' => null);
        }
        foreach($specified as $range){
            if(strcmp($range[0], $current) <= 0 && strcmp($current, $range[1]) < 0){
                return array('available_now' => true, 'label' => 'Available '.$this->format_meal_window($range[0], $range[1]));
            }
        }
        $next = null;
        $next_min = PHP_INT_MAX;
        foreach($specified as $range){
            $min = $this->minutes_until_meal($current, $range[0]);
            if($min < $next_min){
                $next_min = $min;
                $next = $range;
            }
        }
        if($next !== null){
            return array('available_now' => false, 'label' => 'Available '.$this->format_meal_window($next[0], $next[1]));
        }
        return array('available_now' => false, 'label' => null);
    }
    public function minutes_until_meal($current, $from){
        $parts = explode(':', $current);
        $now_total = (int)$parts[0] * 60 + (int)$parts[1];
        $parts = explode(':', $from);
        $from_total = (int)$parts[0] * 60 + (int)$parts[1];
        $diff = $from_total - $now_total;
        if($diff <= 0){
            $diff += 1440;
        }
        return $diff;
    }
    public function format_meal_window($from, $to){
        $format = function($time){
            $parts = explode(':', $time);
            $h = (int)$parts[0];
            $m = (int)$parts[1];
            $suffix = $h >= 12 ? 'PM' : 'AM';
            $h12 = $h % 12;
            if($h12 == 0){ $h12 = 12; }
            return $h12.':'.str_pad($m, 2, '0', STR_PAD_LEFT).' '.$suffix;
        };
        return $format($from).' - '.$format($to);
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
    
    public function send_notification_to_delivery_boy($delivery_boy_id,$title,$message,$type,$order_id,$order_type='food'){
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
                    $order_id,
                    $order_type
                );
            //getting the push from push object
            $m_push_notification = $push->getPush();
            
            //getting the token from database object
                if ($delivery_boy_id == 0) {
                $order_zone_id = 0;
                if ($order_type == 'parcel') {
                    $this->db->sql("SELECT zone_id FROM parcel_requests WHERE id = '$order_id'");
                } else {
                    $this->db->sql("SELECT zone_id FROM orders WHERE id = '$order_id'");
                }
                $res_zone = $this->db->getResult();
                if (!empty($res_zone) && isset($res_zone[0]['zone_id'])) {
                    $order_zone_id = (int)$res_zone[0]['zone_id'];
                }

                $sql="SELECT fcm_id FROM delivery_boys WHERE active_status = 'true'";
                if ($order_zone_id > 0) {
                    $sql .= " AND (zone_id = 0 OR zone_id IS NULL OR zone_id = '$order_zone_id')";
                }
                
                // Broadcast only to delivery boys whose assigned service covers this order type
                if ($this->delivery_boy_has_service_type()) {
                    $order_type = in_array($order_type, array('food','parcel')) ? $order_type : 'food';
                    $sql .= " AND (service_type = 'both' OR service_type = '".$order_type."')";
                }
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
    
    private function delivery_boy_has_service_type(){
        $sql = "SELECT COUNT(*) as c FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'delivery_boys' AND COLUMN_NAME = 'service_type'";
        $this->db->sql($sql);
        $res = $this->db->getResult();
        return (!empty($res) && isset($res[0]['c']) && $res[0]['c'] > 0);
    }

    /**
     * Run the two-stage dispatch for a single order.
     *
     * Stage '':        order is unassigned and no notification has gone out yet.
     *                  Phase-1 notifies only online boys whose live location is
     *                  within 4 km of the pickup point and who belong to the
     *                  order's zone, then stamps dispatch_stage='phase1' so the
     *                  60 second wait starts. The claim is a guarded UPDATE so
     *                  exactly one caller ever runs Phase-1.
     * Stage 'phase1':  once 60 seconds pass without an acceptance the same job
     *                  is promoted to Phase-2 and broadcast to every online boy
     *                  across the service area (first to accept keeps it).
     * Stage 'phase2'/assigned: nothing left to do.
     *
     * Expected $title/$message can be omitted; sensible defaults are built from
     * the order type so every call site speaks the same copy.
     */
    public function dispatch_new_order($order_type, $order_id, $title = '', $message = ''){
        $order_type = ($order_type === 'parcel') ? 'parcel' : 'food';
        $order_id = (int)$order_id;
        $result = array('type' => $order_type, 'stage' => '', 'notified' => 0);
        if ($order_id <= 0) {
            return $result;
        }

        $row = $this->get_dispatch_context($order_type, $order_id);
        if ($row === null || (int)$row['delivery_boy_id'] !== 0) {
            return $result;
        }

        $stage = $row['dispatch_stage'];
        if ($stage === 'phase2' || ($stage !== 'phase1' && !$this->claim_dispatch_phase1($order_type, $order_id))) {
            return $result;
        }

        if ($stage === 'phase1') {
            $result['stage'] = 'phase1';
            if ($this->promote_dispatch_to_phase2($order_type, $order_id)) {
                $result['stage'] = 'phase2';
                $this->broadcast_dispatch_push($order_type, $order_id, $title, $message);
            }
            return $result;
        }

        // Claimed '' -> 'phase1'. Fresh row data is refetched because the claim
        // happened after get_dispatch_context() read the stage.
        $row = $this->get_dispatch_context($order_type, $order_id);
        $count = ($row !== null) ? $this->dispatch_phase1_nearby($order_type, $row, $title, $message) : 0;
        $result['stage'] = 'phase1';
        $result['notified'] = (int)$count;
        return $result;
    }

    /**
     * Escalates a Phase-1 order to Phase-2 when its 60 second window has run
     * out without an acceptance. Idempotent: the guarded UPDATE returns 1 for
     * exactly one caller, which is the only caller that broadcasts. Cheap
     * enough to call from cron (dispatch-worker.php) and from new-order list
     * fetches so escalation is never starved of a scheduler.
     */
    public function escalate_due_dispatch($order_type, $order_id){
        $order_type = ($order_type === 'parcel') ? 'parcel' : 'food';
        $order_id = (int)$order_id;
        if ($order_id <= 0) {
            return false;
        }
        if ($this->promote_dispatch_to_phase2($order_type, $order_id)) {
            $this->broadcast_dispatch_push($order_type, $order_id, '', '');
            return true;
        }
        return false;
    }

    private function get_dispatch_context($order_type, $order_id){
        if ($order_type === 'parcel') {
            $sql = "SELECT delivery_boy_id, zone_id, dispatch_stage, dispatch_phase1_at,
                           pickup_lat AS p_lat, pickup_lng AS p_lng
                    FROM parcel_requests WHERE id = '$order_id' LIMIT 1";
        } else {
            $sql = "SELECT o.delivery_boy_id, o.zone_id, o.dispatch_stage, o.dispatch_phase1_at,
                           o.latitude AS o_lat, o.longitude AS o_lng,
                           s.latitude AS s_lat, s.longitude AS s_lng
                    FROM orders o LEFT JOIN seller s ON s.id = o.seller_id
                    WHERE o.id = '$order_id' LIMIT 1";
        }
        $this->db->sql($sql);
        $res = $this->db->getResult();
        if (empty($res) || empty($res[0])) {
            return null;
        }
        $row = $res[0];

        if ($order_type === 'parcel') {
            $row['pickup_lat'] = $row['p_lat'];
            $row['pickup_lng'] = $row['p_lng'];
        } else {
            $s_lat = $row['s_lat'];
            $s_lng = $row['s_lng'];
            $o_lat = $row['o_lat'];
            $o_lng = $row['o_lng'];
            $row['pickup_lat'] = (is_numeric($s_lat) && is_numeric($s_lng)) ? $s_lat : $o_lat;
            $row['pickup_lng'] = (is_numeric($s_lat) && is_numeric($s_lng)) ? $s_lng : $o_lng;
        }
        return $row;
    }

    private function claim_dispatch_phase1($order_type, $order_id){
        $status_guard = ($order_type === 'parcel')
            ? "status IN ('pending','accepted')"
            : "active_status NOT IN ('delivered','cancelled','returned')";
        $table = ($order_type === 'parcel') ? 'parcel_requests' : 'orders';
        // Phase-1 timestamp comes from MySQL's own clock (NOW()) so the stored
        // value and the Phase-2 "NOW() - INTERVAL 60 SECOND" check share one
        // timebase. affected_rows from update() is reliable here; ROW_COUNT()
        // cannot be used because crud.php's sql() issues "SET NAMES utf8"
        // before every statement, which resets it.
        $this->db->sql("SELECT NOW() AS db_now");
        $r = $this->db->getResult();
        $db_now = (!empty($r) && isset($r[0]['db_now'])) ? $r[0]['db_now'] : date('Y-m-d H:i:s');
        $this->db->update(
            $table,
            array('dispatch_stage' => 'phase1', 'dispatch_phase1_at' => $db_now),
            "id = '$order_id' AND delivery_boy_id = '0'
             AND (dispatch_stage = '' OR dispatch_stage IS NULL)
             AND " . $status_guard
        );
        $aff = $this->db->getResult();
        return (!empty($aff) && (int)$aff[0] === 1);
    }

    private function promote_dispatch_to_phase2($order_type, $order_id){
        $status_guard = ($order_type === 'parcel')
            ? "status IN ('pending','accepted')"
            : "active_status NOT IN ('delivered','cancelled','returned')";
        $wait = (int)self::DISPATCH_PHASE1_WAIT_SECONDS;
        $this->db->update(
            ($order_type === 'parcel') ? 'parcel_requests' : 'orders',
            array('dispatch_stage' => 'phase2'),
            "id = '$order_id' AND delivery_boy_id = '0' AND dispatch_stage = 'phase1'
             AND dispatch_phase1_at IS NOT NULL
             AND dispatch_phase1_at <= NOW() - INTERVAL $wait SECOND AND " . $status_guard
        );
        $r = $this->db->getResult();
        return (!empty($r) && (int)$r[0] === 1);
    }

    /**
     * Phase-1 target push: online boys in the order's zone whose live location
     * is within 4 km of the pickup point. Boys with no coordinates are never
     * reached because their distance is unknown - they are still online so
     * they naturally surface again in the Phase-2 broadcast.
     */
    private function dispatch_phase1_nearby($order_type, $row, $title, $message){
        $pickup_lat = isset($row['pickup_lat']) ? $row['pickup_lat'] : '';
        $pickup_lng = isset($row['pickup_lng']) ? $row['pickup_lng'] : '';
        if (!is_numeric($pickup_lat) || !is_numeric($pickup_lng)) {
            return 0;
        }
        $order_zone = (int)$row['zone_id'];

        $sql = "SELECT id, fcm_id, latitude, longitude FROM delivery_boys
                WHERE active_status = 'true'
                  AND fcm_id IS NOT NULL AND fcm_id != ''
                  AND latitude IS NOT NULL AND longitude IS NOT NULL";
        if ($order_zone > 0) {
            $sql .= " AND (zone_id IS NULL OR zone_id = '' OR zone_id = '0' OR zone_id = '$order_zone')";
        }
        if ($this->delivery_boy_has_service_type()) {
            $sql .= " AND (service_type = 'both' OR service_type = '" . $order_type . "')";
        }
        $this->db->sql($sql);
        $boys = $this->db->getResult();

        $ids = array();
        $tokens = array();
        $radius = (float)self::DISPATCH_PHASE1_RADIUS_KM;
        foreach ($boys as $boy) {
            $distance = $this->GetDeliveryDistance(
                (float)$boy['latitude'], (float)$pickup_lat,
                (float)$boy['longitude'], (float)$pickup_lng
            );
            if ($distance <= $radius) {
                $ids[] = $boy['id'];
                $tokens[] = $boy['fcm_id'];
            }
        }

        if (empty($tokens)) {
            return 0;
        }

        $copy = $this->dispatch_copy($order_type, $order_id, $title, $message);
        $this->push_to_delivery_boy_tokens($tokens, $copy['title'], $copy['message'], 'delivery_boys', $order_id, $order_type);
        $store_type = ($order_type === 'parcel') ? 'parcel' : 'order_status';
        foreach ($ids as $boy_id) {
            $this->store_delivery_boy_notification($boy_id, $order_id, $copy['title'], $copy['message'], $store_type);
        }
        return count($ids);
    }

    private function broadcast_dispatch_push($order_type, $order_id, $title, $message){
        $copy = $this->dispatch_copy($order_type, $order_id, $title, $message);
        $this->send_notification_to_delivery_boy(0, $copy['title'], $copy['message'], 'delivery_boys', $order_id, $order_type);
        $store_type = ($order_type === 'parcel') ? 'parcel' : 'order_status';
        $this->store_delivery_boy_notification(0, $order_id, $copy['title'], $copy['message'], $store_type);
    }

    private function dispatch_copy($order_type, $order_id, $title, $message){
        if ($order_type === 'parcel') {
            $default_title = 'New Parcel Order';
            $default_message = 'New parcel delivery request #' . $order_id . ' is ready. Open the app to view the details.';
        } else {
            $default_title = 'New Order Ready for Delivery';
            $default_message = 'Hello, A new order (ID : #' . $order_id . ') is ready for delivery. Please check your app to accept it.';
        }
        return array(
            'title' => ($title !== '') ? $title : $default_title,
            'message' => ($message !== '') ? $message : $default_message
        );
    }

    private function push_to_delivery_boy_tokens($tokens, $title, $message, $type, $order_id = 0, $order_type = 'food'){
        $push = new Push(
            $title,
            $message,
            null,
            $type,
            $order_id,
            $order_type
        );
        $m_push_notification = $push->getPush();
        $firebase = new Firebase();
        $firebase->send($tokens, $m_push_notification);
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
    
    public function send_notification_to_seller($seller_id,$title,$message,$type,$order_id=0,$order_type='food'){
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
                    $order_id,
                    $order_type
                );
            //getting the push from push object
            $m_push_notification = $push->getPush();
            
            //getting the token from database object
                $sql="SELECT fcm_id FROM seller WHERE id = '".$seller_id."' AND status = '1'";
            
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
                $order_zone_id = 0;
                if ($type == 'parcel') {
                    $this->db->sql("SELECT zone_id FROM parcel_requests WHERE id = '$order_id'");
                } else {
                    $this->db->sql("SELECT zone_id FROM orders WHERE id = '$order_id'");
                }
                $res_zone = $this->db->getResult();
                if (!empty($res_zone) && isset($res_zone[0]['zone_id'])) {
                    $order_zone_id = (int)$res_zone[0]['zone_id'];
                }

            $sql = "SELECT id FROM delivery_boys WHERE active_status = 'true'";
            if ($order_zone_id > 0) {
                $sql .= " AND (zone_id = 0 OR zone_id IS NULL OR zone_id = '$order_zone_id')";
            }
            if ($this->delivery_boy_has_service_type()) {
                $order_type_val = in_array($type, array('food','parcel')) ? $type : 'food';
                $sql .= " AND (service_type = 'both' OR service_type = '".$order_type_val."')";
            }
            
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

    /**
     * Driving distance in km between two coordinate pairs, used for parcel
     * billing. GetDeliveryDistance() is a straight line, which understates the
     * real journey whenever a river, rail line or wall sits between the two
     * points, so parcels are priced on the routed distance instead.
     *
     * Tries the Routes API v2 first because the legacy Distance Matrix API is
     * deprecated, then falls back to Distance Matrix, and finally to the
     * straight-line figure so a Google outage can never block a parcel booking.
     *
     * Returns array('km' => float, 'source' => 'routes'|'matrix'|'fallback').
     * 'source' exists so the fallback rate can be audited rather than failing
     * silently.
     */
    public function get_road_distance_km($lat1, $lng1, $lat2, $lng2)
    {
        $straight = (float)$this->GetDeliveryDistance($lat1, $lat2, $lng1, $lng2);

        if (!is_numeric($lat1) || !is_numeric($lng1) || !is_numeric($lat2) || !is_numeric($lng2)) {
            return array('km' => round($straight, 2), 'source' => 'fallback');
        }

        $api_key = $this->get_google_maps_api_key();
        if ($api_key === false) {
            return array('km' => round($straight, 2), 'source' => 'fallback');
        }

        // Routes API v2 - the currently supported routing endpoint
        $body = array(
            'origin' => array('location' => array('latLng' => array(
                'latitude' => (float)$lat1, 'longitude' => (float)$lng1))),
            'destination' => array('location' => array('latLng' => array(
                'latitude' => (float)$lat2, 'longitude' => (float)$lng2))),
            'travelMode' => 'DRIVE',
            // Deliberately not TRAFFIC_AWARE: billing on live traffic would make
            // the same trip cost a different amount depending on the time of
            // day, which customers would dispute. This is the typical driving
            // distance, which is also what Google Maps shows by default.
            'routingPreference' => 'TRAFFIC_UNAWARE'
        );
        $res = $this->http_post_json(
            'https://routes.googleapis.com/directions/v2:computeRoutes',
            $body,
            array('X-Goog-Api-Key: ' . $api_key, 'X-Goog-FieldMask: routes.distanceMeters')
        );
        $km = $this->parse_routes_distance_km($res);
        if ($km !== false) {
            return array('km' => round($km, 2), 'source' => 'routes');
        }

        // legacy Distance Matrix, kept for keys that still have it enabled
        $url = 'https://maps.googleapis.com/maps/api/distancematrix/json'
            . '?origins=' . rawurlencode($lat1 . ',' . $lng1)
            . '&destinations=' . rawurlencode($lat2 . ',' . $lng2)
            . '&mode=driving&units=metric&key=' . rawurlencode($api_key);
        $km = $this->parse_matrix_distance_km($this->http_get_json($url));
        if ($km !== false) {
            return array('km' => round($km, 2), 'source' => 'matrix');
        }

        return array('km' => round($straight, 2), 'source' => 'fallback');
    }

    /**
     * Pure tariff arithmetic for an in-person food delivery, with no Maps call.
     *
     * The tariff in delivery_method.in_persion_data is a flat amount covering the
     * first first_km kilometres, plus rest_km_amount for every kilometre past
     * that. The excess is NOT rounded up to whole kilometres: a 2.46 km trip is
     * billed on 2.46 km, so the figure the customer is shown is the figure they
     * are charged for. The previous ceil() inflated short trips - a 1.34 km
     * straight line was reported as 2Km and billed as 2.
     *
     * Split out from get_food_delivery_charge() and kept free of any API call so
     * the money can be exercised without a network round trip.
     */
    public function food_delivery_tariff_charge($km, $in_persion_data)
    {
        $tariff = is_string($in_persion_data) ? json_decode($in_persion_data, true) : $in_persion_data;
        if (!is_array($tariff)) {
            $tariff = array();
        }
        $first_km     = isset($tariff['first_km']) ? (float)$tariff['first_km'] : 0;
        $first_amount = isset($tariff['first_km_amount']) ? (float)$tariff['first_km_amount'] : 0;
        $rest_per_km  = isset($tariff['rest_km_amount']) ? (float)$tariff['rest_km_amount'] : 0;

        $excess = ((float)$km) - $first_km;
        if ($excess < 0) {
            $excess = 0;
        }

        return round($first_amount + ($excess * $rest_per_km), 2);
    }

    /**
     * Platform and convenience fees for a zone, falling back to the global
     * settings when the zone has not overridden them.
     *
     * Both zone columns are nullable, and NULL means "inherit", so a zone that
     * an admin has not touched keeps charging the global amount. That is what
     * makes this safe to roll out: every existing zone behaves identically
     * until a fee is actually entered for it.
     *
     * A zone_id of 0, NULL or '' means the order could not be attributed to a
     * zone, which also falls back to the global fees rather than charging
     * nothing.
     *
     * Returns array(
     *   'platform_fee'    => float, in rupees,
     *   'convenience_fee' => float, a percentage of the item subtotal,
     *   'zone_id'         => int, 0 when unattributed,
     *   'inherited'       => bool, true when the global values were used.
     * ).
     */
    public function get_zone_fees($zone_id, $global_platform_fee, $global_convenience_fee)
    {
        $global_platform_fee    = (float)$global_platform_fee;
        $global_convenience_fee = (float)$global_convenience_fee;

        $result = array(
            'platform_fee'    => $global_platform_fee,
            'convenience_fee' => $global_convenience_fee,
            'zone_id'         => 0,
            'inherited'       => true,
        );

        $zone_id = is_numeric($zone_id) ? (int)$zone_id : 0;
        if ($zone_id <= 0) {
            return $result;
        }
        $result['zone_id'] = $zone_id;

        $this->db->sql("SELECT platform_fee, convenience_fee FROM `zone` WHERE id=" . $zone_id . " LIMIT 1");
        $row = $this->db->getResult();
        if (empty($row) || !is_array($row[0])) {
            return $result;
        }

        // A zone only overrides a fee when it actually holds a value, so the
        // two fees can be set independently.
        if ($row[0]['platform_fee'] !== null && $row[0]['platform_fee'] !== '') {
            $result['platform_fee'] = (float)$row[0]['platform_fee'];
            $result['inherited'] = false;
        }
        if ($row[0]['convenience_fee'] !== null && $row[0]['convenience_fee'] !== '') {
            $result['convenience_fee'] = (float)$row[0]['convenience_fee'];
            $result['inherited'] = false;
        }

        return $result;
    }

    /**
     * Delivery method flags and the per-km tariff that apply to a zone.
     *
     * Mirrors get_zone_fees(): every tariff column is nullable and NULL means
     * "inherit the global value from delivery_method id=1", so a zone an admin
     * has not configured behaves exactly as it did before zone-wise delivery
     * existed. That is what makes this safe to roll out.
     *
     * The method flags are per zone but default to the global flags when the
     * zone has no row at all, so a newly created zone is never left with
     * delivery switched off.
     *
     * A zone_id of 0, NULL or '' means the address could not be attributed to a
     * zone, which also falls back to the global configuration.
     *
     * Returns array(
     *   'storepickup'        => int,
     *   'in_person_delivery' => int,
     *   'in_persion_data'    => array, the tariff to hand to
     *                           food_delivery_tariff_charge(),
     *   'zone_id'            => int, 0 when unattributed,
     *   'inherited'          => bool, true when the global tariff was used,
     * ).
     *
     * There is no per-zone courier flag on purpose. Courier has no charge path of
     * its own - courier orders are priced on the in-person per-km tariff - so a
     * courier switch would only look like it does something.
     */
    public function get_zone_delivery_config($zone_id, $global_dm_row)
    {
        $global = is_array($global_dm_row) ? $global_dm_row : array();
        $global_tariff = array();
        if (isset($global['in_persion_data'])) {
            $decoded = is_string($global['in_persion_data'])
                ? json_decode($global['in_persion_data'], true)
                : $global['in_persion_data'];
            if (is_array($decoded)) {
                $global_tariff = $decoded;
            }
        }

        $result = array(
            'storepickup'        => isset($global['storepickup']) ? (int)$global['storepickup'] : 0,
            'in_person_delivery' => isset($global['in_persion_delivery']) ? (int)$global['in_persion_delivery'] : 0,
            'in_persion_data'    => $global_tariff,
            'zone_id'            => 0,
            'inherited'          => true,
        );

        $zone_id = is_numeric($zone_id) ? (int)$zone_id : 0;
        if ($zone_id <= 0) {
            return $result;
        }
        $result['zone_id'] = $zone_id;

        $this->db->sql(
            "SELECT storepickup, in_person_delivery, is_active,
                    first_km, first_km_amount, rest_km_amount
             FROM `zone_delivery_method` WHERE zone_id=" . $zone_id . " LIMIT 1"
        );
        $row = $this->db->getResult();
        if (empty($row) || !is_array($row[0])) {
            return $result;
        }

        $row = $row[0];

        // A zone row that has been switched off is not serviceable, whatever the
        // global setting says.
        if (isset($row['is_active']) && (int)$row['is_active'] !== 1) {
            $result['storepickup']        = 0;
            $result['in_person_delivery'] = 0;
            $result['zone_id']            = $zone_id;
            $result['inherited']          = false;
            return $result;
        }

        $result['storepickup']        = (int)$row['storepickup'];
        $result['in_person_delivery'] = (int)$row['in_person_delivery'];

        // Each tariff column overrides independently, so a zone can override
        // only the per-km rate and keep the global first-km slab.
        $tariff = $global_tariff;
        $overrode = false;
        if ($row['first_km'] !== null && $row['first_km'] !== '') {
            $tariff['first_km'] = (float)$row['first_km'];
            $overrode = true;
        }
        if ($row['first_km_amount'] !== null && $row['first_km_amount'] !== '') {
            $tariff['first_km_amount'] = (float)$row['first_km_amount'];
            $overrode = true;
        }
        if ($row['rest_km_amount'] !== null && $row['rest_km_amount'] !== '') {
            $tariff['rest_km_amount'] = (float)$row['rest_km_amount'];
            $overrode = true;
        }
        $result['in_persion_data'] = $tariff;
        $result['inherited']       = !$overrode;

        return $result;
    }

    /**
     * Split a subtotal into platform fee, convenience fee and tax using the
     * fees that apply to a zone, so the preview and the stored order agree.
     *
     * platform_fee is a flat rupee amount, convenience_fee is a percentage of
     * the subtotal, and tax is a percentage of the subtotal plus both fees -
     * the same order the parcel endpoints have always used.
     *
     * Returns array with 'platform_fee', 'convenience_fee', 'gst' and
     * 'grand_total', plus the fee inputs that produced them.
     */
    public function split_zone_fees($subtotal, $zone_id, $global_settings)
    {
        $subtotal = (float)$subtotal;

        $global_platform    = isset($global_settings['platform_fee']) ? (float)$global_settings['platform_fee'] : 0;
        $global_convenience = isset($global_settings['convenience_fee']) ? (float)$global_settings['convenience_fee'] : 0;
        $tax_percent        = isset($global_settings['tax']) ? (float)$global_settings['tax'] : 0;

        $fees = $this->get_zone_fees($zone_id, $global_platform, $global_convenience);

        $platform_fee    = round($fees['platform_fee'], 2);
        $convenience_fee = round(($subtotal * $fees['convenience_fee']) / 100, 2);
        $gst             = round(($subtotal + $convenience_fee + $platform_fee) * $tax_percent / 100, 2);

        return array(
            'platform_fee'      => $platform_fee,
            'convenience_fee'   => $convenience_fee,
            'gst'               => $gst,
            'grand_total'       => round($subtotal + $convenience_fee + $platform_fee + $gst, 2),
            'zone_id'           => $fees['zone_id'],
            'fees_inherited'    => $fees['inherited'],
            'convenience_fee_percent' => $fees['convenience_fee'],
        );
    }

    /**
     * Delivery charge for an in-person food order, priced on the real driving
     * distance between the store and the delivery address.
     *
     * The charge is worked out by food_delivery_tariff_charge() so the preview
     * shown when an address is picked and the amount actually stored on the
     * order come from the same code and cannot drift apart.
     *
     * Returns array(
     *   'distance_km'     => float, rounded to 2dp and used for billing,
     *   'delivery_charge' => float,
     *   'distance_source' => 'routes'|'matrix'|'fallback',
     *   'error'           => '' when usable, otherwise why it is not
     * ).
     */
    public function get_food_delivery_charge($store_lat, $store_lng, $user_lat, $user_lng, $in_persion_data)
    {
        $blank = array(
            'distance_km' => 0.0,
            'delivery_charge' => 0.0,
            'distance_source' => '',
            'error' => 'Store or delivery location is missing.'
        );

        foreach (array($store_lat, $store_lng, $user_lat, $user_lng) as $coord) {
            if (!is_numeric($coord) || (float)$coord == 0) {
                return $blank;
            }
        }

        $road = $this->get_road_distance_km($store_lat, $store_lng, $user_lat, $user_lng);
        // Charge the rounded figure that the customer is shown, so the
        // displayed distance and the billed distance are the same number.
        $km = round((float)$road['km'], 2);

        return array(
            'distance_km' => $km,
            'delivery_charge' => $this->food_delivery_tariff_charge($km, $in_persion_data),
            'distance_source' => $road['source'],
            'error' => ''
        );
    }

    /**
     * Pull the driving distance out of a Routes API v2 response.
     * Returns km, or false when the response is absent or carries an error.
     * Split out from the request so it can be exercised without a live call.
     */
    public function parse_routes_distance_km($res)
    {
        if (!is_array($res)) {
            return false;
        }
        if (isset($res['error'])) {
            $msg = isset($res['error']['message']) ? $res['error']['message'] : 'unknown Routes API error';
            $this->last_maps_error = 'Routes API: ' . $msg;
            return false;
        }
        if (empty($res['routes']) || !is_array($res['routes'])) {
            $this->last_maps_error = 'Routes API: no routes returned';
            return false;
        }
        // The shape depends on the field mask. Asking for routes.distanceMeters
        // flattens it to routes[0].distanceMeters, while an unfiltered response
        // puts it at routes[0].legs[0].distanceMeters. Accept either.
        $meters = null;
        if (isset($res['routes'][0]['legs'][0]['distanceMeters'])) {
            $meters = $res['routes'][0]['legs'][0]['distanceMeters'];
        } elseif (isset($res['routes'][0]['distanceMeters'])) {
            $meters = $res['routes'][0]['distanceMeters'];
        }
        if ($meters === null) {
            $this->last_maps_error = 'Routes API: response had no distance';
            return false;
        }
        $km = ((float)$meters) / 1000;
        if ($km > 0) {
            $this->last_maps_error = '';
            return $km;
        }
        $this->last_maps_error = 'Routes API: reported a zero distance';
        return false;
    }

    /**
     * Pull the driving distance out of a legacy Distance Matrix response.
     * Returns km, or false when the element is missing or not OK.
     */
    public function parse_matrix_distance_km($res)
    {
        if (!is_array($res)) {
            return false;
        }
        // Distance Matrix answers HTTP 200 even for refusals such as
        // REQUEST_DENIED, so the reason has to be read out of the body.
        if (isset($res['status']) && $res['status'] !== 'OK') {
            $msg = isset($res['error_message']) ? $res['error_message'] : ('status ' . $res['status']);
            $this->last_maps_error = 'Distance Matrix: ' . $res['status'] . ' - ' . $msg;
            $this->log_maps_error($this->last_maps_error);
            return false;
        }
        $el = isset($res['rows'][0]['elements'][0]) ? $res['rows'][0]['elements'][0] : null;
        if (!is_array($el)) {
            $this->last_maps_error = 'Distance Matrix: no rows in response';
            return false;
        }
        if (!isset($el['status']) || $el['status'] !== 'OK') {
            $this->last_maps_error = 'Distance Matrix: element status ' . (isset($el['status']) ? $el['status'] : 'missing');
            $this->log_maps_error($this->last_maps_error);
            return false;
        }
        if (!isset($el['distance']['value'])) {
            $this->last_maps_error = 'Distance Matrix: element had no distance';
            return false;
        }
        $km = ((float)$el['distance']['value']) / 1000;
        if ($km > 0) {
            $this->last_maps_error = '';
            return $km;
        }
        $this->last_maps_error = 'Distance Matrix: reported a zero distance';
        return false;
    }

    // The Maps key is stored alongside the other store settings in system_timezone
    private function get_google_maps_api_key()
    {
        $settings = $this->get_settings('system_timezone', true);
        if (is_array($settings) && !empty($settings['store_map_api'])) {
            return trim($settings['store_map_api']);
        }
        return false;
    }

    /**
     * Optional custom CA bundle for the Google calls, read from the
     * store_map_ca_bundle setting.
     *
     * This exists because TLS-inspecting antivirus software (Avast and similar)
     * re-signs HTTPS traffic with its own root certificate. PHP's cURL then
     * fails with "unable to get local issuer certificate" and the distance
     * silently falls back to a straight line. Pointing this at a bundle that
     * includes the inspector's root makes verification succeed.
     *
     * Left unset on servers that are not intercepted, which is the normal case
     * in production. Turning verification off is deliberately not supported.
     */
    private function get_google_maps_ca_bundle()
    {
        $settings = $this->get_settings('system_timezone', true);
        if (is_array($settings) && !empty($settings['store_map_ca_bundle'])) {
            $path = trim($settings['store_map_ca_bundle']);
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }
        return false;
    }

    // Bounded so a customer waiting on a quote is never stuck, but generous
    // enough for a cold DNS lookup, which measured up to ~8s on some networks.
    // IPv6 is left to cURL's own happy-eyeballs ordering: forcing IPv4 was tried
    // and was slower here, not faster.
    private function http_post_json($url, $body, $headers = array())
    {
        // The Routes API rejects POST bodies that are not declared as JSON.
        // Without this the call fails and silently drops to the next provider.
        array_unshift($headers, 'Content-Type: application/json');
        return $this->http_json('POST', $url, json_encode($body), $headers);
    }

    private function http_get_json($url)
    {
        return $this->http_json('GET', $url, null, array());
    }

    /**
     * Reason the most recent Google call failed, or '' if it succeeded.
     * A distance call that silently falls back to a straight line looks like a
     * pricing bug, so the cause is recorded instead of being swallowed.
     */
    public function get_last_maps_error()
    {
        return $this->last_maps_error;
    }

    private function http_json($method, $url, $payload, $headers)
    {
        $this->last_maps_error = '';
        if (!function_exists('curl_init')) {
            $this->last_maps_error = 'php cURL extension is not installed';
            return false;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        $ca = $this->get_google_maps_ca_bundle();
        if ($ca !== false) {
            curl_setopt($ch, CURLOPT_CAINFO, $ca);
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        $raw = curl_exec($ch);
        $curl_error = curl_error($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            $this->last_maps_error = 'transport failure: ' . ($curl_error ? $curl_error : 'unknown');
            $this->log_maps_error($this->last_maps_error);
            return false;
        }
        if ($status < 200 || $status >= 300) {
            $this->last_maps_error = 'HTTP ' . $status;
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && isset($decoded['error']['message'])) {
                $this->last_maps_error .= ': ' . $decoded['error']['message'];
            } elseif (is_array($decoded) && isset($decoded['error_message'])) {
                $this->last_maps_error .= ': ' . $decoded['error_message'];
            }
            $this->log_maps_error($this->last_maps_error);
            return false;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $this->last_maps_error = 'response was not valid JSON';
            $this->log_maps_error($this->last_maps_error);
            return false;
        }
        return $decoded;
    }

    private function log_maps_error($message)
    {
        error_log('[google-maps-distance] ' . $message);
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

    public function get_zone_id_from_latlng($latitude, $longitude){
        if (empty($latitude) || empty($longitude) || !is_numeric($latitude) || !is_numeric($longitude)) {
            return null;
        }
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        $this->db->sql("SELECT id, polygon FROM zone WHERE status = 1");
        $zones = $this->db->getResult();
        if (empty($zones)) {
            return null;
        }
        foreach ($zones as $zone) {
            $polygon = json_decode($zone['polygon'], true);
            if (!is_array($polygon) || count($polygon) < 3) {
                continue;
            }
            $vertices = [];
            foreach ($polygon as $point) {
                $vertices[] = array(
                    'lat' => (float)$point['lat'],
                    'lng' => (float)$point['lng']
                );
            }
            $inside = false;
            $j = count($vertices) - 1;
            for ($i = 0; $i < count($vertices); $i++) {
                $xi = $vertices[$i]['lng'];
                $yi = $vertices[$i]['lat'];
                $xj = $vertices[$j]['lng'];
                $yj = $vertices[$j]['lat'];
                $intersect = (($yi > $lat) != ($yj > $lat)) &&
                             ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi);
                if ($intersect) {
                    $inside = !$inside;
                }
                $j = $i;
            }
            if ($inside) {
                return (int)$zone['id'];
            }
        }
        return null;
    }

    /**
     * Resolve a delivery address to the zone it falls in.
     * Prefers the stored user_address.zone_id, otherwise runs a live
     * point-in-polygon test on the address coordinates.
     * Returns 0 when the address cannot be resolved to any active zone
     * (missing/zero coordinates, or outside every configured polygon).
     */
    public function get_address_zone($address_row) {
        if (!empty($address_row['zone_id'])) {
            return (int)$address_row['zone_id'];
        }
        $zone = $this->get_zone_id_from_latlng(
            isset($address_row['latitude']) ? $address_row['latitude'] : 0,
            isset($address_row['longitude']) ? $address_row['longitude'] : 0
        );
        return ($zone === null) ? 0 : (int)$zone;
    }

    /**
     * Zone-wise service rule: can $seller_zone deliver to $address_zone?
     * - Seller has no zone assigned  => no restriction (every address allowed)
     * - Address resolves to no zone  => allowed, because we cannot prove it is
     *   outside the seller's area and must not lock the customer out
     * - Otherwise                   => zones must match exactly
     */
    public function is_address_in_seller_zone($address_zone, $seller_zone) {
        $seller_zone = (int)$seller_zone;
        if ($seller_zone <= 0) return true;
        $address_zone = (int)$address_zone;
        if ($address_zone <= 0) return true;
        return $address_zone === $seller_zone;
    }

    /**
     * Returns the zone a seller belongs to, or 0 when unassigned.
     */
    public function get_seller_zone($seller_id) {
        $seller_id = (int)$seller_id;
        if ($seller_id <= 0) return 0;
        $this->db->sql("SELECT zone_id FROM seller WHERE id='".$seller_id."' LIMIT 1");
        $res = $this->db->getResult();
        if (!empty($res) && isset($res[0]['zone_id']) && !empty($res[0]['zone_id'])) {
            return (int)$res[0]['zone_id'];
        }
        return 0;
    }

    /**
     * Returns the shortest great-circle distance (in km) from point P to the
     * line segment A-B (Haversine approximation via projected coordinates).
     */
    private function point_to_segment_distance($plat, $plng, $alat, $alng, $blat, $blng) {
        $plat_r = deg2rad($plat); $plng_r = deg2rad($plng);
        $alat_r = deg2rad($alat); $alng_r = deg2rad($alng);
        $blat_r = deg2rad($blat); $blng_r = deg2rad($blng);
        $dx = $blat_r - $alat_r;
        $dy = $blng_r - $alng_r;
        $len2 = $dx * $dx + $dy * $dy;
        if ($len2 == 0) {
            $t = 0;
        } else {
            $t = (($plat_r - $alat_r) * $dx + ($plng_r - $alng_r) * $dy) / $len2;
            $t = max(0.0, min(1.0, $t));
        }
        $nlat = $alat_r + $t * $dx;
        $nlng = $alng_r + $t * $dy;
        $dlat = $plat_r - $nlat;
        $dlng = $plng_r - $nlng;
        $a = sin($dlat / 2) * sin($dlat / 2)
           + cos($plat_r) * cos($nlat) * sin($dlng / 2) * sin($dlng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return 6371 * $c;
    }

    /**
     * Measures a point against ONE zone polygon.
     * Returns null   when the polygon is missing or unusable.
     * Returns 0.0    when the point falls inside the polygon.
     * Otherwise the shortest straight-line distance in km to its edges.
     */
    private function polygon_boundary_distance($lat, $lng, $polygon_raw) {
        $polygon = json_decode($polygon_raw, true);
        if (!is_array($polygon) || count($polygon) < 3) {
            return null;
        }
        $vertices = array();
        foreach ($polygon as $point) {
            if (!isset($point['lat']) || !isset($point['lng'])) {
                return null;
            }
            $vertices[] = array((float)$point['lat'], (float)$point['lng']);
        }
        $n = count($vertices);
        // Ray-casting: is the point inside this zone?
        $inside = false;
        $j = $n - 1;
        for ($i = 0; $i < $n; $i++) {
            $xi = $vertices[$i][1]; $yi = $vertices[$i][0];
            $xj = $vertices[$j][1]; $yj = $vertices[$j][0];
            if ((($yi > $lat) != ($yj > $lat)) &&
                ($lng < ($xj - $xi) * ($lat - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }
        if ($inside) {
            return 0.0;
        }
        // Measure shortest distance to each edge
        $min_dist = 999999;
        for ($i = 0; $i < $n; $i++) {
            $j2 = ($i + 1) % $n;
            $d = $this->point_to_segment_distance(
                $lat, $lng,
                $vertices[$i][0], $vertices[$i][1],
                $vertices[$j2][0], $vertices[$j2][1]
            );
            if ($d < $min_dist) {
                $min_dist = $d;
            }
        }
        return $min_dist;
    }

    /**
     * Returns the minimum straight-line distance in km from a lat/lng point
     * to the nearest boundary of any active zone.
     * Returns 0.0   if the point is already inside a zone.
     * Returns 999999 if there are no active zones configured.
     */
    public function get_nearest_zone_distance($latitude, $longitude) {
        if (empty($latitude) || empty($longitude) || !is_numeric($latitude) || !is_numeric($longitude)) {
            return 999999;
        }
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        $this->db->sql("SELECT id, polygon FROM zone WHERE status = 1");
        $zones = $this->db->getResult();
        if (empty($zones)) {
            return 999999;
        }
        $min_dist = 999999;
        foreach ($zones as $zone) {
            $d = $this->polygon_boundary_distance($lat, $lng, $zone['polygon']);
            if ($d === null) {
                continue;
            }
            if ($d <= 0.0) {
                return 0.0; // Inside a zone: zero distance to boundary
            }
            if ($d < $min_dist) {
                $min_dist = $d;
            }
        }
        return $min_dist;
    }

    /**
     * Distance in km from a point to the boundary of ONE named zone, ignoring
     * every other zone.
     *
     * Parcel serviceability has to be judged this way. Measuring against "the
     * nearest zone of any kind" lets a pickup in zone A be paired with a drop
     * that sits inside some far-away zone B, because B's own polygon makes the
     * measured distance zero even though B is nowhere near A.
     *
     * Returns 0.0    when the point is inside the given zone.
     * Returns 999999 when the zone is unknown/inactive or the point cannot be
     *                shown to be near it, so callers fail closed.
     */
    public function get_zone_boundary_distance($latitude, $longitude, $zone_id) {
        $zone_id = (int)$zone_id;
        if ($zone_id <= 0) {
            return 999999;
        }
        if (empty($latitude) || empty($longitude) || !is_numeric($latitude) || !is_numeric($longitude)) {
            return 999999;
        }
        $lat = (float)$latitude;
        $lng = (float)$longitude;
        $this->db->sql("SELECT polygon FROM zone WHERE id = '".$zone_id."' AND status = 1 LIMIT 1");
        $zones = $this->db->getResult();
        if (empty($zones)) {
            return 999999;
        }
        $d = $this->polygon_boundary_distance($lat, $lng, $zones[0]['polygon']);
        return ($d === null) ? 999999 : $d;
    }

    /**
     * Display name of a zone, or an empty string when it cannot be resolved.
     */
    public function get_zone_name($zone_id) {
        $zone_id = (int)$zone_id;
        if ($zone_id <= 0) {
            return '';
        }
        $this->db->sql("SELECT name FROM zone WHERE id = '".$zone_id."' LIMIT 1");
        $res = $this->db->getResult();
        if (!empty($res) && isset($res[0]['name'])) {
            return (string)$res[0]['name'];
        }
        return '';
    }

    // target ceiling for a stored product image, in bytes
    const PRODUCT_IMAGE_MAX_BYTES = 250000;

    /**
     * Shrink an already uploaded image until it fits inside $max_bytes.
     *
     * Files that are already within the limit are left completely untouched, so
     * nothing is needlessly re-encoded. Otherwise quality is stepped down first
     * and the dimensions are only reduced once quality has bottomed out, which
     * keeps the picture recognisable for as long as possible. JPEG is preferred
     * when a resize is unavoidable because it is far cheaper per pixel than PNG.
     *
     * Returns true when the file ends up within the limit. Returns false when it
     * could not be processed - the original upload is then left in place rather
     * than replaced with a broken file.
     */
    public function compress_image_file($file_path, $max_bytes = self::PRODUCT_IMAGE_MAX_BYTES)
    {
        if (!is_string($file_path) || $file_path === '' || !is_file($file_path)) {
            return false;
        }
        if (filesize($file_path) <= $max_bytes) {
            return true;
        }
        if (!function_exists('imagecreatetruecolor')) {
            return false; // no GD, leave the upload untouched
        }

        $info = @getimagesize($file_path);
        if ($info === false) {
            return false;
        }
        $width = (int)$info[0];
        $height = (int)$info[1];
        $type = (int)$info[2];

        $image = $this->gd_load_image($file_path, $type);
        if ($image === false) {
            return false;
        }

        // transparency has to be preserved for the formats that support it
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF || $type === IMAGETYPE_WEBP) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        // step_quality is off for GIF (no quality dial) and PNG (level 9 is the
        // best it does, so walking it cannot get us to the target on its own)
        $step_quality = ($type === IMAGETYPE_JPEG || $type === IMAGETYPE_WEBP);
        $quality = 80;
        $floor = 30;
        $passes = 0;

        // Every attempt is written to a scratch file and only swapped in once it
        // is known to be good. GD can fail part way through a write (CMYK JPEG,
        // 16 bit PNG and the like) and leave the target truncated, so the
        // original upload must never be the thing being written to.
        $tmp = $file_path . '.compress_tmp';

        while (true) {
            if (++$passes > 40) {
                break; // safety net so a huge image can never spin
            }
            if (!$this->gd_write_image($image, $tmp, $type, $quality)) {
                imagedestroy($image);
                @unlink($tmp);
                return false; // original left exactly as it was uploaded
            }
            // the scratch file was just rewritten, so its cached stat is stale
            clearstatcache(true, $tmp);
            $size = filesize($tmp);
            if ($size !== false && $size <= $max_bytes) {
                imagedestroy($image);
                return $this->replace_with_compressed($tmp, $file_path);
            }

            if ($step_quality && $quality > $floor) {
                $quality -= 10;
                continue;
            }

            // quality exhausted - shrink by 20% and walk the quality back up
            $new_w = (int)($width * 0.75);
            $new_h = (int)($height * 0.75);
            if ($new_w < 100 || $new_h < 100) {
                break;
            }
            $resized = $this->gd_resize($image, $width, $height, $new_w, $new_h);
            if ($resized === false) {
                break;
            }
            imagedestroy($image);
            $image = $resized;
            $width = $new_w;
            $height = $new_h;
            $quality = 80;
        }

        imagedestroy($image);
        clearstatcache(true, $tmp);
        $final = filesize($tmp);
        if ($final !== false && $final <= $max_bytes) {
            $done = $this->replace_with_compressed($tmp, $file_path);
            if ($done) {
                return true;
            }
        }
        @unlink($tmp);
        return false;
    }

    /**
     * Swap the compressed scratch file in for the original upload. rename() will
     * not clobber an existing file on Windows, so the original is removed first.
     * If the swap cannot be completed the original is left in place.
     */
    private function replace_with_compressed($tmp, $file_path)
    {
        if (!is_file($tmp)) {
            return false;
        }
        if (!@rename($tmp, $file_path)) {
            @unlink($file_path);
            if (!@rename($tmp, $file_path)) {
                @unlink($tmp);
                return false;
            }
        }
        clearstatcache(true, $file_path);
        return true;
    }

    private function gd_load_image($path, $type)
    {
        switch ($type) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            default:
                return false;
        }
    }

    private function gd_write_image($image, $path, $type, $quality)
    {
        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }
        switch ($type) {
            case IMAGETYPE_JPEG:
                // JPEG has no alpha channel, so flatten onto white first -
                // otherwise transparent source images come out with black areas
                $flat = $this->gd_flatten($image);
                $ok = @imagejpeg($flat, $path, $quality);
                imagedestroy($flat);
                return $ok;
            case IMAGETYPE_PNG:
                // imagepng's third argument is a 0-9 compression level, which runs
                // opposite to jpeg's quality. 9 is the best PNG can do, so there is
                // no useful quality walk to make here.
                return @imagepng($image, $path, 9);
            case IMAGETYPE_GIF:
                return @imagegif($image, $path);
            case IMAGETYPE_WEBP:
                return function_exists('imagewebp') ? @imagewebp($image, $path, $quality) : false;
            default:
                return false;
        }
    }

    private function gd_flatten($image)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $flat = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($flat, 255, 255, 255);
        imagefilledrectangle($flat, 0, 0, $w, $h, $white);
        imagealphablending($flat, true);
        imagecopy($flat, $image, 0, 0, 0, 0, $w, $h);
        return $flat;
    }

    private function gd_resize($image, $old_w, $old_h, $new_w, $new_h)
    {
        $resized = imagecreatetruecolor($new_w, $new_h);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
        imagefilledrectangle($resized, 0, 0, $new_w, $new_h, $transparent);
        if (!imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_w, $new_h, $old_w, $old_h)) {
            imagedestroy($resized);
            return false;
        }
        return $resized;
    }

    /*
     * ----------------------------------------------------------------------
     * Announcement banner (weather-aware)
     * ----------------------------------------------------------------------
     * The admin controls the banner from the settings page: a manual
     * on/off switch plus an optional "auto-enable on rain" mode. The
     * home page banner is visible when:
     *   - the manual switch is ON, OR
     *   - auto-weather is ON and the customer's zone is detecting rain.
     * Rain is resolved per zone (nearest coordinates = polygon centroid)
     * through the free Open-Meteo API and cached for 10 minutes in the
     * settings table to avoid hammering the external API on every request.
     */

    function get_zone_center_ll($zone_id){
        $zone_id = (int)$zone_id;
        if($zone_id <= 0){
            return null;
        }
        $this->db->sql("SELECT polygon FROM zone WHERE id=".$zone_id." LIMIT 1");
        $res = $this->db->getResult();
        if(empty($res) || empty($res[0]['polygon'])){
            return null;
        }
        $poly = json_decode($res[0]['polygon'], true);
        if(!is_array($poly) || count($poly) < 3){
            return null;
        }
        $lat_sum = 0.0;
        $lng_sum = 0.0;
        $count = 0;
        foreach($poly as $point){
            if(is_array($point) && isset($point[0], $point[1])){
                $lat = (float)$point[0];
                $lng = (float)$point[1];
            }elseif(is_array($point) && isset($point['lat'], $point['lng'])){
                $lat = (float)$point['lat'];
                $lng = (float)$point['lng'];
            }else{
                continue;
            }
            if($lat == 0.0 && $lng == 0.0){
                continue;
            }
            $lat_sum += $lat;
            $lng_sum += $lng;
            $count++;
        }
        if($count == 0){
            return null;
        }
        return array('lat' => round($lat_sum / $count, 5), 'lng' => round($lng_sum / $count, 5));
    }

    function get_zone_rain_status($zone_id, $force_refresh = false){
        $zone_id = (int)$zone_id;
        if($zone_id <= 0){
            return false;
        }
        $center = $this->get_zone_center_ll($zone_id);
        if(empty($center)){
            return false;
        }
        $cache_ttl = 600; // seconds (10 minutes)
        $now = time();
        $cache = array();
        $cache_row = false;
        $this->db->sql("SELECT value FROM settings WHERE variable='zone_weather_cache' LIMIT 1");
        $res = $this->db->getResult();
        if(!empty($res)){
            $cache_row = true;
            $decoded = json_decode($res[0]['value'], true);
            if(is_array($decoded)){
                $cache = $decoded;
            }
        }
        $key = (string)$zone_id;
        if(!$force_refresh && isset($cache[$key]) && is_array($cache[$key])
            && isset($cache[$key]['ts']) && ($now - (int)$cache[$key]['ts']) < $cache_ttl){
            return !empty($cache[$key]['rain']);
        }

        $weather_code = null;
        if(function_exists('curl_init')){
            $url = 'https://api.open-meteo.com/v1/forecast?latitude='.$center['lat'].'&longitude='.$center['lng'].'&current=weather_code';
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_USERAGENT => 'Mozilla/5.0'
            ));
            $body = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if(!$err && !empty($body)){
                $data = json_decode($body, true);
                if(is_array($data) && isset($data['current']['weather_code'])){
                    $weather_code = (int)$data['current']['weather_code'];
                }
            }
        }

        if($weather_code === null){
            // The external API failed - fall back to the last cached status
            // for this zone (if any) instead of flashing the banner by
            // guessing the weather.
            return isset($cache[$key]['rain']) ? !empty($cache[$key]['rain']) : false;
        }

        // Rain families in Open-Meteo's WMO weather codes: 51-67 cover
        // drizzle/rain/freezing rain, 80-82 cover rain showers and 95-99
        // cover thunderstorms (which are rain/hail events).
        $rain = ($weather_code >= 51 && $weather_code <= 67)
            || ($weather_code >= 80 && $weather_code <= 82)
            || ($weather_code >= 95 && $weather_code <= 99);

        $cache[$key] = array('rain' => $rain ? 1 : 0, 'ts' => $now);
        $cache_value = $this->db->escapeString(json_encode($cache));
        if($cache_row){
            $sql = "UPDATE settings SET value='".$cache_value."' WHERE variable='zone_weather_cache'";
        }else{
            $sql = "INSERT INTO settings (variable, value) VALUES ('zone_weather_cache','".$cache_value."')";
        }
        $this->db->sql($sql);
        $this->db->getResult();

        return $rain;
    }

    function get_zone_announcement_settings($zone_id){
        $zone_id = (int)$zone_id;
        if($zone_id <= 0){
            return null;
        }
        $this->db->sql("SELECT id, name, announcement_text, announcement_enabled, announcement_auto_weather FROM zone WHERE id=".$zone_id." LIMIT 1");
        $res = $this->db->getResult();
        if(empty($res)){
            return null;
        }
        return $res[0];
    }

    function get_announcement_banner($zone_id){
        $zone = $this->get_zone_announcement_settings($zone_id);
        if(empty($zone)){
            return array('visible' => 0, 'text' => '');
        }
        $text = isset($zone['announcement_text']) ? trim($zone['announcement_text']) : '';
        if($text === ''){
            return array('visible' => 0, 'text' => '');
        }
        $manual_on = !empty($zone['announcement_enabled']);
        if($manual_on){
            return array('visible' => 1, 'text' => $text);
        }
        $auto_weather = !empty($zone['announcement_auto_weather']);
        $visible = $auto_weather && $this->get_zone_rain_status($zone_id);
        return array('visible' => $visible ? 1 : 0, 'text' => $text);
    }

    /**
     * Raw per-zone parcel overrides. A NULL column means "inherit the global
     * parcel_settings value" (same convention as the zone platform/convenience
     * fees). Returns null when the zone does not exist.
     */
    function get_zone_parcel_settings($zone_id){
        $zone_id = (int)$zone_id;
        if($zone_id <= 0){
            return null;
        }
        $this->db->sql("SELECT id, name, parcel_per_km_price, parcel_base_price, parcel_max_weight_kg, parcel_is_service_available FROM zone WHERE id=".$zone_id." LIMIT 1");
        $res = $this->db->getResult();
        if(empty($res)){
            return null;
        }
        return $res[0];
    }

    /**
     * Effective parcel settings for a zone: the global parcel_settings row with
     * any non-null zone override layered on top. $zone_id may be null/0 to get
     * the global values. Returns per_km_price, base_price, max_weight_kg,
     * terms_conditions, is_service_available plus zone_id and the list of keys
     * that came from the zone override.
     */
    function get_effective_parcel_settings($zone_id = null){
        $this->db->sql("SELECT per_km_price, base_price, max_weight_kg, terms_conditions, is_service_available, opening_time, closing_time FROM parcel_settings ORDER BY id ASC LIMIT 1");
        $res = $this->db->getResult();
        $settings = !empty($res) ? $res[0] : array();

        $out = array(
            'per_km_price'         => (isset($settings['per_km_price']) && is_numeric($settings['per_km_price'])) ? (float)$settings['per_km_price'] : 0,
            'base_price'           => (isset($settings['base_price']) && is_numeric($settings['base_price'])) ? (float)$settings['base_price'] : 0,
            'max_weight_kg'        => (isset($settings['max_weight_kg']) && is_numeric($settings['max_weight_kg'])) ? (float)$settings['max_weight_kg'] : 0,
            'terms_conditions'     => isset($settings['terms_conditions']) ? $settings['terms_conditions'] : '',
            'is_service_available' => isset($settings['is_service_available']) ? (int)$settings['is_service_available'] : 1,
            // Daily operating window (global only, not overridable per zone).
            // NULL/empty on either end means no time restriction.
            'opening_time'         => (isset($settings['opening_time']) && $settings['opening_time'] !== '') ? $settings['opening_time'] : null,
            'closing_time'         => (isset($settings['closing_time']) && $settings['closing_time'] !== '') ? $settings['closing_time'] : null,
            'zone_id'              => null,
            'overrides'            => array(),
        );

        if($zone_id === null || (int)$zone_id <= 0){
            return $out;
        }
        $zone = $this->get_zone_parcel_settings($zone_id);
        if(empty($zone)){
            return $out;
        }
        $out['zone_id'] = (int)$zone_id;

        if($zone['parcel_per_km_price'] !== null && is_numeric($zone['parcel_per_km_price'])){
            $out['per_km_price'] = (float)$zone['parcel_per_km_price'];
            $out['overrides'][] = 'per_km_price';
        }
        if($zone['parcel_base_price'] !== null && is_numeric($zone['parcel_base_price'])){
            $out['base_price'] = (float)$zone['parcel_base_price'];
            $out['overrides'][] = 'base_price';
        }
        if($zone['parcel_max_weight_kg'] !== null && is_numeric($zone['parcel_max_weight_kg'])){
            $out['max_weight_kg'] = (float)$zone['parcel_max_weight_kg'];
            $out['overrides'][] = 'max_weight_kg';
        }
        if($zone['parcel_is_service_available'] !== null){
            $out['is_service_available'] = (int)$zone['parcel_is_service_available'];
            $out['overrides'][] = 'is_service_available';
        }
        return $out;
    }

    /**
     * The daily operating window for parcel pickup. Both ends are nullable:
     * leave either empty and the service has no time restriction (24 hours).
     * Times are returned as stored ('HH:MM:SS') or null, plus 'enabled' which
     * is true only when both ends are set.
     */
    function get_parcel_operating_hours(){
        $this->db->sql("SELECT opening_time, closing_time FROM parcel_settings ORDER BY id ASC LIMIT 1");
        $res = $this->db->getResult();
        $opening = isset($res[0]['opening_time']) ? $res[0]['opening_time'] : null;
        $closing = isset($res[0]['closing_time']) ? $res[0]['closing_time'] : null;
        if($opening === ''){ $opening = null; }
        if($closing === ''){ $closing = null; }
        return array(
            'opening_time' => $opening,
            'closing_time' => $closing,
            'enabled'      => ($opening !== null && $closing !== null),
        );
    }

    /**
     * Whether parcel pickup is inside its daily operating window right now, in
     * the store timezone. A missing window means 24-hour service. Overnight
     * windows (e.g. 22:00 -> 06:00) are supported. The opening minute is
     * inclusive and the closing minute is exclusive.
     *
     * Pass the window explicitly, or leave both args null to read it from
     * parcel_settings.
     */
    function is_parcel_service_open_now($opening_time = null, $closing_time = null){
        if($opening_time === null && $closing_time === null){
            $hours = $this->get_parcel_operating_hours();
            $opening_time = $hours['opening_time'];
            $closing_time = $hours['closing_time'];
        }
        if($opening_time === null || $closing_time === null || $opening_time === '' || $closing_time === ''){
            return true; // no window configured
        }

        // Compare on the store clock, not the PHP default timezone.
        $config = $this->get_configurations();
        $timezone = (!empty($config) && isset($config['system_timezone']) && !empty($config['system_timezone']))
            ? $config['system_timezone'] : 'Asia/Kolkata';
        date_default_timezone_set($timezone);

        $open  = (strlen($opening_time) === 5) ? $opening_time . ':00' : $opening_time;
        $close = (strlen($closing_time) === 5) ? $closing_time . ':00' : $closing_time;
        $now   = date('H:i:s');

        if($open === $close){
            return true; // a zero-length window is treated as 24 hours
        }
        if($open < $close){
            return ($now >= $open && $now < $close);
        }
        // Overnight window, e.g. 22:00 -> 06:00.
        return ($now >= $open || $now < $close);
    }

    /**
     * Human-readable "09:00 to 21:00" label for the configured operating
     * window, or '' when the service has no time restriction.
     */
    function get_parcel_operating_hours_label(){
        $hours = $this->get_parcel_operating_hours();
        if(!$hours['enabled']){
            return '';
        }
        return substr($hours['opening_time'], 0, 5) . ' to ' . substr($hours['closing_time'], 0, 5);
    }

    /**
     * Combined parcel availability: the manual service switch AND the daily
     * operating window. This is what the app and the booking gate should use.
     * Pass an effective settings array (from get_effective_parcel_settings) to
     * avoid a second lookup, or leave null to read the global settings.
     */
    function is_parcel_service_available_now($effective = null){
        if($effective === null){
            $effective = $this->get_effective_parcel_settings(null);
        }
        $manual = (int)$effective['is_service_available'] === 1;
        $open = $this->is_parcel_service_open_now(
            isset($effective['opening_time']) ? $effective['opening_time'] : null,
            isset($effective['closing_time']) ? $effective['closing_time'] : null
        );
        return ($manual && $open);
    }
}

?>