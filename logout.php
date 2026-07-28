<?php
//session_save_path("../temp");
session_start();
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
// 	session_destroy();
	header("location:$redirect");
?>