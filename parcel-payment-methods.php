<?php
$page = "Parcel Payment Methods Settings";
include "header.php"; ?>
    <?php
    $fn = new custom_functions();
    $db = new Database();
    $db->connect();
    $data = $fn->get_settings('parcel_payment_methods', true);
    // Also fetch global payment_methods as fallback/reference for razorpay keys
    $global_pm = $fn->get_settings('payment_methods', true);

    $message = '';

    if (isset($_POST) && isset($_POST['btn_update'])) {
        unset($_POST['btn_update']);
        $cleaned = $fn->xss_clean_array($_POST);
        $cod = isset($cleaned['cod_payment_method']) ? $cleaned['cod_payment_method'] : '0';
        $razorpay = isset($cleaned['razorpay_payment_method']) ? $cleaned['razorpay_payment_method'] : '1';

        $data = $cleaned;
        $json_data = json_encode($data);

        // Update settings table
        $sql = "SELECT id FROM `settings` WHERE `variable`='parcel_payment_methods'";
        $db->sql($sql);
        $res = $db->getResult();
        if (empty($res)) {
            $sql = "INSERT INTO `settings`(`variable`, `value`) VALUES ('parcel_payment_methods','$json_data')";
            $db->sql($sql);
        } else {
            $sql = "UPDATE `settings` SET `value`='$json_data' WHERE `variable`='parcel_payment_methods'";
            $db->sql($sql);
        }

        // Also sync to parcel_settings table columns
        $sql = "UPDATE `parcel_settings` SET `cod_payment_method` = '$cod', `razorpay_payment_method` = '$razorpay' WHERE id = 1";
        $db->sql($sql);

        $message = "<div class='alert alert-success'>Parcel payment methods updated successfully!</div>";
        $db->disconnect();
    }

    $cod_val = (isset($data['cod_payment_method'])) ? $data['cod_payment_method'] : '0';
    $razorpay_val = (isset($data['razorpay_payment_method'])) ? $data['razorpay_payment_method'] : '1';
    $rzp_key = !empty($data['razorpay_key']) ? $data['razorpay_key'] : (!empty($global_pm['razorpay_key']) ? $global_pm['razorpay_key'] : '');
    $rzp_secret = !empty($data['razorpay_secret_key']) ? $data['razorpay_secret_key'] : (!empty($global_pm['razorpay_secret_key']) ? $global_pm['razorpay_secret_key'] : '');
    ?>
    <section class="content-header">
        <h1>Parcel Payment Methods <small><a href="home.php"><i class="fa fa-home"></i> Home</a></small></h1>
        <ol class="breadcrumb">
            <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="parcel-settings.php">Parcel Pickup</a></li>
            <li class="active">Payment Methods</li>
        </ol>
    </section>

    <section class="content">
        <?php if (!empty($message)) { echo $message; } ?>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Parcel Service Payment Methods Settings</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-12">
                            <form method="post" enctype="multipart/form-data">
                                <h5>Cash on Delivery (COD) Payments</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="cod_payment_method">COD Payments <small>[ Enable / Disable ]</small></label><br>
                                    <input type="checkbox" id="cod_payment_method_btn" class="js-switch" <?php if ($cod_val == '1') { echo 'checked'; } ?>>
                                    <input type="hidden" id="cod_payment_method" name="cod_payment_method" value="<?= htmlspecialchars($cod_val); ?>">
                                    <p class="help-block">When enabled, the Parcel Checkout screen in the User App shows both Cash on Delivery and Online Payment. When disabled, the User App directly prompts online payment via Razorpay.</p>
                                </div>
                                <hr>

                                <h5>Razorpay Online Payments</h5>
                                <hr>
                                <div class="form-group">
                                    <label for="razorpay_payment_method">Razorpay Payments <small>[ Enable / Disable ]</small></label><br>
                                    <input type="checkbox" id="razorpay_payment_method_btn" class="js-switch" <?php if ($razorpay_val == '1') { echo 'checked'; } ?>>
                                    <input type="hidden" id="razorpay_payment_method" name="razorpay_payment_method" value="<?= htmlspecialchars($razorpay_val); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="razorpay_key">Razorpay Key ID</label>
                                    <input type="text" class="form-control" name="razorpay_key" value="<?= htmlspecialchars($rzp_key); ?>" placeholder="Razorpay Key ID" />
                                    <p class="help-block">Uses global system Razorpay key by default if left unchanged.</p>
                                </div>
                                <div class="form-group">
                                    <label for="razorpay_secret_key">Razorpay Secret Key</label>
                                    <input type="text" class="form-control" name="razorpay_secret_key" value="<?= htmlspecialchars($rzp_secret); ?>" placeholder="Razorpay Secret Key" />
                                </div>
                                <hr>

                                <div class="box-footer">
                                    <input type="submit" class="btn-primary btn" value="Update Parcel Payment Methods" name="btn_update" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        var codBtn = document.querySelector('#cod_payment_method_btn');
        if (codBtn) {
            var switcheryCod = new Switchery(codBtn, { color: '#16D39A', secondaryColor: '#F55252' });
            codBtn.onchange = function() {
                $('#cod_payment_method').val($(this).is(':checked') ? 1 : 0);
            };
        }

        var rzpBtn = document.querySelector('#razorpay_payment_method_btn');
        if (rzpBtn) {
            var switcheryRzp = new Switchery(rzpBtn, { color: '#16D39A', secondaryColor: '#F55252' });
            rzpBtn.onchange = function() {
                $('#razorpay_payment_method').val($(this).is(':checked') ? 1 : 0);
            };
        }
    </script>

<?php include "footer.php"; ?>
