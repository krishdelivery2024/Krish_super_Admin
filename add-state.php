<?php ob_start(); ?>

<?php $page="Add State";
include"header.php";?>
 <?php 
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
		if(isset($_POST['btnAdd'])){
			if($permissions['locations']['create']==1){
			$city_name = $db->escapeString($fn->xss_clean($_POST['city_name']));
			
			// create array variable to handle error
			$error = array();
			
			if(empty($city_name)){
				$error['city_name'] = " <span class='label label-danger'>Required!</span>";
			}		
			if(!empty($city_name)){
			    
    			$sql_query = "SELECT * FROM state where name= '".trim($city_name)."'";
            	$db->sql($sql_query); 
            	$res = $db->getResult();
				if(!empty($res)){
				    $result=0;
				    $msg = "State already exist";
				}else{
				// insert new data to menu table
				$sql_query = "INSERT INTO state (name)
						VALUES('$city_name')";
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
												<span class='label label-success'>State Added Successfully</span>
												<h4><small><a  href='state.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to State</a></small></h4>
												
												</div>";
				}else{
					if(!empty($msg)){
					    $error['add_city'] = " <span class='label label-danger'>$msg</span>";
					}else{
					    $error['add_city'] = " <span class='label label-danger'>Failed add state</span>";
					}
			    }
			}
			}else{
			$error['add_city'] = "<div class='content-header'>
												<span class='label label-danger'>You have no permission to create state</span>
												
												
												</div>";

		}
			
		}

		if(isset($_POST['btnCancel'])){
			header("location:state.php");
		}

	?>
	 <div class="row">
		  <div class="col-md-6">
		  
			<?php echo isset($error['add_city']) ? $error['add_city'] : '';?>
		  	<?php if($permissions['locations']['create']==0){?>
		  		<div class="alert alert-danger">You have no permission to create state</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add State</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">State Name</label><?php echo isset($error['city_name']) ? $error['city_name'] : '';?>
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
	
<?php include"footer.php";?>