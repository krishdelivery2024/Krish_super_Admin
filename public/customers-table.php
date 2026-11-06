<style>
    span.exportcustomer {
        background: #3F51B5;
        color: white;
        padding: 8px 14px;
        cursor: pointer;
        border-radius: 3px;
        font-weight: 500;
        display: inline-block;
    }
    span.exportcustomer:hover {
        background: #303F9F;
    }
    #customerOrdersModal {
        z-index: 1060 !important;
        pointer-events: auto !important;
    }
    #customerOrdersModal .modal-dialog {
        width: 90%;
        max-width: 1250px;
        z-index: 1061 !important;
        position: relative;
        pointer-events: auto !important;
    }
    #customerOrdersModal .modal-content {
        background-color: #fff !important;
        position: relative;
        z-index: 1062 !important;
        pointer-events: auto !important;
    }
    .modal-backdrop {
        z-index: 1040 !important;
    }
    #customerOrdersModal th {
        vertical-align: middle;
    }
    #customerOrdersModal td {
        vertical-align: top;
    }
</style>
<script>
    $("body").on('click', '.exportcustomer', function(){
        var url = 'public/exportcustomer.php';
        document.location.href = url;
    });
</script>
<div style="margin-bottom: 20px;">
    <span class="exportcustomer"><i class="fa fa-download"></i> Export Customer Data</span>
</div>
<div class="row">
    <div class="col-xs-12">
        <?php if($permissions['customers']['read']==1){?>
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Customers</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-hover" data-toggle="table" 
                    data-url="api-firebase/get-bootstrap-table-data.php?table=users"
                    data-page-list="[5, 10, 20, 50, 100, 200]"
                    data-show-refresh="true" data-show-columns="true"
                    data-side-pagination="server" data-pagination="true"
                    data-search="true" data-trim-on-search="false"
                    data-filter-control="true" data-filter-show-clear="true"
                    data-sort-name="id" data-sort-order="desc">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="email" data-sortable="true">Email</th>
                            <th data-field="mobile" data-sortable="true">M.No</th>
                            <th data-field="balance" data-sortable="true">Balance</th>
                            <th data-field="orders_count" data-sortable="true">Orders</th>
                            <th data-field="referral_code" data-sortable="true">Referral code</th>
                            <th data-field="friends_code" data-sortable="true">Friends code</th>
                            <th data-field="street" data-sortable="true">Street</th>
                            <th data-field="area" data-sortable="true">Area</th>
                            <th data-field="city" data-sortable="true" data-filter-control="select">City</th>
                            <th data-field="status" data-sortable="true">Status</th>
                            <th data-field="created_at" data-sortable="true">Date & Time</th>
                            <th data-field="operate" data-sortable="false">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <?php } else { ?>
        <div class="alert alert-danger">You have no permission to view customers</div>
        <?php } ?>
        <!-- /.box -->
    </div>
</div>
<!-- /.row (main row) -->

<!-- Modal for Quick View of Customer Orders -->
<div class="modal fade" id="customerOrdersModal" tabindex="-1" role="dialog" aria-labelledby="customerOrdersModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background: #3F51B5; color: white;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.9;"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="customerOrdersModalLabel">
                    <i class="fa fa-shopping-cart"></i> Orders for: <span id="modal_customer_name" style="font-weight: bold;"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 15px 20px;">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-sm-7 col-xs-12">
                        <div class="form-inline">
                            <label for="modal_filter_order_status" style="margin-right: 6px; font-weight: 600;">Status: </label>
                            <select id="modal_filter_order_status" class="form-control" style="display: inline-block; width: auto; min-width: 140px;">
                                <option value="">All Orders</option>
                                <option value="received">Received</option>
                                <option value="processed">Processed</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="returned">Returned</option>
                            </select>
                            <button type="button" class="btn btn-default btn-sm" id="btn_modal_refresh_orders" title="Refresh Orders" style="margin-left: 5px;">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="col-sm-5 col-xs-12 text-right">
                        <a id="modal_customer_profile_link" href="#" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fa fa-user"></i> Full Customer Profile
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped no-margin" id="modal_customer_orders_table"
                        data-toggle="table"
                        data-page-list="[5, 10, 20, 50]"
                        data-page-size="10"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-search="true"
                        data-trim-on-search="false"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-query-params="modalCustomerOrdersQueryParams">
                        <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">O.ID</th>
                                <th data-field="date_added" data-sortable="true">Order Date</th>
                                <th data-field="items" data-sortable="false">Items Details</th>
                                <th data-field="total" data-sortable="true">Total (<?=$currency?>)</th>
                                <th data-field="delivery_charge" data-sortable="true">D.Charge</th>
                                <th data-field="final_total" data-sortable="true">Final Total (<?=$currency?>)</th>
                                <th data-field="payment_method" data-sortable="true">P.Method</th>
                                <th data-field="deliver_by" data-sortable="true">Delivery Boy</th>
                                <th data-field="active_status" data-sortable="true">Status</th>
                                <th data-field="operate">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <a id="modal_customer_profile_link_footer" href="#" class="btn btn-primary btn-sm" target="_blank">
                    <i class="fa fa-external-link"></i> Open Customer Details & Orders Page
                </a>
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Immediately move modal directly under <body> to escape #wrapper's CSS transform & stacking context
    if (document.getElementById('customerOrdersModal')) {
        document.body.appendChild(document.getElementById('customerOrdersModal'));
    }
</script>

<script>
    var currentModalCustomerId = 0;

    function modalCustomerOrdersQueryParams(p) {
        return {
            "user_id": currentModalCustomerId,
            "filter_order": $('#modal_filter_order_status').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }

    $(document).ready(function() {
        // Ensure both modals are children of <body> so they never render under backdrops
        $('#customerOrdersModal').appendTo('body');
        $('#editOrderStatusModal').appendTo('body');

        // Dynamically increment z-index when multiple modals or backdrops are triggered
        $(document).on('show.bs.modal', '.modal', function () {
            var zIndex = 1060 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function() {
                $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });

        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal:visible').length > 0) {
                $('body').addClass('modal-open');
            }
        });

        // Open Customer Orders Modal on click of "View" or Orders Count badge
        $(document).on('click', '.btn-view-orders', function(e) {
            e.preventDefault();
            // Ensure modal is outside #wrapper before opening
            if ($('#customerOrdersModal').parent().prop('tagName') !== 'BODY') {
                $('#customerOrdersModal').appendTo('body');
            }

            currentModalCustomerId = $(this).data('id');
            var customerName = $(this).data('name');
            var profileUrl = 'edit-customer.php?id=' + currentModalCustomerId + '#customer-orders';

            $('#modal_customer_name').text(customerName + ' (ID #' + currentModalCustomerId + ')');
            $('#modal_customer_profile_link').attr('href', profileUrl);
            $('#modal_customer_profile_link_footer').attr('href', profileUrl);
            $('#modal_filter_order_status').val('');

            var orderUrl = 'api-firebase/get-bootstrap-table-data.php?table=orders&user_id=' + currentModalCustomerId;
            $('#modal_customer_orders_table').bootstrapTable('refreshOptions', {
                url: orderUrl
            });

            $('#customerOrdersModal').modal('show');
        });

        // Recalculate and align table column widths once modal is visible
        $('#customerOrdersModal').on('shown.bs.modal', function () {
            $('#modal_customer_orders_table').bootstrapTable('resetView');
        });

        // Filter status in modal
        $('#modal_filter_order_status').on('change', function() {
            $('#modal_customer_orders_table').bootstrapTable('refresh');
        });

        // Refresh modal orders
        $('#btn_modal_refresh_orders').on('click', function() {
            $('#modal_customer_orders_table').bootstrapTable('refresh');
        });

        // Quick Order Status Change Modal from inside the customer orders modal
        $(document).on('click', '.btn-change-status', function(e) {
            e.preventDefault();
            var orderId = $(this).data('id');
            var currentStatus = $(this).data('status');
            var currentBoy = $(this).data('boy');

            $('#modal_order_id').val(orderId);
            $('#modal_order_id_display').text('#' + orderId);
            $('#modal_status').val(currentStatus);
            if (currentStatus === 'cancelled') {
                $('#modal_cancelled_by_group').show();
            } else {
                $('#modal_cancelled_by_group').hide();
            }
            $('#modal_delivery_boy_id').val(currentBoy ? currentBoy : '');
            $('#modal_result_message').hide().removeClass('alert-success alert-danger').html('');
            $('#btn_modal_save_status').prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
            
            $('#editOrderStatusModal').appendTo('body');
            $('#editOrderStatusModal').modal('show');
        });

        // Submit Status Update
        $('#quick_update_order_status_form').on('submit', function(e) {
            e.preventDefault();
            var $btn = $('#btn_modal_save_status');
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

            var formData = $(this).serialize();
            $.ajax({
                url: 'api-firebase/order-process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(resp) {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
                    if (resp && resp.error) {
                        $('#modal_result_message').removeClass('alert-success').addClass('alert-danger').html(resp.message).show();
                    } else {
                        var msg = (resp && resp.message) ? resp.message : 'Order status updated successfully!';
                        $('#modal_result_message').removeClass('alert-danger').addClass('alert-success').html(msg).show();
                        setTimeout(function() {
                            $('#editOrderStatusModal').modal('hide');
                            $('#modal_customer_orders_table').bootstrapTable('refresh');
                        }, 1000);
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
                    $('#modal_result_message').removeClass('alert-success').addClass('alert-danger').html('Server error occurred while updating order status!').show();
                }
            });
        });
    });
</script>
