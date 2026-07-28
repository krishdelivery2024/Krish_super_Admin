<?php $page="Delivery Boy Report";
include"header.php";?>
      
        <?php include('public/delivery-boy-report-table1.php'); ?>
      
  
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
var $delivery_boy_table = $('#delivery_boy_table')
// Excel/PDF export can't render <ul> bullets, so this flattens the arrays
// back down to plain text - one line per item, semicolon separated,
// still lined up the same way as the on-screen bullets.
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
        $delivery_boy_table.bootstrapTable('destroy').bootstrapTable({
            exportDataType: $(this).val(),
            exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
            columns: [
                {
                    field: 'state',
                    checkbox: true,
                    visible: $(this).val() === 'selected'
                },
                { field: 'id', title: 'ID' },
                { field: 'delivery_boy_name', title: 'Delivery Boy Name' },
                { field: 'delivery_boy_mobile', title: 'Delivery Boy Mobile' },
                { field: 'product_details', title: 'Product / Qty / Unit', formatter: exportListFormatter },
                { field: 'discounted_price', title: 'Discounted Price', formatter: exportListFormatter },
                { field: 'delivery_charge', title: 'Delivery Charge' },
                { field: 'final_total', title: 'Final Total' },
                { field: 'platform_fee', title: 'Platform Fee' },
                { field: 'payment_method', title: 'Payment Method' },
                { field: 'active_status', title: 'Order Status', formatter: exportStatusFormatter }
            ]
        })
    }).trigger('change')
});
$(document).ready(function(){
    $('#db_from_date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e){
        $('#db_start_from').val(moment(e.date).format('YYYY-MM-DD'));
        if($('#db_end_to').val() != ''){
            $('#delivery_boy_table').bootstrapTable('refresh');
        }
    });
    $('#db_to_date').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true
    }).on('changeDate', function(e){
        $('#db_end_to').val(moment(e.date).format('YYYY-MM-DD'));
        if($('#db_start_from').val() != ''){
            $('#delivery_boy_table').bootstrapTable('refresh');
        }
    });
    $(document).on('keyup', '#db_keyword', function(){
        $('#delivery_boy_table').bootstrapTable('refresh');
    });

    // Payment method / delivery boy dropdowns trigger a table refresh
    $(document).on('change', '#db_payment_method_filter, #db_boy_filter', function(){
        $('#delivery_boy_table').bootstrapTable('refresh');
    });
});
function queryParams_delivery_boy(p){
    return {
        "start_date": $('#db_start_from').val(),
        "end_date": $('#db_end_to').val(),
        "search": $('#db_keyword').val(),
        "payment_method": $('#db_payment_method_filter').val(),
        "delivery_boy_id": $('#db_boy_filter').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
</script>