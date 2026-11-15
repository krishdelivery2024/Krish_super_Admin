<?php $page="Promo Code";
include"header.php";
if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller'){
    echo "<script>location.href = 'home.php';</script>";
    exit();
}
?>
        <?php include('public/promo-code-table.php'); ?>
<?php include"footer.php";?>