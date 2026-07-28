<?php
	session_start();
    // Seller Details
	if($_SESSION['role'] == 'seller'){
	$main_cat_id = $_SESSION['main_cat_id'];
	$seller_id = $_SESSION['id'];
	}else{
			$main_cat_id = 0;
			$seller_id = 0;
	}

    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    
    // if session not set go to login page
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }
    
    // if current time is more than session timeout back to login page
    if ($currentTime > $_SESSION['timeout']) {
			if($_SESSION['role'] == 'seller'){
			$redirect ='seller-login.php';
			}else{
			$redirect ='index.php';
			}	
			session_destroy();
			header("location:$redirect");
    }
    
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    
    header('Access-Control-Allow-Origin: *');
	header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
	
	
	include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('../includes/crud.php');
	include_once('../includes/variables.php');
	$db = new Database();
	$db->connect();
	$config = $fn->get_configurations();
	
	if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
		date_default_timezone_set($config['system_timezone']);
		$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
	}else{
    	date_default_timezone_set('Asia/Kolkata');
    	$db->sql("SET `time_zone` = '+05:30'");
    }
	
	//data of 'ORDERS' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'orders'){
		$offset = 0; $limit = 10;
		$sort = 'o.id'; $order = 'DESC';

		
		$sort_map = array(
			'id'              => 'o.id',
			'user_id'         => 'o.user_id',
			'qty'             => 'o.id',
			'sname'           => 's.name',
			'smobile'         => 's.mobile',
			'name'            => 'u.name',
			'mobile'          => 'o.mobile',
			'total'           => 'o.total',
			'delivery_charge' => 'o.delivery_charge',
			'discount'        => 'o.total',          // discount is computed, fallback
			'promo_code'      => 'o.promo_code',
			'promo_discount'  => 'o.promo_discount',
			'wallet_balance'  => 'o.wallet_balance',
			'final_total'     => 'o.final_total',
			'payment_method'  => 'o.payment_method',
			'address'         => 'o.address',
			'delivery_time'   => 'o.delivery_time',
			'active_status'   => 'o.active_status',
			'date_added'      => 'o.date_added',
		);

		$where = " where (o.payment_method='cod' OR o.payment_method='wallet' OR o.payment_method='LoyaltyPoints' OR o.payment_status=1)";
// 		if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
// 			$where .= " AND DATE(o.date_added)>=DATE('".$db->escapeString($_GET['start_date'])."') AND DATE(o.date_added)<=DATE('".$db->escapeString($_GET['end_date'])."')";
// 		}

		// ---- Safely resolve sort column (whitelist only, prevents SQL injection + unknown column errors) ----
		if(isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_map)){
			$sort = $sort_map[$_GET['sort']];
		}

		// ---- Safely resolve sort order (only ASC/DESC allowed) ----
		if(isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC'){
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		if(isset($_GET['offset']))
			$offset = (int) $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = (int) $_GET['limit'];

		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $db->escapeString($_GET['search']);
			// All columns fully qualified now -> fixes the "ambiguous column 'name'" fatal error
			$where .= " AND (u.name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR o.address like '%".$search."%' OR o.`payment_method` like '%".$search."%' OR o.`delivery_charge` like '%".$search."%' OR o.`delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR o.`date_added` like '%".$search."%' OR s.name like '%".$search."%' OR s.mobile like '%".$search."%')";
		}

        if(isset($_GET['filter_order']) && $_GET['filter_order']!=''){
            $filter_order=$db->escapeString($_GET['filter_order']);
            $where .=" and o.`active_status`='".$filter_order."'";
        }

        if(!empty($_GET['deliver_by'])){
            $where .=" and o.`delivery_boy_id`='".(int)$_GET['deliver_by']."'";
        }
		if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller'){
            $where .= " and o.`seller_id`='".(int)$seller_id."'";
        }


		$sql = "SELECT COUNT(o.id) as total FROM `orders` o JOIN users u ON u.id=o.user_id LEFT JOIN seller s ON s.id=o.seller_id".$where;
		$db->sql($sql);
		$res = $db->getResult();
		$total = isset($res)?$res[0]['total']:0;

        $sql="select o.*, u.name, s.name as vendor_name, s.mobile as vendor_mobile 
              FROM orders o 
              JOIN users u ON u.id=o.user_id 
              LEFT JOIN seller s ON s.id=o.seller_id".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		for($i=0;$i<count($res);$i++) {
			$sql="select oi.*,p.name as name, u.name as uname,v.measurement, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT status FROM orders o where o.id=oi.order_id)as order_status from `order_items` oi 
			    join product_variant v on oi.product_variant_id=v.id 
			    join products p on p.id=v.product_id 
			    JOIN users u ON u.id=oi.user_id 
			    where oi.order_id=".$res[$i]['id'];
    		$db->sql($sql);
    		$res[$i]['items'] = $db->getResult();
	    }
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		foreach($res as $row){
		    if(!empty($row['items'])){
    			$items = $row['items'];
    			$items1='';
    			$temp = '';
    			$total_amt=0;
    			foreach($items as $item){
    				$temp .= "<b>ID :</b>".$item['id']."<b> Product Variant Id :</b> ".$item['product_variant_id']."<b> Name : </b>".$item['name']." <b>Unit : </b>".$item['measurement'].$item['mesurement_unit_name']." <b>Price : </b>".$item['price']." <b>QTY : </b>".$item['quantity']." <b>Subtotal : </b>".$item['quantity']*$item['price']."<br>------<br>";
    				$total_amt += $item['sub_total'];
    			}
    
    			$items1 = $temp;
    			$temp = '';
    			$status = json_decode($row['items'][0]['order_status']);
    			if(!empty($status)){
        			foreach($status as $st){
        				$temp .= $st[0]." : ".$st[1]."<br>------<br>";
        			}
    			}
    			if($row['active_status']=='received'){
                    $active_status = '<label class="label label-primary">'.$row['active_status'].'</label>';
                }
                if($row['active_status']=='processed'){
                    $active_status = '<label class="label label-info">'.$row['active_status'].'</label>';
                }
                if($row['active_status']=='shipped'){
                    $active_status = '<label class="label label-warning">'.$row['active_status'].'</label>';
                }
                if($row['active_status']=='delivered'){
                    $active_status = '<label class="label label-success">'.$row['active_status'].'</label>';
                }
                if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
                    $active_status = '<label class="label label-danger">'.$row['active_status'].'</label>';
                }
    			$status = $temp;
    			$operate = "<a class='btn btn-sm btn-primary edit-fees' data-id='".$row['id']."' data-toggle='modal' data-target='#editFeesModal'>Edit</a>";
    			
    			$operate .= "<a onclick='return conf(\"delete\");' class='btn btn-sm btn-danger' href='../public/db_operations.php?id=".$row['id']."&delete_order=1' target='_blank'>Delete</a>";
    			$discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100;
        	    $final_total = $row['total'] - $discounted_amount;
                $discount_in_rupees = $row['total']-$final_total;
                $discount_in_rupees = floor($discount_in_rupees);
    			$tempRow['id'] = $row['id'];
    			$tempRow['user_id'] = $row['user_id'];
    			$tempRow['name'] = $row['items'][0]['uname'];
    			$tempRow['mobile'] = $row['mobile'];
    			$tempRow['delivery_charge'] = $row['delivery_charge'];
    			$tempRow['items']=$items1;
    			$tempRow['total']=$row['total'];
    			$tempRow['tax']=$row['tax_amount'].'('.$row['tax_percentage'].'%)';
    			$tempRow['promo_discount']=$row['promo_discount'];
    			$tempRow['wallet_balance']=$row['wallet_balance'];
    			$tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
    			$tempRow['qty'] = $row['items'][0]['quantity'];
    			$tempRow['final_total'] = ($row['final_total']);
    			$tempRow['promo_code'] = $row['promo_code'];
    			$tempRow['deliver_by'] = $row['items'][0]['deliver_by'];
    			$tempRow['payment_method'] = $row['payment_method'];
    			$tempRow['address'] = $row['address'];
    			$tempRow['delivery_time'] = $row['delivery_time'];
    			$tempRow['status'] = $status;
    			$tempRow['active_status'] = $active_status;
    			$tempRow['wallet_balance'] = $row['wallet_balance'];
    			$tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
    			
    			if(isset($_SESSION['role']) && $_SESSION['role'] != 'seller'){
    			    $tempRow['sname'] = $row['vendor_name'];
    			    $tempRow['smobile'] = $row['vendor_mobile'];
    			}
    			if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller'){
    			    $tempRow['operate'] = '<a href="order-detail.php?id='.$row['id'].'"><i class="fa fa-eye"></i> View</a>
    				    <br><a href="delete-order.php?id='.$row['id'].'"><i class="fa fa-trash"></i> Delete</a>';
    			}
    			$rows[] = $tempRow;
    		}
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	
	
		//data of 'ORDERS' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'incomplte_orders'){
		$offset = 0; $limit = 10;
		$sort = 'o.id'; $order = 'DESC';
		$where = " where o.payment_method!='cod' AND o.payment_method!='wallet' AND o.payment_method!='LoyaltyPoints'  AND o.payment_status=0";
		
		if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
			$where .= " AND DATE(date_added)>=DATE('".$_GET['start_date']."') AND DATE(date_added)<=DATE('".$_GET['end_date']."')";
		}
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		if(isset($_GET['search']) && !empty($_GET['search'])){
			$search = $_GET['search'];
			if(!empty($_GET['start_date']) && !empty($_GET['end_date'])){
				$where .= " AND (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR o.address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
			} else{
				$where .= " AND (name like '%".$search."%' OR o.id like '%".$search."%' OR o.mobile like '%".$search."%' OR o.address like '%".$search."%' OR `payment_method` like '%".$search."%' OR `delivery_charge` like '%".$search."%' OR `delivery_time` like '%".$search."%' OR o.`status` like '%".$search."%' OR `date_added` like '%".$search."%')";
			}
		}

		$where .=" AND `seller_id`='".$seller_id."'";
       
		$sql = "SELECT COUNT(o.id) as total FROM `orders` o JOIN users u ON u.id=o.user_id".$where;
		$db->sql($sql);
		$res = $db->getResult();
		$total = isset($res)?$res[0]['total']:0;
		$sql="select o.*,u.name FROM orders o JOIN users u ON u.id=o.user_id".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
 		//echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
 		//print_r($res);
		for($i=0;$i<count($res);$i++) {
			$sql="select oi.*,p.name as name, u.name as uname,v.measurement, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name,(SELECT status FROM orders o where o.id=oi.order_id)as order_status from `order_items` oi 
			    join product_variant v on oi.product_variant_id=v.id 
			    join products p on p.id=v.product_id 
			    JOIN users u ON u.id=oi.user_id 
			    where oi.order_id=".$res[$i]['id'];
    		$db->sql($sql);
    		$res[$i]['items'] = $db->getResult();
	    }
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		// print_r($res);
		foreach($res as $row){
			$items = $row['items'];
// 			print_r($items);
			$items1='';
			$temp = '';
			$total_amt=0;
			foreach($items as $item){
				$temp .= "<b>ID :</b>".$item['id']."<b> Product Variant Id :</b> ".$item['product_variant_id']."<b> Name : </b>".$item['name']." <b>Unit : </b>".$item['measurement'].$item['mesurement_unit_name']." <b>Price : </b>".$item['price']." <b>QTY : </b>".$item['quantity']." <b>Subtotal : </b>".$item['quantity']*$item['price']."<br>------<br>";
				$total_amt += $item['sub_total'];
			}

			$items1 = $temp;
			$temp = '';
			$status = json_decode($row['items'][0]['order_status']);
			if(!empty($status)){
    			foreach($status as $st){
    				$temp .= $st[0]." : ".$st[1]."<br>------<br>";
    			}
			}
			if($row['active_status']=='received'){
                $active_status = '<label class="label label-primary">'.$row['active_status'].'</label>';
            }
            if($row['active_status']=='processed'){
                $active_status = '<label class="label label-info">'.$row['active_status'].'</label>';
            }
            if($row['active_status']=='shipped'){
                $active_status = '<label class="label label-warning">'.$row['active_status'].'</label>';
            }
            if($row['active_status']=='delivered'){
                $active_status = '<label class="label label-success">'.$row['active_status'].'</label>';
            }
            if($row['active_status']=='returned' || $row['active_status'] == 'cancelled' ){
                $active_status = '<label class="label label-danger">'.$row['active_status'].'</label>';
            }
			// print_r($res[0]['items']);
// 			$total = ($total_amt + $row['delivery_charge']) - $row['wallet_balance'];
// 			$discounted_amount = $total*$row['items'][0]['discount']/100;
			$status = $temp;
			$operate = "<a class='btn btn-sm btn-primary edit-fees' data-id='".$row['id']."' data-toggle='modal' data-target='#editFeesModal'>Edit</a>";
			
			$operate .= "<a onclick='return conf(\"delete\");' class='btn btn-sm btn-danger' href='../public/db_operations.php?id=".$row['id']."&delete_order=1' target='_blank'>Delete</a>";
			$discounted_amount = $row['total'] * $row['items'][0]['discount'] / 100; /*  */
    	    $final_total = $row['total'] - $discounted_amount;
            $discount_in_rupees = $row['total']-$final_total;
            $discount_in_rupees = floor($discount_in_rupees);
			$tempRow['id'] = $row['id'];
			$tempRow['user_id'] = $row['user_id'];
			$tempRow['name'] = $row['items'][0]['uname'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['delivery_charge'] = $row['delivery_charge'];
			$tempRow['items']=$items1;
			$tempRow['total']=$row['total'];
			$tempRow['tax']=$row['tax_amount'].'('.$row['tax_percentage'].'%)';
			$tempRow['promo_discount']=$row['promo_discount'];
			$tempRow['wallet_balance']=$row['wallet_balance'];
			$tempRow['discount'] = $discount_in_rupees.'('.$row['items'][0]['discount'].'%)';
			$tempRow['qty'] = $row['items'][0]['quantity'];
			// 	$tempRow['final_total'] = $row['final_total'];
// 			$tempRow['final_total'] = ceil($total-$discounted_amount);
			$tempRow['final_total'] = ($row['final_total']);
			$tempRow['promo_code'] = $row['promo_code'];
			$tempRow['deliver_by'] = $row['items'][0]['deliver_by'];
			$tempRow['payment_method'] = $row['payment_method'];
			$tempRow['address'] = $row['address'];
			$tempRow['delivery_time'] = $row['delivery_time'];
			// $tempRow['items'] = $items;
			$tempRow['status'] = $status;
			$tempRow['active_status'] = $active_status;
			$tempRow['wallet_balance'] = $row['wallet_balance'];
			$tempRow['date_added'] = date('d-m-Y',strtotime($row['date_added']));
			$tempRow['operate'] ='<a href="order-detail.php?id='.$row['id'].'"><i class="fa fa-eye"></i> View</a>
				<br><a href="delete-order.php?id='.$row['id'].'"><i class="fa fa-trash"></i> Delete</a>
				<br><a class="btn-sm btn-primary mt-3 update_payment_status" href="javascript:void(0)" data-id='.$row['id'].'><i class="fa fa-arrow-up"></i> Update Payment</a>';
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	
	// data of 'CATEGORY' table goes here
	if (isset($_GET['table']) && $_GET['table'] === 'category') {

		// Default values
		$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
		$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
		$sort = isset($_GET['sort']) ? $_GET['sort'] : 'c.id';
		$order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC') ? 'ASC' : 'DESC';

		// Whitelist for sortable columns
		$allowedSorts = ['c.id', 'c.name', 'c.subtitle', 'c.image'];
		if (!in_array($sort, $allowedSorts)) {
			$sort = 'c.id';
		}

		// Initialize WHERE clause
		$where = '';
		if (isset($_GET['search']) && !empty($_GET['search'])) {
			$search = $db->escapeString($_GET['search']);
			$where = " WHERE `c`.`id` LIKE '%$search%' OR `c`.`name` LIKE '%$search%' OR `c`.`subtitle` LIKE '%$search%' OR `c`.`image` LIKE '%$search%'";
		}

		// Get total count
		$sql = "SELECT COUNT(*) as total FROM `category` c $where";
		$db->sql($sql);
		$res = $db->getResult();
		$total = $res[0]['total'] ?? 0;

		// Get category data
		$sql = "SELECT c.*, (SELECT name FROM main_category m WHERE m.id = c.main_cat) AS main_category_name 
				FROM `category` c $where 
				ORDER BY $sort $order 
				LIMIT $offset, $limit";
		$db->sql($sql);
		$res = $db->getResult();

		// Build response
		$bulkData = [];
		$bulkData['total'] = $total;
		$rows = [];

		foreach ($res as $row) {
			$id = htmlspecialchars($row['id']);
			$name = htmlspecialchars($row['name']);
			$subtitle = htmlspecialchars($row['subtitle']);
			$imageUrl = htmlspecialchars($row['image']);
			$main_category_name = htmlspecialchars($row['main_category_name']);

			$operate = '';
			$operate .= '<a href="edit-category.php?id=' . $id . '"><i class="fa fa-edit"></i>Edit</a> ';
			$operate .= '<a class="btn-xs btn-danger" href="delete-category.php?id=' . $id . '"><i class="fa fa-trash-o"></i>Delete</a>';

			$rows[] = [
				'id' => $id,
				'main_category_name' => $main_category_name,
				'name' => $name,
				'subtitle' => $subtitle,
				'image' => "<a data-lightbox='category' href='$imageUrl' data-caption='$name'><img src='$imageUrl' title='$name' style='height:50px !important' /></a>",
				'operate' => $operate
			];
		}

		$bulkData['rows'] = $rows;
		echo json_encode($bulkData);
	}


	// data of 'CATEGORY' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'main_category'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR  `image` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `main_category` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `main_category` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			$operate ='';
			$operate .= ' <a href="edit-main-category.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			// $operate .= ' <a class="btn-xs btn-danger" href="main_categories.php?id='.$row['id'].'&delete=yes"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['image'] = "<a data-lightbox='category' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
		// data of 'BRAND' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'brand'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `brand` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `brand` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			
			$operate= ' <a href="edit-brand.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-brand.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['image'] = "<a data-lightbox='brand' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
		// data of 'product_rating' table goes here  
		if (isset($_GET['table']) && $_GET['table'] == 'product_rating') {

			$offset = 0;
			$limit = 10;
			$sort = 'pr.id';
			$order = 'DESC';

			$where = "WHERE p.seller_id = $seller_id";

			if (isset($_GET['offset'])) {
				$offset = $_GET['offset'];
			}
			if (isset($_GET['limit'])) {
				$limit = $_GET['limit'];
			}
			if (isset($_GET['sort'])) {
				$sort = $_GET['sort'];
			}
			if (isset($_GET['order'])) {
				$order = $_GET['order'];
			}

			// Handle search
			if (isset($_GET['search'])) {
				$search = $db->escapeString($_GET['search']); // sanitize input
				$where .= " AND (pr.id LIKE '%$search%' OR pr.product_id LIKE '%$search%' OR pr.user_id LIKE '%$search%')";
			}

			// Get total count
			$sql = "SELECT COUNT(*) as total 
					FROM product_rating pr 
					INNER JOIN products p ON pr.product_id = p.id 
					$where";
			$db->sql($sql);
			$res = $db->getResult();
			$total = isset($res[0]['total']) ? $res[0]['total'] : 0;

			// Get data
			$sql = "SELECT pr.* 
					FROM product_rating pr 
					INNER JOIN products p ON pr.product_id = p.id 
					$where 
					ORDER BY $sort $order 
					LIMIT $offset, $limit";
			$db->sql($sql);
			$res = $db->getResult();

			$bulkData = array();
			$bulkData['total'] = $total;
			$rows = array();
			$tempRow = array();

			foreach ($res as $row) {
				// Get product name
				$sql = "SELECT name FROM products WHERE id='" . $row['product_id'] . "'";
				$db->sql($sql);
				$res1 = $db->getResult();

				// Get user name
				$sql = "SELECT name FROM users WHERE id='" . $row['user_id'] . "'";
				$db->sql($sql);
				$res2 = $db->getResult();

				$tempRow['id'] = $row['id'];
				$tempRow['product_id'] = $row['product_id'];
				$tempRow['name'] = isset($res1[0]['name']) ? $res1[0]['name'] : '';
				$tempRow['username'] = isset($res2[0]['name']) ? $res2[0]['name'] : '';
				$tempRow['user_id'] = $row['user_id'];
				$tempRow['order_id'] = $row['order_id'];
				$tempRow['rating'] = $row['rating'];
				$tempRow['rating_desc'] = $row['rating_desc'];
				$tempRow['created_at'] = $row['created_at'];

				$rows[] = $tempRow;
			}

			$bulkData['rows'] = $rows;
			echo json_encode($bulkData);
		}

	// data of 'SUBCATEGORY' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'subcategory'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where s.`id` like '%".$search."%' OR `name` like '%".$search."%' OR `subtitle` like '%".$search."%' OR `image` like '%".$search."%'";
		}
		
// 		$sql = "SELECT COUNT(*) as total FROM `subcategory` ".$where;
		$sql = "SELECT COUNT(*) as total FROM `subcategory` s".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT s.*,(SELECT name FROM category c WHERE c.id=s.category_id) as category_name FROM `subcategory` s".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			
			$operate = '<a href="view-subcategory-product.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Products</a>';
			$operate .= ' <a href="edit-subcategory.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-subcategory.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['category_name'] = $row['category_name'];
			$tempRow['subtitle'] = $row['subtitle'];
			$tempRow['image'] = "<a data-lightbox='category' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	// data of 'PRODUCTS' table goes here
    
    	if(isset($_GET['table']) && $_GET['table'] == 'min-stock-products'){
// 		print_r($_GET);
		
		$offset = 0; $limit = 10000;
		$sort = 'id'; $order = 'ASC';
		$where = "WHERE p.`seller_id` = $seller_id";
		
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
		if($_GET['sort']=='id'){
		    $sort="id";
		}else{
			$sort = $_GET['sort'];
		}
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) AND $_GET['search']!=''){
			$search = $_GET['search'];
			$where .= " AND (p.`id` like '%".$search."%' OR p.`name` like '%".$search."%' OR pv.`measurement` like '%".$search."%' OR u.`short_code` like '%".$search."%' )";
		}

		if(isset($_GET['category_id']) && $_GET['category_id'] !=''){
			$category_id = $_GET['category_id'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' AND p.`category_id`='.$category_id;
			else
				$where .=' AND p.`category_id`='.$category_id;
		}
		
		$join = "JOIN `product_variant` pv ON pv.product_id = p.id
            LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
		
		$sql = "SELECT COUNT(p.id) as `total` FROM `products` p $join ".$where."" ;
// 		echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
			$total = $res[0]['total'];
		
// 		$sql = "SELECT * FROM products ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
        $sql = "SELECT p.id AS id, p.name, p.image, pv.price, pv.discounted_price, pv.measurement, pv.serve_for, pv.stock,pv.barcode_data,p.min_stock, u.short_code 
            FROM `products` p
            $join 
            $where ORDER BY $sort $order LIMIT $offset, $limit";
        // echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		$currency = $fn->get_settings('currency',false);
		
		foreach($res as $row){
			
			$operate = '<a href="view-product-variants.php?id='.$row['id'].'"><i class="fa fa-folder-open"></i>View</a>';
			$operate .= ' <a href="edit-product.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-product.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			if($row['barcode_data']!=''){
				//$tempRow['barcode_data'] = "<div style='text-align:center'><img alt='' src='barcode/barcode.php?codetype=Code39&size=40&text=".$row['barcode_data']."&print=true'/><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div>";
			    $tempRow['barcode_data'] = "<div style='text-align:center'><svg class='barcode'  jsbarcode-value='".$row['barcode_data']."'  jsbarcode-textmargin='0' jsbarcode-height='50'  jsbarcode-fontoptions='bold'></svg></div><div style='text-align:center'><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div><script>JsBarcode('.barcode').init();</script>";
			    
			}else{
				
				$tempRow['barcode_data'] = "No Barcode";
			}
			$tempRow['name'] = $row['name'];
			$tempRow['measurement'] = $row['measurement']." ".$row['short_code'];
			$tempRow['price'] = $currency." ".$row['price'];
			$tempRow['discounted_price'] = $currency." ".$row['discounted_price'];
			$tempRow['serve_for'] = $row['serve_for'];
			$tempRow['stock'] = $row['stock'];
			$tempRow['min_stock'] = $row['min_stock'];
			$tempRow['image'] = "<a data-lightbox='product' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			if($row['stock']<=$row['min_stock']){
			    $rows[] = $tempRow;
			}
		}
		$bulkData['total'] = count($rows);
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	if(isset($_GET['table']) && $_GET['table'] == 'products'){
// 		print_r($_GET);
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'ASC';
		
		$where = "WHERE p.`seller_id` = $seller_id";

		if(isset($_GET['offset']))
			$offset = (int)$_GET['offset'];

		if(isset($_GET['limit']))
			$limit = (int)$_GET['limit'];

		if(isset($_GET['sort'])) {
			$sort = ($_GET['sort'] == 'id') ? 'id' : $_GET['sort'];
		}

		if(isset($_GET['order']))
			$order = $_GET['order'];

		if(isset($_GET['search']) && $_GET['search'] != '') {
			$search = $_GET['search'];
			$where .= " AND (p.`id` LIKE '%$search%' OR p.`name` LIKE '%$search%' OR pv.`measurement` LIKE '%$search%' OR u.`short_code` LIKE '%$search%')";
		}

		if(isset($_GET['category_id']) && $_GET['category_id'] != '') {
			$category_id = (int)$_GET['category_id'];
			$where .= " AND p.`category_id` = $category_id";
		}

		$join = "JOIN `product_variant` pv ON pv.product_id = p.id
				LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
		
		$sql = "SELECT COUNT(p.id) as `total` FROM `products` p $join ".$where."" ;
// 		echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
// 		$sql = "SELECT * FROM products ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
        $sql = "SELECT p.id AS id, p.name, p.image, pv.price, pv.discounted_price, pv.measurement, pv.serve_for, pv.stock,pv.barcode_data, u.short_code ,p.is_active
            FROM `products` p
            $join 
            $where ORDER BY $sort $order LIMIT $offset, $limit";
        // echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		$currency = $fn->get_settings('currency',false);
		
		foreach($res as $row){
			
			$operate = '<a href="view-product-variants.php?id='.$row['id'].'"><i class="fa fa-folder-open"></i>View</a>';
			$operate .= ' <a href="edit-product.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-product.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			if($row['barcode_data']!=''){
				//$tempRow['barcode_data'] = "<div style='text-align:center'><img alt='' src='barcode/barcode.php?codetype=Code39&size=40&text=".$row['barcode_data']."&print=true'/><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div>";
			    $tempRow['barcode_data'] = "<div style='text-align:center'><svg class='barcode'  jsbarcode-value='".$row['barcode_data']."'  jsbarcode-textmargin='0' jsbarcode-height='50'  jsbarcode-fontoptions='bold'></svg></div><div style='text-align:center'><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div><script>JsBarcode('.barcode').init();</script>";
			    
			}else{
				
				$tempRow['barcode_data'] = "No Barcode";
			}
			$tempRow['name'] = $row['name'];
			$tempRow['measurement'] = $row['measurement']." ".$row['short_code'];
			$tempRow['price'] = $currency." ".$row['price'];
			$tempRow['discounted_price'] = $currency." ".$row['discounted_price'];
			$tempRow['serve_for'] = $row['serve_for'];
			$tempRow['stock'] = $row['stock'];
			$tempRow['is_active'] = ($row['is_active'] == 1)
			? '<span style="color: green;">Active</span>'
			: '<span style="color: red;">Inactive</span>';

			$tempRow['image'] = "<a data-lightbox='product' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}

	if(isset($_GET['table']) && $_GET['table'] == 'products_unlisted'){
// 		print_r($_GET);
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'ASC';
		
		$where = "WHERE p.`is_active` = 0";

		if(isset($_GET['offset']))
			$offset = (int)$_GET['offset'];

		if(isset($_GET['limit']))
			$limit = (int)$_GET['limit'];

		if(isset($_GET['sort'])) {
			$sort = ($_GET['sort'] == 'id') ? 'id' : $_GET['sort'];
		}

		if(isset($_GET['order']))
			$order = $_GET['order'];

		if(isset($_GET['search']) && $_GET['search'] != '') {
			$search = $_GET['search'];
			$where .= " AND (p.`id` LIKE '%$search%' OR p.`name` LIKE '%$search%' OR pv.`measurement` LIKE '%$search%' OR u.`short_code` LIKE '%$search%')";
		}

		if(isset($_GET['category_id']) && $_GET['category_id'] != '') {
			$category_id = (int)$_GET['category_id'];
			$where .= " AND p.`category_id` = $category_id";
		}

		$join = "JOIN `product_variant` pv ON pv.product_id = p.id
				LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
		
		$sql = "SELECT COUNT(p.id) as `total` FROM `products` p $join ".$where."" ;
// 		echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
// 		$sql = "SELECT * FROM products ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
        $sql = "SELECT p.id AS id, p.name, p.image, pv.price, pv.discounted_price, pv.measurement, pv.serve_for, pv.stock,pv.barcode_data, u.short_code, p.is_active 
            FROM `products` p
            $join 
            $where ORDER BY $sort $order LIMIT $offset, $limit";
        // echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		$currency = $fn->get_settings('currency',false);
		
		foreach($res as $row){
			
			$operate = '<a href="view-product-variants.php?id='.$row['id'].'"><i class="fa fa-folder-open"></i>View</a>';
			$operate .= ' <a href="edit-product.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-product.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			if($row['barcode_data']!=''){
				//$tempRow['barcode_data'] = "<div style='text-align:center'><img alt='' src='barcode/barcode.php?codetype=Code39&size=40&text=".$row['barcode_data']."&print=true'/><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div>";
			    $tempRow['barcode_data'] = "<div style='text-align:center'><svg class='barcode'  jsbarcode-value='".$row['barcode_data']."'  jsbarcode-textmargin='0' jsbarcode-height='50'  jsbarcode-fontoptions='bold'></svg></div><div style='text-align:center'><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div><script>JsBarcode('.barcode').init();</script>";
			    
			}else{
				
				$tempRow['barcode_data'] = "No Barcode";
			}
			$tempRow['name'] = $row['name'];
			$tempRow['measurement'] = $row['measurement']." ".$row['short_code'];
			$tempRow['price'] = $currency." ".$row['price'];
			$tempRow['discounted_price'] = $currency." ".$row['discounted_price'];
			$tempRow['serve_for'] = $row['serve_for'];
			$tempRow['stock'] = $row['stock'];
			$tempRow['is_active'] = ($row['is_active'] == 1)
			? '<span style="color: green;">Active</span>'
			: '<span style="color: red;">Inactive</span>';
			$tempRow['image'] = "<a data-lightbox='product' href='".$row['image']."' data-caption='".$row['name']."'><img src='".$row['image']."' title='".$row['name']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	// data of 'USERS' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'users'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `mobile` like '%".$search."%' OR `city` like '%".$search."%' OR `area` like '%".$search."%' OR `street` like '%".$search."%' OR `status` like '%".$search."%' OR `created_at` like '%".$search."%'";
		}
		if(isset($_GET['filter_order_status']) && $_GET['filter_order_status'] !=''){
			$filter_order = $_GET['filter_order'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' and active_status='.$filter_order;
			else
				$where =' where active_status='.$filter_order;
		}
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u ".$where." ORDER BY `".$sort."` ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    $operate = ' <a href="edit-customer.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['email'] = $row['email'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['balance'] = $row['balance'];
			$tempRow['referral_code'] = $row['referral_code'];
			$tempRow['friends_code'] = !empty($row['friends_code'])?$row['friends_code']:'-';
			$tempRow['city_id'] = $row['city'];
			$tempRow['city'] = $row['city_name'];
			$tempRow['area_id'] = $row['area'];
			$tempRow['area'] = $row['area_name'];
			$tempRow['street'] = $row['street'];
			$tempRow['apikey'] = $row['apikey'];
			$tempRow['status'] = $row['status'];
			$tempRow['created_at'] = $row['created_at'];
			$tempRow['operate'] = $operate;
			// $tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}

	if(isset($_GET['table']) && $_GET['table'] == 'sellers'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `mobile` like '%".$search."%' OR `company_name` like '%".$search."%'";
		}
		if(isset($_GET['filter_order_status']) && $_GET['filter_order_status'] !=''){
			$filter_order = $_GET['filter_order'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' and active_status='.$filter_order;
			else
				$where =' where active_status='.$filter_order;
		}
		
		$sql = "SELECT COUNT(*) as total FROM `seller` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `seller` u ".$where." ORDER BY `".$sort."` ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    $operate = ' <a href="edit-seller.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			if($row['status']==0)
			$operate .= ' <a href="javascript:void(0);" onClick="alert(\'Please Active Before Login!\')">&nbsp;&nbsp;&nbsp;<i class="fa fa-sign-in"></i>Login</a>';
			else
			$operate .= ' <a href="login_as_seller.php?id='.$row['id'].'">&nbsp;&nbsp;&nbsp;<i class="fa fa-sign-in"></i>Login</a>';
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['email'] = $row['email'];
			$tempRow['company_name'] = $row['company_name'];
			if($row['status']==0)
			    $tempRow['status']="<label class='label label-danger'>Inactive</label>";
            else
                $tempRow['status']="<label class='label label-success'>Active</label>";
			$tempRow['date_created'] = $row['date_created'];
			$tempRow['operate'] = $operate;
			// $tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	if(isset($_GET['table']) && $_GET['table'] == 'referral_users'){
		$sort = 'id'; $order = 'DESC';
		$offset = 0; $limit = 10;
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
	
		
		if(!empty($_GET['search'])){
			$search = $_GET['search'];
			$where .= " Where `id` like '%".$search."%' OR `name` like '%".$search."%' ";
		}
		
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT
        u.id,
        u.name,
        u.mobile,
        u.referral_code,
        COUNT(r.referral_code) AS referred_count
    FROM
        users u
    LEFT JOIN
        users r ON u.referral_code = r.friends_code";
    if(!empty($search)){
        $sql.=" WHERE `u.id` like '%".$search."%' OR `u.name` like '%".$search."%' ";
    }
    
    $sql.=" GROUP BY
        u.id, u.name
    ORDER BY
        u.id DESC LIMIT ".$offset.", ".$limit;
	
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    $referral_code = $row['referral_code'];
			$tempRow['id'] = $row['id'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['name'] = $row['name'];
			$tempRow['referred_count'] = !empty($row['referred_count'])?"<a href='referred_users.php?code=$referral_code'>".$row['referred_count']."</a>":0;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	
	if(isset($_GET['table']) && $_GET['table'] == 'referred_users'){
	    $code=$_GET['code'];
		//echo $code;die;
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = "WHERE `friends_code`='".$code."'";
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(!empty($_GET['search'])){
			$search = $_GET['search'];
			$where .= "  and (`id` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `mobile` like '%".$search."%' OR `friends_code` like '%".$search."%' OR `city` like '%".$search."%' OR `area` like '%".$search."%' OR `street` like '%".$search."%' OR `status` like '%".$search."%' OR `created_at` like '%".$search."%')";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		//echo $sql;die();
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u ".$where." ORDER BY `".$sort."` ".$order." LIMIT ".$offset.", ".$limit;
		//echo $sql;die;
		
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    //var_dump($row);die();
		    $friends_code=!empty($row['friends_code'])?$row['friends_code']:'';
		    $sql = "SELECT name as fname FROM `users` where friends_code=".$friends_code;
    		$db->sql($sql);
    		$ress = $db->getResult();
		    $referred_by = $ress[0]['fname'];
		    
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['email'] = $row['email'];
			$tempRow['mobile'] = $row['mobile'];
			//$tempRow['balance'] = $row['balance'];
			$tempRow['referral_code'] = $row['referral_code'];
			$tempRow['friends_code'] = $friends_code;
			//$tempRow['referred_by'] = !empty($referred_by)?$referred_by:'';
			$tempRow['city_id'] = $row['city'];
			$tempRow['city'] = $row['city_name'];
			$tempRow['area_id'] = $row['area'];
			$tempRow['area'] = $row['area_name'];
			$tempRow['street'] = $row['street'];
			$tempRow['apikey'] = $row['apikey'];
			$tempRow['status'] = !empty($row['status'])?'Active':'';
			$tempRow['created_at'] = $row['created_at'];
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	// data of 'notification' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'notifications'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `message` like '%".$search."%' OR `image` like '%".$search."%' OR `date_sent` like '%".$search."%' ";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `notifications` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `notifications` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			

			$operate = " <a class='btn btn-xs btn-danger delete-notification' data-id='".$row['id']."' data-image='".$row['image']."' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['title'];
			$tempRow['subtitle'] = $row['message'];
			$tempRow['type'] = $row['type'];
			$tempRow['type_id'] = $row['type_id'];
			$tempRow['image'] = (!empty($row['image']))?"<a data-lightbox='slider' href='".$row['image']."' data-caption='".$row['title']."'><img src='".$row['image']."' title='".$row['title']."' width='50' /></a>" : "No Image";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	if(isset($_GET['table']) && $_GET['table'] == 'stores'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `sname` like '%".$search."%' OR `address` like '%".$search."%' OR `cmobile` like '%".$search."%' OR `cemail` like '%".$search."%' ";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `stores` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `stores` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			

			$operate = " <a class='btn btn-xs btn-success edit-store' data-id='".$row['id']."' onclick='edit_store(".$row['id'].")' title='Edit'><i class='fa fa-pen-o'></i>Edit</a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['sname'];
			$tempRow['area'] = $row['area'];
			$tempRow['pincode'] = $row['pincode'];
			$tempRow['mobile'] = $row['cmobile'];
			$tempRow['email'] = $row['cemail'];
			$tempRow['uname'] = $row['username'];
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	if(isset($_GET['table']) && $_GET['table'] == 'slider'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `image` like '%".$search."%' OR `date_added` like '%".$search."%' ";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `slider` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `slider` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			$operate = " <a class='btn btn-xs btn-danger delete-slider' data-id='".$row['id']."' data-image='".$row['image']."' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";
			
			
			$tempRow['id'] = $row['id'];
			$tempRow['type'] = $row['type'];
			$tempRow['type_id'] = $row['type_id'];
			$tempRow['slider'] = $row['slider'];
			$tempRow['image'] = (!empty($row['image']))?"<a data-lightbox='slider' href='".$row['image']."'><img src='".$row['image']."' width='40'/></a>" : "No Image";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
		if(isset($_GET['table']) && $_GET['table'] == 'offers'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `date_added` like '%".$search."%' ";
		}
		
		$sql = "SELECT COUNT(id) as total FROM `offers` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `offers` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		foreach($res as $row){
		    $operate = " <a class='btn btn-xs btn-danger delete-offer' data-id='".$row['id']."' data-image='".$row['image']."' title='Delete'><i class='fa fa-trash-o'></i>Delete</a>";
		    
			$tempRow['id'] = $row['id'];
			$tempRow['image'] = (!empty($row['image']))?"<a data-lightbox='offer' href='".$row['image']."'><img src='".$row['image']."' width='40'/></a>" : "No Image";
			$tempRow['date_created'] = date('d-m-Y h:i:sa',strtotime($row['date_added']));
			$tempRow['operate'] = $operate;
			
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	if(isset($_GET['table']) && $_GET['table'] == 'sections'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `date_added` like '%".$search."%' ";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `sections` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `sections` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			$operate = "<a class='btn btn-xs btn-primary edit-section' data-id='".$row['id']."' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

			$operate .= " <a class='btn btn-xs btn-danger delete-section' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash-o'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['title'] = $row['title'];
			$tempRow['short_description'] = $row['short_description'];
			$tempRow['style'] = $row['style'];
			$tempRow['product_ids'] = $row['product_ids'];
			$tempRow['place'] = $row['place'];
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	
	if(isset($_GET['table']) && $_GET['table'] == 'seller_request'){
// 		print_r($_GET);
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = ' where status='.$_GET['status'];
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where .= "  and (`id` like '%".$search."%' OR `name` like '%".$search."%')";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `seller` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `seller` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
// 		echo $sql;
// 		print_r($res);
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			$operate = ' <a href="edit-request.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['email'] = $row['email'];
			$tempRow['company'] = $row['company_name'];
			$tempRow['address'] = $row['company_address'];
			$tempRow['gst_no'] = $row['gst_no'];
			$tempRow['pan_no'] = $row['pan_no'];
			if($row['status']==0){
			    $tempRow['status'] = "<span class='label label-warning'>Pending</span>";
			}elseif($row['status']==1){
			    $tempRow['status'] =  "<span class='label label-success'>Accepted</span>";
			}else{
			    $tempRow['status'] =  "<span class='label label-danger'>Denied</span>";
			}
			$tempRow['date_created'] = $row['date_created'];
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
		// data of 'Delivery Boy' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'delivery-boys'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `mobile` like '%".$search."%' OR `address` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `delivery_boys` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `delivery_boys` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		//echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			$sql = "SELECT sname FROM `stores` WHERE id=".$row['store_id'];
        		//echo $sql;
        		$db->sql($sql);
        		$res1 = $db->getResult();
			//$operate = '<a href="view-subcategory.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
			$operate = "<a class='btn btn-xs btn-primary edit-delivery-boy' data-id='".$row['id']."' data-toggle='modal' data-target='#editDeliveryBoyModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

			$operate .= " <a class='btn btn-xs btn-danger delete-delivery-boy' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash-o'></i></a>";

			$operate .= " <a class='btn btn-xs btn-primary transfer-fund' data-id='".$row['id']."' data-name='".$row['name']."' data-mobile='".$row['mobile']."' data-address='".$row['address']."' data-balance='".$row['balance']."' data-toggle='modal' data-target='#fundTransferModal' title='Fund Transfer'><i class='fa fa-chevron-circle-right'></i></a>";
			// $operate .= "<a class='btn btn-xs btn-danger delete-district' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['store_id'] = $row['store_id'];
			$tempRow['store'] = (!empty($res1))?$res1[0]['sname']:'';
			$tempRow['address'] = $row['address'];
			$tempRow['bonus'] = $row['bonus'];
			$tempRow['balance'] = $row['balance'];
			if($row['status']==0)
			    $tempRow['status']="<label class='label label-danger'>Deactive</label>";
            else
                $tempRow['status']="<label class='label label-success'>Active</label>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
		// data of 'Payment Request' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'payment-requests'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where p.`id` like '%".$search."%' OR `user_id` like '%".$search."%' OR `payment_type` like '%".$search."%' OR `amount_requested` like '%".$search."%' OR `remarks` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `date_created` like '%".$search."%' OR `payment_address` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `payment_requests` p JOIN users u ON p.user_id=u.id".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT p.*,u.name,u.email FROM payment_requests p JOIN users u ON u.id=p.user_id".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		//echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){

			
			//$operate = '<a href="view-subcategory.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
			$operate = "<a class='btn btn-xs btn-primary edit-payment-request' data-id='".$row['id']."' data-toggle='modal' data-target='#editPaymentRequestModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
			// $operate .= "<a class='btn btn-xs btn-danger delete-district' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['user_id'] = $row['user_id'];
			$tempRow['payment_type'] = $row['payment_type'];
			if($row['payment_type']=='bank'){
				$payment_address = json_decode($row['payment_address'],true);
				$tempRow['payment_address'] = '<b>A/C Holder</b><br>'.$payment_address[0][1].'<br>'.'<b>A/C Number</b><br>'.$payment_address[1][1].'<br>'.'<b>IFSC Code</b><br>'.$payment_address[2][1].'<br>'.'<b>Bank Name</b><br>'.$payment_address[3][1];
			}else{
				$tempRow['payment_address'] = $row['payment_address'];
			}
			$tempRow['amount_requested'] = $row['amount_requested'];
			$tempRow['remarks'] = $row['remarks'];
			$tempRow['name'] = $row['name'];
			$tempRow['email'] = $row['email'];
			if($row['status']==0)
			    $tempRow['status']="<label class='label label-warning'>Pending</label>";
			if($row['status']==1)
				$tempRow['status']="<label class='label label-primary'>Success</label>";
            if($row['status']==2)
                $tempRow['status']="<label class='label label-danger'>Cancelled</label>";
			$tempRow['operate'] = $operate;
			$tempRow['date_created'] = $row['date_created'];
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	// data of 'Fund Transfer' table goes here
	
	if(isset($_GET['table']) && $_GET['table'] == 'fund-transfers'){

        $offset = 0;
        $limit = 10;
    
        $sort = 'id';
        $order = 'DESC';
    
        $where = " WHERE 1 ";
    
        // ================= PAGINATION =================
    
        if(isset($_GET['offset'])){
            $offset = $_GET['offset'];
        }
    
        if(isset($_GET['limit'])){
            $limit = $_GET['limit'];
        }
    
        // ================= SORT =================
    
        if(isset($_GET['sort'])){
            $sort = $_GET['sort'];
        }
    
        if(isset($_GET['order'])){
            $order = $_GET['order'];
        }
    
        // ================= SEARCH =================
    
        if(isset($_GET['search']) && $_GET['search'] != ''){
    
            $search = $_GET['search'];
    
            $where .= "
            AND (
                f.id LIKE '%".$search."%'
                OR d.name LIKE '%".$search."%'
                OR d.mobile LIKE '%".$search."%'
                OR d.address LIKE '%".$search."%'
            )";
        }
    
        // ================= DELIVERY BOY FILTER =================
    
        if(isset($_GET['delivery_boy_id']) 
            && $_GET['delivery_boy_id'] != ''){
    
            $delivery_boy_id = $_GET['delivery_boy_id'];
    
            $where .= "
            AND f.delivery_boy_id = '".$delivery_boy_id."'";
        }
    
        // ================= TOTAL =================
    
        $sql = "
        SELECT COUNT(*) as total
    
        FROM fund_transfers f
    
        JOIN delivery_boys d
            ON f.delivery_boy_id = d.id
    
        $where
        ";
    
        $db->sql($sql);
    
        $res = $db->getResult();
    
        $total = 0;
    
        foreach($res as $row){
    
            $total = $row['total'];
        }
    
        // ================= DATA QUERY =================
    
        $sql = "
        SELECT 
            f.*,
            d.name,
            d.mobile,
            d.address
    
        FROM fund_transfers f
    
        JOIN delivery_boys d
            ON f.delivery_boy_id = d.id
    
        $where
    
        ORDER BY ".$sort." ".$order."
    
        LIMIT ".$offset.", ".$limit;
    
        // echo $sql;
    
        $db->sql($sql);
    
        $res = $db->getResult();
    
        // ================= RESPONSE =================
    
        $bulkData = array();
    
        $bulkData['total'] = $total;
    
        $rows = array();
    
        foreach($res as $row){
    
            $tempRow = array();
    
            $tempRow['id'] = $row['id'];
    
            $tempRow['name'] = $row['name'];
    
            $tempRow['mobile'] = $row['mobile'];
    
            $tempRow['address'] = $row['address'];
    
            $tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
    
            $tempRow['opening_balance'] = $row['opening_balance'];
    
            $tempRow['closing_balance'] = $row['closing_balance'];
    
            $tempRow['status'] = $row['status'];
    
            $tempRow['message'] = $row['message'];
    
            $tempRow['date_created'] = $row['date_created'];
    
            $rows[] = $tempRow;
        }
    
        $bulkData['rows'] = $rows;
    
        echo json_encode($bulkData);
    }
// 	if(isset($_GET['table']) && $_GET['table'] == 'fund-transfers'){
		
// 		$offset = 0; $limit = 10;
// 		$sort = 'id'; $order = 'DESC';
// 		$where = '';
// 		if(isset($_GET['offset']))
// 			$offset = $_GET['offset'];
// 		if(isset($_GET['limit']))
// 			$limit = $_GET['limit'];
		
// 		if(isset($_GET['sort']))
// 			$sort = $_GET['sort'];
// 		if(isset($_GET['order']))
// 			$order = $_GET['order'];
		
// 		if(isset($_GET['search']) && $_GET['search'] !=''){
// 			$search = $_GET['search'];
// 			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `mobile` like '%".$search."%' OR `address` like '%".$search."%'";
// 		}
		
// 		$sql = "SELECT COUNT(*) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id".$where;
// 		$db->sql($sql);
// 		$res = $db->getResult();
// 		foreach($res as $row)
// 			$total = $row['total'];
		
// 		$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
// 		//echo $sql;
// 		$db->sql($sql);
// 		$res = $db->getResult();
		
// 		$bulkData = array();
// 		$bulkData['total'] = $total;
// 		$rows = array();
// 		$tempRow = array();
		
// 		foreach($res as $row){
			
			
// 			$tempRow['id'] = $row['id'];
// 			$tempRow['name'] = $row['name'];
// 			$tempRow['mobile'] = $row['mobile'];
// 			$tempRow['address'] = $row['address'];
// 			$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
// 			$tempRow['opening_balance'] = $row['opening_balance'];
// 			$tempRow['closing_balance'] = $row['closing_balance'];
// 			$tempRow['status'] = $row['status'];
// 			$tempRow['message'] = $row['message'];
// 			$tempRow['date_created'] = $row['date_created'];
	

// 			$rows[] = $tempRow;
// 		}
// 		$bulkData['rows'] = $rows;
// 		print_r(json_encode($bulkData));
// 	}
    // data of 'Promo Codes' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'promo-codes'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `promo_code` like '%".$search."%' OR `message` like '%".$search."%' OR `start_date` like '%".$search."%' OR `end_date` like '%".$search."%'";
		}
		
		
		$sql = "SELECT COUNT(id) as total FROM `promo_codes`".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `promo_codes`".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		//echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    
		    $operate = "<a class='btn btn-xs btn-primary edit-promo-code' data-id='".$row['id']."' data-toggle='modal' data-target='#editPromoCodeModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
			$operate .= " <a class='btn btn-xs btn-danger delete-promo-code' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash-o'></i></a>";
			
			$seller_ids = array_map('trim', explode(',', $row['seller_ids']));
			$is_checked = in_array($seller_id, $seller_ids) ? 'checked' : '';

			$active = "<input type='checkbox' data-id='".$row['id']."' data-sellerids='".$row['seller_ids']."' id='cod_payment_method_btn_".$row['id']."' class='custom-switch update-seller' ".$is_checked.">";

			
			$tempRow['id'] = $row['id'];
			$tempRow['promo_code'] = $row['promo_code'];
			$tempRow['message'] = $row['message'];
			$tempRow['start_date'] = $row['start_date'];
			$tempRow['end_date'] = $row['end_date'];
			$tempRow['no_of_users'] = $row['no_of_users'];
			$tempRow['minimum_order_amount'] = $row['minimum_order_amount'];
			$tempRow['discount'] = $row['discount'];
			$tempRow['discount_type'] = $row['discount_type'];
			$tempRow['max_discount_amount'] = $row['max_discount_amount'];
			$tempRow['repeat_usage'] = $row['repeat_usage']==1?'Allowed':'Not Allowed';
			$tempRow['no_of_repeat_usage'] = $row['no_of_repeat_usage'];
		    if($row['status']==0)
			    $tempRow['status']="<label class='label label-danger'>Deactive</label>";
            else
                $tempRow['status']="<label class='label label-success'>Active</label>";
			$tempRow['date_created'] = date('d-m-Y h:i:sa',strtotime($row['date_created']));
			$tempRow['image'] = "<a data-lightbox='product' href='".$row['image']."' ><img src='".$row['image']."' style='height:50px !important' /></a>";
			$tempRow['operate'] = $operate;
			$tempRow['active'] = $active;
	
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	if(isset($_GET['table']) && $_GET['table'] == 'time-slots'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `title` like '%".$search."%' OR `from_time` like '%".$search."%' OR `to_time` like '%".$search."%' OR `last_order_time` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `time_slots` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `time_slots` ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		//echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			
			//$operate = '<a href="view-subcategory.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
			$operate = "<a class='btn btn-xs btn-primary edit-time-slot' data-id='".$row['id']."' data-toggle='modal' data-target='#editTimeSlotModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";

			$operate .= " <a class='btn btn-xs btn-danger delete-time-slot' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash-o'></i></a>";
			// $operate .= "<a class='btn btn-xs btn-danger delete-district' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['title'] = $row['title'];
			$tempRow['from_time'] = $row['from_time'];
			$tempRow['to_time'] = $row['to_time'];
			$tempRow['last_order_time'] = $row['last_order_time'];
			if($row['status']==0)
			    $tempRow['status']="<label class='label label-danger'>Deactive</label>";
            else
                $tempRow['status']="<label class='label label-success'>Active</label>";
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
		// data of 'Return Request' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'return-requests'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = "WHERE p.seller_id ='$seller_id' ";
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where .= " AND r.`id` like '%".$search."%' OR r.`user_id` like '%".$search."%' OR r.`order_id` like '%".$search."%' OR p.`name` like '%".$search."%' OR u.`name` like '%".$search."%' OR r.`status` like '%".$search."%' OR r.`date_created` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `return_requests` r LEFT JOIN users u ON r.user_id=u.id LEFT JOIN products p ON p.id = r.product_id".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT r.*,u.name,oi.product_variant_id,oi.quantity,p.id as product_id,p.name as product_name,pv.price,pv.discounted_price FROM return_requests r LEFT JOIN users u ON u.id=r.user_id LEFT JOIN order_items oi ON oi.id=r.order_item_id LEFT JOIN products p ON p.id = r.product_id LEFT JOIN product_variant pv ON pv.id=r.product_variant_id".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
// 		echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		    

			//$operate = '<a href="view-subcategory.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
			$operate = "<a class='btn btn-xs btn-primary edit-return-request' data-id='".$row['id']."' data-toggle='modal' data-target='#editReturnRequestModal' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
			$operate .= " <a class='btn btn-xs btn-danger delete-return-request' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['user_id'] = $row['user_id'];
			$tempRow['order_id'] = $row['order_id'];
			$tempRow['order_item_id'] = $row['order_item_id'];
			$tempRow['product_id'] = $row['product_id'];
			$tempRow['price'] = $row['price'];
			$tempRow['discounted_price'] = $row['discounted_price'];
			
			$tempRow['name'] = $row['name'];
			$tempRow['product_name'] = $row['product_name'];
			$tempRow['product_variant_id'] = $row['product_variant_id'];
			$tempRow['quantity'] = $row['quantity'];
			$tempRow['total'] = $row['discounted_price']==0?$row['price']*$row['quantity']:$row['discounted_price']*$row['quantity'];

			if($row['status']==0)
			    $tempRow['status']="<label class='label label-warning'>Pending</label>";
			if($row['status']==1)
				$tempRow['status']="<label class='label label-primary'>Approved</label>";
            if($row['status']==2)
                $tempRow['status']="<label class='label label-danger'>Cancelled</label>";
			$tempRow['operate'] = $operate;
			// $tempRow['date_created'] = date('d-M-Y h:i A',strtotime($row['date_created']));
			$tempRow['date_created'] = $row['date_created'];
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	// data of 'Promo Codes' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'system-users'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'ASC';
		$where = '';
		$condition = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `username` like '%".$search."%' OR `email` like '%".$search."%' OR `role` like '%".$search."%' OR `date_created` like '%".$search."%'";
		}
		if($_SESSION['role']!='super admin'){
			if(empty($where)){
				$condition .= ' where created_by='.$_SESSION['id'].'AND role="editor"';

			}else{
				$condition .= ' and created_by='.$_SESSION['id'].'AND role="editor"';
			}
		}else{
		    if(empty($where)){
				$condition .= ' where role="editor"';

			}else{
				$condition .= ' AND role="editor"';
			}
		}
		
		$sql = "SELECT COUNT(id) as total FROM `admin`".$where."".$condition;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT * FROM `admin`".$where."".$condition." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		$i=1;
		foreach($res as $row){
			if($row['created_by']!=0){
				$sql = "SELECT username FROM admin WHERE id=".$row['created_by'];
				$db->sql($sql);
				$created_by=$db->getResult();
			}
			
			// print_r($city_names);
			
			
			if($row['role'] != 'super admin'){
			//	$operate = "<a class='btn btn-xs btn-primary edit-system-user' data-id='".$row['id']."' data-name='".$row['username']."' data-email='".$row['email']."' data-mobile='".$row['mobile']."' title='Edit'><i class='fa fa-pencil-square-o'></i></a>";
				$operate = " <a class='btn btn-xs btn-danger delete-system-user' data-id='".$row['id']."' title='Delete'><i class='fa fa-trash-o'></i></a>";
			}else{
				$operate='';
			}
			if($row['role']=='super admin'){
				$role = '<span class="label label-success">Super Admin</span>';
			}
			if($row['role']=='admin'){
				$role = '<span class="label label-primary">Admin</span>';
			}
			if($row['role']=='editor'){
				$role = '<span class="label label-warning">Editor</span>';
			}
			$tempRow['id'] = $i;
			$tempRow['username'] = $row['username'];
			$tempRow['email'] = $row['email'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['permissions'] = $row['permissions'];
			$tempRow['role'] = $role;
			$tempRow['created_by_id'] = $row['created_by']!=0?$row['created_by']:'-';
			$tempRow['created_by'] = $row['created_by']!=0?$created_by[0]['username']:'-';
			$tempRow['date_created'] = date('d-m-Y h:i:sa',strtotime($row['date_created']));
			$tempRow['operate'] = $operate;
	
			$rows[] = $tempRow;
			$i++;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	// data of 'Wallet Transactions' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'wallet-transactions'){
		
		$offset = 0; $limit = 10;
		$sort = 'w.id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) && $_GET['search'] !=''){
			$search = $_GET['search'];
			$where = " Where w.`id` like '%".$search."%' OR `user_id` like '%".$search."%' OR `message` like '%".$search."%' OR `name` like '%".$search."%' OR `date_created` like '%".$search."%'";
		}
		
		$sql = "SELECT COUNT(*) as total FROM `wallet_transactions` w JOIN `users` u ON u.id=w.user_id ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT w.*,u.name FROM `wallet_transactions` w JOIN `users` u ON u.id=w.user_id ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
		// echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
			
			//$operate = '<a href="view-subcategory.php?id='.$row['id'].'"><i class="fa fa-folder-open-o"></i>View Subcategories</a>';
			// $operate .= "<a class='btn btn-xs btn-danger delete-district' data-id='".$row['id']."' title='Delete'><i class='fas fa-trash'></i></a>";
			
			$tempRow['id'] = $row['id'];
			$tempRow['user_id'] = $row['user_id'];
			$tempRow['name'] = $row['name'];
			$tempRow['type'] = $row['type'];
			$tempRow['amount'] = $row['amount'];
			$tempRow['message'] = $row['message'];
			$tempRow['date_created'] = $row['date_created'];
			$tempRow['las_updated'] = $row['last_updated'];
			if($row['status']==0)
			    $tempRow['status']="<label class='label label-danger'>Deactive</label>";
            else
                $tempRow['status']="<label class='label label-success'>Active</label>";
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
	
	
	if (isset($_GET['table']) && $_GET['table'] == 'delivery_boy_report') {
 
        $rows = array();
     
        $offset = 0;
        $limit  = 1000;
     
        if (isset($_GET['offset'])) {
            $offset = (int) $_GET['offset'];
        }
     
        if (isset($_GET['limit'])) {
            $limit = (int) $_GET['limit'];
        }
     
        $sort_map = array(
            'id'                  => 'o.id',
            'delivery_boy_name'   => 'db.name',
            'delivery_boy_mobile' => 'db.mobile',
            'delivery_charge'     => 'o.delivery_charge',
            'final_total'         => 'o.final_total',
            'payment_method'      => 'o.payment_method',
            'active_status'       => 'o.active_status',
        );
        $sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $sort     = isset($sort_map[$sort_key]) ? $sort_map[$sort_key] : 'o.id';
     
        $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }
     
        $keyword = !empty($_GET['search']) ? trim($_GET['search']) : "";

        // ================= Payment method filter =================
        // Whitelist approach so we never concatenate a raw $_GET value
        // straight into SQL. Values match exactly what's stored/displayed
        // for orders.payment_method.
        $valid_payment_methods = array('Cash on Delivery', 'Online Payment');
        $payment_method_filter = (!empty($_GET['payment_method']) && in_array($_GET['payment_method'], $valid_payment_methods, true))
            ? $_GET['payment_method']
            : "";

        // ================= Delivery boy filter =================
        $delivery_boy_filter_id = !empty($_GET['delivery_boy_id']) ? (int) $_GET['delivery_boy_id'] : 0;

        // ================= Build extra filters once, appended to whichever WHERE branch runs =================
        $extra_filters = "";
        if (!empty($payment_method_filter)) {
            $extra_filters .= " AND o.payment_method = '" . $payment_method_filter . "' ";
        }
        if ($delivery_boy_filter_id > 0) {
            $extra_filters .= " AND o.delivery_boy_id = '" . $delivery_boy_filter_id . "' ";
        }
     
        $select_block = "
            SELECT
                o.id,
                db.name             AS delivery_boy_name,
                db.mobile           AS delivery_boy_mobile,
                o.date_added,
                o.final_total,
                o.delivery_charge,
                o.payment_method,
                o.active_status,
     
                -- product name + quantity + unit combined into ONE string
                -- per item BEFORE aggregating, so they can never drift apart.
                GROUP_CONCAT(
                    CONCAT(
                        COALESCE(p.name, '-'),
                        ' - ',
                        COALESCE(oi.quantity, 0),
                        ' ',
                        COALESCE(un.name, '')
                    )
                    ORDER BY oi.id SEPARATOR '||'
                ) AS product_details,
     
                -- No DISTINCT here, and same ORDER BY oi.id as above, so this
                -- list stays the same length/order as product_details.
                GROUP_CONCAT(
                    COALESCE(pv.discounted_price, 0)
                    ORDER BY oi.id SEPARATOR '||'
                ) AS discounted_price_list
     
            FROM orders o
     
            LEFT JOIN order_items oi
                ON oi.order_id = o.id
     
            LEFT JOIN product_variant pv
                ON pv.id = oi.product_variant_id
     
            LEFT JOIN products p
                ON p.id = pv.product_id
     
            LEFT JOIN unit un
                ON un.id = pv.measurement_unit_id
     
            LEFT JOIN delivery_boys db
                ON db.id = o.delivery_boy_id
        ";
     
     
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
     
            $month   = $_GET['start_date'];
            $daysago = $_GET['end_date'];
     
            $where = "
                WHERE DATE(o.date_added) >= '" . $month . "'
                AND DATE(o.date_added) <= '" . $daysago . "'
            ";
     
            if (!empty($keyword)) {
                $where .= "
                    AND (
                        db.name LIKE '%" . $keyword . "%'
                        OR db.mobile LIKE '%" . $keyword . "%'
                        OR p.name LIKE '%" . $keyword . "%'
                    )
                ";
            }

            $where .= $extra_filters;
     
        } else {
     
     
            $where = "
                WHERE o.date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ";
     
            if (!empty($keyword)) {
                $where .= "
                    AND (
                        db.name LIKE '%" . $keyword . "%'
                        OR db.mobile LIKE '%" . $keyword . "%'
                        OR p.name LIKE '%" . $keyword . "%'
                    )
                ";
            }

            $where .= $extra_filters;
        }
     
        $sql_query = $select_block . $where . "
            GROUP BY o.id
            ORDER BY " . $sort . " " . $order . "
            LIMIT " . $offset . ", " . $limit;
     
        $db->sql($sql_query);
     
        $res = $db->getResult();
     
        if (!is_array($res)) {
            $res = array();
        }
     
        $status_labels = array(
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'completed'  => 'Completed',
            'delivered'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'canceled'   => 'Cancelled',
        );
     
        foreach ($res as $row) {
     
            if (!is_array($row)) {
                continue;
            }
     
            $tempRow = array();
     
            $tempRow['id']                  = $row['id'];
            $tempRow['delivery_boy_name']   = !empty($row['delivery_boy_name']) ? $row['delivery_boy_name'] : '-';
            $tempRow['delivery_boy_mobile'] = !empty($row['delivery_boy_mobile']) ? $row['delivery_boy_mobile'] : '-';
     
            $tempRow['product_details'] = !empty($row['product_details'])
                ? explode('||', $row['product_details'])
                : array('-');
     
            $tempRow['discounted_price'] = !empty($row['discounted_price_list'])
                ? explode('||', $row['discounted_price_list'])
                : array('0');
     
            $tempRow['delivery_charge'] = !empty($row['delivery_charge']) ? $row['delivery_charge'] : '0';
            $tempRow['final_total']     = $row['final_total'];
            $tempRow['platform_fee']    = 2; // Static platform fee value

            // NOTE: payment_method used as-is (assumed already stored as
            // "Cash on Delivery" / "Online Payment"), same as sales report.
            $tempRow['payment_method']  = $row['payment_method'];
     
            $status_key = strtolower(trim((string) $row['active_status']));
            $tempRow['active_status'] = isset($status_labels[$status_key])
                ? $status_labels[$status_key]
                : ucfirst($status_key);
     
            $rows[] = $tempRow;
        }
     
        $bulkData = array();
     
        $bulkData['total'] = count($rows);
     
        $bulkData['rows'] = $rows;
     
        echo json_encode($bulkData);
    }
	
	
	// data of 'Sales Report' table goes here
	if (isset($_GET['table']) && $_GET['table'] == 'sales_products') {
 
        $rows = array();
     
        $offset = 0;
        $limit  = 1000;
     
        if (isset($_GET['offset'])) {
            $offset = (int) $_GET['offset'];
        }
     
        if (isset($_GET['limit'])) {
            $limit = (int) $_GET['limit'];
        }
     
        // Whitelist sortable fields -> real columns (avoids raw $_GET['sort']
        // being concatenated straight into ORDER BY).
        $sort_map = array(
            'id'              => 'o.id',
            'seller_name'     => 'se.name',
            'seller_mobile'   => 'se.mobile',
            'user_name'       => 'us.name',
            'user_mobile'     => 'us.mobile',
            'address'         => 'o.address',
            'order_date'      => 'o.date_added',
            'final_total'     => 'o.final_total',
            'payment_method'  => 'o.payment_method',
            'active_status'   => 'o.active_status',
        );
        $sort_key = isset($_GET['sort']) ? $_GET['sort'] : 'id';
        $sort     = isset($sort_map[$sort_key]) ? $sort_map[$sort_key] : 'o.id';
     
        $order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';
        if (!in_array($order, array('ASC', 'DESC'))) {
            $order = 'DESC';
        }
     
        $keyword = !empty($_GET['search']) ? trim($_GET['search']) : "";

        // ================= Payment method filter =================
        // Whitelist approach (same pattern as $sort_map above) so we never
        // concatenate a raw $_GET value straight into SQL. Values match
        // exactly what's stored/displayed for orders.payment_method.
        $valid_payment_methods = array('Cash on Delivery', 'Online Payment');
        $payment_method_filter = (!empty($_GET['payment_method']) && in_array($_GET['payment_method'], $valid_payment_methods, true))
            ? $_GET['payment_method']
            : "";

        // ================= Seller filter =================
        // Only meaningful for non-seller roles; a seller session is already
        // hard-scoped to their own seller_id below.
        $seller_filter_id = !empty($_GET['seller_id']) ? (int) $_GET['seller_id'] : 0;
     
        $is_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');
        $seller_id = isset($_SESSION['seller_id']) ? $_SESSION['seller_id'] : 0;

        // ================= Build extra filters once, appended to whichever WHERE branch runs =================
        $extra_filters = "";
        if (!empty($payment_method_filter)) {
            $extra_filters .= " AND o.payment_method = '" . $payment_method_filter . "' ";
        }
        if (!$is_seller && $seller_filter_id > 0) {
            $extra_filters .= " AND o.seller_id = '" . $seller_filter_id . "' ";
        }
     
        // ================= COMMON SELECT / JOIN BLOCK =================
     
        $select_block = "
            SELECT
                o.id,
                se.name            AS seller_name,
                se.mobile          AS seller_mobile,
                us.name             AS user_name,
                us.mobile           AS user_mobile,
                o.address,
                o.date_added,
                o.final_total,
                o.payment_method,
                o.active_status,
     
                -- product name + quantity + unit combined into ONE string
                -- per item BEFORE aggregating, so they can never drift apart.
                GROUP_CONCAT(
                    CONCAT(
                        COALESCE(p.name, '-'),
                        ' (',
                        COALESCE(pv.measurement, '-'),
                        COALESCE(un.name, ''),
                        ')',
                        ' - x ',
                        COALESCE(oi.quantity, 0)
                    )
                    ORDER BY oi.id SEPARATOR '||'
                ) AS product_details,
     
                -- No DISTINCT here, and same ORDER BY oi.id as above, so this
                -- list stays the same length/order as product_details.
                GROUP_CONCAT(
                    COALESCE(pv.vendor_price, 0)
                    ORDER BY oi.id SEPARATOR '||'
                ) AS vendor_price_list,
     
                GROUP_CONCAT(
                    COALESCE(pv.discounted_price, 0)
                    ORDER BY oi.id SEPARATOR '||'
                ) AS discounted_price_list
     
            FROM orders o
     
            LEFT JOIN order_items oi
                ON oi.order_id = o.id
     
            LEFT JOIN product_variant pv
                ON pv.id = oi.product_variant_id
     
            LEFT JOIN products p
                ON p.id = pv.product_id
     
            LEFT JOIN unit un
                ON un.id = pv.measurement_unit_id
     
            LEFT JOIN seller se
                ON se.id = o.seller_id
     
            LEFT JOIN users us
                ON us.id = o.user_id
        ";
     
        // ================= DATE FILTER =================
     
        if (!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
     
            $month   = $_GET['start_date'];
            $daysago = $_GET['end_date'];
     
            $where = "
                WHERE DATE(o.date_added) >= '" . $month . "'
                AND DATE(o.date_added) <= '" . $daysago . "'
            ";
     
            if ($is_seller) {
                $where .= " AND o.seller_id = '" . $seller_id . "' ";
            }
     
            if (!empty($keyword)) {
                $where .= "
                    AND (
                        o.mobile LIKE '%" . $keyword . "%'
                        OR p.name LIKE '%" . $keyword . "%'
                        OR us.name LIKE '%" . $keyword . "%'
                        OR se.name LIKE '%" . $keyword . "%'
                    )
                ";
            }

            $where .= $extra_filters;
     
        } else {
     
            // ================= DEFAULT LAST 1 MONTH =================
     
            $where = "
                WHERE o.date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ";
     
            if ($is_seller) {
                $where .= " AND o.seller_id = '" . $seller_id . "' ";
            }
     
            if (!empty($keyword)) {
                $where .= "
                    AND (
                        o.mobile LIKE '%" . $keyword . "%'
                        OR p.name LIKE '%" . $keyword . "%'
                        OR us.name LIKE '%" . $keyword . "%'
                        OR se.name LIKE '%" . $keyword . "%'
                    )
                ";
            }

            $where .= $extra_filters;
        }
     
        $sql_query = $select_block . $where . "
            GROUP BY o.id
            ORDER BY " . $sort . " " . $order . "
            LIMIT " . $offset . ", " . $limit;
     
        $db->sql($sql_query);
     
        $res = $db->getResult();
     
        if (!is_array($res)) {
            $res = array();
        }
     
        // Maps o.active_status -> a friendly label. Adjust the left-hand
        // values if your active_status column uses different codes.
        $status_labels = array(
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'completed'  => 'Completed',
            'delivered'  => 'Completed',
            'cancelled'  => 'Cancelled',
            'canceled'   => 'Cancelled',
        );
     
        foreach ($res as $row) {
     
            if (!is_array($row)) {
                continue;
            }
     
            $tempRow = array();
     
            $tempRow['id']           = $row['id'];
            $tempRow['user_name']    = !empty($row['user_name']) ? $row['user_name'] : '-';
            $tempRow['user_mobile']  = !empty($row['user_mobile']) ? $row['user_mobile'] : '-';
            $tempRow['address']      = $row['address'];
     
            // Split the combined strings back into arrays - one bullet per
            // item. Because they were built with the same ORDER BY and no
            // DISTINCT, index N in each array always refers to the same item.
            $tempRow['product_details'] = !empty($row['product_details'])
                ? explode('||', $row['product_details'])
                : array('-');
     
            $tempRow['order_date']     = $row['date_added'];
            $tempRow['final_total']    = $row['final_total'];
            $tempRow['platform_fee']   = 2; // Static platform fee value
     
            // NOTE: payment_method is used as-is (assumed to already be
            // stored/displayed as "Cash on Delivery" / "Online Payment").
            // ucfirst() was removed since it would not correctly reformat
            // a differently-cased or coded raw value (e.g. "cod").
            $tempRow['payment_method'] = $row['payment_method'];
     
            $status_key = strtolower(trim((string) $row['active_status']));
            $tempRow['active_status'] = isset($status_labels[$status_key])
                ? $status_labels[$status_key]
                : ucfirst($status_key);
     
            // Seller/Vendor-price/Discount fields only for non-seller roles
            if (!$is_seller) {
                $tempRow['seller_name']   = !empty($row['seller_name']) ? $row['seller_name'] : '-';
                $tempRow['seller_mobile'] = !empty($row['seller_mobile']) ? $row['seller_mobile'] : '-';
     
                $tempRow['vendor_price'] = !empty($row['vendor_price_list'])
                    ? explode('||', $row['vendor_price_list'])
                    : array('0');
     
                $tempRow['discounted_price'] = !empty($row['discounted_price_list'])
                    ? explode('||', $row['discounted_price_list'])
                    : array('0');
            }
     
            $rows[] = $tempRow;
        }
     
        $bulkData = array();
     
        $bulkData['total'] = count($rows);
     
        $bulkData['rows'] = $rows;
     
        echo json_encode($bulkData);
    }
    
// 	if(isset($_GET['table']) && $_GET['table'] == 'sales_products'){

//         $rows = array();
    
//         $offset = 0;
//         $limit = 1000;
    
//         $sort = 'id';
//         $order = 'DESC';
    
//         if(isset($_GET['offset'])){
//             $offset = $_GET['offset'];
//         }
    
//         if(isset($_GET['limit'])){
//             $limit = $_GET['limit'];
//         }
    
//         if(isset($_GET['sort'])){
    
//             if($_GET['sort'] == 'id'){
//                 $sort = "o.id";
//             }else{
//                 $sort = $_GET['sort'];
//             }
//         }
    
//         if (!empty($_GET['search'])) {
//             $keyword = trim($_GET['search']);
//         } else {
//             $keyword = "";
//         }
    
//         // ================= DATE FILTER =================
    
//         if(!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    
//             $month = $_GET['start_date'];
//             $daysago = $_GET['end_date'];
    
//             if(empty($keyword)) {
    
//                 $sql_query = "
//                 SELECT 
//                     o.id,
//                     o.mobile,
//                     o.address,
//                     o.date_added,
//                     o.final_total,
//                     o.payment_method,
    
//                     GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS product_name,
    
//                     SUM(oi.quantity) AS qty
    
//                 FROM orders o
    
//                 LEFT JOIN order_items oi 
//                     ON oi.order_id = o.id
    
//                 LEFT JOIN product_variant pv 
//                     ON pv.id = oi.product_variant_id
    
//                 LEFT JOIN products p 
//                     ON p.id = pv.product_id
    
//                 WHERE DATE(o.date_added) <= '".$daysago."'
//                 AND DATE(o.date_added) >= '".$month."'
//                 AND o.seller_id = '".$seller_id."'
    
//                 GROUP BY o.id
    
//                 ORDER BY ".$sort." DESC
    
//                 LIMIT ".$offset.", ".$limit;
    
//             } else {
    
//                 $sql_query = "
//                 SELECT 
//                     o.id,
//                     o.mobile,
//                     o.address,
//                     o.date_added,
//                     o.final_total,
//                     o.payment_method,
    
//                     GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS product_name,
    
//                     SUM(oi.quantity) AS qty
    
//                 FROM orders o
    
//                 LEFT JOIN order_items oi 
//                     ON oi.order_id = o.id
    
//                 LEFT JOIN product_variant pv 
//                     ON pv.id = oi.product_variant_id
    
//                 LEFT JOIN products p 
//                     ON p.id = pv.product_id
    
//                 WHERE DATE(o.date_added) <= '".$daysago."'
//                 AND DATE(o.date_added) >= '".$month."'
    
//                 AND (
//                     o.mobile LIKE '%".$keyword."%'
//                     OR p.name LIKE '%".$keyword."%'
//                 )
    
//                 AND o.seller_id = '".$seller_id."'
    
//                 GROUP BY o.id
    
//                 ORDER BY ".$sort." DESC
    
//                 LIMIT ".$offset.", ".$limit;
//             }
    
//         } else {
    
//             // ================= DEFAULT LAST 1 MONTH =================
    
//             if(empty($keyword)) {
    
//                 $sql_query = "
//                 SELECT 
//                     o.id,
//                     o.mobile,
//                     o.address,
//                     o.date_added,
//                     o.final_total,
//                     o.payment_method,
    
//                     GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS product_name,
    
//                     SUM(oi.quantity) AS qty
    
//                 FROM orders o
    
//                 LEFT JOIN order_items oi 
//                     ON oi.order_id = o.id
    
//                 LEFT JOIN product_variant pv 
//                     ON pv.id = oi.product_variant_id
    
//                 LEFT JOIN products p 
//                     ON p.id = pv.product_id
    
//                 WHERE o.date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)
    
//                 AND o.seller_id = '".$seller_id."'
    
//                 GROUP BY o.id
    
//                 ORDER BY ".$sort." DESC
    
//                 LIMIT ".$offset.", ".$limit;
    
//             } else {
    
//                 $sql_query = "
//                 SELECT 
//                     o.id,
//                     o.mobile,
//                     o.address,
//                     o.date_added,
//                     o.final_total,
//                     o.payment_method,
    
//                     GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS product_name,
    
//                     SUM(oi.quantity) AS qty
    
//                 FROM orders o
    
//                 LEFT JOIN order_items oi 
//                     ON oi.order_id = o.id
    
//                 LEFT JOIN product_variant pv 
//                     ON pv.id = oi.product_variant_id
    
//                 LEFT JOIN products p 
//                     ON p.id = pv.product_id
    
//                 WHERE o.date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)
    
//                 AND (
//                     o.mobile LIKE '%".$keyword."%'
//                     OR p.name LIKE '%".$keyword."%'
//                 )
    
//                 AND o.seller_id = '".$seller_id."'
    
//                 GROUP BY o.id
    
//                 ORDER BY ".$sort." DESC
    
//                 LIMIT ".$offset.", ".$limit;
//             }
//         }
    
//         $db->sql($sql_query);
    
//         $res = $db->getResult();
    
//         if(!is_array($res)){
//             $res = array();
//         }
    
//         foreach($res as $row){
    
//             if(!is_array($row)){
//                 continue;
//             }
    
//             $tempRow = array();
    
//             $tempRow['id'] = $row['id'];
    
//             $tempRow['mobile'] = $row['mobile'];
    
//             $tempRow['address'] = $row['address'];
    
//             $tempRow['product_name'] = !empty($row['product_name']) 
//                 ? $row['product_name'] 
//                 : '-';
    
//             $tempRow['qty'] = !empty($row['qty']) 
//                 ? $row['qty'] 
//                 : '0';
    
//             $tempRow['order_date'] = $row['date_added'];
    
//             $tempRow['final_total'] = $row['final_total'];
    
//             $tempRow['payment_method'] = ucfirst($row['payment_method']);
    
//             $rows[] = $tempRow;
//         }
    
//         $bulkData = array();
    
//         $bulkData['total'] = count($rows);
    
//         $bulkData['rows'] = $rows;
    
//         echo json_encode($bulkData);
//     }
    
//     if(isset($_GET['table']) && $_GET['table'] == 'sales_products'){
        
//         //print_r($_GET);
        
//         $rows = array();
		
// 		$offset = 0; $limit = 1000;
		
// 		$sort = 'id'; $order = 'ASC';
		
// 		$where = '';
		
// 		if(isset($_GET['offset']))
// 			$offset = $_GET['offset'];
			
// 		if(isset($_GET['limit']))
// 			$limit = $_GET['limit'];
		
// 		if(isset($_GET['sort']))
// 		if($_GET['sort']=='id'){
// 		    $sort="id";
// 		}else{
// 			$sort = $_GET['sort'];
// 		}
		
		
// 		if(!empty($_GET) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
		    
//             $month =  $_GET['start_date'];
            
//             $daysago = $_GET['end_date'];
            
//             if (!empty($_GET['search'])) {
      
//                 $keyword = $_GET['search'];
                 
//             } else {
                
//                 $keyword = "";
                
//             }
    
//             if(empty($keyword)) {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE DATE(date_added) < '" . $daysago . "' and DATE(date_added) > '" . $month . "' and seller_id = '".$seller_id."' ORDER BY id DESC ";
            
                
//             } else {
               
//               $sql_query = "SELECT id, mobile, address,date_added,final_total,payment_method  FROM orders WHERE DATE(date_added) < '" . $daysago . "' and DATE(date_added) > '" . $month . "'  AND  mobile LIKE '%".$keyword."%'  AND seller_id = '".$seller_id."'  ORDER BY id DESC ";
            
                
//             }

//             $db->sql($sql_query);
            
//             $res=$db->getResult();
            
//             //echo $sql_query; die;
            
//             //print_r($res);
            
            
// 		}else{
		    
// 		     if (!empty($_GET['search'])) {
//                 $keyword = $_GET['search'];
//             } else {
//                 $keyword = "";
//             }

//             if (empty($keyword)) {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)  AND seller_id = '".$seller_id."' ORDER BY id DESC";
            
                
//             } else {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)  AND mobile LIKE '%".$keyword."%'  AND seller_id = '".$seller_id."' ORDER BY id DESC";
//             }
            
     
//             $db->sql($sql_query);
       
//             $res=$db->getResult();
		    
// 		}
		
// 		foreach($res as $row){
			
// 			$tempRow['id'] = $row['id'];
// 			$tempRow['mobile'] = $row['mobile'];
// 			$tempRow['address'] = $row['address'];
// 			$tempRow['order_date'] = $row['date_added'];
// 			$tempRow['final_total'] = $row['final_total'];
// 			$tempRow['payment_method'] = ucfirst($row['payment_method']);
			
// 		    $rows[] = $tempRow;
// 		}
		
// 		$bulkData['total'] = count($rows);
		
// 		$bulkData['rows'] = $rows;
		
// 		print_r(json_encode($bulkData));
// 	}
	
		
	// data of 'Sales Report' table goes here
    
//     if(isset($_GET['table']) && $_GET['table'] == 'sales_products'){
        
//         //print_r($_GET);
        
//         $rows = array();
		
// 		$offset = 0; $limit = 1000;
		
// 		$sort = 'id'; $order = 'ASC';
		
// 		$where = '';
		
// 		if(isset($_GET['offset']))
// 			$offset = $_GET['offset'];
			
// 		if(isset($_GET['limit']))
// 			$limit = $_GET['limit'];
		
// 		if(isset($_GET['sort']))
// 		if($_GET['sort']=='id'){
// 		    $sort="id";
// 		}else{
// 			$sort = $_GET['sort'];
// 		}
		
		
// 		if(!empty($_GET) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
		    
//             $month =  $_GET['start_date'];
            
//             $daysago = $_GET['end_date'];
            
//             if (!empty($_GET['search'])) {
      
//                 $keyword = $_GET['search'];
                 
//             } else {
                
//                 $keyword = "";
                
//             }
    
//             if(empty($keyword)) {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE DATE(date_added) < '" . $daysago . "' and DATE(date_added) > '" . $month . "' ORDER BY id DESC ";
            
                
//             } else {
               
//               $sql_query = "SELECT id, mobile, address,date_added,final_total,payment_method  FROM orders WHERE DATE(date_added) < '" . $daysago . "' and DATE(date_added) > '" . $month . "'  AND  mobile LIKE '%".$keyword."%'   ORDER BY id DESC ";
            
                
//             }

//             $db->sql($sql_query);
            
//             $res=$db->getResult();
            
//             //echo $sql_query; die;
            
//             //print_r($res);
            
            
// 		}else{
		    
// 		     if (!empty($_GET['search'])) {
//                 $keyword = $_GET['search'];
//             } else {
//                 $keyword = "";
//             }

//             if (empty($keyword)) {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY id DESC";
            
                
//             } else {
                
//                 $sql_query = "SELECT id, mobile,address,date_added,final_total,payment_method FROM orders WHERE date_added > DATE_SUB(NOW(), INTERVAL 1 MONTH)  AND mobile LIKE '%".$keyword."%' ORDER BY id DESC";
//             }
            
     
//             $db->sql($sql_query);
       
//             $res=$db->getResult();
		    
// 		}
		
// 		foreach($res as $row){
			
// 			$tempRow['id'] = $row['id'];
// 			$tempRow['mobile'] = $row['mobile'];
// 			$tempRow['address'] = $row['address'];
// 			$tempRow['order_date'] = $row['date_added'];
// 			$tempRow['final_total'] = $row['final_total'];
// 			$tempRow['payment_method'] = ucfirst($row['payment_method']);
			
// 		    $rows[] = $tempRow;
// 		}
		
// 		$bulkData['total'] = count($rows);
		
// 		$bulkData['rows'] = $rows;
		
// 		print_r(json_encode($bulkData));
// 	}
	
	
		// data of 'Invoice Report' table goes here
    
    if(isset($_GET['table']) && $_GET['table'] == 'invoice_report'){
        
        //print_r($_GET);
        
        $rows = array();
		
		$offset = 0; $limit = 1000;
		
		$sort = 'id'; $order = 'ASC';
		
		$where = '';
		
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
			
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
		if($_GET['sort']=='id'){
		    $sort="id";
		}else{
			$sort = $_GET['sort'];
		}
		
		
		if(!empty($_GET) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
		    
            $month =  $_GET['start_date'];
            
            $daysago = $_GET['end_date'];
            
            if (!empty($_GET['search'])) {
      
                $keyword = $_GET['search'];
                 
            } else {
                
                $keyword = "";
                
            }
    
            if(empty($keyword)) {
                
                $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE DATE(invoice_date) <= '" . $daysago . "' and DATE(invoice_date) >= '" . $month . "' ORDER BY id DESC ";
            
                
            } else {
               
               $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE DATE(invoice_date) <= '" . $daysago . "' and DATE(invoice_date) >= '" . $month . "'  AND  name LIKE '%".$keyword."%'   ORDER BY id DESC ";
            
                
            }

            $db->sql($sql_query);
            
            $res=$db->getResult();
            
           // echo $sql_query; die;
            
            //print_r($res);
            
            
		}else{
		    
		     if (!empty($_GET['search'])) {
                $keyword = $_GET['search'];
            } else {
                $keyword = "";
            }

            if (empty($keyword)) {
                
                $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH) ORDER BY id DESC";
            
                
            } else {
                
                $sql_query = "SELECT id, invoice_date,order_id, name, address, order_date, phone_number, order_list, email, discount, total_sale,shipping_charge, payment 
				FROM invoice WHERE invoice_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)  AND name LIKE '%".$keyword."%' ORDER BY id DESC";
            }
            
     
            $db->sql($sql_query);
       
            $res=$db->getResult();
		    
		}
		
		foreach($res as $row){
			
			$tempRow['id'] = $row['id'];
			$tempRow['invoice_date'] = $row['invoice_date'];
			$tempRow['order_id'] = $row['order_id'];
			$tempRow['name'] = ucfirst($row['name']);
			$tempRow['address'] = $row['address'];
			$tempRow['order_date'] = $row['order_date'];
			$tempRow['email'] = $row['email'];
			$tempRow['total_amount'] = $row['total_sale'];
			$tempRow['action']="<a href='order-detail.php?id=".$row['order_id']."'><i class='fa fa-eye'></i>View Order</a>
	                        <br><a href='invoice.php?id=".$row['order_id']."'><i class='fa fa-eye'></i>View Invoice</a>";
		    $rows[] = $tempRow;
		}
		
		$bulkData['total'] = count($rows);
		
		$bulkData['rows'] = $rows;
		
		print_r(json_encode($bulkData));
	}
	
		// data of 'Customers Report' table goes here
    
    if(isset($_GET['table']) && $_GET['table'] == 'customers_report'){
        
        //print_r($_GET);
        
        $rows = array();
		
		$offset = 0; $limit = 1000;
		
		$sort = 'id'; $order = 'ASC';
		
		$where = '';
		
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
			
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
		if($_GET['sort']=='id'){
		    $sort="id";
		}else{
			$sort = $_GET['sort'];
		}
		
		
		if(!empty($_GET) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
		    
            $month =  $_GET['start_date'];
            
            $daysago = $_GET['end_date'];
            
            if (!empty($_GET['search'])) {
      
                $keyword = $_GET['search'];
                 
            } else {
                
                $keyword = "";
                
            }
    
            if(empty($keyword)) {
                
                $sql_query = "SELECT
    	users.`name`, 
    	users.email, 
    	users.id, 
    	users.country_code, 
    	users.mobile, 
    	COUNT(orders.id) AS orders_count,
    	SUM(orders.final_total) as amount
    FROM
    	users
    	INNER JOIN
    	orders
    	ON 
    		users.id = orders.user_id
    		WHERE orders.date_added < '" . $daysago . "' and orders.date_added > '" . $month . "' and orders.seller_id = '".$seller_id."' GROUP BY orders.user_id
    		ORDER BY orders_count DESC";
            
                
            } else {
               
               $sql_query = "SELECT
    	users.`name`, 
    	users.email, 
    	users.id, 
    	users.country_code, 
    	users.mobile, 
    	COUNT(orders.id) AS orders_count,
    	SUM(orders.final_total) as amount
    FROM
    	users
    	INNER JOIN
    	orders
    	ON 
    		users.id = orders.user_id
    		WHERE orders.date_added < '" . $daysago . "' and orders.date_added > '" . $month . "' AND  name LIKE '%".$keyword."%'  AND orders.seller_id = '".$seller_id."' GROUP BY orders.user_id
    		ORDER BY orders_count DESC";
            
                
            }

            $db->sql($sql_query);
            
            $res=$db->getResult();
            
           // echo $sql_query; die;
            
            //print_r($res);
            
            
		}else{
		    
		     if (!empty($_GET['search'])) {
                $keyword = $_GET['search'];
            } else {
                $keyword = "";
            }

            if (empty($keyword)) {
                
                $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE  orders.seller_id = '".$seller_id."'
		GROUP BY orders.user_id
		ORDER BY orders_count DESC";
            
                
            } else {
                
                $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE name LIKE '%".$keyword."%'  AND orders.seller_id = '".$seller_id."'
		GROUP BY orders.user_id
		ORDER BY orders_count DESC";
            }
            
     
            $db->sql($sql_query);
       
            $res=$db->getResult();
		    
		}
		
		foreach($res as $row){
			
			$tempRow['name'] = $row['name'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['email'] = $row['email'];
			$tempRow['orders_count'] = ucfirst($row['orders_count']);
			$tempRow['amount'] = number_format($row['amount'],2);
			$tempRow['order_date'] = $row['order_date'];
			$tempRow['action']="<a class='btn btn-xs' href='get-orders-by-user.php?id=".$row['id']."'> Last 3 Months Orders</a>";
		    $rows[] = $tempRow;
		}
		
		$bulkData['total'] = count($rows);
		
		$bulkData['rows'] = $rows;
		
		print_r(json_encode($bulkData));
	}
	
			// data of 'Products Report' table goes here
    		// data of 'LOYALTY POINTS' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'loyalty'){
		
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `mobile` like '%".$search."%' OR `city` like '%".$search."%' OR `area` like '%".$search."%' OR `street` like '%".$search."%' OR `status` like '%".$search."%' OR `created_at` like '%".$search."%'";
		}
		if(isset($_GET['filter_order_status']) && $_GET['filter_order_status'] !=''){
			$filter_order = $_GET['filter_order'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' and active_status='.$filter_order;
			else
				$where =' where active_status='.$filter_order;
		}
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u ".$where." ORDER BY `".$sort."` ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		$operate = ' <a href="view-loyaltypoints.php?id='.$row['id'].'"><i class="fa fa-view"></i>View</a>';
		$sql = "SELECT count(orders.id),sum(orders.final_total) FROM `orders` where user_id=".$row['id'];
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		    $total_order_amount="";
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['email'] = $row['email'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['balance'] = $row['balance'];
			$tempRow['referral_code'] = $row['referral_code'];
			$tempRow['friends_code'] = !empty($row['friends_code'])?$row['friends_code']:'-';
			$tempRow['city_id'] = $row['city'];
			$tempRow['city'] = $row['city_name'];
			$tempRow['area_id'] = $row['area'];
			$tempRow['area'] = $row['area_name'];
			$tempRow['street'] = $row['street'];
			$tempRow['apikey'] = $row['apikey'];
			$tempRow['status'] = $row['status'];
			$tempRow['Totalorder'] = $res[0]['count(orders.id)'];
			$tempRow['Totalorderamount'] = $res[0]['sum(orders.final_total)'];
			$tempRow['total_earned'] = $row['earned_loyalty_points'];
			$tempRow['total_redeem'] = $row['redeem_loyalty_points'];
			$tempRow['balance_points'] = $row['balance_loyalty_points'];
			$tempRow['created_at'] = $row['created_at'];
			$tempRow['operate'] = $operate;
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
	
			// data of 'Each Order LOYALTY POINTS' table goes here
	if(isset($_GET['table']) && $_GET['table'] == 'viewloyalty'){
		
// 		print_r($_GET['id']);
		$offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
			$sort = $_GET['sort'];
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search'])){
			$search = $_GET['search'];
			$where = " Where `id` like '%".$search."%' OR `name` like '%".$search."%' OR `email` like '%".$search."%' OR `mobile` like '%".$search."%' OR `city` like '%".$search."%' OR `area` like '%".$search."%' OR `street` like '%".$search."%' OR `status` like '%".$search."%' OR `created_at` like '%".$search."%'";
		}
		if(isset($_GET['filter_order_status']) && $_GET['filter_order_status'] !=''){
			$filter_order = $_GET['filter_order'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' and active_status='.$filter_order;
			else
				$where =' where active_status='.$filter_order;
		}
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT *,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u ".$where." ORDER BY `".$sort."` ".$order." LIMIT ".$offset.", ".$limit;
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		foreach($res as $row){
		$operate = ' <a href="view-loyaltypoints.php?id='.$row['id'].'"><i class="fa fa-view"></i>View</a>';
		$sql = "SELECT count(orders.id),sum(orders.final_total) FROM `orders` where user_id=".$row['id'];
		$db->sql($sql);
		$res = $db->getResult();
// 		print_r($res);
		    $total_order_amount="";
			$tempRow['id'] = $row['id'];
			$tempRow['name'] = $row['name'];
			$tempRow['email'] = $row['email'];
			$tempRow['mobile'] = $row['mobile'];
			$tempRow['balance'] = $row['balance'];
			$tempRow['referral_code'] = $row['referral_code'];
			$tempRow['friends_code'] = !empty($row['friends_code'])?$row['friends_code']:'-';
			$tempRow['city_id'] = $row['city'];
			$tempRow['city'] = $row['city_name'];
			$tempRow['area_id'] = $row['area'];
			$tempRow['area'] = $row['area_name'];
			$tempRow['street'] = $row['street'];
			$tempRow['apikey'] = $row['apikey'];
			$tempRow['status'] = $row['status'];
			$tempRow['Totalorder'] = $res[0]['count(orders.id)'];
			$tempRow['Totalorderamount'] = $res[0]['sum(orders.final_total)'];
			$tempRow['total_earned'] = $row['earned_loyalty_points'];
			$tempRow['total_redeem'] = $row['redeem_loyalty_points'];
			$tempRow['balance_points'] = $row['balance_loyalty_points'];
			$tempRow['created_at'] = $row['created_at'];
			$tempRow['operate'] = $_GET['id'];
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		print_r(json_encode($bulkData));
	}
    if(isset($_GET['table']) && $_GET['table'] == 'products_report'){
        
        //print_r($_GET);
        
        $rows = array();
		
		 
		
		$sort = 'id'; $order = 'ASC';
		
		$where = '';
		
		if(isset($_GET['limit'])){
		    $limit = $_GET['limit'];
		}else{
		    $limit = 0;
		}
			
			
		if(isset($_GET['offset'])){
			$offset = $_GET['offset'];
		}else{
		    $offset = 1000;
		}
		
		if(isset($_GET['sort']))
		if($_GET['sort']=='id'){
		    $sort="id";
		}else{
			$sort = $_GET['sort'];
		}
		
		
		if(!empty($_GET) && !empty($_GET['start_date']) && !empty($_GET['end_date'])) {
		    
            $month =  $_GET['start_date'];
            
            $daysago = $_GET['end_date'];
            
            if (!empty($_GET['search'])) {
      
                $keyword = $_GET['search'];
                 
            } else {
                
                $keyword = "";
                
            }
    
            if(empty($keyword)) {
                
                $sql_query = "SELECT
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
	WHERE oi.active_status!='cancelled' AND oi.date_added< '" . $daysago . "' and oi.date_added > '" . $month . "'  and o.seller_id = '".$seller_id."'	GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$limit.", ".$offset;
            
                
            } else {
               
               $sql_query = "SELECT
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
	WHERE oi.active_status!='cancelled' AND oi.date_added< '" . $daysago . "' and oi.date_added > '" . $month . "' AND  p.name LIKE '%".$keyword."%'  AND o.seller_id = '".$seller_id."' GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$limit.", ".$offset;
            
                
            }

            $db->sql($sql_query);
            
            $res=$db->getResult();
            
           // echo $sql_query; die;
            
            //print_r($res);
            
            
		}else{
		    
		     if (!empty($_GET['search'])) {
                $keyword = $_GET['search'];
            } else {
                $keyword = "";
            }

            if (empty($keyword)) {
                
                $sql_query = "SELECT
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
	WHERE oi.active_status!='cancelled'  AND o.seller_id = '".$seller_id."' GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$limit.", ".$offset;
            
                
            } else {
                
                $sql_query = "SELECT
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
	WHERE oi.active_status!='cancelled' AND  p.name LIKE '%".$keyword."%'  AND o.seller_id = '".$seller_id."' GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$limit.", ".$offset;
            }
          //  print_r($sql_query);
     
            $db->sql($sql_query);
       
            $res=$db->getResult();
		    
		}
		
		foreach($res as $row){
			
			$tempRow['name'] = $row['name'];
			$tempRow['unit'] = $row['measurement']." ".$row['mesurement_unit_name'];
			$tempRow['qty'] = $row['qty'];
			$tempRow['orders_count'] = $row['order_count'];
		    $rows[] = $tempRow;
		}
		
		$bulkData['total'] = count($rows);
		
		$bulkData['rows'] = $rows;
		
		print_r(json_encode($bulkData));
	}
	
	$db->disconnect();
?>