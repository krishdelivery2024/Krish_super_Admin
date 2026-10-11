<?php
    include_once('includes/functions.php');
    $fn_parcel = new custom_functions;
    $my_zone_scope = $fn_parcel->get_zone_scope(isset($_SESSION['id']) ? $_SESSION['id'] : 0);
    $db->sql("SELECT id, name, status FROM zone ORDER BY name ASC");
    $res_zone = $db->getResult();
    if(empty($res_zone)) $res_zone = array();
    if(!empty($my_zone_scope)){
        $res_zone = array_values(array_filter($res_zone, function($z) use ($my_zone_scope){ return in_array((int)$z['id'], $my_zone_scope); }));
    }
?>

    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Parcel Payment Transactions</h3>
                    <h4 class="box-subtitle">Online payments made against parcel pickup requests.</h4>
                </div>
                <div class="box-body table-responsive">
                    <form id="parcel_filter_form" style="margin-bottom:10px;">
                    <div class="form-group" style="display:inline-block;margin-bottom:0;">
                        <label for="pdate" class="control-label">From &amp; To Date:</label>
                        <input type="text" id="pdate" name="pdate" class="form-control" style="width:230px;display:inline-block;margin-left:5px;" autocomplete="off" />
                        <input type="hidden" id="p_start_date" name="p_start_date" />
                        <input type="hidden" id="p_end_date" name="p_end_date" />
                    </div>
                    <div class="form-group" style="display:inline-block;margin-bottom:0;margin-left:15px;">
                        <label for="filter_zone" class="control-label">Filter By Zone:</label>
                        <select id="filter_zone" name="filter_zone" class="form-control" style="width:220px;display:inline-block;margin-left:5px;">
                            <option value="">All Zones</option>
                            <?php if(!empty($res_zone)){ foreach($res_zone as $rz){ ?>
                                <option value="<?=$rz['id']?>"><?=htmlspecialchars($rz['name'])?></option>
                            <?php }} ?>
                        </select>
                    </div>
                    </form>
                    <table class="table table-hover" data-toggle="table" id="parcel_transactions_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=parcel_transactions"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="client" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-show-export="true"
                        data-export-types='["excel","pdf"]'
                        data-export-data-type="all"
                        data-show-footer="true"
                        data-export-footer="true"
                        data-query-params="parcelTxnParams">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" data-footer-formatter="pTransTotalLabel">Txn ID</th>
                            <th data-field="user_name" data-sortable="false">User</th>
                            <th data-field="user_mobile" data-sortable="false">Mobile</th>
                            <th data-field="order_id" data-sortable="true">Parcel ID</th>
                            <th data-field="type" data-sortable="true">Method</th>
                            <th data-field="txn_id" data-sortable="false">TX N ID</th>
                            <th data-field="delivery_charge" data-sortable="false" data-align="right" data-sorter="numericSorter" data-footer-formatter="pTransMoneyFormatter">Delivery Charge</th>
                            <th data-field="platform_fee" data-sortable="false" data-align="right" data-sorter="numericSorter" data-footer-formatter="pTransMoneyFormatter">Platform Fee</th>
                            <th data-field="convenience_fee" data-sortable="false" data-align="right" data-sorter="numericSorter" data-footer-formatter="pTransMoneyFormatter">Convenience Fee</th>
                            <th data-field="gst" data-sortable="false" data-align="right" data-sorter="numericSorter" data-footer-formatter="pTransMoneyFormatter">GST</th>
                            <th data-field="amount" data-sortable="true" data-align="right" data-sorter="numericSorter" data-footer-formatter="pTransMoneyFormatter">Amount</th>
                            <th data-field="status" data-sortable="false">Status</th>
                            <th data-field="transaction_date" data-sortable="true">Date</th>
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>

    <div class="modal fade" id="parcelFeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Parcel Fee Breakdown</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered" style="margin-bottom:10px;">
                        <tbody>
                            <tr><th style="width:65%;">Distance</th><td id="pf_distance">&mdash;</td></tr>
                            <tr><th>Per KM price</th><td id="pf_perkm">&mdash;</td></tr>
                            <tr><th>Base fare</th><td id="pf_base">&mdash;</td></tr>
                            <tr><th>Parcel weight</th><td id="pf_weight">&mdash;</td></tr>
                            <tr><th>Delivery charge <small class="text-muted">(base fare + distance &times; per KM)</small></th><td id="pf_delivery">&mdash;</td></tr>
                            <tr><th>Platform fee</th><td id="pf_platform">&mdash;</td></tr>
                            <tr><th>Convenience fee</th><td id="pf_convenience">&mdash;</td></tr>
                            <tr><th>GST</th><td id="pf_gst">&mdash;</td></tr>
                            <tr class="active"><th>Grand total</th><td id="pf_grand">&mdash;</td></tr>
                            <tr class="active"><th>Amount paid</th><td id="pf_amount">&mdash;</td></tr>
                        </tbody>
                    </table>
                    <p class="text-muted" style="margin:0;">
                        Delivery charge = base fare + (distance &times; per KM).<br>
                        Grand total = delivery charge + platform fee + convenience fee + GST.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<script>
function parcelTxnParams(p){
    return {
        filter_zone: $('#filter_zone').val(),
        start_date: $('#p_start_date').val(),
        end_date: $('#p_end_date').val(),
        client_all: 1,
        limit:p.limit,
        sort:p.sort,
        order:p.order,
        offset:p.offset,
        search:p.search
    };
}
function pfMoney(v){
    return '\u20B9 ' + (parseFloat(v) || 0).toFixed(2);
}
function pTransNum(v){
    var s = String(v).replace(/\u20B9|&#8377;/g, '');
    var n = parseFloat(s.replace(/[^0-9.\-]/g, ''));
    return isNaN(n) ? 0 : n;
}
function numericSorter(a, b) {
    return pTransNum(a) - pTransNum(b);
}
function pTransTotalLabel() {
    return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>';
}
function pTransCountLabel(data) {
    return '<span style="color:green;font-weight:bold;font-size:large;">' + data.length + ' Records</span>';
}
function pTransMoneyFormatter(data) {
    var field = this.field;
    var sum = 0;
    for (var i = 0; i < data.length; i++) {
        sum += pTransNum(data[i][field]);
    }
    return '<span style="color:green;font-weight:bold;font-size:large;">\u20B9 ' + sum.toFixed(2) + '</span>';
}
$(document).ready(function(){
    if ($.fn.bootstrapTable && $.fn.bootstrapTable.defaults) {
        var _expOpts = $.fn.bootstrapTable.defaults.exportOptions || {};
        _expOpts.onCellHtmlData = function($cell, rowIndex, colIndex, html) {
            var txt = $cell.is('th') ? $cell.find('.th-inner').text() : String(html);
            return txt.replace(/\u20B9|&#8377;/g, 'Rs.').replace(/\u2014|\u2013/g, '-').replace(/&mdash;/g, '-').replace(/&nbsp;|\u00a0/g, ' ');
        };
        $.fn.bootstrapTable.defaults.exportOptions = _expOpts;
    }
    if ($.fn.daterangepicker) {
        var ds = $('#p_start_date').val() ? moment($('#p_start_date').val(), 'YYYY-MM-DD') : moment().startOf('month');
        var de = $('#p_end_date').val() ? moment($('#p_end_date').val(), 'YYYY-MM-DD') : moment();
        $('#pdate').daterangepicker({
            "autoApply": true,
            "showDropdowns": true,
            "alwaysShowCalendars": true,
            "startDate": ds,
            "endDate": de,
            "locale": {
                "format": "DD/MM/YYYY",
                "separator": " - "
            }
        });
        $('#pdate').on('apply.daterangepicker', function(ev, picker) {
            var drp = $('#pdate').data('daterangepicker');
            $('#p_start_date').val(drp.startDate.format('YYYY-MM-DD'));
            $('#p_end_date').val(drp.endDate.format('YYYY-MM-DD'));
            $('#parcel_transactions_list').bootstrapTable('refresh');
        });
    }
    $('#parcelFeeModal').appendTo('body');
    $(document).on('click', '.parcel-fee-breakdown', function(){
        var b = $(this);
        var dist = parseFloat(b.data('distance')) || 0;
        var weight = parseFloat(b.data('weight')) || 0;
        $('#pf_distance').text(dist.toFixed(2) + ' km');
        $('#pf_perkm').text(pfMoney(b.data('perkm')));
        $('#pf_base').text(pfMoney(b.data('base')));
        $('#pf_weight').text(weight > 0 ? weight.toFixed(2) + ' kg' : '\u2014');
        $('#pf_delivery').text(pfMoney(b.data('delivery')));
        $('#pf_platform').text(pfMoney(b.data('platform')));
        $('#pf_convenience').text(pfMoney(b.data('convenience')));
        $('#pf_gst').text(pfMoney(b.data('gst')));
        $('#pf_grand').html('<strong>' + pfMoney(b.data('grand')) + '</strong>');
        $('#pf_amount').html('<strong>' + pfMoney(b.data('amount')) + '</strong>');
    });
    $('#filter_zone').on('change', function(){
        $('#parcel_transactions_list').bootstrapTable('refresh');
    });
});
</script>