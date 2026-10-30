<?php
include_once('includes/functions.php');
?>

<div class="content-header">
    <div class="row">
        <div class="col-xs-6">
            <h3><i class="fa fa-truck"></i> Shipping Vehicles</h3>
            <p class="text-muted">Manage heavy vehicles, 4-wheelers, mini trucks, tempos, and autos for freight pickup &amp; drop.</p>
        </div>
        <div class="col-xs-6 text-right" style="padding-top:15px;">
            <a class="btn btn-primary" href="add-shipping-vehicle.php"><i class="fa fa-plus-circle"></i> Add New Vehicle</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Configured Shipping Vehicles</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-hover table-striped" data-toggle="table" id="shipping_vehicles_list"
                    data-url="api-firebase/get-bootstrap-table-data.php?table=shipping_vehicles"
                    data-page-list="[5, 10, 20, 50, 100]"
                    data-show-refresh="true" data-show-columns="true"
                    data-side-pagination="server" data-pagination="true"
                    data-search="true" data-trim-on-search="false"
                    data-sort-name="sort_order" data-sort-order="asc"
                    data-query-params="queryParams_1">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="image">Image</th>
                            <th data-field="name" data-sortable="true">Vehicle Name</th>
                            <th data-field="capacity">Capacity &amp; Size</th>
                            <th data-field="base_price" data-sortable="true">Base Fare</th>
                            <th data-field="per_km_price" data-sortable="true">Per KM</th>
                            <th data-field="helper_price" data-sortable="true">Helper Rate</th>
                            <th data-field="sort_order" data-sortable="true">Sort Order</th>
                            <th data-field="status">Status</th>
                            <th data-field="created_at" data-sortable="true">Date</th>
                            <th data-field="operate">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
