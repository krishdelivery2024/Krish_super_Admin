<?php
	include_once('includes/functions.php');
	include_once('includes/custom-functions.php');
    $fn = new custom_functions; 

		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		}else{
			$ID = "";
		}
		
		// create array variable to store item type data
		$item_type_data = array();
			
		$sql_query = "SELECT image 
				FROM parcel_item_types 
				WHERE id =".$ID;
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
		if(isset($_POST['btnEdit'])){
		    $name = $db->escapeString($fn->xss_clean($_POST['name']));
			// get image info
			$menu_image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
			$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
			$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
				
			// create array variable to handle error
			$error = array();
				
			if(empty($name)){
				$error['name'] = " <span class='label label-danger'>Required!</span>";
			}
			
			// common image file extensions
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			
			// get image file extension
			error_reporting(E_ERROR | E_PARSE);
			$extension = end(explode(".", $_FILES["image"]["name"]));
			
			if(!empty($menu_image)){
				if(!(($image_type == "image/gif") || 
					($image_type == "image/jpeg") || 
					($image_type == "image/jpg") || 
					($image_type == "image/x-png") ||
					($image_type == "image/png") || 
					($image_type == "image/pjpeg")) &&
					!(in_array($extension, $allowedExts))){
					
					$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
				}
			}
				
			if(!empty($name) && empty($error['image'])){
					
				if(!empty($menu_image)){
					
					// create random image file name
					$string = '0123456789';
					$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
					$function = new functions;
					$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
				
					// delete previous image
					if(!empty($res[0]['image'])){
					    $delete = @unlink($res[0]['image']);
					}
					
					// upload new image
					$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/'.$image);
	  				$upload_image = 'upload/images/'.$image;
					$sql_query = "UPDATE parcel_item_types 
							SET name = '".$name."', image = '".$upload_image."'
							WHERE id = ".$ID;
					$db->sql($sql_query);
					$update_result = $db->getResult();
				}else{
					
					$sql_query = "UPDATE parcel_item_types 
							SET name = '".$name."', image = '".$res[0]['image']."'
							WHERE id =".$ID;
						// Execute query
						$db->sql($sql_query);
						// store result 
						$update_result = $db->getResult();
				}

						if(!empty($update_result)){
							$update_result=0;
						}
						else{
							$update_result=1;
						}
				
				// check update result
				if($update_result==1){
					$error['update_item_type'] = " <div class='content-header'>
												<span class='label label-success'>Item Type updated Successfully</span>
												<h4><small><a  href='parcel-item-types.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Item Types</a></small></h4>
												
												</div>";
				}else{
					$error['update_item_type'] = " <span class='label label-danger'>Failed to update item type</span>";
				}
			}
		}
			
		// create array variable to store previous data
		$data = array();
		
		$sql_query = "SELECT * 
				FROM parcel_item_types 
				WHERE id =".$ID;
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
	

		if(isset($_POST['btnCancel'])){?>
			<script>
			window.location.href = "parcel-item-types.php";
		</script>
		<?php } ?>
          <!-- Main row -->
		 
          <div class="row">
		  <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Edit Item Type</h3>
                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post"
			enctype="multipart/form-data">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="exampleInputEmail1">Item Type Name</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                      <input type="text" class="form-control"  name="name" value="<?php echo $res[0]['name']; ?>">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputFile">Icon&nbsp;&nbsp;&nbsp;*Please choose square image.</label><?php echo isset($error['image']) ? $error['image'] : '';?>
                      <input type="file" name="image" id="image" title="Please choose square image."/>
                      <p class="help-block"><img src="<?php echo $res[0]['image']; ?>" width="150" height="150"/></p>
                    </div>
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnEdit">Update</button>
					<button type="submit" class="btn btn-danger" name="btnCancel">Cancel</button>
                  </div>
				  <?php echo isset($error['update_item_type']) ? $error['update_item_type'] : '';?>
                </form>
              </div><!-- /.box -->
			 </div>
		  </div>
	<div class="separator"> </div>
	<script>
	    var uploadField = document.getElementById("image");

        uploadField.onchange = function() {
            if(this.files[0].size > 300024){
               alert("Allowed Max File size 300 KB");
               this.value = "";
            };
        };
	</script>
<?php $db->disconnect(); ?>