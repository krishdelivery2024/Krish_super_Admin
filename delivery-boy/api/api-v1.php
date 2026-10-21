<?php
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Access-Control-Allow-Origin: *');


// include_once('../../api-firebase/send-email.php');
include_once('../../includes/crud.php');
include_once('../../includes/custom-functions.php');
include_once('verify-token.php');
$fn = new custom_functions();
$db = new Database();
$db->connect();
include_once('../../includes/variables.php');
include_once('../../api-firebase/send-sms.php');
$config = $fn->get_configurations();
$settings = $fn->get_settings('system_timezone', true);
$app_name = $settings['app_name'];

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
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if ($access_key != $accesskey) {
	$response['error'] = true;
	$response['message'] = "No Accsess key found!";
	print_r(json_encode($response));
	return false;
	exit();
}

if (isset($_POST['login'])) {
	/*
   1.Login
	   accesskey:90336
	   mobile:9876543210
	   password:12345678
	   fcm_id:YOUR_FCM_ID
	   Login:1
   */
	if (empty(trim($_POST['mobile']))) {
		$response['error'] = true;
		$response['message'] = "Mobile should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	if (empty($_POST['password'])) {
		$response['error'] = true;
		$response['message'] = "Password should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}


	$mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
	$password = md5($_POST['password']);
	$sql = "SELECT * FROM delivery_boys	WHERE mobile = '" . $mobile . "' AND password = '" . $password . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);


	if ($num == 1) {
		if ($res[0]['status'] == 0) {
			$response['error'] = true;
			$response['message'] = "It seems your acount is not active please contact admin for more info!";
			$response['data'] = array();
		} else {
			$delivery_boy_id = $res[0]['id'];
			$fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
			//  $fcm_id = $fn->generateBeamsToken('DB'.$res[0]['id']);
			if (!empty($fcm_id)) {
				$sql1 = "update delivery_boys set `fcm_id` ='$fcm_id' where id = '" . $delivery_boy_id . "'";
				$db->sql($sql1);
				$db->sql($sql);
				$res = $db->getResult();
				$db->disconnect();
				$res[0]['fcm_id'] = $fcm_id;
			}

			// Send Push Notification
			$title = "Keep Online";
			$message = "keep online to get more orders";
			$type = "default";
			$fn->send_notification_to_delivery_boy($delivery_boy_id, $title, $message, $type, "0");
			$fn->store_delivery_boy_notification($delivery_boy_id, "0", $title, $message, $type);

			$response['error'] = false;
			$response['message'] = "Delivery Boy Login Susseccfully";
			if (!empty($res)) {
				for ($i = 0; $i < count($res); $i++) {
					$res[$i]['profile'] = !empty($res[$i]['profile']) ? DOMAIN_URL . $res[$i]['profile'] : '';
				}
			}
			$response['data'] = $res;
		}
	} else {
		if ($num == 0) {
			$response['error'] = true;
			$response['message'] = "Invalid number or password, Try again.";
		} else {
			// just in case something else is wrong
			if ($res[0]['mobile'] != $mobile) {
				$response['error'] = true;
				$response['message'] = "Phone Number is not registered!";
			}
			if ($res[0]['password'] != $password) {
				$response['error'] = true;
				$response['message'] = "Invalid password, Try again.";
			}
		}
	}

	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}


/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['login_with_mobile'])) {

	if (empty(trim($_POST['mobile']))) {
		$response['error'] = true;
		$response['message'] = "Mobile should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}


	$mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
	$sql = "SELECT * FROM delivery_boys	WHERE mobile = '" . $mobile . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);


	if ($num == 1) {
		if ($res[0]['status'] == 0) {
			$response['error'] = true;
			$response['message'] = "It seems your acount is not active please contact admin for more info!";
			$response['data'] = array();
		} else {

			// $otpno = rand(111111,999999);
			$otpno = 123456;
			$recipients = "91" . trim($mobile);

			$messagetext = "Hello Partner, your OTP for Krish Delivery login is " . $otpno . ". Ride safe!";
			$template_id = "1207178368065673587";

			if (sms_daily_limit_reached($db)) {
				echo json_encode(["error" => true, "message" => "SMS limit reached. Please try again later."]);
				exit;
			}

			$delivery_boy_id = $res[0]['id'];

			$sql1 = "update delivery_boys set `otp`='$otpno' where id = '" . $delivery_boy_id . "'";
			$db->sql($sql1);

			sendSmsCommon($recipients, $messagetext, $template_id);

			sms_increment_daily_count($db);

			$db->disconnect();

			$response['error'] = false;
			$response['message'] = "Login Susseccfully";
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Invalid Mobile, Try again.";
	}

	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}

/*
---------------------------------------------------------------------------------------------------------
*/
if (isset($_POST['otp_verify'])) {

	if (empty(trim($_POST['mobile']))) {
		$response['error'] = true;
		$response['message'] = "Mobile should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}

	if (empty(trim($_POST['otp']))) {
		$response['error'] = true;
		$response['message'] = "OTP should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}


	$mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
	$otp = $db->escapeString(trim($fn->xss_clean($_POST['otp'])));

	$sql = "SELECT * FROM delivery_boys	WHERE mobile = '" . $mobile . "' AND otp = '" . $otp . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);


	if ($num == 1) {
		if ($res[0]['status'] == 0) {
			$response['error'] = true;
			$response['message'] = "It seems your acount is not active please contact admin for more info!";
			$response['data'] = array();
		} else {


			$delivery_boy_id = $res[0]['id'];
			//  $fcm_id = $fn->generateBeamsToken('DB'.$res[0]['id']);
			$fcm_id = (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) ? $db->escapeString($fn->xss_clean($_POST['fcm_id'])) : "";
			if (!empty($fcm_id)) {
				$sql1 = "update delivery_boys set `fcm_id` ='$fcm_id',`otp`='' where id = '" . $delivery_boy_id . "'";
				$db->sql($sql1);
				$db->sql($sql);
				$db->disconnect();
				$res[0]['fcm_id'] = $fcm_id;
			}

			// Send Push Notification
			$title = "Keep Online";
			$message = "keep online to get more orders";
			$type = "default";
			$fn->send_notification_to_delivery_boy($delivery_boy_id, $title, $message, $type, "0");
			$fn->store_delivery_boy_notification($delivery_boy_id, "0", $title, $message, $type);

			$response['error'] = false;
			$response['message'] = "OTP Verified Susseccfully";
			if (!empty($res)) {
				for ($i = 0; $i < count($res); $i++) {
					$res[$i]['profile'] = !empty($res[$i]['profile']) ? DOMAIN_URL . $res[$i]['profile'] : '';
				}
			}
			$response['data'] = $res;
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Invalid Mobile Or OTP, Try again.";
	}

	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}
/*
---------------------------------------------------------------------------------------------------------
*/
if (isset($_POST['delivery_boy_status'])) {

	if (empty(trim($_POST['id']))) {
		$response['error'] = true;
		$response['message'] = "ID should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}

	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$sql = "SELECT * FROM delivery_boys	WHERE id = '" . $id . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);


	if ($num == 1) {
		if ($res[0]['status'] == 0) {
			$response['error'] = true;
			$response['message'] = "It seems your acount is not active please contact admin for more info!";
			$response['data'] = array();
		} else {

			$active_status = $res[0]['active_status'];
			if ($active_status == 'true') {
				$update_active_status = 'false';
			} else {
				$update_active_status = 'true';
			}

			$sql1 = "update delivery_boys set `active_status` ='$update_active_status' where id = '" . $id . "'";
			$db->sql($sql1);


			$response['error'] = false;
			$response['message'] = "Status Changed Susseccfully";
			$response['active_status'] = $update_active_status;
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Invalid ID, Try again.";
	}

	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}
/*
---------------------------------------------------------------------------------------------------------
*/
if (isset($_POST['update_delivery_boy_location'])) {
	/*
		accesskey:90336
		id:1
		latitude:13.0827
		longitude:80.2707
		update_delivery_boy_location:1

		Called by the delivery boy app every 20 seconds while the boy is Online.
		Only the last known point is stored; the admin live map reads it. Updates
		are accepted only while active_status = 'true', so a boy who has gone
		Offline stops appearing on the map even if a late request arrives.
	*/
	$id = (isset($_POST['id'])) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';
	$latitude = (isset($_POST['latitude'])) ? trim($fn->xss_clean($_POST['latitude'])) : '';
	$longitude = (isset($_POST['longitude'])) ? trim($fn->xss_clean($_POST['longitude'])) : '';

	if ($id === '' || $latitude === '' || $longitude === '' || !is_numeric($latitude) || !is_numeric($longitude)) {
		$response['error'] = true;
		$response['message'] = "Delivery boy id, latitude and longitude are required.";
		print_r(json_encode($response));
		die;
	}

	$sql = "SELECT id, active_status FROM delivery_boys WHERE id = '" . $id . "'";
	$db->sql($sql);
	$res = $db->getResult();

	if (empty($res)) {
		$response['error'] = true;
		$response['message'] = "Invalid delivery boy id.";
		print_r(json_encode($response));
		die;
	}

	if ($res[0]['active_status'] !== 'true') {
		// Not online: ignore the update so a stale point can't be plotted.
		$response['error'] = false;
		$response['message'] = "Delivery boy is offline. Location not stored.";
		$response['stored'] = false;
		print_r(json_encode($response));
		die;
	}

	$lat = (float) $latitude;
	$lng = (float) $longitude;
	if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
		$response['error'] = true;
		$response['message'] = "Coordinates out of range.";
		print_r(json_encode($response));
		die;
	}

	$sql1 = "UPDATE delivery_boys SET latitude = '" . $lat . "', longitude = '" . $lng . "', location_updated_at = NOW() WHERE id = '" . $id . "'";
	$db->sql($sql1);

	$response['error'] = false;
	$response['message'] = "Location updated successfully.";
	$response['stored'] = true;
	$response['latitude'] = $lat;
	$response['longitude'] = $lng;
	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";
}
/*
---------------------------------------------------------------------------------------------------------
*/
if (isset($_POST['get_delivery_boy_by_id'])) {

	/*
	2.get_delivery_boy_by_id
		accesskey:90336
		id:78
		get_delivery_boy_by_id:1
	*/
	if (empty($_POST['id'])) {
		$response['error'] = true;
		$response['message'] = "Id of Delivery boy should be Passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$sql = "SELECT * FROM delivery_boys	WHERE id = '" . $id . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);
	$db->disconnect();
	if ($num == 1) {
		$response['error'] = false;
		$response['message'] = "Delivery Boy Data Fetched Susseccfully";
		if (!empty($res)) {
			for ($i = 0; $i < count($res); $i++) {
				$res[$i]['profile'] = !empty($res[$i]['profile']) ? DOMAIN_URL . $res[$i]['profile'] : '';
			}
		}
		$response['data'] = $res;
	} else {
		$response['error'] = true;
		$response['message'] = "No data found!";
	}
	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/*
---------------------------------------------------------------------------------------------------------
*/



if (isset($_POST['get_orders_by_delivery_boy_id'])) {

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

	$id = (isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id'])) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';

	// For 'new' orders, the delivery boy ID is passed as get_orders_by_delivery_boy_id value
	// e.g. get_orders_by_delivery_boy_id=10 means id=10
	$caller_id = $id;
	if (empty($caller_id) && isset($_POST['get_orders_by_delivery_boy_id']) && is_numeric($_POST['get_orders_by_delivery_boy_id'])) {
		$caller_id = $db->escapeString(trim($fn->xss_clean($_POST['get_orders_by_delivery_boy_id'])));
	}

	// Accept both 'latitude' and correctly-spelled/misspelled longitude keys
	$deliveryboylatitude = (isset($_POST['latitude']) && !empty(trim($_POST['latitude'])) && is_numeric($_POST['latitude'])) ? $db->escapeString(trim($fn->xss_clean($_POST['latitude']))) : '';

	if (isset($_POST['longitude']) && !empty(trim($_POST['longitude'])) && is_numeric($_POST['longitude'])) {
		$deliveryboylongtitude = $db->escapeString(trim($fn->xss_clean($_POST['longitude'])));
	} elseif (isset($_POST['longtitude']) && !empty(trim($_POST['longtitude'])) && is_numeric($_POST['longtitude'])) {
		$deliveryboylongtitude = $db->escapeString(trim($fn->xss_clean($_POST['longtitude'])));
	} else {
		$deliveryboylongtitude = '';
	}

	$order_id = (isset($_POST['order_id']) && !empty(trim($_POST['order_id'])) && is_numeric($_POST['order_id'])) ? $db->escapeString(trim($fn->xss_clean($_POST['order_id']))) : '';
	$where = '';
	$where1 = '';
	$offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
	$limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;


	$status = isset($_POST['status']) ? $_POST['status'] : '';

	if (($status == 'active' || $status == 'past') && empty($id)) {
		$response_data['error'] = true;
		$response_data['message'] = "Delivery Boy ID is required when status is active or past.";
		echo json_encode($response_data);
		die;
	}

	if ($status == 'new' && !empty($caller_id)) {
		$sql_check = "SELECT active_status FROM delivery_boys WHERE id='$caller_id'";
		$db->sql($sql_check);
		$res_check = $db->getResult();
		if (!empty($res_check) && $res_check[0]['active_status'] == 'false') {
			$response_data['error'] = true;
			$response_data['message'] = "You are currently offline. Please turn on your active status to receive new orders.";
			$response_data['data'] = array();
			echo json_encode($response_data);
			die;
		}
	}

	$sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
	$order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
	if (isset($_POST['search']) && !empty(trim($_POST['search']))) {
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where .= " where (name like '%" . $search . "%' OR o.id like '%" . $search . "%' OR o.mobile like '%" . $search . "%' OR address like '%" . $search . "%' OR `payment_method` like '%" . $search . "%' OR `delivery_charge` like '%" . $search . "%' OR `delivery_time` like '%" . $search . "%' OR o.`status` like '%" . $search . "%' OR `date_added` like '%" . $search . "%')";
	}

	if (isset($_POST['filter_order']) && $_POST['filter_order'] != '') {
		$filter_order = $db->escapeString($fn->xss_clean($_POST['filter_order']));
		if (isset($_POST['search']) && $_POST['search'] != '') {
			$where .= " and `active_status`='" . $filter_order . "'";
		} else {
			$where .= " where `active_status`='" . $filter_order . "'";
		}
	}

        if ($status == "new" && empty($where)) {
                $where = " WHERE 1=1";
                $where .= (!empty($order_id)) ? " AND o.id = $order_id" : "";
        } else if ($status != "new" && empty($where)) {
		if (empty($id)) {
			$where .= (!empty($order_id)) ? " WHERE o.id = $order_id" : "";
		} else {
			$where .= " WHERE delivery_boy_id = " . $id;
			$where .= (!empty($order_id)) ? " AND o.id = $order_id" : "";
		}
	} else {
		$where .= (!empty($id) && $status != "new") ? " AND delivery_boy_id = " . $id : "";
		$where .= (!empty($order_id)) ? " AND o.id = $order_id" : "";
	}


	if (isset($_POST['status'])) {
		$where1 = $where;
		if ($_POST['status'] == 'active') {
			$where .= " AND active_status != 'delivered' AND active_status != 'cancelled' AND active_status != 'returned'";
		} else if ($_POST['status'] == 'past') {
			$where .= " AND active_status != 'received' AND active_status != 'processed' AND active_status != 'shipped'";
		} else if ($_POST['status'] == 'new') {
				$where .= " AND o.active_status = 'processed' AND delivery_boy_id = '0' AND (o.delivery_method IS NULL OR LOWER(TRIM(o.delivery_method)) != 'storepickup') AND (o.dispatch_stage IN ('phase1', 'phase2') OR o.dispatch_stage IS NULL OR o.dispatch_stage = '') ";
			// Exclude orders this delivery boy has already rejected
			if (!empty($caller_id)) {
				$where .= " AND (rejected_by IS NULL OR rejected_by = '' OR NOT FIND_IN_SET('$caller_id', rejected_by)) ";
				// Parcel-only delivery boys should not see food orders
				$sql_boy = "SELECT service_type, zone_id FROM delivery_boys WHERE id = '$caller_id'";
				$db->sql($sql_boy);
				$res_boy = $db->getResult();
				$boy_service = (!empty($res_boy) && isset($res_boy[0]['service_type'])) ? $res_boy[0]['service_type'] : 'both';
				$boy_zone_id = (!empty($res_boy) && isset($res_boy[0]['zone_id'])) ? $res_boy[0]['zone_id'] : 0;
				if ($boy_service == 'parcel') {
					$where .= " AND 1 = 0 ";
				}
				if (!empty($boy_zone_id) && $boy_zone_id > 0) {
				    $where .= " AND o.zone_id = " . $boy_zone_id;
				}
			}
		}
	} else {
		$where1 = $where;
	}
	$orders_join = " JOIN users u ON u.id=o.user_id JOIN seller s ON s.id=o.seller_id";


	$start_date = isset($_POST['start_date']) ? $db->escapeString(trim($fn->xss_clean($_POST['start_date']))) : '';
	$end_date = isset($_POST['end_date']) ? $db->escapeString(trim($fn->xss_clean($_POST['end_date']))) : '';

	if (!empty($start_date) && !empty($end_date)) {
		$date_filter = " DATE(o.date_added) >= '$start_date' AND DATE(o.date_added) <= '$end_date'";

		if (!empty($where)) { $where .= " AND" . $date_filter; } else { $where .= " WHERE" . $date_filter; }
		if (!empty($where1)) { $where1 .= " AND" . $date_filter; } else { $where1 .= " WHERE" . $date_filter; }
	} else if ($status != 'history') {
		// Existing behavior
		if (!empty($where1)) {
			$where1 .= " AND DATE(o.date_added) = CURDATE()";
		} else {
			$where1 .= " WHERE DATE(o.date_added) = CURDATE()";
		}
	}

	$sql = "SELECT COUNT(o.id) as total FROM `orders` o " . $orders_join . " " . $where1;
	$db->sql($sql);
	$res = $db->getResult();

	foreach ($res as $row) {
		$total = $row['total'];
	}
	$sql = "select o.*,o.name as username,u.name as name, u.latitude as user_latitude, u.longitude as user_longitude, s.latitude as seller_latitude, s.longitude as seller_longitude, s.name as seller_name,s.company_name as seller_company_name,s.mobile as seller_mobile,s.company_address as seller_company_address,s.image as seller_image FROM orders o " . $orders_join . " " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;

	$db->sql($sql);
	$res = $db->getResult();

	// Opportunistic Phase-2 escalation: any unassigned order that has been in
	// Phase-1 for 60+ seconds is broadcast to the whole network while the boy
	// is already polling. The guarded transition fires the push only once.
	if (isset($_POST['status']) && $_POST['status'] == 'new') {
        // Auto-dispatch any orders accepted by seller that haven't been claimed for Phase 1
        $db->sql("SELECT id FROM orders WHERE (dispatch_stage IS NULL OR dispatch_stage = '') AND delivery_boy_id = '0' AND active_status = 'processed' AND (delivery_method IS NULL OR LOWER(TRIM(delivery_method)) != 'storepickup')");
        $unclaimed_orders = $db->getResult();
        if (!empty($unclaimed_orders)) {
                foreach ($unclaimed_orders as $u_row) {
                        $fn->dispatch_new_order('food', (int)$u_row['id']);
                }
        }

        $wait_sec = (int)custom_functions::DISPATCH_PHASE1_WAIT_SECONDS;
        $db->sql("SELECT id FROM orders WHERE dispatch_stage = 'phase1' AND delivery_boy_id = '0' AND dispatch_phase1_at IS NOT NULL AND dispatch_phase1_at <= NOW() - INTERVAL $wait_sec SECOND AND active_status = 'processed'");
        $due_orders = $db->getResult();
        if (!empty($due_orders)) {
                foreach ($due_orders as $d_row) {
                        $fn->escalate_due_dispatch('food', (int)$d_row['id']);
                }
        }
}

	for ($i = 0; $i < count($res); $i++) {
		$sql = "select oi.*,p.name as name, v.measurement,p.image, (SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name from `order_items` oi
            join product_variant v on oi.product_variant_id=v.id
            join products p on p.id=v.product_id
            where oi.order_id=" . $res[$i]['id'];

		$db->sql($sql);
		$res[$i]['items'] = $db->getResult();
	}

	$rows1 = $tempRow = array();
	$response_data['total'] = $total;


	// ================= TODAY EARNINGS =================

	$today_date = date('Y-m-d');

	$today_earn_cod = 0;
	$today_earn_online = 0;

	if (!empty($id)) {

		// COD Earnings
		$sql_cod = "
            SELECT IFNULL(SUM(final_total),0) AS total_cod
            FROM orders
            WHERE delivery_boy_id = '$id'
            AND active_status = 'delivered'
            AND payment_method = 'Cash on Delivery'
            AND DATE(date_added) = '$today_date'
        ";

		$db->sql($sql_cod);
		$cod_result = $db->getResult();

		if (!empty($cod_result)) {
			$today_earn_cod = $cod_result[0]['total_cod'];
		}

		// ONLINE Earnings
		$sql_online = "
            SELECT IFNULL(SUM(final_total),0) AS total_online
            FROM orders
            WHERE delivery_boy_id = '$id'
            AND active_status = 'delivered'
            AND payment_method != 'Cash on Delivery'
            AND DATE(date_added) = '$today_date'
        ";

		$db->sql($sql_online);
		$online_result = $db->getResult();

		if (!empty($online_result)) {
			$today_earn_online = $online_result[0]['total_online'];
		}

		// DELIVERY CHARGE
		$sql_delivery_charge = "
            SELECT IFNULL(SUM(delivery_charge),0) AS total_delivery_charge
            FROM orders
            WHERE delivery_boy_id = '$id'
            AND active_status = 'delivered'
            AND DATE(date_added) = '$today_date'
        ";

		$db->sql($sql_delivery_charge);
		$delivery_charge_result = $db->getResult();
		$today_delivery_charge = (!empty($delivery_charge_result)) ? $delivery_charge_result[0]['total_delivery_charge'] : 0;

		// ── TODAY ORDERS + EARNINGS BY SERVICE (food in-person delivery vs pickup) ──
		$sql_food_pickup = "
            SELECT
                COUNT(id) AS cnt,
                IFNULL(SUM(CASE WHEN payment_method = 'Cash on Delivery' THEN final_total ELSE 0 END),0) AS cod,
                IFNULL(SUM(CASE WHEN payment_method != 'Cash on Delivery' THEN final_total ELSE 0 END),0) AS online,
                IFNULL(SUM(delivery_charge),0) AS dc
            FROM orders
            WHERE delivery_boy_id = '$id'
            AND active_status = 'delivered'
            AND LOWER(TRIM(delivery_method)) != 'storepickup'
            AND DATE(date_added) = '$today_date'
        ";

		$db->sql($sql_food_pickup);
		$food_result = $db->getResult();

		$sql_pickup = "
            SELECT
                COUNT(id) AS cnt,
                IFNULL(SUM(CASE WHEN payment_method = 'Cash on Delivery' THEN final_total ELSE 0 END),0) AS cod,
                IFNULL(SUM(CASE WHEN payment_method != 'Cash on Delivery' THEN final_total ELSE 0 END),0) AS online,
                IFNULL(SUM(delivery_charge),0) AS dc
            FROM orders
            WHERE delivery_boy_id = '$id'
            AND active_status = 'delivered'
            AND LOWER(TRIM(delivery_method)) = 'storepickup'
            AND DATE(date_added) = '$today_date'
        ";

		$db->sql($sql_pickup);
		$pickup_result = $db->getResult();

		$today_food_orders = (!empty($food_result)) ? $food_result[0]['cnt'] : 0;
		$today_food_cod = (!empty($food_result)) ? $food_result[0]['cod'] : 0;
		$today_food_online = (!empty($food_result)) ? $food_result[0]['online'] : 0;
		$today_food_delivery_charge = (!empty($food_result)) ? $food_result[0]['dc'] : 0;

		$today_pickup_orders = (!empty($pickup_result)) ? $pickup_result[0]['cnt'] : 0;
		$today_pickup_cod = (!empty($pickup_result)) ? $pickup_result[0]['cod'] : 0;
		$today_pickup_online = (!empty($pickup_result)) ? $pickup_result[0]['online'] : 0;
		$today_pickup_delivery_charge = (!empty($pickup_result)) ? $pickup_result[0]['dc'] : 0;
	}

	// response
	// ================= DATE RANGE SETTLEMENT SUMMARY =================

	// Fetch platform fee and GST from settings
	$sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
	$db->sql($sql_settings);
	$settings_res = $db->getResult();
	$platform_fee_percent = 0;
	$gst_percent = 0;
	if (!empty($settings_res)) {
	    $sys_settings = json_decode($settings_res[0]['value'], true);
	    if (isset($sys_settings['platform_fee'])) {
	        $platform_fee_percent = floatval($sys_settings['platform_fee']);
	    }
	    if (isset($sys_settings['tax'])) {
	        $gst_percent = floatval($sys_settings['tax']);
	    }
	}

	$sub_amount = 0;
    $total_cod_amount = 0;
    $total_online_amount = 0;
    $total_delivery_charge_amount = 0;
	if (!empty($id)) {
	    // Sum final_total for the given date range filter
	    $sql_sub_amount = "SELECT
            IFNULL(SUM(o.final_total),0) AS sub_amount,
            IFNULL(SUM(CASE WHEN o.payment_method = 'Cash on Delivery' THEN o.final_total ELSE 0 END),0) AS total_cod_amount,
            IFNULL(SUM(CASE WHEN o.payment_method != 'Cash on Delivery' THEN o.final_total ELSE 0 END),0) AS total_online_amount,
            IFNULL(SUM(o.delivery_charge),0) AS total_delivery_charge_amount
            FROM `orders` o " . $orders_join . " " . $where1;
	    $db->sql($sql_sub_amount);
	    $sub_result = $db->getResult();
	    if (!empty($sub_result)) {
	        $sub_amount = floatval($sub_result[0]['sub_amount']);
            $total_cod_amount = floatval($sub_result[0]['total_cod_amount']);
            $total_online_amount = floatval($sub_result[0]['total_online_amount']);
            $total_delivery_charge_amount = floatval($sub_result[0]['total_delivery_charge_amount']);
	    }
	}

        // Delivery-boy commission information.
        $boy_comm_rate = 5.0;
        if (!empty($id)) {
            $sql_boy_comm = "SELECT commission FROM delivery_boys WHERE id = '$id'";
            $db->sql($sql_boy_comm);
            $boy_comm_res = $db->getResult();

            if (
                !empty($boy_comm_res) &&
                isset($boy_comm_res[0]['commission']) &&
                $boy_comm_res[0]['commission'] !== '' &&
                floatval($boy_comm_res[0]['commission']) > 0
            ) {
                $boy_comm_rate = floatval($boy_comm_res[0]['commission']);
            } else {
                $boy_comm_rate =
                    (isset($config['delivery_boy_commission']) && $config['delivery_boy_commission'] !== '')
                    ? floatval($config['delivery_boy_commission'])
                    : ((isset($config['delivery-boy-bonus-percentage']) && $config['delivery-boy-bonus-percentage'] !== '')
                        ? floatval($config['delivery-boy-bonus-percentage'])
                        : 5.0);
            }
        }

        $delivered_where = !empty($where1) ? $where1 . " AND" : " WHERE";
        $sql_delivered = "SELECT COUNT(o.id) as delivered_count
                          FROM `orders` o " . $orders_join . $delivered_where .
                          " o.active_status = 'delivered'";
        $db->sql($sql_delivered);
        $del_res = $db->getResult();
        $del_count = !empty($del_res)
            ? intval($del_res[0]['delivered_count'])
            : 0;


        $total_commission = $del_count * $boy_comm_rate;

        // Preserve the existing platform fee and GST payout calculation.
        $platform_charges = 0;
        $gst_amount = 0;

        if ($platform_fee_percent > 0) {
            $platform_charges = ($sub_amount * $platform_fee_percent) / 100;
        }
        if ($gst_percent > 0 && $platform_charges > 0) {
            $gst_amount = ($platform_charges * $gst_percent) / 100;
        }

        $total_deduction = $platform_charges + $gst_amount;
        $total_payable = $sub_amount - $total_deduction;

        // Earnings response.
        $response_data['today_earn'] = number_format($today_earn_cod + $today_earn_online, 2, '.', '');
        $response_data['today_earn_cod'] = number_format($today_earn_cod, 2, '.', '');
        $response_data['today_earn_online'] = number_format($today_earn_online, 2, '.', '');
        $response_data['today_delivery_charge'] = number_format($today_delivery_charge ?? 0, 2, '.', '');

        $response_data['today_food_orders'] = (string) ($today_food_orders ?? 0);
        $response_data['today_food_cod'] = number_format($today_food_cod ?? 0, 2, '.', '');
        $response_data['today_food_online'] = number_format($today_food_online ?? 0, 2, '.', '');
        $response_data['today_food_delivery_charge'] = number_format($today_food_delivery_charge ?? 0, 2, '.', '');

        $response_data['today_pickup_orders'] = (string) ($today_pickup_orders ?? 0);
        $response_data['today_pickup_cod'] = number_format($today_pickup_cod ?? 0, 2, '.', '');
        $response_data['today_pickup_online'] = number_format($today_pickup_online ?? 0, 2, '.', '');
        $response_data['today_pickup_delivery_charge'] = number_format($today_pickup_delivery_charge ?? 0, 2, '.', '');

        $response_data['sub_amount'] = number_format($sub_amount, 2, '.', '');
        $response_data['total_cod_amount'] = number_format($total_cod_amount, 2, '.', '');
        $response_data['total_online_amount'] = number_format($total_online_amount, 2, '.', '');
        $response_data['total_delivery_charge_amount'] = number_format($total_delivery_charge_amount, 2, '.', '');
        $response_data['total_commission'] = number_format($total_commission, 2, '.', '');
        $response_data['commission_rate'] = number_format($boy_comm_rate, 2, '.', '');
        $response_data['platform_charges'] = number_format($platform_charges, 2, '.', '');
        $response_data['gst_amount'] = number_format($gst_amount, 2, '.', '');
        $response_data['total_deduction'] = number_format($total_deduction, 2, '.', '');
	$response_data['total_payable'] = number_format($total_payable, 2, '.', '');


	// ================= END TODAY EARNINGS =================

	foreach ($res as $row) {

		$items = $row['items'];
		$items1 = $temp = array();
		$total_amt = 0;

		foreach ($items as $item) {

			if ($item['discounted_price'] != 0) {
				$price = $item['discounted_price'];

			} else {
				$price = $item['price'];
			}



			$temp = array(
				'id' => $item['id'],
				'product_variant_id' => $item['product_variant_id'],
				'name' => $item['name'],
				'unit' => $item['measurement'] . " " . $item['mesurement_unit_name'],
				'product_image' => DOMAIN_URL . $item['image'],
				'price' => number_format((float) $price, 2, '.', ''),
				'quantity' => $item['quantity'],
				'subtotal' => number_format((float) $item['sub_total'], 2, '.', ''),
				'active_status' => $item['active_status']
			);
			$total_amt += $item['sub_total'];
			$items1[] = $temp;
		}

		if ($row['active_status'] == 'received') {
			$active_status = $row['active_status'];
		}
		if ($row['active_status'] == 'processed') {
			$active_status = $row['active_status'];
		}
		if ($row['active_status'] == 'shipped') {
			$active_status = $row['active_status'];
		}
		if ($row['active_status'] == 'delivered') {
			$active_status = $row['active_status'];
		}
		if ($row['active_status'] == 'returned' || $row['active_status'] == 'cancelled') {
			$active_status = $row['active_status'];
		}

		// safe defaults
		$discount_percentage = 0;
		$qty = null;
		$deliver_by = null;

		// only if items exist
		if (!empty($row['items']) && isset($row['items'][0])) {
			$discount_percentage = $row['items'][0]['discount'] ?? 0;
			$qty = $row['items'][0]['quantity'] ?? null;
			$deliver_by = $row['items'][0]['deliver_by'] ?? null;
		}

		$discounted_amount = $row['total'] * $discount_percentage / 100;
		$final_total = $row['total'] - $discounted_amount;
		$discount_in_rupees = floor($row['total'] - $final_total);


		$distance = 0;

		$seller_latitude = $row['seller_latitude'];
		$seller_longitude = $row['seller_longitude'];

		if (isset($_POST['status'])) {

			// STATUS = NEW -> distance between delivery boy (live) and seller (pickup point)
		if ($_POST['status'] == 'new') {
        $distance = calculateDistance(
                $deliveryboylatitude,
                $deliveryboylongtitude,
                $seller_latitude,
                $seller_longitude
        );

        // Phase-1 constraint: only delivery boys within 4 km of seller get to see/accept during Phase 1
        if (isset($row['dispatch_stage']) && $row['dispatch_stage'] == 'phase1') {
                if ($distance > (float)custom_functions::DISPATCH_PHASE1_RADIUS_KM || empty($deliveryboylatitude) || empty($deliveryboylongtitude)) {
                        continue; // Not within 4 km during Phase-1 batch; skip until Phase-2 broadcast
                }
        }
}

			// STATUS = ACTIVE -> distance between delivery boy (live) and customer (drop point)
			// NOTE: previously this used seller_lat/lng -> user_lat/lng, which meant
			// every delivery boy on the same order showed the same distance, regardless
			// of where they actually were. Changed to use the delivery boy's live location.
			if ($_POST['status'] == 'active') {

				$distance = calculateDistance(
					$deliveryboylatitude,
					$deliveryboylongtitude,
					$row['user_latitude'],
					$row['user_longitude']
				);
			}
		} elseif (!empty($deliveryboylatitude) && !empty($deliveryboylongtitude)) {
			// Fallback for any other/unspecified status: still compute delivery boy -> customer distance
			$distance = calculateDistance(
				$deliveryboylatitude,
				$deliveryboylongtitude,
				$row['user_latitude'],
				$row['user_longitude']
			);
		}


		$tempRow['id'] = $row['id'];
		$tempRow['user_id'] = $row['user_id'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['name'] = $row['username'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['seller_name'] = $row['seller_name'];
		$tempRow['seller_company_name'] = $row['seller_company_name'];
		$tempRow['seller_mobile'] = $row['seller_mobile'];
		$tempRow['seller_company_address'] = $row['seller_company_address'];
		$tempRow['seller_image'] = (!empty($row['seller_image'])) ? DOMAIN_URL . 'upload/sellers/' . $row['seller_image'] : '';
		$tempRow['items'] = $items1;
		$tempRow['total'] = number_format((float) $row['total'], 2, '.', '');
		$tempRow['tax'] = $row['tax_amount'] . '(' . $row['tax_percentage'] . '%)';
		$tempRow['promo_discount'] = number_format((float) $row['promo_discount'], 2, '.', '');
		$tempRow['wallet_balance'] = number_format((float) $row['wallet_balance'], 2, '.', '');
		$tempRow['discount'] = $discount_in_rupees . '(' . $discount_percentage . '%)';
		$tempRow['qty'] = $qty;
		$tempRow['final_total'] = ceil($row['final_total']);
		$tempRow['promo_code'] = $row['promo_code'];
		$tempRow['deliver_by'] = $deliver_by;
		$tempRow['payment_method'] = $row['payment_method'];
		$tempRow['payment_request'] = $row['payment_request'];
		$tempRow['address'] = $row['address'];
		$tempRow['notes'] = (isset($row['notes'])) ? $row['notes'] : '';

		$tempRow['seller_latitude'] = $seller_latitude;
		$tempRow['seller_longitude'] = $seller_longitude;


		$tempRow['latitude'] = $row['user_latitude'];
		$tempRow['longitude'] = $row['user_longitude'];

		$tempRow['distance'] = round($distance);

		$tempRow['accept_status'] = $row['accept_status'];
		$tempRow['delivery_time'] = $row['delivery_time'];
		$tempRow['order_date_time'] = !empty($row['date_added']) ? date('d-m-Y h:i A', strtotime($row['date_added'])) : '';
		$tempRow['active_status'] = $active_status;
		$tempRow['wallet_balance'] = number_format((float) $row['wallet_balance'], 2, '.', '');
		$tempRow['date_added'] = date('d-m-Y', strtotime($row['date_added']));

		$rows1[] = $tempRow;

	}
	$response_data['error'] = false;
	$response_data['data'] = $rows1;

	print_r(json_encode($response_data));
	die;
}

function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
	// Use strict emptiness checks instead of empty(), since empty(0) is true in PHP
	// and would wrongly reject valid 0-degree coordinates (equator / prime meridian).
	if (
		$lat1 === '' || $lat1 === null || !is_numeric($lat1) ||
		$lon1 === '' || $lon1 === null || !is_numeric($lon1) ||
		$lat2 === '' || $lat2 === null || !is_numeric($lat2) ||
		$lon2 === '' || $lon2 === null || !is_numeric($lon2)
	) {
		return 0;
	}

	$earth_radius = 6371; // KM
	$dLat = deg2rad($lat2 - $lat1);
	$dLon = deg2rad($lon2 - $lon1);
	$a = sin($dLat / 2) * sin($dLat / 2) +
		cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
		sin($dLon / 2) * sin($dLon / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	$distance = $earth_radius * $c;
	return round($distance, 2); // KM
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['accept_order_delivery_boy'])) {

	if (empty($_POST['id'])) {
		$response['error'] = true;
		$response['message'] = "Id of Delivery boy should be Passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	if (empty($_POST['order_id'])) {
		$response['error'] = true;
		$response['message'] = "Order ID should be Passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	if (empty($_POST['status'])) {
		$response['error'] = true;
		$response['message'] = "Status should be Passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$order_id = $db->escapeString(trim($fn->xss_clean($_POST['order_id'])));
	$status = $db->escapeString(trim($fn->xss_clean($_POST['status'])));
	$sql = "SELECT * FROM delivery_boys	WHERE id = '" . $id . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);
	if ($num == 1) {

		if ($status == "yes") {
			// Atomic first-come-first-served claim. Only the single statement
			// that sees delivery_boy_id = '0' can win, even when two boys tap
			// Accept at the same instant.
			$db->update('orders',
				array('delivery_boy_id' => $id, 'accept_status' => $status, 'dispatch_stage' => 'assigned'),
				"id = '$order_id' AND active_status = 'processed' AND delivery_boy_id = '0'");
			$aff = $db->getResult();
			$affected = (!empty($aff)) ? (int)$aff[0] : 0;
			if ($affected === 1) {
				$response['error'] = false;
				$response['message'] = "Order Accepted Susseccfully";
			} else {
				$db->sql("SELECT delivery_boy_id, active_status FROM orders WHERE id = '$order_id'");
				$res_taken = $db->getResult();
				if (!empty($res_taken) && isset($res_taken[0]['delivery_boy_id']) && (int)$res_taken[0]['delivery_boy_id'] !== 0) {
					$response['error'] = true;
					$response['message'] = "Order already accepted by another partner.";
				} else {
					$response['error'] = true;
					$response['message'] = "Invalid Order ID!";
				}
			}
		} else {
			// Record the rejection so this delivery boy won't see this order again
			$sql_rejected = "UPDATE orders SET rejected_by = IF(rejected_by IS NULL OR rejected_by = '', '$id', CONCAT(rejected_by, ',$id')) WHERE id = '$order_id'";
			$db->sql($sql_rejected);
			$response['error'] = false;
			$response['message'] = "Order Rejected Susseccfully";
		}


	} else {
		$response['error'] = true;
		$response['message'] = "Invalid Delivery Boy ID!";
	}
	print_r(json_encode($response));
	die;
} else {
	$response['error'] = true;
	$response['message'] = "Invalid Call of API!";

}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_parcel_orders'])) {

	/*
	get_parcel_orders
		accesskey:90336
		get_parcel_orders:10        // delivery boy id (caller)
		id:10                       // {optional}
		status:new / active / past  // {optional, default new}
		latitude:12.34              // {optional - delivery boy's live location}
		longtitude:56.78            // {optional}
		offset:0                    // {optional}
		limit:10                    // {optional}
	*/
	$response_data = array();

	$id = (isset($_POST['id']) && !empty(trim($_POST['id'])) && is_numeric($_POST['id'])) ? $db->escapeString(trim($fn->xss_clean($_POST['id']))) : '';

	// For 'new' parcel orders, the delivery boy ID is passed as get_parcel_orders value
	$caller_id = $id;
	if (empty($caller_id) && isset($_POST['get_parcel_orders']) && is_numeric($_POST['get_parcel_orders']) && intval($_POST['get_parcel_orders']) > 1) {
		$caller_id = $db->escapeString(trim($fn->xss_clean($_POST['get_parcel_orders'])));
	}

	// Accept both 'latitude' and correctly-spelled/misspelled longitude keys
	$deliveryboylatitude = (isset($_POST['latitude']) && !empty(trim($_POST['latitude'])) && is_numeric($_POST['latitude'])) ? $db->escapeString(trim($fn->xss_clean($_POST['latitude']))) : '';

	if (isset($_POST['longitude']) && !empty(trim($_POST['longitude'])) && is_numeric($_POST['longitude'])) {
		$deliveryboylongtitude = $db->escapeString(trim($fn->xss_clean($_POST['longitude'])));
	} elseif (isset($_POST['longtitude']) && !empty(trim($_POST['longtitude'])) && is_numeric($_POST['longtitude'])) {
		$deliveryboylongtitude = $db->escapeString(trim($fn->xss_clean($_POST['longtitude'])));
	} else {
		$deliveryboylongtitude = '';
	}

	$offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? (int) $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
	$limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? (int) $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;
	$status = (isset($_POST['status']) && !empty(trim($_POST['status']))) ? $db->escapeString(trim($fn->xss_clean($_POST['status']))) : 'new';

	$boy_id = !empty($id) ? $id : $caller_id;

	if (($status == 'active' || $status == 'past') && empty($boy_id)) {
		$response_data['error'] = true;
		$response_data['message'] = "Delivery Boy ID is required when status is active or past.";
		echo json_encode($response_data);
		die;
	}

	$where = " WHERE 1";
	if ($status == 'new' || $status == '') {
		if (!empty($boy_id)) {
			$sql_check = "SELECT active_status FROM delivery_boys WHERE id='$boy_id'";
			$db->sql($sql_check);
			$res_check = $db->getResult();
			if (!empty($res_check) && $res_check[0]['active_status'] == 'false') {
				$response_data['error'] = true;
				$response_data['message'] = "You are currently offline. Please turn on your active status to receive new parcel orders.";
				$response_data['data'] = array();
				echo json_encode($response_data);
				die;
			}
		}
		$where .= " AND p.status IN ('pending','accepted') AND p.delivery_boy_id = 0";
		// Exclude parcel orders this delivery boy has already rejected
		if (!empty($boy_id)) {
			$where .= " AND (p.rejected_by IS NULL OR p.rejected_by = '' OR NOT FIND_IN_SET('$boy_id', p.rejected_by)) ";
			// Food-only delivery boys should not see parcel orders
			$sql_boy = "SELECT service_type, zone_id FROM delivery_boys WHERE id = '$boy_id'";
			$db->sql($sql_boy);
			$res_boy = $db->getResult();
			$boy_service = (!empty($res_boy) && isset($res_boy[0]['service_type'])) ? $res_boy[0]['service_type'] : 'both';
			$boy_zone_id = (!empty($res_boy) && isset($res_boy[0]['zone_id'])) ? $res_boy[0]['zone_id'] : 0;
			if ($boy_service == 'food') {
				$where .= " AND 1 = 0 ";
			}
			if (!empty($boy_zone_id) && $boy_zone_id > 0) {
			    $where .= " AND p.zone_id = " . $boy_zone_id;
			}
		}
	} else if ($status == 'active') {
		$where .= " AND p.delivery_boy_id = '" . $boy_id . "' AND p.status IN ('accepted','picked')";
	} else if ($status == 'past') {
		$where .= " AND p.delivery_boy_id = '" . $boy_id . "' AND p.status = 'delivered'";
	} else {
		$response_data['error'] = true;
		$response_data['message'] = "Invalid status.";
		echo json_encode($response_data);
		die;
	}

	$sql = "SELECT COUNT(p.id) as total FROM `parcel_requests` p" . $where;
	$db->sql($sql);
	$res = $db->getResult();
	$total = (!empty($res)) ? $res[0]['total'] : 0;

	$sql = "SELECT p.* FROM `parcel_requests` p" . $where . " ORDER BY p.id DESC LIMIT " . $offset . ", " . $limit;
	$db->sql($sql);
	$res = $db->getResult();

	// Opportunistic Phase-2 escalation for parcels (see food block above).
	if ($status == 'new') {
		foreach ($res as $row_dispatch) {
			if (isset($row_dispatch['delivery_boy_id']) && (int)$row_dispatch['delivery_boy_id'] === 0) {
				$fn->escalate_due_dispatch('parcel', (int)$row_dispatch['id']);
			}
		}
	}

	$rows1 = array();
	foreach ($res as $row) {
		$images = (!empty($row['parcel_image'])) ? array_values(array_filter(array_map('trim', explode(',', $row['parcel_image'])))) : array();
		$first_image = (!empty($images)) ? DOMAIN_URL . $images[0] : '';
		$parcel_images = array_map(function($img){ return DOMAIN_URL . $img; }, $images);

		$pickup_lat = $row['pickup_lat'];
		$pickup_lng = $row['pickup_lng'];
		$drop_lat = $row['drop_lat'];
		$drop_lng = $row['drop_lng'];

		$distance = 0;
		if ($status == 'new' || $status == '') {
			// distance between delivery boy (live) and pickup point (sender)
			$distance = calculateDistance($deliveryboylatitude, $deliveryboylongtitude, $pickup_lat, $pickup_lng);
		} else {
			// active/past -> distance between delivery boy (live) and drop point (recipient)
			$distance = calculateDistance($deliveryboylatitude, $deliveryboylongtitude, $drop_lat, $drop_lng);
		}

		$weight = ($row['weight_kg'] != '' && $row['weight_kg'] !== null) ? $row['weight_kg'] . ' kg' : '';

		$items = array(
			array(
				'id' => $row['id'],
				'product_variant_id' => '0',
				'name' => ($row['item_type_name'] != '') ? $row['item_type_name'] : 'Parcel',
				'unit' => ($weight != '') ? $weight : 'Standard',
				'product_image' => $first_image,
				'price' => number_format($row['per_km_price'], 2, '.', ''),
				'quantity' => '1',
				'subtotal' => number_format($row['total_price'], 2, '.', ''),
				'active_status' => $row['status']
			)
		);

		$tempRow = array();
		$tempRow['order_type'] = 'parcel';
		$tempRow['item_type_name'] = $row['item_type_name'];
		$tempRow['weight_kg'] = $weight;
		$tempRow['parcel_image'] = $first_image;
		$tempRow['parcel_images'] = $parcel_images;
		$tempRow['id'] = $row['id'];
		$tempRow['user_id'] = $row['user_id'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['name'] = $row['recipient_name'];
		$tempRow['mobile'] = $row['recipient_phone'];
		$tempRow['seller_name'] = $row['sender_name'];
		$tempRow['seller_company_name'] = $row['sender_name'];
		$tempRow['seller_mobile'] = $row['sender_phone'];
		$tempRow['seller_company_address'] = $row['pickup_location'];
		$tempRow['seller_image'] = $first_image;
		$tempRow['items'] = $items;
		$tempRow['total'] = number_format($row['total_price'], 2, '.', '');
		$tempRow['tax'] = '0(0%)';
		$tempRow['promo_discount'] = '0';
		$tempRow['wallet_balance'] = '0';
		$tempRow['discount'] = '0(0%)';
		$tempRow['qty'] = '1';
		$tempRow['final_total'] = ceil($row['total_price']);
		$tempRow['promo_code'] = '';
		$tempRow['deliver_by'] = '';
		$tempRow['payment_method'] = ($row['payment_status'] == 'paid') ? 'Paid' : 'Not Paid';
		$tempRow['payment_status'] = $row['payment_status'];
		$tempRow['payment_request'] = '0';
		$tempRow['address'] = $row['drop_location'];
		$tempRow['seller_latitude'] = $pickup_lat;
		$tempRow['seller_longitude'] = $pickup_lng;
		$tempRow['latitude'] = $drop_lat;
		$tempRow['longitude'] = $drop_lng;
		$tempRow['distance'] = round($distance);
		$tempRow['accept_status'] = $row['accept_status'];
		$tempRow['delivery_time'] = date('d F Y, l h:i a', strtotime($row['created_at']));
		$tempRow['order_date_time'] = !empty($row['created_at']) ? date('d-m-Y h:i A', strtotime($row['created_at'])) : '';
		$tempRow['active_status'] = $row['status'];
		$tempRow['date_added'] = date('d-m-Y', strtotime($row['created_at']));

		$rows1[] = $tempRow;
	}

	// ================= TODAY EARNINGS (parcels) =================
	// These used to be hardcoded to '0', so a boy who delivered parcels all day
	// still read as earning nothing. Everything below is scoped to this boy,
	// the same way the food block scopes its queries (api-v1.php today_earnings).

	$today_earn_cod = 0;
	$today_earn_online = 0;
	$today_delivery_charge = 0;
	$today_parcel_orders = 0;
	$sub_amount = 0;
	$platform_charges = 0;
	$gst_amount = 0;

	if (!empty($boy_id)) {
		$boy_scope = " WHERE p.delivery_boy_id = '" . $boy_id . "'";

		// Parcels delivered today - this is what the boy actually earned today.
		$sql_today = "
			SELECT
				COUNT(p.id) AS cnt,
				IFNULL(SUM(CASE WHEN p.payment_status = 'paid' THEN p.total_price ELSE 0 END),0) AS total_online,
				IFNULL(SUM(CASE WHEN p.payment_status != 'paid' THEN p.total_price ELSE 0 END),0) AS total_cod,
				IFNULL(SUM(p.base_price + p.per_km_price),0) AS total_delivery_charge
			FROM parcel_requests p" . $boy_scope . "
			AND p.status = 'delivered'
			AND DATE(p.created_at) = CURDATE()
		";
		$db->sql($sql_today);
		$today_result = $db->getResult();
		if (!empty($today_result)) {
			$today_parcel_orders = (int) $today_result[0]['cnt'];
			$today_earn_online = floatval($today_result[0]['total_online']);
			$today_earn_cod = floatval($today_result[0]['total_cod']);
			$today_delivery_charge = floatval($today_result[0]['total_delivery_charge']);
		}

		// Today's parcels regardless of status - mirrors the food block's sub_amount
		// scope so settlement figures line up with what the list shows.
		$sql_sub_amount = "
			SELECT
				IFNULL(SUM(p.total_price),0) AS sub_amount,
				IFNULL(SUM(p.platform_fee),0) AS platform_charges,
				IFNULL(SUM(p.gst),0) AS gst_amount
			FROM parcel_requests p" . $boy_scope . "
			AND DATE(p.created_at) = CURDATE()
		";
		$db->sql($sql_sub_amount);
		$sub_result = $db->getResult();
		if (!empty($sub_result)) {
			$sub_amount = floatval($sub_result[0]['sub_amount']);
			$platform_charges = floatval($sub_result[0]['platform_charges']);
			$gst_amount = floatval($sub_result[0]['gst_amount']);
		}
	}

	$total_deduction = $platform_charges + $gst_amount;
	$total_payable = $sub_amount - $total_deduction;

	$response_data['error'] = false;
	$response_data['data'] = $rows1;
	$response_data['total'] = (string) $total;
	$response_data['today_earn'] = number_format($today_earn_cod + $today_earn_online, 2, '.', '');
	$response_data['today_earn_cod'] = number_format($today_earn_cod, 2, '.', '');
	$response_data['today_earn_online'] = number_format($today_earn_online, 2, '.', '');
	$response_data['today_delivery_charge'] = number_format($today_delivery_charge, 2, '.', '');
	$response_data['today_food_orders'] = '0';
	$response_data['today_food_cod'] = '0';
	$response_data['today_food_online'] = '0';
	$response_data['today_food_delivery_charge'] = '0';
	$response_data['today_pickup_orders'] = '0';
	$response_data['today_pickup_cod'] = '0';
	$response_data['today_pickup_online'] = '0';
	$response_data['today_pickup_delivery_charge'] = '0';
	$response_data['today_parcel_orders'] = (string) $today_parcel_orders;
	$response_data['sub_amount'] = number_format($sub_amount, 2, '.', '');
	$response_data['platform_charges'] = number_format($platform_charges, 2, '.', '');
	$response_data['gst_amount'] = number_format($gst_amount, 2, '.', '');
	$response_data['total_deduction'] = number_format($total_deduction, 2, '.', '');
	$response_data['total_payable'] = number_format($total_payable, 2, '.', '');
	print_r(json_encode($response_data));
	die;
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['accept_parcel_order'])) {

	if (empty($_POST['id'])) {
		$response['error'] = true;
		$response['message'] = "Id of Delivery boy should be Passed!";
		print_r(json_encode($response));
		die;
	}
	if (empty($_POST['order_id'])) {
		$response['error'] = true;
		$response['message'] = "Parcel Order ID should be Passed!";
		print_r(json_encode($response));
		die;
	}
	if (empty($_POST['status'])) {
		$response['error'] = true;
		$response['message'] = "Status should be Passed!";
		print_r(json_encode($response));
		die;
	}
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$order_id = $db->escapeString(trim($fn->xss_clean($_POST['order_id'])));
	$status = $db->escapeString(trim($fn->xss_clean($_POST['status'])));

	$sql = "SELECT * FROM delivery_boys WHERE id = '" . $id . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num = $db->numRows($res);
	if ($num == 1) {
		if ($status == "yes") {
			// Atomic first-come-first-served claim; see accept_order_delivery_boy.
			$db->update('parcel_requests',
				array('delivery_boy_id' => $id, 'accept_status' => $status, 'status' => 'accepted', 'dispatch_stage' => 'assigned'),
				"id = '$order_id' AND status != 'delivered' AND status != 'cancelled' AND delivery_boy_id = '0'");
			$aff = $db->getResult();
			$affected = (!empty($aff)) ? (int)$aff[0] : 0;
			if ($affected === 1) {
				$response['error'] = false;
				$response['message'] = "Parcel Order Accepted Successfully";
			} else {
				$db->sql("SELECT delivery_boy_id FROM parcel_requests WHERE id = '$order_id'");
				$res_taken = $db->getResult();
				if (!empty($res_taken) && isset($res_taken[0]['delivery_boy_id']) && (int)$res_taken[0]['delivery_boy_id'] !== 0) {
					$response['error'] = true;
					$response['message'] = "Order already accepted by another partner.";
				} else {
					$response['error'] = true;
					$response['message'] = "Invalid Parcel Order ID!";
				}
			}
		} else {
			// Record the rejection so this delivery boy won't see this parcel order again
			$sql_rejected = "UPDATE parcel_requests SET rejected_by = IF(rejected_by IS NULL OR rejected_by = '', '$id', CONCAT(rejected_by, ',$id')) WHERE id = '$order_id'";
			$db->sql($sql_rejected);
			$response['error'] = false;
			$response['message'] = "Parcel Order Rejected Successfully";
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Invalid Delivery Boy ID!";
	}
	print_r(json_encode($response));
	die;
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_parcel_order_status']) && isset($_POST['order_id'])) {

	$id = $db->escapeString(trim($fn->xss_clean($_POST['order_id'])));
	$postStatus = $db->escapeString(trim($fn->xss_clean($_POST['status'])));
	$delivery_boy_id = (isset($_POST['delivery_boy_id']) && !empty(trim($_POST['delivery_boy_id']))) ? $db->escapeString(trim($fn->xss_clean($_POST['delivery_boy_id']))) : '0';
	$otp = (isset($_POST['otp'])) ? trim($db->escapeString($fn->xss_clean($_POST['otp']))) : '';

	if (!in_array($postStatus, array('picked', 'delivered'))) {
		$response['error'] = true;
		$response['message'] = "Invalid parcel status!";
		print_r(json_encode($response));
		die;
	}

	$sql = "SELECT * FROM parcel_requests WHERE id = '$id'";
	$db->sql($sql);
	$res = $db->getResult();
	if (count($res) != 1) {
		$response['error'] = true;
		$response['message'] = "Invalid parcel order ID!";
		print_r(json_encode($response));
		die;
	}
	$parcel = $res[0];

	if ($parcel['status'] == 'cancelled') {
		$response['error'] = true;
		$response['message'] = "This parcel order has been cancelled!";
		print_r(json_encode($response));
		die;
	}

	// Auto assign the delivery boy if the order was not already accepted
	if ($parcel['delivery_boy_id'] == 0 && $delivery_boy_id != '0') {
		$sql = "UPDATE parcel_requests SET delivery_boy_id = '$delivery_boy_id' WHERE id = '$id'";
		$db->sql($sql);
	}

	if ($postStatus == 'delivered') {
		// OTP verification before marking as delivered
		if (empty($otp)) {
			$response['error'] = true;
			$response['message'] = "OTP is required to mark this parcel as delivered.";
			print_r(json_encode($response));
			die;
		}
		if (empty($parcel['otp'])) {
			$response['error'] = true;
			$response['message'] = "No OTP found for this parcel request.";
			print_r(json_encode($response));
			die;
		}
		if ((string)$otp !== (string)$parcel['otp']) {
			$response['error'] = true;
			$response['message'] = "Invalid OTP. Please ask the customer for the correct OTP.";
			print_r(json_encode($response));
			die;
		}

		$sql = "UPDATE parcel_requests SET status = 'delivered' WHERE id = '$id'";
		$db->sql($sql);

		// Credit delivery boy commission
		$boy_id = ($delivery_boy_id != '0') ? $delivery_boy_id : $parcel['delivery_boy_id'];
		if ($boy_id != '0') {
			$sql = "SELECT bonus,name FROM delivery_boys WHERE id='" . $boy_id . "'";
			$db->sql($sql);
			$res_boy = $db->getResult();
			if (!empty($res_boy)) {
				$reward = floatval($parcel['total_price']) / 100 * floatval($res_boy[0]['bonus']);
				$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id='" . $boy_id . "'";
				$db->sql($sql);
				$fn->add_delivery_boy_commission($boy_id, 'credit', $reward, 'Parcel Delivery Boy Commission.');
				$message_delivery_boy = "Hello, Dear " . ucwords($res_boy[0]['name']) . ", Your Parcel Delivery Commission of " . ceil($reward) . " has been credited for Parcel #" . $id . ".";
				$fn->send_notification_to_delivery_boy($boy_id, "Your parcel delivery commission credited", $message_delivery_boy, 'delivery_boys', $id, 'parcel');
				$fn->store_delivery_boy_notification($boy_id, $id, "Parcel Commission Credited", $message_delivery_boy, 'parcel');
			}
		}

		// Notify the user who placed the parcel request
		$message_user = "Hello, Your parcel request #" . $id . " has been delivered successfully to " . $parcel['recipient_name'] . ". Thank you for using our service.";
		$fn->send_order_update_notification($parcel['user_id'], "Your parcel has been Delivered", $message_user, 'order');
	} else {
		$sql = "UPDATE parcel_requests SET status = '$postStatus' WHERE id = '$id'";
		$db->sql($sql);

		$message_delivery_boy = "Parcel order #" . $id . " has been updated to " . ucwords($postStatus) . ". Please continue with the delivery.";
		$fn->send_notification_to_delivery_boy($delivery_boy_id, "Parcel status updated", $message_delivery_boy, 'delivery_boys', $id, 'parcel');

		// Notify the user who placed the parcel request when the parcel is picked up
		if ($postStatus == 'picked') {
			$message_user = "Hello, Your parcel request #" . $id . " has been picked up by the delivery boy. Please be ready to receive the delivery.";
			$fn->send_order_update_notification($parcel['user_id'], "Your parcel has been Picked up", $message_user, 'order');
		}
	}

	$response['error'] = false;
	$response['message'] = "Parcel order updated successfully.";
	print_r(json_encode($response));
	die;
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_fund_transfers'])) {

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

	$json_response = array();
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$where = '';
	$offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
	$limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

	$sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
	$order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
	if (isset($_POST['search']) && !empty($_POST['search'])) {
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where f.`id` like '%" . $search . "%' OR d.`name` like '%" . $search . "%' OR f.`message` like '%" . $search . "%' OR d.`mobile` like '%" . $search . "%' OR d.`address` like '%" . $search . "%' OR f.`opening_balance` like '%" . $search . "%' OR f.`closing_balance` like '%" . $search . "%' OR d.`balance` like '%" . $search . "%' OR f.`date_created` like '%" . $search . "%'";
	}

	if (empty($where)) {
		$where .= " WHERE delivery_boy_id = " . $id;
	} else {
		$where .= " AND delivery_boy_id = " . $id;
	}

	$sql = "SELECT COUNT(f.id) as total FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id" . $where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach ($res as $row)
		$total = $row['total'];
	$sql = "SELECT f.*,d.name,d.mobile,d.address FROM `fund_transfers` f JOIN `delivery_boys` d ON f.delivery_boy_id=d.id " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
	$db->sql($sql);
	$res = $db->getResult();

	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach ($res as $row) {
		$tempRow['id'] = $row['id'];
		$tempRow['name'] = $row['name'];
		$tempRow['mobile'] = $row['mobile'];
		$tempRow['address'] = $row['address'];
		$tempRow['delivery_boy_id'] = $row['delivery_boy_id'];
		$tempRow['type'] = $row['type'];
		$tempRow['amount'] = number_format((float) $row['amount'], 2, '.', '');
		$tempRow['opening_balance'] = number_format((float) $row['opening_balance'], 2, '.', '');
		$tempRow['closing_balance'] = number_format((float) $row['closing_balance'], 2, '.', '');
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

if (isset($_POST['update_delivery_boy_profile'])) {

	$json_response = array();
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$name = $db->escapeString(trim($fn->xss_clean($_POST['name'])));
	$address = $db->escapeString(trim($_POST['address']));

	$old_password = (isset($_POST['old_password']) && !empty(trim($_POST['old_password']))) ? $db->escapeString(trim($fn->xss_clean($_POST['old_password']))) : "";
	$update_password = (isset($_POST['update_password']) && !empty(trim($_POST['update_password']))) ? $db->escapeString(trim($fn->xss_clean($_POST['update_password']))) : "";
	$confirm_password = (isset($_POST['confirm_password']) && !empty(trim($_POST['confirm_password']))) ? $db->escapeString(trim($fn->xss_clean($_POST['confirm_password']))) : "";

	$change_password = false;

	if (!isset($_POST['id']) || empty(trim($_POST['id'])) || !is_numeric($_POST['id'])) {
		$json_response['error'] = true;
		$json_response['message'] = "Invalid Id of Delivery Boy";
		print_r(json_encode($json_response));
		return false;
	}

	$sql = "SELECT * FROM delivery_boys WHERE id='$id'";
	$db->sql($sql);
	$res_id = $db->getResult();
	$num = $db->numRows($res_id);

	if ($num != 1) {
		$json_response['error'] = true;
		$json_response['message'] = "Delivery Boy is not Registered.";
		print_r(json_encode($json_response));
		return false;
	}

	if ((!empty($confirm_password) || !empty($update_password)) && empty($old_password)) {
		$json_response['error'] = true;
		$json_response['message'] = "Please enter old password.";
		print_r(json_encode($json_response));
		return false;
	}

	if ((!empty($confirm_password) || !empty($update_password)) && ($update_password != $confirm_password)) {
		$json_response['error'] = true;
		$json_response['message'] = "Password and Confirm Password mismatched.";
		print_r(json_encode($json_response));
		return false;
	}

	if (!empty($confirm_password) && !empty($update_password) && !empty($old_password)) {
		$old_password_hashed = md5($old_password);
		$sql = "SELECT password FROM delivery_boys WHERE id='$id' AND password='$old_password_hashed'";
		$db->sql($sql);
		$res = $db->getResult();

		if (empty($res)) {
			$json_response['error'] = true;
			$json_response['message'] = "Old password mismatched.";
			print_r(json_encode($json_response));
			return false;
		}
		$change_password = true;
		$confirm_password = md5($confirm_password);
	}

	$sql = "UPDATE delivery_boys SET name='$name', address='$address'";
	if ($change_password) {
		$sql .= ", password='$confirm_password'";
	}
	$sql .= " WHERE id='$id'";

	if ($db->sql($sql)) {
		$json_response['error'] = false;
		$json_response['message'] = "Information Updated Successfully.";
		if ($change_password)
			$json_response['message'] .= " and password also updated successfully.";
	} else {
		$json_response['error'] = true;
		$json_response['message'] = "Some Error Occurred! Please Try Again.";
	}

	print_r(json_encode($json_response));
}


if (isset($_POST['update_delivery_boy_profile_image'])) {
	$json_response = array();

	if (!isset($_POST['id']) || empty(trim($_POST['id'])) || !is_numeric($_POST['id'])) {
		$json_response['error'] = true;
		$json_response['message'] = "Invalid Id of Delivery Boy";
		print_r(json_encode($json_response));
		return false;
	}

	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));

	$sql = "SELECT id FROM delivery_boys WHERE id='$id'";
	$db->sql($sql);
	$res = $db->getResult();
	if (empty($res)) {
		$json_response['error'] = true;
		$json_response['message'] = "Delivery Boy is not Registered.";
		print_r(json_encode($json_response));
		return false;
	}

	if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
		$json_response['error'] = true;
		$json_response['message'] = "Please select an image file to upload";
		print_r(json_encode($json_response));
		return false;
	}

	// 2MB size limit
	$max_size = 2 * 1024 * 1024;
	if ($_FILES['image']['size'] > $max_size) {
		$json_response['error'] = true;
		$json_response['message'] = "Image size must be less than 2MB";
		print_r(json_encode($json_response));
		return false;
	}

	// Validate image type
	$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
	$file_mime = mime_content_type($_FILES['image']['tmp_name']);
	if (!in_array($file_mime, $allowed_types)) {
		$json_response['error'] = true;
		$json_response['message'] = "Only JPG, PNG, or WEBP images are allowed";
		print_r(json_encode($json_response));
		return false;
	}

	// Build upload path
	$upload_dir = '../../upload/delivery_boys/';
	if (!is_dir($upload_dir)) {
		mkdir($upload_dir, 0755, true);
	}

	$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
	$filename = 'db_' . $id . '_' . time() . '.' . $ext;
	$upload_path = $upload_dir . $filename;
	$db_path     = 'upload/delivery_boys/' . $filename;

	if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
		$sql_update = "UPDATE delivery_boys SET profile='$db_path' WHERE id='$id'";
		$db->sql($sql_update);
		$json_response['error']   = false;
		$json_response['message'] = "Profile image updated successfully.";
		$json_response['image']   = DOMAIN_URL . $db_path;
	} else {
		$json_response['error']   = true;
		$json_response['message'] = "Failed to upload image. Please try again.";
	}

	print_r(json_encode($json_response));
	die;
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['update_order_status']) && isset($_POST['order_id'])) {

	/*
	6.update_order_status
		accesskey:90336
		update_order_status:1
		id:26
		status:cancelled
		delivery_boy_id:40        // {optional}
	*/
	$id = $db->escapeString($fn->xss_clean($_POST['order_id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));
	$delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));

	$sql = "SELECT user_id,delivery_boy_id,status FROM `orders` where id=$id";
	$db->sql($sql);
	$res_delivery_boy_id = $db->getResult();

	if (($res_delivery_boy_id[0]['delivery_boy_id'] == 0) || ($res_delivery_boy_id[0]['delivery_boy_id'] != $delivery_boy_id && $res_delivery_boy_id[0]['status'] != 'cancelled')) {
		$sql = "UPDATE orders SET `delivery_boy_id`='" . $delivery_boy_id . "' WHERE id=" . $id;
		$db->sql($sql);
		$sql_get_name = "select name from delivery_boys where id='$delivery_boy_id'";
		$db->sql($sql_get_name);
		$delivery_boy_name = $db->getResult();
		$message_delivery_boy = "Hello, Dear " . ucwords($delivery_boy_name[0]['name']) . ", You have new order to deliver. Here is your order ID : #" . $id . ". Please take a note of it.";
		$fn->send_notification_to_delivery_boy($delivery_boy_id, "Your new order has been " . ucwords($postStatus), $message_delivery_boy, 'delivery_boys', $id);
		// $fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your new order has been ".ucwords($postStatus),$message_delivery_boy,'order_reward');
	}
	$sql = "SELECT COUNT(id) as cancelled FROM `orders` WHERE id='" . $id . "' && (active_status LIKE '%cancelled%' OR active_status LIKE '%returned%')";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if ($res_cancelled[0]['cancelled'] > 0) {
		$response['error'] = true;
		$response['message'] = 'Could not update order status!';
		print_r(json_encode($response));
		return false;
	}
	$sql = "select user_id,payment_method,wallet_balance,total,delivery_charge,tax_amount,status from orders where id=" . $id;
	$db->sql($sql);
	$res = $db->getResult();
	$order_user_id = $res[0]['user_id'];
	$sql = "SELECT sub_total FROM order_items WHERE order_id=" . $id;
	$db->sql($sql);
	$res_query = $db->getResult();

	$sql = "SELECT COUNT(id) as total FROM `orders` WHERE user_id=" . $res[0]['user_id'] . " && status LIKE '%delivered%'";
	$db->sql($sql);
	$res_count = $db->getResult();

	$sql = "SELECT * FROM `users` WHERE id=" . $res[0]['user_id'];
	$db->sql($sql);
	$res_user = $db->getResult();
	if (!empty($res)) {
		$status = json_decode($res[0]['status']);
		$user_id = $res[0]['user_id'];
		foreach ($status as $each) {
			if (in_array($postStatus, $each)) {
				$response['error'] = true;
				$response['message'] = 'Delivery Boy updated, But order status not due to duplicate status!';
				print_r(json_encode($response));
				return false;
			}
		}
		if ($postStatus == 'cancelled' || $postStatus == 'returned') {
			$sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE `order_id`=' . $id;
			$db->sql($sql);
			$res_oi = $db->getResult();
			for ($i = 0; $i < count($res_oi); $i++) {
				if ($res_oi[$i]['type'] == 'packet') {
					$sql = "UPDATE product_variant SET stock = stock + " . $res_oi[$i]['quantity'] . " WHERE id='" . $res_oi[$i]['product_variant_id'] . "'";
					$db->sql($sql);
					$sql = "select stock from product_variant where id=" . $res_oi[0]['product_variant_id'];
					$db->sql($sql);
					$res_stock = $db->getResult();
					if ($res_stock[0]['stock'] > 0) {
						$sql = "UPDATE product_variant set serve_for='Available' WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
						$db->sql($sql);
					}
				} else {
					if ($res_oi[$i]['measurement_unit_id'] != $res_oi[$i]['stock_unit_id']) {
						$stock = $fn->convert_to_parent($res_oi[$i]['measurement'], $res_oi[$i]['measurement_unit_id']);
						$stock = $stock * $res_oi[$i]['quantity'];
						$sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[$i]['product_id'] . "'";
						$db->sql($sql);
					} else {
						$stock = $res_oi[$i]['measurement'] * $res_oi[$i]['quantity'];
						$sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[$i]['product_id'] . "'";
						$db->sql($sql);
					}
					$sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
					$db->sql($sql);
					$res_stck = $db->getResult();
					if ($res_stck[0]['stock'] > 0) {
						$sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='" . $res_oi[0]['product_id'] . "'";
						$db->sql($sql);
					}
				}
			}
			if ($res[0]['payment_method'] != 'cod' && $res[0]['payment_method'] != 'COD') {
				$user_id = $res[0]['user_id'];
				$total = $res[0]['total'] + $res[0]['delivery_charge'] + $res[0]['tax_amount'];
				$user_wallet_balance = $fn->get_wallet_balance($user_id);
				$new_balance = $user_wallet_balance + $total;
				$fn->update_wallet_balance($new_balance, $user_id);
				$wallet_txn_id = $fn->add_wallet_transaction($user_id, 'credit', $sub_total, 'Balance credited against item cancellation.');
			} else {
				if ($res[0]['wallet_balance'] != 0) {
					$user_id = $res[0]['user_id'];
					$user_wallet_balance = $fn->get_wallet_balance($user_id);
					$new_balance = ($user_wallet_balance + $res[0]['wallet_balance']);
					$fn->update_wallet_balance($new_balance, $user_id);
					$wallet_txn_id = $fn->add_wallet_transaction($user_id, 'credit', $sub_total, 'Balance credited against item cancellation.');
				}
			}
		}

		if ($postStatus == 'delivered') {
			$sql = "SELECT delivery_boy_id,final_total FROM orders WHERE id=" . $id;
			$db->sql($sql);
			$res_boy = $db->getResult();
			if ($res_boy[0]['delivery_boy_id'] != 0) {
				$sql = "SELECT bonus,name FROM delivery_boys WHERE id=" . $res_boy[0]['delivery_boy_id'];
				$db->sql($sql);
				$res_bonus = $db->getResult();
				$reward = $res_boy[0]['final_total'] / 100 * $res_bonus[0]['bonus'];
				$sql = "UPDATE delivery_boys SET balance = balance + ceil($reward) WHERE id=" . $res_boy[0]['delivery_boy_id'];
				$db->sql($sql);
				$comission = $fn->add_delivery_boy_commission($delivery_boy_id, 'credit', $reward, $message = 'Order Delivery Boy Commission.');

				$sql = "SELECT value FROM `settings` WHERE variable='currency'";
				$db->sql($sql);
				$currency = $db->getResult();
				$message_delivery_boy = "Hello, Dear " . ucwords($res_bonus[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your Commission of" . $reward . " is credited. Please take a note of it.";
				$fn->send_notification_to_delivery_boy($delivery_boy_id, "Your commission " . $reward . " " . $currency[0]['value'] . " has been credited", "$message_delivery_boy", 'delivery_boys', $id);
				//$fn->store_delivery_boy_notification($delivery_boy_id,$id,"Your commission ".$reward." ".$currency[0]['value']." has been credited",$message_delivery_boy,'order_reward');

			}

			if ($config['is-refer-earn-on'] == 1) {
				if ($res_boy[0]['final_total'] >= $config['min-refer-earn-order-amount']) {
					if ($res_count[0]['total'] == 0) {
						if ($res_user[0]['friends_code'] != '') {
							if ($config['refer-earn-method'] == 'percentage') {
								$percentage = $config['refer-earn-bonus'];
								$bonus_amount = $res_boy[0]['final_total'] / 100 * $percentage;
								if ($bonus_amount > $config['max-refer-earn-amount']) {
									$bonus_amount = $config['max-refer-earn-amount'];
								}
							} else {
								$bonus_amount = $config['refer-earn-bonus'];
							}
							$sql = "SELECT name,friends_code FROM users WHERE id=" . $order_user_id;
							$db->sql($sql);
							$res_data = $db->getResult();

							$sql = " select id from `users` where `referral_code` = '" . $res_data[0]['friends_code'] . "'";
							$db->sql($sql);
							$friend_user = $db->getResult();
							if (!empty($friend_user))
								$fn->add_wallet_transaction($friend_user[0]['id'], 'credit', floor($bonus_amount), 'Refer & Earn Bonus on first order by ' . ucwords($res_data[0]['name']));

							$sql = "UPDATE users SET balance = balance + floor($bonus_amount) WHERE referral_code='" . $res_data[0]['friends_code'] . "'";
							$db->sql($sql);
						}
					}
				}
			}
		}

		$temp = [];
		foreach ($status as $s) {
			array_push($temp, $s[0]);
		}
		$sql = "SELECT id,active_status FROM order_items WHERE order_id=" . $id;
		$db->sql($sql);
		$result = $db->getResult();
		if ($postStatus == 'cancelled') {
			if (!in_array('cancelled', $temp)) {
				$status[] = array('cancelled', date("d-m-Y h:i:sa"));
				$data = array(
					'status' => $db->escapeString(json_encode($status)),
				);
			}
			$db->update('orders', $data, 'id=' . $id);
			foreach ($result as $item) {
				if ($item['active_status'] != 'cancelled') {
					$item_data = array(
						'status' => $db->escapeString(json_encode($status)),
						'active_status' => 'cancelled'
					);
					$db->update('order_items', $item_data, 'id=' . $item['id']);
				}
			}
		}
		if ($postStatus == 'processed') {
			if (!in_array('processed', $temp)) {
				$status[] = array('processed', date("d-m-Y h:i:sa"));
				$data = array(
					'status' => $db->escapeString(json_encode($status))
				);
			}
			$db->update('orders', $data, 'id=' . $id);
			foreach ($result as $item) {
				$item_data = array(
					'status' => $db->escapeString(json_encode($status)),
					'active_status' => 'processed'
				);
				if ($item['active_status'] != 'cancelled') {
					$db->update('order_items', $item_data, 'id=' . $item['id']);
				}
			}
		}
		if ($postStatus == 'shipped') {
			if (!in_array('processed', $temp)) {
				$status[] = array('processed', date("d-m-Y h:i:sa"));
				$data = array('status' => $db->escapeString(json_encode($status)));
			}
			if (!in_array('shipped', $temp)) {
				$status[] = array('shipped', date("d-m-Y h:i:sa"));
				$data = array('status' => $db->escapeString(json_encode($status)));
			}
			$db->update('orders', $data, 'id=' . $id);
			foreach ($result as $item) {
				$item_data = array(
					'status' => $db->escapeString(json_encode($status)),
					'active_status' => 'shipped'
				);
				if ($item['active_status'] != 'cancelled') {
					$db->update('order_items', $item_data, 'id=' . $item['id']);
				}
			}
		}
		if ($postStatus == 'delivered') {
			if (!in_array('processed', $temp)) {
				$status[] = array('processed', date("d-m-Y h:i:sa"));
				$data = array('status' => $db->escapeString(json_encode($status)));
			}
			if (!in_array('shipped', $temp)) {
				$status[] = array('shipped', date("d-m-Y h:i:sa"));
				$data = array('status' => $db->escapeString(json_encode($status)));
			}
			if (!in_array('delivered', $temp)) {
				$status[] = array('delivered', date("d-m-Y h:i:sa"));
				$data = array('status' => $db->escapeString(json_encode($status)));
			}
			$db->update('orders', $data, 'id=' . $id);
			$item_data = array(
				'status' => $db->escapeString(json_encode($status)),
				'active_status' => 'delivered'
			);
			foreach ($result as $item) {
				if ($item['active_status'] != 'cancelled') {
					$db->update('order_items', $item_data, 'id=' . $item['id']);
				}
			}
		}
		$i = sizeof($status);
		$currentStatus = $status[$i - 1][0];
		$final_status = array(
			'active_status' => $currentStatus
		);
		if ($db->update('orders', $final_status, 'id=' . $id)) {
			$response['error'] = false;
			$response['message'] = $postStatus == 'cancelled' ? "Order has been cancelled!" : "Order updated successfully.";
			$res = $db->getResult();
			$sql = "select name,email,mobile,country_code from `users` where id=" . $user_id;
			$db->sql($sql);
			$res_user = $db->getResult();

			$to = $res_user[0]['email'];
			$mobile = $res_user[0]['mobile'];
			$country_code = $res_user[0]['country_code'];
			$subject = "Your order has been " . ucwords($postStatus);
			$message = "Hello, Dear " . ucwords($res_user[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
			$message .= "Thank you for using our services!You will receive future updates on your order via Email!";
			$fn->send_order_update_notification($user_id, "Your order has been " . ucwords($postStatus), $message, 'order');
			// send_email($to,$subject,$message);

			$message = "Hello, Dear " . ucwords($res_user[0]['name']) . ", Here is the new update on your order for the order ID : #" . $id . ". Your order has been " . ucwords($postStatus) . ". Please take a note of it.";
			$message .= "Thank you for using our services! Contact us for more information";
			//send_email($to,$subject,$message);
			print_r(json_encode($response));
		} else {
			$response['error'] = true;
			$response['message'] = "Delivery Boy updated, But Could not update order status. Try again!";
			print_r(json_encode($response));
		}
	} else {
		$response['error'] = true;
		$response['message'] = "Sorry Invalid order ID";
		print_r(json_encode($response));
	}
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['delivery_boy_forgot_password']) && isset($_POST['mobile'])) {


	if (empty($_POST['password'])) {
		$response['error'] = true;
		$response['message'] = "Password should be filled!";
		print_r(json_encode($response));
		return false;
		exit();
	}

	if (empty($_POST['mobile'])) {
		$response['error'] = true;
		$response['message'] = "Mobile Number id not passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	//$id = $db->escapeString($_POST['id']);
	$mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));
	$password = md5($db->escapeString($fn->xss_clean($_POST['password'])));

	$sql = "SELECT mobile from delivery_boys where mobile='$mobile'";
	$db->sql($sql);
	$res_mobile = $db->getResult();

	if (isset($res_mobile[0]['mobile']) && $res_mobile[0]['mobile'] == $mobile) {
		$sql_update = "UPDATE `delivery_boys` SET `password`='$password' WHERE `mobile`='$mobile'";
		$db->sql($sql_update);
		$response["error"] = false;
		$response["message"] = "Password updated successfully";
	} else {
		$response["error"] = true;
		$response["message"] = "Mobile number id not Registered!";
	}
	print_r(json_encode($response));
}

/*
---------------------------------------------------------------------------------------------------------
*/

if (isset($_POST['get_notifications'])) {

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

	$json_response = array();
	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	$where = '';
	$offset = (isset($_POST['offset']) && !empty(trim($_POST['offset'])) && is_numeric($_POST['offset'])) ? $db->escapeString(trim($fn->xss_clean($_POST['offset']))) : 0;
	$limit = (isset($_POST['limit']) && !empty(trim($_POST['limit'])) && is_numeric($_POST['limit'])) ? $db->escapeString(trim($fn->xss_clean($_POST['limit']))) : 10;

	$sort = (isset($_POST['sort']) && !empty(trim($_POST['sort']))) ? $db->escapeString(trim($fn->xss_clean($_POST['sort']))) : 'id';
	$order = (isset($_POST['order']) && !empty(trim($_POST['order']))) ? $db->escapeString(trim($fn->xss_clean($_POST['order']))) : 'DESC';
	if (isset($_POST['search']) && !empty($_POST['search'])) {
		$search = $db->escapeString(trim($fn->xss_clean($_POST['search'])));
		$where = " Where `id` like '%" . $search . "%' OR `title` like '%" . $search . "%' OR `message` like '%" . $search . "%' OR `type` like '%" . $search . "%' OR `date_created` like '%" . $search . "%'  ";
	}

	if (empty($where)) {
		$where .= " WHERE delivery_boy_id = " . $id;
	} else {
		$where .= " AND delivery_boy_id = " . $id;
	}

	$sql = "SELECT COUNT(id) as total FROM `delivery_boy_notifications` " . $where;
	$db->sql($sql);
	$res = $db->getResult();
	foreach ($res as $row)
		$total = $row['total'];
	$sql = "SELECT * FROM `delivery_boy_notifications` " . $where . " ORDER BY " . $sort . " " . $order . " LIMIT " . $offset . ", " . $limit;
	$db->sql($sql);
	$res = $db->getResult();

	$json_response['total'] = $total;
	$rows = array();
	$tempRow = array();
	foreach ($res as $row) {
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

if (isset($_POST['update_delivery_boy_fcm_id'])) {
	/*
   9.update_delivery_boy_fcm_id
	   accesskey:90336
	   id:114
	   fcm_id:YOUR_FCM_ID
	   update_delivery_boy_fcm_id:1
   */

	if (empty($_POST['fcm_id'])) {
		$response['error'] = true;
		$response['message'] = "Please pass the fcm_id!";
		print_r(json_encode($response));
		return false;
		exit();
	}

	$id = $db->escapeString(trim($fn->xss_clean($_POST['id'])));
	if (isset($_POST['fcm_id']) && !empty($_POST['fcm_id'])) {
		$fcm_id = $_POST['fcm_id'];
		$sql1 = "update delivery_boys set `fcm_id` ='" . $fcm_id . "' where id = '" . $id . "'";
		if ($db->sql($sql1)) {
			$response['error'] = false;
			$response['message'] = "Delivery Boy fcm_id Updeted Successfully.";
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

if (isset($_POST['check_delivery_boy_by_mobile']) && isset($_POST['mobile'])) {

	if (empty($_POST['mobile'])) {
		$response['error'] = true;
		$response['message'] = "Mobile Number id not passed!";
		print_r(json_encode($response));
		return false;
		exit();
	}
	//$id = $db->escapeString($_POST['id']);
	$mobile = $db->escapeString(trim($fn->xss_clean($_POST['mobile'])));

	$sql = "SELECT mobile from delivery_boys where mobile='$mobile'";
	$db->sql($sql);
	$res_mobile = $db->getResult();

	if (isset($res_mobile[0]['mobile']) && $res_mobile[0]['mobile'] == $mobile) {
		$response["error"] = false;
		$response["message"] = "Mobile number is Registered.";
	} else {
		$response["error"] = true;
		$response["message"] = "Mobile number is not Registered!";
	}

	print_r(json_encode($response));
}


if (isset($_POST['update_order_item_status']) && isset($_POST['order_item_id'])) {
	$order_item_id = $db->escapeString($fn->xss_clean($_POST['order_item_id']));
	$order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
	$postStatus = $db->escapeString($fn->xss_clean($_POST['status']));

	$sql = "SELECT COUNT(id) as cancelled FROM `order_items` WHERE id=" . $order_item_id . " && status LIKE '%$postStatus%'";
	$db->sql($sql);
	$res_cancelled = $db->getResult();
	if ($res_cancelled[0]['cancelled'] > 0) {
		$response['error'] = true;
		$response['message'] = 'Could not update order status. Item is already ' . ucwords($postStatus) . '!';
		print_r(json_encode($response));
		return false;
	}

	$sql = "SELECT user_id,status,active_status,sub_total FROM order_items WHERE id =" . $order_item_id;
	$db->sql($sql);
	$result = $db->getResult();

	if (!empty($result)) {
		$status = json_decode($result[0]['status']);
		$active_status = $result[0]['active_status'];
		if ($active_status == 'delivered') {
			$response['error'] = true;
			$response['message'] = 'Order cancel Not Allowed After delivered';
			$response['subtotal'] = 0;
			print_r(json_encode($response));
			return false;
		}
		if ($postStatus == 'cancelled') {
			$sql = 'SELECT final_total,total,user_id,payment_method,wallet_balance,delivery_charge,tax_amount,status FROM orders WHERE id=' . $order_id;
			$db->sql($sql);
			$res_order = $db->getResult();
			//print_r($res_order[0]['total']);die;
			$sql = 'SELECT oi.`product_variant_id`,oi.`quantity`,oi.`discounted_price`,oi.`price`,pv.`product_id`,pv.`type`,pv.`stock`,pv.`stock_unit_id`,pv.`measurement`,pv.`measurement_unit_id` FROM `order_items` oi join `product_variant` pv on pv.id = oi.product_variant_id WHERE oi.`id`=' . $order_item_id;
			$db->sql($sql);
			$res_oi = $db->getResult();
			$price = $res_oi[0]['discounted_price'] == 0 ? $res_oi[0]['price'] : $res_oi[0]['discounted_price'];
			$total = $res_order[0]['total'];
			$final_total = $res_order[0]['final_total'];
			$delivery_charge = $res_order[0]['delivery_charge'];
			// echo $total - $price;die;
			if ($total - $price >= 0) {
				$sql_total = "update orders set total=$total-$price where id=" . $order_id;
				$db->sql($sql_total);
			}
			$sql = "select total from orders where id=" . $order_id;
			$db->sql($sql);
			$res_total = $db->getResult();
			$total = $res_total[0]['total'];

			if ($total < $config['min_amount']) {
				if ($delivery_charge == 0) {
					$dchrg = $config['delivery_charge'];
					$sql_delivery_chrg = "update orders set delivery_charge=$dchrg where id=" . $order_id;
					// echo $sql_delivery_chrg;
					$db->sql($sql_delivery_chrg);
					$sql_final_total = "update orders set final_total=$final_total-$price+$dchrg where id=" . $order_id;
				} else {
					$sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
				}
				$db->sql($sql_final_total);

			} else {
				$sql_final_total = "update orders set final_total=$final_total-$price where id=" . $order_id;
			}
			$db->sql($sql_final_total);
			if ($total == 0) {
				$sql = "update orders set delivery_charge=0,tax_amount=0,tax_percentage=0,final_total=0 where id=" . $order_id;
				$db->sql($sql);
			}



			if ($res_oi[0]['type'] == 'packet') {
				$sql = "UPDATE product_variant SET stock = stock + " . $res_oi[0]['quantity'] . " WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
				$db->sql($sql);

				$sql = "select stock from product_variant where id=" . $res_oi[0]['product_variant_id'];
				$db->sql($sql);
				$res_stock = $db->getResult();
				if ($res_stock[0]['stock'] > 0) {
					$sql = "UPDATE product_variant set serve_for='Available' WHERE id='" . $res_oi[0]['product_variant_id'] . "'";
					$db->sql($sql);
				}

			} else {
				/* When product type is loose */
				if ($res_oi[0]['measurement_unit_id'] != $res_oi[0]['stock_unit_id']) {
					$stock = $fn->convert_to_parent($res_oi[0]['measurement'], $res_oi[0]['measurement_unit_id']);
					$stock = $stock * $res_oi[0]['quantity'];
					$sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
					$db->sql($sql);
				} else {
					$stock = $res_oi[0]['measurement'] * $res_oi[0]['quantity'];
					$sql = "UPDATE product_variant SET stock = stock + " . $stock . " WHERE product_id='" . $res_oi[0]['product_id'] . "'";
					$db->sql($sql);
				}
				$sql = "select stock from product_variant where product_id=" . $res_oi[0]['product_id'];
				$db->sql($sql);
				$res_stck = $db->getResult();
				if ($res_stck[0]['stock'] > 0) {
					$sql = "UPDATE product_variant set serve_for='Available' WHERE product_id='" . $res_oi[0]['product_id'] . "'";
					$db->sql($sql);
				}
			}
			$status[] = array($postStatus, date("d-m-Y h:i:sa"));
			$currentStatus = $postStatus;
			$data = array(
				'status' => $db->escapeString(json_encode($status)),
				'active_status' => $currentStatus
			);
			$db->update('order_items', $data, 'id=' . $order_item_id);
			$db->getResult();

			$sql = "SELECT id FROM order_items WHERE order_id=" . $order_id;
			$db->sql($sql);
			$total = $db->numRows();
			$sql = "SELECT id FROM `order_items` WHERE order_id=" . $order_id . " && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
			$db->sql($sql);
			$cancelled = $db->numRows();
			//print_r($cancelled." - ".$total);die;
			if ($cancelled == $total) {
				// print_r($res_order);
				if ($res_order[0]['payment_method'] != 'cod' && $res_order[0]['payment_method'] != 'COD') {
					/* update user's wallet */
					$user_id = $res_order[0]['user_id'];
					$total_amount = $res_order[0]['total'] + $res_order[0]['delivery_charge'] + $res_order[0]['tax_amount'];
					$user_wallet_balance = $fn->get_wallet_balance($user_id);
					$new_balance = $user_wallet_balance + $total_amount;
					// return false;
					$fn->update_wallet_balance($new_balance, $user_id);
					/* add wallet transaction */
					$wallet_txn_id = $fn->add_wallet_transaction($user_id, 'credit', $total_amount, 'Balance credited against item cancellation.');
				} else {
					if ($res_order[0]['wallet_balance'] != 0) {
						/* update user's wallet */
						$user_id = $res_order[0]['user_id'];
						// $total = $res[0]['total'];
						$user_wallet_balance = $fn->get_wallet_balance($user_id);
						$new_balance = ($user_wallet_balance + $res_order[0]['wallet_balance']);
						// echo $new_balance;die;
						$fn->update_wallet_balance($new_balance, $user_id);
						/* add wallet transaction */
						$wallet_txn_id = $fn->add_wallet_transaction($user_id, 'credit', $res_order[0]['wallet_balance'], 'Balance credited against item cancellation.');
					}

				}

				$data_order = array(
					'status' => $db->escapeString(json_encode($status)),
					'active_status' => $currentStatus
				);
				$db->update('orders', $data_order, 'id=' . $order_id);
			} else {
				$user_id = $res_order[0]['user_id'];
				$user_wallet_balance = $fn->get_wallet_balance($user_id);
				$new_balance = ($user_wallet_balance + $result[0]['sub_total']);
				// echo $new_balance." - ".$user_id;die;
				$fn->update_wallet_balance($new_balance, $user_id);
				/* add wallet transaction */
				$wallet_txn_id = $fn->add_wallet_transaction($user_id, 'credit', $result[0]['sub_total'], 'Balance credited against item cancellation.');
			}

			$response['error'] = false;
			$response['message'] = 'Order cancelled successfully!';
			$response['subtotal'] = $result[0]['sub_total'];
			print_r(json_encode($response));
			return false;
		}
		if ($postStatus == 'returned') {
			$is_item_delivered = 0;
			foreach ($status as $each_status) {
				if (in_array('delivered', $each_status)) {
					$is_item_delivered = 1;
					$config['max-product-return-days'];
					$now = time(); // or your date as well
					$status_date = strtotime($each_status[1]);
					$datediff = $now - $status_date;

					$no_of_days = round($datediff / (60 * 60 * 24));
					if ($no_of_days > $config['max-product-return-days']) {
						$response['error'] = true;
						$response['message'] = 'Oops! Sorry you cannot return the item now. You have crossed product\'s maximum return period';
						print_r(json_encode($response));
						return false;
					}
				}
			}
			if (!$is_item_delivered) {
				$response['error'] = true;
				$response['message'] = 'Cannot return item unless it is delivered!';
				print_r(json_encode($response));
				return false;
			}
			/* store return request */
			$fn->store_return_request($result[0]['user_id'], $order_id, $order_item_id);
			/* if delivered take the item as returned */
			$status[] = array($postStatus, date("d-m-Y h:i:sa"));
			$data = array(
				'status' => $db->escapeString(json_encode($status)),
				'active_status' => $postStatus
			);
			$db->update('order_items', $data, 'id=' . $order_item_id);
			$db->getResult();

			/* check for other item status and summery of order */
			$sql = "SELECT id FROM order_items WHERE order_id=" . $order_id;
			$db->sql($sql);
			$total = $db->numRows();
			$sql = "SELECT id FROM `order_items` WHERE order_id=" . $order_id . " && (`active_status` LIKE '%cancelled%' OR `active_status` LIKE '%returned%' )";
			$db->sql($sql);
			$returned = $db->numRows();
			if ($returned == $total) {
				$sql = "SELECT status FROM orders WHERE id =" . $order_id;
				$db->sql($sql);
				$res = $db->getResult();
				$status_order = json_decode($res[0]['status']);
				$status_order[] = array($postStatus, date("d-m-Y h:i:sa"));
				$data_order = array(
					'status' => $db->escapeString(json_encode($status)),
					'active_status' => $postStatus
				);
				$db->update('orders', $data_order, 'id=' . $order_id);
			}
			$response['error'] = false;
			$response['message'] = 'Order item returned request received successfully! Amount will credited to your wallet once approved.';
			$response['subtotal'] = $result[0]['sub_total'];
			print_r(json_encode($response));
			return false;
		}
	} else {
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

if (isset($_POST['enable_pay_button'])) {
	$order_id = $db->escapeString($fn->xss_clean($_POST['order_id']));
	$delivery_boy_id = $db->escapeString($fn->xss_clean($_POST['delivery_boy_id']));
	$sql = "SELECT payment_method, active_status, final_total FROM orders WHERE id =" . $order_id . " AND delivery_boy_id=" . $delivery_boy_id;
	$db->sql($sql);
	$res = $db->getResult();
	if (!empty($res)) {
		$order = $res[0];
		if ($order['payment_method'] == 'cod' && $order['active_status'] != 'cancelled') {
			$sql = "UPDATE orders SET payment_request='1' WHERE id =" . $order_id;
			$db->sql($sql);
			$response['error'] = false;
			$response['message'] = 'Payment Request enabled For this Order.';
			$response['payable_amt'] = number_format((float) $order['final_total'], 2, '.', '');
			print_r(json_encode($response));
			return false;
		} else {
			$response['error'] = true;
			$response['message'] = 'Something Wrong';
			print_r(json_encode($response));
			return false;
		}
	}

}

?>
