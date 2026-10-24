<style>
    .parcel-detail-card{margin-bottom:16px;}
    .parcel-detail-card .box-body{display:flex;flex-wrap:wrap;}
    .parcel-detail-card .kv{padding:6px 18px 6px 0;min-width:140px;}
    .parcel-detail-card .kv b{display:block;font-size:11px;text-transform:uppercase;color:#888;}
    .parcel-detail-card .kv span{font-size:14px;color:#333;}
    body.dark .parcel-detail-card .kv span{color:#eee;}
</style>
<div id="content" class="container col-md-12">
	<?php
		include_once('includes/custom-functions.php');
		$fn = new custom_functions;

		$flash_success = "";
		$flash_error = "";

		if(isset($_GET['id'])){
			$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		}else{
			$ID = "";
		}

		if(!empty($ID)){
			$sql_query = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile, (SELECT name FROM delivery_boys b WHERE b.id = pr.delivery_boy_id) AS delivery_boy_name, (SELECT mobile FROM delivery_boys b WHERE b.id = pr.delivery_boy_id) AS delivery_boy_mobile FROM `parcel_requests` pr WHERE pr.id = ".$ID;
			$db->sql($sql_query);
			$res = $db->getResult();
			if(count($res) == 0){
				echo '<div class="alert alert-danger">Parcel request not found.</div>';
				$db->disconnect();
				return false;
			}
			$order = $res[0];
		}else{
			echo '<div class="alert alert-danger">Parcel request id is required.</div>';
			$db->disconnect();
			return false;
		}

		if(isset($_POST['update_parcel_status'])){
			$new_status = (isset($_POST['parcel_status']))?$db->escapeString($fn->xss_clean($_POST['parcel_status'])):"";
			$otp = (isset($_POST['delivery_otp']))?trim($db->escapeString($fn->xss_clean($_POST['delivery_otp']))):"";

			if(empty($new_status)){
				$flash_error = "Please select a status.";
			}else if($new_status == 'delivered'){
				if(empty($otp)){
					$flash_error = "OTP is required to mark this parcel as delivered.";
				}else if(empty($order['otp'])){
					$flash_error = "No OTP found for this parcel request.";
				}else if((string)$otp !== (string)$order['otp']){
					$flash_error = "Invalid OTP. Please ask the customer for the correct OTP.";
				}else{
					$sql_query = "UPDATE parcel_requests SET status = 'delivered' WHERE id = ".$ID;
					$db->sql($sql_query);
					$flash_success = "Parcel marked as delivered successfully.";
				}
			}else{
				$allowed = array('accepted','picked','cancelled');
				if(in_array($new_status, $allowed)){
					$sql_query = "UPDATE parcel_requests SET status = '".$new_status."' WHERE id = ".$ID;
					$db->sql($sql_query);
					$flash_success = "Parcel status updated successfully.";

					// If parcel is cancelled, process automatic refund
					if($new_status == 'cancelled'){
						$refund_info = $fn->refund_parcel_payment($ID);
						if(isset($refund_info) && ($refund_info['status'] == 'success' || $refund_info['status'] == 'processing')){
							$flash_success = "Parcel order cancelled. " . $refund_info['message'];
						} else if(isset($refund_info) && $refund_info['status'] == 'already_refunded'){
							$flash_success = "Parcel order cancelled. Payment was already refunded.";
						} else if(isset($refund_info) && $refund_info['status'] == 'failed' && !empty($refund_info['is_online'])){
							$flash_error = "Parcel order status set to cancelled, but refund failed: " . $refund_info['message'];
						}
					}

					// Notify nearby online delivery boys when the parcel order is accepted
					if($new_status == 'accepted'){
						$fn->dispatch_new_order('parcel', $ID, 'New Parcel Order', "New parcel delivery request #".$ID." is ready. Open the app to view the details.");
					}
				}else{
					$flash_error = "Invalid status selected.";
				}
			}

			// reload fresh data
			$sql_query = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile, (SELECT name FROM delivery_boys b WHERE b.id = pr.delivery_boy_id) AS delivery_boy_name, (SELECT mobile FROM delivery_boys b WHERE b.id = pr.delivery_boy_id) AS delivery_boy_mobile FROM `parcel_requests` pr WHERE pr.id = ".$ID;
			$db->sql($sql_query);
			$res = $db->getResult();
			$order = $res[0];
		}

		$status_labels = array(
			'pending' => 'Order Placed',
			'accepted' => 'Order Accepted',
			'picked' => 'Order Picked',
			'delivered' => 'Order Delivered',
			'cancelled' => 'Cancelled'
		);
		$current_status = strtolower($order['status']);
		$status_label = isset($status_labels[$current_status]) ? $status_labels[$current_status] : ucwords($current_status);
	?>

	<?php if($flash_success != ''): ?>
		<div class="alert alert-success"><?php echo $flash_success; ?></div>
	<?php endif; ?>
	<?php if($flash_error != ''): ?>
		<div class="alert alert-danger"><?php echo $flash_error; ?></div>
	<?php endif; ?>

	<div class="row">
		<div class="col-xs-12">
			<div class="box parcel-detail-card">
				<div class="box-header">
					<h3 class="box-title">Parcel Request #<?php echo $order['id']; ?></h3>
					<h4 class="box-subtitle">Status: <b><?php echo $status_label; ?></b></h4>
				</div>
				<div class="box-body">
					<div class="kv"><b>Item Type</b><span><?php echo $order['item_type_name'] ?: '-'; ?></span></div>
					<div class="kv"><b>Weight (kg)</b><span><?php echo $order['weight_kg'] != '' ? $order['weight_kg'] : '-'; ?></span></div>
					<div class="kv"><b>Distance (km)</b><span><?php echo $order['distance_km'] != '' ? $order['distance_km'] : '-'; ?></span></div>
					<div class="kv"><b>Total Fare</b><span>&#8377;<?php echo $order['total_price'] ?: '0.00'; ?></span></div>
					<div class="kv"><b>Payment</b><span><?php echo ucwords($order['payment_status']); ?></span></div>
					<div class="kv"><b>Pickup Time</b><span><?php echo $order['pickup_time'] ?: '-'; ?></span></div>
					<div class="kv"><b>Placed On</b><span><?php echo $order['created_at']; ?></span></div>
					<div class="kv"><b>User</b><span><?php echo (!empty($order['user_name']))?$order['user_name'].' ('.$order['user_mobile'].')':'User #'.$order['user_id']; ?></span></div>
					<?php if(!empty($order['delivery_boy_id']) && !empty($order['delivery_boy_name'])): ?>
						<div class="kv"><b>Delivery Boy</b><span><?php echo $order['delivery_boy_name'].' ('.$order['delivery_boy_mobile'].')'; ?></span></div>
					<?php endif; ?>
					<?php if($order['parcel_image'] != ''): ?>
						<?php foreach(explode(',', $order['parcel_image']) as $img): ?>
							<?php $img = trim($img); if($img != ''): ?>
								<a href="<?php echo DOMAIN_URL . $img; ?>" target="_blank" style="display:inline-block;margin:2px;"><img src="<?php echo DOMAIN_URL . $img; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;"></a>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<?php
			$refund_details = $fn->get_order_refund_details($ID, 'parcel');
			$show_refund_card = ($refund_details['is_cancelled'] || $refund_details['refund_state'] !== 'none');
			if($show_refund_card){
			?>
			<div class="box box-warning" id="parcel_refund_tracking_box" style="margin-bottom:16px;border-top:3px solid #f39c12;">
				<div class="box-header with-border">
					<h3 class="box-title"><i class="fa fa-refresh"></i> Razorpay Refund & Cancellation Tracking</h3>
					<div class="box-tools pull-right">
						<?php if(!empty($refund_details['can_sync'])){ ?>
							<button type="button" class="btn btn-sm btn-info btn-sync-refund-parcel" data-order-id="<?=$ID?>" data-query-id="<?=!empty($refund_details['refund_id']) ? $refund_details['refund_id'] : $refund_details['payment_id']?>"><i class="fa fa-refresh"></i> Check Live Razorpay Status</button>
						<?php } ?>
					</div>
				</div>
				<div class="box-body">
					<div class="row">
						<div class="col-md-6">
							<table class="table table-bordered">
								<tr>
									<th style="width: 35%;">Parcel Status</th>
									<td><span class="label label-danger">Cancelled</span></td>
								</tr>
								<tr>
									<th>Payment Method</th>
									<td><strong><?=htmlspecialchars($refund_details['payment_method'])?></strong></td>
								</tr>
								<tr>
									<th>Razorpay Payment ID</th>
									<td>
										<?php if(!empty($refund_details['payment_id'])){ ?>
											<code><?=htmlspecialchars($refund_details['payment_id'])?></code>
										<?php } else { ?>
											<span class="text-muted">Not detected</span>
										<?php } ?>
									</td>
								</tr>
								<tr>
									<th>Refund ID</th>
									<td>
										<?php if(!empty($refund_details['refund_id'])){ ?>
											<strong><?=htmlspecialchars($refund_details['refund_id'])?></strong>
										<?php } else { ?>
											<span class="text-muted">-</span>
										<?php } ?>
									</td>
								</tr>
								<tr>
									<th>Refund Amount</th>
									<td><strong>Rs. <?=number_format((float)$refund_details['refund_amount'], 2)?></strong></td>
								</tr>
								<?php if(!empty($refund_details['rrn'])){ ?>
								<tr>
									<th>Bank RRN (Tracking #)</th>
									<td><code style="font-weight:bold;color:#0073b7;"><?=htmlspecialchars($refund_details['rrn'])?></code> <small class="text-muted">(Customer bank reference)</small></td>
								</tr>
								<?php } ?>
								<?php if(!empty($refund_details['refund_date'])){ ?>
								<tr>
									<th>Recorded Date</th>
									<td><?=htmlspecialchars($refund_details['refund_date'])?></td>
								</tr>
								<?php } ?>
							</table>
						</div>
						<div class="col-md-6">
							<div style="padding:15px; background:#f9f9f9; border-radius:4px; border:1px solid #e3e3e3;">
								<h4>Refund Status</h4>
								<div style="margin-bottom:12px;">
									<?php if($refund_details['refund_state'] === 'processing'){ ?>
										<span class="label label-warning" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-clock-o"></i> Razorpay: Refund Processing</span>
										<div class="alert alert-warning" style="margin-top:12px;margin-bottom:0;padding:10px;">
											<strong>Bank Settlement In Progress:</strong> Razorpay has accepted the refund request. Customer's bank typically takes <strong>5 to 7 business days</strong> to credit the customer's account statement.
											<br><small>Status in local database: <code>process</code></small>
										</div>
									<?php } elseif($refund_details['refund_state'] === 'processed'){ ?>
										<span class="label label-success" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-check"></i> Razorpay: Refund Settled / Completed</span>
										<div class="alert alert-success" style="margin-top:12px;margin-bottom:0;padding:10px;">
											<strong>Completed:</strong> The refund has been settled to the customer's payment account.
										</div>
									<?php } elseif($refund_details['refund_state'] === 'wallet_refunded'){ ?>
										<span class="label label-info" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-credit-card"></i> Refunded to User Wallet</span>
										<p class="text-muted" style="margin-top:8px;">Amount was credited back to user's app wallet balance.</p>
									<?php } elseif($refund_details['refund_state'] === 'cod_skipped'){ ?>
										<span class="label label-default" style="font-size:14px;padding:6px 12px;display:inline-block;">COD (Cash on Delivery)</span>
										<p class="text-muted" style="margin-top:8px;">No online payment was collected, so no refund is required.</p>
									<?php } else { ?>
										<span class="label label-danger" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-exclamation-triangle"></i> Not Refunded / Refund Failed</span>
										<div class="alert alert-danger" style="margin-top:12px;margin-bottom:0;padding:10px;">
											<?=!empty($refund_details['refund_message']) ? htmlspecialchars($refund_details['refund_message']) : 'Parcel order was cancelled, but online refund was not processed yet.'?>
										</div>
									<?php } ?>
								</div>

								<?php if($refund_details['can_retry']){ ?>
									<div style="margin-top:15px;padding-top:12px;border-top:1px dashed #ccc;">
										<label><i class="fa fa-paper-plane"></i> Initiate / Retry Refund Manually</label>
										<div class="input-group">
											<input type="text" id="manual_parcel_payment_id" class="form-control" placeholder="Razorpay Payment ID (pay_...)" value="<?=$refund_details['payment_id']?>">
											<span class="input-group-btn">
												<button class="btn btn-warning btn-manual-refund-parcel" type="button" data-order-id="<?=$ID?>"><i class="fa fa-undo"></i> Initiate Refund Now</button>
											</span>
										</div>
										<p class="help-block" style="margin-top:5px;font-size:11px;">If the payment ID was not captured automatically, copy the <code>pay_...</code> ID from your Razorpay Dashboard and paste it here.</p>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php } ?>

		<?php if(!empty($order['otp']) && !in_array($current_status, array('delivered','cancelled'))): ?>
			<div class="alert alert-warning" style="margin-top:12px;">
				<b>Delivery OTP: <?php echo $order['otp']; ?></b><br>
				Useful when the customer's device is switched off at delivery time &mdash; the assigned delivery boy may be given this OTP by you, so he can complete the delivery in his app. Only share it with the assigned delivery boy. You can also mark the parcel as delivered here using the same OTP.
			</div>
		<?php endif; ?>

		<div class="box">
				<div class="box-header">
					<h3 class="box-title">Update Status</h3>
				</div>
				<div class="box-body">
					<form method="post">
						<div class="row">
							<div class="col-md-6">
								<div class="form-group">
									<label for="parcel_status">Status</label>
									<select class="form-control" name="parcel_status" id="parcel_status">
										<option value="accepted" <?php echo ($current_status == 'accepted')?'selected':''; ?>>Order Accepted</option>
										<option value="picked" <?php echo ($current_status == 'picked')?'selected':''; ?>>Order Picked</option>
										<option value="delivered" <?php echo ($current_status == 'delivered')?'selected':''; ?>>Order Delivered</option>
										<option value="cancelled" <?php echo ($current_status == 'cancelled')?'selected':''; ?>>Cancelled</option>
									</select>
								</div>
								<div class="form-group" id="otp_wrap">
									<label for="delivery_otp">Delivery OTP</label>
									<input type="text" class="form-control" name="delivery_otp" id="delivery_otp" maxlength="6" placeholder="OTP provided by the customer">
									<p class="help-block">Required to mark the parcel as delivered. Ask the customer for the OTP shown in their app.</p>
								</div>
							</div>
							<div class="col-md-6">
								<h4 style="margin-top:12px;">Pickup</h4>
								<p style="margin:0 0 2px 0;"><?php echo $order['sender_name'].' ('.$order['sender_phone'].')'; ?></p>
								<p style="margin:0 0 12px 0;"><?php echo $order['pickup_location']; ?></p>
								<h4 style="margin-top:6px;">Drop</h4>
								<p style="margin:0 0 2px 0;"><?php echo $order['recipient_name'].' ('.$order['recipient_phone'].')'; ?></p>
								<p style="margin:0 0 12px 0;"><?php echo $order['drop_location']; ?></p>
							</div>
						</div>
						<button type="submit" name="update_parcel_status" id="submit_btn" class="btn btn-primary">
							<i class="fa fa-check"></i> Update Status
						</button>
						<a href="parcel-requests.php" class="btn btn-default">Back</a>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function(){
		function toggleOtp(){
			if($('#parcel_status').val() === 'delivered'){
				$('#otp_wrap').show();
			} else {
				$('#otp_wrap').hide();
			}
		}
		toggleOtp();
		$('#parcel_status').on('change', toggleOtp);
	});

	// Live Razorpay sync for parcel orders
	$(document).on('click', '.btn-sync-refund-parcel', function(e){
		e.preventDefault();
		var $btn = $(this);
		var orderId = $btn.data('order-id');
		var queryId = $btn.data('query-id');
		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking Razorpay...');

		$.ajax({
			url: 'api-firebase/order-process.php',
			type: 'POST',
			data: {
				sync_refund_status: 1,
				order_id: orderId,
				order_type: 'parcel',
				query_id: queryId
			},
			dataType: 'json',
			success: function(resp){
				$btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check Live Razorpay Status');
				if(!resp.error && resp.data){
					alert(resp.message);
					location.reload();
				} else {
					alert('Razorpay Check: ' + (resp.message || 'Unable to fetch status.'));
				}
			},
			error: function(){
				$btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check Live Razorpay Status');
				alert('Server error occurred while checking Razorpay status.');
			}
		});
	});

	// Manual refund for parcel orders
	$(document).on('click', '.btn-manual-refund-parcel', function(e){
		e.preventDefault();
		var $btn = $(this);
		var orderId = $btn.data('order-id');
		var paymentId = $('#manual_parcel_payment_id').val().trim();

		if(!confirm('Are you sure you want to initiate Razorpay refund for Parcel Order #' + orderId + (paymentId ? (' using Payment ID: ' + paymentId) : '') + '?')){
			return;
		}

		$btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Initiating...');

		$.ajax({
			url: 'api-firebase/order-process.php',
			type: 'POST',
			data: {
				process_refund: 1,
				order_id: orderId,
				order_type: 'parcel',
				payment_id: paymentId
			},
			dataType: 'json',
			success: function(resp){
				$btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Initiate Refund Now');
				alert(resp.message);
				location.reload();
			},
			error: function(){
				$btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Initiate Refund Now');
				alert('Server error while processing refund.');
			}
		});
	});
</script>

<?php $db->disconnect(); ?>