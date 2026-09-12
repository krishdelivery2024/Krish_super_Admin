<?php 
    include_once('includes/functions.php'); 
	date_default_timezone_set('Asia/Kolkata');
	$function = new functions;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $sql = "SELECT * FROM delivery_method WHERE id=1";
    $db->sql($sql);
    $res2 = $db->getResult(); 
    $Delivery_by_courier = $res2[0]['Delivery_by_courier'];
    
    $sql_query = "SELECT id, name FROM category WHERE main_cat='$main_cat_id' ORDER BY id ASC";	
    	// Execute query
    	$db->sql($sql_query);
    	// store result 
    	$res=$db->getResult();
    	$sql_query = "SELECT id, name FROM brand ORDER BY id ASC";	
    	// Execute query
    	$db->sql($sql_query);
    	// store result 
    	$brand_data=$db->getResult();
    $sql_query = "SELECT value FROM settings WHERE variable = 'Currency'";
  
        $db->sql($sql_query);
    	// store result 
    	
        $res_cur=$db->getResult();
    	
    	
    if(isset($_POST['btnAdd'])){
        if($permissions['products']['create']==1){
        //    print_r($_POST);die;
        $product_status = $db->escapeString($fn->xss_clean($_POST['product_status']));
		$name = $db->escapeString($fn->xss_clean($_POST['name']));
		$slug = $function->slugify($fn->xss_clean($_POST['name']));
    	$category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    	$subcategory_id = !empty($_POST['subcategory_id']) ? $db->escapeString($fn->xss_clean($_POST['subcategory_id'])):'0';
    	$brand_id = !empty($_POST['brand_id']) ? $db->escapeString($fn->xss_clean($_POST['brand_id'])):'0';
    	$serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    	$description = $db->escapeString($fn->xss_clean($_POST['description']));
    	$price_type=!empty($_POST['price_type']) ? $db->escapeString($fn->xss_clean($_POST['price_type'])):'';
    	$hsn=!empty($_POST['hsn']) ? $db->escapeString($fn->xss_clean($_POST['hsn'])):'';
    	$sgst=!empty($_POST['sgst']) ? $db->escapeString($fn->xss_clean($_POST['sgst'])):'';
    	$cgst=!empty($_POST['cgst']) ? $db->escapeString($fn->xss_clean($_POST['cgst'])):'';
    	$igst=!empty($_POST['igst']) ? $db->escapeString($fn->xss_clean($_POST['igst'])):'';
    	
    	$min_stock = $db->escapeString($fn->xss_clean($_POST['min_stock']));
        $indicator = $db->escapeString($fn->xss_clean($_POST['indicator']));
    	
    	$min_order_qty = $db->escapeString($fn->xss_clean($_POST['min_order_qty']));
    	$available_time = !empty($_POST['available_time']) ? implode(',', array_map(function($value) use ($fn, $db){ return $db->escapeString($fn->xss_clean($value)); }, $_POST['available_time'])) : '';
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
    	if($Delivery_by_courier == '1'){ 
    	    if(!empty($_POST['packate_weight'])){
    	        $pw = $_POST['packate_weight'];
    	        foreach ($pw as $key => $value) {
                    if (!strlen($value)) {
                       unset($pw);
                    }
                }
                if (!$pw) {
                    $error['weight'] = " <span class='label label-danger'>Weight Required!</span>";
                }
        	}
        	
        	if(!empty($_POST['loose_weight'])){
    	        $lw = $_POST['loose_weight'];
    	        foreach ($lw as $key => $value) {
                    if (!strlen($value)) {
                       unset($lw);
                    }
                }
                if (!$lw) {
                    $error['weight'] = " <span class='label label-danger'>Weight Required!</span>";
                    
                }
        	}

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
    
    if(!empty($name) && !empty($category_id) && !empty($serve_for) && empty($error['weight']) && empty($error['image']) && empty($error['other_images']) && !empty($description)){
    		
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
               // print_r($name);die;
                //$name=str_replace("'", "''", "$name");
                
                if(strpos($description, "'") !== false)
                    $description=str_replace("'", "''", "$description");
            }
            $sql="SELECT COUNT(id) AS count from products";
                $db->sql($sql);
                $res_count=$db->getResult();
                //echo $res_count[0]['count'];exit;
            If($res_count[0]['count']<=2000){
    		// insert new data to product table
    		$data=array(
    		    "name"=>$name,
    		    "slug"=>$slug,
    		    "category_id"=>$category_id,
    		    "subcategory_id"=>$subcategory_id,
    		    "brand_id"=>$brand_id,
    		    "image"=>$upload_image,
    		    "other_images"=>$other_images,
    		    "description"=>$description,
    		    "min_stock"=>$min_stock
    		    );
    		   // $product_id=$db->insert('products',$data);
                //$sql='INSERT INTO products (name,slug,category_id,subcategory_id,brand_id,image,other_images,description,min_stock) VALUES("'.$name.'","'.$slug.'","'.$category_id.'","'.$subcategory_id.'","'.$brand_id.'","'.$upload_image.'","'.$other_images.'","'.$description.'","'.$min_stock.'")';
                $sql="INSERT INTO products (name,slug,category_id,subcategory_id,brand_id,price_type,hsn,sgst,cgst,igst,image,other_images,description,available_time,min_stock,min_order_qty,status,seller_id,indicator) VALUES('".$name."','".$slug."','".$category_id."','".$subcategory_id."','".$brand_id."','".$price_type."','".$hsn."','".$sgst."','".$cgst."','".$igst."','".$upload_image."','".$other_images."','".$description."','".$available_time."','".$min_stock."','".$min_order_qty."','".$product_status."','".$seller_id."','".$indicator."')";
                // print_r($sql);die;
                $db->sql($sql);
    			$product_id = $db->getResult();
                 if(!empty($product_id)){
                    $product_id=0;
                }
                else{
                    $product_id=1;
                }
                // print_r($product_id);die;
                $sql="SELECT id from products ORDER BY id DESC";
                $db->sql($sql);
                $res_inner=$db->getResult();
			if($db->escapeString($fn->xss_clean($_POST['type'])) == 'packet'){
			    for($i=0;$i<count($_POST['packate_measurement']);$i++){
                    $product_id=$res_inner[0]['id'];
                    $type=$db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement=$db->escapeString($fn->xss_clean($_POST['packate_measurement'][$i]));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['packate_measurement_unit_id'][$i]));
                    $weight=$db->escapeString($fn->xss_clean($_POST['packate_weight'][$i]));
                    $price=$db->escapeString($fn->xss_clean($_POST['packate_price'][$i]));
                    $vendorprice=$db->escapeString($fn->xss_clean($_POST['packate_vendor_price'][$i]));
                    $discounted_price=!empty($fn->xss_clean($_POST['packate_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_discounted_price'][$i])) : 0;
                    $product_price=!empty($fn->xss_clean($_POST['packate_product_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_product_price'][$i])) : 0;
                    $item_sgst=!empty($fn->xss_clean($_POST['packate_item_sgst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_item_sgst'][$i])) : 0;
                    $item_cgst=!empty($fn->xss_clean($_POST['packate_item_cgst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_item_cgst'][$i])) : 0;
                    $item_igst=!empty($fn->xss_clean($_POST['packate_item_igst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['packate_item_igst'][$i])) : 0;
                    
                    $serve_for=!empty($db->escapeString($fn->xss_clean($_POST['packate_stock'][$i])))?$db->escapeString($fn->xss_clean($_POST['packate_serve_for'][$i])):'Sold Out';
                    
                    $stock=$db->escapeString($fn->xss_clean($_POST['packate_stock'][$i]));
                    $stock_unit_id=!empty($_POST['packate_stock_unit_id'][$i]) ? $db->escapeString($fn->xss_clean($_POST['packate_stock_unit_id'][$i])):'5';
                    $barcode_data=$db->escapeString($fn->xss_clean($_POST['barcode_text'][$i]));
                    $sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,weight,price,vendor_price,discounted_price,product_price,item_sgst,item_cgst,item_igst,serve_for,stock,stock_unit_id,barcode_data) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$weight','$price','$vendorprice','$discounted_price','$product_price','$item_sgst','$item_cgst','$item_igst','$serve_for','$stock','$stock_unit_id','$barcode_data')";
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
                    $product_id=$res_inner[0]['id'];
                    $type=$db->escapeString($fn->xss_clean($_POST['type']));
                    $measurement=$db->escapeString($fn->xss_clean($_POST['loose_measurement'][$i]));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_measurement_unit_id'][$i]));
                    $weight=$db->escapeString($fn->xss_clean($_POST['loose_weight'][$i]));
                    $price=$db->escapeString($fn->xss_clean($_POST['loose_price'][$i]));
                    $vendorprice=$db->escapeString($fn->xss_clean($_POST['loose_vendor_price'][$i]));
                    $discounted_price=!empty($fn->xss_clean($_POST['loose_discounted_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_discounted_price'][$i])) : 0;
                    $product_price=!empty($fn->xss_clean($_POST['loose_product_price'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_product_price'][$i])) : 0;
                    $item_sgst=!empty($fn->xss_clean($_POST['loose_item_sgst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_item_sgst'][$i])) : 0;
                    $item_cgst=!empty($fn->xss_clean($_POST['loose_item_cgst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_item_cgst'][$i])) : 0;
                    $item_igst=!empty($fn->xss_clean($_POST['loose_item_igst'][$i])) ? $db->escapeString($fn->xss_clean($_POST['loose_item_igst'][$i])) : 0;
                    $serve_for=$db->escapeString($fn->xss_clean($_POST['serve_for']));
                    $stock=$db->escapeString($fn->xss_clean($_POST['loose_stock']));
                    $stock_unit_id=$db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id']));
                    $barcode_data=$db->escapeString($fn->xss_clean($_POST['loose_barcode_text']));
                    $sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,weight,price,vendor_price,discounted_price,product_price,item_sgst,item_cgst,item_igst,serve_for,stock,stock_unit_id,barcode_data) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$weight','$price','$vendorprice','$discounted_price','$product_price','$item_sgst','$item_cgst','$item_igst','$serve_for','$stock','$stock_unit_id',$barcode_data)";
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
    }else{
        $error['add_menu'] = " <span class='label label-danger'>Allowed Product Limit 2000</span>";
    }
    	}
        }else{
        $error['check_permission'] = " <div class='content-header'>
                                                <span class='label label-danger'>You have no permission to create product</span>
                                                
                                                
                                                </div>";

        
    }
    }
    ?>
    <style>
        .col-item{
           
            float:left;
            position: relative;
            min-height: 1px;
            padding-right: 2px;
            padding-left: 2px;
        }
        .col-item-1{
            width:7%;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
		
    <?php echo isset($error['add_menu']) ? $error['add_menu'] : '';?>
     <?=!empty($error['weight'])?$error['weight']:''?>
             <?php if(!isset($permissions['products']['create']) || $permissions['products']['create']==0) { ?>
                <div class="alert alert-danger">You have no permission to create product.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Product</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form id='add_product_form' method="post" enctype="multipart/form-data">
                     <?php 
                        // $db->select('unit','*');
                     $sql="SELECT * FROM unit";
                     $db->sql($sql);
                     $res_unit = $db->getResult();
                     ?>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Products Name</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                            <input type="text" class="form-control"  name="name" required>
                        </div>
                        <div class="form-group mb-3">
                            <div class="row">
                                <div class=" col-md-2 ">
                                    <label for="price_type">Price Type : </label>
                                    <br>
                                    <select  class="form-control"name="price_type" id="price_type">
                                        <option value="including">Including GST</option>
                                        <option value="excluding">Excluding GST</option>
                                    </select>
                                </div>
                                <div class=" col-md-2 ">
                                    <label for="hsn">HSN/SAC : </label>
                                    <br>
                                    <input type="text" class="form-control"  name="hsn">
                                </div>
                                <div class=" col-md-8 ">
                                     <label for="gst">GST Applicable(%) :</label>
                                     <br>
                                     <div class="row">
                                         <div class=" col-md-3 ">
                                          <label for="sgst">SGST</label>
                                          <input type="text" id="sgst" name="sgst" class="form-control" style="max-width: 80px;display: inline;position: relative;left: 3px;">
                                         </div>
                                         <div class="  col-md-3 ">
                                          <label for="cgst">CGST</label>
                                          <input type="text" id="cgst" name="cgst" class="form-control" style="max-width: 80px;display: inline;">
                                         </div>
                                         <div class="  col-md-3">
                                          <label for="igst">IGST</label>
                                          <input type="text" id="igst" name="igst"  class="form-control" style="max-width: 80px;display: inline;position: relative;left: 8px;">
                                         </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                        <label for="type">Type</label><?php echo isset($error['type']) ? $error['type'] : '';?>
                        <div class="form-group">
                          <label class="radio-inline"><input type="radio" name="type"  id="packate" value="packet" checked>Packet</label>
                          <label class="radio-inline"><input type="radio" name="type" id ="loose" value="loose">Loose</label>
                        </div>
                        </div>
                        <hr>
                       
						<div id="packate_div" style="display:none">
							<div class="row input-group">
							    <div class="col-item col-item-1" style="width:10%">
							        <div class="form-group packate_div">
	                                    <label for="exampleInputEmail1">Measurement</label><input type="text" class="form-control" name="packate_measurement[]" required />
	                                </div>
	                            </div>
	                            <div class="col-item col-item-1">
                            	    <div class="form-group packate_div">
                                        <label for="unit">Unit:</label>
                                        <select class="form-control" name="packate_measurement_unit_id[]">
                                            <?php
                                                foreach($res_unit as  $row){
                                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                            		</div>
                            	</div>
                            	<div  class="col-item col-item-1" style="width:8%">
                                    <div class="form-group packate_div">
                            	        <label for="packate_weight ">Weight(Kg):</label>
                            	        <input type="text" class="form-control packate_weight" name="packate_weight[]" />
                            	    </div>
                            	</div>
	                            <div class="col-item col-item-1">
	                                <div class="form-group packate_div">
	                                    <label for="price">MRP  (<?=$settings['currency']?>):</label><input type="text" class="form-control" name="packate_price[]" id="packate_price" required />
                            	    </div>
                            	</div>
                            	<div class="col-item " style="width:11.3%">
	                                <div class="form-group packate_div">
	                                    <label for="price">Vendor Price  (<?=$settings['currency']?>):</label>
	                                    <input type="text" class="form-control" name="packate_vendor_price[]" id="packate_vendor_price" required />
                            	    </div>
                            	</div>
                            	<div  class="col-item di_price" style="width:11.3%">
                                    <div class="form-group packate_div">
                            	        <label for="discounted_price ">Selling Price(<?=$settings['currency']?>):</label>
                            	        <input type="text" class="form-control discounted_price" name="packate_discounted_price[]" />
                            	    </div>
                            	</div>
                            	<div class="col-item pr_price" style="width:12.1%;">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="packate_product_price[]" readonly/>
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group packate_div">
                                        <label for="qty">SGST(<?=$settings['currency']?>):</label>
                                        <input type="text " class="form-control item_sgst" name="packate_item_sgst[]" readonly/>
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group packate_div">
                                        <label for="qty">CGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_cgst" name="packate_item_cgst[]" readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group packate_div">
                                        <label for="qty">IGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_igst" name="packate_item_igst[]" readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Stock:</label>
                                        <input type="text" class="form-control" name="packate_stock[]" />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Status:</label>
                                        <select name="packate_serve_for[]" class="form-control" required>
                                            <option value="Available">Available</option>
                                            <option value="Sold Out">Sold Out</option>
                                        </select>
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1">
                                    <label>Variation</label>
                                    <a id="add_packate_variation" title="Add variation of product" style="cursor: pointer;"><i class="fa fa-plus-square-o fa-2x"></i></a>
                            	</div>
                            </div>
                        </div>
                            
                            
                            <div id="loose_div" style="display:none;">
                            <div class="row">
                                <div class="col-item " style="width:10%;"> 
                                    <div class="form-group loose_div">
                        		        <label for="exampleInputEmail1">Measurement</label>
                        		        <input type="text" class="form-control" name="loose_measurement[]" required="">
                        		    </div>
                        		</div>
                        		<div class="col-item col-item-1">
                            	    <div class="form-group loose_div">
                                        <label for="unit">Unit:</label>
                                        <select class="form-control" name="loose_measurement_unit_id[]">
                                            <?php
                                                foreach($res_unit as  $row){
                                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                            		</div>
                            	</div>
                            	<div  class="col-item" style="width:10%">
                                    <div class="form-group loose_div">
                            	        <label for="loose_weight ">Weight(Kg):</label>
                            	        <input type="text" class="form-control" id="loose_weight" name="loose_weight[]" />
                            	    </div>
                            	</div>
                        		<div class="col-item " style="width:10%;">
                        		    <div class="form-group loose_div">
                            		    <label for="price">MRP  (<?=$settings['currency']?>):</label>
                            		    <input type="text" class="form-control" name="loose_price[]" id="loose_price" required="">
                        		    </div>
                        		</div>
                        		<div class="col-item " style="width:12%;">
                        		    <div class="form-group loose_div">
                            		    <label for="price">Vendor Price  (<?=$settings['currency']?>):</label>
                            		    <input type="text" class="form-control" name="loose_vendor_price[]" id="loose_vendor_price" required="">
                        		    </div>
                        		</div>
                        		<div class="col-item di_price" style="width:14%;">
                        		    <div class="form-group loose_div">
                                		<label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>
                                		<input type="text" class="form-control discounted_price" name="loose_discounted_price[]" />
                        		    </div>
                        		</div>
                        		<div class="col-item pr_price" style="width:12%;">
                            	    <div class="form-group loose_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="loose_product_price[]" readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group loose_div">
                                        <label for="qty">SGST(<?=$settings['currency']?>):</label>
                                        <input type="text " class="form-control item_sgst" name="loose_item_sgst[]" readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group loose_div">
                                        <label for="qty">CGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_cgst" name="loose_item_cgst[]" readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr" style="display:none">
                            	    <div class="form-group loose_div">
                                        <label for="qty">IGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_igst" name="loose_item_igst[]" readonly />
                            		</div>
                            	</div>
                        		<div class="col-item col-item-1">
                        	        <label>Variation</label>
                        	        <a id="add_loose_variation" title="Add variation of product" style="cursor: pointer;"><i class="fa fa-plus-square-o fa-2x"></i></a>
                        		</div>
                        	</div>
                        	</div>
					    <div id="variations">
						</div>
						<hr>
						<div id="loose_stock_div" class="row" style="display:none;">
                        <div class="form-group col-md-3">
                            <label for="quantity">Stock :</label><?php echo isset($error['quantity']) ? $error['quantity']:'';?>
                            <input type="text" class="form-control" name="loose_stock" >
                        </div>
                           
                             
                             <div class="form-group col-md-3">
                                  <label for="stock_unit">Unit :</label><?php echo isset($error['stock_unit']) ? $error['stock_unit']:'';?>
                            <select class="form-control" name="loose_stock_unit_id" id="loose_stock_unit_id">
                                <?php
                                foreach($res_unit as $row){
                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3" id="packate_server_hide">
                            <label for="serve_for">Status :</label><?php echo isset($error['serve_for']) ? $error['serve_for'] : '';?>
                            <select name="serve_for" class="form-control" required>
                                <option value="Available">Available</option>
                                <option value="Sold Out">Sold Out</option>
                            </select>
                            <br/>
                        </div>
                        <div class="form-group col-md-3"  style="display:none">
                            <label >Barcode Data:</label>
                            <input type="text" name="loose_barcode_text" class=" form-control" value="<?=$fn->generateEAN()?>">
                		</div>
                		<hr>
                		</div>
                        <div class="form-group">
                            <label for="category_id">Category :</label><?php echo isset($error['category_id']) ? $error['category_id'] : '';?>
                             <select name="category_id" id="category_id" class="form-control" required>
                                <option value="">--Select Category--</option>
                                <?php if($permissions['categories']['read']==1) { ?>
                                <?php foreach($res as $row){ ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php } 

                            }?>
                            </select>
                            <br/>
                        </div>
                        <div class="form-group" style="display:none;">
                            <label for="subcategory_id">Sub Category :</label><?php echo isset($error['subcategory_id']) ? $error['subcategory_id'] : '';?>
                            <select name="subcategory_id" id="subcategory_id" class="form-control" required>
                                <option value="">--Select Main Category--</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="brand_id">Brand :</label><?php echo isset($error['brand_id']) ? $error['brand_id'] : '';?>
                             <select name="brand_id" id="brand_id" class="form-control" >
                                <option value="0">--Select Brand--</option>
                                <?php if($permissions['categories']['read']==1) { ?>
                                <?php foreach($brand_data as $row){ ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                <?php } 

                            }?>
                            </select>
                            <br/>
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="image">Main Image :&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['image']) ? $error['image'] : '';?>
                            <input type="file" name="image" id="image" required>
                        </div>
                        <div class="form-group">
                            <label for="other_images">Other Images of the Product: *Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['other_images']) ? $error['other_images'] : '';?>
							<input type="file" name="other_images[]" id="other_images" multiple>
                        </div>
                        <div class="form-group">
                            <label for="description">Description :</label><?php echo isset($error['description']) ? $error['description'] : '';?>
                            <textarea name="description" id="description" class="form-control" rows="8"></textarea>
                            <script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
							<script type="text/javascript">CKEDITOR.replace('description');</script>
                        </div>
                        <div class="form-group  col-md-3" style="max-width:200px;">
                            <label for="exampleInputEmail1">Minimum Stock</label><?php echo isset($error['min_stock']) ? $error['min_stock'] : '';?>
                            <input type="number" class="form-control"  name="min_stock">
                        </div>
                        
                         <div class="form-group" style="max-width:200px;display:none;">
                            <label for="exampleInputEmail1">Minimum Order Quantity</label><?php echo isset($error['min_order_qty']) ? $error['min_order_qty'] : '';?>
                            <input type="number" class="form-control"  name="min_order_qty" value="0">
                        </div>
                        
                        <div class="form-group col-md-3" style="max-width:200px;">
                            <label for="product_status">Status  </label>
                            <br>
                            <select  class="form-control"name="product_status" id="product_status">
                                <option value="1" >Enabled</option>
                                <option value="0" >Disabled</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3" style="max-width:200px;">
                            <label for="indicator">Indicator  </label>
                            <br>
                            <select  class="form-control"name="indicator" id="indicator">
                                <option value="0" >None</option>
                                <option value="1" >Veg</option>
                                <option value="2" >Non Veg</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="available_time">Available Time</label>
                            <br>
                            <label class="checkbox-inline"><input type="checkbox" name="available_time[]" value="Anytime"> Anytime</label>
                            <label class="checkbox-inline"><input type="checkbox" name="available_time[]" value="Breakfast"> Breakfast</label>
                            <label class="checkbox-inline"><input type="checkbox" name="available_time[]" value="Lunch"> Lunch</label>
                            <label class="checkbox-inline"><input type="checkbox" name="available_time[]" value="Dinner"> Dinner</label>
                        </div>
                        
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="submit" class="btn-primary btn" value="Add" name="btnAdd" />&nbsp;
                        <input type="reset" class="btn-danger btn" value="Clear"/>
                        <!--<div  id="res"></div>-->
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
    </div>
<div class="separator"> </div>
<script>
	    var uploadField = document.getElementById("other_images");

        uploadField.onchange = function() {
            if(this.files[0].size > 300024){
               alert("Allowed Max File size 300 KB");
               this.value = "";
            };
        };
        var uploadField = document.getElementById("image");

        uploadField.onchange = function() {
            if(this.files[0].size > 300024){
               alert("Allowed Max File size 300 KB");
               this.value = "";
            };
        };
	</script>