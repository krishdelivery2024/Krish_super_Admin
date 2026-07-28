<?php 
session_start();
ini_set('display_errors', 1);
//importing required files
require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
require_once 'includes/functions.php';
require_once('includes/firebase.php');
require_once ('includes/push.php');


$fnc = new functions;

include_once('includes/custom-functions.php');
    
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);

$response = array(); 

if($_SERVER['REQUEST_METHOD']=='POST'){
    if(isset($_POST['edit_store'])){
        $id = $db_con->escapeString($fn->xss_clean($_POST['id']));
        $sql = "SELECT * from stores WHERE id = ".$id;
    		    $db_con->sql($sql);
    		    $result=$db_con->getResult();
    		    print_r(json_encode($result[0]));
    		    exit;
    }
	if(isset($_POST['uname']) && isset($_POST['sname'])) {
	    $response=array();
		$sname = $db_con->escapeString($fn->xss_clean($_POST['sname']));
		$address = $db_con->escapeString($fn->xss_clean($_POST['address']));
		$area = $db_con->escapeString($fn->xss_clean($_POST['area']));
		$pincode = $db_con->escapeString($fn->xss_clean($_POST['pincode']));
		$cname = $db_con->escapeString($fn->xss_clean($_POST['cname']));
		$cmobile = $db_con->escapeString($fn->xss_clean($_POST['cmobile']));
		$cemail = $db_con->escapeString($fn->xss_clean($_POST['cemail']));
		$uname = $db_con->escapeString($fn->xss_clean($_POST['uname']));
		$password = $db_con->escapeString($fn->xss_clean($_POST['password']));
		
		if(isset($_POST['sid']) && !empty($_POST['sid'])){
		       $sql = "SELECT password from stores WHERE id = ".$_POST['sid'];
    		    $db_con->sql($sql);
    		    $result=$db_con->getResult();
    		    foreach($result as $row){
    		        if($row['password']!=$password){
    		            $password=md5($password);
    		        }
    		    }
    		    $sql="UPDATE stores SET sname='".$sname."',address='".$address."',area='".$area."',pincode='".$pincode."',cname='".$cname."',cmobile='".$cmobile."',cemail='".$cemail."',username='".$uname."',password='".$password."' WHERE id=".$_POST['sid'];
    		    $db_con->sql($sql);
    		    $db_con->getResult();
    		    $response['error'] = false;
    	        $response["message"] = "<span class='label label-success'>Store Updated Successfully!</span>";
		}else{
		    $sql="SELECT * FROM stores";
    		$db_con->sql($sql);
    		if($db_con->numRows()>=3){
    		    $response['error']=true;
    	        $response['message']="<span class='label label-danger'>Allowed only 3 Stores</span>";
    	        print_r(json_encode($response));
    		    exit;
    		}
		    $sql="SELECT * FROM stores WHERE sname='".$sname."' OR username='".$uname."'";
    		$db_con->sql($sql);
    		if($db_con->numRows()>0){
    		    $response['error']=true;
    	        $response['message']="<span class='label label-danger'>StoreName or UserName Already Exists</span>";
    	        print_r(json_encode($response));
    		    exit;
    		}
		    $sql = "INSERT INTO `stores`(`sname`, `address`,  `area`, `pincode`, `cname`,`cmobile`,`cemail`,`username`,`password`) VALUES 
			('".$sname."','".$address."','".$area."','".$pincode."','".$cname."','".$cmobile."','".$cemail."','".$uname."','".md5($password)."')";
		    $db_con->sql($sql);
		    $db_con->getResult();
		    $response['error'] = false;
	        $response["message"] = "<span class='label label-success'>Store Added Successfully!</span>";
		}
		print_r(json_encode($response));
		exit;
		
	}
	
}else{
	$response['error']=true;
	$response['message']="<span class='label label-danger'>Invalid request</span>";
}
echo(json_encode($response));

?>
