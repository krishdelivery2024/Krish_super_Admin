<?php
    include_once('includes/functions.php');
?>

<div class="content-header">
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-parcel-item-type.php"><i class="fa fa-plus-square"></i> Add New Item Type</a>
    </ol>
</div>
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Item Types</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="parcel_item_types_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=parcel_item_types"
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
                            <th data-field="image">Icon</th>
                            <th data-field="status">Status</th>
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>