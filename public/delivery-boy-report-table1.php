<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
<style>
    span.export {
    background: blue;
    color: white;
    padding: 10px;
    cursor: pointer;
}

/* Bulleted product list (name + qty + unit combined per line) */
#delivery_boy_table ul.bullet-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
#delivery_boy_table ul.bullet-list li {
    position: relative;
    padding-left: 14px;
    line-height: 1.6;
    white-space: nowrap;
}
#delivery_boy_table ul.bullet-list li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: #7a7a7a;
}

/* Order status badge */
#delivery_boy_table .status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: #fff;
    text-transform: capitalize;
    white-space: nowrap;
}
#delivery_boy_table .status-pending    { background-color: #f0ad4e; } /* amber */
#delivery_boy_table .status-processing { background-color: #5bc0de; } /* blue  */
#delivery_boy_table .status-completed  { background-color: #5cb85c; } /* green */
#delivery_boy_table .status-cancelled  { background-color: #d9534f; } /* red   */
#delivery_boy_table .status-default    { background-color: #777777; } /* gray  */
</style>
<?php
    if($permissions['reports']['read']==1) { 
?>
    <div class="row small-spacing">
        <div class="col-xs-12">
			<div class="box-content">
                <h3 class="box-title">Delivery Boy Report</h3>
                <form method="POST" id="filter_form_db" name="filter_form_db">
				    <div class="row">
					     <div class="col-md-2 form-group">
						     <label for="db_from_date" class="control-label">From Date</label>
						     <input type="text" class="form-control" id="db_from_date" name="from_date" autocomplete="off" placeholder="Select From Date" />
					    </div>
					     <div class="col-md-2 form-group">
						     <label for="db_to_date" class="control-label">To Date</label>
						     <input type="text" class="form-control" id="db_to_date" name="to_date" autocomplete="off" placeholder="Select To Date" />
					    </div>
					    <input type="hidden" id="db_start_from" name="start_date">
					        <input type="hidden" id="db_end_to" name="end_date">

                        <!-- Payment Method filter -->
                        <div class="col-md-2 form-group">
                            <label for="db_payment_method_filter" class="control-label">Payment Method</label>
                            <select class="form-control" id="db_payment_method_filter" name="payment_method">
                                <option value="">All</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="Online Payment">Online Payment</option>
                            </select>
                        </div>

                        <!-- Delivery Boy filter (dynamically filtered by zone) -->
                        <div class="col-md-2 form-group">
                            <label for="db_boy_filter" class="control-label">Delivery Boy</label>
                            <select class="form-control" id="db_boy_filter" name="delivery_boy_id">
                                <option value="">All Delivery Boys</option>
                                <?php
                                    $db->sql("SELECT id, name FROM delivery_boys ORDER BY name ASC");
                                    $delivery_boy_list = $db->getResult();
                                    if (is_array($delivery_boy_list)) {
                                        foreach ($delivery_boy_list as $db_row) {
                                            echo '<option value="' . intval($db_row['id']) . '">'
                                                . htmlspecialchars($db_row['name'], ENT_QUOTES) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <!-- Zone filter. "Unassigned" is a real selection, not
                             filler: orders placed before zones existed carry a
                             NULL zone_id and would otherwise be unreachable. -->
                        <div class="col-md-2 form-group">
                            <label for="db_zone_filter" class="control-label">Zone</label>
                            <select class="form-control" id="db_zone_filter" name="zone_id">
                                <option value="">All Zones</option>
                                <option value="0">Unassigned (no zone)</option>
                                <?php
                                    $db->sql("SELECT id, name FROM zone ORDER BY name ASC");
                                    $zone_list = $db->getResult();
                                    if (is_array($zone_list)) {
                                        foreach ($zone_list as $zone_row) {
                                            echo '<option value="' . intval($zone_row['id']) . '">'
                                                . htmlspecialchars($zone_row['name'], ENT_QUOTES) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <!-- Order Status filter -->
                        <div class="col-md-2 form-group">
                            <label for="db_status_filter" class="control-label">Order Status</label>
                            <select class="form-control" id="db_status_filter" name="order_status">
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
				            <span class="export_db">Export Data</span>
				        </div>
				       <div class="col-md-3 pull-right form-group">
				           <label for="db_keyword" class="control-label">Search</label>
				           <input type="text" class="form-control" id="db_keyword" name="search" placeholder="Enter Keyword" autocomplete="off" />
				        </div>
				      </div>
                </form>
                <div class="box-body table-responsive">
                    <table id='delivery_boy_table' class="table table-hover" data-toggle="table" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=delivery_boy_report"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" 
                        data-show-columns="true"
                        data-side-pagination="server" 
                        data-pagination="false"
                        data-search="false" 
                        data-trim-on-search="false"
                        data-filter-control="true" 
                        data-query-params="queryParams_delivery_boy"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-show-export="true"
                        data-export-types='["excel","pdf"]'
                        data-export-data-type="all"
                        data-show-footer="true"
                        data-export-footer="true">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" data-footer-formatter="dbTotalLabel">ID</th>
                            <th data-field="delivery_boy_name" data-sortable="true">Delivery Boy Name</th>
                            <th data-field="delivery_boy_mobile" data-sortable="true">Delivery Boy Mobile</th>
                            <th data-field="zone_name" data-sortable="true">Order Zone</th>
                            <th data-field="delivery_boy_zone_name" data-sortable="true">Boy's Zone</th>
                            <th data-field="product_details" data-formatter="productListFormatter">Product / Qty / Unit</th>
                            <th data-field="discounted_price" data-formatter="priceListFormatter" data-align="right" data-footer-formatter="dbMoneyFormatter">Discounted Price (&#8377;)</th>
                            <th data-field="delivery_charge" data-sortable="true" data-align="right" data-footer-formatter="dbMoneyFormatter">Delivery Charge (&#8377;)</th>
                            <th data-field="final_total" data-sortable="true" data-align="right" data-footer-formatter="dbMoneyFormatter">Final Total (&#8377;)</th>
                            <th data-field="commission_amount" data-sortable="false" data-align="right" data-footer-formatter="dbMoneyFormatter">Commission (&#8377;)</th>
                            <th data-field="platform_fee" data-sortable="true" data-align="right" data-footer-formatter="dbMoneyFormatter">Platform Fee (&#8377;)</th>
                            <th data-field="convenience_fee" data-sortable="true" data-align="right" data-footer-formatter="dbMoneyFormatter">Convenience Fee (&#8377;)</th>
                            <th data-field="payment_method" data-sortable="true">Payment Method</th>
                            <th data-field="active_status" data-sortable="true" data-formatter="statusBadgeFormatter">Order Status</th>
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
    function dbTotalLabel() {
        return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>';
    }
    function dbMoneyFormatter(data) {
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
        return '<span style="color:green;font-weight:bold;font-size:large;">&#8377; ' + sum.toFixed(2) + '</span>';
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

    // discounted_price arrives as an array built with the same ordering as
    // product_details, so index N here is the price for the same item as
    // index N in the product list above.
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

    // ── Zone → Delivery Boy cascade ───────────────────────────────────────────
    // When the zone dropdown changes:
    //   1. Reload the Delivery Boy dropdown via AJAX (only boys in that zone)
    //   2. Refresh the table rows to match the new zone
    $(document).on('change', '#db_zone_filter', function() {
        var zoneId = $(this).val();
        // Reset delivery boy to "All Delivery Boys"
        $('#db_boy_filter').val('');
        $('#db_boy_filter').empty().append('<option value="">All Delivery Boys</option>');
        $.ajax({
            url: 'api-firebase/get_delivery_boys_by_zone.php',
            type: 'GET',
            data: { zone_id: zoneId },
            dataType: 'json',
            success: function(data) {
                $.each(data, function(i, boy) {
                    $('#db_boy_filter').append('<option value="' + boy.id + '">' + boy.name + '</option>');
                });
            }
        });
        // Refresh table rows to match the selected zone
        $('#delivery_boy_table').bootstrapTable('refresh');
    });

    $(document).on('click', '.export_db', function(){
        var start = $('#db_start_from').val();
        var end   = $('#db_end_to').val();
        if(start != "" && end != ""){
            var url = 'public/test.php?report=delivery_boy&start='+start+'&end='+end;
            document.location.href = url;
        } else {
            alert("please select from and to date");
        }
    });
</script>