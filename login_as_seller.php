<?php
session_start();
    include_once('includes/crud.php');
    $db = new Database;
    $db->connect();
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
    $settings = $fn->get_settings('system_timezone',true);
    $app_name = $settings['app_name'];
	include('./includes/variables.php'); 
	include 'api-firebase/send-sms.php';	

	if (isset($_GET['id'])) {
    $id = $fn->xss_clean($_GET['id']);    
    $currentTime = time() + 25200;
    $response = array();
    $role = $_SESSION['role'];
    $secretlogin = $_SESSION['secretlogin'];
    if (!empty($id) && $role != 'seller') {
        $sql_query = "SELECT * FROM seller WHERE id = '$id' AND status = '1'";
        $db->sql($sql_query);
        $res = $db->getResult();
        $num = $db->numRows($res);

        if ($num == 1) {
            $_SESSION['id'] = $res[0]['id'];
            $_SESSION['role'] = "seller";
            $_SESSION['user'] = $res[0]['name'];
            $_SESSION['secretkey'] = rand();
            $_SESSION['timeout'] = $currentTime + 3600000000000;
            $_SESSION['main_cat_id'] = $res[0]['main_cat_id'];
            $_SESSION['secretlogin'] = 'yes';

            header("location:home.php");
        } else {
            header("location:index.php");
        }
    }else if ($secretlogin == 'yes' && $role == 'seller') {
        $sql_query = "SELECT * FROM admin WHERE id = '1' AND applicable_for='web'";
        $db->sql($sql_query);
        $res = $db->getResult();
        $num = $db->numRows($res);
        $secretkey=rand();
        $currentTime = time() + 25200;

        if ($num == 1) {
            $sql="UPDATE admin SET web_login='".$secretkey."' WHERE id=".$res[0]['id'];
    		$db->sql($sql);
    		$db->getResult();
            $_SESSION['id'] = $res[0]['id'];
            $_SESSION['role'] = $res[0]['role'];
            $_SESSION['user'] = $res[0]['username'];
            $_SESSION['secretkey'] = $secretkey;
            $_SESSION['timeout'] = $currentTime + 3600000000000;
            $_SESSION['secretlogin'] = 'no';

            header("location:home.php");
        } else {
            header("location:index.php");
        }
    } else {
        header("location:index.php");
    }

    echo json_encode($response);
    return;
}

	
	?>