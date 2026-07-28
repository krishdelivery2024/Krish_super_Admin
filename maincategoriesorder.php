<?php 
$page = "Main Categories Order";
include "header.php"; ?>
<?php
header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('includes/crud.php');
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);

$db = new Database();
$db->connect();

if (isset($_POST['update_products_order']) && $_POST['update_products_order'] == 1) {
    if ($permissions['products_order']['update'] == 1) {
        $id_ary = explode(",", $_POST["row_order"]);
        for ($i = 0; $i < count($id_ary); $i++) {
            $sql = "UPDATE `products` SET row_order='" . $i . "' WHERE id=" . $id_ary[$i];
            // echo $sql;
            $db->sql($sql);
            $res = $db->getResult();
        }
        echo "<p class='alert alert-success'>Product order updated!</p>";
        return false;
    } else {
        echo "<p class='alert alert-danger'>You have no permission to update products order</p>";
        return false;
    }
}
?>
<style>
    #sortable-row li {
        margin-bottom: 4px;
        padding: 10px;
        background-color: #fff;
        cursor: move;
    }

    #sortable-row li.ui-state-highlight {
        height: 1.0em;
        background-color: #F0F0F0;
        border: #ccc 2px dotted;
    }

    #sortable-row-2 li {
        margin-bottom: 4px;
        padding: 10px;
        background-color: #fff;
        cursor: move;
    }

    #sortable-row-2 li.ui-state-highlight {
        height: 1.0em;
        background-color: #F0F0F0;
        border: #ccc 2px dotted;
    }
</style>
<style>
    .ordering {
        padding: 5px;
        border: 1px solid gray;
        margin: 5px;
        /*list-style: none;*/
        cursor: pointer;

    }
</style>
<?php
$sql = "SELECT * FROM `main_category` ORDER BY `cat_priority` ASC";
$db->sql($sql);
$categories = $db->getResult();
// print_r($categories[0]['id']);
?>
<?php if ($permissions['products_order']['read'] == 1) { ?>
    <div class='row'>
        <div style="text-align:center;text-align: center;font-size: 25px;margin-bottom: 10px;color: blue;">Main Category Position Reordering</div>
        <div style="border:1px solid #000; width:450px; padding:5px 4px 5px 4px;margin: auto;">

            <div id="contentLeft1">
                <ul>
                    <?php
                    foreach ($categories as $sub_top) {
                    ?>
                        <li id="recordsArray1_<?php echo $sub_top['id']; ?>" class="ordering"><?php echo $sub_top['name']; ?></li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>


<?php } else { ?>
    <div class="alert alert-danger">You have no permission to view products order.</div>
<?php } ?>

<?php include "footer.php"; ?>
<!-- jQuery -->
<script type="text/javascript">
    $(document).ready(function() {

        $(function() {
            $("#contentLeft1 ul").sortable({
                
                
                
                opacity: 0.6,
                cursor: 'move',
                update: function() {
                    var order = $(this).sortable("serialize") + '&change=updatemaincattop';
                    $.post("category_order_status.php", order, function(theResponse) {

                    console.log(theResponse);
                        $("#contentRight").html(theResponse);
                    });
                }
            });
        });

    });
</script>