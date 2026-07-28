   <style>
    span.exportcustomer {
    background: blue;
    color: white;
    padding: 10px;
    cursor: pointer;
  }
</style>

    <div class="row">
        <div class="col-xs-12">
            <?php if($permissions['customers']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Customers</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" 
						data-url="api-firebase/get-bootstrap-table-data.php?table=referral_users"
						data-page-list="[5, 10, 20, 50, 100, 200]"
						data-show-refresh="true" data-show-columns="true"
						data-side-pagination="server" data-pagination="true"
						data-search="true" data-trim-on-search="false"
						data-filter-control="true" data-filter-show-clear="true"
						data-sort-name="id" data-sort-order="desc">
					<thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="mobile" data-sortable="true">Mobile</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="referred_count" data-sortable="true">No of Referrals</th>
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