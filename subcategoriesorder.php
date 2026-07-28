<?php $page="Sub Categories Order";
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
    
    $sql = "SELECT * FROM `category` ORDER BY `cat_priority` ASC";
    $db->sql($sql);
    $categories = $db->getResult();
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
    .suborder{
        display:none;
    }
</style>
                <div class='row'>
                    <div class='col-md-6'>
                        <label class="control-label">Category</label>
                        <select name="category_id" id="category_id" class="form-control">
                            <option value=''>All</option>
                            <?php if($permissions['categories']['read']==1){?>
                            <?php foreach($categories as $category){ ?>
                            <option value='<?=$category['id']?>'><?=$category['name']?></option>
                            <?php }}?>
                        </select>
                    </div>
                </div>
                <div class='row suborder'>
                    <div style="text-align:center;text-align: center;font-size: 25px;margin-bottom: 10px;color: blue;">Category Position Reordering</div>
                    <div style="border:1px solid #000; width:450px; padding:5px 4px 5px 4px;margin: auto;" class="subcategoryappend">
                        <div id="contentLeft1"><ul></ul></div>
                    </div>
                </div>
<?php include"footer.php";?>
<script type="text/javascript">
$(document).ready(function(){                          
   

}); 
</script>
<script>
    $('#category_id').on('change',function(e,category_id,subcategory_id){
        var category_id = $('#category_id').val();
        $.ajax({
          url:"public/db-operation.php",
          data:"category_id="+category_id+"&categoryorder=1",
           method:"POST",
           success:function(data){
               $('.suborder').show();
              $('#contentLeft1 ul').html(data);
            //   $('#subcategory_id').val(subcategory_id);
             
           }
        });
    });
     $(function() {
        $("#contentLeft1 ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
            var order = $(this).sortable("serialize") + '&change=updatesubcatorder'; 
            $.post("category_order_status.php", order, function(theResponse){
                $("#contentRight").html(theResponse);
            });                                                              
        }                                 
        });
    });
</script>
