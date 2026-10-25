<?php 
include_once('includes/functions.php'); 
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo "<div class='alert alert-danger'>Invalid Vehicle ID.</div>";
    return;
}

$error = array();
$success = "";

if (isset($_POST['btnUpdate'])) {
    $name            = $db->escapeString($fn->xss_clean($_POST['name']));
    $vehicle_type    = $db->escapeString($fn->xss_clean($_POST['vehicle_type']));
    $capacity_kg     = (float)$_POST['capacity_kg'];
    $capacity_label  = $db->escapeString($fn->xss_clean($_POST['capacity_label']));
    $size_dimensions = $db->escapeString($fn->xss_clean($_POST['size_dimensions']));
    $base_price      = (float)$_POST['base_price'];
    $base_km         = (float)$_POST['base_km'];
    $per_km_price    = (float)$_POST['per_km_price'];
    $helper_price    = (float)$_POST['helper_price'];
    $sort_order      = (int)$_POST['sort_order'];
    $status          = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    $description     = $db->escapeString($fn->xss_clean($_POST['description']));

    if (empty($name)) {
        $error['name'] = "Vehicle name is required.";
    }

    $image_sql = "";
    if (isset($_FILES['vehicle_image']) && !empty($_FILES['vehicle_image']['name']) && $_FILES['vehicle_image']['error'] == 0) {
        $allowedExts = array("gif", "jpeg", "jpg", "png", "webp");
        $parts = explode(".", $_FILES["vehicle_image"]["name"]);
        $extension = strtolower(end($parts));
        if (!in_array($extension, $allowedExts)) {
            $error['vehicle_image'] = "Image type must be JPG, JPEG, PNG, or WEBP.";
        } else {
            $file_name = "veh_" . time() . "_" . rand(1000, 9999) . "." . $extension;
            if (!is_dir('upload/images')) {
                mkdir('upload/images', 0777, true);
            }
            if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], 'upload/images/' . $file_name)) {
                $image_sql = ", `image` = 'upload/images/" . $file_name . "'";
            }
        }
    }

    if (empty($error)) {
        $sql = "UPDATE `shipping_vehicles` SET 
                `name` = '$name', 
                `vehicle_type` = '$vehicle_type', 
                `capacity_kg` = $capacity_kg, 
                `capacity_label` = '$capacity_label', 
                `size_dimensions` = '$size_dimensions', 
                `base_price` = $base_price, 
                `base_km` = $base_km, 
                `per_km_price` = $per_km_price, 
                `helper_price` = $helper_price, 
                `description` = '$description', 
                `sort_order` = $sort_order, 
                `status` = $status 
                $image_sql 
                WHERE `id` = $id";
        if ($db->sql($sql)) {
            $success = "Shipping vehicle updated successfully!";
        } else {
            $error['db'] = "Failed to update shipping vehicle.";
        }
    }
}

// Fetch current details
$sql_fetch = "SELECT * FROM `shipping_vehicles` WHERE `id` = $id LIMIT 1";
$db->sql($sql_fetch);
$res_veh = $db->getResult();
if (empty($res_veh)) {
    echo "<div class='alert alert-danger'>Vehicle record not found.</div>";
    return;
}
$veh = $res_veh[0];
?>

<div class="content-header">
    <ol class="breadcrumb">
        <li><a href="shipping-vehicles.php"><i class="fa fa-truck"></i> Shipping Vehicles</a></li>
        <li class="active">Edit Vehicle</li>
    </ol>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php if (!empty($success)) { ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="fa fa-check-circle"></i> <?=$success?> <a href="shipping-vehicles.php">Back to list</a>
            </div>
        <?php } ?>
        <?php if (!empty($error['db'])) { ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="fa fa-exclamation-triangle"></i> <?=$error['db']?>
            </div>
        <?php } ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-edit"></i> Edit Shipping Vehicle #<?=$id?></h3>
            </div>

            <form method="post" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vehicle Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" required value="<?=htmlspecialchars($veh['name'])?>">
                                <?php if (!empty($error['name'])) { ?><span class="text-danger"><?=$error['name']?></span><?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_type">Category / Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="vehicle_type" id="vehicle_type" required>
                                    <option value="auto" <?=$veh['vehicle_type'] == 'auto' ? 'selected' : ''?>>Auto / 3-Wheeler</option>
                                    <option value="four_wheeler" <?=$veh['vehicle_type'] == 'four_wheeler' ? 'selected' : ''?>>4-Wheeler / Mini Truck (Tata Ace)</option>
                                    <option value="pickup_tempo" <?=$veh['vehicle_type'] == 'pickup_tempo' ? 'selected' : ''?>>Pickup / 8ft Tempo</option>
                                    <option value="heavy_vehicle" <?=$veh['vehicle_type'] == 'heavy_vehicle' ? 'selected' : ''?>>Heavy Vehicle / Large Truck</option>
                                    <option value="other" <?=$veh['vehicle_type'] == 'other' ? 'selected' : ''?>>Other Commercial Vehicle</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity_kg">Weight Capacity (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="capacity_kg" id="capacity_kg" value="<?=$veh['capacity_kg']?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity_label">Capacity Label</label>
                                <input type="text" class="form-control" name="capacity_label" id="capacity_label" value="<?=htmlspecialchars($veh['capacity_label'])?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="size_dimensions">Dimensions / Bed Size</label>
                                <input type="text" class="form-control" name="size_dimensions" id="size_dimensions" value="<?=htmlspecialchars($veh['size_dimensions'])?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="base_price">Base Fare (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="base_price" id="base_price" required value="<?=$veh['base_price']?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="base_km">Base Distance Included (km)</label>
                                <input type="number" step="0.01" class="form-control" name="base_km" id="base_km" required value="<?=$veh['base_km']?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="per_km_price">Per KM Fare (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="per_km_price" id="per_km_price" required value="<?=$veh['per_km_price']?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="helper_price">Helper Labor Rate (₹ / person)</label>
                                <input type="number" step="0.01" class="form-control" name="helper_price" id="helper_price" value="<?=$veh['helper_price']?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_image">Vehicle Icon / Photo</label>
                                <input type="file" name="vehicle_image" id="vehicle_image" class="form-control" accept="image/*">
                                <?php if (!empty($veh['image'])) { ?>
                                    <div style="margin-top:8px;">
                                        <img src="<?=((strpos($veh['image'], 'http') === 0) ? $veh['image'] : DOMAIN_URL . ltrim($veh['image'], '/'))?>" style="height:50px;max-width:100px;object-fit:contain;border:1px solid #ccc;padding:2px;border-radius:4px;">
                                        <small class="text-muted">Current image</small>
                                    </div>
                                <?php } ?>
                                <?php if (!empty($error['vehicle_image'])) { ?><span class="text-danger"><?=$error['vehicle_image']?></span><?php } ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" id="sort_order" value="<?=$veh['sort_order']?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="1" <?=$veh['status'] == 1 ? 'selected' : ''?>>Active</option>
                                    <option value="0" <?=$veh['status'] == 0 ? 'selected' : ''?>>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Vehicle Description / Suitable For</label>
                        <textarea class="form-control" name="description" id="description" rows="3"><?=htmlspecialchars($veh['description'])?></textarea>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" name="btnUpdate" class="btn btn-primary"><i class="fa fa-save"></i> Update Vehicle</button>
                    <a href="shipping-vehicles.php" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
