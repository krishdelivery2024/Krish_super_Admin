<?php
session_start();
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('includes/crud.php');
$db = new Database();
$db->connect();
$page = "Passenger Ride Pricing";
include_once('includes/custom-functions.php');
$fn = new custom_functions;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Passenger Ride Pricing | <?=$settings['app_name']?></title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <?php include('header.php'); ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <div class="content-wrapper">
        <?php include('public/service-pricing-form.php'); ?>
    </div>
    <?php include('footer.php'); ?>
</div>
</body>
</html>
