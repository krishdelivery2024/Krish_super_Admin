<?php 
   	$page = "Privacy Policy";
    include "header.php";

    $message = '';
    if (isset($_POST['btn_update'])) {
        if ($permissions['settings']['update'] == 1) {
            if (!empty($_POST['privacy_policy']) && !empty($_POST['terms_conditions'])) {

                $privacy_policy = $db->escapeString($fn->xss_clean($_POST['privacy_policy']));
                $terms_conditions = $db->escapeString($fn->xss_clean($_POST['terms_conditions']));
                $terms_in_inovice = $db->escapeString($fn->xss_clean($_POST['terms_in_inovice']));

                // Update privacy_policy
                $sql = "UPDATE `settings` SET `value`='".$privacy_policy."' WHERE `variable` = 'privacy_policy'";
                $db->sql($sql);

                // Update terms_conditions
                $sql = "UPDATE `settings` SET `value`='".$terms_conditions."' WHERE `variable` = 'terms_conditions'";
                $db->sql($sql);

                // Update terms_in_inovice
                $sql = "UPDATE `settings` SET `value`='".$terms_in_inovice."' WHERE `variable` = 'terms_in_inovice'";
                $db->sql($sql);

                // Update delivery_boy_privacy_policy
                if (isset($_POST['delivery_boy_privacy_policy'])) {
                    $delivery_boy_privacy_policy = $db->escapeString($fn->xss_clean($_POST['delivery_boy_privacy_policy']));
                    $db->sql("SELECT id FROM `settings` WHERE `variable` = 'delivery_boy_privacy_policy'");
                    $chk_d = $db->getResult();
                    if (!empty($chk_d)) {
                        $sql = "UPDATE `settings` SET `value`='".$delivery_boy_privacy_policy."' WHERE `variable` = 'delivery_boy_privacy_policy'";
                    } else {
                        $sql = "INSERT INTO `settings` (`variable`, `value`) VALUES ('delivery_boy_privacy_policy', '".$delivery_boy_privacy_policy."')";
                    }
                    $db->sql($sql);
                }

                $message .= "<div class='alert alert-success'><i class='fa fa-check-circle'></i> Privacy Policies and Terms Updated Successfully!</div>";
            }
        } else {
            $message .= "<label class='alert alert-danger'>You have no permission to update settings</label>";
        }
    }

    // Query all settings by variable name
    $sql = "SELECT variable, value FROM settings WHERE variable IN ('privacy_policy', 'terms_conditions', 'terms_in_inovice', 'delivery_boy_privacy_policy')";
    $db->sql($sql);
    $raw_settings = $db->getResult();
    $settings_map = array(
        'privacy_policy' => '',
        'terms_conditions' => '',
        'terms_in_inovice' => '',
        'delivery_boy_privacy_policy' => '',
    );
    if (is_array($raw_settings)) {
        foreach ($raw_settings as $s_row) {
            $settings_map[$s_row['variable']] = $s_row['value'];
        }
    }
?>

    <div class="row">
        <div class="col-md-12" style="margin-bottom:15px; display:flex; gap:12px; flex-wrap:wrap;">
            <?php if ($permissions['settings']['read'] == 1) { ?>
                <a href="play-store-privacy-policy.php" target="_blank" class="btn btn-primary btn-sm">
                    <i class="fa fa-user"></i> Customer App Privacy Policy (Play Store)
                </a>
                <a href="delivery-partner-privacy-policy.php" target="_blank" class="btn btn-success btn-sm">
                    <i class="fa fa-motorcycle"></i> Delivery Partner Privacy Policy (Play Console URL)
                </a>
            <?php } ?>
        </div>
    </div>

    <?=$message;?>

    <div class="row">
        <div class="col-md-12">
            <?php if ($permissions['settings']['read'] == 1) {
                if ($permissions['settings']['update'] == 0) { ?>
                    <div class="alert alert-danger">You have no permission to update settings</div>
                <?php } ?>

                <!-- General form elements -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Update Customer & Partner Privacy Policies</h3>
                    </div>
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form method="post" enctype="multipart/form-data">
                        <div class="box-body">

                            <!-- Delivery Partner Privacy Policy for Play Console -->
                            <div class="panel panel-success" style="margin-bottom: 25px;">
                                <div class="panel-heading" style="background:#059669;color:#fff;">
                                    <h4 class="panel-title" style="font-weight:700;">
                                        <i class="fa fa-motorcycle"></i> Delivery Partner Privacy Policy (Google Play Store)
                                    </h4>
                                </div>
                                <div class="panel-body" style="background:#f9fafb;">
                                    <div class="alert alert-info" style="font-size:13px;margin-bottom:15px;background:#ecfdf5;border-color:#a7f3d0;color:#065f46;">
                                        <i class="fa fa-info-circle"></i> <strong>Play Console URL:</strong>
                                        <a href="delivery-partner-privacy-policy.php" target="_blank" style="color:#047857;text-decoration:underline;font-weight:600;margin-left:5px;">
                                            https://krishdelivery.com/delivery-partner-privacy-policy.php
                                        </a>
                                        <br>
                                        <small>This policy covers foreground & background location disclosure, KYC documents, driver license, camera, and partner account deletion for <strong>Krish Partner</strong> (<code>com.krishdelivery.krishdeliveryboy</code>). Leave blank to automatically use the built-in compliant template.</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="delivery_boy_privacy_policy">Partner Privacy Policy HTML Content:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="delivery_boy_privacy_policy" id="delivery_boy_privacy_policy"><?=htmlspecialchars($settings_map['delivery_boy_privacy_policy']);?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Privacy Policy -->
                            <div class="form-group">
                                <label for="privacy_policy"><i class="fa fa-user text-primary"></i> Customer App Privacy Policy:</label>
                                <textarea rows="10" cols="10" class="form-control" name="privacy_policy" id="privacy_policy" required><?=$settings_map['privacy_policy'];?></textarea>
                            </div>

                            <hr>

                            <!-- Customer Terms & Conditions -->
                            <div class="form-group">
                                <label for="terms_conditions"><i class="fa fa-file-text-o text-info"></i> Terms &amp; Conditions:</label>
                                <textarea rows="10" cols="10" class="form-control" name="terms_conditions" id="terms_conditions" required><?=$settings_map['terms_conditions'];?></textarea>
                            </div>

                            <hr>

                            <!-- Terms in Invoice -->
                            <div class="form-group">
                                <label for="terms_in_inovice"><i class="fa fa-file-pdf-o text-danger"></i> Terms &amp; Conditions For Invoice:</label>
                                <textarea rows="10" cols="10" class="form-control" name="terms_in_inovice" id="terms_in_inovice" required><?=$settings_map['terms_in_inovice'];?></textarea>
                            </div>

                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer">
                            <input type="submit" class="btn-primary btn" value="Save All Policies" name="btn_update"/>
                        </div>
                    </form>
                <?php } else { ?>
                    <div class="alert alert-danger">You have no permission to view settings</div>
                <?php } ?>
            </div>
            <!-- /.box -->
        </div>
    </div>
    <div class="separator"> </div>

<?php include "footer.php"; ?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('delivery_boy_privacy_policy');
        CKEDITOR.replace('privacy_policy');
        CKEDITOR.replace('terms_conditions');
        CKEDITOR.replace('terms_in_inovice');
    }
</script>
