<?php session_start();
    ob_start(); 
    include_once('includes/crud.php');
    $db = new Database;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    $sql = "SELECT * FROM settings";
    $db->sql($sql);
    $res = $db->getResult();
    $settings = json_decode($res[5]['value'],1);
    $logo = $fn->get_settings('logo');
    
    ?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" type="image/ico" href="<?='dist/img/'.$logo?>">
	<title>Admin Login - <?=$settings['app_name']?></title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    
	<link rel="stylesheet" href="dist/styles/style.min.css">

	<!-- Waves Effect -->
	<link rel="stylesheet" href="dist/plugin/waves/waves.min.css">
  </head>
</body>
      <!-- Content Wrapper. Contains page content -->
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
			<!-- /.frm-input -->
			<div id="otp_frm">
		<div class="frm-input"><input type="text"  name="otp" id="otp" placeholder="Enter OTP" class="frm-inp"><i class="fa fa-user frm-ico" required></i></div>
			<!-- /.clearfix -->
			<button type="submit" name="btnLogin" onclick="otp_sent()" class="frm-submit">Submit OTP<i class="fa fa-arrow-circle-right"></i></button>
			</div>
			
			<div class="frm-footer text-center">SpiderIndia © <?=date('Y')?>.</div>
			<!-- /.footer -->
		</div>
		<!-- .inside -->
	</form>
	<!-- /.frm-single -->
</div><!--/#single-wrapper -->

	   
	<script src="dist/scripts/jquery.min.js"></script>
	<script src="dist/scripts/modernizr.min.js"></script>
	<script src="dist/plugin/bootstrap/js/bootstrap.min.js"></script>
	<script src="dist/plugin/nprogress/nprogress.js"></script>
	<script src="dist/plugin/waves/waves.min.js"></script>

	<script src="dist/scripts/main.min.js"></script>
	<script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-app.js"></script>
	   <script src="https://www.gstatic.com/firebasejs/8.3.0/firebase-auth.js"></script>
   <script>
     window.onload = function() {
		 // alertify.set('notifier','position', 'top-right');
    // TODO: Replace the following with your app's Firebase project configuration
    // For Firebase JavaScript SDK v7.20.0 and later, `measurementId` is an optional field
    
//   var firebaseConfig = {
//   apiKey: "AIzaSyCI_OO7bGZhcc89fzSeAfvkqQ79BcjdiAM",
//   authDomain: "kensho-daily.firebaseapp.com",
//   databaseURL: "https://kensho-daily.firebaseio.com",
//   projectId: "kensho-daily",
//   storageBucket: "kensho-daily.appspot.com",
//   messagingSenderId: "kensho-daily",
//   appId: "kensho-daily",
//   measurementId: "kensho-daily",
// };

var firebaseConfig = {
    apiKey: "AIzaSyBGp3XX2NspVAvGV5YnmTSUs8WWaU0l_E4",
    authDomain: "euspider-376ae.firebaseapp.com",
    projectId: "euspider-376ae",
    storageBucket: "euspider-376ae.appspot.com",
    messagingSenderId: "610918406020",
    appId: "1:610918406020:web:8386933eb58b8a636defd1",
    measurementId: "G-BVCLRXP3VG"
  };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
	
	window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sign-in-button', {
      'size': 'invisible',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
        register_submit();
      }
    });

    recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
    });
  };
  
  function register_submit(){
	  mobile=$('#mobile').val();
	  name=$('#name').val();
	  password=$('#password').val();
	  email=$('#email').val();
	  if(mobile!='' && name!='' && password!=''){
		$.ajax({
				   type: "POST",
				   url: "<?=base_url()?>site/register_validate1",
				   data: "phone="+mobile+"&email="+email+"&password="+password, 
				   success: function(res)
				   {
					   console.log(res);
					   result=$.trim(res);
					   res1=JSON.parse(result);
						if(res1.success == 0){
							alertify.error(res1.data);
							
						setTimeout(function(){ location.reload(); }, 3000);
						}else{
							var htm="A text with a One Time Password (OTP) has been sent to your mobile number: <b>+91"+mobile+"</b> <a style='color:red; !important' onclick='changenumber()'>Change</a>";
							$("#message-board").html(htm);
							onSignInSubmit();
							//$('#register_form').trigger("reset");
							
						}
				   }
		});
	  }else{
		 alertify.error("Required All Fields"); 
	  }
  }
  
  function register_submit1(){
	  mobile=$('#mobile').val();
	  name=$('#name').val();
	  password=$('#password').val();
	  email=$('#email').val();
	  $.ajax({
			   type: "POST",
			   url: "<?=base_url()?>site/register_validate",
			   data: "phone="+mobile+"&name="+name+"&password="+password+"&email="+email, 
			   success: function(res)
			   {
			       console.log(res);
				   result=$.trim(res);
				   res1=JSON.parse(result);
					if(res1.success == 0){
						alertify.error(res1.data);
					}else{
						alertify.error(res1.data);
						setTimeout(function(){ window.location.href = "login" }, 3000);
						//$('#register_form').trigger("reset");
						
					}
			   }
			});
  }
  function changenumber(){
	  $('.accout1').hide();
	$('.accout').show();
  }
   function onSignInSubmit() {
    if (isPhoneNumberValid()) {
      var phoneNumber = getPhoneNumberFromUserInput();
      var appVerifier = window.recaptchaVerifier;
      firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
          .then(function (confirmationResult) {
			  
			  
						$('.accout').hide();
						$('.accout1').show();
						alertify.delay(0).log(res1.data);
			  
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
          }).catch(function (error) {
            // Error; SMS not sent
            console.error('Error during signInWithPhoneNumber', error);
            window.alert('Error during signInWithPhoneNumber:\n\n'
                + error.code + '\n\n' + error.message);
          });
    }
  }
  
  function onVerifyCodeSubmit(e) {
    if (!!getCodeFromUserInput()) {
      window.verifyingCode = true;
      var code = getCodeFromUserInput();
      confirmationResult.confirm(code).then(function (result) {
        // User signed in successfully.
        var user = result.user;
        window.verifyingCode = false;
        window.confirmationResult = null;
		register_submit1();
      }).catch(function (error) {
        // User couldn't sign in (bad verification code?)
        console.error('Error while checking the verification code', error);
        window.alert('Error while checking the verification code:\n\n'
            + error.code + '\n\n' + error.message);
        window.verifyingCode = false;
      });
    }
  }
  
  function cancelVerification(e) {
    e.preventDefault();
    window.confirmationResult = null;
    updateVerificationCodeFormUI();
    updateSignInFormUI();
  }
  
  function onSignOutClick() {
    firebase.auth().signOut();
  }

  /**
   * Reads the verification code from the user input.
   */
  function getCodeFromUserInput() {
    return document.getElementById('verification-code').value;
  }

  /**
   * Reads the phone number from the user input.
   */
  function getPhoneNumberFromUserInput() {
    return "+91"+document.getElementById('mobile').value;
  }

  /**
   * Returns true if the phone number is valid.
   */
  function isPhoneNumberValid() {
    var pattern = /^\+[0-9\s\-\(\)]+$/;
    var phoneNumber = getPhoneNumberFromUserInput();
    return phoneNumber.search(pattern) !== -1;
  }

  /**
   * Re-initializes the ReCaptacha widget.
   */
  function resetReCaptcha() {
    if (typeof grecaptcha !== 'undefined'
        && typeof window.recaptchaWidgetId !== 'undefined') {
      grecaptcha.reset(window.recaptchaWidgetId);
    }
  }

  /**
   * Updates the Sign-in button state depending on ReCAptcha and form values state.
   */


  /**
   * Updates the Verify-code button state depending on form values state.
   */


  /**
   * Updates the state of the Sign-in form.
   */
  function updateSignInFormUI() {
    if (firebase.auth().currentUser || window.confirmationResult) {
      document.getElementById('sign-in-form').style.display = 'none';
    } else {
      resetReCaptcha();
      document.getElementById('sign-in-form').style.display = 'block';
    }
  }

  /**
   * Updates the state of the Verify code form.
   */
  function updateVerificationCodeFormUI() {
    if (!firebase.auth().currentUser && window.confirmationResult) {
      document.getElementById('verification-code-form').style.display = 'block';
    } else {
      document.getElementById('verification-code-form').style.display = 'none';
    }
  }

  /**
   * Updates the state of the Sign out button.
   */
  function updateSignOutButtonUI() {
    if (firebase.auth().currentUser) {
      document.getElementById('sign-out-button').style.display = 'block';
    } else {
      document.getElementById('sign-out-button').style.display = 'none';
    }
  }

  /**
   * Updates the Signed in user status panel.
   */
  function updateSignedInUserStatusUI() {
    var user = firebase.auth().currentUser;
    if (user) {
      document.getElementById('sign-in-status').textContent = 'Signed in';
      document.getElementById('account-details').textContent = JSON.stringify(user, null, '  ');
    } else {
      document.getElementById('sign-in-status').textContent = 'Signed out';
      document.getElementById('account-details').textContent = 'null';
    }
  }
	
  </script>
  </body>
</html>