<?php
session_start();
    include_once('includes/crud.php');
    $db = new Database;
    $db->connect();
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	/* include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/connect_database.php'); 
	include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/variables.php');  */
	include('./includes/variables.php'); 
	
	$sql = "SELECT * FROM settings";
    $db->sql($sql);
    $res = $db->getResult();
    $settings = json_decode($res[5]['value'],1);
	// start session
	// if user click Login button
	if(isset($_POST['username']) && isset($_POST['password'])){
	    	// get username and password
		$username = $fn->xss_clean($_POST['username']);
		$password = $fn->xss_clean($_POST['password']);
		
		// set time for session timeout
		$currentTime = time() + 25200;
		
		
		// create array variable to handle error
		$error = array();
		
		// check whether $username is empty or not
		if(empty($username)){
			$error['username'] = "*Username should be filled.";
		}
		
		// check whether $password is empty or not
		if(empty($password)){
			$error['password'] = "*Password should be filled.";
		}
		
		// if username and password is not empty, check in database
		if(!empty($username) && !empty($password)){
			
			// change username to lowercase
			$username = strtolower($username);
			
			//encript password to sha256
		    $password = md5($password);
			
			// get data from user table
			$sql_query = "SELECT * FROM admin WHERE username = '".$username."' AND password = '".$password."' AND applicable_for='web'";
				// echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num == 1){
    				$error['success'] = 1;
    				$error['mobile'] = $res[0]['mobile'];
    				$error['user'] = $username;
    				if(isset($_POST['is_login']) || !isset($settings['two_auth'])){
						if(!isset($settings['two_auth'])){
							if(empty($_POST['captcha'])){
								$error['logged_in']=0;
								$error['message'] = "<span class='label label-danger'>Please Verify Captcha</span>";
								echo json_encode($error);die;
							}
							$secret_key = "6Lf6wnQtAAAAANBPPdSyo9dnZZaGTgX9a2cpksqk";
							$url = "https://www.google.com/recaptcha/api/siteverify?secret=".$secret_key."&response=".$_POST['captcha'];
							if(function_exists('curl_init')){
								$ch = curl_init();
								curl_setopt($ch, CURLOPT_URL, $url);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_TIMEOUT, 10);
								$verify_response = curl_exec($ch);
								curl_close($ch);
							} else {
								$verify_response = file_get_contents($url);
							}
							$response_data = json_decode($verify_response);
							if(!$response_data || !$response_data->success){
								$error['logged_in']=0;
								$error['message'] = "<span class='label label-danger'>Captcha Verification Failed</span>";
								echo json_encode($error);die;
							}
						}
    				    $secretkey=rand();
    					$_SESSION['id'] = $res[0]['id'];
    					$_SESSION['role'] = $res[0]['role'];
    					$_SESSION['secretkey']=$secretkey;
    					$_SESSION['user'] = $username;
						$_SESSION['secretlogin'] = 'no';
    					
						if(isset($_POST['remember_me']) && $_POST['remember_me']=='1'){
							$expired = 3600000000000;
							
						}else{
							$expired = 36000;
							//$sql="UPDATE admin SET web_login='".$secretkey."', remember_me=0 WHERE id=".$res[0]['id'];
						}
						$sql="UPDATE admin SET web_login='".$secretkey."' WHERE id=".$res[0]['id'];
    					$_SESSION['timeout'] = $currentTime + $expired;
    					$db->sql($sql);
    					$db->getResult();
    					$error['logged_in']=1;
    					$error['message'] = "<span class='label label-success'>Logged in Successfully</span>";
    				}else if(isset($settings['two_auth'])){
    				    $error['logged_in']=0;
    				    $error['message'] = "<span class='label label-success'>Please Verify otp</span>";
    				}
				}else{
				    $error['success'] = 0;
					$error['message'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
					
				}
			
			
		}
		echo json_encode($error);
	}
	
	?>