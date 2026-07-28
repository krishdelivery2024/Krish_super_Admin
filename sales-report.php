<?php $page="Sales Report";
include"header.php";?>
      
        <?php include('public/sales-report-table1.php'); ?>
      
  
<?php include"footer.php";?>
<script src="dist/plugin/bootstrap-table/bootstrap-table-print.min.js"></script>
<link href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF/jspdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<script>
// Pass the role check from PHP into JS once, reused below
var isSeller = <?php echo (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') ? 'true' : 'false'; ?>;
var $table = $('#sales_table')

// Excel/PDF export can't render <ul>/badges, so these flatten the arrays
// and status back down to plain text - one line per item, semicolon
// separated, still lined up the same way as the on-screen bullets.
function exportListFormatter(value) {
    if (!value || !value.length) return '-';
    var items = Array.isArray(value) ? value : [value];
    return items.join(' ; ');
}
function exportStatusFormatter(value) {
    return value ? value : '-';
}

$(function() {
    $('#toolbar').find('select').change(function () {
        var exportColumns = [
            {
                field: 'state',
                checkbox: true,
                visible: $(this).val() === 'selected'
            },
            { field: 'id', title: 'ID' }
        ];
        if (!isSeller) {
            exportColumns.push({ field: 'seller_name', title: 'Seller Name' });
            exportColumns.push({ field: 'seller_mobile', title: 'Seller Mobile' });
        }
        exportColumns.push({ field: 'user_name', title: 'User Name' });
        exportColumns.push({ field: 'user_mobile', title: 'User Mobile' });
        exportColumns.push({ field: 'address', title: 'Address' });
        exportColumns.push({ field: 'product_details', title: 'Product / Qty / Unit', formatter: exportListFormatter });
        exportColumns.push({ field: 'order_date', title: 'Order Date' });
        if (!isSeller) {
            exportColumns.push({ field: 'vendor_price', title: 'Vendor Price', formatter: exportListFormatter });
            exportColumns.push({ field: 'discounted_price', title: 'Discounted Price', formatter: exportListFormatter });
        }
        exportColumns.push({ field: 'final_total', title: 'Final Total' });
        exportColumns.push({ field: 'platform_fee', title: 'Platform Fee' });
        exportColumns.push({ field: 'payment_method', title: 'Payment Method' });
        exportColumns.push({ field: 'active_status', title: 'Order Status', formatter: exportStatusFormatter });
        $table.bootstrapTable('destroy').bootstrapTable({
            exportDataType: $(this).val(),
            exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
            columns: exportColumns
        })
    }).trigger('change')
});
$(document).ready(function(){
    $('#from_date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e){
        $('#start_from').val(moment(e.date).format('YYYY-MM-DD'));
        if($('#end_to').val() != ''){
            $('#sales_table').bootstrapTable('refresh');
        }
    });
    $('#to_date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e){
        $('#end_to').val(moment(e.date).format('YYYY-MM-DD'));
        if($('#start_from').val() != ''){
            $('#sales_table').bootstrapTable('refresh');
        }
    });
    $(document).on('keyup', '#sales_keyword', function(){
        $('#sales_table').bootstrapTable('refresh');
    });

    // Payment method / seller dropdowns trigger a table refresh
    $(document).on('change', '#payment_method_filter, #seller_filter', function(){
        $('#sales_table').bootstrapTable('refresh');
    });
});
function queryParams_sales(p){
    return {
        "start_date": $('#start_from').val(),
        "end_date": $('#end_to').val(),
        "search": $('#sales_keyword').val(),
        "payment_method": $('#payment_method_filter').val(),
        "seller_id": $('#seller_filter').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
</script>