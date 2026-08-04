<?php
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	/* include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/connect_database.php'); 
	include($_SERVER['DOCUMENT_ROOT'].'/admin/includes/variables.php');  */
	include('./includes/variables.php'); 
	
	// start session
	// if user click Login button
	if(isset($_POST['btnLogin'])){
	
		// get username and password
		$username = $db->escapeString($fn->xss_clean($_POST['username']));
		$password = $db->escapeString($fn->xss_clean($_POST['password']));
		
		// set time for session timeout
		$currentTime = time() + 25200;
		$expired = 3600;
		
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
			$sql_query = "SELECT * 
				FROM admin 
				WHERE username = '".$username."' AND password = '".$password."'";
				// echo $sql_query;
				// Bind your variables to replace the ?s
				// Execute query
				$db->sql($sql_query);
				/* store result */
				$res=$db->getResult();
				$num = $db->numRows($res);
				// Close statement object
				if($num == 1){
				    $secretkey=rand();
					$_SESSION['id'] = $res[0]['id'];
					$_SESSION['role'] = $res[0]['role'];
					$_SESSION['secretkey']=$secretkey;
					$_SESSION['user'] = $username;
					$_SESSION['timeout'] = $currentTime + $expired;
					$sql="UPDATE admin SET web_login='".$secretkey."' WHERE id=".$res[0]['id'];
					$db->sql($sql);
					$db->getResult();
				//	header("location: home.php");
				}else{
					$error['failed'] = "<span class='label label-danger'>Invalid Username or Password!</span>";
				}
			
			
		}	
	}
	?>
	<?php $sql_logo="select value from `settings` where variable='Logo' OR variable='logo'";
	    $db->sql($sql_logo);
	    $res_logo=$db->getResult();
	    
	    ?>
		<?php echo isset($error['update_user']) ? $error['update_user'] : '';?>
		<style>
		    #double-wrapper {
    min-height: 100%;
    background: url(../images/sativa.png) top center repeat;
    overflow: hidden;
    width: 100%;
    padding: 0 15px 0 15px;
}
		    
		</style>
<div id="single-wrapper">
	<form method="post" enctype="multipart/form-data" class="frm-single" action="otp_auth.php">
		<div class="inside">
		<div class="title">
			<img src="<?=DOMAIN_URL.'dist/img/'.$res_logo[0]['value']?>" height="110">
			<h3><?=$settings['app_name']?> Dashboard</h3>
		</div>
		<div id="login_frm">
			<div class="frm-input"><input type="text"  name="username" placeholder="Username" class="frm-inp"><i class="fa fa-user frm-ico" required></i></div>
			<!-- /.frm-input -->
			<div class="frm-input"><input type="password" name="password" placeholder="Password" class="frm-inp"><i class="fa fa-lock frm-ico" required></i></div>
			
			<button type="submit" name="btnLogin" onclick="otp_sent()" class="frm-submit">Login<i class="fa fa-arrow-circle-right"></i></button>
			</div>
			<!-- /.frm-input -->
			<div id="otp_frm" style="display:none">
		<div class="frm-input"><input type="text"  name="otp" id="otp" placeholder="Enter OTP" class="frm-inp"><i class="fa fa-user frm-ico" required></i></div>
			<!-- /.clearfix -->
			<button type="submit" name="btnLogin" onclick="otp_sent()" class="frm-submit">Submit OTP<i class="fa fa-arrow-circle-right"></i></button>
			</div>
			
			<div class="frm-footer text-center"><?=$settings['app_name']?> © <?=date('Y')?>.</div>
			<!-- /.footer -->
		</div>
		<!-- .inside -->
	</form>
	<!-- /.frm-single -->
</div><!--/#single-wrapper -->

