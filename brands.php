<?php $page="Brands";
include"header.php";?>  
<?php
    include_once('includes/functions.php');
?>
<?php
    if($permissions['categories']['read']==1) { 
?>

<div class="content-header">
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-brand.php"><i class="fa fa-plus-square"></i> Add New Brand</a>
    </ol>
</div>
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Brands</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="brand_list" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=brand"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_1">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="image">Image</th>
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>
<?php } else { ?>
<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view brands.</div>
<?php } ?>
<?php include"footer.php";?>
