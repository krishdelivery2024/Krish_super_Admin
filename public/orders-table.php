<?php
    if($permissions['orders']['read']==1) { 
        $sql_query = "SELECT id, short_code 
        FROM routes 
        ORDER BY id ASC";
        
        // Execute query
        $db->sql($sql_query);
        // store result 
        $res_route=$db->getResult();

        $sql_query = "SELECT title 
			FROM time_slots where status=1
			ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_slot=$db->getResult();
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
                    <h3 class="box-title">Latest Orders</h3>
                    <div class="dropdown js__drop_down">
						<a href="#" class="dropdown-icon glyphicon glyphicon-option-vertical js__drop_down_button"></a>
						<ul class="sub-menu">
							
						</ul>
						<!-- /.sub-menu -->
					</div>
                <form method="POST" id="filter_form" name="filter_form">
				<div class="row">
					<div class="col-md-3 form-group ">
						<label for="date" class="control-label">From & To Date:</label>
						<input type="text"  class="form-control" id="date" name="date" autocomplete="off" />
					</div>
					<input type="hidden" id="start_date" name="start_date">
					<input type="hidden" id="end_date" name="end_date">
                    <div class="col-md-3  form-group" style="display:none;">
                        <label for="filter_route" class="control-label">Filter By Route:</label>
                        <select id="filter_route" name="filter_route" placeholder="Select Route" required class="form-control">
                            <option value="">All Orders</option>
                            <?php if(!empty($res_route)){
                                foreach($res_route as $row){ ?>
                                <option value="<?php echo $row['id']; ?>"><?php echo $row['short_code']; ?></option>
                            <?php  }}?>
                        </select>
					</div>
					
					<div class="col-md-3  form-group">
                    <label for="deliver_by" class="control-label">Delivery boy:</label>
                        <?php
                            $sql="SELECT id,name FROM delivery_boys WHERE status=1 and name <> 'dunzo'"; 
                            $db->sql($sql);
                            $result=$db->getResult();
                        ?>
                       <select id='deliver_by' name='deliver_by' class='form-control'>
                           <option value=''></option>
                            <?php foreach($result as $row1){ ?>
                                <option value='<?=$row1['id']?>'><?=$row1['name']?></option>
                            <?php }  ?>
                        </select>
					</div>

                    <div class="col-md-3  form-group" style="display:none;">
                    <label for="filter_slot" class="control-label">Filter By Slot:</label>
                      <select id="filter_slot" name="filter_slot" placeholder="Select Slot" required class="form-control">
                            <option value="">All Orders</option>
                            <?php if(!empty($res_slot)){
                                foreach($res_slot as $row){ ?>
                                <option value="<?php echo $row['title']; ?>"><?php echo $row['title']; ?></option>
                            <?php  }}?>
                        </select>
					</div>

					<div class="col-md-3 pull-right form-group">
                        <label for="filter_order" class="control-label">Filter By Status:</label>
                        <select id="filter_order" name="filter_order" placeholder="Select Status" required class="form-control">
                            <option value="">All Orders</option>
                            <option value='received'>Received</option>
                            <option value='processed'>Processed</option>
                            <option value='shipped'>Shipped</option>
                            <option value='delivered'>Delivered</option>
                            <option value='cancelled'>Cancelled</option>
                            <option value='returned'>Returned</option>
                        </select>
					</div>
                    <input type="hidden" id="filter_order_status" name="filter_order_status">
                    <input type="hidden" id="filter_order_route" name="filter_order_route">
                    <input type="hidden" id="filter_order_slot" name="filter_order_slot">
				</div>
				<div class="row totalords">
				   <span><b>Total Number of Orders :</b></span> <span class="totalorder"></span>
				</div>    
                </form>
                    
                        <table class="table no-margin" data-toggle="table"  id="order_list"
                            data-url="api-firebase/get-bootstrap-table-data.php?table=orders"
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
									 <?php if(isset($_SESSION['role']) && $_SESSION['role'] != 'seller'){ ?>
									 <th data-field="sname" data-sortable='true'>V.Name</th>
									 <th data-field="smobile" data-sortable='true'>V.Mob.</th>
									 <?php } ?>
                                    <th data-field="name" data-sortable='true'>U.Name</th>
									<th data-field="mobile" data-sortable='true' data-visible="true">Mob.</th>
									<th data-field="items" data-sortable='true' data-visible="false">Items</th>
									<th data-field="total" data-sortable='true' data-visible="true">Total(<?=$settings['currency']?>)</th>
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
									<th data-field="active_status" data-sortable='true' data-visible='true'>A.Status</th>
									<th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
									<?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller'){ ?>
									<th data-field="operate">Action</th>
									<?php } ?>
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
</script>