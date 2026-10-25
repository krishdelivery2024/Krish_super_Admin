<?php
include_once('includes/functions.php');
$fn = new custom_functions;

$message = '';
if (isset($_POST['btn_add'])) {
    $name = isset($_POST['name']) ? $db->escapeString($fn->xss_clean($_POST['name'])) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if (!empty($name)) {
        $sql = "INSERT INTO `shipping_goods_types` (`name`, `status`) VALUES ('$name', $status)";
        if ($db->sql($sql)) {
            $message = "<div class='alert alert-success'>Cargo / Goods type added successfully! <a href='shipping-goods-types.php'>View All</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to add cargo type.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please enter cargo type name.</div>";
    }
}
?>

<section class="content-header">
    <h1>Add Cargo / Goods Type</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="shipping-goods-types.php">Cargo Types</a></li>
        <li class="active">Add New</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">New Cargo / Goods Type Details</h3>
                </div>
                <form method="post" action="">
                    <div class="box-body">
                        <?= $message ?>
                        <div class="form-group">
                            <label for="name">Cargo / Goods Type Name *</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="e.g. Household Appliances" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="btn_add" class="btn btn-primary"><i class="fa fa-check"></i> Add Cargo Type</button>
                        <a href="shipping-goods-types.php" class="btn btn-default pull-right">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
