<?php
	include_once('includes/functions.php');
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;

	$sql_query = "SELECT per_km_price,base_price,max_weight_kg,terms_conditions,is_service_available FROM parcel_settings ORDER BY id ASC LIMIT 1";
	$db->sql($sql_query);
	$settings_res = $db->getResult();
	$settings_data = !empty($settings_res) ? $settings_res[0] : array();

	$per_km_price = isset($settings_data['per_km_price']) ? $settings_data['per_km_price'] : "";
	$base_price = isset($settings_data['base_price']) ? $settings_data['base_price'] : "";
	$max_weight_kg = isset($settings_data['max_weight_kg']) ? $settings_data['max_weight_kg'] : "";
	$terms_conditions = isset($settings_data['terms_conditions']) ? $settings_data['terms_conditions'] : "";
	$is_service_available = isset($settings_data['is_service_available']) ? $settings_data['is_service_available'] : "1";

	if(isset($_POST['btnUpdate'])){
		$per_km_price = $db->escapeString($fn->xss_clean($_POST['per_km_price']));
		$base_price = $db->escapeString($fn->xss_clean($_POST['base_price']));
		$max_weight_kg = $db->escapeString($fn->xss_clean($_POST['max_weight_kg']));
		$terms_conditions = $db->escapeString($fn->xss_clean($_POST['terms_conditions']));
		$is_service_available = (isset($_POST['is_service_available']) && $_POST['is_service_available'] == '1') ? '1' : '0';

		$error = array();
		if($per_km_price == ''){
			$error['per_km_price'] = " <span class='label label-danger'>Required!</span>";
		}
		if($base_price == ''){
			$error['base_price'] = " <span class='label label-danger'>Required!</span>";
		}
		if($max_weight_kg == ''){
			$error['max_weight_kg'] = " <span class='label label-danger'>Required!</span>";
		}
		if(empty($terms_conditions)){
			$error['terms_conditions'] = " <span class='label label-danger'>Required!</span>";
		}

		if(empty($error)){
			if(!empty($settings_data)){
				$sql_query = "UPDATE parcel_settings SET per_km_price = '$per_km_price', base_price = '$base_price', max_weight_kg = '$max_weight_kg', terms_conditions = '$terms_conditions', is_service_available = '$is_service_available' WHERE id = 1";
			}else{
				$sql_query = "INSERT INTO parcel_settings (per_km_price, base_price, max_weight_kg, terms_conditions, is_service_available) VALUES ('$per_km_price', '$base_price', '$max_weight_kg', '$terms_conditions', '$is_service_available')";
			}
			if($db->sql($sql_query)){
				$error['update_settings'] = "<div class='content-header'><span class='label label-success'>Settings Updated Successfully</span></div>";
			}else{
				$error['update_settings'] = "<span class='label label-danger'>Failed to update settings</span>";
			}
		}
	}
?>
<div class="row">
    <div class="col-md-8">
        <?php echo isset($error['update_settings']) ? $error['update_settings'] : '';?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Parcel Settings</h3>
                <h4 class="box-subtitle">Configure fare (per km price and base price), maximum parcel weight capacity, and terms &amp; conditions shown in the user app.</h4>
            </div>
            <form method="post">
                <div class="box-body">
                    <div class="form-group col-md-6">
                        <label for="per_km_price">Per KM Price (&#8377;)</label><?php echo isset($error['per_km_price']) ? $error['per_km_price'] : '';?>
                        <input type="number" step="0.01" min="0" class="form-control" name="per_km_price" value="<?php echo $per_km_price; ?>" placeholder="e.g. 15.00" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="base_price">Base Price (&#8377;)</label><?php echo isset($error['base_price']) ? $error['base_price'] : '';?>
                        <input type="number" step="0.01" min="0" class="form-control" name="base_price" value="<?php echo $base_price; ?>" placeholder="e.g. 30.00" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="max_weight_kg">Max Weight Capacity (kg)</label><?php echo isset($error['max_weight_kg']) ? $error['max_weight_kg'] : '';?>
                        <input type="number" step="0.1" min="0" class="form-control" name="max_weight_kg" value="<?php echo $max_weight_kg; ?>" placeholder="e.g. 5.00" required>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="pickup-service-available-button">Pickup Service Availability</label>
                        <div>
                            <input type="checkbox" id="pickup-service-available-button" class="js-switch" <?php echo $is_service_available == '1' ? 'checked' : ''; ?>>
                            <input type="hidden" name="is_service_available" id="is-service-available" value="<?php echo $is_service_available == '1' ? '1' : '0'; ?>">
                            <label for="pickup-service-available-button" style="margin-left:10px; font-weight:400;">Enabled - users can use the Pickup service. Turn off to show "Service not available" when users tap Pickup in the app.</label>
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="terms_conditions">Terms &amp; Conditions</label><?php echo isset($error['terms_conditions']) ? $error['terms_conditions'] : '';?>
                        <textarea class="form-control" name="terms_conditions" rows="8" placeholder="Enter one term per line..." required><?php echo $terms_conditions; ?></textarea>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnUpdate">Update Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="separator"> </div>

<script>
    var pickupAvailableCheckbox = document.querySelector('#pickup-service-available-button');
    if (pickupAvailableCheckbox) {
        var init = new Switchery(pickupAvailableCheckbox);
        pickupAvailableCheckbox.onchange = function() {
            $('#is-service-available').val($(this).is(':checked') ? '1' : '0');
        };
    }
</script>

<?php $db->disconnect(); ?>