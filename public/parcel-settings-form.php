<?php
	include_once('includes/functions.php');
	include_once('includes/custom-functions.php');
	$fn = new custom_functions;

	$sql_query = "SELECT per_km_price,base_price,max_weight_kg,terms_conditions,is_service_available,is_shipping_available,opening_time,closing_time,cod_payment_method,razorpay_payment_method FROM parcel_settings ORDER BY id ASC LIMIT 1";
	$db->sql($sql_query);
	$settings_res = $db->getResult();
	$settings_data = !empty($settings_res) ? $settings_res[0] : array();

	$per_km_price = isset($settings_data['per_km_price']) ? $settings_data['per_km_price'] : "";
	$base_price = isset($settings_data['base_price']) ? $settings_data['base_price'] : "";
	$max_weight_kg = isset($settings_data['max_weight_kg']) ? $settings_data['max_weight_kg'] : "";
	$terms_conditions = isset($settings_data['terms_conditions']) ? $settings_data['terms_conditions'] : "";
	$is_service_available = isset($settings_data['is_service_available']) ? $settings_data['is_service_available'] : "1";
	$is_shipping_available = isset($settings_data['is_shipping_available']) ? $settings_data['is_shipping_available'] : "1";
	$cod_payment_method = isset($settings_data['cod_payment_method']) ? $settings_data['cod_payment_method'] : "0";
	$razorpay_payment_method = isset($settings_data['razorpay_payment_method']) ? $settings_data['razorpay_payment_method'] : "1";
	$opening_time = (isset($settings_data['opening_time']) && $settings_data['opening_time'] !== '' && $settings_data['opening_time'] !== null) ? $settings_data['opening_time'] : "";
	$closing_time = (isset($settings_data['closing_time']) && $settings_data['closing_time'] !== '' && $settings_data['closing_time'] !== null) ? $settings_data['closing_time'] : "";

	// TIME columns come back as HH:MM:SS; the time inputs want HH:MM.
	$opening_time_input = ($opening_time !== "") ? substr($opening_time, 0, 5) : "";
	$closing_time_input = ($closing_time !== "") ? substr($closing_time, 0, 5) : "";

	if(isset($_POST['btnUpdate'])){
		$per_km_price = $db->escapeString($fn->xss_clean($_POST['per_km_price']));
		$base_price = $db->escapeString($fn->xss_clean($_POST['base_price']));
		$max_weight_kg = $db->escapeString($fn->xss_clean($_POST['max_weight_kg']));
		$terms_conditions = $db->escapeString($fn->xss_clean($_POST['terms_conditions']));
		$is_service_available = (isset($_POST['is_service_available']) && $_POST['is_service_available'] == '1') ? '1' : '0';
		$is_shipping_available = (isset($_POST['is_shipping_available']) && $_POST['is_shipping_available'] == '1') ? '1' : '0';
		$cod_payment_method = (isset($_POST['cod_payment_method']) && $_POST['cod_payment_method'] == '1') ? '1' : '0';
		$razorpay_payment_method = (isset($_POST['razorpay_payment_method']) && $_POST['razorpay_payment_method'] == '1') ? '1' : '0';
		$opening_time_input = isset($_POST['opening_time']) ? trim($fn->xss_clean($_POST['opening_time'])) : "";
		$closing_time_input = isset($_POST['closing_time']) ? trim($fn->xss_clean($_POST['closing_time'])) : "";

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

		// Operating hours are optional, but both ends must be given together.
		// An empty pair means the service is open 24 hours.
		$opening_sql = "NULL";
		$closing_sql = "NULL";
		if($opening_time_input !== "" || $closing_time_input !== ""){
			$opening_norm = preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $opening_time_input, $om) ? $opening_time_input.':00' : false;
			$closing_norm = preg_match('/^([01][0-9]|2[0-3]):([0-5][0-9])$/', $closing_time_input, $cm) ? $closing_time_input.':00' : false;
			if($opening_norm === false || $closing_norm === false){
				$error['operating_hours'] = " <span class='label label-danger'>Please enter both times in HH:MM format.</span>";
			}else{
				$opening_sql = "'".$opening_norm."'";
				$closing_sql = "'".$closing_norm."'";
			}
		}

		if(empty($error)){
			if(!empty($settings_data)){
				$sql_query = "UPDATE parcel_settings SET per_km_price = '$per_km_price', base_price = '$base_price', max_weight_kg = '$max_weight_kg', terms_conditions = '$terms_conditions', is_service_available = '$is_service_available', is_shipping_available = '$is_shipping_available', cod_payment_method = '$cod_payment_method', razorpay_payment_method = '$razorpay_payment_method', opening_time = $opening_sql, closing_time = $closing_sql WHERE id = 1";
			}else{
				$sql_query = "INSERT INTO parcel_settings (per_km_price, base_price, max_weight_kg, terms_conditions, is_service_available, is_shipping_available, cod_payment_method, razorpay_payment_method, opening_time, closing_time) VALUES ('$per_km_price', '$base_price', '$max_weight_kg', '$terms_conditions', '$is_service_available', '$is_shipping_available', '$cod_payment_method', '$razorpay_payment_method', $opening_sql, $closing_sql)";
			}
			if($db->sql($sql_query)){
				$ppm_json = json_encode(array('cod_payment_method' => $cod_payment_method, 'razorpay_payment_method' => $razorpay_payment_method));
				$db->sql("INSERT INTO settings (variable, value) VALUES ('parcel_payment_methods', '$ppm_json') ON DUPLICATE KEY UPDATE value = '$ppm_json'");

				$error['update_settings'] = "<div class='content-header'><span class='label label-success'>Settings Updated Successfully</span></div>";
				// Reflect the saved values so a re-render shows them.
				$opening_time_input = ($opening_sql === "NULL") ? "" : substr($opening_sql, 1, 5);
				$closing_time_input = ($closing_sql === "NULL") ? "" : substr($closing_sql, 1, 5);
			}else{
				$error['update_settings'] = "<span class='label label-danger'>Failed to update settings</span>";
			}
		}
	}

	// ------------------------------------------------------------------
	// Zone-wise parcel settings. Each zone can override the global parcel
	// tariff (per km price, base price) and its max weight, and can switch
	// pickup availability off for its own area. A blank fare/weight means
	// "inherit the global parcel settings value"; pickup availability is an
	// explicit per-zone on/off once the zone is saved.
	// ------------------------------------------------------------------
	include_once('includes/zone-functions.php');

	$zone_options = array();
	$db->sql("SELECT id, name FROM zone ORDER BY id");
	$zone_options = $db->getResult();

	$selected_zone_id = isset($_GET['zone_id']) ? intval($_GET['zone_id']) : 0;

	if(isset($_POST['btnUpdateZoneParcel'])){
		$selected_zone_id = (int)$db->escapeString($fn->xss_clean(isset($_POST['zone_id']) ? $_POST['zone_id'] : 0));
		if($selected_zone_id <= 0){
			$error['update_zone_parcel'] = "<span class='label label-danger'>Please select a zone.</span>";
		}else{
			$zp_problems = array();
			$zp_per_km = zn_read_optional_number($_POST, 'parcel_per_km_price', $zp_problems);
			$zp_base = zn_read_optional_number($_POST, 'parcel_base_price', $zp_problems);
			$zp_weight = zn_read_optional_number($_POST, 'parcel_max_weight_kg', $zp_problems);
			$zp_service = (isset($_POST['parcel_is_service_available']) && $_POST['parcel_is_service_available'] == '1') ? '1' : '0';
			$zone_sql = "UPDATE zone SET parcel_per_km_price=".$zp_per_km.", parcel_base_price=".$zp_base.", parcel_max_weight_kg=".$zp_weight.", parcel_is_service_available=".$zp_service." WHERE id=".$selected_zone_id;
			if($db->sql($zone_sql)){
				$error['update_zone_parcel'] = "<span class='label label-success'>Zone parcel settings updated successfully".(!empty($zp_problems) ? " (invalid values were ignored for: ".implode(', ', $zp_problems).")" : "")."</span>";
			}else{
				$error['update_zone_parcel'] = "<span class='label label-danger'>Failed to update zone parcel settings</span>";
			}
		}
	}

	$zone_data = array();
	$zone_effective = null;
	if($selected_zone_id > 0){
		$db->sql("SELECT id, name, parcel_per_km_price, parcel_base_price, parcel_max_weight_kg, parcel_is_service_available FROM zone WHERE id=".$selected_zone_id);
		$zr = $db->getResult();
		if(!empty($zr)){ $zone_data = $zr[0]; }
		$zone_effective = $fn->get_effective_parcel_settings($selected_zone_id);
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
                        <label for="shipping-service-available-button">Shipping Service Availability</label>
                        <div>
                            <input type="checkbox" id="shipping-service-available-button" class="js-switch" <?php echo $is_shipping_available == '1' ? 'checked' : ''; ?>>
                            <input type="hidden" name="is_shipping_available" id="is-shipping-available" value="<?php echo $is_shipping_available == '1' ? '1' : '0'; ?>">
                            <label for="shipping-service-available-button" style="margin-left:10px; font-weight:400;">Enabled - users can see and book Shipping (Truck/Auto) service. Turn off to hide the Shipping tab in the user app.</label>
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label>Daily Operating Hours</label><?php echo isset($error['operating_hours']) ? $error['operating_hours'] : '';?>
                        <div class="row" style="margin-top:5px;">
                            <div class="col-md-4">
                                <label for="opening_time" style="font-weight:400;">Opening Time</label>
                                <input type="time" class="form-control" id="opening_time" name="opening_time" value="<?php echo htmlspecialchars($opening_time_input); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="closing_time" style="font-weight:400;">Closing Time</label>
                                <input type="time" class="form-control" id="closing_time" name="closing_time" value="<?php echo htmlspecialchars($closing_time_input); ?>">
                            </div>
                        </div>
                        <p class="help-block" style="margin-bottom:0;">
                            Parcel pickup can only be booked between these times (store timezone).
                            Leave <strong>both</strong> blank to keep the service open 24 hours.
                            An overnight window is allowed, e.g. Opening 22:00 and Closing 06:00.
                            This applies on top of the availability switch above.
                        </p>
                    <div class="form-group col-md-12">
                        <h4>Parcel Payment Methods</h4>
                        <hr style="margin-top:5px; margin-bottom:15px;">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="parcel-cod-button">Cash on Delivery (COD) <small>[ Enable / Disable ]</small></label>
                                <div>
                                    <input type="checkbox" id="parcel-cod-button" class="js-switch" <?php echo $cod_payment_method == '1' ? 'checked' : ''; ?>>
                                    <input type="hidden" name="cod_payment_method" id="parcel-cod-payment-method" value="<?php echo $cod_payment_method == '1' ? '1' : '0'; ?>">
                                </div>
                                <p class="help-block" style="margin-top:5px;">When enabled, the User App Parcel Checkout screen shows both Cash on Delivery and Online Payment. When disabled, the User App calls online payment directly without showing method choices.</p>
                            </div>
                            <div class="col-md-6">
                                <label for="parcel-razorpay-button">Razorpay Online Payment <small>[ Enable / Disable ]</small></label>
                                <div>
                                    <input type="checkbox" id="parcel-razorpay-button" class="js-switch" <?php echo $razorpay_payment_method == '1' ? 'checked' : ''; ?>>
                                    <input type="hidden" name="razorpay_payment_method" id="parcel-razorpay-payment-method" value="<?php echo $razorpay_payment_method == '1' ? '1' : '0'; ?>">
                                </div>
                                <p class="help-block" style="margin-top:5px;">Enable online payment through Razorpay for parcel deliveries.</p>
                            </div>
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

<div class="row">
    <div class="col-md-8">
        <?php echo isset($error['update_zone_parcel']) ? $error['update_zone_parcel'] : '';?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Zone-wise Parcel Settings</h3>
                <h4 class="box-subtitle">Override the parcel tariff and pickup availability for a specific delivery zone. Customers whose pickup point falls inside a zone are charged that zone's tariff.</h4>
            </div>
            <form method="get">
                <div class="box-body">
                    <div class="form-group col-md-12">
                        <label for="zone_id">Select Zone</label>
                        <select class="form-control" id="zone_id" name="zone_id" onchange="this.form.submit()">
                            <option value="">-- Select Zone --</option>
                            <?php if(!empty($zone_options)){ foreach($zone_options as $zo){ ?>
                            <option value="<?php echo $zo['id'];?>" <?php if($selected_zone_id == $zo['id']){ echo "selected"; } ?>><?php echo htmlspecialchars($zo['name']);?></option>
                            <?php } } ?>
                        </select>
                    </div>
                </div>
            </form>
            <?php if(!empty($zone_data)){ ?>
            <form method="post">
                <input type="hidden" name="zone_id" value="<?php echo $selected_zone_id;?>">
                <div class="box-body">
                    <h4>Parcel Tariff &mdash; <?php echo htmlspecialchars($zone_data['name']);?></h4>
                    <p class="text-muted">Leave a field blank to use the global parcel setting (Per KM: &#8377;<?php echo $per_km_price;?>, Base: &#8377;<?php echo $base_price;?>, Max weight: <?php echo $max_weight_kg;?> kg).</p>
                    <div class="form-group col-md-4">
                        <label for="parcel_per_km_price">Per KM Price (&#8377;)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="parcel_per_km_price" value="<?php echo ($zone_data['parcel_per_km_price']===null||$zone_data['parcel_per_km_price']==='') ? '' : htmlspecialchars($zone_data['parcel_per_km_price']);?>" placeholder="Global: <?php echo $per_km_price;?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parcel_base_price">Base Price (&#8377;)</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="parcel_base_price" value="<?php echo ($zone_data['parcel_base_price']===null||$zone_data['parcel_base_price']==='') ? '' : htmlspecialchars($zone_data['parcel_base_price']);?>" placeholder="Global: <?php echo $base_price;?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="parcel_max_weight_kg">Max Weight Capacity (kg)</label>
                        <input type="number" step="0.1" min="0" class="form-control" name="parcel_max_weight_kg" value="<?php echo ($zone_data['parcel_max_weight_kg']===null||$zone_data['parcel_max_weight_kg']==='') ? '' : htmlspecialchars($zone_data['parcel_max_weight_kg']);?>" placeholder="Global: <?php echo $max_weight_kg;?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="zone-parcel-service-button">Pickup Service Availability in this Zone</label>
                        <div>
                            <input type="checkbox" id="zone-parcel-service-button" class="js-switch" <?php echo (!empty($zone_effective) && $zone_effective['is_service_available'] == 1) ? 'checked' : ''; ?>>
                            <input type="hidden" name="parcel_is_service_available" id="zone-parcel-is-service-available" value="<?php echo (!empty($zone_effective) && $zone_effective['is_service_available'] == 1) ? '1' : '0'; ?>">
                            <label for="zone-parcel-service-button" style="margin-left:10px; font-weight:400;">
                                <?php if(!empty($zone_effective) && in_array('is_service_available', $zone_effective['overrides'])): ?>
                                    Currently overridden for this zone. Turn off to block parcel pickup here.
                                <?php else: ?>
                                    Currently inheriting the global setting. Saving this zone makes the choice explicit for this zone only.
                                <?php endif; ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary" name="btnUpdateZoneParcel">Update Zone Parcel Settings</button>
                </div>
            </form>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    var pickupAvailableCheckbox = document.querySelector('#pickup-service-available-button');
    if (pickupAvailableCheckbox) {
        var init = new Switchery(pickupAvailableCheckbox);
        pickupAvailableCheckbox.onchange = function() {
            $('#is-service-available').val($(this).is(':checked') ? '1' : '0');
        };
    }
    var shippingAvailableCheckbox = document.querySelector('#shipping-service-available-button');
    if (shippingAvailableCheckbox) {
        var initShipping = new Switchery(shippingAvailableCheckbox);
        shippingAvailableCheckbox.onchange = function() {
            $('#is-shipping-available').val($(this).is(':checked') ? '1' : '0');
        };
    }
    var zoneParcelServiceCheckbox = document.querySelector('#zone-parcel-service-button');
    if (zoneParcelServiceCheckbox) {
        var init = new Switchery(zoneParcelServiceCheckbox);
        zoneParcelServiceCheckbox.onchange = function() {
            $('#zone-parcel-is-service-available').val($(this).is(':checked') ? '1' : '0');
        };
    }
    var parcelCodCheckbox = document.querySelector('#parcel-cod-button');
    if (parcelCodCheckbox) {
        var initCod = new Switchery(parcelCodCheckbox);
        parcelCodCheckbox.onchange = function() {
            $('#parcel-cod-payment-method').val($(this).is(':checked') ? '1' : '0');
        };
    }
    var parcelRzpCheckbox = document.querySelector('#parcel-razorpay-button');
    if (parcelRzpCheckbox) {
        var initRzp = new Switchery(parcelRzpCheckbox);
        parcelRzpCheckbox.onchange = function() {
            $('#parcel-razorpay-payment-method').val($(this).is(':checked') ? '1' : '0');
        };
    }
</script>

<?php $db->disconnect(); ?>
