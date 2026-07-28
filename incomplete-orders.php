<?php $page="Incomplete Orders";
include"header.php";?>
	<?php
    if($permissions['orders']['read']==1) { 
?>
<style>
.uppercase {
  text-transform: uppercase;
}
.row.totalords {
    margin-left: -1px;
    margin-top: 13px;
    font-size: 15px;
}
</style>
    <div class="row small-spacing">
        <div class="col-xs-12">
			<div class="box-content">
                    <h3 class="box-title">Incomplete Orders</h3>
                    <div class="dropdown js__drop_down">
						<a href="#" class="dropdown-icon glyphicon glyphicon-option-vertical js__drop_down_button"></a>
						<ul class="sub-menu">
							
						</ul>
						<!-- /.sub-menu -->
					</div>
                <form method="POST" id="filter_form" name="filter_form">
				<div class="row">
					<div class="col-md-7 form-group form-inline">
						<label for="date" class="control-label">From & To Date</label>
						<input type="text" style="width:80%" class="form-control" id="date" name="date" autocomplete="off" />
					</div>
					<input type="hidden" id="start_date" name="start_date">
					<input type="hidden" id="end_date" name="end_date">
				</div>
				<div class="row totalords">
				   <span><b>Total Number of Orders :</b></span> <span class="totalorder"></span>
				</div>    
                </form>
                    
                        <table class="table no-margin" data-toggle="table"  id="order_list"
                            data-url="api-firebase/get-bootstrap-table-data.php?table=incomplte_orders"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-show-refresh="true" data-show-columns="true"
                            data-side-pagination="server" data-pagination="true"
                            data-search="true" data-trim-on-search="false"
							data-sort-name="id" data-sort-order="desc"
                            data-query-params="queryParams_1"
                            data-show-footer="true"
                            data-footer-style="footerStyle"
                            >
                            <thead>
                                <tr>
    								<th data-field="id" data-sortable='true'>O.ID</th>
									<th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
									 <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                    <th data-field="name" data-sortable='true'>U.Name</th>
									<th data-field="mobile" data-sortable='true' data-visible="true">Mob.</th>
									<th data-field="items" data-sortable='true' data-visible="false">Items</th>
									<th data-field="total" data-sortable='true' data-visible="false">Total(<?=$settings['currency']?>)</th>
									<th data-field="delivery_charge" data-sortable='true'>D.Chrg</th>
									<th data-field="tax" data-sortable='false'>Tax <?=$settings['currency']?>(%)</th>
									<th data-field="discount" data-sortable='true' data-visible="false">Disc.<?=$settings['currency']?>(%)</th>
									<th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
									<th data-field="promo_discount" data-sortable='true' data-visible="false">Promo Disc.(<?=$settings['currency']?>)</th>
									<th data-field="wallet_balance" data-sortable='true' data-visible="false">Wallet Used(<?=$settings['currency']?>)</th>
									<th data-field="final_total" data-sortable='true'>F.Total(<?=$settings['currency']?>)</th>
									<th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
									<th data-field="payment_method" data-sortable='true' data-visible="true">P.Method</th>
									<th data-field="address" data-sortable='true' data-visible="false">Address</th>
									<th data-field="delivery_time" data-sortable='true' data-visible='false'>D.Time</th>
									<th data-field="status" data-sortable='true' data-visible='false'>Status</th>
									<th data-field="active_status" data-sortable='true' data-visible='false'>A.Status</th>
									<th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
									<th data-field="operate">Action</th>
								</tr>
                            </thead>
                        </table>
                    
            </div>
        </div>
    </div>
 <?php } else { ?>
    <div class="alert alert-danger topmargin-sm leftmargin-sm">You have no permission to view orders.</div>
 <?php } ?>
<!-- /.content -->

<?php 
$db->disconnect();
?>
<script>
$(document).ready(function(){
    setInterval(function () {
        var ab=$(".pagination-info").html();  console.log(ab); var b=ab.split(" "); console.log(b[11]);
        $(".totalorder").html(b[11]);
    },1000);
});

$(document).on('click','.update_payment_status',function(){
       var id = $(this).data('id');
       if(confirm('Are you sure want to update payment status as Success for order?')){
        $.ajax({
            type:'POST',
            url: 'public/db-operation.php',
            data:'id='+id+'&update_payment_status=1',
            success:function(result){
                if(result==0){
                    $('#order_list').bootstrapTable('refresh');
                }
                if(result==1){
                    alert('Error! Payment could not be Updated.');
                }
                if(result==2){
                    alert('You have no permission to Update Payment');
                }
            }
        });
    }
    
});
</script>
	
<?php include"footer.php";?>

