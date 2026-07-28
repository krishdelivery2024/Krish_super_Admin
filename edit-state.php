<?php $page="Edit State";
include"header.php";?>
<?php
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		}else{
			$ID = "";
		}
		
		// create array variable to store category data
		$category_data = array();
		
			
		if(isset($_POST['btnEdit'])){
			if($permissions['locations']['update']==1){
		    
			$city_name = $db->escapeString($fn->xss_clean($_POST['city_name']));
			// create array variable to handle error
			$error = array();
				
			if(empty($city_name)){
				$error['city_name'] = " <span class='label label-danger'>Required!</span>";
			}
				
			if(!empty($city_name) ){
				$sql_query = "SELECT * FROM state where name= '".trim($city_name)."' and id !=".$ID;
            	$db->sql($sql_query); 
            	$res = $db->getResult();
				if(!empty($res)){
				    $result=0;
				    $msg = "State already exist";
				}else{
					$sql_query = "UPDATE state 
							SET name = '".$city_name."' 
							WHERE id =".$ID;
						// Execute query
						
						$db->sql($sql_query);
						// store result 
						$update_result = $db->getResult();
						if(!empty($update_result)){
							$update_result =0;
						}else{
							$update_result =1;
						}
				}
				
				// check update result
				if($update_result==1){
					$error['update_city'] = " <div class='content-header'>
												<span class='label label-success'>State updated Successfully</span>
												<h4><small><a  href='state.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to States</a></small></h4>
												
												</div>";
				}else{
				    if(!empty($msg)){
					    $error['add_city'] = " <span class='label label-danger'>$msg</span>";
					}else{
					    $error['add_city'] = " <span class='label label-danger'>Failed update state</span>";
					}
				}
			}
				
			}else{
				$error['update_city'] = " <span class='label label-danger'>You have no permission to update state</span>";
			}

				
			}
		
				
			
		// create array variable to store previous data
		$data = array();
		
		$sql_query = "SELECT * 
				FROM state
				WHERE id =".$ID;	
			$db->sql($sql_query);
			// store result 
			
			$res=$db->getResult();
		

		if(isset($_POST['btnCancel'])) { ?>
			<script>
			window.location.href = "state.php";
		</script>
		<?php }; ?>
          <!-- Main row -->
		 
          <div class="row">
		  <div class="col-md-6">
		  <?php echo isset($error['update_city']) ? $error['update_city'] : '';?>
		  	<?php if($permissions['locations']['update']==0) { ?>
		  		<div class="alert alert-danger">You have no permission to update state</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Edit State</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">State Name</label><?php echo isset($error['city_name']) ? $error['city_name'] : '';?>
                      <input type="text" class="form-control"  name="city_name" value="<?php echo $res[0]['name']; ?>">
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
					<button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
                  </div>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>
	<div class="separator"> </div>
	
<?php $db->disconnect(); ?>
      
  
<?php include"footer.php";?>