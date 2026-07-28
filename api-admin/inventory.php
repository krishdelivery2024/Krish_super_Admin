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
    if($_POST['type']=="cat-sub-product-list"){
		$sql="SELECT id,name,status FROM category ORDER BY name";
		$db->sql($sql);
		$res = $db->getResult();
		$resp=array();
    		for($i=0;$i<count($res);$i++){
    		    $resp[$i]['category_name']=$res[$i]['name'];
    		    $sql1="SELECT id,name,status FROM subcategory WHERE category_id=".$res[$i]['id']." ORDER BY name";
    		    $db->sql($sql1);
    		    $res1 = $db->getResult();
    		    if(count($res1)==0){
    		        $sql1="SELECT id,name,status FROM products WHERE category_id=".$res[$i]['id']." ORDER BY name";
		            $db->sql($sql1);
		            $res1 = $db->getResult();
		            $resp[$i]['products']=$res1;
    		    }else{
        		    for($j=0;$j<count($res1);$j++){
    		            $resp[$i]['subcategory'][$j]['subcategory_name']=$res1[$j]['name'];
        		        $sql2="SELECT id,name,status FROM products WHERE subcategory_id=".$res1[$j]['id']." ORDER BY name";
        		         // echo $sql2;
        		          $db->sql($sql2);
        		          $res2 = $db->getResult();
        		          $resp[$i]['subcategory'][$j]['products']=$res2;
        		    }
    		    }
    		}
		$response['error']     = false;
		$response['data'] = $resp;
    }else if($_POST['type']=="cat-product-list"){
        $seller_id = $_POST['admin_id'];
        //$sql="SELECT id,name,status FROM category ORDER BY name";
	    $sql="SELECT DISTINCT c.id, c.name, c.status
            FROM category c
            INNER JOIN products p ON p.category_id = c.id
            WHERE p.seller_id = $seller_id
            ORDER BY c.name";
		$db->sql($sql);
		$res = $db->getResult();
		$resp=array();
		for($i=0;$i<count($res);$i++){
		    $resp[$i]['category_name']=$res[$i]['name'];
		    $sql1="SELECT id as product_id,name,status FROM products WHERE category_id=".$res[$i]['id']." ORDER BY name";
		    $db->sql($sql1);
		    $res1 = $db->getResult();
		    $resp[$i]['products']=$res1;
		}
		$response['error']     = false;
		$response['data'] = $resp;
    }else if($_POST['type']=="subcat-product-list"){
		$sql="SELECT id,name,status FROM subcategory ORDER BY name";
		$db->sql($sql);
		$res = $db->getResult();
		$resp=array();
		for($i=0;$i<count($res);$i++){
		    $resp[$i]['subcategory_name']=$res[$i]['name'];
		    $sql1="SELECT id,name,status FROM products WHERE subcategory_id=".$res[$i]['id']." ORDER BY name";
		    $db->sql($sql1);
		    $res1 = $db->getResult();
		    $resp[$i]['products']=$res1;
		}
		$response['error']     = false;
		$response['data'] = $resp;
    }else if($_POST['type']=="product-list"){
		$sql="SELECT id,name,status FROM products ORDER BY name";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="category-list"){
    	$sql="SELECT id,name,status FROM category ORDER BY name";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }else if($_POST['type']=="subcategory-list"){
    	$sql="SELECT id,name,status FROM subcategory ORDER BY name";
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
    }
    //     else if($_POST['type']=="product-update"){
    //         if($_POST['status']==0){
    //             $status="Sold Out";
    //         }else{
    //             $status="Available";
    //         }
    //         $sql="UPDATE products SET status=".$_POST['status']." WHERE id=".$_POST['id'];
    // 		$db->sql($sql);
    // 		$res = $db->getResult();
    // 		$sql="UPDATE product_variant SET serve_for='".$status."' WHERE product_id=".$_POST['id'];
    // 		$db->sql($sql);
    // 		$res = $db->getResult();
    // 		$response['error']     = false;
    // // 		$response['data'] = $res;
    // 		$response['message'] = "Updated Successfully";
    //     }

    else if ($_POST['type'] == "product-update") {
    
        if (!isset($_POST['id']) || !isset($_POST['status'])) {
            $response['error'] = true;
            $response['message'] = "Required fields are missing";
            echo json_encode($response);
            exit;
        }
    
        $id = $_POST['id'];
        // if ($id <= 0) {
        //     $response['error'] = true;
        //     $response['message'] = "Invalid Product ID";
        //     echo json_encode($response);
        //     exit;
        // }
    
        $status_value = $_POST['status'];
    
        $status_text = ($status_value == 0) ? "Sold Out" : "Available";
    
        $sql = "UPDATE products SET status = $status_value WHERE id = $id";
        $db->sql($sql);
        
        $sql = "UPDATE product_variant SET serve_for = '$status_text' WHERE product_id = $id";
        $db->sql($sql);
    
        $response['error'] = false;
        $response['message'] = "Updated Successfully";
    }
    
    else if($_POST['type']=="category-update"){
        $sql="UPDATE category SET status=".$_POST['status']." WHERE id=".$_POST['id'];
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
		$response['message'] = "Updated Successfully";
    }else if($_POST['type']=="subcategory-update"){
        $sql="UPDATE subcategory SET status=".$_POST['status']." WHERE id=".$_POST['id'];
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