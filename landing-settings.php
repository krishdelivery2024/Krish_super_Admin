<?php
$page = "Landing Page Settings";
include "header.php";

$message = '';
$default_services = array(
    array('icon' => 'fa-utensils',        'title' => 'Food Delivery',            'desc' => 'Fresh meals from your favourite local restaurants, delivered hot and fast.'),
    array('icon' => 'fa-basket-shopping', 'title' => 'Grocery & Daily Needs',    'desc' => 'Fruits, vegetables, dairy and daily essentials from stores near you.'),
    array('icon' => 'fa-shop',            'title' => 'Online Shopping',          'desc' => 'A multi-vendor marketplace with a wide range of products at great prices.'),
    array('icon' => 'fa-box-open',        'title' => 'Parcel Delivery',          'desc' => 'Send and receive parcels quickly with safe, reliable doorstep pickup and drop.'),
    array('icon' => 'fa-location-dot',    'title' => 'Nearby Stores',            'desc' => 'Discover the best rated shops around you and get hyperlocal same-day delivery.'),
    array('icon' => 'fa-credit-card',     'title' => 'Easy Payments',            'desc' => 'Pay the way you like - cash on delivery, wallet, UPI or online payment.'),
    array('icon' => 'fa-store',           'title' => 'Store Pickup',             'desc' => 'Order online and pick up your items directly from the store at your convenience.'),
    array('icon' => 'fa-truck-fast',      'title' => 'Live Order Tracking',      'desc' => 'Follow your order at every step from the store to your doorstep.'),
    array('icon' => 'fa-gift',            'title' => 'Offers & Rewards',         'desc' => 'Save more with daily offers, coupons and a refer & earn rewards program.'),
);

// Handle Form Submission
if(isset($_POST['btn_update_landing'])){
    $settings = $fn->get_settings('system_timezone', true);
    if(!is_array($settings)) {
        $settings = array();
    }

    // Contacts
    if(isset($_POST['app_name']))       $settings['app_name'] = $fn->xss_clean($_POST['app_name']);
    if(isset($_POST['support_number'])) $settings['support_number'] = $fn->xss_clean($_POST['support_number']);
    if(isset($_POST['support_email']))  $settings['support_email'] = $fn->xss_clean($_POST['support_email']);
    if(isset($_POST['whatsapp_number']))$settings['whatsapp_number'] = $fn->xss_clean($_POST['whatsapp_number']);
    if(isset($_POST['address']))        $settings['address'] = $fn->xss_clean($_POST['address']);

    // Social Media Links & Visibility
    $settings['facebook_url']      = isset($_POST['facebook_url']) ? $fn->xss_clean($_POST['facebook_url']) : '';
    $settings['facebook_status']   = (isset($_POST['facebook_status']) && $_POST['facebook_status'] == '1') ? '1' : '0';
    $settings['instagram_url']     = isset($_POST['instagram_url']) ? $fn->xss_clean($_POST['instagram_url']) : '';
    $settings['instagram_status']  = (isset($_POST['instagram_status']) && $_POST['instagram_status'] == '1') ? '1' : '0';
    $settings['youtube_url']       = isset($_POST['youtube_url']) ? $fn->xss_clean($_POST['youtube_url']) : '';
    $settings['youtube_status']    = (isset($_POST['youtube_status']) && $_POST['youtube_status'] == '1') ? '1' : '0';

    // Floating WhatsApp Button
    $settings['show_whatsapp_floating_btn'] = (isset($_POST['show_whatsapp_floating_btn']) && $_POST['show_whatsapp_floating_btn'] == '1') ? '1' : '0';
    $settings['whatsapp_floating_msg']      = isset($_POST['whatsapp_floating_msg']) ? $fn->xss_clean($_POST['whatsapp_floating_msg']) : '';

    // App Download Links
    $settings['customer_play_store_url'] = isset($_POST['customer_play_store_url']) ? $fn->xss_clean($_POST['customer_play_store_url']) : '';
    $settings['customer_app_store_url']  = isset($_POST['customer_app_store_url']) ? $fn->xss_clean($_POST['customer_app_store_url']) : '';
    $settings['seller_play_store_url']   = isset($_POST['seller_play_store_url']) ? $fn->xss_clean($_POST['seller_play_store_url']) : '';
    $settings['seller_app_store_url']    = isset($_POST['seller_app_store_url']) ? $fn->xss_clean($_POST['seller_app_store_url']) : '';
    $settings['delivery_play_store_url'] = isset($_POST['delivery_play_store_url']) ? $fn->xss_clean($_POST['delivery_play_store_url']) : '';
    $settings['delivery_app_store_url']  = isset($_POST['delivery_app_store_url']) ? $fn->xss_clean($_POST['delivery_app_store_url']) : '';

    // Landing Services (JSON Array)
    if(isset($_POST['landing_services']) && trim($_POST['landing_services']) !== '') {
        $test_services = json_decode($_POST['landing_services'], true);
        if(is_array($test_services)){
            $settings['landing_services'] = json_encode($test_services);
        } else {
            $settings['landing_services'] = $_POST['landing_services'];
        }
    }

    // Logo Upload Handling
    if(isset($_FILES['logo']) && !empty($_FILES['logo']['name']) && $_FILES['logo']['size'] > 0){
        $file_name = $_FILES['logo']['name'];
        $tmp = explode('.', $file_name);
        $ext = strtolower(end($tmp));
        $allowed = array("gif", "jpeg", "jpg", "png", "webp");
        if(in_array($ext, $allowed)){
            $target_name = "logo." . $ext;
            $target_path = "dist/img/" . $target_name;
            if(move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)){
                $sql_logo = "UPDATE `settings` SET `value`='".$target_name."' WHERE variable='Logo' OR variable='logo'";
                $db->sql($sql_logo);
            }
        }
    }

    // Save into database
    $sql = "UPDATE settings SET value='" . $db->escapeString(json_encode($settings)) . "' WHERE variable='system_timezone'";
    $db->sql($sql);
    $message = "<div class='alert alert-success'><i class='fa fa-check-circle'></i> Landing Page Settings Updated Successfully!</div>";
}

// Fetch current configurations
$data = $fn->get_settings('system_timezone', true);
if(!is_array($data)) $data = array();

// Fetch logo
$sql = "SELECT value FROM `settings` WHERE variable='Logo' OR variable='logo'";
$db->sql($sql);
$res_logo = $db->getResult();
$logo_val = (!empty($res_logo)) ? $res_logo[0]['value'] : 'logo.png';

// Services initial data
$current_services = array();
if(!empty($data['landing_services'])){
    $decoded_svcs = json_decode($data['landing_services'], true);
    if(is_array($decoded_svcs) && count($decoded_svcs) > 0){
        $current_services = $decoded_svcs;
    }
}
if(empty($current_services)){
    $current_services = $default_services;
}
?>

<!-- FontAwesome 6 for modern landing page icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
    .custom-toggle-switch {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 22px;
        vertical-align: middle;
        margin-bottom: 0;
    }
    .custom-toggle-switch input { opacity: 0; width: 0; height: 0; }
    .custom-toggle-slider {
        position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
        background-color: #cbd5e1; transition: .25s; border-radius: 22px;
    }
    .custom-toggle-slider:before {
        position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px;
        background-color: white; transition: .25s; border-radius: 50%;
        box-shadow: 0 1px 3px rgba(0,0,0,0.25);
    }
    .custom-toggle-switch input:checked + .custom-toggle-slider { background-color: #3F51B5; }
    .custom-toggle-switch input:checked + .custom-toggle-slider:before { transform: translateX(22px); }

    .section-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border-top: 3px solid #3F51B5;
        padding: 20px 24px;
        margin-bottom: 24px;
    }
    .section-card h4 {
        margin-top: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 16px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 12px;
        margin-bottom: 18px;
    }
    .section-card h4 i {
        color: #3F51B5;
        margin-right: 8px;
    }

    /* Jump nav bar */
    .quick-jump-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }
    .quick-jump-bar a {
        background: #f1f5f9;
        color: #334155;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s;
        border: 1px solid #e2e8f0;
    }
    .quick-jump-bar a:hover, .quick-jump-bar a.active {
        background: #3F51B5;
        color: #fff;
        border-color: #3F51B5;
    }

    /* Inline Service Editor Box */
    .service-editor-box {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .service-editor-box.editing-active {
        background: #fffdf5;
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.18);
    }
    .quick-icon-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 16px;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        margin: 3px 2px;
        transition: all .15s;
    }
    .quick-icon-pill:hover {
        background: #3F51B5;
        color: #fff;
        border-color: #3F51B5;
    }
    .quick-icon-pill i { font-size: 12px; }

    /* Service list rows */
    .service-row-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #eef2ff;
        color: #3F51B5;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
    }
    .service-order-btn {
        padding: 2px 7px;
        font-size: 11px;
        line-height: 1.2;
    }
</style>

<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <?php if(!$is_sub_admin || $permissions['settings']['read'] == 1){ ?>

        <?= $message ?>

        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
            <div>
                <h3 style="margin:0; font-weight:700; color:#1e293b;">Landing Page Settings</h3>
                <p class="text-muted" style="margin:2px 0 0 0;">Manage homepage branding, contact info, social links, floating WhatsApp, and service cards.</p>
            </div>
            <div>
                <a href="landing.php" target="_blank" class="btn btn-default"><i class="fa fa-external-link"></i> View Live Landing Page</a>
            </div>
        </div>

        <!-- Quick Jump Links -->
        <div class="quick-jump-bar">
            <a href="#branding-section"><i class="fa fa-paint-brush"></i> Branding & Logo</a>
            <a href="#contact-section"><i class="fa fa-phone"></i> Contacts</a>
            <a href="#social-section"><i class="fa fa-share-alt"></i> Social Media</a>
            <a href="#whatsapp-section"><i class="fa fa-whatsapp"></i> WhatsApp Button</a>
            <a href="#apps-section"><i class="fa fa-mobile"></i> App Download Links</a>
            <a href="#landing-services-section" style="background:#eef2ff; border-color:#6366f1; color:#4338ca;"><i class="fa fa-th-large"></i> Landing Page Services</a>
        </div>

        <form method="post" enctype="multipart/form-data" id="landingSettingsForm">

            <!-- 1. Branding & Logo -->
            <div class="section-card" id="branding-section">
                <h4><i class="fa fa-paint-brush"></i> Branding & Logo</h4>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="app_name">App / Brand Name:</label>
                        <input type="text" class="form-control" name="app_name" id="app_name" value="<?=isset($data['app_name']) ? htmlspecialchars($data['app_name']) : 'Krish Delivery';?>" placeholder="Krish Delivery" required/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label for="logo">Landing Page Logo:</label>
                        <div style="display:flex; align-items:center; gap:16px;">
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:6px; display:inline-block;">
                                <img src="<?= 'dist/img/' . $logo_val ?>" alt="Current Logo" style="height:44px; max-width:160px; object-fit:contain;"/>
                            </div>
                            <div style="flex:1;">
                                <input type="file" name="logo" id="logo" accept="image/*" class="form-control"/>
                                <small class="text-muted">Upload PNG or JPG logo. Displayed in Header & Footer.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Contacts Details -->
            <div class="section-card" id="contact-section">
                <h4><i class="fa fa-phone"></i> Contact Details (Landing Page)</h4>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label for="support_number"><i class="fa fa-phone text-primary"></i> Support Phone Number:</label>
                        <input type="text" class="form-control" name="support_number" id="support_number" value="<?=isset($data['support_number']) ? htmlspecialchars($data['support_number']) : '';?>" placeholder="e.g. 7402152219"/>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="support_email"><i class="fa fa-envelope text-primary"></i> Support Email Address:</label>
                        <input type="email" class="form-control" name="support_email" id="support_email" value="<?=isset($data['support_email']) ? htmlspecialchars($data['support_email']) : '';?>" placeholder="e.g. support@krishdelivery.com"/>
                    </div>
                    <div class="col-md-4 form-group">
                        <label for="whatsapp_number"><i class="fa fa-whatsapp text-success"></i> WhatsApp Number:</label>
                        <input type="text" class="form-control" name="whatsapp_number" id="whatsapp_number" value="<?=isset($data['whatsapp_number']) ? htmlspecialchars($data['whatsapp_number']) : '';?>" placeholder="e.g. 7402152219"/>
                    </div>
                    <div class="col-md-12 form-group">
                        <label for="address"><i class="fa fa-map-marker text-danger"></i> Office / Store Address:</label>
                        <textarea class="form-control" name="address" id="address" rows="2" placeholder="Complete address shown on the landing page contact section"><?=isset($data['address']) ? htmlspecialchars($data['address']) : '';?></textarea>
                    </div>
                </div>
            </div>

            <!-- 3. Social Media Links & Visibility -->
            <div class="section-card" id="social-section">
                <h4><i class="fa fa-share-alt"></i> Social Media Links & Visibility</h4>
                <p class="text-muted"><small>Set URLs and control whether each social platform icon is visible on the landing page.</small></p>

                <div class="row">
                    <!-- Facebook -->
                    <div class="col-md-8 form-group">
                        <label for="facebook_url"><i class="fa fa-facebook-square" style="color:#1877F2; font-size:16px;"></i> Facebook Page URL:</label>
                        <input type="url" class="form-control" name="facebook_url" id="facebook_url" value="<?=isset($data['facebook_url']) ? htmlspecialchars($data['facebook_url']) : '';?>" placeholder="https://facebook.com/krishdelivery"/>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Facebook Status:</label><br>
                        <label class="custom-toggle-switch">
                            <input type="checkbox" onchange="document.getElementById('facebook_status').value = this.checked ? '1' : '0';" <?=(!empty($data['facebook_status']) && $data['facebook_status'] == '1') ? 'checked' : '';?>>
                            <span class="custom-toggle-slider"></span>
                        </label>
                        <input type="hidden" id="facebook_status" name="facebook_status" value="<?=(!empty($data['facebook_status'])) ? $data['facebook_status'] : '0';?>">
                        <span class="text-muted" style="margin-left:8px; font-size:13px;">Visible on Page</span>
                    </div>

                    <!-- Instagram -->
                    <div class="col-md-8 form-group">
                        <label for="instagram_url"><i class="fa fa-instagram" style="color:#E1306C; font-size:16px;"></i> Instagram Profile URL:</label>
                        <input type="url" class="form-control" name="instagram_url" id="instagram_url" value="<?=isset($data['instagram_url']) ? htmlspecialchars($data['instagram_url']) : '';?>" placeholder="https://instagram.com/krishdelivery"/>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Instagram Status:</label><br>
                        <label class="custom-toggle-switch">
                            <input type="checkbox" onchange="document.getElementById('instagram_status').value = this.checked ? '1' : '0';" <?=(!empty($data['instagram_status']) && $data['instagram_status'] == '1') ? 'checked' : '';?>>
                            <span class="custom-toggle-slider"></span>
                        </label>
                        <input type="hidden" id="instagram_status" name="instagram_status" value="<?=(!empty($data['instagram_status'])) ? $data['instagram_status'] : '0';?>">
                        <span class="text-muted" style="margin-left:8px; font-size:13px;">Visible on Page</span>
                    </div>

                    <!-- YouTube -->
                    <div class="col-md-8 form-group">
                        <label for="youtube_url"><i class="fa fa-youtube-play" style="color:#FF0000; font-size:16px;"></i> YouTube Channel URL:</label>
                        <input type="url" class="form-control" name="youtube_url" id="youtube_url" value="<?=isset($data['youtube_url']) ? htmlspecialchars($data['youtube_url']) : '';?>" placeholder="https://youtube.com/@krishdelivery"/>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>YouTube Status:</label><br>
                        <label class="custom-toggle-switch">
                            <input type="checkbox" onchange="document.getElementById('youtube_status').value = this.checked ? '1' : '0';" <?=(!empty($data['youtube_status']) && $data['youtube_status'] == '1') ? 'checked' : '';?>>
                            <span class="custom-toggle-slider"></span>
                        </label>
                        <input type="hidden" id="youtube_status" name="youtube_status" value="<?=(!empty($data['youtube_status'])) ? $data['youtube_status'] : '0';?>">
                        <span class="text-muted" style="margin-left:8px; font-size:13px;">Visible on Page</span>
                    </div>
                </div>
            </div>

            <!-- 4. Floating WhatsApp Chat Button -->
            <div class="section-card" id="whatsapp-section">
                <h4><i class="fa fa-whatsapp" style="color:#25D366;"></i> WhatsApp Floating Chat Button</h4>
                <p class="text-muted"><small>Add an animated WhatsApp button to the bottom-right of your landing page for direct customer chat.</small></p>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Show Floating WhatsApp Button:</label><br>
                        <label class="custom-toggle-switch">
                            <input type="checkbox" onchange="document.getElementById('show_whatsapp_floating_btn').value = this.checked ? '1' : '0';" <?=(!isset($data['show_whatsapp_floating_btn']) || $data['show_whatsapp_floating_btn'] == '1') ? 'checked' : '';?>>
                            <span class="custom-toggle-slider"></span>
                        </label>
                        <input type="hidden" id="show_whatsapp_floating_btn" name="show_whatsapp_floating_btn" value="<?=(isset($data['show_whatsapp_floating_btn'])) ? $data['show_whatsapp_floating_btn'] : '1';?>">
                        <span class="text-muted" style="margin-left:8px; font-size:13px;">Enabled</span>
                    </div>
                    <div class="col-md-8 form-group">
                        <label for="whatsapp_floating_msg">Default Greeting Message:</label>
                        <input type="text" class="form-control" name="whatsapp_floating_msg" id="whatsapp_floating_msg" value="<?=isset($data['whatsapp_floating_msg']) ? htmlspecialchars($data['whatsapp_floating_msg']) : 'Hello Krish Delivery, I have an inquiry!';?>" placeholder="Hello Krish Delivery, I have an inquiry!"/>
                        <small class="text-muted">Pre-filled text when visitor clicks the WhatsApp floating button.</small>
                    </div>
                </div>
            </div>

            <!-- 5. App Store Links -->
            <div class="section-card" id="apps-section">
                <h4><i class="fa fa-mobile"></i> App Download URLs (Landing Page)</h4>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-android text-success"></i> Customer App - Google Play URL:</label>
                        <input type="url" class="form-control" name="customer_play_store_url" value="<?=isset($data['customer_play_store_url']) ? htmlspecialchars($data['customer_play_store_url']) : '';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-apple text-muted"></i> Customer App - App Store URL:</label>
                        <input type="url" class="form-control" name="customer_app_store_url" value="<?=isset($data['customer_app_store_url']) ? htmlspecialchars($data['customer_app_store_url']) : '';?>" placeholder="https://apps.apple.com/app/..."/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-android text-success"></i> Seller App - Google Play URL:</label>
                        <input type="url" class="form-control" name="seller_play_store_url" value="<?=isset($data['seller_play_store_url']) ? htmlspecialchars($data['seller_play_store_url']) : '';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-apple text-muted"></i> Seller App - App Store URL:</label>
                        <input type="url" class="form-control" name="seller_app_store_url" value="<?=isset($data['seller_app_store_url']) ? htmlspecialchars($data['seller_app_store_url']) : '';?>" placeholder="https://apps.apple.com/app/..."/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-android text-success"></i> Delivery Boy App - Google Play URL:</label>
                        <input type="url" class="form-control" name="delivery_play_store_url" value="<?=isset($data['delivery_play_store_url']) ? htmlspecialchars($data['delivery_play_store_url']) : '';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                    </div>
                    <div class="col-md-6 form-group">
                        <label><i class="fa fa-apple text-muted"></i> Delivery Boy App - App Store URL:</label>
                        <input type="url" class="form-control" name="delivery_app_store_url" value="<?=isset($data['delivery_app_store_url']) ? htmlspecialchars($data['delivery_app_store_url']) : '';?>" placeholder="https://apps.apple.com/app/..."/>
                    </div>
                </div>
            </div>

            <!-- 6. Dynamic Services Manager (INLINE EDITING SECTION) -->
            <div class="section-card" id="landing-services-section">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 18px;">
                    <div>
                        <h4 style="margin:0; border:none; padding:0;"><i class="fa fa-th-large"></i> Landing Page Services</h4>
                        <p class="text-muted" style="margin:2px 0 0 0;"><small>Add, edit, reorder or delete service cards shown in the "All the services we provide" section.</small></p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-xs btn-default" onclick="resetDefaultServices()" title="Restore default Krish delivery services"><i class="fa fa-refresh"></i> Reset Defaults</button>
                        <button type="button" class="btn btn-xs btn-success" onclick="prepareAddNewService()"><i class="fa fa-plus"></i> + Add New Service</button>
                    </div>
                </div>

                <!-- Hidden Input holding JSON string of all services -->
                <input type="hidden" name="landing_services" id="landing_services" value="<?=htmlspecialchars(json_encode($current_services), ENT_QUOTES, 'UTF-8');?>">

                <!-- Alert Feedback Box -->
                <div id="service_feedback_alert" style="display:none; margin-bottom: 15px;" class="alert alert-info">
                    <span id="service_feedback_text"></span>
                </div>

                <!-- DIRECT INLINE EDITING / ADDING SECTION (ALWAYS ACCESSIBLE) -->
                <div class="service-editor-box" id="service_editor_box">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 14px;">
                        <div>
                            <span id="editor_mode_badge" class="label label-primary" style="font-size:12px; padding: 4px 8px;">Add New Service</span>
                            <span id="editor_subtitle" style="font-weight:600; color:#334155; margin-left: 8px;">Fill the fields below to add a service to the list</span>
                        </div>
                        <button type="button" id="btn_cancel_edit" class="btn btn-xs btn-default" onclick="cancelServiceEdit()" style="display:none;"><i class="fa fa-times"></i> Cancel Edit</button>
                    </div>

                    <!-- Hidden Edit Index (-1 = New, 0..N = Editing existing) -->
                    <input type="hidden" id="editor_service_idx" value="-1">

                    <div class="row">
                        <!-- Service Title -->
                        <div class="col-md-6 form-group">
                            <label for="editor_service_title" style="font-weight:600; color:#1e293b;">Service Title: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editor_service_title" placeholder="e.g. Food Delivery, Grocery, Parcel..." style="font-weight:600;">
                        </div>

                        <!-- Icon & Live Preview -->
                        <div class="col-md-6 form-group">
                            <label for="editor_service_icon" style="font-weight:600; color:#1e293b;">FontAwesome Icon Class:</label>
                            <div class="input-group">
                                <span class="input-group-addon" style="background:#fff; min-width:44px; text-align:center;">
                                    <i id="editor_icon_preview" class="fas fa-utensils" style="font-size:18px; color:#3F51B5;"></i>
                                </span>
                                <input type="text" class="form-control" id="editor_service_icon" value="fa-utensils" placeholder="fa-utensils" oninput="updateIconPreview(this.value)">
                            </div>
                        </div>

                        <!-- Quick Popular Icons Selector -->
                        <div class="col-md-12 form-group" style="margin-bottom: 12px;">
                            <label style="font-size:11px; text-transform:uppercase; color:#64748b; font-weight:700; letter-spacing:0.5px; margin-bottom:4px; display:block;">Quick Select Popular Icons:</label>
                            <div style="display:flex; flex-wrap:wrap; gap:4px;">
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-utensils')"><i class="fas fa-utensils"></i> Food</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-basket-shopping')"><i class="fas fa-basket-shopping"></i> Grocery</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-shop')"><i class="fas fa-shop"></i> Shopping</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-box-open')"><i class="fas fa-box-open"></i> Parcel</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-location-dot')"><i class="fas fa-location-dot"></i> Nearby</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-credit-card')"><i class="fas fa-credit-card"></i> Payments</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-store')"><i class="fas fa-store"></i> Store Pickup</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-truck-fast')"><i class="fas fa-truck-fast"></i> Live Tracking</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-gift')"><i class="fas fa-gift"></i> Rewards</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-motorcycle')"><i class="fas fa-motorcycle"></i> Express</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-capsules')"><i class="fas fa-capsules"></i> Pharmacy</span>
                                <span class="quick-icon-pill" onclick="selectQuickIcon('fa-tags')"><i class="fas fa-tags"></i> Deals</span>
                            </div>
                        </div>

                        <!-- Service Description -->
                        <div class="col-md-12 form-group">
                            <label for="editor_service_desc" style="font-weight:600; color:#1e293b;">Short Description:</label>
                            <textarea class="form-control" id="editor_service_desc" rows="2" placeholder="Brief summary of this service shown on the landing page card..."></textarea>
                        </div>
                    </div>

                    <!-- Editor Action Buttons -->
                    <div style="display:flex; justify-content:space-between; align-items:center; padding-top: 8px; border-top: 1px dashed #cbd5e1;">
                        <span class="text-muted" style="font-size:12px;">
                            <i class="fa fa-info-circle text-primary"></i> Clicking "Apply" adds/updates the service in the list below.
                        </span>
                        <div>
                            <button type="button" class="btn btn-default" onclick="cancelServiceEdit()" style="margin-right:6px;">Clear Form</button>
                            <button type="button" class="btn btn-primary" id="btn_apply_service" onclick="applyServiceFromEditor()" style="font-weight:700;">
                                <i class="fa fa-check"></i> <span id="btn_apply_label">Add Service to List</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SERVICES LIST / TABLE -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 8px;">
                    <h5 style="margin:0; font-weight:700; color:#334155;">
                        <i class="fa fa-list"></i> Current Landing Page Services (<span id="services_count_badge"><?= count($current_services); ?></span>)
                    </h5>
                    <small class="text-muted">Use <b>Edit</b> to modify or <b>▲ / ▼</b> to rearrange order.</small>
                </div>

                <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 6px; background:#fff;">
                    <table class="table table-hover table-striped" style="margin-bottom:0;" id="services_table">
                        <thead style="background:#f8fafc;">
                            <tr>
                                <th style="width:50px; text-align:center;">#</th>
                                <th style="width:70px; text-align:center;">Icon</th>
                                <th style="width:200px;">Service Title</th>
                                <th>Description</th>
                                <th style="width:85px; text-align:center;">Order</th>
                                <th style="width:130px; text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="services_tbody">
                            <!-- Populated by PHP and updated dynamically by JS -->
                            <?php foreach($current_services as $s_idx => $svc):
                                $s_icon = !empty($svc['icon']) ? $svc['icon'] : 'fa-tag';
                                if(strpos($s_icon, 'fa-') === false) $s_icon = 'fa-' . $s_icon;
                            ?>
                            <tr id="service_row_<?= $s_idx ?>">
                                <td style="text-align:center; vertical-align:middle; font-weight:600; color:#64748b;"><?= $s_idx + 1 ?></td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <div class="service-row-icon">
                                        <i class="fas <?= htmlspecialchars($s_icon) ?>"></i>
                                    </div>
                                </td>
                                <td style="vertical-align:middle;">
                                    <strong style="color:#1e293b; font-size:14px;"><?= htmlspecialchars($svc['title']) ?></strong>
                                    <br><small class="text-muted"><i class="fa fa-tag"></i> <?= htmlspecialchars($s_icon) ?></small>
                                </td>
                                <td style="font-size:12px; color:#475569; vertical-align:middle;"><?= htmlspecialchars(isset($svc['desc']) ? $svc['desc'] : '') ?></td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button type="button" class="btn btn-default btn-xs service-order-btn" onclick="moveService(<?= $s_idx ?>, -1)" title="Move Up"><i class="fa fa-arrow-up"></i></button>
                                    <button type="button" class="btn btn-default btn-xs service-order-btn" onclick="moveService(<?= $s_idx ?>, 1)" title="Move Down"><i class="fa fa-arrow-down"></i></button>
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button type="button" class="btn btn-xs btn-primary" onclick="loadServiceForEdit(<?= $s_idx ?>)" title="Edit this service" style="margin-right:4px;">
                                        <i class="fa fa-pencil"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-xs btn-danger" onclick="deleteService(<?= $s_idx ?>)" title="Delete this service">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 10px; font-size:12px; color:#64748b;">
                    <i class="fa fa-lightbulb-o text-warning"></i> <b>Remember:</b> After adding, editing or reordering services, click the green <b>"Save Landing Page Settings"</b> button below to permanently save your changes!
                </div>
            </div>

            <!-- Submit Button -->
            <div style="margin-bottom: 40px; padding: 15px 0;">
                <button type="submit" name="btn_update_landing" class="btn btn-primary btn-lg" style="padding: 12px 36px; font-weight:700; font-size:16px; box-shadow: 0 4px 14px rgba(63,81,181,0.35);">
                    <i class="fa fa-save"></i> Save Landing Page Settings
                </button>
            </div>

        </form>

        <script>
        // Store services in JavaScript state
        var defaultServicesList = <?= json_encode($default_services); ?>;
        var currentServices = <?= json_encode($current_services); ?>;

        // Ensure array validity
        if (!Array.isArray(currentServices) || currentServices.length === 0) {
            currentServices = JSON.parse(JSON.stringify(defaultServicesList));
        }

        // Sync to hidden input and update UI
        function syncServicesData() {
            var input = document.getElementById('landing_services');
            if (input) {
                input.value = JSON.stringify(currentServices);
            }
            renderServicesTable();
            var badge = document.getElementById('services_count_badge');
            if (badge) badge.innerText = currentServices.length;
        }

        // Render table rows dynamically
        function renderServicesTable() {
            var tbody = document.getElementById('services_tbody');
            if (!tbody) return;

            if (!currentServices || currentServices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted" style="padding: 24px;">No services added yet. Use the form above to add your first service card!</td></tr>';
                return;
            }

            var html = '';
            currentServices.forEach(function(item, idx) {
                var iconClass = item.icon || 'fa-tag';
                if (iconClass.indexOf('fa-') === -1) {
                    iconClass = 'fa-' + iconClass;
                }
                var escapedTitle = escapeHtml(item.title);
                var escapedDesc  = escapeHtml(item.desc || '');
                var escapedIcon  = escapeHtml(iconClass);

                html += '<tr id="service_row_' + idx + '">';
                html += '<td style="text-align:center; vertical-align:middle; font-weight:600; color:#64748b;">' + (idx + 1) + '</td>';
                html += '<td style="text-align:center; vertical-align:middle;"><div class="service-row-icon"><i class="fas ' + escapedIcon + '"></i></div></td>';
                html += '<td style="vertical-align:middle;"><strong style="color:#1e293b; font-size:14px;">' + escapedTitle + '</strong><br><small class="text-muted"><i class="fa fa-tag"></i> ' + escapedIcon + '</small></td>';
                html += '<td style="font-size:12px; color:#475569; vertical-align:middle;">' + escapedDesc + '</td>';
                html += '<td style="text-align:center; vertical-align:middle;">';
                html += '<button type="button" class="btn btn-default btn-xs service-order-btn" onclick="moveService(' + idx + ', -1)" title="Move Up"' + (idx === 0 ? ' disabled' : '') + '><i class="fa fa-arrow-up"></i></button> ';
                html += '<button type="button" class="btn btn-default btn-xs service-order-btn" onclick="moveService(' + idx + ', 1)" title="Move Down"' + (idx === currentServices.length - 1 ? ' disabled' : '') + '><i class="fa fa-arrow-down"></i></button>';
                html += '</td>';
                html += '<td style="text-align:center; vertical-align:middle;">';
                html += '<button type="button" class="btn btn-xs btn-primary" onclick="loadServiceForEdit(' + idx + ')" title="Edit this service" style="margin-right:4px;"><i class="fa fa-pencil"></i> Edit</button>';
                html += '<button type="button" class="btn btn-xs btn-danger" onclick="deleteService(' + idx + ')" title="Delete this service"><i class="fa fa-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });
            tbody.innerHTML = html;
        }

        // Escape HTML for XSS prevention
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        // Live Icon Preview updater
        function updateIconPreview(val) {
            var preview = document.getElementById('editor_icon_preview');
            if (!preview) return;
            var cleanVal = (val || '').trim();
            if (!cleanVal) cleanVal = 'fa-tag';
            if (cleanVal.indexOf('fa-') === -1) cleanVal = 'fa-' + cleanVal;
            preview.className = 'fas ' + cleanVal;
        }

        // Quick select icon pill click
        function selectQuickIcon(iconClass) {
            var iconInput = document.getElementById('editor_service_icon');
            if (iconInput) {
                iconInput.value = iconClass;
                updateIconPreview(iconClass);
            }
        }

        // Load a service into the inline editor form
        function loadServiceForEdit(idx) {
            var item = currentServices[idx];
            if (!item) return;

            document.getElementById('editor_service_idx').value = idx;
            document.getElementById('editor_service_title').value = item.title || '';
            document.getElementById('editor_service_icon').value = item.icon || 'fa-utensils';
            document.getElementById('editor_service_desc').value = item.desc || '';
            updateIconPreview(item.icon || 'fa-utensils');

            // Switch editor box to editing state
            var box = document.getElementById('service_editor_box');
            if (box) box.classList.add('editing-active');

            var badge = document.getElementById('editor_mode_badge');
            if (badge) {
                badge.className = 'label label-warning';
                badge.innerText = 'Editing Service #' + (idx + 1);
            }

            var subtitle = document.getElementById('editor_subtitle');
            if (subtitle) {
                subtitle.innerText = 'Modify "' + (item.title || '') + '" and click "Update Service in List"';
            }

            var btnLabel = document.getElementById('btn_apply_label');
            if (btnLabel) btnLabel.innerText = 'Update Service in List';

            var cancelBtn = document.getElementById('btn_cancel_edit');
            if (cancelBtn) cancelBtn.style.display = 'inline-block';

            // Smoothly scroll editor into view and focus title
            box.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function() {
                var titleInput = document.getElementById('editor_service_title');
                if (titleInput) titleInput.focus();
            }, 300);
        }

        // Reset editor back to Add mode
        function cancelServiceEdit() {
            document.getElementById('editor_service_idx').value = '-1';
            document.getElementById('editor_service_title').value = '';
            document.getElementById('editor_service_icon').value = 'fa-utensils';
            document.getElementById('editor_service_desc').value = '';
            updateIconPreview('fa-utensils');

            var box = document.getElementById('service_editor_box');
            if (box) box.classList.remove('editing-active');

            var badge = document.getElementById('editor_mode_badge');
            if (badge) {
                badge.className = 'label label-primary';
                badge.innerText = 'Add New Service';
            }

            var subtitle = document.getElementById('editor_subtitle');
            if (subtitle) subtitle.innerText = 'Fill the fields below to add a service to the list';

            var btnLabel = document.getElementById('btn_apply_label');
            if (btnLabel) btnLabel.innerText = 'Add Service to List';

            var cancelBtn = document.getElementById('btn_cancel_edit');
            if (cancelBtn) cancelBtn.style.display = 'none';
        }

        // Scroll to editor and focus
        function prepareAddNewService() {
            cancelServiceEdit();
            var box = document.getElementById('service_editor_box');
            if (box) {
                box.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(function() {
                    var titleInput = document.getElementById('editor_service_title');
                    if (titleInput) titleInput.focus();
                }, 300);
            }
        }

        // Apply service from the inline editor
        function applyServiceFromEditor() {
            var titleInput = document.getElementById('editor_service_title');
            var iconInput  = document.getElementById('editor_service_icon');
            var descInput  = document.getElementById('editor_service_desc');
            var idxInput   = document.getElementById('editor_service_idx');

            var title = (titleInput.value || '').trim();
            var icon  = (iconInput.value || '').trim();
            var desc  = (descInput.value || '').trim();
            var idx   = parseInt(idxInput.value, 10);

            if (!title) {
                alert('Please enter a service title.');
                titleInput.focus();
                return;
            }
            if (!icon) icon = 'fa-tag';
            if (icon.indexOf('fa-') === -1) icon = 'fa-' + icon;

            var serviceObj = { title: title, icon: icon, desc: desc };

            if (idx >= 0 && idx < currentServices.length) {
                currentServices[idx] = serviceObj;
                showFeedbackAlert('✓ Service "' + escapeHtml(title) + '" updated in list! Click "Save Landing Page Settings" below to persist.');
            } else {
                currentServices.push(serviceObj);
                showFeedbackAlert('✓ New Service "' + escapeHtml(title) + '" added to list! Click "Save Landing Page Settings" below to persist.');
            }

            syncServicesData();
            cancelServiceEdit();
        }

        // Delete a service
        function deleteService(idx) {
            var item = currentServices[idx];
            if (!item) return;
            if (confirm('Are you sure you want to delete "' + (item.title || 'this service') + '"?')) {
                currentServices.splice(idx, 1);
                syncServicesData();
                showFeedbackAlert('✓ Service removed from list. Remember to click "Save Landing Page Settings" below.');
                if (parseInt(document.getElementById('editor_service_idx').value, 10) === idx) {
                    cancelServiceEdit();
                }
            }
        }

        // Reorder service Up (-1) or Down (+1)
        function moveService(idx, direction) {
            var targetIdx = idx + direction;
            if (targetIdx < 0 || targetIdx >= currentServices.length) return;
            var temp = currentServices[idx];
            currentServices[idx] = currentServices[targetIdx];
            currentServices[targetIdx] = temp;
            syncServicesData();
        }

        // Reset to Krish default 9 services
        function resetDefaultServices() {
            if (confirm('Reset services to the default 9 Krish delivery services? Any custom services will be replaced.')) {
                currentServices = JSON.parse(JSON.stringify(defaultServicesList));
                syncServicesData();
                cancelServiceEdit();
                showFeedbackAlert('✓ Reset to default services. Click "Save Landing Page Settings" to save.');
            }
        }

        // Show a brief feedback banner
        function showFeedbackAlert(msg) {
            var alertBox = document.getElementById('service_feedback_alert');
            var textSpan = document.getElementById('service_feedback_text');
            if (alertBox && textSpan) {
                textSpan.innerHTML = msg;
                alertBox.style.display = 'block';
                setTimeout(function() {
                    alertBox.style.display = 'none';
                }, 6000);
            }
        }

        // Initial sync on load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', syncServicesData);
        } else {
            syncServicesData();
        }
        </script>

        <?php } else { ?>
            <div class="alert alert-danger">You have no permission to view settings</div>
        <?php } ?>
    </div>
</div>

<?php include "footer.php"; ?>
