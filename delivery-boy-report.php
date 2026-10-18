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
var $delivery_boy_table = $('#delivery_boy_table');


// Flatten arrays to semicolon-separated plain text for Excel/PDF
function exportListFormatter(value) {
    if (!value || !value.length) return '-';
    var items = Array.isArray(value) ? value : [value];
    return items.join(' ; ');
}
function exportStatusFormatter(value) {
    return value ? value : '-';
}

$(document).ready(function(){
    // Set exportOptions so onCellHtmlData strips HTML tags from footer/body cells
    $('#delivery_boy_table').bootstrapTable('refreshOptions', {
        exportOptions: {
            onCellHtmlData: function(cell, row, col, data) {
                if (cell.is('td') || cell.is('th')) {
                    // Strip any HTML (e.g. <span>, <ul>, <li>) to plain text
                    return $('<div>').html(data).text();
                }
                return data;
            }
        }
    });

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

    // Payment method / delivery boy / zone / status dropdowns trigger a table refresh
    $(document).on('change', '#db_payment_method_filter, #db_boy_filter, #db_zone_filter, #db_status_filter', function(){
        $('#delivery_boy_table').bootstrapTable('refresh');
    });
});

function queryParams_delivery_boy(p){
    return {
        "start_date":      $('#db_start_from').val(),
        "end_date":        $('#db_end_to').val(),
        "search":          $('#db_keyword').val(),
        "payment_method":  $('#db_payment_method_filter').val(),
        "delivery_boy_id": $('#db_boy_filter').val(),
        // Empty string means "all zones"; "0" is the explicit
        // unassigned selection and must survive as 0, not be dropped.
        "zone_id":         $('#db_zone_filter').val(),
        "order_status":    $('#db_status_filter').val(),
        limit:  p.limit,
        sort:   p.sort,
        order:  p.order,
        offset: p.offset,
    };
}
</script>
