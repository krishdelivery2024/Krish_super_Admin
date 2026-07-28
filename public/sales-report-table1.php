<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
<style>
    span.export {
    background: blue;
    color: white;
    padding: 10px;
    cursor: pointer;
}

/* Bulleted product list (name + qty + unit combined per line) */
#sales_table ul.bullet-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
#sales_table ul.bullet-list li {
    position: relative;
    padding-left: 14px;
    line-height: 1.6;
    white-space: nowrap;
}
#sales_table ul.bullet-list li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: #7a7a7a;
}

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
</style>
<?php
    if($permissions['reports']['read']==1) { 
        $is_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');
?>
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

                        <!-- Seller filter (hidden for seller-role users, who are already scoped to themselves) -->
                        <?php if(!$is_seller) { ?>
                        <div class="col-md-2 form-group">
                            <label for="seller_filter" class="control-label">Seller</label>
                            <select class="form-control" id="seller_filter" name="seller_id">
                                <option value="">All Sellers</option>
                                <?php
                                    $db->sql("SELECT id, name FROM seller ORDER BY name ASC");
                                    $seller_list = $db->getResult();
                                    if (is_array($seller_list)) {
                                        foreach ($seller_list as $seller_row) {
                                            echo '<option value="' . intval($seller_row['id']) . '">'
                                                . htmlspecialchars($seller_row['name'], ENT_QUOTES) . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <?php } ?>

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
                        data-export-types='["excel","pdf"]'>
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <?php if(!$is_seller) { ?>
                            <th data-field="seller_name" data-sortable="true">Seller Name</th>
                            <th data-field="seller_mobile" data-sortable="true">Seller Mobile</th>
                            <?php } ?>
                            <th data-field="user_name" data-sortable="true">User Name</th>
                            <th data-field="user_mobile" data-sortable="true">User Mobile</th>
                            <th data-field="address" data-sortable="true">Address</th>
                            <th data-field="product_details" data-formatter="productListFormatter">Product / Qty / Unit</th>
                            <th data-field="order_date" data-sortable="true">Order Date</th>
                            <?php if(!$is_seller) { ?>
                            <th data-field="vendor_price" data-formatter="priceListFormatter">Vendor Price</th>
                            <th data-field="discounted_price" data-formatter="priceListFormatter">Discounted Price</th>
                            <?php } ?>
                            <th data-field="final_total" data-sortable="true">Final Total</th>
                            <th data-field="platform_fee" data-sortable="false">Platform Fee</th>
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
        if (key === 'pending') cls = 'status-pending';
        else if (key === 'processing') cls = 'status-processing';
        else if (key === 'completed') cls = 'status-completed';
        else if (key === 'cancelled' || key === 'canceled') cls = 'status-cancelled';
        return '<span class="status-badge ' + cls + '">' + value + '</span>';
    }

     $("body").on('click', '.export', function(){
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