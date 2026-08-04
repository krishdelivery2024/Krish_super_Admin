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
	<title>Seller Login - <?=$settings['app_name']?></title>
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
			<img src="<?=isset($res_logo)?DOMAIN_URL.'dist/img/'.$res_logo[0]['value']:''?>" height="70px">
			<h3>Seller Login <?=$settings['app_name']?></h3>
		</div>
			<div id="login_frm">
				<input type="hidden"  name="mobile" id="mobile" value="<?=$_GET['mobile']; ?>" >
			<div class="frm-input"><input type="tel"  name="otp" id="otp" placeholder="OTP" class="frm-inp" required><i class="fa fa-user frm-ico" required></i></div>
			<!-- /.frm-input -->
			
			<!-- /.clearfix -->
			<button id="sign-in-button" onclick="login_submit()" class="frm-submit">Submit<i class="fa fa-arrow-circle-right"></i></button>
			</div>
			<!-- /.frm-input -->
			<p id="message"></p>
			<div class="frm-footer text-center"><?=$settings['app_name']?> © <?=date('Y')?>.</div>
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
<script>
function login_submit() {
    let mobile = $('#mobile').val();
    let otp = $('#otp').val();
    if (otp.trim() !== '') {
        $.ajax({
            type: "POST",
            url: "seller_login_validate.php",
            data: {
                mobile: mobile,
                otp: otp,
                is_login_verify: 1
            },
            success: function(res) {
                try {
                    let res1 = JSON.parse(res.trim());
                    $("#message").html(res1.message);
                    if (res1.success == 1) {
                        setTimeout(() => {
                            window.location.href = "home.php";
                        }, 500);
                    }
                } catch (e) {
                    $("#message").html("<span class='label label-danger'>Unexpected server response.</span>");
                }
            },
            error: function() {
                $("#message").html("<span class='label label-danger'>Network error.</span>");
            }
        });
    } else {
        $("#message").html("<span class='label label-warning'>Please enter OTP.</span>");
    }
}
</script>
  </body>
</html>