<?php 
/*login*/
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
    $db = new Database();
    $db->connect();
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    date_default_timezone_set('Asia/Kolkata');
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if($access_key != $accesskey){
    $response['error']= true;
    $response['message']="invalid accesskey";
    print_r(json_encode($response));
    return false;
}
if(isset($_POST['type'])){
     if($_POST['type']=="high_selling_products"){
         
         if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
             $start_date=date('Y-m-d 00:00:00',strtotime($_POST['start_date']));
             $end_date=date('Y-m-d 23:59:59',strtotime($_POST['end_date']));
             //$store_id=$_POST['store_id'];
             $sql="SELECT
                        	p.name,
                        	v.product_id,
                        	sum(oi.quantity) AS qty,
                        	v.measurement,
                        	count(o.id) AS order_count,
                        	p.NAME AS pname,(
                        	SELECT
                        		short_code 
                        	FROM
                        		unit un 
                        	WHERE
                        		un.id = v.measurement_unit_id 
                        	) AS mesurement_unit_name 
                            FROM
                        	`order_items` oi
                        	JOIN orders o ON o.id = oi.order_id
                        	JOIN product_variant v ON oi.product_variant_id = v.id
                        	JOIN products p ON p.id = v.product_id
                        	WHERE oi.active_status!='cancelled' AND oi.date_added < '" . $end_date . "' and oi.date_added >'" . $start_date . "'
                        	GROUP BY v.id
                        	ORDER BY qty DESC";
         }else{
             $sql="SELECT
                        	p.name,
                        	v.product_id,
                        	sum(oi.quantity) AS qty,
                        	v.measurement,
                        	count(o.id) AS order_count,
                        	p.NAME AS pname,(
                        	SELECT
                        		short_code 
                        	FROM
                        		unit un 
                        	WHERE
                        		un.id = v.measurement_unit_id 
                        	) AS mesurement_unit_name 
                            FROM
                        	`order_items` oi
                        	JOIN orders o ON o.id = oi.order_id
                        	JOIN product_variant v ON oi.product_variant_id = v.id
                        	JOIN products p ON p.id = v.product_id
                        	WHERE oi.active_status!='cancelled' 
                        	GROUP BY v.id
                        	ORDER BY qty DESC";
         }
         //echo $sql;
         $db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="high_buying_customers"){
        if(isset($_POST['filter_by']) ){
            $cdate=date('Y-m-d');
            if($_POST['filter_by']=='day'){
                $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added > '" . $cdate . "' 
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }else if($_POST['filter_by']=='week'){
                $odate=date('Y-m-d',strtotime('last sunday'));
                $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added >'" . $odate . "'
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }else if($_POST['filter_by']=='month'){
                $odate=date('Y-m-01');
                $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added >'" . $odate . "'
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }
        }else if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            
             $start_date=date('Y-m-d 00:00:00',strtotime($_POST['start_date']));
             $end_date=date('Y-m-d 23:59:59',strtotime($_POST['end_date']));
             $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added < '" . $end_date . "' and orders.date_added >'" . $start_date . "'
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
         }else{
             $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' 
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
         }
         $db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="customer_orders" && isset($_POST['id'])){
        $cdate=date('Y-m-d');
		$odate=date('Y-01-01');
                $sql="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	users.street,
                    	users.pincode,
                    	users.city,
                    	users.area,
                    	orders.id AS order_id,
                    	DATE_FORMAT((orders.date_added),'%b %d') AS order_date,
                    	orders.final_total
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE users.id=".$_POST['id']." AND orders.date_added >'" . $odate . "'
		                    ORDER BY orders.id DESC";
		$db->sql($sql);
		//echo $sql;
		$res = $db->getResult();
		$resp=array();
		for($i=0;$i<count($res);$i++){
		    $resp['name']=$res[$i]['name'];
		    $resp['email']=$res[$i]['email'];
		    $resp['user_id']=$res[$i]['id'];
		    $sql="SELECT name FROM city WHERE id=".$res[$i]['city'];
		    $db->sql($sql);
		    $city_query = $db->getResult();
		    $resp['city']=$city_query[0]['name'];
		    
		    $sql="SELECT name FROM area WHERE id=".$res[$i]['area'];
		    $db->sql($sql);
		    $area_query = $db->getResult();
		    $resp['area']=$area_query[0]['name'];
		    $resp['country_code']=$res[$i]['country_code'];
		    $resp['mobile']=$res[$i]['mobile'];
		    $resp['street']=$res[$i]['street'];
		    $resp['pincode']=$res[$i]['pincode'];
		    $resp['orders'][]=$res[$i];
		}
		$response['error']     = false;
		$response['data'] = $resp;
    }else if($_POST['type']=="order-detail" && isset($_POST['id'])){
               $sql = "select o.*,u.name,u.email from orders o join users u on u.id=o.user_id WHERE o.id=".$_POST['id'];
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
        $sql="SELECT COUNT(id) AS item_count FROM order_items WHERE order_id=".$res[$i]['id'];
		    $db->sql($sql);
		    $resa = $db->getResult();
		    $res[$i]['item_count']=$resa[0]['item_count'];
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
    }else if($_POST['type']=="sales_list"){
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            
             $start_date=date('Y-m-d 00:00:00',strtotime($_POST['start_date']));
             $end_date=date('Y-m-d 23:59:59',strtotime($_POST['end_date']));
            $sql="SELECT id AS order_id,total,mobile,active_status,date_added,DATE_FORMAT(date_added, '%d-%m-%Y') AS order_date,final_total,payment_method FROM orders 
            WHERE orders.active_status!='cancelled' AND orders.date_added < '" . $end_date . "' and orders.date_added >'" . $start_date . "'
            ORDER BY id DESC";
        }else{
            $sql="SELECT id AS order_id,total,mobile,active_status,date_added,DATE_FORMAT(date_added, '%d-%m-%Y') AS order_date,final_total,payment_method FROM orders  WHERE orders.active_status!='cancelled' ORDER BY id DESC";
        }
		$db->sql($sql);
		$res = $db->getResult();
		for($i=0;$i<count($res);$i++){
		    $sql="SELECT COUNT(id) AS item_count FROM order_items WHERE order_id=".$res[$i]['order_id'];
		    $db->sql($sql);
		    $resa = $db->getResult();
		    $res[$i]['item_count']=$resa[0]['item_count'];
		};
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="invoice_list"){
        if (isset($_GET['keyword'])) {
        // check value of keyword variable
            $keyword = $_GET['keyword'];
        } else {
            $keyword = "";
        }
        if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
            if (empty($keyword)) {
                $sql="SELECT id,invoice_date,order_id,name FROM invoice 
                WHERE invoice_date < '" . $_POST['end_date'] . "' and invoice_date >'" . $_POST['start_date'] . "'
                ORDER BY id DESC";
            }else{    
                $sql="SELECT id,invoice_date,order_id,name FROM invoice 
                WHERE invoice_date < '" . $_POST['end_date'] . "' and invoice_date >'" . $_POST['start_date'] . "' AND name LIKE '%".$keyword."%'
                ORDER BY id DESC";
            }
        }else{
            if (empty($keyword)) {
                $sql="SELECT id,invoice_date,order_id,name FROM invoice ORDER BY id DESC";
            }else{
                $sql="SELECT id,invoice_date,order_id,name FROM invoice WHERE name LIKE '%".$keyword."%' ORDER BY id DESC";
            }
        }
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
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