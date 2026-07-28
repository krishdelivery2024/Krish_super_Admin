<?php 
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
?>
	<?php 
			$sql_query = "SELECT id, name 
			FROM city where name!='Choose Your City'
			ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_city=$db->getResult();	
		

			$sql_query = "SELECT id, short_code 
			FROM routes 
			ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_route=$db->getResult();

			
		
		// get currency symbol from setting table		
		if(isset($_POST['btnAdd'])){
			if($permissions['locations']['create']==1){
			$area_name = $db->escapeString($fn->xss_clean($_POST['area_name']));
			$pincode = $db->escapeString($fn->xss_clean($_POST['pincode']));
			$city_ID = $db->escapeString($fn->xss_clean($_POST['city_ID']));
			$route_id = $db->escapeString($fn->xss_clean($_POST['route_id']));
			$route_seq = $db->escapeString($fn->xss_clean($_POST['route_seq']));

			
			$sql_query = "SELECT * 
			FROM area WHERE city_id=".$city_ID;	
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_area=$db->getResult();
				$TOTAL=$db->numRows($res_area);
			// create array variable to handle error
			$error = array();
			
			if(empty($area_name)){
				$error['area_name'] = " <span class='label label-danger'>Required!</span>";
			}
				
			if(empty($city_ID) || $city_ID == '' ){
				$error['city_ID'] = " <span class='label label-danger'>Required!</span>";
			}

			if(!empty($route_seq) && (empty($route_id) || $route_id == '')){
				$error['route_id'] = " <span class='label label-danger'>Required!</span>";
			}

			if(!empty($route_id) && empty($route_seq)){
				$error['route_seq'] = " <span class='label label-danger'>Required!</span>";
			}

			if(!empty($route_id) && !empty($route_seq)){
				$sql_query = "SELECT * FROM area WHERE route_id=".$route_id." and route_seq=".$route_seq;	
				// Execute query
				$db->sql($sql_query);
				// store result 
				$res_seq=$db->getResult();
				if(!empty($res_seq)){
					$error['route_seq'] = " <span class='label label-danger'>Route Seqeuence already Exist!</span>";
				}
			}

				
				
				if(empty($error) && !empty($area_name) && !empty($city_ID) ){	
				// create random image file name
				// insert new data to menu table
				$sql_query = "INSERT INTO area (name, pincode, city_id,route_id,route_seq)
						VALUES('$area_name', '$pincode', '$city_ID', '$route_id', '$route_seq')";
					// Execute query
					$db->sql($sql_query);
					// store result 
					$result = $db->getResult();
					if(!empty($result)){
						$result=0;
					}else{
						$result=1;
					}
				
				
				if($result==1){
					$error['add_area'] = "<div class='content-header'>
												<span class='label label-success'>Area Added Successfully</span>
												<h4><small><a  href='areas.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Areas</a></small></h4>
												
												</div>";
				}else {
					$error['add_area'] = " <span class='label label-danger'>Failed</span>";
				}
			}
			
		}else{
			$error['add_area'] = "<div class='content-header'>
												<span class='label label-danger'>You have no permission to create area</span>
												</div>";

			}
		}
	?>

<div class="row">
		  <div class="col-md-6">
		  <?php echo isset($error['add_area']) ? $error['add_area'] : '';?>
		  	<?php if($permissions['locations']['create']==0){?>
		  		<div class="alert alert-danger">You have no permission to create area</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Area</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
						<label for="exampleInputEmail1">City :</label><?php echo isset($error['city_ID']) ? $error['city_ID'] : '';?>
						<select name="city_ID" class="form-control" required>
						<option value=''>Select Your City</option>

						<?php 
						if($permissions['locations']['read']==1){
							foreach($res_city as $row){ ?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
							<?php } }?>
						</select>
						<br/>
					</div>

					<div class="form-group">
                      <label for="exampleInputEmail1">Area Name</label><?php echo isset($error['area_name']) ? $error['area_name'] : '';?>
                      <input type="text" class="form-control"  name="area_name" required/>
                    </div>
                    
                    <div class="form-group">
                      <label for="exampleInputEmail1">Pincode</label><?php echo isset($error['pincode']) ? $error['pincode'] : '';?>
                      <input type="number" class="form-control"  name="pincode" />
                    </div>

					<div class="form-group">
						<label for="exampleInputEmail1">Route :</label><?php echo isset($error['route_id']) ? $error['route_id'] : '';?>
						<select name="route_id" class="form-control">
						<option value=''>Select Your Route</option>

						<?php 
						if($permissions['locations']['read']==1){
							foreach($res_route as $row){ ?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['short_code']; ?></option>
							<?php } }?>
						</select>
						<br/>
					</div>
					
					<div class="form-group">
                      <label for="exampleInputEmail1">Route Seq</label><?php echo isset($error['route_seq']) ? $error['route_seq'] : '';?>
                      <input type="number" class="form-control"  name="route_seq" />
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <input type="submit" class="btn-primary btn" value="Add" name="btnAdd"/>&nbsp;
					<input type="reset" class="btn-danger btn" value="Clear"/>
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>

	<div class="separator"> </div>
	
<?php $db->disconnect(); ?>