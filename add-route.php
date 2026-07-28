<?php ob_start(); ?>

<?php $page="Add Route";
include"header.php";?>
 <?php 
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
		if(isset($_POST['btnAdd'])){
			if($permissions['locations']['create']==1){
			$short_name = $db->escapeString($fn->xss_clean($_POST['short_name']));
			$route_name = $db->escapeString($fn->xss_clean($_POST['route_name']));
			
			// create array variable to handle error
			$error = array();
			
			if(empty($short_name)){
				$error['short_name'] = " <span class='label label-danger'>Required!</span>";
			}	
            if(empty($route_name)){
				$error['route_name'] = " <span class='label label-danger'>Required!</span>";
			}	
			if(!empty($short_name) && !empty($route_name) ){
			    
    			$sql_query = "SELECT * FROM routes where short_code= '".trim($short_name)."'";
            	$db->sql($sql_query); 
            	$res = $db->getResult();
				if(!empty($res)){
				    $result=0;
				    $msg = "Route already exist";
				}else{
				// insert new data to menu table
				$sql_query = "INSERT INTO routes (short_code,name)
						VALUES('$short_name','$route_name')";
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
					$error['add_route'] = "<div class='content-header'>
												<span class='label label-success'>Routes Added Successfully</span>
												<h4><small><a  href='routes.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Routes</a></small></h4>
												
												</div>";
				}else{
					if(!empty($msg)){
					    $error['add_route'] = " <span class='label label-danger'>$msg</span>";
					}else{
					    $error['add_route'] = " <span class='label label-danger'>Failed add routes</span>";
					}
			    }
			}
			}else{
			$error['add_route'] = "<div class='content-header'>
												<span class='label label-danger'>You have no permission to create route</span>
												</div>";

		}
			
		}

		if(isset($_POST['btnCancel'])){
			header("location:routes.php");
		}

	?>
	 <div class="row">
		  <div class="col-md-6">
		  
			<?php echo isset($error['add_route']) ? $error['add_route'] : '';?>
		  	<?php if($permissions['locations']['create']==0){?>
		  		<div class="alert alert-danger">You have no permission to create state</div>
		  	<?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Route</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">

                    <div class="form-group">
                      <label for="exampleInputEmail1">Short Name</label><?php echo isset($error['short_name']) ? $error['short_name'] : '';?>
                      <input type="text" class="form-control"  name="short_name">
                    </div>

                    <div class="form-group">
                      <label for="exampleInputEmail1">Route Name</label><?php echo isset($error['route_name']) ? $error['route_name'] : '';?>
                      <input type="text" class="form-control"  name="route_name">
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