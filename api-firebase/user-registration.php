<?php
header('Access-Control-Allow-Origin: *');
session_start();
include '../includes/crud.php';
include '../includes/custom-functions.php';
$fn = new custom_functions;
include '../includes/variables.php';
include_once('verify-token.php');
$db = new Database();
$db->connect();
$fn = new custom_functions();
$settings = $fn->get_settings('system_timezone', true);
$app_name = $settings['app_name'];
include 'send-email.php';
include 'send-sms.php';

$response = array();
$accesskey = $_POST['accesskey'];
// echo $access_key;

if ($access_key != $accesskey) {
	$response['error'] = true;
	$response['message'] = "invalid accesskey";
	print_r(json_encode($response));
	return false;
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'verify-user')) {
	if (!verify_token()) {
		return false;
	}
	$mobile = $db->escapeString($_POST['mobile']);
	// 	$country_code = $db->escapeString($_POST['country_code']);
	if (!empty($mobile)) {
		$sql = 'select id from users where mobile =' . $mobile;
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if ($num_rows > 0) {
			$response["error"] = true;
			$response["id"] = $res[0]['id'];
			$response["message"] = "This mobile is already registered. Please login!";
			echo json_encode($response);
		} else if ($num_rows == 0) {
			$response["error"] = false;
			$response["message"] = "Ready to sent firebase OTP request!";

			echo json_encode($response);
		}
	} else {
		$response['error'] = true;
		$response['message'] = "mobile is required.";
		echo json_encode($response);
	}
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'verify-user-email') {
	if (!verify_token()) {
		return false;
	}
	$email = $db->escapeString($_POST['email']);
	$otp = rand(100000, 999999);

	$sql = "select `id`,`name` from `users` where `email`='" . $email . "'";
	$db->sql($sql);
	$result = $db->getResult();
	$num_rows = $db->numRows($result);
	if ($num_rows == 0) {
		//send email
		$to = $email;
		$subject = "$app_name Registration Verification";

		$message = "<#> Your OTP for $app_name Registration verification is : " . $otp . ". Please enter this OTP to activate your profile. ";

		if (!send_email($to, $subject, $message)) {
			$response["error"] = true;
			$response["message"] = "Activation mail could not be sent!Try Again";
			echo json_encode($response);
			return false;
		}
		$response["error"] = false;
		$response["message"] = "OTP for account activation is sent to your email. Please verify it to complete the registration process."/*.$smsResult*/ ;
		$response["OTP"] = $otp;
	} else {
		$response["error"] = true;
		$response["message"] = "Email is already registered. Please login!";
	}
	echo json_encode($response);
}

if ((isset($_POST['type'])) && ($_POST['type'] == 'register')) {
	if (!verify_token()) {
		return false;
	}
	$name = (isset($_POST['name'])) ? $db->escapeString($_POST['name']) : "";
	$mobile = (isset($_POST['mobile'])) ? $db->escapeString($_POST['mobile']) : "";
	$country_code = (isset($_POST['country_code'])) ? $db->escapeString($_POST['country_code']) : 91;
	$fcm_id = (isset($_POST['fcm_id'])) ? $db->escapeString($_POST['fcm_id']) : "";
	$dob = (isset($_POST['dob'])) ? $db->escapeString($_POST['dob']) : "";
	$username = (isset($_POST['username']) && !empty($_POST['username'])) ? $db->escapeString($_POST['username']) : "";
	$email = (isset($_POST['email']) && !empty($_POST['email'])) ? $db->escapeString($_POST['email']) : "";
	$password = md5($_POST['password']);
	$state_raw = (isset($_POST['state_id']) && trim($_POST['state_id']) !== '') ? trim($_POST['state_id']) : ((isset($_POST['state']) && trim($_POST['state']) !== '') ? trim($_POST['state']) : '');
	$city_raw = (isset($_POST['city_id']) && trim($_POST['city_id']) !== '') ? trim($_POST['city_id']) : ((isset($_POST['city']) && trim($_POST['city']) !== '') ? trim($_POST['city']) : '');
	$area_raw = (isset($_POST['area_id']) && trim($_POST['area_id']) !== '') ? trim($_POST['area_id']) : ((isset($_POST['area']) && trim($_POST['area']) !== '') ? trim($_POST['area']) : '');

	$state = is_numeric($state_raw) ? (int) $state_raw : 0;
	$city = is_numeric($city_raw) ? (int) $city_raw : 0;
	$area = is_numeric($area_raw) ? (int) $area_raw : 0;
	$street = (isset($_POST['street'])) ? $db->escapeString($_POST['street']) : "";
	$address = (isset($_POST['address'])) ? $db->escapeString($_POST['address']) : "";
	$pincode = (isset($_POST['pincode'])) ? $db->escapeString($_POST['pincode']) : "";
	$api_key = (isset($_POST['api_key'])) ? $db->escapeString($_POST['api_key']) : "";
	$latitude = (isset($_POST['latitude'])) ? $db->escapeString($_POST['latitude']) : "0";
	$longitude = (isset($_POST['longitude'])) ? $db->escapeString($_POST['longitude']) : "0";
	$status = 1;

	if (empty($username)) {
		$response["error"] = true;
		$response["message"] = "Username is required";

		echo json_encode($response);
		exit;
	}
	if (empty($mobile)) {
		$response["error"] = true;
		$response["message"] = "Mobile number is required";

		echo json_encode($response);
		exit;
	}
	if (empty($email)) {
		$response["error"] = true;
		$response["message"] = "Email is required";

		echo json_encode($response);
		exit;
	}
	$chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$referral_code = "";
	for ($i = 0; $i < 10; $i++) {
		$referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
	}
	if (isset($_POST['friends_code']) && $_POST['friends_code'] != '') {
		$sql = "SELECT id FROM users WHERE referral_code='" . $_POST['friends_code'] . "'";
		$db->sql($sql);
		$result = $db->getResult();
		$num_rows = $db->numRows($result);
		if ($num_rows > 0) {
			$friends_code = $_POST['friends_code'];
		} else {
			$response["error"] = true;
			$response["message"] = "Invalid friends code!";
			echo json_encode($response);
			return false;
		}
	} else {
		$friends_code = '';
	}


	$sql = "select mobile from users where mobile='" . $mobile . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num_rows = $db->numRows($res);
	if ($num_rows > 0) {

		$response["error"] = true;
		$response["message"] = "This $mobile is already registered. Please login!";

		echo json_encode($response);
		exit;


	}

	$sql = "select mobile from users where email='" . $email . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num_rows = $db->numRows($res);
	if ($num_rows > 0) {

		$response["error"] = true;
		$response["message"] = "This $email is already registered. Please login!";

		echo json_encode($response);
		exit;


	}

	$sql = "select mobile from users where username='" . $username . "'";
	$db->sql($sql);
	$res = $db->getResult();
	$num_rows = $db->numRows($res);
	if ($num_rows > 0) {

		$response["error"] = true;
		$response["message"] = "This $username is already registered. Please login!";

		echo json_encode($response);
		exit;


	}


	$data = array(
		'name' => $name,
		'username' => $username,
		'email' => $email,
		'mobile' => $mobile,
		'country_code' => $country_code,
		'fcm_id' => $fcm_id,
		'dob' => $dob,
		'state' => $state,
		'city' => $city,
		'area' => $area,
		'street' => $street,
		'address' => $address,
		'pincode' => $pincode,
		'apikey' => $api_key,
		'password' => $password,
		'referral_code' => $referral_code,
		'friends_code' => $friends_code,
		'latitude' => $latitude,
		'longitude' => $longitude,
		'status' => $status
	);
	$db->insert('users', $data);
	$res = $db->getResult();

	$data1 = array(
		'name' => $name,
		'mobile' => $mobile,
		'user_id' => $res[0],
		'state' => $state,
		'city' => $city,
		'area' => $area,
		'street' => $street,
		'address' => $address,
		'pincode' => $pincode,
		'latitude' => $latitude,
		'longitude' => $longitude,
		'is_default' => 1,
		'status' => 1
	);
	//$db->insert('user_address',$data1);
	$res23 = $db->getResult();
	$state_name = $data['state'] ?? '';
	$city_name = $data['city'] ?? '';
	$area_name = $data['area'] ?? '';

	$response["error"] = false;
	$response["message"] = "User registered successfully";
	$response["user_id"] = $res[0];
	$response['name'] = $data['name'];
	$response['email'] /*= $_SESSION['email']*/ = $data['email'];
	$response['mobile'] /*= $_SESSION['user']*/ = $data['mobile'];
	$response['country_code'] = $data['country_code'];
	$response['fcm_id'] = $data['fcm_id'];
	$response['dob'] = $data['dob'];
	$response['state_id'] = !empty($data['state']) ? $data['state'] : '';
	$response['state_name'] = !empty($state_name) ? $state_name : '';
	$response['city_id'] = !empty($data['city']) ? $data['city'] : '';
	$response['city_name'] = !empty($city_name) ? $city_name : '';
	$response['area_id'] = !empty($data['area']) ? $data['area'] : '';
	$response['area_name'] = !empty($area_name) ? $area_name : '';
	$response['street'] = $data['street'];
	$response['address'] = $data['address'];
	$response['pincode'] = $data['pincode'];
	$response['referral_code'] = $data['referral_code'];
	$response['friends_code'] = $data['friends_code'];
	$response['latitude'] = (!empty($data['latitude'])) ? $data['latitude'] : '0';
	$response['longitude'] = (!empty($data['longitude'])) ? $data['longitude'] : '0';
	$response['apikey'] = $data['apikey'];
	$response['status'] = $data['status'];
	$response['created_at'] = date('Y-m-d h:i:s a');
	echo json_encode($response);


}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'edit-profile') {
	if (!verify_token()) {
		return false;
	}
	$id = $db->escapeString($_POST['id']);
	$name = $db->escapeString($_POST['name']);
	$email = $db->escapeString($_POST['email']);
	$state_raw = (isset($_POST['state_id']) && trim($_POST['state_id']) !== '') ? trim($_POST['state_id']) : ((isset($_POST['state']) && trim($_POST['state']) !== '') ? trim($_POST['state']) : '');
	$city_raw = (isset($_POST['city_id']) && trim($_POST['city_id']) !== '') ? trim($_POST['city_id']) : ((isset($_POST['city']) && trim($_POST['city']) !== '') ? trim($_POST['city']) : '');
	$area_raw = (isset($_POST['area_id']) && trim($_POST['area_id']) !== '') ? trim($_POST['area_id']) : ((isset($_POST['area']) && trim($_POST['area']) !== '') ? trim($_POST['area']) : '');

	$state = is_numeric($state_raw) ? (int) $state_raw : 0;
	$city = is_numeric($city_raw) ? (int) $city_raw : 0;
	$area = is_numeric($area_raw) ? (int) $area_raw : 0;
	$country_code = (isset($_POST['country_code']) && trim($_POST['country_code']) !== '') ? $db->escapeString(trim($_POST['country_code'])) : ((isset($_POST['country']) && trim($_POST['country']) !== '') ? $db->escapeString(trim($_POST['country'])) : '');
	$street = $db->escapeString($_POST['street']);
	// Older clients only send `street`, so the address falls back to it rather
	// than going stale from whatever was stored at registration time.
	$address = (isset($_POST['address']) && trim($_POST['address']) !== '')
		? $db->escapeString($_POST['address'])
		: $street;
	$pincode = $db->escapeString($_POST['pincode']);
	$dob = $db->escapeString($_POST['dob']);
	$latitude = (isset($_POST['latitude']) && !empty($_POST['latitude'])) ? $db->escapeString($_POST['latitude']) : "0";
	$longitude = (isset($_POST['longitude']) && !empty($_POST['longitude'])) ? $db->escapeString($_POST['longitude']) : "0";

	$sql = 'select * from users where id =' . $id;
	$db->sql($sql);
	$res = $db->getResult();

	if (!empty($res)) {
		$country_code_sql = '';
		if ($country_code !== '') {
			$country_code_sql = ',`country_code`="' . $country_code . '"';
		} elseif (empty($res[0]['country_code'])) {
			$country_code_sql = ',`country_code`="+91"';
		}

				$update_data = array(
			'name' => $name,
			'email' => $email,
			'dob' => $dob,
			'state' => $state,
			'city' => $city,
			'area' => $area,
			'street' => $street,
			'address' => $address,
			'pincode' => $pincode,
			'latitude' => $latitude,
			'longitude' => $longitude
		);
		if (!empty($country_code)) {
			$update_data['country_code'] = $country_code;
		} elseif (empty($res[0]['country_code'])) {
			$update_data['country_code'] = '+91';
		}
		$db->update('users', $update_data, 'id=' . $id);
		$db->getResult();

		$sql = "select `id` from `user_address` where `user_id`='" . $id . "'";
		$db->sql($sql);
		$result = $db->getResult();
		$num_rows = $db->numRows($result);
		if ($num_rows == 0) {

			// Zone-wise service: cache the zone this address falls in so the
			// checkout address list does not have to re-run the polygon test.
			$signup_zone = $fn->get_zone_id_from_latlng($latitude, $longitude);
			$signup_zone = ($signup_zone === null) ? 0 : (int) $signup_zone;

			$data = array(
				'user_id' => $id,
				'name' => $name,
				'state' => $state,
				'city' => $city,
				'area' => $area,
				'street' => $street,
				'address' => $address,
				'pincode' => $pincode,
				'latitude' => $latitude,
				'longitude' => $longitude,
				'zone_id' => $signup_zone,
				'email' => $email,
				'mobile' => $res[0]['mobile'],
				'is_default' => '1',
				'status' => '1'
			);
			$db->insert('user_address', $data);

		} else {
			// Re-resolve the zone because the coordinates may have changed.
			$signup_zone = $fn->get_zone_id_from_latlng($latitude, $longitude);
			$signup_zone = ($signup_zone === null) ? 0 : (int) $signup_zone;
			$sql1 = 'UPDATE `user_address` SET `name`="' . $name . '",`state`="' . $state . '",`city`="' . $city . '",`area`="' . $area . '",`street`="' . $street . '",`address`="' . $address . '",`pincode`="' . $pincode . '",`latitude`="' . $latitude . '",`longitude`="' . $longitude . '",`zone_id`="' . $signup_zone . '" WHERE `user_id`=' . $id . ' AND is_default=1';
			$db->sql($sql1);
			$db->getResult();
		}


		$response["error"] = false;
		$response["message"] = "Profile has been updated successfully.";

	} else {
		$response["error"] = true;
		$response["message"] = "valid id is required!!";
	}
	echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'change-password') {
	if (!verify_token()) {
		return false;
	}

	$id = $db->escapeString($_POST['id']);
	$password = $db->escapeString($_POST['password']);
	// $password = 'test1234';
	$password = md5($password);

	// if(!empty($password)) {
	$sql = 'UPDATE `users` SET `password`="' . $password . '" WHERE `id`=' . $id;
	if ($db->sql($sql)) {
		$response["error"] = false;
		$response["message"] = "Profile updated successfully";
	} else {
		$response["error"] = true;
		$response["message"] = "Something went wrong! Try Again!";
	}
	echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-email') {
	if (!verify_token()) {
		return false;
	}
	$email = $db->escapeString($_POST['email']);
	$password = rand(10000, 99999);
	$encrypted_password = md5($password);

	$sql = "select * from `users` where `email`='" . $email . "'";
	$db->sql($sql);
	$result = $db->getResult();
	if ($db->numRows($result)) {
		//send email
		$to = $email;
		$username = $result[0]['username'];
		$subject = "Password Recovery Mail - Password is reset ( $email )";
		$message = "Hi, <b>" . $result[0]['name'] . " - " . $email . "</b>, \t\r\n Your Password has been reset. Please Login with the new password. \r\nYour Username is : " . $username . " \r\nYour new Password is : " . $password . "\r\n Thank you";

		if (!send_email($to, $subject, $message)) {
			$response["error"] = true;
			$response["message"] = "Password could not be reset!Try Again";
			echo json_encode($response);
			return false;
		}
		$sql = 'UPDATE `users` SET `password`="' . $encrypted_password . '" WHERE `email`="' . $email . '"';
		if ($db->sql($sql)) {
			$response["error"] = false;
			$response["message"] = "Password updated successfully! Please check the mail!";
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Email ID does not exist!";
	}
	echo json_encode($response);
}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'forgot-password-mobile') {
	if (!verify_token()) {
		return false;
	}
	$mobile = $db->escapeString($_POST['mobile']);
	// $password = rand(10000,99999);
	$password = 'test1234';
	$encrypted_password = md5($password);
	$sql = "select `id`,`name`,`country_code` from `users` where `mobile`='" . $mobile . "'";
	// 	echo $sql;
	$db->sql($sql);
	$result = $db->getResult();

	if ($db->numRows($result) > 0) {
		$country_code = $result[0]['country_code'];
		//send sms
		$message = 'Your Password for ' . $app_name . ' is Reset. Please login using new Password : ' . $password . '.';
		$sql = 'UPDATE `users` SET `password`="' . $encrypted_password . '" WHERE `mobile`="' . $mobile . '"';
		if ($db->sql($sql)) {
			// sendSms($mobile,$message,$country_code);    
			$response["error"] = false;
			$response["message"] = "Password is sent successfully! Please login via the OTP sent to your mobile number!";

		}
	} else {
		$response["error"] = true;
		$response["message"] = "Mobile number does not exist! Please Register";
	}
	echo json_encode($response);
}


if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'delete-notification') {

	$id = $_POST['id'];
	$image = $_POST['image'];

	if (!empty($image))
		unlink('../' . $image);

	$sql = 'DELETE FROM `notifications` WHERE `id`=' . $id;
	if ($db->sql($sql)) {
		echo 1;
	} else {
		echo 0;
	}
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'register-device') {
	if (!verify_token()) {
		return false;
	}
	$user_id = $db->escapeString($_POST['user_id']);
	$token = $db->escapeString($_POST['token']);

	$sql = "select `id` from `users` where `id`='" . $user_id . "'";
	$db->sql($sql);
	$result = $db->getResult();
	if ($db->numRows($result) > 0) {
		// Update the Device ID
		$sql = 'UPDATE `users` SET `fcm_id`="' . $token . '" WHERE `id`="' . $user_id . '"';
		if ($db->sql($sql)) {
			$response["error"] = false;
			$response["message"] = "Device updated successfully";
		}
	} else {
		// $sql = "INSERT INTO devices (user_id, token) VALUES ('$user_id','$token')";
		// $db->sql($sql);
		$response["error"] = true;
		$response["message"] = "User does't exists.";
	}
	echo json_encode($response);
}

if (isset($_POST['type']) && $_POST['type'] != '' && $_POST['type'] == 'send-invitation') {
	if (!verify_token()) {
		return false;
	}
	$referral_code = $db->escapeString($_POST['referral_code']);
	$friend_id = $db->escapeString($_POST['friend_id']);
	$sql = "select * from `users` where `referral_code`='" . $referral_code . "'";
	$db->sql($sql);
	$result = $db->getResult();
	if ($db->numRows($result) > 0) {
		// Update the Device ID
		$sql = 'UPDATE `users` SET `friends_code`="' . $referral_code . '" WHERE `id`="' . $friend_id . '"';
		if ($db->sql($sql)) {
			$response["error"] = false;
			$response["message"] = "Invitation sent successfully";
			$response['data'] = $result;
		}
	} else {
		$response["error"] = true;
		$response["message"] = "Invalid referral code.";
	}
	echo json_encode($response);
}


if ((isset($_POST['type'])) && ($_POST['type'] == 'delete_user')) {
	if (!verify_token()) {
		return false;
	}
	$user_id = $db->escapeString($_POST['user_id']);
	$sql = 'DELETE FROM user_address WHERE user_id =' . $user_id;
	$db->sql($sql);
	$res = $db->getResult();

	$sql = 'DELETE FROM users WHERE id =' . $user_id;
	$db->sql($sql);
	$res = $db->getResult();

	$response["error"] = false;
	$response["message"] = "User Deleted successfully";

	echo json_encode($response);
	exit;

}


?>