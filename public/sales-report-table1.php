<?php
    if($permissions['reports']['read']==1) { 
        $is_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');
?>
<style>
/* Order status badge */
#sales_table .status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    text-transform: capitalize;
    white-space: nowrap;
}
#sales_table .status-pending    { background-color: #f0ad4e; } /* amber */
#sales_table .status-processing { background-color: #5bc0de; } /* blue  */
#sales_table .status-completed  { background-color: #5cb85c; } /* green */
#sales_table .status-cancelled  { background-color: #d9534f; } /* red   */
#sales_table .status-default    { background-color: #777777; } /* gray  */

/* Sticky table header styles */
#wrapper {
    transform: none !important;
    -webkit-transform: none !important;
    overflow: visible !important;
}
.fixed-navbar {
    z-index: 105 !important;
}
.fix-sticky {
    z-index: 99 !important;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.12);
}
.fix-sticky table thead th {
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #ccc !important;
    color: #333 !important;
}
#sales_table thead th {
    background-color: #f8f9fa;
}
.box-body.table-responsive {
    overflow-x: visible !important;
}
</style>
    <div class="row small-spacing">
        <div class="col-xs-12">
			<div class="box-content">
                <h3 class="box-title">Sales Report</h3>
                <form method="POST" id="filter_form" name="filter_form">
				    <div class="row">
					     <div class="col-md-2 form-group">
						     <label for="from_date" class="control-label">From Date</label>
						     <input type="text" class="form-control" id="from_date" name="from_date" autocomplete="off" placeholder="Select From Date" />
					    </div>
					     <div class="col-md-2 form-group">
						     <label for="to_date" class="control-label">To Date</label>
						     <input type="text" class="form-control" id="to_date" name="to_date" autocomplete="off" placeholder="Select To Date" />
					    </div>
					    <input type="hidden" id="start_from" name="start_date">
					        <input type="hidden" id="end_to" name="end_date">

                        <!-- Payment Method filter -->
                        <div class="col-md-2 form-group">
                            <label for="payment_method_filter" class="control-label">Payment Method</label>
                            <select class="form-control" id="payment_method_filter" name="payment_method">
                                <option value="">All</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Online Payment">Online Payment</option>
                            </select>
                        </div>

                        <!-- Seller filter (hidden for seller-role users, filtered by zone) -->
                        <?php if(!$is_seller) { ?>
                        <div class="col-md-2 form-group">
                            <label for="seller_filter" class="control-label">Seller</label>
                            <select class="form-control" id="seller_filter" name="seller_id">
                                <option value="">All Sellers</option>
                                <?php
                                    $db->sql("SELECT id, name FROM seller ORDER BY name ASC");
                                    $sellers = $db->getResult();
                                    if (is_array($sellers)) {
                                        foreach ($sellers as $s_row) {
                                            echo '<option value="' . intval($s_row['id']) . '">' . htmlspecialchars($s_row['name'], ENT_QUOTES) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php } ?>

                        <!-- Zone filter (hidden for seller-role users) -->
                        <?php if(!$is_seller) { ?>
                        <div class="col-md-2 form-group">
                            <label for="zone_filter" class="control-label">Zone</label>
                            <select class="form-control" id="zone_filter" name="zone_id">
                                <option value="">All Zones</option>
                                <option value="0">Unassigned (no zone)</option>
                                <?php
                                    $db->sql("SELECT id, name FROM zone ORDER BY name ASC");
                                    $zones = $db->getResult();
                                    if (is_array($zones)) {
                                        foreach ($zones as $z_row) {
                                            echo '<option value="' . intval($z_row['id']) . '">' . htmlspecialchars($z_row['name'], ENT_QUOTES) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php } ?>

                        <!-- Order Status filter -->
                        <div class="col-md-2 form-group">
                            <label for="status_filter" class="control-label">Order Status</label>
                            <select class="form-control" id="status_filter" name="order_status">
                                <option value="">All Orders</option>
                                <option value="delivered">Delivered</option>
                                <option value="received">Received</option>
                                <option value="processed">Processed</option>
                                <option value="shipped">Shipped</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="returned">Returned</option>
                            </select>
                        </div>

				        <div class="col-md-2 form-group" style="padding-top:25px;">
				            <span class="export">Export Data</span>
				        </div>
				       <div class="col-md-2 pull-right form-group">
				           <label for="sales_keyword" class="control-label">Search</label>
				           <input type="text" class="form-control" id="sales_keyword" name="search" placeholder="Enter Keyword" autocomplete="off" />
				        </div>
				      </div>
                </form>
                <div class="box-body table-responsive">
                    <table id='sales_table' class="table table-hover" data-toggle="table" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=sales_products"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" 
                        data-show-columns="true"
                        data-side-pagination="server" 
                        data-pagination="false"
                        data-search="false" 
                        data-trim-on-search="false"
                        data-filter-control="true" 
                        data-query-params="queryParams_sales"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-show-export="true"
                        data-export-types='["excel","pdf"]'
                        data-export-data-type="all"
                        data-show-footer="true"
                        data-export-footer="true"
                        data-sticky-header="true"
                        data-sticky-header-offset-y="50">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" data-footer-formatter="salesTotalLabel">ID</th>
                            <?php if(!$is_seller) { ?>
                            <th data-field="seller_name" data-sortable="true">Seller Name</th>
                            <th data-field="seller_mobile" data-sortable="true">Seller Mobile</th>
                            <?php } ?>
                            <th data-field="user_name" data-sortable="true">User Name</th>
                            <th data-field="user_mobile" data-sortable="true">User Mobile</th>
                            <th data-field="address" data-sortable="true">Address</th>
                            <th data-field="product_details" data-formatter="productListFormatter">Product / Qty / Unit</th>
                            <?php if(!$is_seller) { ?>
                            <th data-field="zone_name" data-sortable="true">Zone</th>
                            <?php } ?>
                            <th data-field="order_date" data-sortable="true">Order Date</th>
                            <?php if(!$is_seller) { ?>
                            <th data-field="vendor_price" data-formatter="priceListFormatter" data-align="right" data-footer-formatter="salesMoneyFormatter">Vendor Price (&#8377;)</th>
                            <th data-field="discounted_price" data-formatter="priceListFormatter" data-align="right" data-footer-formatter="salesMoneyFormatter">Discounted Price (&#8377;)</th>
                            <th data-field="commission_percent" data-sortable="false" data-align="center">Commission (%)</th>
                            <th data-field="commission_amount" data-sortable="false" data-align="right" data-footer-formatter="salesMoneyFormatter">Commission (&#8377;)</th>
                            <th data-field="seller_received" data-formatter="priceListFormatter" data-align="right" data-footer-formatter="salesMoneyFormatter">Seller Received (&#8377;)</th>
                            <?php } ?>
                            <th data-field="final_total" data-sortable="true" data-align="right" data-footer-formatter="salesMoneyFormatter">Final Total (&#8377;)</th>
                            <th data-field="platform_fee" data-sortable="true" data-align="right" data-footer-formatter="salesMoneyFormatter">Platform Fee (&#8377;)</th>
                            <th data-field="convenience_fee" data-sortable="true" data-align="right" data-footer-formatter="salesMoneyFormatter">Convenience Fee (&#8377;)</th>
                            <th data-field="payment_method" data-sortable="true">Payment Method</th>
                            <th data-field="active_status" data-sortable="true" data-formatter="statusBadgeFormatter">Order Status</th>
                            <th data-field="refund_status" data-formatter="refundListFormatter">Refund Status</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"></div>
    </div>
<?php } else { ?>
    <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view reports</div>
<?php } ?>
<script>
    function salesTotalLabel() {
        return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>';
    }
    function salesMoneyFormatter(data) {
        var field = this.field;
        var sum = 0;
        if (data && data.length) {
            for (var i = 0; i < data.length; i++) {
                var row = data[i];
                if (!row) continue;
                var val = row[field];
                if (val === undefined || val === null) continue;

                if (Array.isArray(val)) {
                    for (var j = 0; j < val.length; j++) {
                        var item = val[j];
                        if (typeof item === 'string') {
                            item = item.replace(/[^0-9.\-]/g, '');
                        }
                        sum += (parseFloat(item) || 0);
                    }
                } else {
                    if (typeof val === 'string') {
                        val = val.replace(/[^0-9.\-]/g, '');
                    }
                    sum += (parseFloat(val) || 0);
                }
            }
        }
        return '<span style="color:green;font-weight:bold;font-size:large;">\u20B9 ' + sum.toFixed(2) + '</span>';
    }

    // product_details arrives as an array, e.g.
    // ["Tomato - 2 kg", "Onion - 500 g"]
    // Rendered as one bullet per product, all in a single cell/row.
    function productListFormatter(value) {
        if (!value || !value.length) return '-';
        var items = Array.isArray(value) ? value : [value];
        var html = '<ul class="bullet-list">';
        items.forEach(function (item) {
            html += '<li>' + item + '</li>';
        });
        html += '</ul>';
        return html;
    }

    // vendor_price / discounted_price arrive as arrays built with the same
    // ordering as product_details, so index N here is the price for the
    // same item as index N in the product list above.
    function priceListFormatter(value) {
        if (!value || !value.length) return '-';
        var items = Array.isArray(value) ? value : [value];
        var html = '<ul class="bullet-list">';
        items.forEach(function (item) {
            html += '<li>' + item + '</li>';
        });
        html += '</ul>';
        return html;
    }

    // active_status -> colored badge
    function statusBadgeFormatter(value) {
        if (!value) return '-';
        var key = String(value).toLowerCase();
        var cls = 'status-default';
        if (key === 'pending' || key === 'received') cls = 'status-pending';
        else if (key === 'processing' || key === 'processed' || key === 'shipped') cls = 'status-processing';
        else if (key === 'completed' || key === 'delivered') cls = 'status-completed';
        else if (key === 'cancelled' || key === 'canceled' || key === 'returned') cls = 'status-cancelled';
        return '<span class="status-badge ' + cls + '">' + value + '</span>';
    }

    // refund_status -> bullet list of badges per product item
    function refundListFormatter(value, row) {
        if (!value || !value.length) return '-';
        var items = Array.isArray(value) ? value : [value];
        var html = '<ul class="bullet-list" style="margin-bottom:2px;">';
        items.forEach(function (item) {
            var str = String(item).trim();
            var lower = str.toLowerCase();
            var cls = 'default';
            if (lower.indexOf('refunded') !== -1 && lower.indexOf('not') === -1) {
                cls = 'success';
            } else if (lower.indexOf('not refunded') !== -1 || lower.indexOf('failed') !== -1) {
                cls = 'danger';
            } else if (lower.indexOf('wallet') !== -1) {
                cls = 'info';
            } else if (lower.indexOf('cod') !== -1 || str === '-' || str === 'N/A') {
                cls = 'default';
            }
            html += '<li><span class="label label-' + cls + '" style="font-size:11px;padding:3px 7px;display:inline-block;margin:1px 0;">' + str + '</span></li>';
        });
        html += '</ul>';

        if (row && row.can_retry_refund && row.refund_action) {
            html += '<div style="margin-top:4px;">' + row.refund_action + '</div>';
        }
        return html;
    }

    // Process / Retry refund click handler
    $(document).on('click', '.btn-retry-refund', function(e) {
        e.preventDefault();
        var orderId = $(this).data('order-id');
        var $btn = $(this);

        var promptMsg = "Are you sure you want to process Razorpay refund for Order #" + orderId + "?\n\nIf you have a specific Razorpay Payment ID (e.g. pay_XXXXX), enter it below, or leave blank to auto-detect from order records:";
        var manualPaymentId = prompt(promptMsg, "");
        if (manualPaymentId === null) {
            return; // user cancelled prompt
        }
        manualPaymentId = manualPaymentId.trim();

        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: 'api-firebase/order-process.php',
            type: 'POST',
            data: {
                ajaxCall: 1,
                process_refund: 1,
                order_id: orderId,
                payment_id: manualPaymentId
            },
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Process Refund');
                if (resp && resp.error === false) {
                    alert('SUCCESS: ' + (resp.message || 'Refund initiated successfully.'));
                    $('#sales_table').bootstrapTable('refresh');
                } else {
                    var errMsg = (resp && resp.message) ? resp.message : 'Unknown error processing refund.';
                    alert('FAILED: ' + errMsg);
                }
            },
            error: function(xhr, status, err) {
                $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Process Refund');
                alert('Network or server error: ' + (err || status));
            }
        });
    });

    // ── Zone → Seller cascade ──────────────────────────────────────────────────
    // When the zone dropdown changes:
    //   1. Reload the Seller dropdown via AJAX (only sellers in that zone)
    //   2. Refresh the table rows to match the new zone
    $(document).on('change', '#zone_filter', function() {
        var zoneId = $(this).val();
        // Reset seller to "All Sellers"
        $('#seller_filter').val('');
        $('#seller_filter').empty().append('<option value="">All Sellers</option>');
        $.ajax({
            url: 'api-firebase/get_sellers_by_zone.php',
            type: 'GET',
            data: { zone_id: zoneId },
            dataType: 'json',
            success: function(data) {
                $.each(data, function(i, seller) {
                    $('#seller_filter').append('<option value="' + seller.id + '">' + seller.name + '</option>');
                });
            }
        });
        // Table refresh is handled by the existing change listener in sales-report.php
        $('#sales_table').bootstrapTable('refresh');
    });

    $('body').on('click', '.export', function(){
        var start = $('#start_from').val();
        var end   = $('#end_to').val();
        if(start != "" && end != ""){
            var url = 'public/test.php?start='+start+'&end='+end;
            document.location.href = url;
        } else {
            alert("please select from and to date");
        }
    });
</script>
