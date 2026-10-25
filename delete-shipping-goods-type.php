<?php
session_start();
header("Expires: on, 01 Jan 1970 00:00:02 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (!isset($_SESSION['user'])) {
    header("location:index.php");
    exit();
}

include_once('includes/crud.php');
$db = new Database();
$db->connect();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id > 0) {
    $db->sql("DELETE FROM `shipping_goods_types` WHERE `id` = $id");
}

$db->disconnect();
header("location:shipping-goods-types.php");
exit();
?>
