<?php $page="Main Category";
include"header.php";
if(isset($_GET['id'])){
    $ID = $_GET['id'];
    $sql_query = "DELETE FROM `main_category` WHERE  id =".$ID;
    $db->sql($sql_query);
    header("Location: main_categories.php");
		}
?>      
        <?php include('public/main-category-table.php'); ?>  
<?php include"footer.php";?>