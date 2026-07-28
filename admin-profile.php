<?php $page="My Profile";
include"header.php";?>
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper">
	<?php $username = $_SESSION['user'];
	$id=$_SESSION['id'];
	$sql_query = "SELECT username, mobile,password, email FROM admin 
	WHERE id ='".$id."'";
	// create array variable to store previous data
	$data = array();			
		// Execute query
		$db->sql($sql_query);
		// store result 
		$res=$db->getResult();
	$previous_password = $res[0]['password'];
	$previous_email = $res[0]['email'];
	
	if(isset($_POST['btnChange'])){
		$email = $_POST['email'];
		$update_username = $_POST['username'];
		$mobile = $_POST['mobile'];
		$old_password = md5($_POST['old_password']);
		$new_password = md5($_POST['new_password']);
		$confirm_password = md5($_POST['confirm_password']);
		// create array variable to handle error
		$error = array();
		// check password
		if(!empty($_POST['old_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])){
			if(!empty($_POST['old_password'])){				
				if($old_password == $previous_password){
					if($new_password == $confirm_password){
						// update password in user table
						if(!empty($_POST['new_password'])){
							$sql_query = "UPDATE admin 
							SET `password` = '".$new_password."',`username`='".$update_username."',`mobile`='".$mobile."',`email`='".$email."'
							WHERE `username` ='".$username."'";
						}else{
						    $sql_query = "UPDATE admin 
							SET `username`='".$update_username."',`mobile`='".$mobile."',`email`='".$email."'
							WHERE `username` ='".$username."'";
						}
						
						// Execute query
						$db->sql($sql_query);
						// store result 
						$update_result = $db->getResult();
						if(!empty($_POST['new_password'])){
				?>
				<script>alert("Password Changed!");window.location = "logout.php";</script>
				<?php }
					}else{
						$error['confirm_password'] = " <span class='label label-danger'>New password dosen't match!</span>";
					}
				}else{
					$error['old_password'] = " <span class='label label-danger'>Current password wrong!</span>";
				}
			}
		}else{
		    
		    $sql_query = "UPDATE admin 
							SET `username`='".$update_username."',`mobile`='".$mobile."',`email`='".$email."'
							WHERE `username` ='".$username."'";
						$db->sql($sql_query);
						if($update_username!=$username){
            		        echo '<script>alert("Profile Updated!");;window.location = "logout.php";</script>';
            		    }else{
            		        echo '<script>alert("Profile Updated!");</script>';
            		    }
						
		}
		
	}		

			$sql_query = "SELECT username, mobile, email  FROM admin WHERE id ='".$id."'";	
 			//echo $sql_query;
 				// Execute query
 				$db->sql($sql_query);
 				// store result 
 				$res_email = $db->getResult();				
	?>

		<?php echo isset($error['update_user']) ? $error['update_user'] : '';?>
          <div class="row">
		  <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Change Password</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form id='change_password_form' method="post" enctype="multipart/form-data">
				<div class="box-body">
				    <div class="form-group">
						<span class="label label-primary">If you change username or password you will need to login again.</span>
					</div>
                   <div class="form-group">
						<label for="exampleInputEmail1">Username : </label>
						<input type="text" class="form-control" name="username" id="disabledInput" value="<?php echo $username; ?>"/>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">Email :</label><?php echo isset($error['email']) ? $error['email'] : '';?>
						<input type="email" class="form-control" name="email" value="<?php echo $res_email[0]['email']; ?>"/>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">Mobile :</label><?php echo isset($error['mobile']) ? $error['mobile'] : '';?>
						<input type="number" class="form-control" name="mobile" value="<?php echo $res_email[0]['mobile']; ?>"/>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">Current Password :</label><?php echo isset($error['old_password']) ? $error['old_password'] : '';?>
						<input type="password" class="form-control" name="old_password"/>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">New Password :</label><?php echo isset($error['new_password']) ? $error['new_password'] : '';?>
						<input type="password" class="form-control" name="new_password" id="new_password"/>
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">Re Type New Password :</label><?php echo isset($error['confirm_password']) ? $error['confirm_password'] : '';?>
						<input type="password" class="form-control" name="confirm_password"/>
					</div>
					<div class="box-footer">
						<input type="submit" class="btn-primary btn" value="Change" name="btnChange"/>
					</div>
				</div><!-- /.box -->
				</form>
			</div>
		  </div>
	</div>
	<div class="separator"> </div>

  
<?php include"footer.php";?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
/*$('#change_password_form').validate({
	rules:{
		username:"required",
		old_password:"required",
		email:"required",
		new_password:{minlength:6},
		confirm_password:{minlength:6},
	}
});*/
</script>