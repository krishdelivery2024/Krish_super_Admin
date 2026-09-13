<?php 
	include_once('includes/functions.php'); 
	$function = new functions;
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
?>
	<?php 
		if(isset($_POST['btnAdd'])){
			$item_type_name = $db->escapeString($fn->xss_clean($_POST['item_type_name']));
			
			// get image info
			$menu_image = $fn->xss_clean($_FILES['item_type_image']['name']);
			$image_error = $fn->xss_clean($_FILES['item_type_image']['error']);
			$image_type = $fn->xss_clean($_FILES['item_type_image']['type']);
			
			// create array variable to handle error
			$error = array();
			
			if(empty($item_type_name)){
				$error['item_type_name'] = " <span class='label label-danger'>Required!</span>";
			}
			
			// common image file extensions
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// get image file extension
			error_reporting(E_ERROR | E_PARSE);
			$extension = end(explode(".", $_FILES["item_type_image"]["name"]));
					
			if($image_error > 0){
				$error['item_type_image'] = " <span class='label label-danger'>Not Uploaded!!</span>";
			}else if(!(($image_type == "image/gif") || 
				($image_type == "image/jpeg") || 
				($image_type == "image/jpg") || 
				($image_type == "image/x-png") ||
				($image_type == "image/png") || 
				($image_type == "image/pjpeg")) &&
				!(in_array($extension, $allowedExts))){
			
				$error['item_type_image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
			}
			
			if(!empty($item_type_name) && empty($error['item_type_image'])){
				
				// create random image file name
				$string = '0123456789';
				$file = preg_replace("/\s+/", "_", $_FILES['item_type_image']['name']);
				$menu_image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
					
				// upload new image
				$upload = move_uploaded_file($_FILES['item_type_image']['tmp_name'], 'upload/images/'.$menu_image);
		
                if($upload) {
				    // insert new data to menu table
				    $upload_image = 'upload/images/'.$menu_image;
				    $sql_query = "INSERT INTO parcel_item_types (name,image)
						    VALUES('$item_type_name', '$upload_image')";
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
					    $error['add_item_type'] = " <div class='content-header'>
												    <span class='label label-success'>Item Type Added Successfully</span>
												    </div>";
				    }else{
					    $error['add_item_type'] = " <span class='label label-danger'>Failed to add item type</span>";
				    }
                } else {
                    $error['add_item_type'] = " <span class='label label-danger'>Image upload failed! Please check folder permissions (chmod 777) for upload/images/ on your live server.</span>";
                }
			}
		}
	?>
	 <div class="row">
		  <div class="col-md-6">
		  <?php echo isset($error['add_item_type']) ? $error['add_item_type'] : '';?>
			
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Item Type</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Item Type Name</label><?php echo isset($error['item_type_name']) ? $error['item_type_name'] : '';?>
                      <input type="text" class="form-control"  name="item_type_name" placeholder="e.g. Document, Keys & Small Items, Home Cooked Food" required>
                    </div>
                    
                    <div class="form-group">
                      <label for="exampleInputFile">Icon&nbsp;&nbsp;&nbsp;*Please choose square image.</label><?php echo isset($error['item_type_image']) ? $error['item_type_image'] : '';?>
                      <input type="file" name="item_type_image" id="item_type_image" required/>
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
	<script>
	    var uploadField = document.getElementById("item_type_image");

        uploadField.onchange = function() {
            if(this.files[0].size > 300024){
               alert("Allowed Max File size 300 KB");
               this.value = "";
            };
        };
	</script>