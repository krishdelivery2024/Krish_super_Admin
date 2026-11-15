<?php
    $seller_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
?>
<style>
.table-responsive {
    overflow-x: auto;
}
.btn-set-avail {
    min-width: 80px;
    font-weight: 600;
}
.btn-set-avail.active {
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);
}
</style>
<div class="row small-spacing">
    <div class="col-xs-12">
        <div class="box-content">
            <h3 class="box-title"><i class="fa fa-cubes text-primary"></i> Products</h3>
            <p class="text-muted" style="margin-bottom: 15px;">Manage product availability for your store. Click <b>Available</b> or <b>Sold</b> to update stock status in real-time.</p>
            
            <div class="box-body table-responsive">
                <table id="seller_products_table" class="table table-hover table-striped"
                    data-toggle="table"
                    data-url="api-firebase/get-bootstrap-table-data.php?table=seller_products"
                    data-page-list="[10, 20, 50, 100, 200]"
                    data-page-size="20"
                    data-show-refresh="true"
                    data-side-pagination="server"
                    data-pagination="true"
                    data-search="true"
                    data-trim-on-search="false"
                    data-sort-name="id"
                    data-sort-order="desc">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" style="width: 80px;">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="vendor_price" data-sortable="true" style="width: 150px;">Vendor Price</th>
                            <th data-field="action" style="width: 200px; text-align: center;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-set-avail', function(e) {
    e.preventDefault();
    var $btn = $(this);
    var productId = $btn.data('id');
    var variantId = $btn.data('variant-id');
    var targetStatus = $btn.data('status');
    
    if ($btn.hasClass('active')) {
        return;
    }
    
    var $group = $btn.closest('.btn-group');
    $group.find('.btn').prop('disabled', true);
    
    $.ajax({
        url: 'public/update-product-availability.php',
        type: 'POST',
        dataType: 'json',
        data: {
            product_id: productId,
            variant_id: variantId,
            status: targetStatus
        },
        success: function(res) {
            $group.find('.btn').prop('disabled', false);
            if (res && !res.error) {
                if (res.is_available) {
                    $group.find('.btn-avail').addClass('btn-success active').removeClass('btn-default');
                    $group.find('.btn-sold').removeClass('btn-danger active').addClass('btn-default');
                } else {
                    $group.find('.btn-avail').removeClass('btn-success active').addClass('btn-default');
                    $group.find('.btn-sold').addClass('btn-danger active').removeClass('btn-default');
                }
            } else {
                alert((res && res.message) ? res.message : 'Failed to update status.');
            }
        },
        error: function() {
            $group.find('.btn').prop('disabled', false);
            alert('Error communicating with server. Please try again.');
        }
    });
});
</script>
