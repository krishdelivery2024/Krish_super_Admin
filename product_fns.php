<?php 
/*login*/
    header('Access-Control-Allow-Origin: *');
    header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
session_start();
include '../includes/crud.php';
include_once('../includes/variables.php');
include_once('../api-firebase/verify-token.php');
    $db = new Database();
    $db->connect();
    include_once('../includes/custom-functions.php');
    include_once('../includes/functions.php');
    $function = new functions;
    $fn = new custom_functions;
    date_default_timezone_set('Asia/Kolkata');
   /* accesskey:90336
    mobile:9974692496
    password:36652
    status:1   // 1 - Active & 0 Deactive */
$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
if($access_key != $accesskey){
    $response['error']= true;
    $response['message']="invalid accesskey";
    print_r(json_encode($response));
    return false;
}
/*
if(isset($_POST['secretkey']) && isset($_POST['admin_id'])){
     $sql_login="select id from `admin` where id=".$_POST['admin_id']." AND app_login='".$_POST['secretkey']."'";
        $db->sql($sql_login);
        $sql_login=$db->getResult();  
        if(count($sql_login)==0){
            $response['error']= true;
            $response['message']="secretkey mismatch";
            print_r(json_encode($response));
            return false;
        }
}else{
    $response['error']= true;
    $response['message']="secretkey mismatch";
    print_r(json_encode($response));
    return false;
}*/
if(!verify_token()){
	return false;
}

if(isset($_POST['type'])){ 
   // print_r($_POST['type']);
    if($_POST['type']=="add_product"){
        $barcode = $db->escapeString($fn->xss_clean($_POST['barcode']));
        $name = $db->escapeString($fn->xss_clean($_POST['name']));
		$slug = $function->slugify($fn->xss_clean($_POST['name']));
    	$category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    	$subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
    	$brand_id = $db->escapeString($fn->xss_clean($_POST['brand_id']));
    	$serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    	$description = $db->escapeString($fn->xss_clean($_POST['description']));
    	$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    	$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    	$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
    	$other_images='';
        if(!empty($barcode) && !empty($name) && !empty($category_id)){ 
            
    		$string = '0123456789';
    		$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
    		$extension = end(explode(".", $_FILES["image"]["name"]));
    		$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
    			
    		// upload new image
    		$upload = move_uploaded_file($_FILES['image']['tmp_name'], '../upload/images/'.$image);
    		$upload_image='upload/images/'.$image;
            $sql="INSERT INTO products (name,slug,category_id,subcategory_id,brand_id,image,other_images,description) VALUES('$name','$slug','$category_id','$subcategory_id','$brand_id','$upload_image','$other_images','$description')";
               // echo $sql;
                $db->sql($sql);
                $res = $db->getResult();
                $sql="SELECT id from products ORDER BY id DESC";
                $db->sql($sql);
                $res_inner=$db->getResult();
                $product_id=$db->escapeString($res_inner[0]['id']);
                    $type="packet";
                    $measurement=$db->escapeString($fn->xss_clean($_POST['measurement']));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
                    $price=$db->escapeString($fn->xss_clean($_POST['price']));
                    $discounted_price=!empty($fn->xss_clean($_POST['discounted_price'])) ? $db->escapeString($fn->xss_clean($_POST['discounted_price'])) : 0;
                    $serve_for="Available";
                    $stock=$_POST['old_stock']+$_POST['new_stock'];
                    $stock_unit_id=$db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
                    $barcode_data=$db->escapeString($fn->xss_clean($_POST['barcode']));
                
                $sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,price,discounted_price,serve_for,stock,stock_unit_id,barcode_data) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$price','$discounted_price','$serve_for','$stock','$stock_unit_id','$barcode_data')";
                $db->sql($sql);
                $result = $db->getResult();
                $sql="SELECT id from product_variant ORDER BY id DESC";
                $db->sql($sql);
                $res1=$db->getResult();
                $variant_id=$res1[0]['id'];
                $expire_date=$_POST['expire_date'];
                $batch_no=$_POST['batch_no'];
                $old_stock=$_POST['old_stock'];
                $new_stock=$_POST['new_stock'];
        		$sql="INSERT INTO product_inventory(variant_id,batch_no,expire_date,stock_added,remaining_stock) VALUES('$variant_id','$batch_no','$expire_date','$new_stock','$new_stock') ";
        		$db->sql($sql);
        		$res = $db->getResult();
                $response['error']     = false;
		        $response['data'] = array();
		        $response['message'] = "Added Successfully";
        }else{
           $response['error']     = true;
	$response['message']   = "Something went Wrong3"; 
        }
    }
    if($_POST['type']=="get_product"){
        $variant_id=$db->escapeString($fn->xss_clean($_POST['variant_id']));
        $sql="SELECT p.id AS id, pv.id AS variant_id, p.name, p.image,p.category_id,p.subcategory_id, p.brand_id,p.description, pv.price, pv.discounted_price, pv.measurement,pv.measurement_unit_id, pv.serve_for, pv.stock,pv.barcode_data, u.short_code 
            FROM `products` p JOIN `product_variant` pv ON pv.product_id = p.id LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id WHERE pv.id=".$variant_id;
        $db->sql($sql);
        $res = $db->getResult();
       
		$response['error']     = false;
		$response['data'] = $res;
    }
    if($_POST['type']=="edit_product"){
        $variant_id=$db->escapeString($fn->xss_clean($_POST['variant_id']));
        $barcode = $db->escapeString($fn->xss_clean($_POST['barcode']));
        $name = $db->escapeString($fn->xss_clean($_POST['name']));
		$slug = $function->slugify($fn->xss_clean($_POST['name']));
    	$category_id = $db->escapeString($fn->xss_clean($_POST['category_id']));
    	$subcategory_id = $db->escapeString($fn->xss_clean($_POST['subcategory_id']));
    	$brand_id = $db->escapeString($fn->xss_clean($_POST['brand_id']));
    	$serve_for = $db->escapeString($fn->xss_clean($_POST['serve_for']));
    	$description = $db->escapeString($fn->xss_clean($_POST['description']));
    	$image = $db->escapeString($fn->xss_clean($_FILES['image']['name']));
    	$image_error = $db->escapeString($fn->xss_clean($_FILES['image']['error']));
    	$image_type = $db->escapeString($fn->xss_clean($_FILES['image']['type']));
    	$other_images='';
        if(!empty($barcode) && !empty($name) && !empty($category_id)){ 
            if(!empty($image)){
        		$string = '0123456789';
        		$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
        		
        		$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
        			$sql = "SELECT pv.product_id, (SELECT image FROM products p WHERE p.id=pv.product_id) AS image FROM product_variant pv WHERE pv.id =".$variant_id;
    $db->sql($sql);
    $res = $db->getResult();
    $product_id=$res[0]['product_id'];
        $previous_menu_image = $res[0]['image'];
        $delete = unlink("$previous_menu_image");
        		// upload new image
        		$extension = end(explode(".", $_FILES["image"]["name"]));
        		$image = $function->get_random_string($string, 4)."-".date("Y-m-d").".".$extension;
        		$upload = move_uploaded_file($_FILES['image']['tmp_name'], '../upload/images/'.$image);
        		$upload_image='upload/images/'.$image;
        		$sql="UPDATE products SET name = '$name' ,slug = '$slug' , category_id = '$category_id' ,subcategory_id = '$subcategory_id', brand_id = '$brand_id', image = '$upload_image', description = '$description' WHERE id = $product_id";
            }else{
                $sql = "SELECT pv.product_id, (SELECT image FROM products p WHERE p.id=pv.product_id) AS image FROM product_variant pv WHERE pv.id =".$variant_id;
    $db->sql($sql);
    $res = $db->getResult();
    $product_id=$res[0]['product_id'];
                $sql="UPDATE products SET name = '$name' ,slug = '$slug' , category_id = '$category_id' ,subcategory_id = '$subcategory_id', brand_id = '$brand_id', description = '$description' WHERE id = $product_id";
            }
                $db->sql($sql);
                $res = $db->getResult();
                    $type="packet";
                    $measurement=$db->escapeString($fn->xss_clean($_POST['measurement']));
                    $measurement_unit_id=$db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
                    $price=$db->escapeString($fn->xss_clean($_POST['price']));
                    $discounted_price=!empty($fn->xss_clean($_POST['discounted_price'])) ? $db->escapeString($fn->xss_clean($_POST['discounted_price'])) : 0;
                    $serve_for="Available";
                    $stock=$_POST['old_stock']+$_POST['new_stock'];
                    $stock_unit_id=$db->escapeString($fn->xss_clean($_POST['measurement_unit_id']));
                    $barcode_data=$db->escapeString($fn->xss_clean($_POST['barcode']));
                
                $sql="Update product_variant SET product_id='$product_id',type='$type',measurement='$measurement',measurement_unit_id='$measurement_unit_id',price='$price',discounted_price='$discounted_price',serve_for='$serve_for',stock='$stock',stock_unit_id='$stock_unit_id',barcode_data='$barcode_data' WHERE id=".$variant_id;
                $db->sql($sql);
                $result = $db->getResult();
                $expire_date=$_POST['expire_date'];
                $batch_no=$_POST['batch_no'];
                $old_stock=$_POST['old_stock'];
                $new_stock=$_POST['new_stock'];
        		$sql="INSERT INTO product_inventory(variant_id,batch_no,expire_date,stock_added,remaining_stock) VALUES('$variant_id','$batch_no','$expire_date','$new_stock','$new_stock') ";
        		$db->sql($sql);
        		$res = $db->getResult();
                $response['error']     = false;
		        $response['data'] = array();
		        $response['message'] = "Product Updated Successfully";
        }else{
            $response['error']     = true;
	$response['message']   = "Something went Wrong3";
        }
    }
}else{
   $response['error']     = true;
	$response['message']   = "Something went Wrong2"; 
}
echo json_encode($response);
$db->disconnect();
?>