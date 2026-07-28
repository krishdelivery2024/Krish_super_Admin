<?php
include_once('includes/functions.php'); 
	date_default_timezone_set('Asia/Kolkata');
	$function = new functions;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	if($_FILES['file']['name']==='') {
		$error = "File Upload Failed";
	} else {
		if(in_array($_FILES['file']['type'],$csvMimes)){
			if(is_uploaded_file($_FILES['file']['tmp_name'])){
				
				// check file size
				if(filesize($_FILES['file']['tmp_name']) > 2000000) {
					$error = "File Size Exceed";
				} else {
					
					//open uploaded csv file with read only mode
					$csvFile = fopen($_FILES['file']['tmp_name'], 'r');
					
					//skip first line
					fgetcsv($csvFile);
					
					//parse data from csv file line by line
					while(($line = fgetcsv($csvFile)) !== FALSE){
						$category_name = $db->escapeString($fn->xss_clean($line[0]));
						echo $category_name;
						exit;
						$sql="SELECT id from products ORDER BY id DESC";
						$db->sql($sql);
						$res_inner=$db->getResult();
						$name = $db->escapeString($fn->xss_clean($line[0]));
						$slug = $function->slugify($fn->xss_clean($line[1]));
						$category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
						$subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
						$serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
						$description = $db->escapeString($fn->xss_clean($_POST['description']));
    		
						// get image info
						$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
						$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
						$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
						
						// create array variable to handle error
						$error = array();
						
						if(empty($name)){
							$error['name'] = " <span class='label label-danger'>Required!</span>";
						}
							
						if(empty($category_id)){
							$error['category_id'] = " <span class='label label-danger'>Required!</span>";
						}				
							
						if(empty($price)){
							$error['price'] = " <span class='label label-danger'>Required!</span>";
						}/* else if(!is_numeric($price)){
							$error['price'] = " <span class='label label-danger'>Price in number!</span>";
						} */
						
						if(empty($measurement)){
							$error['measurement'] = " <span class='label label-danger'>Required!</span>";
						}
						
						if(empty($quantity)){
							$error['quantity'] = " <span class='label label-danger'>Required!</span>";
						}else if(!is_numeric($quantity)){
							$error['quantity'] = " <span class='label label-danger'>Quantity in number!</span>";
						}
							
						if(empty($serve_for)){
							$error['serve_for'] = " <span class='label label-danger'>Not choosen</span>";
						}			
					
						if(empty($description)){
							$error['description'] = " <span class='label label-danger'>Required!</span>";
						}
						// common image file extensions
						$allowedExts = array("gif", "jpeg", "jpg", "png");
						
						// get image file extension
						error_reporting(E_ERROR | E_PARSE);
						$extension = end(explode(".", $_FILES["image"]["name"]));
						
						if($image_error > 0){
							$error['image'] = " <span class='label label-danger'>Not uploaded!</span>";
						}else if(!(($image_type == "image/gif") || 
							($image_type == "image/jpeg") || 
							($image_type == "image/jpg") || 
							($image_type == "image/x-png") ||
							($image_type == "image/png") || 
							($image_type == "image/pjpeg")) &&
							!(in_array($extension, $allowedExts))){
						
							$error['image'] = " <span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
						}
						$error['other_images'] = '';
						if($_FILES["other_images"]["error"][0] == 0){
							for($i=0;$i<count($_FILES["other_images"]["name"]);$i++){
								$_FILES["other_images"]["type"][$i];
								if($_FILES["other_images"]["error"][$i] > 0){
									$error['other_images'] = " <span class='label label-danger'>Images not uploaded!</span>";
								}else if(!(($_FILES["other_images"]["type"][$i] == "image/gif") || 
									($_FILES["other_images"]["type"][$i] == "image/jpeg") || 
									($_FILES["other_images"]["type"][$i] == "image/jpg") || 
									($_FILES["other_images"]["type"][$i] == "image/x-png") ||
									($_FILES["other_images"]["type"][$i] == "image/png") || 
									($_FILES["other_images"]["type"][$i] == "image/pjpeg")) &&
									!(in_array($_FILES["other_images"]["type"][$i], $allowedExts))){
									$error['other_images'] = " <span class='label label-danger'>Images type must jpg, jpeg, gif, or png!</span>";
								}
							}
						}
					
					if(!empty($name) && !empty($category_id) && !empty($serve_for) && empty($error['image']) && empty($error['other_images']) && !empty($description)){
							
							// create random image file name
							$string = '0123456789';
							$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
							
							$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
								
							// upload new image
							$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/'.$image);
							$other_images = '';
							if(isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0 )){
								//Upload other images
								$file_data = array();
								$target_path = 'upload/other_images/';
								for($i=0;$i<count($_FILES["other_images"]["name"]);$i++){
									
									$filename = $_FILES["other_images"]["name"][$i];
									$temp = explode('.',$filename);
									$filename = microtime(true) . '.' . end($temp);
									$file_data[] = $target_path.''.$filename;
									if(!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path.''.$filename))
										echo "{$_FILES['image']['name'][$i]} not uploaded<br/>";
								}
								$other_images = json_encode($file_data);
							}
							
							$upload_image = 'upload/images/'.$image;
							if (strpos($name, "'") !== false) {
								$name=str_replace("'", "''", "$name");
								if(strpos($description, "'") !== false)
									$description=str_replace("'", "''", "$description");
							}
							// insert new data to product table
								$sql="INSERT INTO products (name,slug,category_id,subcategory_id,image,other_images,description) VALUES('$name','$slug','$category_id','$subcategory_id','$upload_image','$other_images','$description')";
								$db->sql($sql);
								$product_id = $db->getResult();
								 if(!empty($product_id)){
									$product_id=0;
								}
								else{
									$product_id=1;

								}
								// print_r($product_id);
								$sql="SELECT id from products ORDER BY id DESC";
								$db->sql($sql);
								$res_inner=$db->getResult();
							if($db->escapeString($fn->xss_clean($_POST['type'])) == 'packet'){
								for($i=0;$i<count($_POST['packate_measurement']);$i++){
									$product_id=$db->escapeString($res_inner[0]['id']);
									$type=$db->escapeString($fn->xss_clean($_POST['type']));
									$measurement=$db->escapeString($fn->xss_clean($_POST['packate_measurement'][$i]));
									$measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['packate_measurement_unit_id'][$i]));
									$price=$db->escapeString($fn->xss_clean($_POST['packate_price'][$i]));
									$discounted_price=!empty($fn->xss_clean($_POST['packate_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_discounted_price'][$i])) : 0;
									$serve_for=$db->escapeString($fn->xss_clean($_POST['packate_serve_for'][$i]));
									$stock=$db->escapeString($fn->xss_clean($_POST['packate_stock'][$i]));
									$stock_unit_id=$db->escapeString($fn->xss_clean($_POST['packate_stock_unit_id'][$i]));

									$sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,serve_for,stock,stock_unit_id) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$serve_for','$stock','$stock_unit_id')";
									 $db->sql($sql);
									 $product_variant = $db->getResult();   
								}
									if(!empty($product_variant)){
									$product_variant=0;
								}
								else{
									$product_variant=1;
								}
								// print_r($product_variant);
								
							}elseif($db->escapeString($fn->xss_clean($_POST['type'])) == "loose"){
								for($i=0;$i<count($_POST['loose_measurement']);$i++){
									$product_id=$db->escapeString($res_inner[0]['id']);
									$type=$db->escapeString($fn->xss_clean($_POST['type']));
									$measurement=$db->escapeString($fn->xss_clean($_POST['loose_measurement'][$i]));
									$measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_measurement_unit_id'][$i]));
									$price=$db->escapeString($fn->xss_clean($_POST['loose_price'][$i]));
									$discounted_price=!empty($fn->xss_clean($_POST['loose_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_discounted_price'][$i])) : 0;
									$serve_for=$db->escapeString($fn->xss_clean($_POST['serve_for']));
									$stock=$db->escapeString($fn->xss_clean($_POST['loose_stock']));
									$stock_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id']));

									$sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,serve_for,stock,stock_unit_id) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$serve_for','$stock','$stock_unit_id')";
									 $db->sql($sql);
									 $product_variant = $db->getResult();
							   
								}
									 if(!empty($product_variant)){
									 $product_variant=0;
									}
								else{
									$product_variant=1;
								}
								// print_r($product_variant);
							}
							if($product_variant==1){
								$error['add_menu'] = "<div class='content-header'>
																<span class='label label-success'>Product Added Successfully</span>
																<h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
																
																</div>";
							}else {
								$error['add_menu'] = " <span class='label label-danger'>Failed</span>";
							}
						}
						
				}					
				//close opened csv file
				fclose($csvFile);
	
				$error="Products Imported Successfully";
				}
			}else{
				$error = "Products Import Failed";
			}
		}else{
			$error = "File is Invalid";
		}
	} 
	?>