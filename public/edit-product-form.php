<?php
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    // include_once('includes/crud.php');
    $function = new Functions;
    $sql = "SELECT * FROM delivery_method WHERE id=1";
    $db->sql($sql);
    $res2 = $db->getResult(); 
    $Delivery_by_courier = $res2[0]['Delivery_by_courier'];
    // $db = new Database();
    if(isset($_GET['id'])){
        $ID = $db->escapeString($fn->xss_clean($_GET['id']));
    }else{
        // $ID = "";
        return false;
        exit(0);
    }
    // create array variable to store category data
    $category_data = array();
    $sql = "select id,name from category order by id asc";
    $db->sql($sql);
    $category_data = $db->getResult();
    $sql = "select id,name from brand order by id asc";
    $db->sql($sql);
    $brand_data = $db->getResult();
    $sql = "SELECT image, other_images FROM products WHERE id =".$ID;
    $db->sql($sql);
    $res = $db->getResult();
    foreach($res as $row){
        $previous_menu_image = $row['image'];
        $other_images = $row['other_images'];
    }
    if(isset($_POST['btnEdit'])){
        
        if($permissions['products']['update']==1){
              $name = $db->escapeString($fn->xss_clean($_POST['name']));
              if (strpos($name, '-') !== false) {
                    $temp=(explode("-", $name)[1]);
            }else{
                $temp=$name;
            }
              
              $slug = $function->slugify($temp);
              $sql = "SELECT slug FROM products where id!=".$_GET['id'];
              $db->sql($sql);
              $res = $db->getResult();
              $i=1;
              foreach($res as $row){
                  if($slug==$row['slug']){
                    $slug = $slug.'-'.$i;
                    $i++;  
                  }
              }
            
            $product_status = $db->escapeString($fn->xss_clean($_POST['product_status']));
            $subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
            $category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
            $brand_id = !empty($_POST['brand_id']) ? $db->escapeString($fn->xss_clean($_POST['brand_id'])) : '';
            $serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
            $price_type = $db->escapeString($fn->xss_clean($_POST['price_type']));
            $hsn=!empty($_POST['hsn']) ? $db->escapeString($fn->xss_clean($_POST['hsn'])):'';
        	$sgst=!empty($_POST['sgst']) ? $db->escapeString($fn->xss_clean($_POST['sgst'])):'';
        	$cgst=!empty($_POST['cgst']) ? $db->escapeString($fn->xss_clean($_POST['cgst'])):'';
        	$igst=!empty($_POST['igst']) ? $db->escapeString($fn->xss_clean($_POST['igst'])):'';
        	
            $description = $db->escapeString($fn->xss_clean($_POST['description']));
            $min_stock = $db->escapeString($fn->xss_clean($_POST['min_stock']));
            $min_order_qty = $db->escapeString($fn->xss_clean($_POST['min_order_qty']));
            $indicator = $db->escapeString($fn->xss_clean($_POST['indicator']));
            // $quantity = $_POST['quantity'];
            
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
            if(empty($measurement)){
             $error['measurement'] = " <span class='label label-danger'>Required!</span>";
            }
                
            if(empty($price)){
             $error['price'] = " <span class='label label-danger'>Required!</span>";
            }/* else if(!is_numeric($price)){
             $error['price'] = " <span class='label label-danger'>Price in number!</span>";
            } */
                
            if(empty($discounted_price)){
             $error['discounted_price'] = " <span class='label label-danger'>Required! At least Zero(0)</span>";
            }
            
            if(empty($stock)){
             $error['stock'] = " <span class='label label-danger'>Required!</span>";
            }else if(!is_numeric($stock)){
             $error['stock'] = " <span class='label label-danger'>stock in number!</span>";
            }
                
            if(empty($serve_for)){
             $error['serve_for'] = " <span class='label label-danger'>Not choosen</span>";
            }            
    
            if(empty($description)){
                $error['description'] = " <span class='label label-danger'>Required!</span>";
            }
            
            if($Delivery_by_courier == '1'){ 
    	    if(!empty($_POST['packate_weight']) && $_POST['type']=="packet"){
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
        	
        	if(!empty($_POST['loose_weight']) && $_POST['type']=="loose"){
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
            
            if(!empty($image)){
                if(!(($image_type == "image/gif") || 
                    ($image_type == "image/jpeg") || 
                    ($image_type == "image/jpg") || 
                    ($image_type == "image/x-png") ||
                    ($image_type == "image/png") || 
                    ($image_type == "image/pjpeg")) &&
                    !(in_array($extension, $allowedExts))){
                    
                    $error['image'] = "*<span class='label label-danger'>Image type must jpg, jpeg, gif, or png!</span>";
                }
            }
            /*updating other_images if any*/
            
            if(isset($_FILES['other_images']) && ($_FILES['other_images']['size'][0] > 0 )){
                // print_r($_FILES);
                $file_data = array();
                $target_path = 'upload/other_images/';
                for($i=0;$i<count($_FILES["other_images"]["name"]);$i++){
                    
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
                    $filename = $_FILES["other_images"]["name"][$i];
                    $temp = explode('.',$filename);
                    $filename = microtime(true) . '.' . end($temp);
                    $file_data[] = $target_path.''.$filename;
                    if(!move_uploaded_file($_FILES["other_images"]["tmp_name"][$i], $target_path.''.$filename))
                        echo "{$_FILES['image']['name'][$i]} not uploaded<br/>";
                }
                if(!empty($other_images)){
                    $arr_old_images = json_decode($other_images);
                    $all_images = array_merge($arr_old_images,$file_data);
                    $all_images = json_encode(array_values($all_images));
                }else{
                    $all_images = json_encode($file_data);
                }
                
                $sql = "update `products` set `other_images`='".$all_images."' where `id`=".$ID;
                $db->sql($sql);
               // echo $all_images;
            }
                if(!empty($name) && !empty($category_id) && !empty($description) && empty($error['image']) && empty($error['weight']) ){
                     if (strpos($name, "'") !== false) {
                    // $name=str_replace("'", "''", "$name");
                     if(strpos($description, "'") !== false)
                     $description=str_replace("'", "''", "$description");
                }
                if(!empty($image)){
                    
                    // create random image file name
                    $string = '0123456789';
                    $file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
                    $function = new functions;
                    $image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
                
                    // delete previous image
                    $delete = unlink("$previous_menu_image");
                    
                    // upload new image
                    $upload = move_uploaded_file($_FILES['image']['tmp_name'], 'upload/images/'.$image);
      
                    $upload_image = 'upload/images/'.$image;
                    $sql_query = "UPDATE products SET name = '$name' ,slug = '$slug' , category_id = '$category_id' ,subcategory_id = '$subcategory_id', brand_id = '$brand_id',price_type='$price_type', hsn = '$hsn',sgst = '$sgst',cgst = '$cgst',igst = '$igst', image = '$upload_image', description = '$description', min_stock = '$min_stock', min_order_qty = '$min_order_qty', status = '$product_status',  indicator = '$indicator'  WHERE id = $ID";
                    $db->sql($sql_query);
                }else{
                    $sql_query = "UPDATE products SET name = '$name' ,slug = '$slug' ,category_id = '$category_id' ,subcategory_id = '$subcategory_id' , brand_id = '$brand_id',price_type='$price_type', hsn = '$hsn',sgst = '$sgst',cgst = '$cgst',igst = '$igst', description = '$description', min_stock = '$min_stock', min_order_qty = '$min_order_qty', status = '$product_status',  indicator = '$indicator' WHERE id = $ID";
                    $db->sql($sql_query);
                }
                //print_r($sql_query);die;
                $res = $db->getResult();
                for($i=0;$i<count($_POST['product_variant_id']);$i++){
                    if($_POST['type']=="packet"){
                        $data = array(
                            'type'=>$db->escapeString($fn->xss_clean($_POST['type'])),
                            'measurement'=>$db->escapeString($fn->xss_clean($_POST['packate_measurement'][$i])),
                            'measurement_unit_id'=>$db->escapeString($fn->xss_clean($_POST['packate_measurement_unit_id'][$i])),
                            'weight'=>$db->escapeString($fn->xss_clean($_POST['packate_weight'][$i])),
                            'price'=>$db->escapeString($fn->xss_clean($_POST['packate_price'][$i])),
                            'vendor_price'=>$db->escapeString($fn->xss_clean($_POST['packate_vendor_price'][$i])),
                            'discounted_price'=>$db->escapeString($fn->xss_clean($_POST['packate_discounted_price'][$i])),
                            'product_price'=>$db->escapeString($fn->xss_clean($_POST['packate_product_price'][$i])),
                            'item_sgst'=>$db->escapeString($fn->xss_clean($_POST['packate_item_sgst'][$i])),
                            'item_cgst'=>$db->escapeString($fn->xss_clean($_POST['packate_item_cgst'][$i])),
                            'item_igst'=>$db->escapeString($fn->xss_clean($_POST['packate_item_igst'][$i])),
                            'stock'=>$db->escapeString($fn->xss_clean($_POST['packate_stock'][$i])),
                            'stock_unit_id'=> !empty($_POST['packate_stock_unit_id'][$i]) ? $db->escapeString($fn->xss_clean($_POST['packate_stock_unit_id'][$i])):'5',
                            'serve_for'=>$db->escapeString($fn->xss_clean($_POST['packate_serve_for'][$i])),
                            'barcode_data'=>$db->escapeString($fn->xss_clean($_POST['barcode_text'][$i]))

                        );
                        
                        $db->update('product_variant',$data,'id='.$fn->xss_clean($_POST['product_variant_id'][$i]));
                        $res = $db->getResult();
                        if($min_stock<$_POST['packate_stock'][$i]){
                            $data = array(
                                    "min_stock_notified"=>0
                                );
                            $db->update('products',$data,'id='.$ID);
                            $res = $db->getResult();
                        }
                        // print_r($res);
                    }else if($_POST['type']=="loose"){ //echo $_POST['product_variant_id'][$i];die();
                        $data = array(
                            'type'=>$db->escapeString($fn->xss_clean($_POST['type'])),
                            'measurement'=>$db->escapeString($fn->xss_clean($_POST['loose_measurement'][$i])),
                            'measurement_unit_id'=>$db->escapeString($fn->xss_clean($_POST['loose_measurement_unit_id'][$i])),
                            'weight'=>$db->escapeString($fn->xss_clean($_POST['loose_weight'][$i])),
                            'price'=>$db->escapeString($fn->xss_clean($_POST['loose_price'][$i])),
                            'vendor_price'=>$db->escapeString($fn->xss_clean($_POST['loose_vendor_price'][$i])),
                            'discounted_price'=>$db->escapeString($fn->xss_clean($_POST['loose_discounted_price'][$i])),
                            'product_price'=>$db->escapeString($fn->xss_clean($_POST['loose_product_price'][$i])),
                            'item_sgst'=>$db->escapeString($fn->xss_clean($_POST['loose_item_sgst'][$i])),
                            'item_cgst'=>$db->escapeString($fn->xss_clean($_POST['loose_item_cgst'][$i])),
                            'item_igst'=>$db->escapeString($fn->xss_clean($_POST['loose_item_igst'][$i])),
                            'stock'=>$db->escapeString($fn->xss_clean($_POST['loose_stock'])),
                            'stock_unit_id'=>$db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id'])),
                            'serve_for'=>$db->escapeString($fn->xss_clean($_POST['serve_for'])),
                            'barcode_data'=>$db->escapeString($fn->xss_clean($_POST['loose_barcode_text']))
                        );  
                        $db->update('product_variant',$data,'id='.$fn->xss_clean($_POST['product_variant_id'][$i]));
                        $res = $db->getResult();
                         if($min_stock<$_POST['loose_stock']){
                            $data = array(
                                    "min_stock_notified"=>0
                                );
                            $db->update('products',$data,'id='.$ID);
                            $res = $db->getResult();
                        }
                    }
                }
                if(isset($_POST['insert_packate_measurement']) && isset($_POST['insert_packate_measurement_unit_id']) 
                && isset($_POST['insert_packate_price']) && isset($_POST['insert_packate_discounted_price'])
                && isset($_POST['insert_packate_stock'])){
                    
                    for($i=0;$i<count($_POST['insert_packate_measurement']);$i++){
                        $data = array(
                            "product_id"=>$db->escapeString($ID),
                            "type"=>$db->escapeString($fn->xss_clean($_POST['type'])),
                            "measurement"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_measurement'][$i])),
                            "measurement_unit_id"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_measurement_unit_id'][$i])),
                            'weight'=>$db->escapeString($fn->xss_clean($_POST['insert_packate_weight'][$i])),
                            "price"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_price'][$i])),
                            "vendor_price"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_vendor_price'][$i])),
                            "discounted_price"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_discounted_price'][$i])),
                            'product_price'=>$db->escapeString($fn->xss_clean($_POST['insert_packate_product_price'][$i])),
                            'item_sgst'=>$db->escapeString($fn->xss_clean($_POST['insert_packate_item_sgst'][$i])),
                            'item_cgst'=>$db->escapeString($fn->xss_clean($_POST['insert_packate_item_cgst'][$i])),
                            'item_igst'=>$db->escapeString($fn->xss_clean($_POST['insert_packate_item_igst'][$i])),
                            "stock"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_stock'][$i])),
                            "stock_unit_id"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_measurement_unit_id'][$i])),
                            "serve_for"=>$db->escapeString($fn->xss_clean($_POST['serve_for'])[$i]),
                            "barcode_data"=>$db->escapeString($fn->xss_clean($_POST['insert_packate_barcode_text'][$i]))
                        );
                      //  print_r($data);exit;
                      if($_POST['insert_packate_measurement_unit_id'][$i]!='' && $_POST['insert_packate_measurement_unit_id'][$i]!=0){
                        $db->insert('product_variant',$data);
                        $res = $db->getResult();
                      }
                //      print_R($res);
                    }
                }
                
                if(isset($_POST['insert_loose_measurement']) && isset($_POST['insert_loose_measurement_unit_id']) 
                && isset($_POST['insert_loose_price']) && isset($_POST['insert_loose_discounted_price'])){
                    for($i=0;$i<count($_POST['insert_loose_measurement']);$i++){
                        $data = array(
                            "product_id"=>$db->escapeString($ID),
                            "type"=>$db->escapeString($fn->xss_clean($_POST['type'])),
                            "measurement"=>$db->escapeString($fn->xss_clean($_POST['insert_loose_measurement'][$i])),
                            "measurement_unit_id"=>$db->escapeString($fn->xss_clean($_POST['insert_loose_measurement_unit_id'][$i])),
                            'weight'=>$db->escapeString($fn->xss_clean($_POST['insert_loose_weight'][$i])),
                            "price"=>$db->escapeString($fn->xss_clean($_POST['insert_loose_price'][$i])),
                            "vendor_price"=>$db->escapeString($fn->xss_clean($_POST['insert_loose_vendor_price'][$i])),
                            "discounted_price"=>$db->escapeString($fn->xss_clean($_POST['insert_loose_discounted_price'][$i])),
                            'product_price'=>$db->escapeString($fn->xss_clean($_POST['insert_loose_product_price'][$i])),
                            'item_sgst'=>$db->escapeString($fn->xss_clean($_POST['insert_loose_item_sgst'][$i])),
                            'item_cgst'=>$db->escapeString($fn->xss_clean($_POST['insert_loose_item_cgst'][$i])),
                            'item_igst'=>$db->escapeString($fn->xss_clean($_POST['insert_loose_item_igst'][$i])),
                            "stock"=>$db->escapeString($fn->xss_clean($_POST['loose_stock'])),
                            "stock_unit_id"=>$db->escapeString($fn->xss_clean($_POST['loose_stock_unit_id'])),
                            "serve_for"=>$db->escapeString($fn->xss_clean($_POST['serve_for'])),
                            "barcode_data"=>$db->escapeString($fn->xss_clean($_POST['loose_barcode_text']))
                        );
                        if($_POST['insert_loose_measurement_unit_id'][$i]!='' && $_POST['insert_loose_measurement_unit_id'][$i]!=0){
                            $db->insert('product_variant',$data);
                            $res = $db->getResult();
                        }
                    }
                }
                $error['update_data'] = "<div class='content-header'>
                                                <span class='label label-success'>Product Updated Successfully</span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                                                </div>";
                
            }
            }else{
            $error['check_permission'] = " <div class='content-header'>
                                                <span class='label label-danger'>You have no permission to update product</span>
                                                
                                                
                                                </div>";
            }
        }
        // create array variable to store previous data
        $data = array();
        $sql_query = "SELECT v.*,p.*,v.id as product_variant_id FROM product_variant v JOIN products p ON p.id=v.product_id WHERE p.id=".$ID;
        $db->sql($sql_query);
        $res = $db->getResult();
        foreach($res as $row)
            $data = $row;
        
        //to check if the string is json or not
        function isJSON($string){
            return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
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
    <!-- Main row -->
    <div class="row">
        <div class="col-md-12">
		<?php echo isset($error['update_data']) ? $error['update_data'] : '';?>
		<?=!empty($error['weight'])?$error['weight']:''?>
            <?php if($permissions['products']['update']==0) { ?>
            <div class="alert alert-danger topmargin-sm">You have no permission to update product.</div>
        <?php } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Edit Product</h3>
            </div>
            <!-- form start -->
            <form id='edit_product_form' method="post" enctype="multipart/form-data">
                <?php 
                    $db->select('unit','*');
                    $unit_data = $db->getResult();
                 ?>
              
            <div class="box-body">
                <div class="form-group">
                    <label for="exampleInputEmail1">Product Name</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                    <input type="text" name="name" class="form-control" value="<?php echo $data['name']; ?>"/>
                </div>
                <div class="form-group mb-3">
                        <div class="row">
                            <div class=" col-md-2 ">
                                <label for="price_type">Price Type : </label>
                                <br>
                                <select  class="form-control"name="price_type" id="price_type">
                                    <option <?=($data['price_type']=='including')?'selected':''; ?>  value="including" >Including GST</option>
                                    <option  <?=($data['price_type']=='excluding')?'selected':'';?> value="excluding" >Excluding GST</option>
                                </select>
                            </div>
                            <div class=" col-md-2 ">
                                <label for="hsn">HSN/SAC : </label>
                                <br>
                                <input type="text" class="form-control"  name="hsn" style="display: inline;" value="<?php echo $data['hsn']; ?>">
                            </div>
                            <div class=" col-md-8 ">
                                 <label for="gst">GST Applicable(%) :</label>
                                 <br>
                                 <div class="row">
                                     <div class=" col-md-3 ">
                                      <label for="sgst">SGST</label>
                                      <input type="text" id="sgst" name="sgst" class="form-control" style="max-width: 80px;display: inline;position: relative;left: 3px;" value="<?php echo $data['sgst']; ?>">
                                     </div>
                                     <div class="  col-md-3 ">
                                      <label for="cgst">CGST</label>
                                      <input type="text" id="cgst" name="cgst" class="form-control" style="max-width: 80px;display: inline;" value="<?php echo $data['cgst']; ?>">
                                     </div>
                                     <div class="  col-md-3">
                                      <label for="igst">IGST</label>
                                      <input type="text" id="igst" name="igst" class="form-control" style="max-width: 80px;display: inline;position: relative;left: 8px;" value="<?php echo $data['igst']; ?>">
                                     </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div>
                <label for="type">Type</label><?php echo isset($error['type']) ? $error['type'] : '';?>
                    <div class="form-group">
                      <label class="radio-inline"><input type="radio" name="type"  id="packate" value="packet" <?=($res[0]['type']=="packet")?"checked":""; ?> >Packet</label>
                      <label class="radio-inline"><input type="radio" name="type" id ="loose" value="loose" <?=($res[0]['type']=="loose")?"checked":""; ?> >Loose</label>
                    </div>
                    </div>
                <hr>
                <div id="variations">
                    <h5>Product Variations</h5>
                    <hr>
                    <?php 
                        if(isJSON($data['price'])){
                            $price = json_decode($data['price'],1);
                            $measurement = json_decode($data['measurement'],1);
                            $discounted_price = json_decode($data['discounted_price'],1);
                        }else{
                            $price = array('0'=>$data['price']);
                            $measurement = array('0'=>$data['measurement']);
                            $discounted_price = array('0'=>$data['discounted_price']);
                        }
                //      for($i=0;$i<count($price);$i++){
                        $i=0;
                        if($res[0]['type']=="packet"){
                        foreach($res as $row){
                        ?>
                            <!-- <div id="packate_div" style="display:block"> -->
                            <div class="row packate_div">
                            <input type="hidden" class="form-control" name="product_variant_id[]" id="product_variant_id" value='<?=$row['product_variant_id'];?>'/>
                                <div class="col-item col-item-1" style="width:10%;">
                                    <div class="form-group packate_div">
                                        <label for="exampleInputEmail1">Measurement</label><input type="text" class="form-control" name="packate_measurement[]" value='<?=$row['measurement'];?>' required />
                                    </div>
                                </div>
                                <div class="col-item col-item-1">
                                    <div class="form-group packate_div">
                                        <label for="unit">Unit:</label>
                                        <select class="form-control" name="packate_measurement_unit_id[]">
                                            <?php
                                            // print_r($unit_data);
                                                foreach($unit_data as  $unit){
                                                    echo "<option";
                                                    if($unit['id'] ==$row['measurement_unit_id']){
                                                        echo " selected ";
                                                    }
                                                    echo " value='".$unit['id']."'>".$unit['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-item " style="width:8%">
                                    <div class="form-group packate_div">
                                        <label for="weight">Weight(Kg):</label><input type="text" class="form-control" name="packate_weight[]" id="packate_weight"  value='<?=$row['weight'];?>'/>
                                    </div>
                                </div>
                                <div class="col-item col-item-1">
                                    <div class="form-group packate_div">
                                        <label for="price">MRP  (<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control" name="packate_price[]" id="packate_price"  value='<?=$row['price'];?>' required />
                                    </div>
                                </div>
                                <div class="col-item" style="width:11.3%;">
                                    <div class="form-group packate_div">
                                        <label for="vendor price">Vendor Price  (<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control" name="packate_vendor_price[]" id="packate_vendor_price"  value='<?=$row['vendor_price'];?>' required />
                                    </div>
                                </div>
                                <div class="col-item di_price" style="width:11.3%;">
                                    <div class="form-group packate_div">
                                        <label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control discounted_price" name="packate_discounted_price[]"  value='<?=$row['discounted_price'];?>' <?=($data['price_type']=='including')?'':'readonly';?>/>
                                    </div>
                                </div>
                                <div class="col-item pr_price" style="width:12.1%;">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="packate_product_price[]" value='<?=$row['product_price'];?>' <?=($data['price_type']=='excluding')?'':'readonly';?>/>
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group packate_div">
                                        <label for="qty">SGST(<?=$settings['currency']?>):</label>
                                        <input type="text " class="form-control item_sgst" name="packate_item_sgst[]" value='<?=$row['item_sgst'];?>' readonly/>
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group packate_div">
                                        <label for="qty">CGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_cgst" name="packate_item_cgst[]" value='<?=$row['item_cgst'];?>' readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group packate_div">
                                        <label for="qty">IGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_igst" name="packate_item_igst[]" value='<?=$row['item_igst'];?>' readonly />
                            		</div>
                            	</div>
                                <div class="col-item col-item-1">
                                    <div class="form-group packate_div">
                                        <label for="qty">Stock:</label>
                                        <input type="text" class="form-control" name="packate_stock[]" value='<?=$row['stock'];?>'/>
                                    </div>
                                </div>
                                <div class="col-item col-item-1">
                            	    <div class="form-group packate_div">
                            	        
                                        <label for="qty">Status:</label>
                                        <select name="packate_serve_for[]" class="form-control">
                                            <option value="Available" <?php if(strtolower($row['serve_for'])=="availabel"){ echo "selected"; }?> >Available</option>
                                            <option value="Sold Out" <?php if(strtolower($row['serve_for'])=="sold out"){ echo "selected"; }?> >Sold Out</option>
                                        </select>
                            		</div>
                            	</div>
                                <div class="col-md-10" style="display:none">
                            	    <div class="form-group packate_div">
                                        <label >Barcode Data:</label>
                                        <?php if($row['barcode_data']==''){ 
                                            $barcode_val=$fn->generateEAN();
                                       }else{ 
                                            $barcode_val=$row['barcode_data'];
                                        } ?>
                                <input type="text" name="barcode_text[]" value="<?=$barcode_val?>" style="width:50%" class=" form-control">
                            		</div>
                            	</div>
                                <?php if($i==0){?>
                                    <div class='col-item col-item-1'>
                                        <label>Variation</label>
                                        <a id='add_packate_variation' title='Add variation of product' style='cursor: pointer;'><i class="fa fa-plus-square-o fa-2x"></i></a>
                                    </div>
                                    <?php }else{?>
                                    <div class="col-item col-item-1" style="display: grid;">
                                        <label>Remove</label>
                                        <a class="remove_variation text-danger" data-id="data_delete" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>
                                    </div>
                                <?php }?>
                        </div>
                        <!-- </div> -->
                        <?php $i++;
                        }
                        }else{
                        $db->select('unit','*');
                        $resedit = $db->getResult();
                            ?>
						    <div id="packate_div" style="display:none">
							<div class="row">
							    <div class="col-item col-item-1" style="width:10%;" >
							        <div class="form-group packate_div">
	                                    <label for="exampleInputEmail1">Measurement</label><input type="text" class="form-control" name="packate_measurement[]" required />
	                                </div>
	                            </div>
	                            <div class="col-item col-item-1">
                            	    <div class="form-group packate_div">
                                        <label for="unit">Unit:</label>
                                        <select class="form-control" name="packate_measurement_unit_id[]">
                                            <?php
                                                foreach($resedit as  $row){
                                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                            		</div>
                            	</div>
                            	<div class="col-item" style="width:8%">
                                    <div class="form-group packate_div">
                                        <label for="weight">Weight(Kg):</label><input type="text" class="form-control" name="packate_weight[]" id="packate_weight"/>
                                    </div>
                                </div>
	                            <div class="col-item col-item-1" style="width:10%;">
	                                <div class="form-group packate_div">
	                                    <label for="price">MRP  (INR):</label><input type="text" class="form-control" name="packate_price[]" id="packate_price" required />
                            	    </div>
                            	</div>
                            	<div class="col-item" style="width:11%;">
	                                <div class="form-group packate_div">
	                                    <label for="vendor_price">Vendor Price  (INR):</label><input type="text" class="form-control" name="packate_vendor_price[]" id="packate_vendor_price" required />
                            	    </div>
                            	</div>
                            	<div class="col-item di_price" style="width:11.3%;">
                                    <div class="form-group packate_div">
                            	        <label for="discounted_price">Selling Price:</label>
                            	        <input type="text" class="form-control discounted_price" name="packate_discounted_price[]" <?=($data['price_type']=='including')?'':'readonly';?>/>
                            	    </div>
                            	</div>
                            	<div class="col-item pr_price" style="width:12.1%;">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="packate_product_price[]" <?=($data['price_type']=='excluding')?'':'readonly';?>/>
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
                            	<div class="col-md-2">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Stock:</label>
                                        <input type="text" class="form-control" name="packate_stock[]" />
                            		</div>
                            	</div>
                            	<div class="col-md-1">
                            	    <div class="form-group packate_div">
                                        <label for="unit">Unit:</label>
                                        <select class="form-control" name="packate_stock_unit_id[]">
                                            <?php
                                                foreach($resedit as  $row){
                                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                            		</div>
                            	</div>
                            	<div class="col-md-2">
                            	    <div class="form-group packate_div">
                                        <label for="qty">Status:</label>
                                        <select name="packate_serve_for[]" class="form-control" required>
                                            <option value="Available">Available</option>
                                            <option value="Sold Out">Sold Out</option>
                                        </select>
                            		</div>
                            	</div>
                            	 <div class="form-group col-md-3" style="display:none">
                                <label >Barcode Data:</label>
                                <?php if($row['barcode_data']==''){ 
                                    $barcode_val=$fn->generateEAN();
                               }else{ 
                                    $barcode_val=$row['barcode_data'];
                                } ?>
                                <input type="text" name="loose_barcode_text" value="<?=$barcode_val?>" class=" form-control">
                    		</div>
                            	<div class="col-md-1">
                                    <label>Variation</label>
                                    <a id="add_packate_variation" title="Add variation of product" style="cursor: pointer;"><i class="fa fa-plus-square-o fa-2x"></i></a>
                            	</div>
                            </div>
                        </div>
					    <?php }?>
                        <div id="packate_variations"></div>
                <?php 
                $i=0;
                if($res[0]['type']=="loose"){
                    foreach($res as $row){
                ?>
                        
                <!-- <div id="loose_div" style="display:block;"> -->
                <div class="row loose_div">
                    <input type="hidden" class="form-control" name="product_variant_id[]" id="product_variant_id" value='<?=$row['product_variant_id'];?>'/>
                        <div class="col-item col-item-1" style="width:10%;">
                            <div class="form-group loose_div">
                                <label for="exampleInputEmail1">Measurement</label>
                                <input type="text" class="form-control" name="loose_measurement[]" required="" value='<?=$row['measurement'];?>'>
                            </div>
                        </div>
                        <div class="col-item col-item-1">
                            <div class="form-group loose_div">
                                <label for="unit">Unit:</label>
                                <select class="form-control" name="loose_measurement_unit_id[]">
                                    <?php
                                        foreach($unit_data as  $unit){
                                            echo "<option";
                                            if($unit['id'] ==$row['measurement_unit_id']){
                                                echo " selected ";
                                            }
                                            echo " value='".$unit['id']."'>".$unit['short_code']."</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                            </div>
                            <div class="col-item" style="width:8%">
                                <div class="form-group loose_div">
                                    <label for="weight">Weight(Kg):</label>
                                    <input type="text" class="form-control" name="loose_weight[]" id="loose_weight"  value='<?=$row['weight'];?>'/>
                                </div>
                            </div>
                            <div class="col-item" style="width:10%;">
                                <div class="form-group loose_div">
                                    <label for="price">MRP  (<?=$settings['currency']?>):</label>
                                    <input type="text" class="form-control" name="loose_price[]" id="loose_price" required="" value='<?=$row['price'];?>'>
                                </div>
                            </div>
                            <div class="col-item" style="width:11%;">
                                <div class="form-group loose_div">
                                    <label for="price">Vendor Price  (<?=$settings['currency']?>):</label>
                                    <input type="text" class="form-control" name="loose_vendor_price[]" id="loose_vendor_price" required="" value='<?=$row['vendor_price'];?>'>
                                </div>
                            </div>
                            <div class="col-item di_price" style="width:14%;">
                                <div class="form-group loose_div">
                                    <label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>
                                    <input type="text" class="form-control discounted_price" name="loose_discounted_price[]" id="discounted_price"  value='<?=$row['discounted_price'];?>'<?=($data['price_type']=='including')?'':'readonly';?>/>
                                </div>
                            </div>
                            <div class="col-item pr_price" style="width:12%;">
                            	    <div class="form-group loose_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="loose_product_price[]" value='<?=$row['product_price'];?>' <?=($data['price_type']=='excluding')?'':'readonly';?> />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group loose_div">
                                        <label for="qty">SGST(<?=$settings['currency']?>):</label> 
                                        <input type="text " class="form-control item_sgst" name="loose_item_sgst[]" value='<?=$row['item_sgst'];?>' readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group loose_div">
                                        <label for="qty">CGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_cgst" name="loose_item_cgst[]" value='<?=$row['item_cgst'];?>' readonly />
                            		</div>
                            	</div>
                            	<div class="col-item col-item-1 t_pr">
                            	    <div class="form-group loose_div">
                                        <label for="qty">IGST(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control item_igst" name="loose_item_igst[]" value='<?=$row['item_igst'];?>' readonly />
                            		</div>
                            	</div>
                           
                            <?php if($i==0){?>
                                <div class='col-item col-item-1'>
                                    <label>Variation</label>
                                    <a id='add_loose_variation' title='Add variation of product' style='cursor: pointer;'><i class="fa fa-plus-square-o fa-2x"></i></a>
                                </div>
                            <?php }else{?>
                                <div class="col-item col-item-1" style="display: grid;">
                                    <label>Remove</label>
                                    <a class="remove_variation text-danger" data-id="data_delete" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>
                                </div>
                            <?php }
                                $i++;?>
                            </div>
                            <!-- </div> -->
                        <?php } ?>
                        <div id="loose_variations"></div>
                        <hr>
                        <div id="loose_stock_div" class="row">
                        <div class="form-group col-md-3" >
                            <label for="quantity">Stock :</label><?php echo isset($error['quantity']) ? $error['quantity']:'';?>
                            <input type="text" class="form-control" name="loose_stock" required value='<?=$row['stock'];?>'>
                        </div>
                        <div class="form-group col-md-3">
                            <label for="stock_unit">Unit :</label><?php echo isset($error['stock_unit']) ? $error['stock_unit']:'';?>
                            <select class="form-control" name="loose_stock_unit_id" id="loose_stock_unit_id">
                                <?php                                
                                foreach($unit_data as  $unit){
                                    echo "<option";
                                    if($unit['id'] ==$row['stock_unit_id']){
                                        echo " selected ";
                                    }
                                    echo " value='".$unit['id']."'>".$unit['short_code']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3" id="status_div" <?php if($res[0]['type']=="packet"){ echo "style='display:none'"; }?>>
                                <label for="exampleInputEmail1">Status :</label><?php echo isset($error['serve_for']) ? $error['serve_for'] : '';?>
                                <select name="serve_for" class="form-control">
                                    <option value="Available" <?php if(strtolower($res[0]['serve_for'])=="available"){ echo "selected"; }?> >Available</option>
                                    <option value="Sold Out" <?php if(strtolower($res[0]['serve_for'])=="sold out"){ echo "selected"; }?> >Sold Out</option>
                                </select>
                            </div>
                    	    <div class="form-group col-md-3" style="display:none">
                                <label >Barcode Data:</label>
                                <?php if($row['barcode_data']==''){ 
                                    $barcode_val=$fn->generateEAN();
                               }else{ 
                                    $barcode_val=$row['barcode_data'];
                                } ?>
                                <input type="text" name="loose_barcode_text" value="<?=$barcode_val?>" class=" form-control">
                    		</div>
                        </div>
                            <?php }else{ 
							$db->select('unit','*');
                            $resedit = $db->getResult();
                        ?>
						<div id="loose_div" style="display:none;">
                            <div class="row">
                                <div class="col-item col-item-1" style="width:10%;">
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
                                                foreach($resedit as  $row){
                                                    echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                                }
                                            ?>
                                        </select>
                            		</div>
                            	</div>
                            	<div class="col-item" style="width:8%">
                                    <div class="form-group loose_div">
                                        <label for="weight">Weight(Kg):</label>
                                        <input type="text" class="form-control" name="loose_weight[]" id="loose_weight"/>
                                    </div>
                                </div>
                        		<div class="col-item" style="width:10%;">
                        		    <div class="form-group loose_div">
                            		    <label for="price">MRP  (INR):</label>
                            		    <input type="text" class="form-control" name="loose_price[]" id="loose_price" required="">
                        		    </div>
                        		</div>
                        		<div class="col-item" style="width:10%;">
                        		    <div class="form-group loose_div">
                            		    <label for="price">Vendor Price  (INR):</label>
                            		    <input type="text" class="form-control" name="loose_vendor_price[]" id="loose_vendor_price" required="">
                        		    </div>
                        		</div>
                        		<div class="col-item di_price" style="width:14%;">
                        		    <div class="form-group loose_div">
                                		<label for="discounted_price">Selling Price:</label>
                                		<input type="text" class="form-control discounted_price" name="loose_discounted_price[]" <?=($data['price_type']=='including')?'':'readonly';?>/>
                        		    </div>
                        		</div>
                        		<div class="col-item pr_price" style="width:12%;">
                            	    <div class="form-group loose_div">
                                        <label for="qty">Product Price(<?=$settings['currency']?>):</label>
                                        <input type="text" class="form-control product_price" name="loose_product_price[]" <?=($data['price_type']=='excluding')?'':'readonly';?> />
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
                        <div id="loose_variations"></div>
                         <div id="variations">
						</div>
						<hr>
						<div id="loose_stock_div" class="row" style="display:none;">
                            <div class="form-group col-md-3">
                                <label for="quantity">Stock :</label><?php echo isset($error['quantity']) ? $error['quantity']:'';?>
                                <input type="text" class="form-control" name="loose_stock" required>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="stock_unit">Unit :</label><?php echo isset($error['stock_unit']) ? $error['stock_unit']:'';?>
                                <select class="form-control" name="loose_stock_unit_id" id="loose_stock_unit_id">
                                    <?php
                                    foreach($resedit as $row){
                                        echo "<option value='".$row['id']."'>".$row['short_code']."</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-3" id="status_div" <?php if($res[0]['type']=="packet"){ echo "style='display:none'"; }?>>
                                <label for="exampleInputEmail1">Status :</label><?php echo isset($error['serve_for']) ? $error['serve_for'] : '';?>
                                <select name="serve_for" class="form-control">
                                    <option value="Available" <?php if(strtolower($res[0]['serve_for'])=="available"){ echo "selected"; }?> >Available</option>
                                    <option value="Sold Out" <?php if(strtolower($res[0]['serve_for'])=="sold out"){ echo "selected"; }?> >Sold Out</option>
                                </select>
                            </div>
                    	    <div class="form-group col-md-3" style="display:none">
                                <label >Barcode Data:</label>
                                <?php if($row['barcode_data']==''){ 
                                    $barcode_val=$fn->generateEAN();
                               }else{ 
                                    $barcode_val=$row['barcode_data'];
                                } ?>
                                <input type="text" name="loose_barcode_text" value="<?=$barcode_val?>" class=" form-control">
                    		</div>
    							<?php } ?>
                        
                            
                	</div>
                	<hr>
                	
                    
                        <div class="form-group">
                            <label for="exampleInputEmail1">Category :</label><?php echo isset($error['category_id']) ? $error['category_id'] : '';?>
                            <select name="category_id"  id="category_id" class="form-control">
                            <?php 
                                if($permissions['categories']['read']==1){ 
                                foreach($category_data as $row){ ?>
                                <option value="<?php echo $row['id']; ?>" <?=($row['id'] == $data['category_id'])?"selected":"";?>><?php echo $row['name']; ?></option>
                            <?php } }else{ ?>
                                    <option value="" >---Select Category---</option>
                                <?php } ?>?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="exampleInputEmail1">Sub Category :</label><?php echo isset($error['subcategory_id']) ? $error['subcategory_id'] : '';?>
                            <select name="subcategory_id" id="subcategory_id" class="form-control">

                                <?php 
                                if($permissions['subcategories']['read']==1){
                                    if($res[0]['subcategory_id']==0){?>
                                        <option value=0>No Subcategory</option>
                                   <?php }else{
                                       
                                $sql = "select * from subcategory WHERE category_id=".$data['category_id'];
                                $db->sql($sql);
                                $subcategory = $db->getResult();
                                foreach($subcategory as $subcategories){ 
                                    $selected = "";
                                    if($res[0]['subcategory_id']==$subcategories['id']){ 
                                            $selected = "selected";                                
                                    } ?>
                                    <option value="<?=$subcategories['id'];?>" <?=$selected?>><?=$subcategories['name']; ?></option>
                                <?php } }} else {?>
                                    <option value=0>No Subcategory</option>
                                <?php } ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="exampleInputEmail1">Brand :</label><?php echo isset($error['brand_id']) ? $error['brand_id'] : '';?>
                            <select name="brand_id"  id="brand_id" class="form-control">
                            <?php 
                                if($permissions['categories']['read']==1){ 
                                foreach($brand_data as $row){ ?>
                                <option value="<?php echo $row['id']; ?>" <?=($row['id'] == $data['brand_id'])?"selected":"";?>><?php echo $row['name']; ?></option>
                            <?php } }else{ ?>
                                    <option value="0" >---Select Brand---</option>
                                <?php } ?>?>
                            </select>
                        </div>
                        
                        
                        <div class="form-group">
                            <label for="exampleInputFile">Image&nbsp;&nbsp;&nbsp;*Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['image']) ? $error['image'] : '';?>
                            <input type="file" name="image" id="image" title="Please choose square image of larger than 350px*350px & smaller than 550px*550px."/><br />
                            <img src="<?php echo $data['image']; ?>" width="210" height="160"/>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputFile">Other Images *Please choose square image of larger than 350px*350px & smaller than 550px*550px.</label><?php echo isset($error['other_images']) ? $error['other_images'] : '';?>
                            <input type="file" name="other_images[]" id="other_images" multiple title="Please choose square image of larger than 350px*350px & smaller than 550px*550px."/><br />
                            <?php
                            if(!empty($data['other_images'])){
                                $other_images = json_decode($data['other_images']);
                            
                            for($i=0;$i<count($other_images);$i++){ ?>
                            <img src="<?=$other_images[$i]; ?>" width="210" height="160"/>
                            <a class='btn btn-xs btn-danger delete-image' data-i='<?=$i;?>' data-pid='<?=$_GET['id'];?>'>Delete</a>
                            <?php }
                            }?>
                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Description :</label><?php echo isset($error['description']) ? $error['description'] : '';?>
                            <textarea name="description" id="description" class="form-control" rows="16"><?php echo $data['description']; ?></textarea>
                            <script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
                            <script type="text/javascript">                        
                                CKEDITOR.replace( 'description' );
                            </script>
                        </div>
                        <div class="form-group col-md-3" style="max-width:200px;">
                            <label for="exampleInputEmail1">Minimum Stock</label><?php echo isset($error['min_stock']) ? $error['min_stock'] : '';?>
                            <input type="number" class="form-control"  name="min_stock" value="<?=$data['min_stock']?>" >
                        </div>
                        
                        <div class="form-group" style="max-width:200px;display:none;">
                            <label for="exampleInputEmail1">Minimum Order Quantity</label><?php echo isset($error['min_order_qty']) ? $error['min_order_qty'] : '';?>
                            <input type="number" class="form-control"  name="min_order_qty" value="<?=$data['min_order_qty']?>" >
                        </div>
                        
                        <div class="form-group col-md-3" style="max-width:200px;">
                            <label for="product_status">Status </label>
                            <br>
                            <select  class="form-control"name="product_status" id="product_status">
                                <option <?=($data['status']=='1')?'selected':''; ?>  value="1" >Enabled</option>
                                <option  <?=($data['status']=='0')?'selected':'';?> value="0" >Disabled</option>
                            </select>
                        </div>

                        <div class="form-group col-md-3" style="max-width:200px;">
                            <label for="indicator">Indicator  </label>
                            <br>
                            <select  class="form-control"name="indicator" id="indicator">
                                <option <?=($data['indicator']=='0')?'selected':''; ?> value="0" >None</option>
                                <option <?=($data['indicator']=='1')?'selected':''; ?> value="1" >Veg</option>
                                <option <?=($data['indicator']=='2')?'selected':''; ?> value="2" >Non Veg</option>
                            </select>
                        </div>
                    
                            
                    </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="submit" class="btn-primary btn" value="Update" name="btnEdit" />
                    </div>
        </form>
        </div><!-- /.box -->
        </div>
    </div>
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
<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>