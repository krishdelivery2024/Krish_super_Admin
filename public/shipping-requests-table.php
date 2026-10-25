<?php
include_once('includes/functions.php');
?>

<div class="content-header">
    <div class="row">
        <div class="col-xs-8">
            <h3><i class="fa fa-truck"></i> Shipping &amp; Heavy Vehicle Requests</h3>
            <p class="text-muted">Requests placed by users from the Shipping tab for Auto, 4-wheelers, mini trucks, tempos, and heavy vehicles.</p>
        </div>
        <div class="col-xs-4 text-right" style="padding-top:15px;">
            <a class="btn btn-default" href="shipping-vehicles.php"><i class="fa fa-cog"></i> Manage Vehicles</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Shipping Bookings</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-hover table-striped" data-toggle="table" id="shipping_requests_list"
                    data-url="api-firebase/get-bootstrap-table-data.php?table=shipping_requests"
                    data-page-list="[5, 10, 20, 50, 100, 200]"
                    data-show-refresh="true" data-show-columns="true"
                    data-side-pagination="server" data-pagination="true"
                    data-search="true" data-trim-on-search="false"
                    data-sort-name="id" data-sort-order="desc"
                    data-query-params="queryParams_1">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">Order ID</th>
                            <th data-field="vehicle_name" data-sortable="true">Vehicle Type</th>
                            <th data-field="goods_type">Goods &amp; Load</th>
                            <th data-field="parcel_image">Photos</th>
                            <th data-field="helpers">Helpers</th>
                            <th data-field="distance_km" data-sortable="true">Distance</th>
                            <th data-field="total_price" data-sortable="true">Total Fare</th>
                            <th data-field="user_name">Customer</th>
                            <th data-field="pickup_location">Pickup Details</th>
                            <th data-field="drop_location">Drop Details</th>
                            <th data-field="driver_name">Assigned Driver</th>
                            <th data-field="payment_status">Payment</th>
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
