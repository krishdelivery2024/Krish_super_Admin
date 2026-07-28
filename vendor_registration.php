<?php 
include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
   
    $db->sql('SELECT * FROM state');
    $states = $db->getResult();
    
    $sql = "select * from pricing_slabs";
	$db->sql($sql);
	$price_slab = $db->getResult();
     
if ((isset($_POST['btnAdd']))) {
	$name  		= (isset($_POST['name']))?$db->escapeString($_POST['name']):"";
	$user_type 	= "wholesale";
	$company_name 	= (isset($_POST['pincode']))?$db->escapeString($_POST['company_name']):"";
	$country_code  	= (isset($_POST['country_code']))?$db->escapeString($_POST['country_code']):"91";
	$mobile  	= (isset($_POST['mobile']))?$db->escapeString($_POST['mobile']):"";
	$email  	= ( isset($_POST['email']) && !empty($_POST['email']) )?$db->escapeString($_POST['email']):"";
	$pass 	= $_POST['password'];
    $password 	= md5($_POST['password']);
    $state 		= (isset($_POST['state']))?$db->escapeString($_POST['state']):"";
    $city 		= (isset($_POST['city']))?$db->escapeString($_POST['city']):"";
    $area 		= (isset($_POST['area']))?$db->escapeString($_POST['area']):"";
	$street 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$address 	= (isset($_POST['street']))?$db->escapeString($_POST['street']):"";
	$pincode 	= (isset($_POST['pincode']))?$db->escapeString($_POST['pincode']):"";
	$price_slab 	= (isset($_POST['price_slab']))?$db->escapeString($_POST['price_slab']):"";
	$invoice_type 	= (isset($_POST['invoice_type']))?$db->escapeString($_POST['invoice_type']):"1";
	//$owner_or_agent 	= (isset($_POST['pincode']))?$db->escapeString($_POST['owner_or_agent']):"";
	$gst_no 	= (isset($_POST['pincode']))?$db->escapeString($_POST['gst_no']):"";
    $status 	= 1;
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$referral_code  = "";
	for ($i = 0; $i < 10; $i++) {
	    $referral_code .= $chars[mt_rand(0, strlen($chars)-1)];
	}
	
	$friends_code = '';
	$errors = 0;
    if (!empty($mobile)) {
		$sql = "select mobile from users where mobile='".$mobile."'";
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if($num_rows > 0){
			$error['add_mobile'] = " <span class='label label-danger'>Mobile Number Already Exists</span>";
			$errors = 1;
		}
	}else{
		$error['add_mobile'] = " <span class='label label-danger'>Mobile Number Required</span>";
		$errors = 1;
	}
	if (!empty($email)) {
		$sql = "select mobile from users where email='".$email."'";
		$db->sql($sql);
		$res = $db->getResult();
		$num_rows = $db->numRows($res);
		if($num_rows > 0){
			$error['add_email'] = " <span class='label label-danger'>Email Already Exists</span>";
			$errors = 1;
		}
	}
	if($errors == 0){
			
			$data = array(
			    'user_type' => $user_type,
			    'name' => $name,
			    'company_name' => $company_name,
			    'email' => $email,
			    'mobile' => $mobile,
			    'country_code' => $country_code,
			    'state' => $state,
			    'city' => $city,
			    'area' => $area,
			    'street' => $street,
			    'address' => $address,
			    'pincode' => $pincode,
			    'price_slab' => $price_slab,
			    'password' => $password,
			    'referral_code' => $referral_code,
			    'friends_code' => $friends_code,
			    'invoice_type'=>$invoice_type,
			    //'proprietor_name' => $proprietor_name,
			    //'owner_or_agent' => $owner_or_agent,
			    'gst_no' => $gst_no,
			    'status' => $status
			);
			$db->insert('users',$data);
			$res = $db->getResult();
        	
        // 	$subject = "New Password";
        //     $message = "<p>Hi ".ucfirst($name).",</p>";
        //     $message .= "<p>Thank you for joining as customer. Your username is <b>".$mobile."</b> and Your password is: <b>".$pass."</b></p>";
        //     $message .= "<p>We look forward to seeing you soon.</p>";
        //     send_email($email,$subject,$message);
            
        //     $_SESSION['flash_message']="<span class='label label-success'>Buyer Added Successfully</span>";
		    
		    header("Location: success.php");exit();
		    
// 			$error['add_customer'] = " <div class='content-header'>
// 												<span class='label label-success'>Customer Added Successfully</span>
												
// 												<a href='customers.php'></a>
// 												</div>";
		}
    }
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <title>Hello, world!</title>
  </head>
  <body>
    <div class="container mt-4">
    <div class="card">
      <div class="card-header">
       <h4 class="text-center">Vendor Registration</h4> 
      </div>
      <div class="card-body">
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <?php echo isset($error['add_customer']) ? $error['add_customer'] : '';?>
                  <?php echo isset($error['add_mobile']) ? $error['add_mobile'] : '';?>
                  <?php echo isset($error['add_email']) ? $error['add_email'] : '';?>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" id="add_form" action="">
                    <input type="hidden" id="add_customer" name="add_customer" required="" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="">Name</label>
                      <input type="text" class="form-control"  name="name" required>
                    </div>
                    
                    <div class="form-group">
                      <label for="">Company Name</label>
                      <input type="text" class="form-control"  name="company_name" required>
                    </div>
                    
                    <div class="form-group">
                      <label for="">Mobile</label>
                      <input type="text" class="form-control"  name="mobile" id="mobile"  >
                    </div>
                    <div class="form-group">
                      <label for="">Email<small>(optional)</small></label>
                      <input type="email" class="form-control"  name="email" id="email" >
                    </div>
                    <div class="form-group">
                      <label for="">Password</label>
                      <input type="password" class="form-control"  name="password" id="password">
                    </div>
                    
                    
                    <!--                    <div class="form-group">-->
                    <!--  <label for="">Proprietor Name</label>-->
                    <!--  <input type="text" class="form-control"  name="proprietor_name" required>-->
                    <!--</div>-->
                    <!--                    <div class="form-group">-->
                    <!--  <label for="">Owner / Agent</label>-->
                    <!--  <select class="form-control"  name="owner_or_agent" required>-->
                    <!--      <option value="Owner">Owner</option>-->
                    <!--                                <option value="Agent">Agent</option>-->
                    <!--      </select>-->
                    <!--</div>-->
                    
                    <div class="form-group">
                      <label for="">GST Number<small>(optional)</small></label>
                      <input type="text" class="form-control"  name="gst_no" >
                    </div>
                    
                    <div class="form-group">
                      <label for="">State</label>
                      <Select class="form-control"  name="state" id="state" required>
                          <option value=''></option>
                        <?php foreach($states as $state){
                            echo "<option value='".$state['id']."'>".$state['name']."</option>";
                        }?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="">City</label>
                      <Select class="form-control"  name="city" id="city" required>
                        </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="">Area</label>
                      <Select class="form-control"  name="area" id="area">
                        </select>
                    </div>
                    
                    <div class="form-group">
                      <label for="">Address</label>
                      <textarea  class="form-control"  name="street" required></textarea>
                    </div>
                    
                    <div class="form-group">
                      <label for="">Pincode</label>
                      <input type="number"  class="form-control"  name="pincode" id="" required>
                    </div>
                    
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                    <input type="reset" class="btn-warning btn" value="Clear"/>
                
                  </div>
                  <div class="form-group">
                      
                      <div id="result" style="display: none;"></div>
                    </div>
                </form>
              </div><!-- /.box -->
             </div>
        <!-- Left col -->
        <div class="separator"> </div>
    </div>
      </div>
    </div>
    </div>

    

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

    <!-- Option 2: Separate Popper and Bootstrap JS -->
    <!--
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>
    -->
    <script src="dist/scripts/jquery.min.js"></script>
    <script>
$(document).on('change','#state',function(){
//   alert('change');
    $.ajax({
       url:'public/db-operation.php',
       method:'POST',
       data:'state_id='+$('#state').val()+'&find_city=1',
       success:function(data){
          // alert(data);
           $('#city').html(data);
       }
    });
});

$(document).on('change','#city',function(){
    $.ajax({
       url:'public/db-operation.php',
       method:'POST',
       data:'city_id='+$('#city').val()+'&find_area=1',
       success:function(data){
          // alert(data);
           $('#area').html(data);
       }
    });
});

$('#add_form').validate({
    rules:{
        mobile: {
            required:true,
            remote: {
                type:'post',
                url:'public/db-operation.php',
                data:{
                    'field': $('#mobile').attr('name'),
                    'validate_unique':1
                },
            }
        },
        email: {
            email:true,
            remote: {
                url:'public/db-operation.php',
                method:'POST',
                data:{
                    'field': $('#email').attr('name'),
                    'validate_unique':1,
                },
            }
        }
        
    },
    messages: {
                mobile: {
                   remote: 'Mobile already taken'
                },
                email: {
                        remote: 'E-Mail already taken'
                    }
            }
});

$(document).ready(function() { 
         
    $('#mobile').on('focusout',function(){
        $("#add_form").validate().element('#mobile');
    });
    
    $('#email').on('focusout',function(){
        $("#add_form").validate().element('#email');
    });
}); 
</script>
  </body>
</html>
