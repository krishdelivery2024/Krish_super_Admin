<?php
include_once('includes/functions.php');
?>
<section class="content-header">
    <h1>Shipping Cargo Types / Goods Categories</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        <li>Pickup & Shipping</li>
        <li class="active">Cargo Types</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Manage Cargo & Goods Types</h3>
                    <div class="box-tools pull-right">
                        <a href="add-shipping-goods-type.php" class="btn btn-sm btn-success"><i class="fa fa-plus-circle"></i> Add Cargo Type</a>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="shipping_goods_types_table"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=shipping_goods_types"
                        data-page-list="[10, 25, 50, 100]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="asc">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="name" data-sortable="true">Cargo / Goods Type Name</th>
                                <th data-field="status" data-sortable="true">Status</th>
                                <th data-field="created_at" data-sortable="true">Created At</th>
                                <th data-field="operate">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
