<?php
session_start();
    include_once('includes/crud.php');
    include_once('api-firebase/send-email.php');
    $db = new Database;
    $db->connect();
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	
    $settings = $fn->get_settings('system_timezone',true);
    $app_name = $settings['app_name'];
	/* include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/connect_database.php'); 
	include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/variables.php');  */
	include('./includes/variables.php'); 
	
	// start session
	// if user click Login button
	if(isset($_POST['username']) && isset($_POST['email']) && isset($_POST['is_login'])){
	    	// get username and password
		$username = $fn->xss_clean($_POST['username']);
		$email = $fn->xss_clean($_POST['email']);
		
		// set time for session timeout
		$currentTime = time() + 25200;
		$expired = 36000;
		
		// create array variable to handle error
		$error = array();
		
		// check whether $username is empty or not
		if(empty($username)){
			$error['username'] = "*Username should be filled.";
		}
		
		// check whether $password is empty or not
		if(empty($email)){
			$error['email'] = "*Email should be filled.";
		}
		
		// if username and password is not empty, check in database
		if(!empty($username) && !empty($email)){
			
			// change username to lowercase
			$username = strtolower($username);
			
			//encript password to sha256
			
			// get data from user table
			$sql_query = "SELECT * FROM admin WHERE email = '".$email."' OR mobile = '".$email."' AND username = '".$username."'";
				 //echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num > 0){
    				$error['success'] = 1;
    				
    				$error['user'] = $username;
    				if($email==$res[0]['email']){
    				    $otp=rand(100000,999999); 
    				    $error['mobile'] = $res[0]['email'];
    				    $subject = "OTP for Reset your password in $app_name";
    				    $message = "Your OTP for Reset Password in  $app_name is $otp";
    				    send_email($res[0]['email'],$subject,$message);
    				    $sql_query = "UPDATE admin SET otp=".$otp." WHERE id = ".$res[0]['id'];
				        $db->sql($sql_query);
    				    $error['type']=1;
    				}else{
    				    $error['mobile'] = $res[0]['mobile'];
    				   $error['type']=2; 
    				}
    				
				}else{
				    $error['success'] = 0;
					$error['message'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
					
				}
			
			
		}
		echo json_encode($error);
	}else if(isset($_POST['is_mobile'])){
	    
	    $username = $fn->xss_clean($_POST['username']);
	    $username = strtolower($username);
			$email = $fn->xss_clean($_POST['email']);
			//encript password to sha256
			
			// get data from user table
			$sql_query = "SELECT * FROM admin WHERE mobile = '".$email."' AND username = '".$username."'";
				 //echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num > 0){
    				$error['success'] = 1;
    				
    				$error['user'] = $username;
    				
    				    $sql_query = "UPDATE admin SET password='".md5($_POST['password'])."' WHERE id = ".$res[0]['id'];
    				    //echo $sql_query;
				        $db->sql($sql_query);
    				    $error['mobile'] = $res[0]['mobile'];
    				    $error['type']=2; 
    				
    				$error['success'] = 1;
					$error['message'] = "<span class='label label-success'>Password Reset Successfully!</span>";
    				
    				
				}else{
				    $error['success'] = 0;
					$error['message'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
				}
			
			
		
		echo json_encode($error);
	}else if(isset($_POST['is_email'])){
	    $username = $fn->xss_clean($_POST['username']);
	    $username = strtolower($username);
			$email = $fn->xss_clean($_POST['email']);
			//encript password to sha256
			
			// get data from user table
			$sql_query = "SELECT * FROM admin WHERE email = '".$email."' AND username = '".$username."'";
				 //echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num > 0){
    				$error['success'] = 1;
    				
    				$error['user'] = $username;
    				if($email==$res[0]['email']){
    				    if($_POST['otp']==$res[0]['otp']){
    				        $sql_query = "UPDATE admin SET password='".md5($_POST['password'])."' WHERE id = ".$res[0]['id'];
    				       // echo $sql_query;
				            $db->sql($sql_query);
				            
    				$error['success'] = 1;
					$error['message'] = "<span class='label label-success'>Password Reset Successfully!</span>";
    				    }else{
    				        $error['success'] = 0;
					        $error['message'] = "<span class='label label-danger'>Invalid OTP!</span>";
    				    }
    				    
    				    $error['type']=1;
    				}
    				
				}else{
				    $error['success'] = 0;
					$error['message'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
				}
			
			
		
		echo json_encode($error);
	}
	
	?>