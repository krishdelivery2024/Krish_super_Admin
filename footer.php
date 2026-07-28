<!--<footer class="footer">
			<ul class="list-inline">
				<li><?=date('Y');?> © Spider India.</li>
			</ul>
		</footer>-->
	</div>
	<!-- /.main-content -->
</div><!--/#wrapper -->
<?php if($page=="Promo Code"){ ?>
	<div class="modal fade" id='editPromoCodeModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Edit Promo Code</h4>
                        </div>
                        
                        <div class="modal-body">
                          <?php if($permissions['promo_codes']['update']==0){?>
                          <div class="alert alert-danger">You have no permission to update promo code.</div>
                        <?php } ?>
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="promo_code_id" id="promo_code_id" value=''/>
                                <input type='hidden' name="update_promo_code" id="update_promo_code" value='1'/>
                    <div class="form-group">
                      <label for="">Promo Code</label>
                      <input type="text" class="form-control"  name="update_promo" id="update_promo">
                    </div>
                    <div class="form-group">
                      <label for="">Message</label>
                      <input type="text" class="form-control"  name="update_message" id="update_message">
                    </div>
                    <div class="form-group">
                      <label for="">Start Date</label>
                      <input type="date" class="form-control"  name="update_start_date" id="update_start_date">
                    </div>
                    <div class="form-group">
                      <label for="">End Date</label>
                      <input type="date" class="form-control"  name="update_end_date" id="update_end_date">
                    </div>
                    <div class="form-group">
                      <label for="">No. Of Users</label>
                      <input type="text" class="form-control"  name="update_no_of_users" id="update_no_of_users">
                    </div>
                     <div class="form-group">
                      <label for="">Minimum Order Amount</label>
                      <input type="text" class="form-control"  name="update_minimum_order_amount" id="update_minimum_order_amount">
                    </div>
                    <div class="form-group">
                      <label for="">Discount</label>
                      <input type="text" class="form-control"  name="update_discount" id="update_discount">
                    </div>
                    <div class="form-group">
                        <label for="">Discount Type</label>
                        <select name="update_discount_type" id="update_discount_type" class="form-control">
                            <option value="">Select</option>
                            <option value="percentage">Percentage</option>
                            <option value="amount">Amount</option>
                        </select>
                    </div>
                    <div class="form-group">
                      <label for="">Max Discount Amount</label>
                      <input type="text" class="form-control"  name="update_max_discount_amount" id="update_max_discount_amount">
                    </div>
                    <div class="form-group">
                        <label for="">Repeat Usage</label>
                        <select name="update_repeat_usage" id="update_repeat_usage" class="form-control">
                            <option value="">Select</option>
                            <option value="1">Allowed</option>
                            <option value="0">Not Allowed</option>
                        </select>
                    </div>
                    <div class="form-group" id="update_repeat_usage_block" style="display:none">
                      <label for="">No. Of Repeat Usage</label>
                      <input type="text" class="form-control"  name="update_no_of_repeat_usage" id="update_no_of_repeat_usage">
                    </div>
                    <div class="form-group">
                      <label for="exampleInputFile">Image</label>
                      <input type="file" name="category_image" id="category_image" />
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <div id="status" class="btn-group" >
                                <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                <input type="radio" name="status" value="0">  Deactive 
                                </label>
                                <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                <input type="radio" name="status" value="1"> Active
                                </label>
                            </div>
                        </div>
                        </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
<?php if($page=="Delivery Boys"){ ?>
	<div class="modal fade" id='editDeliveryBoyModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Edit Delivery Boy</h4>
                        </div>
                        
                        <div class="modal-body">
                            <?php if($permissions['delivery_boys']['update']==0){?>
                                <div class="alert alert-danger">You have no permission to update delivery boy</div>
                            <?php } ?>
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="delivery_boy_id" id="delivery_boy_id" value=''/>
                                <input type='hidden' name="update_delivery_boy" id="update_delivery_boy" value='1'/>
                                <!-- <input type='hidden' name="image_url" id="image_url" value=''/> -->
                                    
                                    
                                                <div class="form-group">
                                                    <label class="" for="">Name</label>
                                                    <input type="text" id="update_name" name="update_name" class="form-control col-md-7 col-xs-12">
                                                </div>
                                                <div class="form-group">
                                                    <label class="" for="">Mobile</label>
                                                    <input type="text" id="update_mobile" name="update_mobile" class="form-control col-md-7 col-xs-12" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label class="" for="">Password</label><small>( Leave it blank for no change )</small>
                                                    <input type="password" id="update_password" name="update_password" class="form-control col-md-7 col-xs-12">
                                                </div>
                                                <div class="form-group">
                                                    <label class="" for="">Confirm Password</label>
                                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control col-md-7 col-xs-12">
                                                </div>
                                                <div class="form-group">
                                                    <label class="" for="">Address</label>
                                                    <textarea name="update_address" id="update_address" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                                                </div>
                                                <div class="form-group">
                                                   <label for="">Bonus (%)</label>
                                                   <input type="number" class="form-control"  name="update_bonus" id="update_bonus">
                                                </div>
                                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0">  Deactive 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
                        <div class="modal fade" id='fundTransferModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Transfer Fund</h4>
                        </div>
                        <div class="modal-body">
                            <?php if($permissions['delivery_boys']['update']==0){?>
                                <div class="alert alert-danger">You have no permission to update delivery boy</div>
                            <?php } ?>
                            <form id="transfer_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="boy_id" id="boy_id" value=''/>
                                <input type='hidden' name="transfer_fund" id="transfer_fund" value='1'/>
                                        <div class="form-group">
                                    <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="name">Name</label> -->
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label>Name</label><input type="text" name="delivery_boy_name" id="delivery_boy_name" class="form-control" readonly>
                                    </div>
                                    <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="email">Email</label> -->
                                        <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="mobile">Mobile</label> -->
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label>Mobile</label><input type="text" name="delivery_boy_mobile" id="delivery_boy_mobile" class="form-control" readonly>
                                    </div>
                                    
                                    
                                </div>
                                  <!-- <div class="form-group">
                                                    <label class="" for="">Address</label>
                                                    <textarea name="delivery_boy_address" id="delivery_boy_address" style=" min-width:10px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                                                </div> -->
                                <div class="form-group">
                                    <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="account holder">A/C Holder</label> -->
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label>Balance</label><input type="text" name="delivery_boy_balance" id="delivery_boy_balance" class="form-control" readonly>
                                    </div>
                                    <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="account number">A/C Number</label> -->
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label>Transfer Amount</label><input type="text" name="amount" id="amount" class="form-control" onkeyup="validate_amount(this.value);">
                                    </div>
                                    <div class="col-md-12 col-sm-6 col-xs-12">
                                        <label>Message</label><input type="text" name="message" id="message" class="form-control">
                                    </div>
                                        <!-- <label class="control-label col-md-1 col-sm-3 col-xs-12" for="ifsc code">IFSC code</label> -->
                                       
                                    
                                    
                                </div>
                                                

                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="submit_button" class="btn btn-success">Submit</button>
                                    </div>
                                </div>
                            </form>
                            <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="transfer_result"></div></div>
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
<?php if($page=="Payment Requests"){ ?>
	<div class="modal fade" id='editPaymentRequestModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Payment Request</h4>
                        </div>
                        
                        <div class="modal-body">
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="payment_request_id" id="payment_request_id" value=''/>
                                <input type='hidden' name="update_payment_request" id="update_payment_request" value='1'/>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-warning" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0">  Pending 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Success
                                            </label>
                                            <label class="btn btn-danger" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="2"> Cancelled
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="" for="">Remark</label>
                                    <textarea id="update_remarks" name="update_remarks" class="form-control col-md-7 col-xs-12" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
<?php if($page=="Return Requests"){ ?>
	<div class="modal fade" id='editReturnRequestModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Update Return Request</h4>
                        </div>
                        
                        <div class="modal-body">
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="return_request_id" id="return_request_id" value=''/>
                                <input type='hidden' name="order_item_id" id="order_item_id" value=''/>
                                <input type='hidden' name="order_id" id="order_id" value=''/>
                                <input type='hidden' name="update_return_request" id="update_return_request" value='1'/>

                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-7 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-warning" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0">  Pending 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Approved
                                            </label>
                                            <label class="btn btn-danger" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="2"> Cancelled
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="" for="">Remark</label>
                                    <textarea id="update_remarks" name="update_remarks" class="form-control col-md-7 col-xs-12" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
<?php if($page=="Time Slots"){ ?>
	<div class="modal fade" id='editTimeSlotModal' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog modal-md" role="document">
                     
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Edit Time Slot</h4>
                        </div>

                        
                        <div class="modal-body">
                            <?php if($permissions['settings']['update']==0) { ?>
                        <div class="alert alert-danger">You have no permission to update settings</div>
                        <?php }  ?>
                            <div class="box-body">
                            <form id="update_form"  method="POST" action ="public/db-operation.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="time_slot_id" id="time_slot_id" value=''/>
                                <input type='hidden' name="update_time_slot" id="update_time_slot" value='1'/>
                                <!-- <input type='hidden' name="image_url" id="image_url" value=''/> -->
                                <div class="form-group">
                                  <label for="">Title</label>
                                  <input type="text" class="form-control"  name="update_title" id="update_title">
                                </div>
                                <div class="form-group">
                                  <label for="">From Time</label>
                                  <input type="text" class="form-control"  name="update_from_time" id="update_from_time">
                                </div>
                                <div class="form-group">
                                  <label for="">To Time</label>
                                  <input type="text" class="form-control"  name="update_to_time" id="update_to_time">
                                </div>
                                <div class="form-group">
                                  <label for="">Last Order Time</label>
                                  <input type="text" class="form-control"  name="update_last_order_time" id="update_last_order_time">
                                </div>              
                                <div class="form-group">
                                    <label class="control-label col-md-3 col-sm-3 col-xs-12">Status</label>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div id="status" class="btn-group" >
                                            <label class="btn btn-default" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="0">  Deactive 
                                            </label>
                                            <label class="btn btn-primary" data-toggle-class="btn-primary" data-toggle-passive-class="btn-default">
                                            <input type="radio" name="status" value="1"> Active
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="id" name="id">
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Update</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>

<?php if($page=="Products"){ ?>
	<div class="modal fade" id='count_print_barcode' tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
                <div class="modal-dialog" role="document">
                     
                    <div class="modal-content">

                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title" id="myModalLabel">Enter Barcode Count</h4>
                        </div>

                        
                        <div class="modal-body">
                            <div class="box-body">
                            <form id="update_form" target="_blank" method="POST" action ="print_barcode.php" data-parsley-validate class="form-horizontal form-label-left">
                                <input type='hidden' name="barcode_data" id="barcode_data" value=''/>
                                <div class="form-group" style="text-align:center">
                                <svg id="barcode"></svg>
                                </div>
                                <div class="form-group">
                                  <label for="">Barcode Count</label>
                                  <input type="Number" class="form-control"  name="barcode_count" id="barcode_count">
                                </div>
                                     
                                <div class="ln_solid"></div>
                                <div class="form-group">
                                    <div class="col-md-12 col-sm-6 col-xs-12 col-md-offset-3">
                                        <button type="submit" id="update_btn" class="btn btn-success">Print</button>
                                    </div>
                                </div>
                                <div class="form-group">
                      
                                    <div class="row"><div  class="col-md-offset-3 col-md-8" style ="display:none;" id="update_result"></div></div>
                                </div>
                            </form>
                        </div>
                            
                        </div>
                    </div>
                </div>
            </div>
<?php } ?>
	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="dist/script/html5shiv.min.js"></script>
		<script src="dist/script/respond.min.js"></script>
	<![endif]-->
	<!-- 
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="dist/scripts/modernizr.min.js"></script>
	<script src="dist/plugin/bootstrap/js/bootstrap.min.js"></script>
<!--	<script src="dist/plugin/mCustomScrollbar/jquery.mCustomScrollbar.concat.min.js"></script>-->
	<script src="dist/plugin/nprogress/nprogress.js"></script>
	<script src="dist/plugin/sweet-alert/sweetalert.min.js"></script>
	<script src="dist/plugin/waves/waves.min.js"></script>
	<!-- Sparkline Chart -->
	<script src="dist/plugin/chart/sparkline/jquery.sparkline.min.js"></script>
	<script src="dist/scripts/chart.sparkline.init.min.js"></script>

	<!-- Percent Circle -->
	<script src="dist/plugin/percircle/js/percircle.js"></script>

	<!-- Google Chart -->
	<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

	<!-- Chartist Chart -->
	<script src="dist/plugin/chart/chartist/chartist.min.js"></script>
	<script src="dist/scripts/jquery.chartist.init.min.js"></script>

	<!-- FullCalendar -->
	<script src="dist/plugin/moment/moment.js"></script>
	<script src="dist/plugin/fullcalendar/fullcalendar.min.js"></script>
	<script src="dist/plugin/bootstrap-table/bootstrap-table.js"></script>
	<script src="dist/scripts/fullcalendar.init.js"></script>
	
	<script src="dist/plugin/datepicker/js/bootstrap-datepicker.min.js"></script>
	<script src="dist/plugin/select2/js/select2.min.js"></script>
	<script src="dist/plugin/daterangepicker/daterangepicker.js"></script>
	<script src="dist/scripts/main.min.js"></script>
	
<?php if($page=="Create or Edit Users"){ ?>
	         
<script>
  $('#add_form').validate({
    rules:{
    username:"required",
    email:"required",
    password:"required",
    role:"required",
    confirm_password : {
                required:true,
                equalTo : "#password"
            }
    }
  });
</script>
<script>
  $('#add_form').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
  //if( $("#add_form").validate().form() ){
        if(confirm('Are you sure?Want to Add.')){
        $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data:formData,
        beforeSend:function(){$('#submit_btn').html('Please wait..');},
        cache:false,
        contentType: false,
        processData: false,
        success:function(result){
            $('#result').html(result);
            $('#result').show().delay(6000).fadeOut();
            $('#submit_btn').html('Submit');
            $('#add_form')[0].reset();
            // $("#city_ids").val('').trigger('change');
            $('#system-users').bootstrapTable('refresh');    
        }
        });
        }
         // }
    }); 
    $(".edit-system-user").click(function(){
    id=$(this).data("id");
    name=$(this).data("name");
    email=$(this).data("email");
    mobile=$(this).data("mobile");
    $("#update_form").trigger( "reset" );
        $('#user_id').val(id);
        $('#user_name').val(name);
    
});
</script>

<script>
  $('#update_form').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
        if(confirm('Are you sure?Want to update.')){
        $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data:formData,
        beforeSend:function(){$('#update_btn').html('Please wait..');},
        cache:false,
        contentType: false,
        processData: false,
        success:function(result){
            $('#update_result').html(result);
            $('#update_result').show().delay(6000).fadeOut();
            $('#update_btn').html('Submit');
            $('#system-users').bootstrapTable('refresh');
            setTimeout(function() {$('#editSystemUserModal').modal('hide');}, 3000);
        }
        });
          }
    }); 
</script>

<script>
    var changeCheckbox = document.querySelector('#create-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
    if ($(this).is(':checked')) {
        $('#is-create-order').val(1);
    }else{
    		$('#is-create-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
        $('#is-read-order').val(1);
    }else{
    		$('#is-read-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
        if ($(this).is(':checked')) {
        	$('#is-update-order').val(1);
    	}else{
    		$('#is-update-order').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-order').val(1);
    	}else{
    		$('#is-delete-order').val(0);
    	}
    };
</script>
<script>
    var changeCheckbox = document.querySelector('#create-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-category').val(1);
    	}else{
    		$('#is-create-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-read-category').val(1);
    	}else{
    		$('#is-read-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-category').val(1);
    	}else{
    		$('#is-update-category').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-category-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-category').val(1);
    	}else{
    		$('#is-delete-category').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-subcategory').val(1);
    	}else{
    		$('#is-create-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-subcategory').val(1);
    	}else{
    		$('#is-read-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-subcategory').val(1);
    	}else{
    		$('#is-update-subcategory').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-subcategory-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-subcategory').val(1);
    	}else{
    		$('#is-delete-subcategory').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-product').val(1);
    	}else{
    		$('#is-create-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-product').val(1);
    	}else{
    		$('#is-read-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#update-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-update-product').val(1);
    	}else{
    		$('#is-update-product').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-product-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-product').val(1);
    	}else{
    		$('#is-delete-product').val(0);
    	}
    };
    // var switchStatus = false;
</script>
<script>

    var changeCheckbox = document.querySelector('#read-products-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-products-order').val(1);
        }else{
            $('#is-read-products-order').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-products-order-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-products-order').val(1);
        }else{
            $('#is-update-products-order').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-create-home-slider').val(1);
    	}else{
    		$('#is-create-home-slider').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#read-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
        	$('#is-read-home-slider').val(1);
    	}else{
    		$('#is-read-home-slider').val(0);
    	}
    };
    var changeCheckbox = document.querySelector('#delete-home-slider-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
        	$('#is-delete-home-slider').val(1);
    	}else{
    		$('#is-delete-home-slider').val(0);
    	}
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-new-offer').val(1);
        }else{
            $('#is-create-new-offer').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-new-offer').val(1);
        }else{
            $('#is-read-new-offer').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-new-offer-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-new-offer').val(1);
        }else{
            $('#is-delete-new-offer').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-promo').val(1);
        }else{
            $('#is-create-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-promo').val(1);
        }else{
            $('#is-read-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-promo').val(1);
        }else{
            $('#is-update-promo').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-promo-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-promo').val(1);
        }else{
            $('#is-delete-promo').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-featured').val(1);
        }else{
            $('#is-create-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-featured').val(1);
        }else{
            $('#is-read-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-featured').val(1);
        }else{
            $('#is-update-featured').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-featured-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-featured').val(1);
        }else{
            $('#is-delete-featured').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-customers-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-customers').val(1);
        }else{
            $('#is-read-customers').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-payment-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-payment').val(1);
        }else{
            $('#is-read-payment').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-payment-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-payment').val(1);
        }else{
            $('#is-update-payment').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-delivery').val(1);
        }else{
            $('#is-create-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-delivery').val(1);
        }else{
            $('#is-read-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-delivery').val(1);
        }else{
            $('#is-update-delivery').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-delivery-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-delivery').val(1);
        }else{
            $('#is-delete-delivery').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>

    var changeCheckbox = document.querySelector('#read-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-return').val(1);
        }else{
            $('#is-read-return').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-return').val(1);
        }else{
            $('#is-update-return').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-return-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-return').val(1);
        }else{
            $('#is-delete-return').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-notification').val(1);
        }else{
            $('#is-create-notification').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-notification').val(1);
        }else{
            $('#is-read-notification').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-notification-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-delete-notification').val(1);
        }else{
            $('#is-delete-notification').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#read-transaction-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-transaction').val(1);
        }else{
            $('#is-read-transaction').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
  var changeCheckbox = document.querySelector('#read-settings-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-settings').val(1);
        }else{
            $('#is-read-settings').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-settings-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-settings').val(1);
        }else{
            $('#is-update-settings').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-create-location').val(1);
        }else{
            $('#is-create-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-location').val(1);
        }else{
            $('#is-read-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-update-location').val(1);
        }else{
            $('#is-update-location').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-location-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
        if ($(this).is(':checked')) {
            $('#is-delete-location').val(1);
        }else{
            $('#is-delete-location').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-report-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-report').val(1);
        }else{
            $('#is-create-report').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-report-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-report').val(1);
        }else{
            $('#is-read-report').val(0);
        }
    };

    // var switchStatus = false;
</script>
<script>
    var changeCheckbox = document.querySelector('#create-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-create-faq').val(1);
        }else{
            $('#is-create-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#read-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-read-faq').val(1);
        }else{
            $('#is-read-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#update-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-update-faq').val(1);
        }else{
            $('#is-update-faq').val(0);
        }
    };
    var changeCheckbox = document.querySelector('#delete-faq-button');
    var init = new Switchery(changeCheckbox);
    changeCheckbox.onchange = function() {
         // alert(changeCheckbox.checked);
       if ($(this).is(':checked')) {
            $('#is-delete-faq').val(1);
        }else{
            $('#is-delete-faq').val(0);
        }
    };
    // var switchStatus = false;
</script>
<script>
  // var changeCheckbox = document.querySelector('#permission-create-order-button');
  // var init = new Switchery(changeCheckbox);
  // $('.switchery').trigger('click');
  // $('#permission-create-order-button').attr('checked', true);
</script>
<!-- removed  code goes here -->

<script>

window.actionEvents = {
    'click .edit-system-user2': function (e, value, row, index) {
        $("#update_form").trigger( "reset" );
        $('#system_user_id').val(row.id);
        $('#system_user_name').val(row.name);
        $('#system_user_email').val(row.email);
        $('#system_user_mobile').val(row.mobile);
    }
}
window.actionEvents = {
    'click .edit-system-user1': function (e, value, row, index) {
      permissions = row.permissions;
      permissions = JSON.parse(permissions);
      // console.log(permissions);
      $("#update_form").trigger( "reset" );


      $('#system_user_id').val(row.id);

      if(permissions.orders.create==1){
        // $('#permission-create-order-button').attr('checked', true);
        $('#permission-create-order-button').prop('checked', true);
        $('#permission-is-create-order').val(1);
      }else{
        $('#permission-create-order-button').attr('checked', false);
        $('#permission-is-create-order').val(0);
      }
      if(permissions.orders.read==1){
        $('#permission-read-order-button').attr('checked', true);
        $('#permission-is-read-order').val(1);
      }else{
        $('#permission-read-order-button').attr('checked', false);
        $('#permission-is-read-order').val(0);
      }
      if(permissions.orders.update==1){
        $('#permission-update-order-button').attr('checked', true);
        $('#permission-is-update-order').val(1);
      }else{
        $('#permission-update-order-button').attr('checked', false);
        $('#permission-is-update-order').val(0);
      }
      if(permissions.orders.delete==1){
        $('#permission-delete-order-button').attr('checked', true);
        $('#permission-is-delete-order').val(1);
      }else{
        $('#permission-delete-order-button').attr('checked', false);
        $('#permission-is-delete-order').val(0);
      }

      if(permissions.categories.create==1){
        $('#permission-create-category-button').attr('checked', true);
        $('#permission-is-create-category').val(1);
      }else{
        $('#permission-create-category-button').attr('checked', false);
        $('#permission-is-create-category').val(0);
      }
      if(permissions.categories.read==1){
        $('#permission-read-category-button').attr('checked', true);
        $('#permission-is-read-category').val(1);
      }else{
        $('#permission-read-category-button').attr('checked', false);
        $('#permission-is-read-category').val(0);
      }
      if(permissions.categories.update==1){
        $('#permission-update-category-button').attr('checked', true);
        $('#permission-is-update-category').val(1);
      }else{
        $('#permission-update-category-button').attr('checked', false);
        $('#permission-is-update-category').val(0);
      }
      if(permissions.categories.delete==1){
        $('#permission-delete-category-button').attr('checked', true);
        $('#permission-is-delete-category').val(1);
      }else{
        $('#permission-delete-category-button').attr('checked', false);
        $('#permission-is-delete-category').val(0);
      }


      if(permissions.subcategories.create==1){
        $('#permission-create-subcategory-button').attr('checked', true);
        $('#permission-is-create-subcategory').val(1);
      }else{
        $('#permission-create-subcategory-button').attr('checked', false);
        $('#permission-is-create-subcategory').val(0);
      }
      if(permissions.subcategories.read==1){
        $('#permission-read-subcategory-button').attr('checked', true);
        $('#permission-is-read-subcategory').val(1);
      }else{
        $('#permission-read-subcategory-button').attr('checked', false);
        $('#permission-is-read-subcategory').val(0);
      }
      if(permissions.subcategories.update==1){
        $('#permission-update-subcategory-button').attr('checked', true);
        $('#permission-is-update-subcategory').val(1);
      }else{
        $('#permission-update-subcategory-button').attr('checked', false);
        $('#permission-is-update-subcategory').val(0);
      }
      if(permissions.subcategories.delete==1){
        $('#permission-delete-subcategory-button').attr('checked', true);
        $('#permission-is-delete-subcategory').val(1);
      }else{
        $('#permission-delete-subcategory-button').attr('checked', false);
        $('#permission-is-delete-subcategory').val(0);
      }


      if(permissions.products.create==1){
        $('#permission-create-product-button').attr('checked', true);
        $('#permission-is-create-product').val(1);
      }else{
        $('#permission-create-product-button').attr('checked', false);
        $('#permission-is-create-product').val(0);
      }
      if(permissions.products.read==1){
        $('#permission-read-product-button').attr('checked', true);
        $('#permission-is-read-product').val(1);
      }else{
         $('#permission-read-product-button').attr('checked', false);
        $('#permission-is-read-product').val(0);
      }
      if(permissions.products.update==1){
        $('#permission-update-product-button').attr('checked', true);
        $('#permission-is-update-product').val(1);
      }else{
        $('#permission-update-product-button').attr('checked', false);
        $('#permission-is-update-product').val(0);
      }
      if(permissions.products.delete==1){
        $('#permission-delete-product-button').attr('checked', true);
        $('#permission-is-delete-product').val(1);
      }else{
        $('#permission-delete-product-button').attr('checked', false);
        $('#permission-is-delete-product').val(0);
      }


      if(permissions.products_order.read==1){
        $('#permission-read-products-order-button').attr('checked', true);
        $('#permission-is-read-products-order').val(1);
      }else{
        $('#permission-read-products-order-button').attr('checked', false);
        $('#permission-is-read-products-order').val(0);
      }
      if(permissions.products_order.update==1){
        $('#permission-update-products-order-button').attr('checked', true);
        $('#permission-is-update-products-order').val(1);
      }else{
        $('#permission-update-products-order-button').attr('checked', false);
        $('#permission-is-update-products-order').val(0);
      }


      if(permissions.home_sliders.create==1){
        $('#permission-create-home-slider-button').attr('checked', true);
        $('#permission-is-create-home-slider').val(1);
      }else{
        $('#permission-create-home-slider-button').attr('checked', false);
        $('#permission-is-create-home-slider').val(0);
      }
      if(permissions.home_sliders.read==1){
        $('#permission-read-home-slider-button').attr('checked', true);
        $('#permission-is-read-home-slider').val(1);
      }else{
        $('#permission-read-home-slider-button').attr('checked', false);
        $('#permission-is-read-home-slider').val(0);
      }
      if(permissions.home_sliders.delete==1){
        $('#permission-delete-home-slider-button').attr('checked', true);
        $('#permission-is-delete-home-slider').val(1);
      }else{
        $('#permission-delete-home-slider-button').attr('checked', false);
        $('#permission-is-delete-home-slider').val(0);
      }


      if(permissions.new_offers.create==1){
        $('#permission-create-new-offer-button').attr('checked', true);
        $('#permission-is-create-new-offer').val(1);
      }else{
        $('#permission-create-new-offer-button').attr('checked', false);
        $('#permission-is-create-new-offer').val(0);
      }
      if(permissions.new_offers.read==1){
        $('#permission-read-new-offer-button').attr('checked', true);
        $('#permission-is-read-new-offer').val(1);
      }else{
        $('#permission-read-new-offer-button').attr('checked', false);
        $('#permission-is-read-new-offer').val(0);
      }
      if(permissions.new_offers.delete==1){
        $('#permission-delete-new-offer-button').attr('checked', true);
        $('#permission-is-delete-new-offer').val(1);
      }else{
        $('#permission-delete-new-offer-button').attr('checked', false);
        $('#permission-is-delete-new-offer').val(0);
      }


      if(permissions.promo_codes.create==1){
        $('#permission-create-promo-button').attr('checked', true);
        $('#permission-is-create-promo').val(1);
      }else{
        $('#permission-create-promo-button').attr('checked', false);
        $('#permission-is-create-promo').val(0);
      }
      if(permissions.promo_codes.read==1){
        $('#permission-read-promo-button').attr('checked', true);
        $('#permission-is-read-promo').val(1);
      }else{
        $('#permission-read-promo-button').attr('checked', false);
        $('#permission-is-read-promo').val(0);
      }
      if(permissions.promo_codes.update==1){
        $('#permission-update-promo-button').attr('checked', true);
        $('#permission-is-update-promo').val(1);
      }else{
        $('#permission-update-promo-button').attr('checked', false);
        $('#permission-is-update-promo').val(0);
      }
      if(permissions.promo_codes.delete==1){
        $('#permission-delete-promo-button').attr('checked', true);
        $('#permission-is-delete-promo').val(1);
      }else{
        $('#permission-delete-promo-button').attr('checked', false);
        $('#permission-is-delete-promo').val(0);
      }

      if(permissions.featured.create==1){
        $('#permission-create-featured-button').attr('checked', true);
        $('#permission-is-create-featured').val(1);
      }else{
        $('#permission-create-featured-button').attr('checked', false);
        $('#permission-is-create-featured').val(0);
      }
      if(permissions.featured.read==1){
        $('#permission-read-featured-button').attr('checked', true);
        $('#permission-is-read-featured').val(1);
      }else{
        $('#permission-read-featured-button').attr('checked', false);
        $('#permission-is-read-featured').val(0);
      }
      if(permissions.featured.update==1){
        $('#permission-update-featured-button').attr('checked', true);
        $('#permission-is-update-featured').val(1);
      }else{
        $('#permission-update-featured-button').attr('checked', false);
        $('#permission-is-update-featured').val(0);
      }
      if(permissions.featured.delete==1){
        $('#permission-delete-featured-button').attr('checked', true);
        $('#permission-is-delete-featured').val(1);
      }else{
        $('#permission-delete-featured-button').attr('checked', false);
        $('#permission-is-delete-featured').val(0);
      }


      if(permissions.customers.read==1){
        $('#permission-read-customers-button').attr('checked', true);
        $('#permission-is-read-customers').val(1);
      }else{
        $('#permission-read-customers-button').attr('checked', false);
        $('#permission-is-read-customers').val(0);
      }

      if(permissions.payment.read==1){
        $('#permission-read-payment-button').attr('checked', true);
        $('#permission-is-read-payment').val(1);
      }else{
        $('#permission-read-payment-button').attr('checked', false);
        $('#permission-is-read-payment').val(0);
      }
      if(permissions.payment.update==1){
        $('#permission-update-payment-button').attr('checked', true);
        $('#permission-is-update-payment').val(1);
      }else{
        $('#permission-update-payment-button').attr('checked', false);
        $('#permission-is-update-payment').val(0);
      }


      if(permissions.delivery_boys.create==1){
        $('#permission-create-delivery-button').attr('checked', true);
        $('#permission-is-create-delivery').val(1);
      }else{
        $('#permission-create-delivery-button').attr('checked', false);
        $('#permission-is-create-delivery').val(0);
      }
      if(permissions.delivery_boys.read==1){
        $('#permission-read-delivery-button').attr('checked', true);
        $('#permission-is-read-delivery').val(1);
      }else{
        $('#permission-read-delivery-button').attr('checked', false);
        $('#permission-is-read-delivery').val(0);
      }
      if(permissions.delivery_boys.update==1){
        $('#permission-update-delivery-button').attr('checked', true);
        $('#permission-is-update-delivery').val(1);
      }else{
        $('#permission-update-delivery-button').attr('checked', false);
        $('#permission-is-update-delivery').val(0);
      }
      if(permissions.delivery_boys.delete==1){
        $('#permission-delete-delivery-button').attr('checked', true);
        $('#permission-is-delete-delivery').val(1);
      }else{
        $('#permission-delete-delivery-button').attr('checked', false);
        $('#permission-is-delete-delivery').val(0);
      }


      if(permissions.return_requests.read==1){
        $('#permission-read-return-button').attr('checked', true);
        $('#permission-is-read-return').val(1);
      }else{
        $('#permission-read-return-button').attr('checked', false);
        $('#permission-is-read-return').val(0);
      }
      if(permissions.return_requests.update==1){
        $('#permission-update-return-button').attr('checked', true);
        $('#permission-is-update-return').val(1);
      }else{
        $('#permission-update-return-button').attr('checked', false);
        $('#permission-is-update-return').val(0);
      }
      if(permissions.return_requests.delete==1){
        $('#permission-delete-return-button').attr('checked', true);
        $('#permission-is-delete-return').val(1);
      }else{
        $('#permission-delete-return-button').attr('checked', false);
        $('#permission-is-delete-return').val(0);
      }

      if(permissions.notifications.create==1){
        $('#permission-create-notification-button').attr('checked', true);
        $('#permission-is-create-notification').val(1);
      }else{
        $('#permission-create-notification-button').attr('checked', false);
        $('#permission-is-create-notification').val(0);
      }
      if(permissions.notifications.read==1){
        $('#permission-read-notification-button').attr('checked', true);
        $('#permission-is-read-notification').val(1);
      }else{
        $('#permission-read-notification-button').attr('checked', false);
        $('#permission-is-read-notification').val(0);
      }
      if(permissions.notifications.delete==1){
        $('#permission-delete-notification-button').attr('checked', true);
        $('#permission-is-delete-notification').val(1);
      }else{
        $('#permission-delete-notification-button').attr('checked', false);
        $('#permission-is-delete-notification').val(0);
      }

      if(permissions.transactions.read==1){
        $('#permission-read-transaction-button').attr('checked', true);
        $('#permission-is-read-transaction').val(1);
      }else{
        $('#permission-read-transaction-button').attr('checked', false);
        $('#permission-is-read-transaction').val(0);
      }


      if(permissions.settings.read==1){
        $('#permission-read-settings-button').attr('checked', true);
        $('#permission-is-read-settings').val(1);
      }else{
        $('#permission-read-settings-button').attr('checked', false);
        $('#permission-is-read-settings').val(0);
      }

      if(permissions.settings.update==1){
        $('#permission-update-settings-button').attr('checked', true);
        $('#permission-is-update-settings').val(1);
      }else{
        $('#permission-update-settings-button').attr('checked', false);
        $('#permission-is-update-settings').val(0);
      }

      if(permissions.locations.create==1){
        $('#permission-create-location-button').attr('checked', true);
        $('#permission-is-create-location').val(1);
      }else{
        $('#permission-create-location-button').attr('checked', false);
        $('#permission-is-create-location').val(0);
      }

      if(permissions.locations.read==1){
        $('#permission-read-location-button').attr('checked', true);
        $('#permission-is-read-location').val(1);
      }else{
        $('#permission-read-location-button').attr('checked', false);
        $('#permission-is-read-location').val(0);
      }
      if(permissions.locations.update==1){
        $('#permission-update-location-button').attr('checked', true);
        $('#permission-is-update-location').val(1);
      }else{
        $('#permission-update-location-button').attr('checked', false);
        $('#permission-is-update-location').val(0);
      }

      if(permissions.locations.delete==1){
        $('#permission-delete-location-button').attr('checked', true);
        $('#permission-is-delete-location').val(1);
      }else{
        $('#permission-delete-location-button').attr('checked', false);
        $('#permission-is-delete-location').val(0);
      }

      if(permissions.reports.create==1){
        $('#permission-create-report-button').attr('checked', true);
        $('#permission-is-create-report').val(1);
      }else{
        $('#permission-create-report-button').attr('checked', false);
        $('#permission-is-create-report').val(0);
      }

      if(permissions.reports.read==1){
        $('#permission-read-report-button').attr('checked', true);
        $('#permission-is-read-report').val(1);
      }else{
        $('#permission-read-report-button').attr('checked', false);
        $('#permission-is-read-report').val(0);
      }


      if(permissions.faqs.create==1){
        
        $('#permission-create-faq-button').attr('checked', true);
        $('#permission-is-create-faq').val(1);
      }else{
        $('#permission-create-faq-button').attr('checked', false);
        $('#permission-is-create-faq').val(0);
      }

      if(permissions.faqs.read==1){
        $('#permission-read-faq-button').attr('checked', true);
        $('#permission-is-read-faq').val(1);
      }else{
        $('#permission-read-faq-button').attr('checked', false);
        $('#permission-is-read-faq').val(0);
      }

      if(permissions.faqs.update==1){
        $('#permission-update-faq-button').attr('checked', true);
        $('#permission-is-update-faq').val(1);
      }else{
        $('#permission-update-faq-button').attr('checked', false);
        $('#permission-is-update-faq').val(0);
      }

      if(permissions.faqs.delete==1){
        $('#permission-delete-faq-button').attr('checked', true);
        $('#permission-is-delete-faq').val(1);
      }else{
        $('#permission-delete-faq-button').attr('checked', false);
        $('#permission-is-delete-faq').val(0);
      }
    }
}
    //   var changeCheckbox = document.querySelector('#permission-create-order-button');
    // var init = new Switchery(changeCheckbox);
    // changeCheckbox.onchange = function() {
    //     if ($(this).is(':checked')) {
    //         $('#permission-is-create-order').val(1);
    //     }else{
    //         $('#permission-is-create-order').val(0);
    //     }
    // };
      $('#permission-create-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-order').val(1);
          }else{
              $('#permission-is-create-order').val(0);
          }
      });
      $('#permission-read-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-order').val(1);
          }else{
              $('#permission-is-read-order').val(0);
          }
      });
      $('#permission-update-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-order').val(1);
          }else{
              $('#permission-is-update-order').val(0);
          }
      });
      $('#permission-delete-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-order').val(1);
          }else{
              $('#permission-is-delete-order').val(0);
          }
      });

      $('#permission-create-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-category').val(1);
          }else{
              $('#permission-is-create-category').val(0);
          }
      });
      $('#permission-read-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-category').val(1);
          }else{
              $('#permission-is-read-category').val(0);
          }
      });
      $('#permission-update-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-category').val(1);
          }else{
              $('#permission-is-update-category').val(0);
          }
      });
      $('#permission-delete-category-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-category').val(1);
          }else{
              $('#permission-is-delete-category').val(0);
          }
      });

      $('#permission-create-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-subcategory').val(1);
          }else{
              $('#permission-is-create-subcategory').val(0);
          }
      });
      $('#permission-read-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-subcategory').val(1);
          }else{
              $('#permission-is-read-subcategory').val(0);
          }
      });
      $('#permission-update-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-subcategory').val(1);
          }else{
              $('#permission-is-update-subcategory').val(0);
          }
      });
      $('#permission-delete-subcategory-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-subcategory').val(1);
          }else{
              $('#permission-is-delete-subcategory').val(0);
          }
      });


      $('#permission-create-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-product').val(1);
          }else{
              $('#permission-is-create-product').val(0);
          }
      });
      $('#permission-read-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-product').val(1);
          }else{
              $('#permission-is-read-product').val(0);
          }
      });
      $('#permission-update-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-product').val(1);
          }else{
              $('#permission-is-update-product').val(0);
          }
      });
      $('#permission-delete-product-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-product').val(1);
          }else{
              $('#permission-is-delete-product').val(0);
          }
      });


      $('#permission-read-products-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-products-order').val(1);
          }else{
              $('#permission-is-read-products-order').val(0);
          }
      });
      $('#permission-update-products-order-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-products-order').val(1);
          }else{
              $('#permission-is-update-products-order').val(0);
          }
      });


      $('#permission-create-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-home-slider').val(1);
          }else{
              $('#permission-is-create-home-slider').val(0);
          }
      });
      $('#permission-read-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-home-slider').val(1);
          }else{
              $('#permission-is-read-home-slider').val(0);
          }
      });
      $('#permission-update-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-home-slider').val(1);
          }else{
              $('#permission-is-update-home-slider').val(0);
          }
      });
      $('#permission-delete-home-slider-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-home-slider').val(1);
          }else{
              $('#permission-is-delete-home-slider').val(0);
          }
      });


      $('#permission-create-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-new-offer').val(1);
          }else{
              $('#permission-is-create-new-offer').val(0);
          }
      });
      $('#permission-read-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-new-offer').val(1);
          }else{
              $('#permission-is-read-new-offer').val(0);
          }
      });
      $('#permission-update-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-new-offer').val(1);
          }else{
              $('#permission-is-update-new-offer').val(0);
          }
      });
      $('#permission-delete-new-offer-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-new-offer').val(1);
          }else{
              $('#permission-is-delete-new-offer').val(0);
          }
      });


      $('#permission-create-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-promo').val(1);
          }else{
              $('#permission-is-create-promo').val(0);
          }
      });
      $('#permission-read-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-promo').val(1);
          }else{
              $('#permission-is-read-promo').val(0);
          }
      });
      $('#permission-update-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-promo').val(1);
          }else{
              $('#permission-is-update-promo').val(0);
          }
      });
      $('#permission-delete-promo-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-promo').val(1);
          }else{
              $('#permission-is-delete-promo').val(0);
          }
      });


      $('#permission-create-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-featured').val(1);
          }else{
              $('#permission-is-create-featured').val(0);
          }
      });
      $('#permission-read-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-featured').val(1);
          }else{
              $('#permission-is-read-featured').val(0);
          }
      });
      $('#permission-update-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-featured').val(1);
          }else{
              $('#permission-is-update-featured').val(0);
          }
      });
      $('#permission-delete-featured-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-featured').val(1);
          }else{
              $('#permission-is-delete-featured').val(0);
          }
      });

      $('#permission-read-customers-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-customers').val(1);
          }else{
              $('#permission-is-read-customers').val(0);
          }
      });

      $('#permission-read-payment-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-payment').val(1);
          }else{
              $('#permission-is-read-payment').val(0);
          }
      });

      $('#permission-update-payment-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-payment').val(1);
          }else{
              $('#permission-is-update-payment').val(0);
          }
      });


       $('#permission-create-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-delivery').val(1);
          }else{
              $('#permission-is-create-delivery').val(0);
          }
      });
      $('#permission-read-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-delivery').val(1);
          }else{
              $('#permission-is-read-delivery').val(0);
          }
      });
      $('#permission-update-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-delivery').val(1);
          }else{
              $('#permission-is-update-delivery').val(0);
          }
      });
      $('#permission-delete-delivery-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-delivery').val(1);
          }else{
              $('#permission-is-delete-delivery').val(0);
          }
      });


      $('#permission-read-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-return').val(1);
          }else{
              $('#permission-is-read-return').val(0);
          }
      });
      $('#permission-update-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-return').val(1);
          }else{
              $('#permission-is-update-return').val(0);
          }
      });
      $('#permission-delete-return-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-return').val(1);
          }else{
              $('#permission-is-delete-return').val(0);
          }
      });


      $('#permission-create-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-notification').val(1);
          }else{
              $('#permission-is-create-notification').val(0);
          }
      });
      $('#permission-read-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-notification').val(1);
          }else{
              $('#permission-is-read-notification').val(0);
          }
      });
      $('#permission-delete-notification-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-notification').val(1);
          }else{
              $('#permission-is-delete-notification').val(0);
          }
      });

      $('#permission-read-transaction-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-transaction').val(1);
          }else{
              $('#permission-is-read-transaction').val(0);
          }
      });


      $('#permission-read-settings-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-settings').val(1);
          }else{
              $('#permission-is-read-settings').val(0);
          }
      });

      $('#permission-update-settings-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-settings').val(1);
          }else{
              $('#permission-is-update-settings').val(0);
          }
      });

      $('#permission-create-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-location').val(1);
          }else{
              $('#permission-is-create-location').val(0);
          }
      });

      $('#permission-read-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-location').val(1);
          }else{
              $('#permission-is-read-location').val(0);
          }
      });
      $('#permission-update-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-location').val(1);
          }else{
              $('#permission-is-update-location').val(0);
          }
      });

      $('#permission-delete-location-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-location').val(1);
          }else{
              $('#permission-is-delete-location').val(0);
          }
      });


      $('#permission-create-report-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-report').val(1);
          }else{
              $('#permission-is-create-report').val(0);
          }
      });

      $('#permission-read-report-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-report').val(1);
          }else{
              $('#permission-is-read-report').val(0);
          }
      });


      $('#permission-create-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-create-faq').val(1);
          }else{
              $('#permission-is-create-faq').val(0);
          }
      });

      $('#permission-read-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-read-faq').val(1);
          }else{
              $('#permission-is-read-faq').val(0);
          }
      });
      $('#permission-update-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-update-faq').val(1);
          }else{
              $('#permission-is-update-faq').val(0);
          }
      });

      $('#permission-delete-faq-button').change(function () {
        if ($(this).is(':checked')) {
              $('#permission-is-delete-faq').val(1);
          }else{
              $('#permission-is-delete-faq').val(0);
          }
      });
</script>
<script>
    $(document).on('click','.delete-system-user',function(){
          if(confirm('Are you sure? Want to delete system user.')){
              
              id = $(this).data("id");
          
              // image = $(this).data("image");
              $.ajax({
                  url : 'public/db-operation.php',
                  type: "get",
                  data: 'id='+id+'&delete_system_user=1',
                  success: function(result){
                      if(result==0){
                          $('#system-users').bootstrapTable('refresh');
                      }
                      else{
                          alert('Error! System user could not be deleted.');
                      }
                      
                  }
              });
          }
      });
</script>
<script>
  /*$('#city_ids').select2({
    width: 'element',
    placeholder: 'type in city name to search',
    // minimumInputLength: 3,
     ajax: {
      url: 'api/get-bootstrap-table-data.php',
      dataType: 'json',
      type: "GET",
      quietMillis: 1,
      data:function(params){
        return{
          products_list: 1,
          name:params.term,
        };
      },
      processResults:function(data) {
        // alert(JSON.stringify(data));
        return {
          results: data
        };
      },
    } 
  });*/
</script>
<?php } ?>
<?php if($page=="Time Slots"){?>
	  <script>
      $('#add_form').validate({
        rules:{
        title:"required",
        from_time:"required",
        to_time:"required",
        last_order_time:"required",
        status:"required",
        }
      });
  </script>
    <script>
      $('#update_form').validate({
        rules:{
        update_title:"required",
        update_from_time:"required",
        update_to_time:"required",
        update_last_order_time:"required",

        }
      });
  </script>
    <script>
      $('#add_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
     // if( $("#add_form").validate().form() ){
            if(confirm('Are you sure?Want to Add Time Slot')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#submit_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result);
                $('#result').show().delay(6000).fadeOut();
                $('#submit_btn').html('Submit');
                $('#add_form')[0].reset();
                $('#time-slots').bootstrapTable('refresh');
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            }
           //   }
        }); 
  </script>
  <script>
      $('#update_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
      //if( $("#update_form").validate().form() ){
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#update_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#update_result').html(result);
                $('#update_result').show().delay(6000).fadeOut();
                $('#update_btn').html('Update');
                $('#update_form')[0].reset();
                $('#time-slots').bootstrapTable('refresh');
                setTimeout(function() {$('#editTimeSlotModal').modal('hide');}, 3000);
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            //}
              //}
        }); 
  </script>
  <script>
    window.actionEvents = {
        'click .edit-time-slot': function (e, value, row, index) {
            // alert('You click remove icon, row: ' + JSON.stringify(row));
            // alert(row.title);
            $("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Deactive')
                $("input[name=status][value=0]").prop('checked', true);
            $('#time_slot_id').val(row.id);
            $('#update_title').val(row.title);
            $('#update_from_time').val(row.from_time);
            $('#update_to_time').val(row.to_time);
            $('#update_last_order_time').val(row.last_order_time);
        }
    }
</script>
<script>
    $(document).on('click','.delete-time-slot',function(){
        if(confirm('Are you sure? Want to delete time slot.')){
            
            id = $(this).data("id");
        
            // image = $(this).data("image");
            $.ajax({
                url : 'public/db-operation.php',
                type: "get",
                data: 'id='+id+'&delete_time_slot=1',
                success: function(result){
                    if(result==0){
                        $('#time-slots').bootstrapTable('refresh');
                    }
                    if(result==1){
                        alert('Error! Time slot could not be deleted.');
                    }
                    if(result==2){
                        alert('You have no permission to delete time slot');
                    }
                    
                    
                }
            });
        }
    });
</script>
<?php } ?>
<?php if($page=="Store Settings"){?>
	            <script>
                 $('#system_timezone').on('change',function(e){
                gmt = $(this).find(':selected').data('gmt');
                $('#system_timezone_gmt').val(gmt);
                
            });
            
            $('#system_configurations_form').validate({
            	rules:{
				currency:"required",
				}
            });

            $('#system_configurations_form').on('submit',function(e){
                e.preventDefault();
                var formData = new FormData(this);
             //   if($("#system_configurations_form").validate().form()){
                    $.ajax({
                    type:'POST',
                    url:'public/db-operation.php',
                    data:formData,
                    beforeSend:function(){$('#btn_update').html('Please wait..');},
                    cache:false,
                    contentType: false,
                    processData: false,
                    success:function(result){
                        $('#result').html(result);
                        $('#result').show().delay(5000).fadeOut();
                        $('#btn_update').html('Save Settings');
                        // $('#system_configurations_form')[0].reset();
                        // location.reload();
                    }
                    });
             //   }
            }); 
            </script>
            
            
           
            <script>
                var changeCheckbox = document.querySelector('#version-system-button');
                var init = new Switchery(changeCheckbox);
                changeCheckbox.onchange = function() {
                if ($(this).is(':checked')) {
                    $('#is-version-system-on').val(1);
                }else{
                		$('#is-version-system-on').val(0);
                	}
                };
                var changeCheckbox = document.querySelector('#refer-earn-system-button');
                var init = new Switchery(changeCheckbox);
                changeCheckbox.onchange = function() {
                    if ($(this).is(':checked')) {
                    $('#is-refer-earn-on').val(1);
                }else{
                		$('#is-refer-earn-on').val(0);
                	}
                };
    
            </script>
<?php } ?>
<?php if($page=="Return Requests"){?>
	  <script>
      $('#update_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
     // if( $("#update_form").validate().form() ){
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#update_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#update_result').html(result);
                $('#update_result').show().delay(6000).fadeOut();
                $('#update_btn').html('Update');
                $('#update_form')[0].reset();
                $('#return-requests').bootstrapTable('refresh');
                setTimeout(function() {$('#editReturnRequestModal').modal('hide');}, 3000);
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            //}
            //  }
        }); 
  </script>
  <script>
    window.actionEvents = {
        'click .edit-return-request': function (e, value, row, index) {
            //alert('You click remove icon, row: ' + JSON.stringify(row));
            //$("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Pending')
                $("input[name=status][value=0]").prop('checked', true);
            if($(row.status).text() == 'Approved')
                $("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Cancelled')
                $("input[name=status][value=2]").prop('checked', true);
            $('#return_request_id').val(row.id);
            $('#order_item_id').val(row.order_item_id);
            $('#order_id').val(row.order_id);
            $('#update_remarks').val(row.remarks);
        }
    }
    </script>
      <script>
      $(document).on('click','.delete-return-request',function(){
            if(confirm('Are you sure? Want to delete return request.')){
                
                id = $(this).data("id");
            
                // image = $(this).data("image");
                $.ajax({
                    url : 'public/db-operation.php',
                    type: "get",
                    data: 'id='+id+'&delete_return_request=1',
                    success: function(result){
                        if(result==0){
                            $('#return-requests').bootstrapTable('refresh');
                        }
                        if(result==2){
                           alert('You have no permission to delete return request'); 
                        }
                        if(result==1){
                           alert('Error! Return request could not be deleted.'); 
                        }
                        
                    }
                });
            }
        });
  </script>

<?php } ?>
<?php if($page=="Promo Code"){?>
	   <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
  <script>
      $('#add_form').validate({
        rules:{
        promo_code:"required",
        message:"required",
        start_date:"required",
        end_date:"required",
        no_of_users:"required",
        minimum_order_amount:"required",
        max_discount_amount:"required",
        discount:"required",
        discount_type:"required",
        repeat_usage:"required",
        status:"required",

        }
      });
  </script>
    <script>
      $('#update_form').validate({
        rules:{
        update_promo:"required",
        update_message:"required",
        update_start_date:"required",
        update_end_date:"required",
        update_no_of_users:"required",
        update_minimum_order_amount:"required",
        update_discount:"required",
        update_discount_type:"required",
        update_repeat_usage:"required",

        }
      });
  </script>
    <script>
      $('#add_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
    //  if( $("#add_form").validate().form() ){
            if(confirm('Are you sure?Want to Add Promo Code')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#submit_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result);
                $('#result').show().delay(6000).fadeOut();
                $('#submit_btn').html('Submit');
                $('#add_form')[0].reset();
                $('#promo-codes').bootstrapTable('refresh');
            }
            });
            }
      //        }
        }); 
  </script>
  <script>
      $('#update_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
     // if( $("#update_form").validate().form() ){
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#update_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#update_result').html(result);
                $('#update_result').show().delay(6000).fadeOut();
                $('#update_btn').html('Update');
                $('#update_form')[0].reset();
                $('#promo-codes').bootstrapTable('refresh');
                setTimeout(function() {$('#editPromoCodeModal').modal('hide');}, 3000);
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            //}
     //         }
        }); 
  </script>
  <script>
            window.actionEvents = {
               
                'click .edit-promo-code': function (e, value, row, index) {
                    //alert('You click remove icon, row: ' + JSON.stringify(row));
                    $("input[name=status][value=1]").prop('checked', true);
                    if($(row.status).text() == 'Deactive')
                        $("input[name=status][value=0]").prop('checked', true);
                    $('#promo_code_id').val(row.id);
                    $('#update_promo').val(row.promo_code);
                    $('#update_message').val(row.message);
                    $('#update_start_date').val(row.start_date);
                    $('#update_end_date').val(row.end_date);
                    $('#update_no_of_users').val(row.no_of_users);
                    $('#update_minimum_order_amount').val(row.minimum_order_amount);
                    $('#update_discount').val(row.discount);
                    $('#update_discount_type').val(row.discount_type);
                    $('#update_max_discount_amount').val(row.max_discount_amount);
                    if(row.repeat_usage=='Allowed'){
                        $('#update_repeat_usage').val(1);
                    }else{
                        $('#update_repeat_usage').val(0);
                    }
                    if(row.repeat_usage=='Allowed'){
                        $('#update_repeat_usage_block').show();
                        $('#update_no_of_repeat_usage').val(row.no_of_repeat_usage);
                      
                    }
                }
            }
        </script>
   <script>
      $(document).on('click','.delete-promo-code',function(){
            if(confirm('Are you sure? Want to delete promo code.')){
                
                id = $(this).data("id");
            
                // image = $(this).data("image");
                $.ajax({
                    url : 'public/db-operation.php',
                    type: "get",
                    data: 'id='+id+'&delete_promo_code=1',
                    success: function(result){
                        if(result==0){
                            $('#promo-codes').bootstrapTable('refresh');
                        }
                        if(result==2){
                           alert('You have no permission to delete promo code'); 
                        }
                        if(result==1){
                           alert('Error! Promo code could not be deleted.'); 
                        }
                        
                        
                    }
                });
            }
        });
  </script>
  <script>
      	$("#repeat_usage").change(function() {
		repeat_usage = $("#repeat_usage").val();
		if(repeat_usage == 1){
		    $("#repeat_usage_block").show();
		}else{
		    $("#repeat_usage_block").hide();
		}

	});
  </script>
    <script>
      	$("#update_repeat_usage").change(function() {
		update_repeat_usage = $("#update_repeat_usage").val();
		if(update_repeat_usage == 1){
		    $("#update_repeat_usage_block").show();
		}else{
		    $("#update_repeat_usage_block").hide();
		}

	});
  </script>
<?php } ?>
<?php if($page=="Products"){?>
<script>
function print_barcode(barcode){
    JsBarcode("#barcode", barcode, {
    height:50
});
 $("#barcode_data").val(barcode);
// $("#barcode_image").attr("src","barcode/barcode.php?codetype=Code39&size=40&text="+barcode+"&print=true");
 $("#count_print_barcode").modal('show');
}
function queryParams(p){
    return {
        "category_id": $('#category_id').val(),
        limit:p.limit,
        sort:p.sort,
        order:p.order,
        offset:p.offset,
        search:p.search
    };
}
</script>
<script>
$('#category_id').on('change',function(){
    id = $('#category_id').val();
    $('#products_table').bootstrapTable('refresh');
});
document.addEventListener("DOMContentLoaded", function(){
    JsBarcode(".barcode").init();
});
</script>
<?php } ?>
<?php if($page=="Payment Requests"){?>
	  <script>
      $('#add_form').validate({
        rules:{
        name:"required",
        mobile:"required",
        password:"required",
        address:"required",
        confirm_password : {
                    required:true,
                    equalTo : "#password"
                }
        }
      });
  </script>
    <script>
      $('#update_form').validate({
        rules:{
        update_name:"required",
        update_mobile:"required",
        update_address:"required",
        confirm_password : {
                    equalTo : "#update_password"
                }
        }
      });
  </script>
    <script>
      $('#add_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
     // if( $("#add_form").validate().form() ){
            if(confirm('Are you sure?Want to Add Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#submit_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result);
                $('#result').show().delay(6000).fadeOut();
                $('#submit_btn').html('Submit');
                $('#add_form')[0].reset();
                $('#delivery-boys').bootstrapTable('refresh');
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            }
           //   }
        }); 
  </script>
  <script>
      $('#update_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
    //  if( $("#update_form").validate().form() ){
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#update_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#update_result').html(result);
                $('#update_result').show().delay(6000).fadeOut();
                $('#update_btn').html('Update');
                $('#update_form')[0].reset();
                $('#payment-requests').bootstrapTable('refresh');
                setTimeout(function() {$('#editPaymentRequestModal').modal('hide');}, 3000);
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            //}
             // }
        }); 
  </script>
  <script>
    window.actionEvents = {
        'click .edit-payment-request': function (e, value, row, index) {
            //alert('You click remove icon, row: ' + JSON.stringify(row));
            //$("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Pending')
                $("input[name=status][value=0]").prop('checked', true);
            if($(row.status).text() == 'Success')
                $("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Cancelled')
                $("input[name=status][value=2]").prop('checked', true);
            $('#payment_request_id').val(row.id);
            $('#update_remarks').val(row.remarks);
        }
    }
    </script>
<?php } ?>
<?php if($page=="Orders"){?>
	<script>
    $('#filter_order').on('change', function() {
        status = $('#filter_order').val();
        $('#filter_order_status').val(status);
        $('#order_list').bootstrapTable('refresh'); 
        
	});
	
	 $('#deliver_by').on('change', function() {
        $('#order_list').bootstrapTable('refresh'); 
	});
</script>
<script>
  $(document).ready(function(){
   $('#date').daterangepicker({
		"autoApply": true,
		"showDropdowns": true,
        "alwaysShowCalendars":true,
		"startDate":moment(),
		"endDate":moment(),
		"locale": {
			"format": "DD/MM/YYYY",
			"separator": " - "
		},
	});

    $('#date').on('apply.daterangepicker', function(ev, picker) {
		var drp = $('#date').data('daterangepicker');
		$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
		$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
	});
	$('#date').on('apply.daterangepicker', function(ev, picker) {
		var drp = $('#date').data('daterangepicker');
		$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
		$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
        $('#order_list').bootstrapTable('refresh');
	});
	$('#filter_order').on('change',function(){
	 //   alert('change');
	    $('#order_list').bootstrapTable('refresh');
	});
  });
  function queryParams_1(p){
			return {
				"start_date": $('#start_date').val(),
				"end_date": $('#end_date').val(),
				"filter_order": $('#filter_order_status').val(),
				"deliver_by": $('#deliver_by').val(),
				limit:p.limit,
				sort:p.sort,
				order:p.order,
				offset:p.offset,
				search:p.search
			};
		}

        function totalFormatter() {
    return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>'
  }

  function orderFormatter(data) {
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.length + ' Order'
  }
  var total =0;
  function priceFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+ ' Rs.<span>'
  }
  function delivery_chargeFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+' Rs.</span>'
  }
  
  function final_totalFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+ ' Rs.<span>'
  }
</script>
<?php } ?>

<?php if($page=="Incomplete Orders"){?>

<script>
  $(document).ready(function(){
    	$('#date').daterangepicker({
				"autoApply": true,
				"showDropdowns": true,
                "alwaysShowCalendars":true,
				"startDate":moment(),
				"endDate":moment(),
				"locale": {
					"format": "DD/MM/YYYY",
					"separator": " - "
				},
			});

            $('#date').on('apply.daterangepicker', function(ev, picker) {
				var drp = $('#date').data('daterangepicker');
				$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
				$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
			});
        	$('#date').on('apply.daterangepicker', function(ev, picker) {
				var drp = $('#date').data('daterangepicker');
				$('#start_date').val(drp.startDate.format('YYYY-MM-DD'));
				$('#end_date').val(drp.endDate.format('YYYY-MM-DD'));
                $('#order_list').bootstrapTable('refresh');
			});
  });
  function queryParams_1(p){
			return {
				"start_date": $('#start_date').val(),
				"end_date": $('#end_date').val(),
				limit:p.limit,
				sort:p.sort,
				order:p.order,
				offset:p.offset,
				search:p.search
			};
		}

        function totalFormatter() {
    return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>'
  }

  function orderFormatter(data) {
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.length + ' Order'
  }
  var total =0;
  function priceFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+ ' Rs.<span>'
  }
  function delivery_chargeFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+' Rs.</span>'
  }
  
  function final_totalFormatter(data) {
    // return JSON.stringify(data);
    var field = this.field
    return '<span style="color:green;font-weight:bold;font-size:large;">'+ data.map(function (row) {
     return +row[field]
    })
    .reduce(function (sum, i) {
      return sum + i
    }, 0)+ ' Rs.<span>'
  }
</script>
<?php } ?>

<?php if($page=="Order Details"){?>
	<!-- <script>
    var total_amount=$('#total_amount').val();
    $("#final_total").val(total_amount);
</script> -->
<script>
    
$(document).on('click','.update_order_status',function(e){
    e.preventDefault();
        var update_permission = '<?=$update_order_permission;?>';
        if(update_permission==0){
            alert('Sorry! you have no permission to update orders.');
            window.location.reload();
            return false;
        }
        var status = $('#status').val();
        var id = $('#order_id').val();
        var deliver_by = $('#deliver_by').val();
        var dataString ='update_order_status=true&id='+id+'&status='+status+'&delivery_boy_id='+deliver_by+'&ajaxCall=1';
		//console.log(dataString);
    $.ajax({        
        url: "api-firebase/order-process.php",
        type: "POST",
        data: dataString,
        beforeSend:function(){$('#submit_btn').html('Please wait..');$('#submit_btn').attr('disabled',true);},
        dataType: "json",
		async:false,
        success: function (data) {
			console.log(data);
            var result = $.map(data, function(value, index) {
                return [value];
            });
            if(result[1][0]=='C'){
                $('#result_fail').html(result[1]);
                $('#result_fail').show().delay(3000).fadeOut();
            }else{
                $('#result_success').html(result[1]);
                $('#result_success').show().delay(3000).fadeOut();
            }
            
            
            $('#submit_btn').attr('disabled',false);
            $('#submit_btn').html('Update');
            
             //alert(result[1]);
//          if(!result[0]){
//              location.reload();
//             }
        },
		error:function (err){
			console.log(err);
		}

    });
});
$(document).on('click','.update_item_status',function(e){
    e.preventDefault();
        var update_permission = '<?=$update_order_permission;?>';
        if(update_permission==0){
            alert('Sorry! you have no permission to update orders.');
            window.location.reload();
            return false;
        }
        var status = "cancelled";
        var id = $(this).data("id");
         var order_id = $('#order_id').val();
        var dataString ='update_order_item_status1=true&order_item_id='+id+'&order_id='+order_id+'&status='+status+'&ajaxCall=1';
		//console.log(dataString);
    $.ajax({        
        url: "api-firebase/order-process.php",
        type: "POST",
        data: dataString,
     //   beforeSend:function(){$('#submit_btn').html('Please wait..');$('#submit_btn').attr('disabled',true);},
        dataType: "json",
		async:false,
        success: function (data) {
			console.log(data);
            var result = $.map(data, function(value, index) {
                return [value];
            });
            if(result[1][0]=='C'){
                $('#result_fail').html(result[1]);
                $('#result_fail').show().delay(3000).fadeOut();
            }else{
                $('#result_success').html(result[1]);
                $('#result_success').show().delay(3000).fadeOut();
            }
            
            
       //     $('#submit_btn').attr('disabled',false);
         //   $('#submit_btn').html('Update');
            
             alert(result[1]);
//          if(!result[0]){
             location.reload();
//             }
        },
		error:function (err){
			console.log(err);
		}

    });
});
</script>

<script>
$(document).on('click','.update_order_total_payable',function(e){
    e.preventDefault();
        var update_permission = '<?=$update_order_permission;?>';
        if(update_permission==0){
            alert('Sorry! you have no permission to update orders.');
            window.location.reload();
            return false;
        }
        var discount = $('#input_discount').val();
        var total_payble = $('#final_total').val();
        // alert(total_payble);
        var deliver_by = $('#deliver_by').val();
        var id = $('#order_id').val();
        var dataString ='update_order_total_payable=true&id='+id+'&discount='+discount+'&total_payble='+total_payble+'&deliver_by='+deliver_by+'&ajaxCall=1';
    $.ajax({        
        url: "api-firebase/order-process.php" ,
        type: "POST",
        data: dataString,
        beforeSend: function(){$(this).html('...');},
        dataType: "json",
        success: function (data) {
            var result = $.map(data, function(value, index) {
                return [value];
            });
             alert(result[1]);
            if(!result[0]){}
                location.reload();
        }

    });
});
</script>


<script type="text/javascript">


/* function sendMail(){
    var process = $('#status').val();
    window.location.href = './public/send-message.php?process='+process+'&id=<?php //echo $data['id']; ?>';
} */
</script>

<script>
    $(document).ready(function () {
        $("#status").val("<?= $GLOBALS['currentStatus'] ?>");
    });
</script>
<script>
    function myfunction() {
         var create = '<?php echo $permissions['reports']['create']; ?>';
         if(create==0){
            alert('You have no permission to create invoice');
            return false;

         }
        window.location.href = 'invoice.php?id=<?php echo $res[0]['id']; ?>';
    }
</script>

<script>
$('#input_discount').on('input',function() {
    var total=$("#total_amount").val();
    
    var delivery_charge=$("#delivery_charge").val();
    
    var tax_amount=$("#tax_amount").val();
    
    var promo_discount=$("#promo_discount").val();
    
    var wallet_balance=$("#wallet_balance").val();
    
    // alert(total);
          var discount = $('#input_discount').val();
            discounted_amount = total * discount / 100; /*  */
            final_total = total - discounted_amount;
            discount_in_rupees = total-final_total;
            discount_in_rupees = discount_in_rupees;
            var f_total = +total + +delivery_charge + +tax_amount - promo_discount - wallet_balance - discount_in_rupees;
            // alert(f_total);
          if(discount >= 0){
              
        
              $("#final_total").val(Math.round((f_total + Number.EPSILON) * 100) / 100);
          }
});

</script>
<?php } ?>
<?php if($page=="Manage Customer Wallet"){?>
	<script>
    $('#wallet_form').validate({
    rules:{
    amount:"required",
    type:"required",
    }
    });
</script>
<script>
  $('#wallet_form').on('submit',function(e){
    e.preventDefault();
    var formData = new FormData(this);
 // if( $("#wallet_form").validate().form() ){
    if($('#details').val() != ''){
        if(confirm('Are you sure?')){
            
        $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data:formData,
        beforeSend:function(){$('#submit_btn').html('Please wait..');},
        cache:false,
        contentType: false,
        processData: false,
        success:function(result){
            $('#result').html(result);
            $('#result').show().delay(6000).fadeOut();
            $('#submit_btn').html('Submit');
            $('#wallet_form')[0].reset();
            $('#users').bootstrapTable('refresh');
        }
        });
       // }

    }else{
        alert('Please select atleast one user.');
        
    }
    }
    }); 
</script>
<script>
    $('#users').on('check.bs.table', function (e, row) {
    $('#details').val(row.id + " | " + row.name + " | " + row.email);
    $('#user_id').val(row.id);
});
</script>

<?php } ?>
<?php if($page=="Edit Product"){?>
	<script>
$(document).on('click','.delete-image',function(e){
    var SendButton = $(e.target);
    var pid = $(this).data('pid');
    var i = $(this).data('i');
    if(confirm('Are you sure want to delete the image?')){
        $.ajax({
            type:'POST',
            url: 'public/delete-other-images.php',
            data:'i='+i+'&pid='+pid,
            // beforeSend:function(){$('#submit_btn').html('Please wait..');},
            // cache:false,
            // contentType: false,
            // processData: false,
            success:function(result){
                if(result == '1'){
                    SendButton.prev('img').remove();
                    SendButton.remove();
                    alert('Image deleted successfully');
                    //window.location.replace("view-product-variants.php?id="+pid);
                }
                else
                    alert('Image could not be deleted!');
                    
            }
        });
    }
});
</script>
<script>
$.validator.addMethod('lessThanEqual', function(value, element, param) {
    return this.optional(element) || parseInt(value) < parseInt($(param).val());
}, "Discounted Price should be lesser than Price");
</script>
<script>
$('#edit_product_form').validate({
    rules:{
        name:"required",
        measurement:"required",
        price:"required",
        quantity:"required",
        // image:"required",
        discounted_price: { lessThanEqual: "#price" }
    }
});
</script>
<script>
$('#add_loose_variation').on('click',function(){
    var price_type=$('#price_type').val();
	html = '<div class="row"><div class="col-item col-item-1" style="width:10%;"><div class="form-group"><label for="measurement">Measurement</label>'
		+'<input type="text" class="form-control" name="insert_loose_measurement[]" required=""></div></div>'
		+'<div class="col-item col-item-1"><div class="form-group loose_div">'
        +'<label for="unit">Unit:</label><select class="form-control" name="insert_loose_measurement_unit_id[]">'
        +'<?php
            foreach($unit_data as  $row){
                echo "<option value=".$row['id'].">".$row['short_code']."</option>";
            }
        ?>'
        +'</select></div></div>'
        +'<div class="col-item" style="width:8%"><div class="form-group"><label for="weight">Weight(Kg):</label>'
		+'<input type="text" class="form-control" name="insert_loose_weight[]"></div></div>'
		+'<div class="col-item" style="width:10%"><div class="form-group"><label for="price">MRP  (<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="insert_loose_price[]" required=""></div></div>'
		+'<div class="col-item" style="width:10%"><div class="form-group"><label for="price">Vendor Price  (<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="insert_loose_vendor_price[]" required=""></div></div>';
		if(price_type=="including"){
    		html+='<div class="col-item di_price" style="width:14%"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="insert_loose_discounted_price[]" /></div></div>'
    		+'<div class="col-item pr_price" style="width:12%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="insert_loose_product_price[]" readonly/></div></div>';
		}
		if(price_type=="excluding"){
    		html+='<div class="col-item di_price" style="width:14%;"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="insert_loose_discounted_price[]" readonly/></div></div>'
    		+'<div class="col-item pr_price" style="width:12%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="insert_loose_product_price[]" /></div></div>';
		}
		html+='<div class="col-item col-item-1 t_pr" style="display:none"><div class="form-group"><label for="qty">SGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_sgst" name="insert_loose_item_sgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"  style="display:none"><div class="form-group"><label for="qty">CGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_cgst" name="insert_loose_item_cgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"style="display:none"><div class="form-group"><label for="qty">IGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_igst" name="insert_loose_item_igst[]" readonly/></div></div>'
        +'<div class="col-item col-item-1" style="display: grid;">'
        +'<label>Remove</label><a class="remove_variation text-danger" data-id="remove" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>'
        +'</div></div>';
    $('#loose_variations').append(html);
});
var numl=2;
$('#add_packate_variation').on('click',function(){
    var price_type=$('#price_type').val();
    
	html = '<div class="row"><div class="col-item col-item-1" style="width:10%"><div class="form-group"><label for="measurement">Measurement</label>'
		+'<input type="text" class="form-control" name="insert_packate_measurement[]" required=""></div></div>'
	    +'<div class="col-item col-item-1"><div class="form-group">'
	    +'<label for="measurement_unit">Unit</label><select class="form-control" name="insert_packate_measurement_unit_id[]">'
        +'<?php
            foreach($unit_data as $row){
                echo "<option value=".$row['id'].">".$row['short_code']."</option>";
            }   
            ?>'
        +'</select></div></div>'
        +'<div class="col-item" style="width:8%"><div class="form-group"><label for="weight">Weight(Kg):</label>'
		+'<input type="text" class="form-control" name="insert_packate_weight[]"></div></div>'
		+'<div class="col-item col-item-1" ><div class="form-group"><label for="price">MRP(<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="insert_packate_price[]" required=""></div></div>'
		+'<div class="col-item col-item-1" ><div class="form-group"><label for="price">Vendor Price(<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="insert_packate_vendor_price[]" required=""></div></div>';
		
		if(price_type=="including"){
    		html+='<div class="col-item di_price" style="width:11.3%"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name=insert_packate_discounted_price[]" /></div></div>'
    		+'<div class="col-item pr_price" style="width:12.1%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="insert_packate_product_price[]" readonly/></div></div>';
		}
		if(price_type=="excluding"){
    		html+='<div class="col-item di_price" style="width:11.3%;"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="insert_packate_discounted_price[]" readonly/></div></div>'
    		+'<div class="col-item pr_price" style="width:12.1%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="insert_packate_product_price[]" /></div></div>';
		}
		html+='<div class="col-item col-item-1 t_pr" style="display:none"><div class="form-group"><label for="qty">SGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_sgst" name="insert_packate_item_sgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"  style="display:none"><div class="form-group"><label for="qty">CGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_cgst" name="insert_packate_item_cgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"style="display:none"><div class="form-group"><label for="qty">IGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_igst" name="insert_packate_item_igst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1" ><div class="form-group"><label for="stock">Stock:</label>'
		+'<input type="text" class="form-control" name="insert_packate_stock[]" /></div></div>'
        +'<div class="col-item col-item-1"><div class="form-group packate_div"><label for="qty">Status:</label><select name="insert_packate_serve_for[]" class="form-control" required><option value="Available">Available</option><option value="Sold Out">Sold Out</option></select></div></div>'
        +'<div class="col-md-10" style="display:none"><div class="form-group packate_div"><label >Barcode Data:</label><input type="text" name="insert_packate_barcode_text[]" value="'+(parseInt(<?=$fn->generateEAN()?>)+numl)+'" style="width:50%" class=" form-control"></div>            	</div>'
        +'<div class="col-item col-item-1" style="display: grid;">'
        +'<label>Remove</label><a class="remove_variation text-danger" data-id="remove" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a>'
        +'</div></div>';
        numl++;
    $('#packate_variations').append(html);
});
</script>
<script>
$(document).on('click','.remove_variation',function(){
    if($(this).data('id')=='data_delete'){
        if(confirm('Are you sure? Want to delete this row')){
            // id = $('#product_variant_id').val();
            var id = $(this).closest('div.row').find("input[id='product_variant_id']").val();
            $.ajax({
                url:'public/db-operation.php',
                type: "post",
                data: 'id='+id+'&delete_variant=1',
                success: function(result){
                    //alert(result);
                    // $('#class_list').bootstrapTable("refresh");
                    location.reload();
                }
            });
        }
    }else{
        $(this).closest('.row').remove();
    }
});




$(document).on('change','#category_id',function(){
//   alert('change');
    $.ajax({
       url:'public/db-operation.php',
       method:'POST',
       data:'category_id='+$('#category_id').val()+'&find_subcategory=1',
       success:function(data){
          // alert(data);
           $('#subcategory_id').html(data);
       }
    });
});
$(document).on('change','#packate',function(){
    // alert('packate');
    // $('#variations').html("");
    $('#packate_div').show();
    $('.packate_div').show();
    // $('.packate_div').children(":input").prop('disabled',false);
     $('#loose_div').hide();
    $('.loose_div').hide();
    
    $('#status_div').hide();
    // $('.loose_div').children(":input").prop('disabled',true);
    $('#loose_stock_div').hide();
    // $('#loose_stock_div').children(":input").prop('disabled',true);
    
});
$(document).on('change','#loose',function(){
    // $('#variations').html("");
    // alert('loose');
    $('#loose_div').show();
    $('.loose_div').show();
    // $('.loose_div').children(":input").prop('disabled',false);
    $('#loose_stock_div').show();
    // $('#loose_stock_div').children(":input").prop('disabled',false);
    $('#status_div').show();
    $('#packate_div').hide();
    $('.packate_div').hide();
    // $('.packate_div').children(":input").prop('disabled',true);
    
});
</script>
<?php } ?>
<?php if($page=="Delivery Boys"){?>
	        <script>
            $('#transfer_form').validate({
                rules:{
                    amount:"required",
                }
            });
        </script>
                <script>
            $('#transfer_form').on('submit',function(e){
                e.preventDefault();
                var formData = new FormData(this);
                if($("#transfer_form").validate().form()){
                    $.ajax({
                    type:'POST',
                    url: $(this).attr('action'),
                    data:formData,
                    beforeSend:function(){$('#submit_button').html('Please wait..');},
                    cache:false,
                    contentType: false,
                    processData: false,
                    success:function(result){

                        $('#transfer_result').html(result);
                        $('#transfer_result').show().delay(3000).fadeOut();
                        $('#submit_button').html('Submit');
                        $('#amount').val('');
                        $('#delivery-boys').bootstrapTable('refresh');
                        setTimeout(function() {$('#fundTransferModal').modal('hide');}, 3000);
                    }
                    });
                }
            }); 
        </script>
        <script>
        $(document).on('click','.transfer-fund',function(){
                    id = $(this).data("id");
                    name = $(this).data("name");
                    mobile = $(this).data("mobile");
                    address = $(this).data("address");
                    balance = $(this).data("balance");

                    $('#boy_id').val(id);
                    $('#delivery_boy_name').val(name);
                    // alert(row.city_id);
                    $('#delivery_boy_mobile').val(mobile);
                    $('#delivery_boy_address').val(address);    
                    $('#delivery_boy_balance').val(balance);    

        });
        </script>
                <script>
           function validate_amount(){
            var balance=$('#delivery_boy_balance').val();
            var amount=$('#amount').val();
            if(parseInt(balance)>0){
                if(parseInt(amount) > parseInt(balance)){   
                    alert('You Can not enter amount greater than balance.');
                    $('#amount').val('');

                }
            }else{
                alert('Balance must be greater than zero.');
                    $('#amount').val('');
            }
            if(parseInt(amount)<=0){
                alert('Amount must be greater than zero.');
                $('#amount').val('');
            }
            
           }
        </script>
  <script>
      $('#add_form').validate({
        rules:{
        name:"required",
        mobile:"required",
        password:"required",
        address:"required",
        confirm_password : {
                    required:true,
                    equalTo : "#password"
                }
        }
      });
  </script>
    <script>
      $('#update_form').validate({
        rules:{
        update_name:"required",
        update_mobile:"required",
        update_address:"required",
        confirm_password : {
                    equalTo : "#update_password"
                }
        }
      });
  </script>
    <script>
      $('#add_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
      if( $("#add_form").validate().form() ){
            if(confirm('Are you sure?Want to Add Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#submit_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result);
                $('#result').show().delay(6000).fadeOut();
                $('#submit_btn').html('Submit');
                $('#add_form')[0].reset();
                $('#delivery-boys').bootstrapTable('refresh');
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            }
              }
        }); 
  </script>
  <script>
      $('#update_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
      if( $("#update_form").validate().form() ){
            //if(confirm('Are you sure?Want to Update Delivery Boy')){
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            beforeSend:function(){$('#update_btn').html('Please wait..');},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#update_result').html(result);
                $('#update_result').show().delay(6000).fadeOut();
                $('#update_btn').html('Update');
                $('#update_form')[0].reset();
                $('#delivery-boys').bootstrapTable('refresh');
                setTimeout(function() {$('#editDeliveryBoyModal').modal('hide');}, 3000);
                // $('#area_tp_form').find(':input').each(function(){
                //      $('#area_tp').val('');
                // });
                // $('#area_tp_list').bootstrapTable('refresh');
            }
            });
            //}
              }
        }); 
  </script>
  <script>
    window.actionEvents = {
        'click .edit-delivery-boy': function (e, value, row, index) {
            //alert('You click remove icon, row: ' + JSON.stringify(row));
            $("input[name=status][value=1]").prop('checked', true);
            if($(row.status).text() == 'Deactive')
                $("input[name=status][value=0]").prop('checked', true);
            $('#delivery_boy_id').val(row.id);
            $('#update_name').val(row.name);
            // alert(row.city_id);
            $('#update_mobile').val(row.mobile);
            $('#store_id1').val(row.store_id);
            $('#update_address').val(row.address);
            $('#update_bonus').val(row.bonus);
        }
    }
</script>
 <script>
      $(document).on('click','.delete-delivery-boy',function(){
            if(confirm('Are you sure? Want to delete delivery boy.')){
                
                id = $(this).data("id");
            
                // image = $(this).data("image");
                $.ajax({
                    url : 'public/db-operation.php',
                    type: "get",
                    data: 'id='+id+'&delete_delivery_boy=1',
                    success: function(result){
                        if(result==0){
                            $('#delivery-boys').bootstrapTable('refresh');
                        }
                        if(result==2){
                            alert('You have no permission to delete delivery boy');
                        }
                        if(result==1){
                            alert('Error! Delivery boy could not be deleted.');
                        }
                        if(result==3){
                            alert('You can not delete this delivery boy.');
                        }
                        
                    }
                });
            }
        });
  </script>

<?php } ?>


<?php if($page=="Add Product" || $page=="Edit Product"){?>
<script>
function incl_gst(tagThis){
            var discounted_price=$(tagThis).val();
            var sgst=$('#sgst').val();
            var cgst=$('#cgst').val();
            var igst=$('#igst').val();
            var gst=(sgst!='' && sgst!='undefined')?parseFloat(sgst):0;
            gst+=(cgst!='' && cgst!='undefined')?parseFloat(cgst):0;
            product_price=100*discounted_price/(100+gst);
            var item_sgst=item_cgst=item_igst=0;
             if(sgst!='' && sgst!='undefined'){
               var item_sgst=product_price*sgst/100;
               item_sgst=parseFloat(item_sgst.toFixed(2));
              $(tagThis).closest('.di_price').nextAll(':lt(2)').find('div>.item_sgst').val(item_sgst);
              // console.log($(tagThis).closest('.di_price').nextAll(':lt(2)').find('div>.item_sgst'));
             }else{
                 $(tagThis).closest('.di_price').nextAll(':lt(2)').find('div>.item_sgst').val('');
             }
             if(cgst!='' && cgst!='undefined'){
               var item_cgst=product_price*cgst/100;
               item_cgst=parseFloat(item_cgst.toFixed(2));
               $(tagThis).closest('.di_price').nextAll(':lt(3)').find('div>.item_cgst').val(item_cgst);
             }else{
                $(tagThis).closest('.di_price').nextAll(':lt(3)').find('div>.item_cgst').val(''); 
             }
             
             if(igst!='' && igst!='undefined'){
                i_gst=(igst!='' && igst!='undefined')?parseFloat(igst):0;
                iproduct_price=100*discounted_price/(100+i_gst);
               var item_igst=iproduct_price*igst/100;
               item_igst=parseFloat(item_igst.toFixed(2));
               $(tagThis).closest('.di_price').nextAll(':lt(4)').find('div>.item_igst').val(item_igst);
             }else{
                 $(tagThis).closest('.di_price').nextAll(':lt(4)').find('div>.item_igst').val('');
             }
            //var gst_amount=item_sgst+item_cgst+item_igst;
            var gst_amount=item_sgst+item_cgst;
            product_price=parseFloat((discounted_price-gst_amount).toFixed(2));
            $(tagThis).closest('.di_price').nextAll(':lt(1)').find('div>.product_price').val(product_price);
            $('.t_pr').show();
            $('.pr_price').show();
    }
    
    function excl_gst(tagThis){
        var product_price=$(tagThis).val();
            var sgst=$('#sgst').val();
            var cgst=$('#cgst').val();
            var igst=$('#igst').val();
            var item_sgst=item_cgst=item_igst=0;
             if(sgst!='' && sgst!='undefined'){
               var item_sgst=product_price*sgst/100;
               item_sgst=parseFloat(item_sgst.toFixed(2));
              $(tagThis).closest('.pr_price').nextAll(':lt(1)').find('div>.item_sgst').val(item_sgst);
              // console.log($(this).closest('.di_price').nextAll(':lt(2)').find('div>.item_sgst'));
             }else{
                 $(tagThis).closest('.pr_price').nextAll(':lt(1)').find('div>.item_sgst').val('');
             }
             if(cgst!='' && cgst!='undefined'){
               var item_cgst=product_price*cgst/100;
               item_cgst=parseFloat(item_cgst.toFixed(2));
               $(tagThis).closest('.pr_price').nextAll(':lt(2)').find('div>.item_cgst').val(item_cgst);
             }else{
                 $(tagThis).closest('.pr_price').nextAll(':lt(2)').find('div>.item_cgst').val('');
             }
             
             if(igst!='' && igst!='undefined'){
               var item_igst=product_price*igst/100;
               item_igst=parseFloat(item_igst.toFixed(2));
               $(tagThis).closest('.pr_price').nextAll(':lt(3)').find('div>.item_igst').val(item_igst);
             }else{
                  $(tagThis).closest('.pr_price').nextAll(':lt(3)').find('div>.item_igst').val('');
             }
            //var gst_amount=item_sgst+item_cgst+item_igst;
            var gst_amount=item_sgst+item_cgst;
            discounted_price=parseFloat((parseInt(product_price)+gst_amount).toFixed(2));
            $(tagThis).closest('.pr_price').prevAll(':lt(1)').find('div>.discounted_price').val(discounted_price);
            $('.t_pr').show();
            $('.di_price').show();
    }
    $('#price_type').on('change',function(){   
        
        var price_type=$('#price_type').val();
        if(price_type=="including"){
            // $('.di_price').show();
            // $('.pr_price').hide();
            $(".discounted_price").prop("readonly", false);
            $(".product_price").prop("readonly", true);

        }
        
        if(price_type=="excluding"){
            // $('.di_price').hide();
            // $('.pr_price').show();
            $(".discounted_price").prop("readonly", true);
            $(".product_price").prop("readonly", false);
        }
    });
    
    $(document).on("focusout", ".discounted_price,.product_price" , function() {
        var price_type=$('#price_type').val();
        if(price_type=="including"){
            incl_gst(this);
        }
        
        if(price_type=="excluding"){
            excl_gst(this);
        }
    });
    
    $(document).on("focusout", "#sgst,#cgst,#igst" , function() {
         var price_type=$('#price_type').val();
        if(price_type=="including"){
            var size=$('.discounted_price').length
            for(var i = 0; i <size; i++){
                if($('.discounted_price').eq(i).val()!='' && $('.discounted_price').eq(i).val!='undefined'){
                    incl_gst($('.discounted_price').eq(i));
                }
            }   
            
        }
        if(price_type=="excluding"){
            var size=$('.product_price').length
            for(var i = 0; i <size; i++){
                if($('.product_price').eq(i).val()!='' && $('.product_price').eq(i).val!='undefined'){
                    excl_gst($('.product_price').eq(i));
                }
            }
        }
    });
    
</script>
<?php } ?>

<?php if($page=="Add Product"){?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.min.js"></script>
<script>
$(".generate").click(function() {
    bar_data=$(this).parent().find('input').val();

});
 if($('#packate').prop('checked')){
     $('#packate_div').show();
    $('#packate_server_hide').hide();
     $('.loose_div').children(":input").prop('disabled',true);
    $('#loose_stock_div').children(":input").prop('disabled',true);
 }
 
$.validator.addMethod('lessThanEqual', function(value, element, param) {
    return this.optional(element) || parseInt(value) < parseInt($(param).val());
}, "Discounted Price should be lesser than Price");
</script>
<script>
$('#add_product_form').validate({
    ignore: [],
    debug: false,
	rules:{
		name:"required",
		measurement:"required",
		price:"required",
		quantity:"required",
		image:"required",
		discounted_price: { lessThanEqual: "#price" },
		description: {
              required: function(textarea) {
              CKEDITOR.instances[textarea.id].updateElement();
              var editorcontent = textarea.value.replace(/<[^>]*>/gi, '');
              return editorcontent.length === 0;
            }
        }
	}
});
</script>
<script>
var num = 2;
$('#add_packate_variation').on('click',function(){     
    var price_type=$('#price_type').val();
    
	html = '<div class="row"><div class="col-item " style="width:10%"><div class="form-group"><label for="measurement">Measurement</label>'
		+'<input type="text" class="form-control" name="packate_measurement[]" required=""></div></div>'
	    +'<div class="col-item col-item-1"><div class="form-group">'
	    +'<label for="measurement_unit">Unit</label><select class="form-control" name="packate_measurement_unit_id[]">'
        +'<?php
            foreach($res_unit as $row){
                echo "<option value=".$row['id'].">".$row['short_code']."</option>";
            }   
            ?>'
        +'</select></div></div>'
        +'<div class="col-item" style="width:8%"><div class="form-group"><label for="weight">Weight(Kg):</label>'
		+'<input type="text" class="form-control" name="packate_weight[]"></div></div>'
		+'<div class="col-item col-item-1" ><div class="form-group"><label for="price">MRP(<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="packate_price[]" required=""></div></div>'
		+'<div class="col-item" style="width:11.3%"><div class="form-group"><label for="price">Vendor Price(<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="packate_vendor_price[]" required=""></div></div>';
		
		if(price_type=="including"){
    		html+='<div class="col-item di_price" style="width:11.3%"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="packate_discounted_price[]" /></div></div>'
    		+'<div class="col-item pr_price" style="width:12.1%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="packate_product_price[]" readonly/></div></div>';
		}
		if(price_type=="excluding"){
    		html+='<div class="col-item di_price" style="width:11.3%;"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="packate_discounted_price[]" readonly/></div></div>'
    		+'<div class="col-item pr_price" style="width:12.1%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="packate_product_price[]" /></div></div>';
		}
		html+='<div class="col-item col-item-1 t_pr" style="display:none"><div class="form-group"><label for="qty">SGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_sgst" name="packate_item_sgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"  style="display:none"><div class="form-group"><label for="qty">CGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_cgst" name="packate_item_cgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"style="display:none"><div class="form-group"><label for="qty">IGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_igst" name="packate_item_igst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1" ><div class="form-group"><label for="stock">Stock:</label>'
		+'<input type="text" class="form-control" name="packate_stock[]" /></div></div>'
        +'<div class="col-item col-item-1"><div class="form-group packate_div"><label for="qty">Status:</label><select name="packate_serve_for[]" class="form-control" required><option value="Available">Available</option><option value="Sold Out">Sold Out</option></select></div></div>'
		+'<div class="col-md-10" style="display:none"><div class="form-group packate_div"><label >Barcode Data:</label><input type="text" name="barcode_text[]" value="'+(parseInt(<?=$fn->generateEAN()?>)+num)+'" style="width:50%" class=" form-control"></div></div>'
        +'<div class="col-item col-item-1" style="display: grid;"><label>Remove</label><a class="remove_variation text-danger" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a></div>'
		+'</div>';
		num++;
	$('#variations').append(html);
	$('#add_product_form').validate();
});

$('#add_loose_variation').on('click',function(){
    var price_type=$('#price_type').val();
	html = '<div class="row"><div class="col-item"style="width:10%;" ><div class="form-group"><label for="measurement">Measurement</label>'
		+'<input type="text" class="form-control" name="loose_measurement[]" required=""></div></div>'
		+'<div class="col-item col-item-1"><div class="form-group loose_div">'
        +'<label for="unit">Unit:</label><select class="form-control" name="loose_measurement_unit_id[]">'
        +'<?php
            foreach($res_unit as  $row){
                echo "<option value=".$row['id'].">".$row['short_code']."</option>";
            }
        ?>'
        +'</select></div></div>'
        +'<div class="col-item" style="width:8%"><div class="form-group"><label for="weight">Weight(Kg):</label>'
		+'<input type="text" class="form-control" name="loose_weight[]"></div></div>'
		+'<div class="col-item" style="width:10%"><div class="form-group"><label for="price">MRP  (<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="loose_price[]" required=""></div></div>'
		+'<div class="col-item" style="width:11%"><div class="form-group"><label for="price">Vendor Price  (<?=$settings['currency']?>):</label>'
		+'<input type="text" class="form-control" name="loose_vendor_price[]" required=""></div></div>';
		if(price_type=="including"){
    		html+='<div class="col-item di_price" style="width:14%"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="loose_discounted_price[]" /></div></div>'
    		+'<div class="col-item pr_price" style="width:12%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="loose_product_price[]" readonly/></div></div>';
		}
		if(price_type=="excluding"){
    		html+='<div class="col-item di_price" style="width:14%;"><div class="form-group"><label for="discounted_price">Selling Price(<?=$settings['currency']?>):</label>'
    		+'<input type="text" class="form-control discounted_price" name="loose_discounted_price[]" readonly/></div></div>'
    		+'<div class="col-item pr_price" style="width:12%;"><div class="form-group"><label for="qty">Product Price(<?=$settings['currency']?>):</label><input type="text" class="form-control product_price" name="loose_product_price[]" /></div></div>';
		}
		html+='<div class="col-item col-item-1 t_pr" style="display:none"><div class="form-group"><label for="qty">SGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_sgst" name="loose_item_sgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"  style="display:none"><div class="form-group"><label for="qty">CGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_cgst" name="loose_item_cgst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1 t_pr"style="display:none"><div class="form-group"><label for="qty">IGST(<?=$settings['currency']?>):</label><input type="text" class="form-control item_igst" name="loose_item_igst[]" readonly/></div></div>'
		+'<div class="col-item col-item-1" style="display: grid;"><label>Remove</label><a class="remove_variation text-danger" title="Remove variation of product" style="cursor: pointer;"><i class="fa fa-times fa-2x"></i></a></div>'
		+'</div>';
	$('#variations').append(html);
});
</script>
<script>
$(document).on('click','.remove_variation',function(){
	$(this).closest('.row').remove();
});


$(document).on('change','#category_id',function(){
//   alert("change");
    $.ajax({
    //   url:"add-product.php",
      url:"public/db-operation.php",
      data:"category_id="+$('#category_id').val()+"&change_category=1",
       method:"POST",
       success:function(data){
        //   alert(data);
           $('#subcategory_id').html(data);
        //   $('#res').html(data);
       }
    });
});

$(document).on('change','#packate',function(){
    $('#variations').html("");
    $('#packate_div').show();
    $('#packate_server_hide').hide();
    $('.packate_div').children(":input").prop('disabled',false);
    $('#loose_div').hide();
    $('.loose_div').children(":input").prop('disabled',true);
    $('#loose_stock_div').hide();
    $('#loose_stock_div').children(":input").prop('disabled',true);
    
});
$(document).on('change','#loose',function(){
    $('#variations').html("");
    $('#loose_div').show();
    $('.loose_div').children(":input").prop('disabled',false);
    $('#loose_stock_div').show();
    $('#loose_stock_div').children(":input").prop('disabled',false);
       $('#packate_server_hide').show();
    $('#packate_div').hide();
    $('.packate_div').children(":input").prop('disabled',true);
    
});
</script>
<?php } ?>
<?php if($page=="Categories" || $page=="Brands"){ ?>
    <script>
function queryParams_1(p){
    return {
        limit:p.limit,
        sort:p.sort,
        order:p.order,
        offset:p.offset,
        search:p.search
    };
}
</script>
<?php }
?>

</body>
</html>