<?php $page="Add Brand";
include"header.php";?>

<?php 
	include_once('includes/functions.php'); 
	$function = new functions;
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
?>
	<?php 
		if(isset($_POST['btnAdd'])){
			if($permissions['categories']['create']==1){

			$brand_name = $db->escapeString($fn->xss_clean($_POST['brand_name']));
			
			// get image info
			$menu_image = $fn->xss_clean($_FILES['brand_image']['name']);
			$image_error = $fn->xss_clean($_FILES['brand_image']['error']);
			$image_type = $fn->xss_clean($_FILES['brand_image']['type']);
			
			// create array variable to handle error
			$error = array();
			
			if(empty($brand_name)){
				$error['brand_name'] = " <span class='label label-danger'>Required!</span>";
			}
			
			// common image file extensions
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// get image file extension
			error_reporting(E_ERROR | E_PARSE);
			$extension = end(explode(".", $_FILES["brand_image"]["name"]));
					
			if($image_error > 0){
				$error['brand_image'] = " <span class='label label-danger'>Not Uploaded!!</span>";
			}else if(!(($image_type == "image/gif") || 
				($image_type == "image/jpeg") || 
				($image_type == "image/jpg") || 
				($image_type == "image/x-png") ||
				($image_type == "image/png") || 
				($image_type == "image/pjpeg")) &&
				!(in_array($extension, $allowedExts))){
			
				$error['brand_image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
			}
			
			if(!empty($brand_name) && empty($error['brand_image'])){
				
				// create random image file name
				$string = '0123456789';
				$file = preg_replace("/\s+/", "_", $_FILES['brand_image']['name']);
				// $function = new functions;
				$menu_image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
					
				// upload new image
				$upload = move_uploaded_file($_FILES['brand_image']['tmp_name'], 'upload/images/'.$menu_image);
		
				// insert new data to menu table
				$upload_image = 'upload/images/'.$menu_image;
				$sql_query = "INSERT INTO brand (name, image)
						VALUES('$brand_name', '$upload_image')";
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
					$error['add_brand'] = " <div class='content-header'>
												<span class='label label-success'>Brand Added Successfully</span>
												
												
												</div>";
				}else{
					$error['add_brand'] = " <span class='label label-danger'>Failed add Brand</span>";
				}
			}
		}else{
			$error['check_permission'] = " <div class='content-header'>
												<span class='label label-danger'>You have no permission to create Brand</span>
												
												
												</div>";
		}
		}
		if(isset($_POST['btnCancel'])){?>
			<script>
			window.location.href = "brands.php";
		</script>
		<?php } ?>
	?>
	 <div class="row">
		  <div class="col-md-6">
		  <?php echo isset($error['add_brand']) ? $error['add_brand'] : '';?>
			
		  	<?php if($permissions['categories']['create']==0) { ?>
        	<div class="alert alert-danger">You have no permission to create Brand.</div>
        <?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Brand</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Brand Name</label><?php echo isset($error['brand_name']) ? $error['brand_name'] : '';?>
                      <input type="text" class="form-control"  name="brand_name" required>
                    </div>
                    
                    <div class="form-group">
                      <label for="exampleInputFile">Brand Image&nbsp;&nbsp;&nbsp;<br>*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['brand_image']) ? $error['brand_image'] : '';?>
                      <input type="file" name="brand_image" id="brand_image" required/>
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnAdd">Add</button>
					
					<a href="brands.php" class="btn btn-danger">Cancel</a>
				
                  </div>

                </form>

              </div><!-- /.box -->
              <?php echo isset($error['check_permission']) ? $error['check_permission'] : '';?>
			 </div>
		  </div>

	<div class="separator"> </div>
	
<?php $db->disconnect(); ?>
	
      
  
<?php include"footer.php";?>