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
    $sql_logo="select value from `settings` where variable='Logo' OR variable='logo'";
	    $db->sql($sql_logo);
	    $res_logo=$db->getResult();
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
       <div id="single-wrapper">
	<form method="post" enctype="multipart/form-data" class="frm-single">
		<div class="inside">
		<div class="title">
			<img src="<?=DOMAIN_URL.'dist/img/'.$res_logo[0]['value']?>" height="110">
			<h3>Forgot Password</h3>
		</div>
			<div id="login_frm">
			<div class="frm-input"><input type="text"  name="username" id="username" placeholder="Username" class="frm-inp"></div>
			
			<div class="frm-input"><input type="text"  name="email" id="email" placeholder="Email / Mobile" class="frm-inp"></div>
			
			<!--<div class="frm-input"><div class="g-recaptcha" data-sitekey="6Lf62XQtAAAAAIV0ZAxRP1cdh5BbsSe9-VyQCec-"></div></div>-->
			
			<!-- /.frm-input -->
			<button id="sign-in-button" class="frm-submit">Forgot Password</button>
			</div>
			<!-- /.frm-input -->
			<div id="otp_frm" style="display:none">
		<div class="frm-input"><input type="text"  name="otp" id="verification-code" placeholder="Enter OTP" class="frm-inp"></div>
		<div class="frm-input"><input type="password"  name="new-password" id="new-password" placeholder="Enter New Password" class="frm-inp"></div>
		<div class="frm-input"><input type="password"  name="confirm-password" id="confirm-password" placeholder="Confirm Password" class="frm-inp"></div>
		<input type="hidden" id="mobile">
		<input type="hidden" id="type">
			<!-- /.clearfix -->
			<button id="login-button" class="frm-submit">Change Password</button>
			</div>
			
			<p id="message"></p>
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

var firebaseConfig = {
      apiKey: "AIzaSyDMMeFytKUYQgPSTvj4RTIesMr6MA95Joc",
      authDomain: "buy-eazy-a94d6.firebaseapp.com",
      projectId: "buy-eazy-a94d6",
      storageBucket: "buy-eazy-a94d6.firebasestorage.app",
      messagingSenderId: "1039271684439",
      appId: "1:1039271684439:web:f3408060427a30d4f33612",
      measurementId: "G-HBQ8DN8P2Z"
  };

    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);
	
	window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('sign-in-button', {
      'size': 'invisible',
      'callback': function(response) {
        // reCAPTCHA solved, allow signInWithPhoneNumber.
        login_submit();
      }
    });

    recaptchaVerifier.render().then(function(widgetId) {
      window.recaptchaWidgetId = widgetId;
    });
  };
  
  function login_submit(){
	  username=$('#username').val();
	  email=$('#email').val();
	  if(username!='' && email!=''){
		$.ajax({
				   type: "POST",
				   url: "forgot_validate.php",
				   data: "username="+username+"&email="+email+"&is_login=1", 
				   success: function(res)
				   {
					   console.log(res);
					   res2=$.trim(res);
					   res1=JSON.parse(res2);
						if(res1.success == 0){
							$("#message").html(res1.message);
							
						setTimeout(function(){ location.reload(); }, 3000);
						}else{
						    mobile=res1.mobile;
						    $("#mobile").val(mobile);
						    if(res1.type==1){
							    var htm="A text with a One Time Password (OTP) has been sent to your Mail ID: <b>"+mobile+"</b>";
							    $("#login_frm").hide();
						        $("#otp_frm").show();
						        $('#type').val(1);
						    }else{
							    var htm="A text with a One Time Password (OTP) has been sent to your mobile number: <b>+91"+mobile+"</b>";
							     $('#type').val(2);
							    onSignInSubmit();
						    }
							$("#message").html(htm);
							
							//$('#register_form').trigger("reset");
							
						}
				   }
		});
	  }else{
		 alert("Required All Fields"); 
	  }
  }
  function login_submit1(){
	  username=$('#username').val();
	  email=$('#email').val();
	  type=$('#type').val();
	  otp=$('#verification-code').val();
	  password=$('#new-password').val();
	  confirm_password=$('#confirm-password').val();
	  if(username!='' && password!='' && otp !='' && email !=''){
	      if(password== confirm_password){
	          if(type==1){
	              
	          
    		$.ajax({
    				   type: "POST",
    				   url: "forgot_validate.php",
    				   data: "username="+username+"&email="+email+"&otp="+otp+"&password="+password+"&is_email=1", 
    				   success: function(res)
    				   {
    					   console.log(res);
    					   res2=$.trim(res);
    					   res1=JSON.parse(res2);
    						if(res1.success == 0){
    							$("#message").html(res1.message);
    							
    						
    						}else{
						    
							$("#message").html(res1.message);
    						    setTimeout(function(){ window.location.href="index.php"; }, 2000);
    							
    				   }
    				   }
    		});
	    
	          }else{
	              onVerifyCodeSubmit();
	          }
	      }else{
	         $("#message").html('Password and Confirm Password is not same');
	      }
	   }else{
	  }
  }
  function login_submit2(){
	  username=$('#username').val();
	  email=$('#email').val();
	  type=$('#type').val();
	  otp=$('#verification-code').val();
	  password=$('#new-password').val();
	  confirm_password=$('#confirm-password').val();
	  if(username!='' && password!='' && otp !='' && email !=''){
	      if(password== confirm_password){
	          
	              
	          
    		$.ajax({
    				   type: "POST",
    				   url: "forgot_validate.php",
    				   data: "username="+username+"&email="+email+"&password="+password+"&is_mobile=1", 
    				   success: function(res)
    				   {
    					   console.log(res);
    					   res2=$.trim(res);
    					   res1=JSON.parse(res2);
    						if(res1.success == 0){
    							$("#message").html(res1.message);
    							
    						
    						}else{
						    
							$("#message").html(res1.message);
    						    setTimeout(function(){ window.location.href="index.php"; }, 2000);
    							
    						}
    				   }
    		});
	    
	          
	      }else{
	         $("#message").html('Password and Confirm Password is not same');
	      }
	   }else{
	  }
  }
  function onSignInSubmit() {
    if (isPhoneNumberValid()) {
      var phoneNumber = getPhoneNumberFromUserInput();
      var appVerifier = window.recaptchaVerifier;
      firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
          .then(function (confirmationResult) {
			$('#login_frm').hide();
			$('#otp_frm').show();
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
  $("#login-button").click(e => {
  e.preventDefault();
  login_submit1();
});
  function onVerifyCodeSubmit() {
    if (!!getCodeFromUserInput()) {
      window.verifyingCode = true;
      var code = getCodeFromUserInput();
      confirmationResult.confirm(code).then(function (result) {
          console.log(result);
        // User signed in successfully.
        var user = result.user;
        window.verifyingCode = false;
        window.confirmationResult = null;
		login_submit2();
      }).catch(function (error) {
        // User couldn't sign in (bad verification code?)
        console.error('Error while checking the verification code', error);
        $("#message").html('Error while checking the verification code');
        window.verifyingCode = false;
      });
    }
  }
  function getCodeFromUserInput() {
    return document.getElementById('verification-code').value;
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