<?php
    include_once('includes/functions.php');
?>

    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Parcel Payment Transactions</h3>
                    <h4 class="box-subtitle">Online payments made against parcel pickup requests.</h4>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="parcel_transactions_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=parcel_transactions"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_1">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">Txn ID</th>
                            <th data-field="user_name" data-sortable="false">User</th>
                            <th data-field="user_mobile" data-sortable="false">Mobile</th>
                            <th data-field="order_id" data-sortable="true">Parcel ID</th>
                            <th data-field="type" data-sortable="true">Method</th>
                            <th data-field="txn_id" data-sortable="false">TX N ID</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
                            <th data-field="status" data-sortable="false">Status</th>
                            <th data-field="transaction_date" data-sortable="true">Date</th>
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>