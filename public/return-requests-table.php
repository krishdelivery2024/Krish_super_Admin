<?php 
    include_once('includes/functions.php'); 
    ?>
    
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                 <?php if($permissions['return_requests']['read']==1){?>
                <div class="box-header">
                    <h3 class="box-title">Return Requests</h3>
                </div>
               
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="return-requests"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=return-requests"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="user_id" data-sortable="true">U.ID</th>
                            <th data-field="order_id" data-sortable="true" data-visible="false">O.ID</th>
                            <th data-field="order_item_id" data-sortable="true" data-visible="false">Item ID</th>
                            <th data-field="product_id" data-sortable="true" data-visible="false">Product ID</th>
                            <th data-field="product_variant_id" data-sortable="true" data-visible="false">Product Variant ID</th>
                            <th data-field="name" data-sortable="true">U.Name</th>
                            <th data-field="product_name" data-sortable="true">Product Name</th>
                            <th data-field="price" data-sortable="true">Price</th>
                            <th data-field="discounted_price" data-sortable="true">Discounted Price</th>
                            <th data-field="quantity" data-sortable="true">Quantity</th>
                            <th data-field="total" data-sortable="true">Total</th>
                            <th data-field="status">Status</th>
                            <th data-field="date_created" data-sortable="true">Date</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
                 
            </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view return requests.</div>
            <?php } ?>
        </div>
        <div class="separator"> </div>
    </div>
