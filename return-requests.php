<?php $page="Return Requests";
include"header.php";
if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller'){
    echo "<script>location.href = 'home.php';</script>";
    exit();
}
?>
<?php include('public/return-requests-table.php'); ?>
<?php include"footer.php";?>