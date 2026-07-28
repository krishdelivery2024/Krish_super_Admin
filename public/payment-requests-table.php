<?php 
    include_once('includes/functions.php'); 
    ?>
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <?php if($permissions['payment']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Payment Requests</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="payment-requests"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=payment-requests"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="user_id" data-sortable="true">User ID</th>
                            <th data-field="payment_type" data-sortable="true">Payment Type</th>
                            <th data-field="payment_address" data-sortable="true">Payment Address</th>
                            <!-- <th data-field="account_holder" data-sortable="true">Amount Holder</th>
                            <th data-field="account_number" data-sortable="true">Account Number</th>
                            <th data-field="ifsc_code" data-sortable="true">IFSC Code</th> -->
                            <!-- <th data-field="mobile" data-sortable="true">Mobile</th> -->
                            <th data-field="amount_requested" data-sortable="true">Amount Requested</th>
                            <th data-field="remarks" data-sortable="true">Remarks</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="email" data-sortable="true">Email</th>
                            <th data-field="status">Status</th>
                            <th data-field="date_created" data-sortable="true">Date</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view payment requests</div>
        <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>

