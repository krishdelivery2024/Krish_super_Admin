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
    $settings = json_decode($res[6]['value'],1);
    //echo '<pre>';print_r($res);die;
    $logo = $fn->get_settings('logo');
    $sql_logo="select value from `settings` where variable='Logo' OR variable='logo'";
	    $db->sql($sql_logo);
	    $res_logo=$db->getResult();
		if (!isset($settings['two_auth'])) {
	        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
	    }
	    if (isset($_SESSION['id'])) {
	        header("location:home.php");
	    }
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
	<style>
    .float-left{
        float:left;
    }
    .float-right{
        float:right;
    }
    .frm-submit1 {
    /*display: block;*/
    width: 45% !important;
    }
    button#resend-button {
    width: 45%;
    height: 36px;
    border: none;
    background: #3F51B5;
    color: #ffffff;
    margin-left: 20px;
    }
    .inlinestyle {
    display: inline-flex;
    width:100%;
   }
    </style>
  </head>
</body>
      <!-- Content Wrapper. Contains page content -->
       <div id="single-wrapper">
	<!--<form method="post" enctype="multipart/form-data" class="frm-single">-->
	<div  class="frm-single">
		<div class="inside">
		<div class="title">
			<img src="<?=isset($res_logo)?DOMAIN_URL.'dist/img/'.$res_logo[0]['value']:''?>" height="110">
			<h3><?=$settings['app_name']?></h3>
		</div>
			<div id="login_frm">
			<div class="frm-input"><input type="text"  name="username" id="username" placeholder="Username" class="frm-inp"><i class="fa fa-user frm-ico" required></i></div>
			<!-- /.frm-input -->
			<div class="frm-input"><input type="password" name="password" id="password" placeholder="Password" class="frm-inp"><i class="fa fa-lock frm-ico" required></i></div>
			<?php if (!isset($settings['two_auth'])) {
				echo '<div class="frm-input"><div class="g-recaptcha" data-sitekey="6LfQ3XQtAAAAAA9fiaaaVfJ37PDtUhp-rY_a_nOG"></div></div>';
			}?>
			
			<div class="clearfix margin-bottom-20">
				<div class="float-left">
					<div class="checkbox primary"><input type="checkbox" name="remember_me" id="rememberme" value='1'><label for="rememberme">Remember me</label></div>
					<!-- /.checkbox -->
				</div>
				<!-- /.float-left -->
				<div class="float-right"><a href="forgot-password.php" class="a-link"><i class="fa fa-unlock-alt"></i>Forgot password?</a></div>
				<!-- /.float-right -->
			</div>
			<!-- /.clearfix -->
			<?php if (isset($settings['two_auth'])) {
	        echo '<button id="sign-in-button" class="frm-submit">Login<i class="fa fa-arrow-circle-right"></i></button>';
	    }else{
			echo '<button id="sign-in-button" onclick="login_submit()" class="frm-submit">Login<i class="fa fa-arrow-circle-right"></i></button>';
		} ?>
			</div>
			<!-- /.frm-input -->
			<div id="otp_frm" style="display:none">
		<div class="frm-input"><input type="text"  name="otp" id="verification-code" placeholder="Enter OTP" class="frm-inp"><i class="fa fa-user frm-ico" required></i></div>
		<input type="hidden" id="mobile">
			<p><span id="timernote">WAIT FOR OTP:</span><span id="element"></span></p>
			<!-- /.clearfix -->
			<div class="inlinestyle">
			<button id="login-button" class="frm-submit frm-submit1">Submit OTP<i class="fa fa-arrow-circle-right"></i></button>
			<button id="resend-button" class="frm-resend">Resend OTP<i class="fa fa-arrow-circle-right"></i></button>
			</div>
			</div>
			<p id="message"></p>
						<div class="frm-footer text-center">Krish Delivery © <?=date('Y')?>.</div>

			<!-- /.footer -->
		</div>
		<!-- .inside -->
	<!--</form>-->
	</div>
	<!-- /.frm-single -->
</div><!--/#single-wrapper -->
	   
	<script src="dist/scripts/jquery.min.js"></script>
	<script src="dist/scripts/modernizr.min.js"></script>
	<script src="dist/plugin/bootstrap/js/bootstrap.min.js"></script>
	<script src="dist/plugin/nprogress/nprogress.js"></script>
	<script src="dist/plugin/waves/waves.min.js"></script>

	<script src="dist/scripts/main.min.js"></script>
	<?php if (isset($settings['two_auth'])) {?>
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
	  password=$('#password').val();
	  if(username!='' && password!=''){
		$.ajax({
				   type: "POST",
				   url: "login_validate.php",
				   data: "username="+username+"&password="+password, 
				   success: function(res)
				   {
					   console.log(res);
					   res2=$.trim(res);
					   res1=JSON.parse(res2);
						if(res1.success == 0){
							$("#message").html(res1.message);
							
						setTimeout(function(){ location.reload(); }, 3000);
						}else{
						    if(res1.logged_in ==1){
						        $("#message").html(res1.message);
						        setTimeout(function(){ window.location.href="home.php"; }, 2000);
						    }else{
    						    mobile=res1.mobile;
    						    $("#mobile").val(mobile);
    							var htm="A text with a One Time Password (OTP) has been sent to your mobile number: <b>+91"+mobile+"</b>";
    							$("#message").html(htm);
    							onSignInSubmit();
						    }
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
	  password=$('#password').val();
	  remember_me=$('#rememberme').val();
	  
	  if(username!='' && password!=''){
		$.ajax({
				   type: "POST",
				   url: "login_validate.php",
				   data: "username="+username+"&password="+password+"&remember_me="+remember_me+"&is_login=1", 
				   success: function(res)
				   {
					   console.log(res);
					   res2=$.trim(res);
					   res1=JSON.parse(res2);
						if(res1.success == 0){
							$("#message").html(res1.message);
							
						
						}else{
						    $("#message").html(res1.message);
						    setTimeout(function(){ window.location.href="home.php"; }, 2000);
							
						}
				   }
		});
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
			$('.frm-resend').prop('disabled', true);
            $(".frm-resend").css("cursor", "not-allowed");
			countdown('clock', 0, 120);
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
          }).catch(function (error) {
            // Error; SMS not sent
            console.error('Error during signInWithPhoneNumber', error);
            if(error.message=="We have blocked all requests from this device due to unusual activity. Try again later."){
                  alert("We have blocked all requests from this device due to unusual activity. Try again later.");
              }
            // window.alert('Error during signInWithPhoneNumber:\n\n'
            //     + error.code + '\n\n' + error.message);
          });
    }
  }
    function onSignInSubmit1() {
    //   alert("ram");
    if (isPhoneNumberValid()) {
      var phoneNumber = getPhoneNumberFromUserInput();
      var appVerifier = window.recaptchaVerifier;
      firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
          .then(function (confirmationResult) {
			$('#login_frm').hide();
			$('#otp_frm').show();
			countdown('clock', 0, 120);
            // SMS sent. Prompt user to type the code from the message, then sign the
            // user in with confirmationResult.confirm(code).
            window.confirmationResult = confirmationResult;
          }).catch(function (error) {
            // Error; SMS not sent
            console.error('Error during signInWithPhoneNumber', error);
            
            if(error.message=="We have blocked all requests from this device due to unusual activity. Try again later."){
                  alert("We have blocked all requests from this device due to unusual activity. Try again later.");
              }
              
            // window.alert('Error during signInWithPhoneNumber:\n\n'
            //     + error.code + '\n\n' + error.message);
          });
    }
  }
    $(".frm-resend").click(function(){
     $(".frm-resend").css("cursor", "not-allowed"); 
     $('.frm-resend').prop('disabled', true);
     onSignInSubmit1()
  });
  
  function countdown(element, minutes, seconds) {
// set time for the particular countdown
var time = minutes*60 + seconds;
var interval = setInterval(function() {
    var el = document.getElementById('element');
    // if the time is 0 then end the counter
    if(time == 0) {
        
        $('.frm-resend').prop('disabled', false);
        $(".frm-resend").css("cursor", "pointer");
        // el.innerHTML = "RESEND OTP";
        // setTimeout(function() {
        //     el.innerHTML = "RESEND OTP";
        // }, 1000);


        clearInterval(interval);

        // setTimeout(function() {
        //     countdown('clock', 0, 5);
        // }, 2000);
    }
    var minutes = Math.floor( time / 60 );
    if (minutes < 10) minutes = "0" + minutes;
    var seconds = time % 60;
    if (seconds < 10) seconds = "0" + seconds; 
    var text = minutes + ':' + seconds;
    el.innerHTML = text;
    time--;
}, 1000);
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
  onVerifyCodeSubmit();
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
		login_submit1();
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
	<?php }else{  ?>
		<script>
		function login_submit(){
	  username=$('#username').val();
	  password=$('#password').val();
	  remember_me=$('#rememberme').val();
	  captcha=$('#g-recaptcha-response').val();
	  if(username!='' && password!=''){
		$.ajax({
				   type: "POST",
				   url: "login_validate.php",
				   data: "username="+username+"&password="+password+"&remember_me="+remember_me+"&captcha="+captcha+"&is_login=1", 
				   success: function(res)
				   {
					   console.log(res);
					   res2=$.trim(res);
					   res1=JSON.parse(res2);
						if(res1.success == 0){
							$("#message").html(res1.message);
						}else{
						    $("#message").html(res1.message);
						    setTimeout(function(){ window.location.href="home.php"; }, 2000);
							
						}
				   }
		});
	  }else{
	  }
  }
		</script>
	<?php } ?>
  </body>
</html>