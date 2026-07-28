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
    if($_POST['type']=="get_inventory"){
        $barcode = $db->escapeString($fn->xss_clean($_POST['barcode']));
        if(!empty($barcode)){ 
            $sql256="SELECT p.name,p.image,p.description, pv.id AS variant_id,pv.product_id,pv.measurement, pv.price, pv.discounted_price, pv.barcode_data, (SELECT c.name FROM category c WHERE p.category_id=c.id) as category_name,(SELECT b.name FROM brand b WHERE p.brand_id=b.id) as brand_name,(SELECT short_code FROM unit u WHERE u.id=pv.measurement_unit_id) as measurement_unit_name,(SELECT short_code FROM unit u WHERE u.id=pv.stock_unit_id) as stock_unit_name FROM product_variant pv JOIN products p ON p.id=pv.product_id WHERE pv.barcode_data = '".$barcode."' ";
               // echo $sql256;
                $db->sql($sql256);
                $res = $db->getResult();
                if((count($res))>0){
                    $sql="SELECT SUM(remaining_stock) AS stock FROM product_inventory WHERE variant_id = '".$res[0]['variant_id']."' ";
                    $db->sql($sql);
                    $result = $db->getResult();
                    if(is_null($result[0]['stock'])){
                        $res[0]['stock']="0";
                    }else{
                        $res[0]['stock']=$result[0]['stock'];
                    }
                    $response['error']     = false;
    		        $response['data'] = $res;
                }else{
                    $response['error']     = true;
            $response['data']=array();
	    $response['message']   = "No Product available";
                }
                
        }else{
            $response['error']     = true;
            $response['data']=array();
	    $response['message']   = "Something went Wrong";
        }
    }else if($_POST['type']=="update_inventory"){
        $variant_id=$_POST['variant_id'];
        $expire_date=$_POST['expire_date'];
        $batch_no=$_POST['batch_no'];
        $old_stock=$_POST['old_stock'];
        $new_stock=$_POST['new_stock'];
        $updated_stock=$old_stock+$new_stock;
        $sql="SELECT stock FROM product_variant WHERE id = ".$variant_id;
        $db->sql($sql);
        $result = $db->getResult();
        $stock=$result[0]['stock'];
                
        $sql="UPDATE product_variant SET stock='".$updated_stock."' WHERE id=".$variant_id;
		$db->sql($sql);
		$res = $db->getResult();
		$sql="INSERT INTO product_inventory(variant_id,batch_no,expire_date,stock_added,remaining_stock) VALUES('$variant_id','$batch_no','$expire_date','$new_stock','$new_stock') ";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
		$response['message'] = "Updated Successfully";
    }else{
        $response['error']     = true;
	    $response['message']   = "Something went Wrong";
    }
}else{
    $response['error']     = true;
	$response['message']   = "Something went Wrong";
}
echo json_encode($response);
$db->disconnect();
?>