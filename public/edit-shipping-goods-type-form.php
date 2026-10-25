<?php
include_once('includes/functions.php');
$fn = new custom_functions;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header("location:shipping-goods-types.php");
    exit();
}

$message = '';
if (isset($_POST['btn_update'])) {
    $name = isset($_POST['name']) ? $db->escapeString($fn->xss_clean($_POST['name'])) : '';
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if (!empty($name)) {
        $sql = "UPDATE `shipping_goods_types` SET `name` = '$name', `status` = $status WHERE `id` = $id";
        if ($db->sql($sql)) {
            $message = "<div class='alert alert-success'>Cargo / Goods type updated successfully! <a href='shipping-goods-types.php'>View All</a></div>";
        } else {
            $message = "<div class='alert alert-danger'>Failed to update cargo type.</div>";
        }
    } else {
        $message = "<div class='alert alert-warning'>Please enter cargo type name.</div>";
    }
}

$db->sql("SELECT * FROM `shipping_goods_types` WHERE `id` = $id");
$res = $db->getResult();
if (empty($res)) {
    echo "<div class='alert alert-danger'>Cargo type not found!</div>";
    exit();
}
$data = $res[0];
?>

<section class="content-header">
    <h1>Edit Cargo / Goods Type</h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        <li><a href="shipping-goods-types.php">Cargo Types</a></li>
        <li class="active">Edit</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Cargo / Goods Type Details</h3>
                </div>
                <form method="post" action="">
                    <div class="box-body">
                        <?= $message ?>
                        <div class="form-group">
                            <label for="name">Cargo / Goods Type Name *</label>
                            <input type="text" class="form-control" name="name" id="name" value="<?= htmlspecialchars($data['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="1" <?= ($data['status'] == 1) ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($data['status'] == 0) ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button type="submit" name="btn_update" class="btn btn-primary"><i class="fa fa-save"></i> Save Changes</button>
                        <a href="shipping-goods-types.php" class="btn btn-default pull-right">Back</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
