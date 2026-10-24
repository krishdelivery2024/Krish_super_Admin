<?php $page="Sales Report";
include"header.php";?>
      
        <?php include('public/sales-report-table1.php'); ?>
      
  
<?php include"footer.php";?>
<script src="dist/plugin/bootstrap-table/bootstrap-table-print.min.js"></script>
<link href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css" rel="stylesheet">
<link rel="stylesheet" href="dist/plugin/bootstrap-table/bootstrap-table-sticky-header.min.css">
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF/jspdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
<script src="dist/plugin/bootstrap-table/bootstrap-table-sticky-header.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/export/bootstrap-table-export.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.js"></script>
<script>
// Pass the role check from PHP into JS once, reused below
var isSeller = <?php echo (isset($_SESSION['role']) && $_SESSION['role'] == 'seller') ? 'true' : 'false'; ?>;
var $table = $('#sales_table');

function salesTotalLabel() {
    return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>';
}
function transTotalLabel() {
    return salesTotalLabel();
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
function transMoneyFormatter(data) {
    return salesMoneyFormatter.call(this, data);
}

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

$(document).ready(function(){
    if ($.fn.bootstrapTable && $.fn.bootstrapTable.defaults) {
        var _expOpts = $.fn.bootstrapTable.defaults.exportOptions || {};
        _expOpts.onCellHtmlData = function($cell, rowIndex, colIndex, html) {
            if ($cell.is('th')) {
                return $cell.find('.th-inner').text();
            }
            if ($cell.find('ul.bullet-list').length) {
                var items = [];
                $cell.find('ul.bullet-list li').each(function() {
                    items.push($(this).text().trim());
                });
                return items.join(' ; ');
            }
            var txt = html ? $('<div>').html(html).text().trim() : '';
            return txt.replace(/\u20B9|&#8377;/g, 'Rs.').replace(/\u2014|\u2013/g, '-').replace(/&mdash;/g, '-').replace(/&nbsp;|\u00a0/g, ' ');
        };
        $.fn.bootstrapTable.defaults.exportOptions = _expOpts;
    }

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

    // Payment method / seller / status dropdowns trigger a table refresh
    // (zone_filter is handled separately by the cascade in sales-report-table1.php
    //  which also reloads the seller dropdown before refreshing)
    $(document).on('change', '#payment_method_filter, #seller_filter, #status_filter', function(){
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
        // Empty string means "all zones"; "0" is the explicit
        // unassigned selection and must survive as 0, not be dropped.
        "zone_id": $('#zone_filter').length ? $('#zone_filter').val() : '',
        "order_status": $('#status_filter').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
    };
}
</script>
