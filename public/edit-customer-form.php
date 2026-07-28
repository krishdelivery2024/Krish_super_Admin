<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" integrity="sha256-ze/OEYGcFbPRmvCnrSeKbRTtjG4vGLHXgOqsyLFTRjg=" crossorigin="anonymous" />
<script> 
  $(document).ready(function () {
      $('select').selectize({
          sortField: 'text'
      });
    //   $(".city").change(function(){
    //   var cityid=$('.city').val();
    //   $.ajax({
    //       url:'https://spiderekart.in/EUdev/public/updatecustomerdata.php',
    //       type: 'POST',
    //       dataType: 'json',
    //       data: {'cityid':cityid},
    //           success: function(result){
    //       }});
    //   });
  });
</script>
<?php
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    // include_once('includes/crud.php');
    $function = new Functions;
    // $db = new Database();
    if(isset($_GET['id'])){
        $ID = $db->escapeString($fn->xss_clean($_GET['id']));
    }else{
        // $ID = "";
        return false;
        exit(0);
    }
    // create array variable to store category data
    
    $sql = "SELECT * FROM users WHERE id =".$ID;
    $db->sql($sql);
    $res = $db->getResult();

    $sql_query = "SELECT id, name FROM city where name!='Choose Your City' ORDER BY id ASC";
			
	// Execute query
	$db->sql($sql_query);
	// store result 
	$res_city=$db->getResult();	
	
	$areaid=$res[0]['area'];
	$sql_query = "SELECT id, name FROM area where id=".$areaid;
			
	// Execute query
	$db->sql($sql_query);
	// store result 
	$res_area=$db->getResult();	
	
	
	
// 	$sql_query = "SELECT id, name FROM area where city_id =".$res[0]['city'];
	$sql_query = "SELECT id, name FROM area";	
	// Execute query
	$db->sql($sql_query);
	// store result 
	$res_area_whole=$db->getResult();
	
// 	print_r($res_area_whole);
// 	exit;
	
	
// 	print_r($res_area[0]['name']);
// 	exit;
	
    // echo"<pre>";
    
    // print_r($res[0]['name']);
    // print_r($res);
    // exit;
?>  
<style>
    .form-group {
    margin-bottom: 15px;
    /*DISPLAY: INLINE-FLEX;*/
}
.box-body{
    width: 50%;
    BACKGROUND: WHITE;
    padding-top: 20px;
    padding-bottom: 20px;
}
.col-md-6 {
    width: 50%;
    display: inline-grid;
}
input.form-control {
    width: 150%;
    margin-bottom: 10px;
    margin-left: -136px;
}
select.form-control {
    width: 150%;
    margin-bottom: 10px;
    margin-left: -136px;
}
.sub{
    width:20%;
    margin:auto;
}
button {
    background: #3F51B5;
    color: white;
}
.selectize-control.form-control{
    width:150%;
    margin-bottom: 10px;
    margin-left: -136px;
}
.box-body1 {
    background: white;
    width: 50%;
    height: 695px;
    float: right;
    border-left: 1px solid black;
    
}
.statusupdate {
    margin-left: 20px;
    font-size: 20px;
    /*color: red;*/
}
</style>
<div class="row rpm">
                <div class="box-body1">
                    <h2 style="text-align:center;margin-top: 40px;">CUSTOMER PASSWORD CHANGE</h2>
                    <div class="form-group">
                       <div class="col-md-6">
                          <label for="exampleInputEmail1">New Password</label>
                        </div>
                       <div class="col-md-6">
                           <input type="password" name="newpassword" class="form-control newpassword" value="" required/>
                        </div>
                    </div>
                    <div class="form-group">
                       <div class="col-md-6">
                          <label for="exampleInputEmail1">Confirm Password</label>
                        </div>
                       <div class="col-md-6">
                           <input type="password" name="confirmpassword" class="form-control confirmpassword" value="" required/>
                       </div>
                    </div>
                    <div class="sub">
                      <button type="submit" id="changepassword" value="Change Password">Change Password</button>
                    </div>
                    <div class="statusupdate"></div>
            </div>
            <div class="box-body">
                <h2 style="text-align:center">CUSTOMER DETAILS EDIT</h2>
                <form method="post" enctype="multipart/form-data">
                <input type="hidden" class="customerid" value="<?php echo $res[0]['id']; ?>" />
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Name</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="name" class="form-control name1" value="<?php echo $res[0]['name']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Email</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="email" class="form-control email" value="<?php echo $res[0]['email']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Mobile Number</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="mobile" class="form-control mobile" value="<?php echo $res[0]['mobile']; ?>" required/>
                    </div>
                </div>
                 <div class="form-group">
                     <div class="col-md-6">
                    <label for="exampleInputEmail1">DOB</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="dob" class="form-control dob" value="<?php echo $res[0]['dob']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">City</label>
                    </div>
                    <div class="col-md-6">
                    <select name="city" class="form-control city" required>
						<option value=''>Select Your City</option>
						<?php 
						if($permissions['locations']['read']==1){
							foreach($res_city as $row){ ?>
							<option value="<?php echo $row['id']; ?>"<?php 
            if($row['id'] == $res[0]['city']) { 
            echo " selected"; 
            } ?>><?php echo $row['name']; ?></option>
						<?php } }?>
					</select>
                    </div>
                </div>  
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Area</label>
                    </div>
                    <div class="col-md-6">
                    <!--<input type="text" name="area" class="form-control area" value="<?php echo $res_area[0]['name']; ?>" required/>-->
                    <select name="area" class="form-control area" required>
						<option value=''>Select Your Area</option>
						<?php 
						if($permissions['locations']['read']==1){
							foreach($res_area_whole as $row){ ?>
							<option value="<?php echo $row['id']; ?>"<?php 
                              if($row['id'] == $res[0]['area']) { 
                              echo " selected"; 
                              } ?>><?php echo $row['name']; ?></option>
						<?php } }?>
					</select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Street</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="street" class="form-control street" value="<?php echo $res[0]['street']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Pincode</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="pincode" class="form-control pincode" value="<?php echo $res[0]['pincode']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Balance</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="balance" class="form-control balance" value="<?php echo $res[0]['balance']; ?>" required/>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                    <label for="exampleInputEmail1">Created At</label>
                    </div>
                    <div class="col-md-6">
                    <input type="text" name="created_at" class="form-control created_at" value="<?php echo $res[0]['created_at']; ?>" required readonly/>
                    </div>
                </div>
                <div class="sub">
                <button type="submit" value="Edit Customer">Edit Customer</button>
                </div>
                </form>
            </div>
            </div>
<script>
    $(document).ready(function() {
      $('form').on('submit', function(e){
        e.preventDefault();
        var id=$('.customerid').val();
        var name=$('.name1').val();
        var email=$('.email').val();
        var mobile=$('.mobile').val();
        var dob=$('.dob').val();
        var city=$('.city').val();
        var area=$('.area').val();
        var street=$('.street').val();
        // alert(street);
        var pincode=$('.pincode').val();
        var balance=$('.balance').val();
         $.ajax({
           url:'public/updatecustomerdata.php',
           type: 'POST',
           dataType: 'json',
           data: {'id':id,'name':name,'email':email,'mobile':mobile,'dob':dob,'city':city,'area':area,'street':street,'pincode':pincode,'balance':balance},
              success: function(result){
          }});
        });
        
      $('#changepassword').on('click',function(e){
        var newpassword = $('.newpassword').val();
        var confirmpassword = $('.confirmpassword').val();
        var id=$('.customerid').val();
        $.ajax({
          url:"public/db-operation.php",
          data:"newpassword="+newpassword+"&confirmpassword="+confirmpassword+"&customerid="+id+"&changecustomerpass=1",
           method:"POST",
           success:function(data){
               if(data=="password incorrect"){
                   $('.statusupdate').css("color", "red");
               }else{
                   $('.statusupdate').css("color", "green");
               }
               
               $('.statusupdate').html(data);
               setTimeout(function(){ 
                 $('.statusupdate').fadeOut();}, 2000); 
               setTimeout(function(){ 
               location.reload(true);}, 2500);
            //   $('#contentLeft1 ul').html(data);
            //   $('#subcategory_id').val(subcategory_id);
              }
        });
     });    
});
</script>
            