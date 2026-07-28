<?php $page="Seller Order";
include"header.php";?>
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
    
    if(isset($_POST['update_seller_order']) && $_POST['update_seller_order'] == 1){
        if($permissions['seller_order']['update']==1){
        $id_ary = explode(",",$_POST["row_order"]);
        for($i=0;$i<count($id_ary);$i++){
            $sql = "UPDATE `seller` SET sel_priority='" . $i . "' WHERE id=". $id_ary[$i];
            // echo $sql;
            $db->sql($sql);
            $res = $db->getResult();
        }
        echo "<p class='alert alert-success'>Seller order updated!</p>";
        return false;
        }else{
        echo "<p class='alert alert-danger'>You have no permission to update seller order</p>";
        return false;
        }
    }
?>
<style>
    #sortable-row li { margin-bottom:4px; padding:10px; background-color:#fff;cursor:move;} 
    #sortable-row li.ui-state-highlight { height: 1.0em; background-color:#F0F0F0;border:#ccc 2px dotted;}
    #sortable-row-2 li { margin-bottom:4px; padding:10px; background-color:#fff;cursor:move;} 
    #sortable-row-2 li.ui-state-highlight { height: 1.0em; background-color:#F0F0F0;border:#ccc 2px dotted;}
</style>
<style>
    .ordering{
    padding: 5px;
    border: 1px solid gray;
    margin: 5px;
    /*list-style: none;*/
    cursor: pointer;
    
    }
</style>
<?php
$sql = "SELECT * FROM `seller` ORDER BY `sel_priority` ASC";
$db->sql($sql);
$sellers = $db->getResult();
// print_r($sellers[0]['id']);
?>
                
                <div class='row'>
                    <div style="text-align:center;text-align: center;font-size: 25px;margin-bottom: 10px;color: blue;">Seller Position Reordering</div>
            <div style="border:1px solid #000; width:450px; padding:5px 4px 5px 4px;margin: auto;">
                
                <div id="contentLeftSeller">
                <ul>            
                <?php                  
                foreach($sellers as $sub_top)
                {                       
                ?>
                <li id="recordsArraySeller_<?php echo $sub_top['id']; ?>"  class="ordering"><?php echo $sub_top['name']; ?></li>
                <?php } ?>
                </ul>
                </div>
            </div>
                </div>
                
                
                       
  
<?php include"footer.php";?>
<!-- jQuery -->
<script type="text/javascript">
$(document).ready(function(){                          
    $(function() {
        $("#contentLeftSeller ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
            var order = $(this).sortable("serialize") + '&change=updatesellerorder'; 
            $.post("seller_order_status.php", order, function(theResponse){
                $("#contentRight").html(theResponse);
            });                                                              
        }                                 
        });
    });
}); 
</script>