<?php
header('Access-Control-Allow-Origin: *');
header("Content-Type: application/json");
header("Expires: 0");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('../includes/crud.php');
$db=new Database();
include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$db->connect(); 
include_once('../includes/variables.php');
include_once('verify-token.php');
/* accesskey:90336 */
if(!verify_token()){
    return false;
}
if(isset($_POST['accesskey'])) {
	$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));		
	$main_cat = (isset($_POST['main_cat_id']))?$db->escapeString($fn->xss_clean($_POST['main_cat_id'])):"0";
	if($access_key_received == $access_key){
		// get all category data from category table
		$sql_query = "SELECT * 
			FROM category 
			WHERE main_cat = '$main_cat'
			ORDER BY id ASC ";
		$db->sql($sql_query);
		$res=$db->getResult();
		// $categories = array();
		if (!empty($res)) {
			for($i=0;$i<count($res);$i++){
				// $categories[] = array('category'=>$i);
				$res[$i]['image'] = (!empty($res[$i]['image']))?DOMAIN_URL.''.$res[$i]['image']:'';
				    $sql_query1 = "SELECT count(id) as count 
			FROM subcategory WHERE category_id=".$res[$i]['id']."
			GROUP BY category_id ";
		$db->sql($sql_query1);
		$res1=$db->getResult();
		if (!empty($res1)) {
		    $res[$i]['subcategory']="Yes";
		}else{
		     $res[$i]['subcategory']="No";
		}
			}
			$response['error'] = "false";
			$response['data'] = $res;
		}else{
			$response['error'] = "true";
			$response['message'] = "No data found!";
		}
	print_r(json_encode($response));
	}else{
		die('accesskey is incorrect.');
	}
} else {
	die('accesskey is require.');
}
//Output the output.
// echo $output;
$db->disconnect(); 
?>