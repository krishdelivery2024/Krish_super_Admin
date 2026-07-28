
<div class="content-header">
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-product.php"><i class="fa fa-plus-square"></i> Add New Product</a>
    </ol>
</div>

<?php
    if($permissions['products']['read']==1) { 
?>
<!-- Main content -->
<div id="wrapper">
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">
            <div class="box">
                <!-- <div class="col-xs-6"> -->
                <div class="box-header">
                    <div class="col-md-6">
                       <span class="box-title">Filter by Products Category</span>
                        <form method="post">
                            <select id="category_id" name="category_id" placeholder="Select Category" required class="form-control col-xs-3" style="width: 300px;">
                                <?php
                                    $Query="select name, id from category where main_cat='$main_cat_id'";
                                    $db->sql($Query);
                                    $result=$db->getResult();
                                    if($result)
                                    {
                                    ?>
                                <option value="">All Products</option>
                                <?php foreach($result as $row){
                                    if($permissions['categories']['read']==1){
                                ?>
                                <option value='<?=$row['id']?>'><?=$row['name']?></option>
                                <?php }}}   
                                ?>
                            </select>
                        </form> 
                    </div>
<button class="btn btn-danger" id="showSelectedRows">Delete Selected</button>
                </div>

                    
                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    
                    <table id='products_table' class="table table-hover" data-toggle="table" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=products"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-filter-control="true" data-query-params="queryParams"
                        data-sort-name="id" data-sort-order="desc"
                        data-show-export="true"
                        data-multiple-select-row="true"
                        data-export-types='["txt","excel"]'
                        data-export-options='{
                            "fileName": "products-list-<?=date('d-m-Y')?>",
                            "ignoreColumn": ["operate"] 
                        }'>
                        <thead>
                        <tr>
                             <th data-field="state" data-checkbox="true"></th>
                            <th data-field="id" data-sortable="true">ID</th>
                         <!--   <th data-field="barcode_data" >Barcode</th>-->
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="image">Image</th>
                            <th data-field="price" data-sortable="true">Price</th> 
                            <th data-field="measurement" data-sortable="true">Measurement (Kg, gm, Ltr)</th> 
                            <th data-field="stock" data-sortable="true" >Stock</th>
                            <th data-field="serve_for" data-sortable="true">Availability</th>
                            <th data-field="is_active" data-sortable="true">Status</th>
                            <!-- <th data-field="discounted_price" data-sortable="true" >Discounted Price</th> -->
                            <!--<th data-field="category_id" data-sortable="true">Category ID</th>-->
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <div class="separator"> </div>
    </div>
    <!-- /.row (main row) -->
</div>

<?php } else { ?>
<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view products.</div>
<?php } ?>
<script>
    var $table = $('#products_table');

function getRowSelections() {
  return $.map($table.bootstrapTable('getSelections'), function(row) {
    return row;
  })
}

$('#showSelectedRows').click(function() {
  var selectedRows = getRowSelections();
  var selectedItems = '\n';
  var r = confirm("Are you sure you want to delete this item");
if (r == true) {
  $.each(selectedRows, function(index, value) {
    selectedItems += value.id + '\n';
     var dataString ='delete_product=true&id='+value.id;
		//console.log(dataString);
    $.ajax({        
        url: "public/db-operation.php",
        type: "POST",
        data: dataString,
     //   beforeSend:function(){$('#submit_btn').html('Please wait..');$('#submit_btn').attr('disabled',true);},
        dataType: "json",
		async:false,
        success: function (data) {
			console.log(data);
        }
    });
  });
} 
 location.reload();

 // alert('The following products are selected: ' + selectedItems);
});
</script>