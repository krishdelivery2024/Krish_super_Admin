<?php
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	
?>
<?php $page="Home";
include"header.php";?>

<title><?=$settings['app_name']?> - Dashboard</title>

      
        <?php include('public/loginusers_table.php'); ?>
      
  
<?php include"footer.php";?>

