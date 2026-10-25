<?php
session_start();
include_once('includes/crud.php');
$db = new Database();
$db->connect();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $sql = "DELETE FROM `shipping_vehicles` WHERE `id` = $id";
    $db->sql($sql);
}
header("location: shipping-vehicles.php");
exit();
?>
