<?php $page="Incomplete Orders & Payment Recovery";
include"header.php";?>
<?php
if(!empty($permissions['orders']['read']) || (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin' || $_SESSION['role'] == 'seller'))) { 
?>
<style>
.uppercase {
  text-transform: uppercase;
}
.row.totalords {
    margin-left: -1px;
    margin-top: 13px;
    font-size: 15px;
}
.recovery-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px 20px;
    margin-bottom: 20px;
}
.nav-tabs {
    margin-bottom: 15px;
}
.nav-tabs > li.active > a {
    font-weight: bold;
    border-top: 3px solid #3c8dbc;
}
</style>

<div class="row small-spacing">
    <div class="col-xs-12">
        <div class="box-content">
            <h3 class="box-title">Incomplete Orders & Razorpay Payment Recovery</h3>
            
            <!-- Quick Razorpay Payment Sync / Lookup Box -->
            <div class="recovery-card">
                <div class="row">
                    <div class="col-md-8">
                        <label class="control-label" style="font-size: 14px;"><i class="fa fa-refresh text-primary"></i> Sync Razorpay Payment (e.g. <code>pay_TwQMmnBuHG67w4</code>):</label>
                        <div class="input-group">
                            <input type="text" id="razorpay_payment_input" class="form-control" placeholder="Paste Payment ID here (pay_...)" autocomplete="off">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary" id="btn_sync_payment"><i class="fa fa-search"></i> Fetch & Sync Payment</button>
                            </span>
                        </div>
                        <small class="text-muted">If a customer paid on Razorpay but the order did not appear due to an app disconnection, paste the payment ID here to recover it instantly.</small>
                    </div>
                    <div class="col-md-4 text-right">
                        <div style="margin-top: 25px;">
                            <span class="label label-info" style="font-size: 12px; padding: 6px 12px;"><i class="fa fa-info-circle"></i> Webhook: <code>/api-firebase/razorpay-webhook.php</code></span>
                        </div>
                    </div>
                </div>
                <div id="sync_result_box" style="display:none; margin-top: 15px; padding: 10px; border-radius: 5px;"></div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#tab_unlinked_payments" aria-controls="tab_unlinked_payments" role="tab" data-toggle="tab"><i class="fa fa-credit-card"></i> Captured Payments (Missing Orders)</a></li>
                <li role="presentation"><a href="#tab_incomplete_orders" aria-controls="tab_incomplete_orders" role="tab" data-toggle="tab"><i class="fa fa-clock-o"></i> Pending Incomplete Orders (Payment Status 0)</a></li>
            </ul>

            <div class="tab-content">
                <!-- Tab 1: Unlinked Razorpay Payments -->
                <div role="tabpanel" class="tab-pane active" id="tab_unlinked_payments">
                    <table class="table no-margin" data-toggle="table" id="unlinked_payments_table"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=unlinked_razorpay_payments"
                        data-page-list="[5, 10, 20, 50, 100]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="txn_id" data-sortable="true">Payment ID (Razorpay)</th>
                                <th data-field="user_name" data-sortable="true">Customer Name</th>
                                <th data-field="user_mobile" data-sortable="true">Customer Phone</th>
                                <th data-field="amount" data-sortable="true">Amount (<?=$settings['currency']?>)</th>
                                <th data-field="status" data-sortable="true">Status</th>
                                <th data-field="cart_item_count" data-sortable="false">Customer Cart</th>
                                <th data-field="transaction_date" data-sortable="true">Payment Date</th>
                                <th data-field="operate">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>

                <!-- Tab 2: Standard Incomplete Orders (payment_status = 0) -->
                <div role="tabpanel" class="tab-pane" id="tab_incomplete_orders">
                    <form method="POST" id="filter_form" name="filter_form">
                    <div class="row">
                        <div class="col-md-7 form-group form-inline">
                            <label for="date" class="control-label">From & To Date</label>
                            <input type="text" style="width:80%" class="form-control" id="date" name="date" autocomplete="off" />
                        </div>
                        <input type="hidden" id="start_date" name="start_date">
                        <input type="hidden" id="end_date" name="end_date">
                    </div>
                    <div class="row totalords">
                       <span><b>Total Number of Orders :</b></span> <span class="totalorder"></span>
                    </div>    
                    </form>
                        
                    <table class="table no-margin" data-toggle="table" id="order_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=incomplte_orders"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_1"
                        data-show-footer="true"
                        data-footer-style="footerStyle">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable='true'>O.ID</th>
                                <th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
                                <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                <th data-field="name" data-sortable='true'>U.Name</th>
                                <th data-field="mobile" data-sortable='true' data-visible="true">Mob.</th>
                                <th data-field="items" data-sortable='true' data-visible="false">Items</th>
                                <th data-field="total" data-sortable='true' data-visible="false">Total(<?=$settings['currency']?>)</th>
                                <th data-field="delivery_charge" data-sortable='true'>D.Chrg</th>
                                <th data-field="tax" data-sortable='false'>Tax <?=$settings['currency']?>(%)</th>
                                <th data-field="discount" data-sortable='true' data-visible="false">Disc.<?=$settings['currency']?>(%)</th>
                                <th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
                                <th data-field="promo_discount" data-sortable='true' data-visible="false">Promo Disc.(<?=$settings['currency']?>)</th>
                                <th data-field="wallet_balance" data-sortable='true' data-visible="false">Wallet Used(<?=$settings['currency']?>)</th>
                                <th data-field="final_total" data-sortable='true'>F.Total(<?=$settings['currency']?>)</th>
                                <th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
                                <th data-field="payment_method" data-sortable='true' data-visible="true">P.Method</th>
                                <th data-field="address" data-sortable='true' data-visible="false">Address</th>
                                <th data-field="delivery_time" data-sortable='true' data-visible='false'>D.Time</th>
                                <th data-field="status" data-sortable='true' data-visible='false'>Status</th>
                                <th data-field="active_status" data-sortable='true' data-visible='false'>A.Status</th>
                                <th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
                                <th data-field="operate">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
                    
        </div>
    </div>
</div>
<?php } else { ?>
<div class="alert alert-danger topmargin-sm leftmargin-sm">You have no permission to view orders.</div>
<?php } ?>

<?php 
$db->disconnect();
?>
<script>
$(document).ready(function(){
    setInterval(function () {
        var ab=$(".pagination-info").html();
        if(ab){
            var b=ab.split(" ");
            if(b && b.length > 11) $(".totalorder").html(b[11]);
        }
    },1000);

    // Sync payment click
    $('#btn_sync_payment').on('click', function(){
        var paymentId = $('#razorpay_payment_input').val().trim();
        if(!paymentId){
            alert('Please enter a Razorpay Payment ID (e.g. pay_...)');
            return;
        }
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Fetching...');
        $('#sync_result_box').hide();

        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: { sync_razorpay_payment: 1, payment_id: paymentId },
            dataType: 'json',
            success: function(resp){
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Fetch & Sync Payment');
                if(resp.error){
                    $('#sync_result_box').removeClass('alert-success').addClass('alert alert-danger').html('<strong>Error:</strong> ' + resp.message).show();
                } else {
                    var html = '<strong>Success!</strong> Payment <code>' + resp.payment_id + '</code> (' + resp.amount + ') synced. ';
                    if(resp.contact) html += '<br>Customer Mobile: ' + resp.contact;
                    if(resp.email) html += '<br>Customer Email: ' + resp.email;
                    if(resp.result && resp.result.order_id){
                        html += '<br><strong>Order created/linked: Order #' + resp.result.order_id + '</strong>';
                    }
                    $('#sync_result_box').removeClass('alert-danger').addClass('alert alert-success').html(html).show();
                    $('#unlinked_payments_table').bootstrapTable('refresh');
                }
            },
            error: function(xhr, status, err){
                $btn.prop('disabled', false).html('<i class="fa fa-search"></i> Fetch & Sync Payment');
                $('#sync_result_box').removeClass('alert-success').addClass('alert alert-danger').html('<strong>Network error:</strong> ' + err).show();
            }
        });
    });

    // Create order from cart button
    $(document).on('click', '.btn-create-order-cart', function(){
        var txnId = $(this).data('txn-id');
        var userId = $(this).data('user-id');
        if(!confirm('Create an order from this customer\'s cart for payment ' + txnId + '?')){
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Creating...');

        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: { create_order_from_cart: 1, txn_id: txnId, user_id: userId },
            dataType: 'json',
            success: function(resp){
                if(resp.error){
                    alert('Error: ' + resp.message);
                    $btn.prop('disabled', false).html('<i class="fa fa-shopping-cart"></i> Create Order');
                } else {
                    alert('Order #' + resp.order_id + ' created successfully!');
                    $('#unlinked_payments_table').bootstrapTable('refresh');
                }
            },
            error: function(){
                alert('Server error occurred.');
                $btn.prop('disabled', false).html('<i class="fa fa-shopping-cart"></i> Create Order');
            }
        });
    });

    // Manually link order ID button
    $(document).on('click', '.btn-link-order-manual', function(){
        var txnId = $(this).data('txn-id');
        var orderId = prompt('Enter the existing Order ID to link payment ' + txnId + ' to:');
        if(!orderId) return;

        $.ajax({
            type: 'POST',
            url: 'public/db-operation.php',
            data: { link_payment_to_order: 1, txn_id: txnId, order_id: orderId },
            dataType: 'json',
            success: function(resp){
                if(resp.error){
                    alert('Error: ' + resp.message);
                } else {
                    alert(resp.message);
                    $('#unlinked_payments_table').bootstrapTable('refresh');
                }
            }
        });
    });
});

$(document).on('click','.update_payment_status',function(){
    var id = $(this).data('id');
    if(confirm('Are you sure want to update payment status as Success for order?')){
        $.ajax({
            type:'POST',
            url: 'public/db-operation.php',
            data:'id='+id+'&update_payment_status=1',
            success:function(result){
                if(result==0){
                    $('#order_list').bootstrapTable('refresh');
                }
                if(result==1){
                    alert('Error! Payment could not be Updated.');
                }
                if(result==2){
                    alert('You have no permission to Update Payment');
                }
            }
        });
    }
});
</script>
	
<?php include"footer.php";?>
