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
			$sql_query = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile FROM `parcel_requests` pr WHERE pr.id = ".$ID;
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

					// Notify all online delivery boys when the parcel order is accepted
					if($new_status == 'accepted'){
						$fn->send_notification_to_delivery_boy(0, "New Parcel Order", "New parcel delivery request #".$ID." is ready. Open the app to view the details.", 'delivery_boys', $ID, 'parcel');
						$fn->store_delivery_boy_notification(0, $ID, "New Parcel Order", "New parcel delivery request #".$ID." is ready.", 'parcel');
					}
				}else{
					$flash_error = "Invalid status selected.";
				}
			}

			// reload fresh data
			$sql_query = "SELECT pr.*, (SELECT name FROM users u WHERE u.id = pr.user_id) AS user_name, (SELECT mobile FROM users u WHERE u.id = pr.user_id) AS user_mobile FROM `parcel_requests` pr WHERE pr.id = ".$ID;
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
					<?php if($order['parcel_image'] != ''): ?>
						<?php foreach(explode(',', $order['parcel_image']) as $img): ?>
							<?php $img = trim($img); if($img != ''): ?>
								<a href="<?php echo DOMAIN_URL . $img; ?>" target="_blank" style="display:inline-block;margin:2px;"><img src="<?php echo DOMAIN_URL . $img; ?>" style="width:60px;height:60px;object-fit:cover;border-radius:4px;border:1px solid #ddd;"></a>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
</div>
		</div>

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
</script>

<?php $db->disconnect(); ?>