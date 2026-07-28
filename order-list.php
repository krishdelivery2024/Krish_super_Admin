<?php 
/*login*/
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
    $db = new Database();
    $db->connect();
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    date_default_timezone_set('Asia/Kolkata');
   /* accesskey:90336
    mobile:9974692496
    password:36652
    status:1   // 1 - Active & 0 Deactive */

if(isset($_POST['type'])){
    if($_POST['type']=="orders"){
        //$store_id=$_POST['store_id'];
        if(isset($_POST['sub_type'])){
            $where="";
           // $where="store_id=".$store_id." AND ";
            if($_POST['sub_type']=='received'){
    		    $where .="o.active_status='received'";
    		}else if($_POST['sub_type']=='processed'){
    		    $where .="o.active_status='processed'";
    		}else if($_POST['sub_type']=='shipped'){
    		    $where .="o.active_status='shipped'";
    		}else if($_POST['sub_type']=='delivered'){
    		    $where .="o.active_status='delivered'";
    		}else if($_POST['sub_type']=='cancelled'){
    		    $where .="o.active_status='cancelled'";
    		}else if($_POST['sub_type']=='returned'){
    		    $where .="o.active_status='returned'";
    		}
        }else{
         // $where="store_id=".$store_id." "; 
          $where="1"; 
        }
		if($where=="1"){
		    $sql = "select o.*,(select name from users u where u.id=o.user_id) as user_name from orders o ORDER BY date_added DESC";
		}else{
		    $sql = "select o.*,(select name from users u where u.id=o.user_id) as user_name from orders o WHERE ".$where." ORDER BY date_added DESC";
		}
	//	echo $sql;die;
    $db->sql($sql);
    $res = $db->getResult();
    $i=0; $j=0;
    foreach($res as $row){
        $res[$i]['o_month']=date_format(date_create($row['date_added']),'F');
        $res[$i]['o_day']=date_format(date_create($row['date_added']),'d');
        if($row['discount']>0){
            $discounted_amount = $row['total'] * $row['discount'] / 100; /*  */
    	    $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total']-$final_total;
            // echo $discount_in_rupees;
        } else {
            $discount_in_rupees = 0;
        }
        
        $res[$i]['discount_rupees'] = "$discount_in_rupees";
        $final_total = ($res[$i]['final_total']);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $sql = "select oi.*,p.name,p.image,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi join product_variant v on oi.product_variant_id=v.id join products p on p.id=v.product_id where order_id=".$row['id'];
        //echo $sql;die;
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        $res[$i]['status'] = json_decode($res[$i]['status']);
            
            for($j=0; $j < count($res[$i]['items']); $j++){
                $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status']))?json_decode($res[$i]['items'][$j]['status']):array();
                // unset($res[$i]['items'][$j]['status']);
                $res[$i]['items'][$j]['image'] = DOMAIN_URL.$res[$i]['items'][$j]['image'];
            }
        $i++;
    }
        $orders = $order = array();
        
        if(!empty($res)){
            $response['error'] = false;
            $response['data'] = array_values($res);
          
        }else{
            $response['error'] = true;
            $response['data']=array();
            $response['message'] = "No orders found!";
            // return $res;
        }
    }else if($_POST['type']=="order-detail" && isset($_POST['id'])){
		
		$sql = "select *,(select name from users u where u.id=o.user_id) as user_name from orders o WHERE o.id=".$_POST['id'];
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
        
        $res[$i]['discount_rupees'] = "$discount_in_rupees";
        $final_total = ($res[$i]['final_total']);
        $res[$i]['final_total'] = "$final_total";
        $res[$i]['date_added'] = date('d-m-Y h:i:sa', strtotime($res[$i]['date_added']));
        $sql = "select oi.*,p.name,p.category_id,p.subcategory_id,p.image,c.name AS category_name,v.measurement,(select short_code from unit u where u.id=v.measurement_unit_id) as unit from order_items oi join product_variant v on oi.product_variant_id=v.id join products p on p.id=v.product_id JOIN category c ON c.id=p.category_id where order_id=".$row['id'];
        $db->sql($sql);
        $res[$i]['items'] = $db->getResult();
        $res[$i]['status'] = json_decode($res[$i]['status']);
            for($j=0; $j < count($res[$i]['items']); $j++){
                if($res[$i]['items'][$j]['subcategory_id']==0){
                    $res[$i]['items'][$j]['subcategory_name']='';
                }else{
                    $sql1 = "select name from subcategory WHERE id=".$res[$i]['items'][$j]['subcategory_id'];
                    $db->sql($sql1);
                    $subc = $db->getResult();
                    $res[$i]['items'][$j]['subcategory_name']=$subc[0]['name'];
                }
                $res[$i]['items'][$j]['status'] = (!empty($res[$i]['items'][$j]['status']))?json_decode($res[$i]['items'][$j]['status']):array();
                // unset($res[$i]['items'][$j]['status']);
                $res[$i]['items'][$j]['image'] = DOMAIN_URL.$res[$i]['items'][$j]['image'];
            }
        $i++;
    }
        $orders = $order = array();
        
        if(!empty($res)){
            $response['error'] = false;
            $response['data'] = array_values($res);
          
        }else{
            $response['error'] = true;
            $response['data']=array();
            $response['message'] = "No orders found!";
            // return $res;
        }
    }else if($_POST['type']=="delivery-boy"){
       // $store_id=$_POST['store_id'];
    	$sql="SELECT id,name FROM delivery_boys WHERE status=1";
    	//	$sql="SELECT id,name FROM delivery_boys WHERE status=1 AND store_id=".$store_id;
    	//$sql="SELECT id,name FROM delivery_boys WHERE status=1";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="get_settings") {
	$sql = "select value from `settings` where variable='system_timezone'";
	$db->sql($sql);
	$res = $db->getResult();
	$sql = "select value from `settings` where variable='currency'";
	$db->sql($sql);
	$res_currency = $db->getResult();
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
            print_r(json_encode($response));
            
        }else{
            $response['error'] = true;
            $response['settings'] = "No settings found!";
            $response['message'] = "Something went wrong!";
            print_r(json_encode($response));
            
        }
}else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
}else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
        }
echo json_encode($response);
$db->disconnect();
?>