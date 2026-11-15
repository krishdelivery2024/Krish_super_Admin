<?php
$page = "Manage Category";
include "header.php";

$is_admin_mode = (isset($_SESSION['secretlogin']) && $_SESSION['secretlogin'] == 'yes') || (isset($_SESSION['role']) && in_array($_SESSION['role'], array('admin', 'super admin')));
if (isset($_SESSION['role']) && $_SESSION['role'] == 'seller' && !$is_admin_mode) {
    echo "<script>location.href = 'products.php';</script>";
    exit();
}

include "public/manage-category-form.php";
include "footer.php";
?>
