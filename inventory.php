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
        $seller_id = isset($_POST['admin_id']) ? (int)$_POST['admin_id'] : 0;
        
        // 1. Fetch categories explicitly selected by this seller
        $sql = "SELECT c.id, c.name, c.status 
                FROM category c 
                INNER JOIN seller_categories sc ON sc.category_id = c.id 
                WHERE sc.seller_id = $seller_id 
                ORDER BY c.name ASC";
        $db->sql($sql);
        $res = $db->getResult();

        // 2. Check seller.categories column fallback
        if (empty($res)) {
            $db->sql("SELECT * FROM seller WHERE id = $seller_id LIMIT 1");
            $s_res = $db->getResult();
            if (!empty($s_res)) {
                $raw_cats = trim($s_res[0]['categories'] ?? '');
                if (!empty($raw_cats)) {
                    $c_arr = array_filter(array_map('intval', explode(',', $raw_cats)));
                    if (!empty($c_arr)) {
                        $c_str = implode(',', $c_arr);
                        $db->sql("SELECT id, name, status FROM category WHERE id IN ($c_str) ORDER BY name ASC");
                        $res = $db->getResult();
                    }
                }
                // 3. Fallback for unconfigured sellers: show categories for their Business Category (main_cat_id)
                if (empty($res) && !empty($s_res[0]['main_cat_id'])) {
                    $mcat_id = (int)$s_res[0]['main_cat_id'];
                    $db->sql("SELECT id, name, status FROM category WHERE main_cat = $mcat_id AND status = 1 ORDER BY name ASC");
                    $res = $db->getResult();
                }
            }
        }

        $resp = array();
        for ($i = 0; $i < count($res); $i++) {
            $resp[$i]['category_name'] = $res[$i]['name'];
            $cat_id = (int)$res[$i]['id'];
            $sql1 = "SELECT id as product_id, name, status FROM products WHERE category_id = $cat_id AND seller_id = $seller_id ORDER BY name ASC";
            $db->sql($sql1);
            $res1 = $db->getResult();
            $resp[$i]['products'] = !empty($res1) ? $res1 : array();
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
    }else if($_POST['type']=="product-update"){
        if($_POST['status']==0){
            $status="Sold Out";
        }else{
            $status="Available";
        }
        $sql="UPDATE products SET status=".$_POST['status']." WHERE id=".$_POST['id'];
		$db->sql($sql);
		$res = $db->getResult();
		$sql="UPDATE product_variant SET serve_for='".$status."' WHERE product_id=".$_POST['id'];
		$db->sql($sql);
		$res = $db->getResult();
		$response['error']     = false;
		$response['data'] = $res;
		$response['message'] = "Updated Successfully";
    }else if($_POST['type']=="category-update"){
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