<?php
//session_save_path("../temp");
session_start();
if (!empty($_SESSION['id']) && !empty($_SESSION['secretkey'])) {
    include_once('includes/crud.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $fn->remove_admin_session($_SESSION['id'], $_SESSION['secretkey']);
}
if($_SESSION['role'] == 'seller'){
$redirect ='seller-login.php';
}else{
$redirect ='index.php';
}	
	unset($_SESSION['user']);
	unset($_SESSION['id']);
	unset($_SESSION['role']);
	unset($_SESSION['timeout']);
	unset($_SESSION['secretlogin']);
	unset($_SESSION['secretkey']);
// 	session_destroy();
	header("location:$redirect");
?>