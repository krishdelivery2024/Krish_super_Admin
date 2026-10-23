<?php
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $function = new Functions;

    if(isset($_GET['id'])){
        $ID = $db->escapeString($fn->xss_clean($_GET['id']));
    }else{
        return false;
        exit(0);
    }

    $sql = "SELECT * FROM users WHERE id = '".$ID."'";
    $db->sql($sql);
    $res = $db->getResult();

    if(empty($res)){
        echo "<div class='alert alert-danger'>Customer not found!</div>";
        return false;
    }

    // Resolve string values for state, city, area
    $user_state = trim($res[0]['state'] ?? '');
    if (is_numeric($user_state) && intval($user_state) > 0) {
        $db->sql("SELECT name FROM state WHERE id = " . intval($user_state));
        $s_row = $db->getResult();
        if (!empty($s_row[0]['name'])) {
            $user_state = $s_row[0]['name'];
        }
    }

    $user_city = trim($res[0]['city'] ?? '');
    if (is_numeric($user_city) && intval($user_city) > 0) {
        $db->sql("SELECT name FROM city WHERE id = " . intval($user_city));
        $c_row = $db->getResult();
        if (!empty($c_row[0]['name'])) {
            $user_city = $c_row[0]['name'];
        }
    }

    $user_area = trim($res[0]['area'] ?? '');
    if (is_numeric($user_area) && intval($user_area) > 0) {
        $db->sql("SELECT name FROM area WHERE id = " . intval($user_area));
        $a_row = $db->getResult();
        if (!empty($a_row[0]['name'])) {
            $user_area = $a_row[0]['name'];
        }
    }
?>  
<style>
    .form-group {
    margin-bottom: 15px;
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
.box-body1 {
    background: white;
    width: 50%;
    height: 750px;
    float: right;
    border-left: 1px solid black;
}
.statusupdate {
    margin-left: 20px;
    font-size: 20px;
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
        <form method="post" id="customerEditForm" enctype="multipart/form-data">
        <input type="hidden" class="customerid" value="<?php echo $res[0]['id']; ?>" />
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Name</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="name" class="form-control name1" value="<?php echo htmlspecialchars($res[0]['name'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Email</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="email" class="form-control email" value="<?php echo htmlspecialchars($res[0]['email'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Mobile Number</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="mobile" class="form-control mobile" value="<?php echo htmlspecialchars($res[0]['mobile'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">DOB</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="dob" class="form-control dob" value="<?php echo htmlspecialchars($res[0]['dob'] ?? ''); ?>"/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">State</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="state" class="form-control state" value="<?php echo htmlspecialchars($user_state); ?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">City</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="city" class="form-control city" value="<?php echo htmlspecialchars($user_city); ?>" required/>
            </div>
        </div>  
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Area</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="area" class="form-control area" value="<?php echo htmlspecialchars($user_area); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Street</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="street" class="form-control street" value="<?php echo htmlspecialchars($res[0]['street'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Pincode</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="pincode" class="form-control pincode" value="<?php echo htmlspecialchars($res[0]['pincode'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Balance</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="balance" class="form-control balance" value="<?php echo htmlspecialchars($res[0]['balance'] ?? '0'); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Created At</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="created_at" class="form-control created_at" value="<?php echo htmlspecialchars($res[0]['created_at'] ?? ''); ?>" required readonly/>
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
      $('#customerEditForm').on('submit', function(e){
        e.preventDefault();
        var id = $('.customerid').val();
        var name = $('.name1').val();
        var email = $('.email').val();
        var mobile = $('.mobile').val();
        var dob = $('.dob').val();
        var state = $('.state').val();
        var city = $('.city').val();
        var area = $('.area').val();
        var street = $('.street').val();
        var pincode = $('.pincode').val();
        var balance = $('.balance').val();

        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Updating...');

        $.ajax({
            url: 'public/updatecustomerdata.php',
            type: 'POST',
            dataType: 'json',
            data: {
                'id': id,
                'name': name,
                'email': email,
                'mobile': mobile,
                'dob': dob,
                'state': state,
                'city': city,
                'area': area,
                'street': street,
                'pincode': pincode,
                'balance': balance
            },
            success: function(result){
                submitBtn.prop('disabled', false).text('Edit Customer');
                alert('Customer updated successfully!');
                location.reload();
            },
            error: function(xhr, status, error){
                submitBtn.prop('disabled', false).text('Edit Customer');
                alert('Customer updated successfully!');
                location.reload();
            }
        });
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
              }
        });
     });    
});
</script>