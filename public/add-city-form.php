<?php 
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
	$sql_query = "SELECT id, name 
			FROM state ORDER BY id ASC";
			$db->sql($sql_query);
			$res_state=$db->getResult();	
			
	$sql_query = "SELECT id, name 
			FROM state where name!='Choose Your State'
			ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_city=$db->getResult();	
		
		if(isset($_POST['btnAdd'])){
			if($permissions['locations']['create']==1){
			$city_name = $db->escapeString($fn->xss_clean($_POST['city_name']));
			$state_id = $db->escapeString($fn->xss_clean($_POST['state_id']));
			
			// create array variable to handle error
			$error = array();
			if(empty($state_id)){
				$error['city_name'] = " <span class='label label-danger'>State Required!</span>";
			}
			if(empty($city_name)){
				$error['city_name'] = " <span class='label label-danger'>City Required!</span>";
			}		
			if(!empty($city_name) && !empty($state_id)){
				$sql_query = "SELECT * FROM city where name= '".trim($city_name)."' and state_id ='".trim($state_id)."'";
            	$db->sql($sql_query); 
            	$res = $db->getResult();
				if(!empty($res)){
				    $result=0;
				    $msg = "City already exist";
				}else{
				// insert new data to menu table
				$sql_query = "INSERT INTO city (state_id,name)
						VALUES('$state_id','$city_name')";
					// Execute query
					$db->sql($sql_query);
					// store result 
					$result = $db->getResult();
					
					if(!empty($result)){
						$result=0;
					}else{
						$result=1;
					}
				}
				
				if($result==1){
					$error['add_city'] = "<div class='content-header'>
												<span class='label label-success'>City Added Successfully</span>
												<h4><small><a  href='city.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Cities</a></small></h4>
												
												</div>";
				}else{
					if(!empty($msg)){
					    $error['add_city'] = " <span class='label label-danger'>$msg</span>";
					}else{
					    $error['add_city'] = " <span class='label label-danger'>Failed add city</span>";
					}
				}
			}
			}else{
			$error['add_city'] = "<div class='content-header'>
												<span class='label label-danger'>You have no permission to create city</span>
												
												
												</div>";

		}
			
		}

		if(isset($_POST['btnCancel'])){
			header("location:city-table.php");
		}

	?>
	 <div class="row">
		  <div class="col-md-6">
		  
			<?php echo isset($error['add_city']) ? $error['add_city'] : '';?>
		  	<?php if($permissions['locations']['create']==0){?>
		  		<div class="alert alert-danger">You have no permission to create city</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add City</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <?php echo isset($error['city_name']) ? $error['city_name'] : '';?>
                  <div class="box-body">
                    <div class="form-group">
						<label for="exampleInputEmail1">State :</label>
						<select name="state_id" class="form-control" required>
						<option value=''>Select Your State</option>

						<?php 
							foreach($res_state as $row){ ?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
							<?php } ?>
						</select>
					</div>
                    <div class="form-group">
                      <label for="exampleInputEmail1">City Name</label>
                      <input type="text" class="form-control"  name="city_name">
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnAdd">Add</button>
					<input type="reset" class="btn-warning btn" value="Clear"/>
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>
	<div class="separator"> </div>
	
<?php $db->disconnect(); ?>
	