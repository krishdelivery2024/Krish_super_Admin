<?php
    include_once('includes/functions.php');
?>

    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Parcel Pickup Requests</h3>
                    <h4 class="box-subtitle">Requests placed by users from the pickup tab in the user app.</h4>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="parcel_requests_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=parcel_requests"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_1">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="parcel_image">Parcel Image</th>
                            <th data-field="item_type_name">Item Type</th>
                            <th data-field="weight_kg" data-sortable="true">Weight (kg)</th>
                            <th data-field="distance_km" data-sortable="true">Distance (km)</th>
                            <th data-field="total_price" data-sortable="true">Total Fare</th>
                            <th data-field="user_name">User</th>
                            <th data-field="pickup_location" data-sortable="true">Pickup Location</th>
                            <th data-field="drop_location" data-sortable="true">Drop Location</th>
                            <th data-field="sender_name">Sender</th>
                            <th data-field="recipient_name">Recipient</th>
                            <th data-field="pickup_time">Pickup Time</th>
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
        <div class="separator"> </div>
    </div>