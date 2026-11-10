    <div class="row">
        <div class="col-xs-12">
            <?php if($permissions['customers']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Sellers</h3>
                </div>
                <div class="box-body">
                    <?php
                    $sql_query = "SELECT id, name FROM zone ORDER BY name ASC";
                    $db->sql($sql_query);
                    $res_zone = $db->getResult();
                    ?>
                    <form method="POST" id="filter_form" name="filter_form">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="filter_zone" class="control-label">Filter By Zone:</label>
                                <select id="filter_zone" name="filter_zone" placeholder="Select Zone" class="form-control">
                                    <option value="">All Zones</option>
                                    <?php if(!empty($res_zone)){ foreach($res_zone as $rz){ ?>
                                        <option value="<?=$rz['id']?>"><?=htmlspecialchars($rz['name'])?></option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="filter_store_status" class="control-label">Filter By Store Status:</label>
                                <select id="filter_store_status" name="filter_store_status" class="form-control">
                                    <option value="">All (Opened &amp; Closed)</option>
                                    <option value="open">Opened</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="seller_list"
						data-url="api-firebase/get-bootstrap-table-data.php?table=sellers"
						data-page-list="[5, 10, 20, 50, 100, 200]"
						data-show-refresh="true" data-show-columns="true"
						data-side-pagination="server" data-pagination="true"
						data-search="true" data-trim-on-search="false"
						data-filter-control="true" data-filter-show-clear="true"
						data-sort-name="id" data-sort-order="desc"
						data-query-params="queryParams_seller">
                    <thead>
                    <tr>
                    <th data-field="id" data-sortable="true">ID</th>
                    <th data-field="name" data-sortable="true">Name</th>
                    <th data-field="mobile" data-sortable="true">Mobile</th>
                    <th data-field="email" data-sortable="true">Email</th>
                    <th data-field="company_name" data-sortable="true">Company Name</th>
                    <th data-field="zone_name" data-sortable="true">Zone</th>
                    <th data-field="commission" data-sortable="true">Commission (%)</th>
                    <th data-field="store_status" data-sortable="true">Store Status</th>
                    <th data-field="status" data-sortable="false">Account Status</th>
                    <th data-field="date_created" data-sortable="true">Created At</th>
                    <th data-field="operate" data-sortable="false">Action</th>
                    </tr>
                    </thead>

                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view customers</div>
        <?php } ?>
            <!-- /.box -->
        </div>
    </div>
    <!-- /.row (main row) -->
<script>
function queryParams_seller(p){
    var zone = $('#filter_zone').val();
    if(zone){
        p.filter_zone = zone;
    }
    var storeStatus = $('#filter_store_status').val();
    if(storeStatus){
        p.filter_store_status = storeStatus;
    }
    return p;
}
$(document).ready(function(){
    $('#filter_zone, #filter_store_status').on('change', function(){
        $('#seller_list').bootstrapTable('refresh');
    });
});
$(document).on('click', '.toggle-store-status', function(e){
    e.preventDefault();
    var btn = $(this);
    var sellerId = btn.data('id');
    var targetStatus = btn.data('status');
    var actionText = (targetStatus === 'true') ? 'Open' : 'Close';
    if(confirm('Are you sure you want to ' + actionText + ' this store?')){
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');
        $.ajax({
            url: 'public/db-operation.php',
            type: 'POST',
            data: {
                toggle_store_status: 1,
                seller_id: sellerId,
                status: targetStatus
            },
            dataType: 'json',
            success: function(resp){
                if(resp && !resp.error){
                    $('#seller_list').bootstrapTable('refresh');
                }else{
                    alert((resp && resp.message) ? resp.message : 'Failed to update store status');
                    $('#seller_list').bootstrapTable('refresh');
                }
            },
            error: function(){
                alert('Network or server error while updating store status.');
                $('#seller_list').bootstrapTable('refresh');
            }
        });
    }
});
</script>
