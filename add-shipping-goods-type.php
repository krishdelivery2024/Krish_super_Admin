<?php
session_start();
header("Expires: on, 01 Jan 1970 00:00:02 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user'])) {
    header("location:index.php");
}
$page = "Shipping Cargo Types";
include "header.php";
?>
<html>
<head>
    <title>Add Cargo Type | <?= $settings['app_name'] ?> - Dashboard</title>
</head>
<body>
    <div class="content-wrapper">
        <?php include('public/add-shipping-goods-type-form.php'); ?>
    </div>
    <?php include "footer.php"; ?>
</body>
</html>
