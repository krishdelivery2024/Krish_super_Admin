<?php
$page="Products";
include"header.php";

$is_admin_mode = (isset($_SESSION['secretlogin']) && $_SESSION['secretlogin'] == 'yes') || (isset($_SESSION['role']) && in_array($_SESSION['role'], array('admin', 'super admin')));
$is_restricted_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller' && !$is_admin_mode);

if ($is_restricted_seller) {
    include('public/seller-products-table.php');
} else {
    include('public/products-table.php'); 
}

include"footer.php";
?>
