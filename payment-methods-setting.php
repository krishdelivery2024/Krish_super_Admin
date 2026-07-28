<?php
$page="Payment Methods Settings";
include "header.php"; ?>
    <?php
    $fn = new custom_functions();
    $db = new Database();
    $db->connect();
    $data = $fn->get_settings('payment_methods', true);
    // print_r($data);
    $message = '';

    if (isset($_POST) && isset($_POST['btn_update'])) {
        unset($_POST['btn_update']);
        // print_r(json_encode($_POST));
        if (empty($data)) {
            $data = $fn->xss_clean_array($_POST);
            $json_data = json_encode($data);
            $sql = "INSERT INTO `settings`(`variable`, `value`) VALUES ('payment_methods','$json_data')";
            $db->sql($sql);
            $message = "<div class='alert alert-success'> Settings created successfully!</div>";
        } else {
            $data = $fn->xss_clean_array($_POST);
            $json_data = json_encode($data);
            $sql = "UPDATE `settings` SET `value`='$json_data' WHERE `variable`='payment_methods'";
            $db->sql($sql);
            $message = "<div class='alert alert-success'> Settings updated successfully!</div>";
        }
        // echo $sql;
        $db->disconnect();
    }

    ?>
    <?php if ($permissions['settings']['read'] == 1) { ?>
            <div class="row">
                <div class="col-md-6">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Payment Methods Settings</h3>
                        </div>
                        <!-- /.box-header -->
                        <!-- form start -->
                        <div class="box-body">
                            <div class="col-md-12">
                                <form method="post" enctype="multipart/form-data">
                                    <h5>COD Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        
                                        <label for="cod_payment_method">COD Payments <small>[ Enable / Disable ] </small></label><br>
                                        
                                        <input type="checkbox" id="cod_payment_method_btn" class="js-switch" <?php if (!empty($data['cod_payment_method']) && $data['cod_payment_method'] == '1') { echo 'checked'; } ?>>
                                        
                                        <input type="hidden" id="cod_payment_method" name="cod_payment_method" value="<?= (isset($data['cod_payment_method']) && !empty($data['cod_payment_method'])) ? $data['cod_payment_method'] : 0; ?>">
                                    </div>
                                    <hr>
                                    <h5>Razorpay Payments </h5>
                                    <hr>
                                    <div class="form-group" >
                                        <label for="razorpay_payment_method">Razorpay Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="razorpay_payment_method_btn" class="js-switch" <?php if (!empty($data['razorpay_payment_method']) && $data['razorpay_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="razorpay_payment_method" name="razorpay_payment_method" value="<?= (isset($data['razorpay_payment_method']) && !empty($data['razorpay_payment_method'])) ? $data['razorpay_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="razorpay_key">Razorpay key ID</label>
                                        <input type="text" class="form-control" name="razorpay_key" value="<?= (isset($data['razorpay_key'])) ? $data['razorpay_key'] : '' ?>" placeholder="Razor Key ID" />
                                    </div>
                                    <div class="form-group" >
                                        <label for="razorpay_secret_key">Secret Key</label>
                                        <input type="text" class="form-control" name="razorpay_secret_key" value="<?= (isset($data['razorpay_secret_key'])) ? $data['razorpay_secret_key'] : '' ?>" placeholder="Razorpay Secret Key " />
                                    </div>
                                    <hr>
                                    
                                    <div class="all-pay" style="display:none;">
                                    <h5>Paypal Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="paypal_payment_method">Paypal Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="paypal_payment_method_btn" class="js-switch" <?php if (!empty($data['paypal_payment_method']) && $data['paypal_payment_method'] == '1') {
                                                                                                                    echo 'checked';
                                                                                                                } ?>>
                                        <input type="hidden" id="paypal_payment_method" name="paypal_payment_method" value="<?= (isset($data['paypal_payment_method']) && !empty($data['paypal_payment_method'])) ? $data['paypal_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paypal_mode" class="form-control" required>
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="paypal_business_email">Paypal Business Email</label>
                                        <input type="text" class="form-control" name="paypal_business_email" value="<?= (isset($data['paypal_business_email'])) ? $data['paypal_business_email'] : '' ?>" placeholder="Paypal Business Email" />
                                    </div>
                                    <hr>
                                    <h5>PayUMoney Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="payumoney_payment_method">PayUMoney Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="payumoney_payment_method_btn" class="js-switch" <?php if (!empty($data['payumoney_payment_method']) && $data['payumoney_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="payumoney_payment_method" name="payumoney_payment_method" value="<?= (isset($data['payumoney_payment_method']) && !empty($data['payumoney_payment_method'])) ? $data['payumoney_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="">Payment Mode <small>[ sandbox / live ]</small></label>
                                        <select name="paypal_mode" class="form-control" required>
                                            <option value="">Select Mode </option>
                                            <option value="sandbox" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'sandbox') ? "selected" : "" ?>>Sandbox ( Testing )</option>
                                            <option value="production" <?= (isset($data['paypal_mode']) && $data['paypal_mode'] == 'production') ? "selected" : "" ?>>Production ( Live )</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_key">Merchant key</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_key" value="<?= (isset($data['payumoney_merchant_key'])) ? $data['payumoney_merchant_key'] : '' ?>" placeholder="PayUMoney Merchant Key" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="payumoney_merchant_id" value="<?= (isset($data['payumoney_merchant_id'])) ? $data['payumoney_merchant_id'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="payumoney_salt">Salt</label>
                                        <input type="text" class="form-control" name="payumoney_salt" value="<?= (isset($data['payumoney_salt'])) ? $data['payumoney_salt'] : '' ?>" placeholder="PayUMoney Merchant ID" />
                                    </div>
                                    <hr>
                                    
                                    <h5>InstaMojo Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="upi_payment_method">InstaMojo Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="instamojo_payment_method_btn" class="js-switch" <?php if (!empty($data['instamojo_payment_method']) && $data['instamojo_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="instamojo_payment_method" name="instamojo_payment_method" value="<?= (isset($data['instamojo_payment_method']) && !empty($data['instamojo_payment_method'])) ? $data['instamojo_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="instamojo_client_id">InstaMojo Client ID</label>
                                        <input type="text" class="form-control" name="instamojo_client_id" value="<?= (isset($data['instamojo_client_id'])) ? $data['instamojo_client_id'] : '' ?>" placeholder="InstaMojo Client ID " />
                                    </div>
                                    <div class="form-group">
                                        <label for="instamojo_client_secret">InstaMojo Salt</label>
                                        <input type="text" class="form-control" name="instamojo_client_secret" value="<?= (isset($data['instamojo_client_secret'])) ? $data['instamojo_client_secret'] : '' ?>" placeholder="InstaMojo Client Secret " />
                                    </div>
                                    
                                    <div class="form-group" style="display:none">
                                        <label for="merchant_id">InstaMojo API Key</label>
                                        <input type="text" class="form-control" name="instamojo_api_key" value="<?= (isset($data['instamojo_api_key'])) ? $data['instamojo_api_key'] : '' ?>" placeholder="InstaMojo API Key" />
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="upi_id">InstaMojo Auth Token</label>
                                        <input type="text" class="form-control" name="instamojo_auth_token" value="<?= (isset($data['instamojo_auth_token'])) ? $data['instamojo_auth_token'] : '' ?>" placeholder="InstaMojo Auth Token" />
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="upi_secret_key">InstaMojo Salt</label>
                                        <input type="text" class="form-control" name="instamojo_salt" value="<?= (isset($data['instamojo_salt'])) ? $data['instamojo_salt'] : '' ?>" placeholder="InstaMojo Salt " />
                                    </div>
                                    <hr>
                                    
                                    <h5>CCAvenue Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="ccavenue_payment_method">CCAvenue Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="ccavenue_payment_method_btn" class="js-switch" <?php if (!empty($data['ccavenue_payment_method']) && $data['ccavenue_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="ccavenue_payment_method" name="ccavenue_payment_method" value="<?= (isset($data['ccavenue_payment_method']) && !empty($data['ccavenue_payment_method'])) ? $data['ccavenue_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="ccavenue_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="ccavenue_merchant_id" value="<?= (isset($data['ccavenue_merchant_id'])) ? $data['ccavenue_merchant_id'] : '' ?>" placeholder="Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="ccavenue_access_code">Access Code</label>
                                        <input type="text" class="form-control" name="ccavenue_access_code" value="<?= (isset($data['ccavenue_access_code'])) ? $data['ccavenue_access_code'] : '' ?>" placeholder="Access Code " />
                                    </div>
                                    <div class="form-group">
                                        <label for="ccavenue_working_key">Working Key</label>
                                        <input type="text" class="form-control" name="ccavenue_working_key" value="<?= (isset($data['ccavenue_working_key'])) ? $data['ccavenue_working_key'] : '' ?>" placeholder="Working Key " />
                                    </div>
                                    <div class="form-group">
                                        <label for="ccavenue_currency">Currency</label>
                                        <input type="text" class="form-control" name="ccavenue_currency" value="<?= (isset($data['ccavenue_currency'])) ? $data['ccavenue_currency'] : '' ?>" placeholder="Curency " />
                                    </div>
                                    <hr>
                                    </div><!-- all-pay-end-here -->
                                    
                                    <h5 style="display:none">Google Pay UPI Payments </h5>
                                    <hr>
                                    <div class="form-group" style="display:none">
                                        <label for="upi_payment_method">UPI Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="upi_payment_method_btn" class="js-switch" <?php if (!empty($data['upi_payment_method']) && $data['upi_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="upi_payment_method" name="upi_payment_method" value="<?= (isset($data['upi_payment_method']) && !empty($data['upi_payment_method'])) ? $data['upi_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="merchant_id" value="<?= (isset($data['merchant_id'])) ? $data['merchant_id'] : '' ?>" placeholder="Merchant ID" />
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="upi_id">UPI ID</label>
                                        <input type="text" class="form-control" name="upi_id" value="<?= (isset($data['upi_id'])) ? $data['upi_id'] : '' ?>" placeholder="UPI ID" />
                                    </div>
                                    <div class="form-group" style="display:none">
                                        <label for="upi_secret_key">UPI Name</label>
                                        <input type="text" class="form-control" name="upi_secret_key" value="<?= (isset($data['upi_secret_key'])) ? $data['upi_secret_key'] : '' ?>" placeholder="UPI Name " />
                                    </div>
									<div class="form-group" style="display:none">
                                        <label for="upi_notes">UPI Notes</label>
                                        <input type="text" class="form-control" name="upi_notes" value="<?= (isset($data['upi_notes'])) ? $data['upi_notes'] : '' ?>" placeholder="UPI Notes" />
                                    </div>
                                    
                                    <hr style="display:none">
                                    
                                    <!--Phonepay-->
                                    <h5>PhonePe Payments </h5>
                                    <hr>
                                    <div class="form-group">
                                        <label for="phonepe_payment_method">PhonePe Payments <small>[ Enable / Disable ] </small></label><br>
                                        <input type="checkbox" id="phonepe_payment_method_btn" class="js-switch" <?php if (!empty($data['phonepe_payment_method']) && $data['phonepe_payment_method'] == '1') {
                                                                                                                        echo 'checked';
                                                                                                                    } ?>>
                                        <input type="hidden" id="phonepe_payment_method" name="phonepe_payment_method" value="<?= (isset($data['phonepe_payment_method']) && !empty($data['phonepe_payment_method'])) ? $data['phonepe_payment_method'] : 0; ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="phonepe_merchant_id">Merchant ID</label>
                                        <input type="text" class="form-control" name="phonepe_merchant_id" value="<?= (isset($data['phonepe_merchant_id'])) ? $data['phonepe_merchant_id'] : '' ?>" placeholder="Merchant ID" />
                                    </div>
                                    <div class="form-group">
                                        <label for="phonepe_secret_key">Secret key</label>
                                        <input type="text" class="form-control" name="phonepe_secret_key" value="<?= (isset($data['phonepe_secret_key'])) ? $data['phonepe_secret_key'] : '' ?>" placeholder="Secret key" />
                                    </div>
                                    
                                    <hr>
                                    
                                    
                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update" />
                                </form>
                                </br>
                            </div>
                        </div>
                    </div>
                    <!-- /.box -->
                </div>
            </div>
    <?php } else { ?>
        <div class="alert alert-danger">You have no permission to view settings</div>
    <?php } ?>
    <div class="separator"> </div>

</body>

</html>
<?php include "footer.php"; ?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="dist/plugin/switchery/switchery.js"></script>
<script type="text/javascript">
    CKEDITOR.replace('contact_us');
</script>
<script type="text/javascript">
    var changeCheckbox = document.querySelector('#cod_payment_method_btn');
    var changeCheckbox1 = document.querySelector('#paypal_payment_method_btn');
    var changeCheckbox2 = document.querySelector('#payumoney_payment_method_btn');
    var changeCheckbox3 = document.querySelector('#razorpay_payment_method_btn');
    var changeCheckbox4 = document.querySelector('#upi_payment_method_btn');
    var changeCheckbox5 = document.querySelector('#instamojo_payment_method_btn');
    var changeCheckbox6 = document.querySelector('#ccavenue_payment_method_btn');
    var changeCheckbox7 = document.querySelector('#phonepe_payment_method_btn');
    var init = new Switchery(changeCheckbox);
    var init1 = new Switchery(changeCheckbox1);
    var init2 = new Switchery(changeCheckbox2);
    var init3 = new Switchery(changeCheckbox3);
    var init4 = new Switchery(changeCheckbox4);
    var init5 = new Switchery(changeCheckbox5);
    var init6 = new Switchery(changeCheckbox6);
    var init7 = new Switchery(changeCheckbox7);
    changeCheckbox.onchange = function() {
        // alert(changeCheckbox1.checked);
        if (changeCheckbox.checked)
            $('#cod_payment_method').val(1);
        else
            $('#cod_payment_method').val(0);
    };
    
    /* paypal change button value */
    changeCheckbox1.onchange = function() {
        // alert(changeCheckbox1.checked);
        if (changeCheckbox1.checked)
            $('#paypal_payment_method').val(1);
        else
            $('#paypal_payment_method').val(0);
    };

    /* payumoney change button value */
    changeCheckbox2.onchange = function() {
        if (changeCheckbox2.checked)
            $('#payumoney_payment_method').val(1);
        else
            $('#payumoney_payment_method').val(0);
    };

    /* razorpay change button value */
    changeCheckbox3.onchange = function() {
        if (changeCheckbox3.checked)
            $('#razorpay_payment_method').val(1);
        else
            $('#razorpay_payment_method').val(0);
    };
	/* UPI change button value */
    changeCheckbox4.onchange = function() {
        if (changeCheckbox4.checked)
            $('#upi_payment_method').val(1);
        else
            $('#upi_payment_method').val(0);
    };
    changeCheckbox5.onchange = function() {
        if (changeCheckbox5.checked)
            $('#instamojo_payment_method').val(1);
        else
            $('#instamojo_payment_method').val(0);
    };
    /* ccavenue change button value */
    changeCheckbox6.onchange = function() {
        if (changeCheckbox6.checked)
            $('#ccavenue_payment_method').val(1);
        else
            $('#ccavenue_payment_method').val(0);
    };
    /* phonepe change button value */
    changeCheckbox7.onchange = function() {
        if (changeCheckbox7.checked)
            $('#phonepe_payment_method').val(1);
        else
            $('#phonepe_payment_method').val(0);
    };
</script>