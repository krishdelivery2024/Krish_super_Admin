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
                    <div class="form-group" style="display:inline-block;margin-bottom:10px;">
                        <label for="filter_zone" class="control-label">Filter By Zone:</label>
                        <select id="filter_zone" name="filter_zone" class="form-control" style="width:220px;display:inline-block;margin-left:5px;">
                            <option value="">All Zones</option>
                            <?php if(!empty($res_zone)){ foreach($res_zone as $rz){ ?>
                                <option value="<?=$rz['id']?>"><?=htmlspecialchars($rz['name'])?></option>
                            <?php }} ?>
                        </select>
                    </div>
                    <table class="table table-hover" data-toggle="table" id="parcel_transactions_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=parcel_transactions"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="parcelTxnParams">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">Txn ID</th>
                            <th data-field="user_name" data-sortable="false">User</th>
                            <th data-field="user_mobile" data-sortable="false">Mobile</th>
                            <th data-field="order_id" data-sortable="true">Parcel ID</th>
                            <th data-field="type" data-sortable="true">Method</th>
                            <th data-field="txn_id" data-sortable="false">TX N ID</th>
                            <th data-field="amount" data-sortable="true">Amount</th>
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

<script>
function parcelTxnParams(p){
    return {
        filter_zone: $('#filter_zone').val(),
        limit:p.limit,
        sort:p.sort,
        order:p.order,
        offset:p.offset,
        search:p.search
    };
}
$(document).ready(function(){
    $('#filter_zone').on('change', function(){
        $('#parcel_transactions_list').bootstrapTable('refresh');
    });
});
</script>