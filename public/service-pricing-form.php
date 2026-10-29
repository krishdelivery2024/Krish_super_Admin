<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;

$message = '';
$message_type = '';

if (isset($_POST['btnUpdatePricing'])) {
    if (!checkadmin($auth_username)) {
        $message = "Access denied - You are not authorized to update settings.";
        $message_type = "danger";
    } else {
        $pricing_data = isset($_POST['pricing']) && is_array($_POST['pricing']) ? $_POST['pricing'] : array();
        $updated_count = 0;

        foreach ($pricing_data as $id => $fields) {
            $id = intval($id);
            $base_price = floatval($fields['base_price'] ?? 0);
            $base_km = floatval($fields['base_km'] ?? 1);
            $per_km_price = floatval($fields['per_km_price'] ?? 0);
            $status = isset($fields['status']) ? 1 : 0;

            $sql = "UPDATE `service_pricing` SET 
                `base_price` = {$base_price},
                `base_km` = {$base_km},
                `per_km_price` = {$per_km_price},
                `status` = {$status}
                WHERE `id` = {$id} AND `service_type` = 'passenger'";
            if ($db->sql($sql)) {
                $updated_count++;
            }
        }

        $message = "Passenger Ride Pricing updated successfully! ({$updated_count} vehicle types saved)";
        $message_type = "success";
    }
}

// Fetch passenger pricing rows
$db->sql("SELECT * FROM `service_pricing` WHERE `service_type` = 'passenger' ORDER BY id ASC");
$pricing_rows = $db->getResult();

$vehicle_icons = array(
    'bike' => 'fa-motorcycle text-yellow',
    'auto' => 'fa-taxi text-orange',
    'cab'  => 'fa-car text-blue'
);
?>

<section class="content-header">
    <h1><i class="fa fa-car"></i> Passenger Ride Pricing</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="parcel-requests.php">Pickup &amp; Shipping</a></li>
        <li class="active">Passenger Ride Pricing</li>
    </ol>
</section>

<section class="content">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?=$message_type?> alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <i class="icon fa fa-<?=($message_type=='success'?'check':'ban')?>"></i> <?=$message?>
        </div>
    <?php endif; ?>

    <div class="alert alert-info" style="border-left: 5px solid #0097a7; background-color: #e0f7fa; color: #006064;">
        <h4><i class="fa fa-info-circle"></i> Service Pricing Overview &amp; Existing Sections:</h4>
        <p style="margin-bottom: 5px;">All services have dedicated configuration sections to avoid duplicate pricing:</p>
        <ul style="margin-bottom: 0; padding-left: 20px;">
            <li><strong>Passenger Rides (Bike Taxi, Auto, Cab):</strong> Configured right below on this page.</li>
            <li><strong>Parcel Delivery Pricing:</strong> Configured under <a href="parcel-settings.php" style="color: #004d40; font-weight: bold; text-decoration: underline;">Parcel Settings</a> (includes global &amp; per-zone rates).</li>
            <li><strong>Shipping / Freight Vehicles:</strong> Configured under <a href="shipping-vehicles.php" style="color: #004d40; font-weight: bold; text-decoration: underline;">Shipping Vehicles</a> (Auto, Tata Ace, Tempo, Trucks).</li>
            <li><strong>Food Delivery Pricing:</strong> Configured under <a href="delivery_method.php" style="color: #004d40; font-weight: bold; text-decoration: underline;">Delivery Method</a> (First KM &amp; Per KM rates).</li>
            <li><strong>Platform Fee &amp; Convenience Fee:</strong> Configured under <a href="zone-settings.php" style="color: #004d40; font-weight: bold; text-decoration: underline;">Zone Settings</a> and automatically applied to all services.</li>
        </ul>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-money"></i> Passenger Vehicle Fares</h3>
            <p class="text-muted" style="margin-top: 5px;">
                Set the <strong>Base Price</strong>, <strong>Included Base KM</strong>, and <strong>Per KM Price</strong> for Passenger Pickup &amp; Drop vehicle types.
            </p>
        </div>

        <form method="POST" action="">
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" style="vertical-align: middle;">
                        <thead style="background: #f4f6f9;">
                            <tr>
                                <th style="width: 280px;">Vehicle Option</th>
                                <th style="width: 170px;">Base Fare (₹)</th>
                                <th style="width: 150px;">Base Distance (KM)</th>
                                <th style="width: 170px;">Per KM Fare (₹)</th>
                                <th style="width: 100px; text-align: center;">Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pricing_rows as $row): 
                                $id = $row['id'];
                                $icon = isset($vehicle_icons[$row['vehicle_type']]) ? $vehicle_icons[$row['vehicle_type']] : 'fa-car';
                            ?>
                            <tr>
                                <td>
                                    <h4 style="margin: 0 0 5px 0;">
                                        <i class="fa <?=$icon?>"></i> <strong><?=htmlspecialchars($row['display_name'])?></strong>
                                    </h4>
                                    <span class="label label-default" style="text-transform: uppercase; font-size: 11px;">
                                        Type: <?=htmlspecialchars($row['vehicle_type'])?>
                                    </span>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">₹</span>
                                        <input type="number" step="0.01" min="0" class="form-control" 
                                               name="pricing[<?=$id?>][base_price]" 
                                               value="<?=htmlspecialchars($row['base_price'])?>" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" step="0.1" min="0" class="form-control" 
                                               name="pricing[<?=$id?>][base_km]" 
                                               value="<?=htmlspecialchars($row['base_km'])?>" required>
                                        <span class="input-group-addon">km</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">₹</span>
                                        <input type="number" step="0.01" min="0" class="form-control" 
                                               name="pricing[<?=$id?>][per_km_price]" 
                                               value="<?=htmlspecialchars($row['per_km_price'])?>" required>
                                    </div>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <label class="switch" style="cursor: pointer;">
                                        <input type="checkbox" name="pricing[<?=$id?>][status]" value="1" <?=($row['status']==1?'checked':'')?>>
                                        <span class="text-success" style="font-size: 18px;">✔</span>
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="box-footer" style="padding: 16px 20px;">
                <button type="submit" name="btnUpdatePricing" class="btn btn-primary btn-lg">
                    <i class="fa fa-save"></i> Save Passenger Pricing
                </button>
                <a href="home.php" class="btn btn-default btn-lg pull-right">Back to Dashboard</a>
            </div>
        </form>
    </div>
</section>
