<?php 
include_once('includes/functions.php'); 
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;

$error = array();
$success = "";

if (isset($_POST['btnAdd'])) {
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

    $image_path = "";
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
                $image_path = 'upload/images/' . $file_name;
            }
        }
    }

    if (empty($error)) {
        $sql = "INSERT INTO `shipping_vehicles` (`name`, `vehicle_type`, `image`, `capacity_kg`, `capacity_label`, `size_dimensions`, `base_price`, `base_km`, `per_km_price`, `helper_price`, `description`, `sort_order`, `status`) 
                VALUES ('$name', '$vehicle_type', '$image_path', $capacity_kg, '$capacity_label', '$size_dimensions', $base_price, $base_km, $per_km_price, $helper_price, '$description', $sort_order, $status)";
        if ($db->sql($sql)) {
            $success = "Shipping vehicle added successfully!";
        } else {
            $error['db'] = "Failed to add shipping vehicle: " . $db->get_error();
        }
    }
}
?>

<div class="content-header">
    <ol class="breadcrumb">
        <li><a href="shipping-vehicles.php"><i class="fa fa-truck"></i> Shipping Vehicles</a></li>
        <li class="active">Add Vehicle</li>
    </ol>
</div>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php if (!empty($success)) { ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <i class="fa fa-check-circle"></i> <?=$success?> <a href="shipping-vehicles.php">View Vehicles</a>
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
                <h3 class="box-title"><i class="fa fa-plus-circle"></i> Add Shipping Vehicle</h3>
            </div>

            <form method="post" enctype="multipart/form-data">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vehicle Display Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="e.g. 4-Wheeler / Tata Ace (Mini Truck)" required value="<?=isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''?>">
                                <?php if (!empty($error['name'])) { ?><span class="text-danger"><?=$error['name']?></span><?php } ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_type">Category / Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="vehicle_type" id="vehicle_type" required>
                                    <option value="auto">Auto / 3-Wheeler</option>
                                    <option value="four_wheeler" selected>4-Wheeler / Mini Truck (Tata Ace)</option>
                                    <option value="pickup_tempo">Pickup / 8ft Tempo</option>
                                    <option value="heavy_vehicle">Heavy Vehicle / Large Truck</option>
                                    <option value="other">Other Commercial Vehicle</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity_kg">Weight Capacity (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="capacity_kg" id="capacity_kg" placeholder="e.g. 1000" value="<?=isset($_POST['capacity_kg']) ? htmlspecialchars($_POST['capacity_kg']) : '1000'?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacity_label">Capacity Label</label>
                                <input type="text" class="form-control" name="capacity_label" id="capacity_label" placeholder="e.g. Up to 1 Ton (1000 kg)" value="<?=isset($_POST['capacity_label']) ? htmlspecialchars($_POST['capacity_label']) : 'Up to 1 Ton'?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="size_dimensions">Dimensions / Bed Size</label>
                                <input type="text" class="form-control" name="size_dimensions" id="size_dimensions" placeholder="e.g. 7ft x 4.5ft x 5ft" value="<?=isset($_POST['size_dimensions']) ? htmlspecialchars($_POST['size_dimensions']) : '7ft x 4.5ft x 5ft'?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="base_price">Base Fare (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="base_price" id="base_price" placeholder="e.g. 250" required value="<?=isset($_POST['base_price']) ? htmlspecialchars($_POST['base_price']) : '250.00'?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="base_km">Base Distance Included (km)</label>
                                <input type="number" step="0.01" class="form-control" name="base_km" id="base_km" placeholder="e.g. 2" required value="<?=isset($_POST['base_km']) ? htmlspecialchars($_POST['base_km']) : '2.00'?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="per_km_price">Per KM Fare (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="per_km_price" id="per_km_price" placeholder="e.g. 25" required value="<?=isset($_POST['per_km_price']) ? htmlspecialchars($_POST['per_km_price']) : '25.00'?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="helper_price">Helper Labor Rate (₹ / person)</label>
                                <input type="number" step="0.01" class="form-control" name="helper_price" id="helper_price" placeholder="e.g. 150" value="<?=isset($_POST['helper_price']) ? htmlspecialchars($_POST['helper_price']) : '150.00'?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_image">Vehicle Icon / Photo</label>
                                <input type="file" name="vehicle_image" id="vehicle_image" class="form-control" accept="image/*">
                                <?php if (!empty($error['vehicle_image'])) { ?><span class="text-danger"><?=$error['vehicle_image']?></span><?php } ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" class="form-control" name="sort_order" id="sort_order" value="<?=isset($_POST['sort_order']) ? htmlspecialchars($_POST['sort_order']) : '1'?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" name="status" id="status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Vehicle Description / Suitable For</label>
                        <textarea class="form-control" name="description" id="description" rows="3" placeholder="e.g. Best for household furniture, electronics, and medium commercial loads."><?=isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''?></textarea>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" name="btnAdd" class="btn btn-primary"><i class="fa fa-check"></i> Save Vehicle</button>
                    <a href="shipping-vehicles.php" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
