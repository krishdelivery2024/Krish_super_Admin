<?php
$sql = "SELECT id, name, polygon FROM zone WHERE status=1 ORDER BY name ASC";
$db->sql($sql);
$active_zones = $db->getResult();
if (!is_array($active_zones)) {
    $active_zones = array();
}

foreach ($active_zones as &$z) {
    $z['center_lat'] = 0;
    $z['center_lng'] = 0;
    if (!empty($z['polygon'])) {
        $pts = json_decode($z['polygon'], true);
        if (is_array($pts) && count($pts) > 0) {
            $sumLat = 0;
            $sumLng = 0;
            $count = 0;
            foreach ($pts as $pt) {
                if (isset($pt['lat']) && isset($pt['lng'])) {
                    $sumLat += (float)$pt['lat'];
                    $sumLng += (float)$pt['lng'];
                    $count++;
                }
            }
            if ($count > 0) {
                $z['center_lat'] = round($sumLat / $count, 6);
                $z['center_lng'] = round($sumLng / $count, 6);
            }
        }
    }
}
unset($z);

$map_key = '';
$db->sql("SELECT value FROM settings WHERE variable='store_map_api'");
$mk_res = $db->getResult();
if (!empty($mk_res) && !empty($mk_res[0]['value'])) {
    $map_key = $mk_res[0]['value'];
}
if (empty($map_key)) {
    $map_key = isset($settings['store_map_api']) ? $settings['store_map_api'] : 'AIzaSyA2HxHFxFJUUH9Xm3nnVZIz8numHc-XEUQ';
}
$store_lat = isset($settings['store_lattitude']) && !empty($settings['store_lattitude']) ? (float)$settings['store_lattitude'] : 8.701978;
$store_lng = isset($settings['store_longitude']) && !empty($settings['store_longitude']) ? (float)$settings['store_longitude'] : 77.4195713;

$default_delivery_charge = isset($settings['delivery_charge']) && is_numeric($settings['delivery_charge']) ? (float)$settings['delivery_charge'] : 45.0;

// Fetch service meta for instant rendering
$db->sql("SELECT id, name FROM parcel_item_types WHERE status=1 ORDER BY name ASC");
$active_item_types = $db->getResult();
if (!is_array($active_item_types)) $active_item_types = array();

$db->sql("SELECT id, name, capacity_label, base_price, base_km, per_km_price, helper_price, image FROM shipping_vehicles WHERE status=1 ORDER BY sort_order ASC, id ASC");
$active_shipping_vehicles = $db->getResult();
if (!is_array($active_shipping_vehicles)) $active_shipping_vehicles = array();

$db->sql("SELECT id, name FROM shipping_goods_types WHERE status=1 ORDER BY name ASC");
$active_goods_types = $db->getResult();
if (!is_array($active_goods_types)) $active_goods_types = array();

$db->sql("SELECT id, vehicle_type, display_name, base_price, base_km, per_km_price, platform_fee, convenience_fee FROM service_pricing WHERE service_type='passenger' AND status=1 ORDER BY id ASC");
$active_passenger_pricing = $db->getResult();
if (!is_array($active_passenger_pricing)) $active_passenger_pricing = array();

$db->sql("SELECT id, name, mobile, service_type, vehicle_type FROM delivery_boys WHERE status=1 ORDER BY name ASC");
$active_delivery_boys = $db->getResult();
if (!is_array($active_delivery_boys)) $active_delivery_boys = array();

$db->sql("SELECT * FROM parcel_settings ORDER BY id ASC LIMIT 1");
$ps_res = $db->getResult();
$active_parcel_settings = !empty($ps_res) ? $ps_res[0] : array();

$initial_service = isset($_GET['service']) ? strtolower(trim($_GET['service'])) : 'food';
if (!in_array($initial_service, array('food', 'parcel', 'shipping', 'passenger'))) {
    $initial_service = 'food';
}
?>


<style>
.order-section-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    padding: 20px;
    margin-bottom: 22px;
    border-top: 3px solid #3051d3;
}
.order-section-card.accent-green {
    border-top-color: #2e7d32;
}
.order-section-card.accent-orange {
    border-top-color: #f57c00;
}
.order-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-top: 0;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.order-section-title i {
    color: #3051d3;
}
.product-card-item {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 10px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    background: #fafbfc;
}
.product-card-item:hover {
    border-color: #3051d3;
    background: #fff;
    box-shadow: 0 2px 8px rgba(48,81,211,0.1);
}
.price-breakdown-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 14px;
    color: #555;
    border-bottom: 1px dashed #e9ecef;
}
.price-breakdown-row.total-row {
    font-size: 18px;
    font-weight: 700;
    color: #2e7d32;
    border-bottom: none;
    padding-top: 14px;
}
.payment-option-card {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    background: #fff;
}
.payment-option-card:hover {
    border-color: #3051d3;
}
.payment-option-card.selected {
    border-color: #3051d3;
    background: #f4f7ff;
}
.payment-option-card input[type="radio"] {
    margin-right: 12px;
    transform: scale(1.3);
}
.payment-option-info {
    flex-grow: 1;
}
.payment-option-title {
    font-weight: 700;
    font-size: 15px;
    color: #222;
}
.payment-option-desc {
    font-size: 12px;
    color: #666;
    margin-top: 2px;
}
.cart-qty-btn {
    width: 28px;
    height: 28px;
    padding: 0;
    line-height: 26px;
    text-align: center;
    font-weight: bold;
    border-radius: 4px;
}
.cart-qty-input {
    width: 44px;
    height: 28px;
    text-align: center;
    padding: 2px 4px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}
.product-thumbnail {
    width: 52px;
    height: 52px;
    border-radius: 6px;
    object-fit: cover;
    background: #eee;
    margin-right: 12px;
    border: 1px solid #ddd;
}
.badge-stock {
    font-size: 11px;
    padding: 3px 6px;
    border-radius: 4px;
}
.empty-cart-state {
    text-align: center;
    padding: 36px 15px;
    color: #999;
}
.empty-cart-state i {
    font-size: 42px;
    margin-bottom: 12px;
    color: #ccc;
}
.customer-status-badge {
    margin-left: 10px;
    font-size: 11px;
    display: none;
}
#delivery_map_canvas {
    width: 100%;
    height: 275px;
    border-radius: 6px;
    border: 1px solid #ced4da;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
}
.pac-container {
    z-index: 99999 !important;
}
#orderSuccessModal {
    z-index: 105000 !important;
}
.modal-backdrop {
    z-index: 104000 !important;
}

/* Service Navigation Tabs */
.service-nav-container {
    background: #fff;
    padding: 8px;
    border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    display: flex;
    gap: 10px;
    overflow-x: auto;
}
.service-nav-tab {
    flex: 1;
    min-width: 170px;
    padding: 13px 20px;
    border: 2px solid transparent;
    border-radius: 9px;
    background: #f8f9fa;
    color: #495057;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    outline: none !important;
}
.service-nav-tab:hover {
    background: #eef2ff;
    color: #3051d3;
}
.service-nav-tab.active {
    background: #3051d3;
    color: #fff;
    box-shadow: 0 4px 14px rgba(48,81,211,0.3);
}
.service-nav-tab.active[data-tab="parcel"] {
    background: #0284c7;
    box-shadow: 0 4px 14px rgba(2,132,199,0.3);
}
.service-nav-tab.active[data-tab="shipping"] {
    background: #ea580c;
    box-shadow: 0 4px 14px rgba(234,88,12,0.3);
}
.service-nav-tab.active[data-tab="passenger"] {
    background: #16a34a;
    box-shadow: 0 4px 14px rgba(22,163,74,0.3);
}
.service-nav-tab i {
    font-size: 17px;
}

/* Service Item Type Chips */
.item-chip-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 14px;
}
.item-chip {
    padding: 8px 14px;
    border-radius: 20px;
    border: 1.5px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    user-select: none;
}
.item-chip:hover {
    border-color: #0284c7;
    color: #0284c7;
    background: #f0f9ff;
}
.item-chip.active {
    background: #0284c7;
    color: #fff;
    border-color: #0284c7;
    box-shadow: 0 2px 8px rgba(2,132,199,0.25);
}

/* Vehicle Selector Cards */
.vehicle-selector-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}
.vehicle-select-card {
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
}
.vehicle-select-card:hover {
    border-color: #3051d3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.vehicle-select-card.active {
    border-color: #3051d3;
    background: #f0f4ff;
    box-shadow: 0 4px 14px rgba(48,81,211,0.18);
}
.vehicle-select-card.active.passenger-active {
    border-color: #16a34a;
    background: #f0fdf4;
    box-shadow: 0 4px 14px rgba(22,163,74,0.18);
}
.vehicle-select-card.active.shipping-active {
    border-color: #ea580c;
    background: #fff7ed;
    box-shadow: 0 4px 14px rgba(234,88,12,0.18);
}
.vehicle-card-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 700;
}

/* Helper selector pills */
.helper-btn-group {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
}
.helper-btn {
    flex: 1;
    padding: 9px 12px;
    border-radius: 6px;
    border: 1.5px solid #d1d5db;
    background: #fff;
    color: #4b5563;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}
.helper-btn:hover {
    border-color: #ea580c;
    color: #ea580c;
}
.helper-btn.active {
    background: #ea580c;
    color: #fff;
    border-color: #ea580c;
    box-shadow: 0 2px 6px rgba(234,88,12,0.25);
}

/* Passenger Count Box */
.passenger-counter-box {
    display: inline-flex;
    align-items: center;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.passenger-counter-btn {
    width: 38px;
    height: 38px;
    background: #f3f4f6;
    border: none;
    font-size: 18px;
    font-weight: bold;
    color: #374151;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.passenger-counter-btn:hover:not(:disabled) {
    background: #e5e7eb;
}
.passenger-counter-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
.passenger-counter-val {
    width: 48px;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
    color: #111827;
}

/* Route map container */
#svc_route_map_canvas {
    width: 100%;
    height: 320px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.route-distance-chip {
    background: #1e293b;
    color: #fff;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.image-upload-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}
.image-upload-preview img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #d1d5db;
}
</style>

<div class="content-wrapper" style="padding: 20px;">
    <section class="content-header" style="margin-bottom: 20px; padding: 0;">
        <h1>
            <i class="fa fa-shopping-basket" id="hdr_main_icon" style="color: #3051d3;"></i> <span id="hdr_main_title">Create Food Order</span>
            <small id="hdr_main_subtitle">Order for walk-in / phone-in customers directly from admin panel</small>
        </h1>
        <ol class="breadcrumb" style="background: transparent; padding: 0; top: 15px;">
            <li><a href="home.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li class="active" id="breadcrumb_active_service">Create Order</li>
        </ol>
    </section>

    <!-- SERVICE MODE SELECTOR TABS -->
    <div class="service-nav-container">
        <button type="button" class="service-nav-tab <?=($initial_service==='food')?'active':''?>" data-tab="food" data-service="food" onclick="switchServiceTab('food')">
            <i class="fa fa-cutlery"></i> <span>Food Order</span>
        </button>
        <button type="button" class="service-nav-tab <?=($initial_service==='parcel')?'active':''?>" data-tab="parcel" data-service="parcel" onclick="switchServiceTab('parcel')">
            <i class="fa fa-cube"></i> <span>Parcel Delivery</span>
        </button>
        <button type="button" class="service-nav-tab <?=($initial_service==='shipping')?'active':''?>" data-tab="shipping" data-service="shipping" onclick="switchServiceTab('shipping')">
            <i class="fa fa-truck"></i> <span>Shipping &amp; Cargo</span>
        </button>
        <button type="button" class="service-nav-tab <?=($initial_service==='passenger')?'active':''?>" data-tab="passenger" data-service="passenger" onclick="switchServiceTab('passenger')">
            <i class="fa fa-car"></i> <span>Passenger Ride</span>
        </button>
    </div>

    <!-- 1. FOOD ORDER PANEL -->
    <div id="panel_food_order" class="service-order-panel" style="<?=($initial_service!=='food')?'display:none;':''?>">
    <div class="row">
        <!-- LEFT COLUMN: Customer Info, Delivery Map & Location, Zone & Restaurant -->
        <div class="col-md-6">
            <!-- 1. Customer Information & Delivery Map Card -->
            <div class="order-section-card">
                <h4 class="order-section-title">
                    <i class="fa fa-user"></i> 1. Customer Details & Delivery Location
                    <span id="cust_lookup_badge" class="customer-status-badge label label-success">Existing Customer Found</span>
                </h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cust_mobile">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon"><i class="fa fa-phone"></i> +91</span>
                                <input type="tel" id="cust_mobile" class="form-control" placeholder="10-digit mobile" maxlength="10" autocomplete="off" required>
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" id="btn_lookup_phone" title="Search existing customer"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cust_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="cust_name" class="form-control" placeholder="Enter customer full name" required>
                        </div>
                    </div>
                </div>

                <!-- Google Map Search & Interactive Picker -->
                <div class="form-group" style="margin-bottom: 8px;">
                    <label for="map_place_search"><i class="fa fa-map-marker text-danger"></i> Pin Delivery Location on Map <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="map_place_search" class="form-control" placeholder="Search address, landmark or town on Google Maps...">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button" id="btn_detect_gps" title="Locate via GPS"><i class="fa fa-crosshairs text-success"></i> GPS</button>
                        </span>
                    </div>
                </div>

                <!-- Map Canvas -->
                <div class="form-group" style="margin-bottom: 12px;">
                    <div id="delivery_map_canvas"></div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                        <small class="text-muted"><i class="fa fa-info-circle text-primary"></i> Click map or drag red marker to pinpoint exact delivery spot.</small>
                        <span id="coords_indicator" class="label label-default" style="font-size: 11px;">Lat: <?=$store_lat?>, Lng: <?=$store_lng?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cust_address">Delivery Address (Auto-filled from Map / Editable) <span class="text-danger">*</span></label>
                    <textarea id="cust_address" class="form-control" rows="2" placeholder="Full delivery address / flat / door no / landmark" required></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="cust_notes">Order Notes (Optional)</label>
                    <input type="text" id="cust_notes" class="form-control" placeholder="e.g. Less spicy, call before arriving, deliver to 2nd floor">
                </div>
                
                <input type="hidden" id="cust_latitude" value="<?=$store_lat?>">
                <input type="hidden" id="cust_longitude" value="<?=$store_lng?>">
            </div>

            <!-- 2. Zone & Restaurant Selection Card -->
            <div class="order-section-card">
                <h4 class="order-section-title">
                    <i class="fa fa-map-marker"></i> 2. Zone & Restaurant Selection
                </h4>

                <div class="form-group">
                    <label for="order_zone">Delivery Zone <span class="text-danger">*</span></label>
                    <select id="order_zone" class="form-control select2" required>
                        <option value="">-- Select Delivery Zone --</option>
                        <?php foreach($active_zones as $z){ ?>
                            <option value="<?=$z['id']?>" data-lat="<?=$z['center_lat']?>" data-lng="<?=$z['center_lng']?>"><?=htmlspecialchars($z['name'])?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="order_seller">Select Restaurant / Store <span class="text-danger">*</span></label>
                    <select id="order_seller" class="form-control" disabled required>
                        <option value="">-- First Select a Zone above --</option>
                    </select>
                    <div id="seller_status_notice" style="margin-top: 8px; display: none;"></div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Products Catalog & Live Cart & Payment -->
        <div class="col-md-6">
            <!-- 3. Product Catalog Card -->
            <div class="order-section-card accent-orange">
                <h4 class="order-section-title" style="justify-content: space-between;">
                    <span><i class="fa fa-cutlery"></i> 3. Select Products</span>
                    <span id="product_count_badge" class="badge" style="background: #f57c00;">0 Products</span>
                </h4>

                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-search"></i></span>
                        <input type="text" id="product_search_filter" class="form-control" placeholder="Search product name..." disabled>
                    </div>
                </div>

                <div id="products_container" style="max-height: 340px; overflow-y: auto; padding-right: 4px;">
                    <div class="empty-cart-state" id="products_placeholder">
                        <i class="fa fa-store"></i>
                        <p>Select a zone and restaurant to view their food menu.</p>
                    </div>
                </div>
            </div>

            <!-- 4. Selected Items (Live Cart) -->
            <div class="order-section-card accent-green">
                <h4 class="order-section-title" style="justify-content: space-between;">
                    <span><i class="fa fa-shopping-cart"></i> 4. Order Items & Price Details</span>
                    <button type="button" class="btn btn-xs btn-default text-danger" id="btn_clear_cart" style="display: none;"><i class="fa fa-trash"></i> Clear All</button>
                </h4>

                <div class="table-responsive" style="margin-bottom: 15px;">
                    <table class="table table-bordered table-striped" id="cart_table" style="display: none; margin-bottom: 0;">
                        <thead>
                            <tr class="bg-gray-light">
                                <th>Item</th>
                                <th style="width: 100px; text-align: right;">Unit Price</th>
                                <th style="width: 130px; text-align: center;">Qty</th>
                                <th style="width: 110px; text-align: right;">Total</th>
                                <th style="width: 40px; text-align: center;"></th>
                            </tr>
                        </thead>
                        <tbody id="cart_items_tbody">
                            <!-- Injected dynamically -->
                        </tbody>
                    </table>

                    <div class="empty-cart-state" id="cart_empty_state">
                        <i class="fa fa-shopping-cart"></i>
                        <p>Your order cart is empty. Add products from the menu above.</p>
                    </div>
                </div>

                <!-- Price Details Breakdown (User App Style) -->
                <div id="price_breakdown_box" style="display: none; background: #fdfdfd; border: 1px solid #edf0f5; border-radius: 6px; padding: 14px 16px; margin-bottom: 18px;">
                    <div class="price-breakdown-row">
                        <span>Items Subtotal:</span>
                        <span id="txt_subtotal" style="font-weight: 600;">&#8377; 0.00</span>
                    </div>
                    <div class="price-breakdown-row" style="align-items: center;">
                        <span>Delivery Charge <span id="txt_delivery_distance"></span>:</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span>&#8377;</span>
                            <input type="number" id="input_delivery_charge" class="form-control input-sm" style="width: 80px; text-align: right; height: 26px; padding: 2px 6px;" value="<?=$default_delivery_charge?>" min="0" step="1">
                            <button type="button" id="btn_reset_delivery_charge" class="btn btn-default btn-xs" style="height: 26px; padding: 2px 6px; display: none;" title="Reset to auto-calculated delivery charge"><i class="fa fa-refresh"></i></button>
                        </div>
                    </div>
                    <div class="price-breakdown-row">
                        <span>Platform Fee:</span>
                        <span id="txt_platform_fee">&#8377; 0.00</span>
                    </div>
                    <div class="price-breakdown-row">
                        <span>Convenience Fee:</span>
                        <span id="txt_convenience_fee">&#8377; 0.00</span>
                    </div>
                    <div class="price-breakdown-row" id="row_tax" style="display: none;">
                        <span>Taxes & GST:</span>
                        <span id="txt_tax_amount">&#8377; 0.00</span>
                    </div>
                    <div class="price-breakdown-row total-row">
                        <span>Final Total:</span>
                        <span id="txt_final_total">&#8377; 0.00</span>
                    </div>
                </div>

                <!-- 5. Payment Method Selection -->
                <div id="payment_method_section" style="display: none;">
                    <label style="margin-bottom: 10px; font-weight: 700; color: #333;">Select Payment Type:</label>
                    
                    <!-- Paid to Admin Option -->
                    <label class="payment-option-card selected" for="pay_paid_admin" id="card_paid_admin">
                        <input type="radio" name="payment_method" id="pay_paid_admin" value="Paid to Admin" checked>
                        <div class="payment-option-info">
                            <div class="payment-option-title"><i class="fa fa-check-circle text-primary"></i> Paid to Admin</div>
                            <div class="payment-option-desc">Paid in cash or direct UPI to admin. <strong class="text-success">Delivery boy does NOT collect cash.</strong></div>
                        </div>
                    </label>

                    <!-- Cash on Delivery Option -->
                    <label class="payment-option-card" for="pay_cod" id="card_cod">
                        <input type="radio" name="payment_method" id="pay_cod" value="Cash on Delivery">
                        <div class="payment-option-info">
                            <div class="payment-option-title"><i class="fa fa-money text-warning"></i> Cash on Delivery (COD)</div>
                            <div class="payment-option-desc">Delivery boy collects cash payment from customer upon delivery.</div>
                        </div>
                    </label>

                    <!-- Place Order Button -->
                    <div style="margin-top: 20px;">
                        <button type="button" class="btn btn-success btn-lg btn-block" id="btn_place_order" style="font-weight: 700; padding: 14px; font-size: 17px; box-shadow: 0 4px 12px rgba(46,125,50,0.3);">
                            <i class="fa fa-check"></i> Place Food Order Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div><!-- End #panel_food_order -->

    <!-- ============================================================== -->
    <!-- 2. SERVICE ORDER PANEL: PARCEL / SHIPPING / PASSENGER RIDE      -->
    <!-- ============================================================== -->
    <div id="panel_service_order" class="service-order-panel" style="<?=($initial_service==='food')?'display:none;':''?>">
        <div class="row">
            <!-- LEFT COLUMN: Customer Info, Interactive Dual Map, Pickup & Drop, Recipient -->
            <div class="col-md-6">
                <!-- 1. Customer Details (Sender / Passenger) -->
                <div class="order-section-card">
                    <h4 class="order-section-title">
                        <i class="fa fa-user"></i> <span id="svc_cust_section_title">1. Customer / Sender Details</span>
                        <span id="svc_cust_lookup_badge" class="customer-status-badge label label-success" style="display:none;">Existing Customer Found</span>
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_cust_mobile">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i> +91</span>
                                    <input type="tel" id="svc_cust_mobile" class="form-control" placeholder="10-digit mobile" maxlength="10" autocomplete="off" required>
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" id="svc_btn_lookup_phone" title="Search existing customer"><i class="fa fa-search"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_cust_name"><span id="svc_lbl_cust_name">Sender / Customer Name</span> <span class="text-danger">*</span></label>
                                <input type="text" id="svc_cust_name" class="form-control" placeholder="Enter customer full name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="svc_order_zone">Pickup Service Zone <span class="text-danger">*</span></label>
                        <select id="svc_order_zone" class="form-control select2" required style="width: 100%;">
                            <option value="">-- Auto-detected from pickup pin or select manually --</option>
                            <?php foreach($active_zones as $z){ ?>
                                <option value="<?=$z['id']?>" data-lat="<?=$z['center_lat']?>" data-lng="<?=$z['center_lng']?>"><?=htmlspecialchars($z['name'])?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- 2. Interactive Route Locations & Google Map Card -->
                <div class="order-section-card accent-green">
                    <h4 class="order-section-title" style="justify-content: space-between;">
                        <span><i class="fa fa-map-marker text-success"></i> 2. Pickup &amp; Drop Locations</span>
                        <span id="svc_distance_badge" class="route-distance-chip" style="display:none;"><i class="fa fa-road"></i> 0.0 km</span>
                    </h4>

                    <!-- Pickup Search Input -->
                    <div class="form-group" style="margin-bottom: 10px;">
                        <label for="svc_map_pickup_search" style="color: #15803d;">
                            <i class="fa fa-map-pin text-success"></i> <span id="svc_lbl_pickup_search">Pickup Location (Where from?)</span> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon" style="background:#f0fdf4; color:#16a34a; font-weight:bold;">P</span>
                            <input type="text" id="svc_map_pickup_search" class="form-control" placeholder="Search pickup address, landmark, building...">
                            <span class="input-group-btn">
                                <button class="btn btn-default" type="button" id="svc_btn_detect_gps" title="Locate via GPS"><i class="fa fa-crosshairs text-success"></i> GPS</button>
                            </span>
                        </div>
                    </div>

                    <!-- Drop Search Input -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="svc_map_drop_search" style="color: #b91c1c;">
                            <i class="fa fa-flag-checkered text-danger"></i> <span id="svc_lbl_drop_search">Drop Location (Where to?)</span> <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-addon" style="background:#fef2f2; color:#dc2626; font-weight:bold;">D</span>
                            <input type="text" id="svc_map_drop_search" class="form-control" placeholder="Search destination address, area, town...">
                        </div>
                    </div>

                    <!-- Route Map Canvas -->
                    <div class="form-group" style="margin-bottom: 10px;">
                        <div id="svc_route_map_canvas"></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px; flex-wrap: wrap; gap: 6px;">
                            <small class="text-muted"><i class="fa fa-info-circle text-primary"></i> Drag green pin <strong>[P]</strong> for pickup &amp; red pin <strong>[D]</strong> for destination.</small>
                            <span id="svc_coords_indicator" class="label label-default" style="font-size: 11px;">Pins: Pending selection</span>
                        </div>
                    </div>

                    <!-- Address Textareas -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_pickup_address" style="font-size: 12px;">Full Pickup Address <span class="text-danger">*</span></label>
                                <textarea id="svc_pickup_address" class="form-control" rows="2" placeholder="Full pickup address / flat / landmark" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_drop_address" style="font-size: 12px;">Full Drop Address <span class="text-danger">*</span></label>
                                <textarea id="svc_drop_address" class="form-control" rows="2" placeholder="Full drop address / door no / landmark" required></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Time (For Parcel & Shipping) -->
                    <div class="form-group" id="svc_pickup_time_group" style="margin-bottom: 0;">
                        <label for="svc_pickup_time"><i class="fa fa-clock-o text-primary"></i> Pickup Timing</label>
                        <div class="row">
                            <div class="col-xs-6">
                                <select id="svc_pickup_time_preset" class="form-control">
                                    <option value="Immediate / Now">Immediate / Now (within 1 hr)</option>
                                    <option value="Today Evening">Today Evening</option>
                                    <option value="Tomorrow Morning">Tomorrow Morning</option>
                                    <option value="custom">Custom Time...</option>
                                </select>
                            </div>
                            <div class="col-xs-6">
                                <input type="text" id="svc_pickup_time" class="form-control" placeholder="e.g. 05:30 PM" value="Immediate / Now">
                            </div>
                        </div>
                    </div>

                    <!-- Hidden Coords -->
                    <input type="hidden" id="svc_pickup_lat" value="<?=$store_lat?>">
                    <input type="hidden" id="svc_pickup_lng" value="<?=$store_lng?>">
                    <input type="hidden" id="svc_drop_lat" value="<?=$store_lat + 0.02?>">
                    <input type="hidden" id="svc_drop_lng" value="<?=$store_lng + 0.02?>">
                </div>

                <!-- 3. Recipient Information Card (Hidden for Passenger, visible for Parcel & Shipping) -->
                <div class="order-section-card" id="card_svc_recipient">
                    <h4 class="order-section-title">
                        <i class="fa fa-address-book"></i> 3. Recipient (Delivery Contact)
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_recipient_name">Recipient Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="svc_recipient_name" class="form-control" placeholder="Person receiving the delivery">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="svc_recipient_phone">Recipient Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-phone"></i> +91</span>
                                    <input type="tel" id="svc_recipient_phone" class="form-control" placeholder="10-digit mobile" maxlength="10" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Service Specifics, Pricing, Payment & Driver Assignment -->
            <div class="col-md-6">
                <!-- 4. Service Specifics Card -->
                <div class="order-section-card accent-orange" id="card_svc_details">
                    <h4 class="order-section-title">
                        <i class="fa fa-list-alt text-warning"></i> <span id="svc_details_section_title">4. Parcel Details</span>
                    </h4>

                    <!-- 4A. PARCEL OPTIONS -->
                    <div id="svc_parcel_container">
                        <label style="font-weight: 700; color: #333; margin-bottom: 8px;">Select Parcel Item Type <span class="text-danger">*</span></label>
                        <div class="item-chip-group" id="parcel_item_type_chips">
                            <?php foreach($active_item_types as $idx => $it){ ?>
                                <div class="item-chip <?=($idx===0)?'active':''?>" data-id="<?=$it['id']?>" data-name="<?=htmlspecialchars($it['name'])?>">
                                    <i class="fa fa-cube text-primary"></i> <?=htmlspecialchars($it['name'])?>
                                </div>
                            <?php } ?>
                            <div class="item-chip" data-id="0" data-name="Others">
                                <i class="fa fa-pencil text-muted"></i> Others...
                            </div>
                        </div>
                        <input type="hidden" id="svc_item_type_id" value="<?=$active_item_types[0]['id'] ?? ''?>">
                        <input type="hidden" id="svc_item_type_name" value="<?=$active_item_types[0]['name'] ?? ''?>">

                        <!-- Custom item type input if 'Others' is clicked -->
                        <div class="form-group" id="group_other_item_type" style="display:none;">
                            <label for="svc_custom_item_name">Custom Item Description <span class="text-danger">*</span></label>
                            <input type="text" id="svc_custom_item_name" class="form-control" placeholder="e.g. Legal Agreement Documents, House Keys">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_weight_kg">Approximate Weight (kg)</label>
                                    <div class="input-group">
                                        <input type="number" id="svc_weight_kg" class="form-control" value="1.0" min="0.1" step="0.5">
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                    <small class="text-muted"><i class="fa fa-info-circle"></i> Capacity up to <?=$active_parcel_settings['max_weight_kg'] ?? 15?> kg</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_parcel_images">Parcel Photos (Optional)</label>
                                    <input type="file" id="svc_parcel_images" class="form-control" multiple accept="image/*">
                                    <div id="svc_image_preview_parcel" class="image-upload-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="svc_parcel_notes">Delivery Notes / Special Instructions</label>
                            <input type="text" id="svc_parcel_notes" class="form-control" placeholder="e.g. Handle with care, fragile, call before arriving">
                        </div>
                    </div>

                    <!-- 4B. SHIPPING OPTIONS -->
                    <div id="svc_shipping_container" style="display: none;">
                        <label style="font-weight: 700; color: #333; margin-bottom: 8px;">Select Shipping Vehicle <span class="text-danger">*</span></label>
                        <div class="vehicle-selector-grid" id="shipping_vehicle_grid">
                            <?php foreach($active_shipping_vehicles as $idx => $v){ ?>
                                <div class="vehicle-select-card <?=($idx===0)?'active shipping-active':''?>" 
                                     data-id="<?=$v['id']?>" 
                                     data-name="<?=htmlspecialchars($v['name'])?>" 
                                     data-base-price="<?=$v['base_price']?>" 
                                     data-base-km="<?=$v['base_km']?>" 
                                     data-per-km="<?=$v['per_km_price']?>" 
                                     data-helper-price="<?=$v['helper_price']?>">
                                    <span class="vehicle-card-badge label label-warning"><?=htmlspecialchars($v['capacity_label'])?></span>
                                    <div style="font-size: 15px; font-weight: 700; color: #1e293b; margin-top: 4px; padding-right: 70px;">
                                        <i class="fa fa-truck text-warning"></i> <?=htmlspecialchars($v['name'])?>
                                    </div>
                                    <div style="margin-top: 10px; font-size: 13px; color: #64748b;">
                                        Base: <strong>&#8377; <?=number_format((float)$v['base_price'], 0)?></strong> (<?=$v['base_km']?> km) &bull; &#8377; <?=number_format((float)$v['per_km_price'], 0)?> / km
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <input type="hidden" id="svc_shipping_vehicle_id" value="<?=$active_shipping_vehicles[0]['id'] ?? ''?>">
                        <input type="hidden" id="svc_shipping_vehicle_name" value="<?=$active_shipping_vehicles[0]['name'] ?? ''?>">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_goods_type">Goods / Cargo Type <span class="text-danger">*</span></label>
                                    <select id="svc_goods_type" class="form-control">
                                        <option value="">-- Select Goods Type --</option>
                                        <?php foreach($active_goods_types as $gt){ ?>
                                            <option value="<?=htmlspecialchars($gt['name'])?>"><?=htmlspecialchars($gt['name'])?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Helpers Required</label>
                                    <div class="helper-btn-group" id="helper_btn_group">
                                        <div class="helper-btn active" data-count="0">No Helper (0)</div>
                                        <div class="helper-btn" data-count="1">+1 Helper</div>
                                        <div class="helper-btn" data-count="2">+2 Helpers</div>
                                    </div>
                                    <input type="hidden" id="svc_helper_count" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_shipping_weight">Approx Weight / Quantity</label>
                                    <input type="text" id="svc_shipping_weight" class="form-control" placeholder="e.g. 500 kg, 12 boxes, 1 sofa set">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_shipping_images">Cargo Photos (Optional)</label>
                                    <input type="file" id="svc_shipping_images" class="form-control" multiple accept="image/*">
                                    <div id="svc_image_preview_shipping" class="image-upload-preview"></div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="svc_shipping_notes">Loading / Unloading Instructions</label>
                            <input type="text" id="svc_shipping_notes" class="form-control" placeholder="e.g. Ground floor pickup, elevator available at drop">
                        </div>
                    </div>

                    <!-- 4C. PASSENGER OPTIONS -->
                    <div id="svc_passenger_container" style="display: none;">
                        <label style="font-weight: 700; color: #333; margin-bottom: 8px;">Select Ride Vehicle <span class="text-danger">*</span></label>
                        <div class="vehicle-selector-grid" id="passenger_vehicle_grid">
                            <div class="vehicle-select-card active passenger-active" data-vehicle="bike" data-max="1">
                                <span class="vehicle-card-badge label label-success">1 Seat</span>
                                <div style="font-size: 15px; font-weight: 700; color: #1e293b;">
                                    <i class="fa fa-motorcycle text-success"></i> Bike Taxi
                                </div>
                                <div style="margin-top: 8px; font-size: 12px; color: #64748b;">
                                    Fastest &amp; affordable single rider
                                </div>
                            </div>
                            <div class="vehicle-select-card" data-vehicle="auto" data-max="3">
                                <span class="vehicle-card-badge label label-success">Up to 3 Persons</span>
                                <div style="font-size: 15px; font-weight: 700; color: #1e293b;">
                                    <i class="fa fa-taxi text-warning"></i> Auto Rickshaw
                                </div>
                                <div style="margin-top: 8px; font-size: 12px; color: #64748b;">
                                    Budget city travel for 1-3 people
                                </div>
                            </div>
                            <div class="vehicle-select-card" data-vehicle="cab" data-max="4">
                                <span class="vehicle-card-badge label label-success">Up to 4 Persons</span>
                                <div style="font-size: 15px; font-weight: 700; color: #1e293b;">
                                    <i class="fa fa-car text-primary"></i> Cab / Car
                                </div>
                                <div style="margin-top: 8px; font-size: 12px; color: #64748b;">
                                    Comfortable AC car ride
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="svc_passenger_vehicle" value="bike">

                        <div class="row" style="margin-top: 15px;">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Number of Passengers</label>
                                    <div>
                                        <div class="passenger-counter-box">
                                            <button type="button" class="passenger-counter-btn" id="svc_btn_pass_minus">&minus;</button>
                                            <span class="passenger-counter-val" id="svc_pass_count_display">1</span>
                                            <button type="button" class="passenger-counter-btn" id="svc_btn_pass_plus">&plus;</button>
                                        </div>
                                        <input type="hidden" id="svc_passenger_count" value="1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="svc_passenger_notes">Rider Notes / Pickup Landmark</label>
                                    <input type="text" id="svc_passenger_notes" class="form-control" placeholder="e.g. Wait near bus stop, 1 suitcase">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Live Fare & Price Details Card -->
                <div class="order-section-card accent-green">
                    <h4 class="order-section-title" style="justify-content: space-between;">
                        <span><i class="fa fa-calculator text-success"></i> 5. Live Fare &amp; Price Breakdown</span>
                        <span id="svc_price_calc_status" class="label label-default" style="font-size: 11px;">Awaiting coordinates</span>
                    </h4>

                    <div id="svc_price_breakdown_box" style="background: #fdfdfd; border: 1px solid #edf0f5; border-radius: 6px; padding: 14px 16px; margin-bottom: 16px;">
                        <div class="price-breakdown-row">
                            <span>Road Distance:</span>
                            <span id="svc_txt_distance" style="font-weight: 600;">0.00 km</span>
                        </div>
                        <div class="price-breakdown-row">
                            <span>Base Fare (<span id="svc_txt_base_km">1 km</span>):</span>
                            <span id="svc_txt_base_price">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row">
                            <span>Distance Charges (<span id="svc_txt_per_km_rate">&#8377; 0/km</span>):</span>
                            <span id="svc_txt_distance_charge">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row" id="svc_row_helper" style="display: none;">
                            <span>Helper Charges:</span>
                            <span id="svc_txt_helper_charge">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row" style="align-items: center;">
                            <span>Base Tariff Total:</span>
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <span>&#8377;</span>
                                <input type="number" id="svc_input_tariff" class="form-control input-sm" style="width: 85px; text-align: right; height: 26px; padding: 2px 6px;" value="0.00" min="0" step="1" readonly>
                                <button type="button" id="svc_btn_edit_tariff" class="btn btn-default btn-xs" style="height: 26px;" title="Override Tariff Manually"><i class="fa fa-pencil"></i></button>
                            </div>
                        </div>
                        <div class="price-breakdown-row">
                            <span>Platform Fee:</span>
                            <span id="svc_txt_platform_fee">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row">
                            <span>Convenience Fee:</span>
                            <span id="svc_txt_convenience_fee">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row" id="svc_row_tax" style="display: none;">
                            <span>Taxes &amp; GST:</span>
                            <span id="svc_txt_tax_amount">&#8377; 0.00</span>
                        </div>
                        <div class="price-breakdown-row total-row">
                            <span>Final Payable Total:</span>
                            <span id="svc_txt_grand_total">&#8377; 0.00</span>
                        </div>
                    </div>

                    <!-- 6. Driver Assignment & Payment Method -->
                    <div style="margin-top: 15px;">
                        <label style="font-weight: 700; color: #333; margin-bottom: 8px;">Delivery Boy / Driver Assignment:</label>
                        
                        <div class="row" style="margin-bottom: 12px;">
                            <div class="col-md-6">
                                <label class="radio-inline" style="font-weight: 600;">
                                    <input type="radio" name="svc_driver_mode" id="svc_driver_broadcast" value="broadcast" checked> Broadcast to nearby drivers
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="radio-inline" style="font-weight: 600;">
                                    <input type="radio" name="svc_driver_mode" id="svc_driver_direct" value="direct"> Assign specific driver
                                </label>
                            </div>
                        </div>

                        <div class="form-group" id="svc_driver_select_group" style="display: none;">
                            <label for="svc_assign_delivery_boy">Select Driver to Assign <span class="text-danger">*</span></label>
                            <select id="svc_assign_delivery_boy" class="form-control select2" style="width: 100%;">
                                <option value="">-- Select Active Driver --</option>
                                <?php foreach($active_delivery_boys as $dboy){ ?>
                                    <option value="<?=$dboy['id']?>" data-vehicle="<?=$dboy['vehicle_type']?>" data-services="<?=$dboy['service_type']?>">
                                        <?=htmlspecialchars($dboy['name'])?> (<?=$dboy['mobile']?>) &bull; <?=ucfirst($dboy['vehicle_type'])?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <label style="font-weight: 700; color: #333; margin-top: 10px; margin-bottom: 8px;">Payment Collection Method:</label>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="payment-option-card selected" for="svc_pay_admin" id="card_svc_pay_admin">
                                    <input type="radio" name="svc_payment_method" id="svc_pay_admin" value="Paid to Admin" checked>
                                    <div class="payment-option-info">
                                        <div class="payment-option-title"><i class="fa fa-check-circle text-primary"></i> Paid to Admin</div>
                                        <div class="payment-option-desc">Paid in cash / UPI to admin. Driver does NOT collect.</div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="payment-option-card" for="svc_pay_cod" id="card_svc_pay_cod">
                                    <input type="radio" name="svc_payment_method" id="svc_pay_cod" value="Cash on Delivery">
                                    <div class="payment-option-info">
                                        <div class="payment-option-title"><i class="fa fa-money text-warning"></i> Pay to Driver (COD)</div>
                                        <div class="payment-option-desc">Driver collects cash payment from customer.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Place Order Button -->
                        <div style="margin-top: 18px;">
                            <button type="button" class="btn btn-primary btn-lg btn-block" id="btn_svc_place_order" style="font-weight: 700; padding: 14px; font-size: 17px; box-shadow: 0 4px 12px rgba(2,132,199,0.3); background:#0284c7; border-color:#0284c7;">
                                <i class="fa fa-check-circle"></i> <span id="btn_svc_place_order_text">Place Parcel Pickup Order</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- End #panel_service_order -->
</div><!-- End .content-wrapper -->


<!-- SUCCESS MODAL -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 105000;">
    <div class="modal-dialog" role="document" style="margin-top: 80px; z-index: 105001;">
        <div class="modal-content" style="border-radius: 8px; text-align: center; padding: 25px 20px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="position: absolute; right: 15px; top: 12px; font-size: 26px; color: #555; opacity: 0.7; outline: none; border: none; background: transparent; cursor: pointer;">&times;</button>
            <div style="margin-bottom: 15px;">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #e8f5e9; color: #2e7d32; font-size: 38px; line-height: 70px; margin: 0 auto;">
                    <i class="fa fa-check"></i>
                </div>
            </div>
            <h3 style="margin-top: 0; font-weight: 700; color: #2e7d32;">Order Placed Successfully!</h3>
            <p style="font-size: 16px; color: #555;">Order ID: <strong id="modal_order_id" style="color: #3051d3; font-size: 20px;">#--</strong></p>
            <p id="modal_order_details" style="color: #666;"></p>
            
            <div style="margin-top: 25px; display: flex; justify-content: center; gap: 12px;">
                <a href="#" id="modal_view_order_btn" class="btn btn-primary btn-md"><i class="fa fa-eye"></i> View Order Details</a>
                <button type="button" class="btn btn-default btn-md" id="modal_create_another_btn"><i class="fa fa-plus"></i> Create Another Order</button>
            </div>
        </div>
    </div>
</div>

<!-- GOOGLE MAPS API SCRIPT WITH PLACES LIBRARY (Using proven key from seller-register.php) -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I&libraries=places&callback=initDeliveryMap" async defer></script>

<script>
var defaultStoreLocation = { lat: <?=$store_lat?>, lng: <?=$store_lng?> };
var deliveryMap = null;
var deliveryMarker = null;
var deliveryGeocoder = null;
var deliveryAutocomplete = null;

function updateMapCoords(lat, lng) {
    $('#cust_latitude').val(lat.toFixed(7));
    $('#cust_longitude').val(lng.toFixed(7));
    $('#coords_indicator').text('Lat: ' + lat.toFixed(5) + ', Lng: ' + lng.toFixed(5));
}

function setDeliveryLocation(lat, lng, address) {
    var loc = { lat: lat, lng: lng };
    if (deliveryMarker) {
        deliveryMarker.setPosition(loc);
    }
    if (deliveryMap) {
        deliveryMap.setCenter(loc);
        deliveryMap.setZoom(16);
    }
    updateMapCoords(lat, lng);

    if (address) {
        $('#cust_address').val(address);
        $('#map_place_search').val(address);
    } else if (deliveryGeocoder) {
        deliveryGeocoder.geocode({ location: loc }, function(results, status) {
            if (status === 'OK' && results && results[0]) {
                var formatted = results[0].formatted_address;
                $('#cust_address').val(formatted);
                $('#map_place_search').val(formatted);
            }
        });
    }
    $(document).trigger('delivery_location_changed', [lat, lng]);
}

// Global callback for Google Maps API
// Food Order Map Init
function initFoodMap() {
    var mapCanvas = document.getElementById('delivery_map_canvas');
    if (!mapCanvas) return;

    deliveryGeocoder = new google.maps.Geocoder();

    deliveryMap = new google.maps.Map(mapCanvas, {
        center: defaultStoreLocation,
        zoom: 14,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });

    deliveryMarker = new google.maps.Marker({
        position: defaultStoreLocation,
        map: deliveryMap,
        draggable: true,
        animation: google.maps.Animation.DROP,
        title: 'Delivery Location'
    });

    updateMapCoords(defaultStoreLocation.lat, defaultStoreLocation.lng);

    google.maps.event.addListener(deliveryMap, 'click', function(e) {
        setDeliveryLocation(e.latLng.lat(), e.latLng.lng());
    });

    google.maps.event.addListener(deliveryMarker, 'dragend', function() {
        var p = deliveryMarker.getPosition();
        setDeliveryLocation(p.lat(), p.lng());
    });

    var searchInput = document.getElementById('map_place_search');
    if (searchInput && google.maps.places) {
        deliveryAutocomplete = new google.maps.places.Autocomplete(searchInput);
        deliveryAutocomplete.bindTo('bounds', deliveryMap);

        deliveryAutocomplete.addListener('place_changed', function() {
            var place = deliveryAutocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) {
                return;
            }
            var loc = place.geometry.location;
            var address = place.formatted_address || place.name || '';
            setDeliveryLocation(loc.lat(), loc.lng(), address);
        });
    }
}

// Service Route Map Variables
var svcRouteMap = null;
var svcPickupMarker = null;
var svcDropMarker = null;
var svcDirectionsService = null;
var svcDirectionsRenderer = null;
var svcGeocoder = null;
var svcPickupAutocomplete = null;
var svcDropAutocomplete = null;

function setSvcPickupLocation(lat, lng, address) {
    var loc = { lat: lat, lng: lng };
    $('#svc_pickup_lat').val(lat.toFixed(7));
    $('#svc_pickup_lng').val(lng.toFixed(7));
    if (svcPickupMarker) svcPickupMarker.setPosition(loc);
    updateSvcCoordsIndicator();

    if (address) {
        $('#svc_pickup_address').val(address);
        $('#svc_map_pickup_search').val(address);
    } else if (svcGeocoder) {
        svcGeocoder.geocode({ location: loc }, function(results, status) {
            if (status === 'OK' && results && results[0]) {
                var formatted = results[0].formatted_address;
                $('#svc_pickup_address').val(formatted);
                $('#svc_map_pickup_search').val(formatted);
            }
        });
    }
    updateSvcRouteAndFare();
}

function setSvcDropLocation(lat, lng, address) {
    var loc = { lat: lat, lng: lng };
    $('#svc_drop_lat').val(lat.toFixed(7));
    $('#svc_drop_lng').val(lng.toFixed(7));
    if (svcDropMarker) svcDropMarker.setPosition(loc);
    updateSvcCoordsIndicator();

    if (address) {
        $('#svc_drop_address').val(address);
        $('#svc_map_drop_search').val(address);
    } else if (svcGeocoder) {
        svcGeocoder.geocode({ location: loc }, function(results, status) {
            if (status === 'OK' && results && results[0]) {
                var formatted = results[0].formatted_address;
                $('#svc_drop_address').val(formatted);
                $('#svc_map_drop_search').val(formatted);
            }
        });
    }
    updateSvcRouteAndFare();
}

function updateSvcCoordsIndicator() {
    var pLat = parseFloat($('#svc_pickup_lat').val());
    var dLat = parseFloat($('#svc_drop_lat').val());
    if (!isNaN(pLat) && !isNaN(dLat)) {
        $('#svc_coords_indicator').html('<span class="text-success font-weight-bold">P: ' + pLat.toFixed(4) + '</span> | <span class="text-danger font-weight-bold">D: ' + dLat.toFixed(4) + '</span>');
    }
}

var currentActiveService = '<?=htmlspecialchars($initial_service)?>';
var isManualTariff = false;
var lastCalculatedTariff = 0;

function updateSvcRouteAndFare() {
    if (currentActiveService === 'food') return;

    var pLat = parseFloat($('#svc_pickup_lat').val());
    var pLng = parseFloat($('#svc_pickup_lng').val());
    var dLat = parseFloat($('#svc_drop_lat').val());
    var dLng = parseFloat($('#svc_drop_lng').val());
    var zoneId = $('#svc_order_zone').val() || '';
    var shippingVehicleId = $('#svc_shipping_vehicle_id').val() || '';
    var helperCount = $('#svc_helper_count').val() || 0;
    var passengerVehicle = $('#svc_passenger_vehicle').val() || 'bike';

    if (isNaN(pLat) || isNaN(pLng) || isNaN(dLat) || isNaN(dLng) || pLat === 0 || dLat === 0) {
        $('#svc_price_calc_status').removeClass('label-success label-info label-danger').addClass('label-default').text('Awaiting coordinates');
        return;
    }

    if (svcDirectionsService && svcDirectionsRenderer) {
        svcDirectionsService.route({
            origin: new google.maps.LatLng(pLat, pLng),
            destination: new google.maps.LatLng(dLat, dLng),
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status) {
            if (status === 'OK' && response && response.routes && response.routes[0]) {
                svcDirectionsRenderer.setDirections(response);
                var leg = response.routes[0].legs[0];
                if (leg && leg.distance) {
                    $('#svc_distance_badge').html('<i class="fa fa-road"></i> ' + leg.distance.text + (leg.duration ? ' &bull; ' + leg.duration.text : '')).fadeIn();
                }
            }
        });
    }

    $('#svc_price_calc_status').removeClass('label-default label-danger label-success').addClass('label-info').html('<i class="fa fa-spinner fa-spin"></i> Calculating...');

    $.ajax({
        url: 'api-firebase/create-admin-order.php',
        type: 'POST',
        data: {
            action: 'calculate_service_fare',
            service_type: currentActiveService,
            pickup_lat: pLat,
            pickup_lng: pLng,
            pickup_latitude: pLat,
            pickup_longitude: pLng,
            drop_lat: dLat,
            drop_lng: dLng,
            drop_latitude: dLat,
            drop_longitude: dLng,
            zone_id: zoneId,
            shipping_vehicle_id: shippingVehicleId,
            helper_count: helperCount,
            passenger_vehicle: passengerVehicle
        },
        dataType: 'json',
        success: function(resp) {
            if (!resp.error) {
                function num(v, def) {
                    var n = parseFloat(v);
                    return isNaN(n) ? (def || 0) : n;
                }

                var basePrice = num(resp.base_price);
                var perKmRate = num(resp.per_km_price);
                var helperCharge = num(resp.helper_charge);
                var tariff = num(resp.tariff);
                var platformFee = num(resp.platform_fee);
                var convenienceFee = num(resp.convenience_fee);
                var taxAmount = num(resp.tax_amount || resp.gst);

                var distanceCharge = (resp.distance_charge !== undefined) ? num(resp.distance_charge) : Math.max(0, tariff - basePrice - helperCharge);
                var grandTotal = (resp.grand_total !== undefined) ? num(resp.grand_total) : ((resp.final_total !== undefined) ? num(resp.final_total) : (tariff + platformFee + convenienceFee + taxAmount));

                $('#svc_txt_distance').text((resp.distance_km || 0) + ' km');
                $('#svc_txt_base_km').text((resp.base_km || 1) + ' km');
                $('#svc_txt_base_price').html('&#8377; ' + basePrice.toFixed(2));
                $('#svc_txt_per_km_rate').html('&#8377; ' + perKmRate.toFixed(2) + '/km');
                $('#svc_txt_distance_charge').html('&#8377; ' + distanceCharge.toFixed(2));

                if (helperCharge > 0) {
                    $('#svc_row_helper').show();
                    $('#svc_txt_helper_charge').html('&#8377; ' + helperCharge.toFixed(2));
                } else {
                    $('#svc_row_helper').hide();
                }

                lastCalculatedTariff = tariff;
                if (!isManualTariff) {
                    $('#svc_input_tariff').val(lastCalculatedTariff.toFixed(2));
                }

                $('#svc_txt_platform_fee').html('&#8377; ' + platformFee.toFixed(2));
                $('#svc_txt_convenience_fee').html('&#8377; ' + convenienceFee.toFixed(2));

                if (taxAmount > 0) {
                    $('#svc_row_tax').show();
                    $('#svc_txt_tax_amount').html('&#8377; ' + taxAmount.toFixed(2));
                } else {
                    $('#svc_row_tax').hide();
                }

                if (resp.zone_id && !$('#svc_order_zone').val()) {
                    $('#svc_order_zone').val(resp.zone_id);
                }

                $('#svc_txt_grand_total').html('&#8377; ' + grandTotal.toFixed(2));
                $('#svc_price_calc_status').removeClass('label-info label-danger label-default').addClass('label-success').text('Fare Calculated');
            } else {
                $('#svc_price_calc_status').removeClass('label-info label-success label-default').addClass('label-danger').text(resp.message || 'Fare error');
            }
        },
        error: function() {
            $('#svc_price_calc_status').removeClass('label-info label-success label-default').addClass('label-danger').text('Calc offline');
        }
    });
}

// Global Service Tab Switcher (Accessible immediately by inline onclick and jQuery)
function switchServiceTab(service) {
    currentActiveService = service;

    // 1. Update Tab Button Styles
    var tabs = document.querySelectorAll('.service-nav-tab');
    for (var i = 0; i < tabs.length; i++) {
        tabs[i].classList.remove('active');
        var tabKey = tabs[i].getAttribute('data-tab') || tabs[i].getAttribute('data-service');
        if (tabKey === service) {
            tabs[i].classList.add('active');
        }
    }
    $('.service-pill').removeClass('active');
    $('.service-pill[data-service="' + service + '"]').addClass('active');

    // 2. Toggle Panels
    var foodPanel = document.getElementById('panel_food_order');
    var svcPanel = document.getElementById('panel_service_order');

    if (service === 'food') {
        $('#hdr_main_icon').attr('class', 'fa fa-shopping-basket').css('color', '#3051d3');
        $('#hdr_main_title').text('Create Food Order');
        $('#hdr_main_subtitle').text('Order for walk-in / phone-in customers directly from admin panel');
        $('#breadcrumb_active_service').text('Create Order');

        if (foodPanel) foodPanel.style.display = 'block';
        if (svcPanel) svcPanel.style.display = 'none';

        setTimeout(function() {
            if (window.google && window.google.maps && window.deliveryMap) {
                google.maps.event.trigger(window.deliveryMap, 'resize');
                if (window.defaultStoreLocation) window.deliveryMap.setCenter(window.defaultStoreLocation);
            }
        }, 150);
        return;
    }

    // Non-food service: Parcel, Shipping, or Passenger
    if (foodPanel) foodPanel.style.display = 'none';
    if (svcPanel) svcPanel.style.display = 'block';

    var parcelBox = document.getElementById('svc_parcel_container');
    var shipBox = document.getElementById('svc_shipping_container');
    var passBox = document.getElementById('svc_passenger_container');
    var recipCard = document.getElementById('card_svc_recipient');
    var timeGroup = document.getElementById('svc_pickup_time_group');

    if (service === 'parcel') {
        $('#hdr_main_icon').attr('class', 'fa fa-cube').css('color', '#0284c7');
        $('#hdr_main_title').text('Create Parcel Delivery Order');
        $('#hdr_main_subtitle').text('Book point-to-point courier & parcel pickup and drop');
        $('#breadcrumb_active_service').text('Parcel Delivery');
        $('#svc_cust_section_title').html('<i class="fa fa-user"></i> 1. Customer / Sender Details');
        $('#svc_lbl_cust_name').text('Sender Full Name');
        $('#svc_lbl_pickup_search').text('Pickup Location (Where from?)');
        $('#svc_lbl_drop_search').text('Delivery Destination (Where to?)');
        $('#svc_details_section_title').text('4. Parcel Details');
        $('#btn_svc_place_order_text').text('Place Parcel Pickup Order');

        if (parcelBox) parcelBox.style.display = 'block';
        if (shipBox) shipBox.style.display = 'none';
        if (passBox) passBox.style.display = 'none';
        if (recipCard) recipCard.style.display = 'block';
        if (timeGroup) timeGroup.style.display = 'block';
    } else if (service === 'shipping') {
        $('#hdr_main_icon').attr('class', 'fa fa-truck').css('color', '#ea580c');
        $('#hdr_main_title').text('Book Shipping & Cargo Order');
        $('#hdr_main_subtitle').text('Book trucks, tempos, and commercial cargo transport');
        $('#breadcrumb_active_service').text('Shipping & Cargo');
        $('#svc_cust_section_title').html('<i class="fa fa-user"></i> 1. Customer / Consignor Details');
        $('#svc_lbl_cust_name').text('Consignor Full Name');
        $('#svc_lbl_pickup_search').text('Loading Location (Where from?)');
        $('#svc_lbl_drop_search').text('Unloading Location (Where to?)');
        $('#svc_details_section_title').text('4. Shipping & Cargo Details');
        $('#btn_svc_place_order_text').text('Book Shipping / Cargo Order');

        if (parcelBox) parcelBox.style.display = 'none';
        if (shipBox) shipBox.style.display = 'block';
        if (passBox) passBox.style.display = 'none';
        if (recipCard) recipCard.style.display = 'block';
        if (timeGroup) timeGroup.style.display = 'block';
    } else if (service === 'passenger') {
        $('#hdr_main_icon').attr('class', 'fa fa-car').css('color', '#16a34a');
        $('#hdr_main_title').text('Book Passenger Ride');
        $('#hdr_main_subtitle').text('Book Bike Taxi, Auto Rickshaw, or Cab ride for passenger');
        $('#breadcrumb_active_service').text('Passenger Ride');
        $('#svc_cust_section_title').html('<i class="fa fa-user"></i> 1. Passenger / Rider Details');
        $('#svc_lbl_cust_name').text('Rider Full Name');
        $('#svc_lbl_pickup_search').text('Pickup Location');
        $('#svc_lbl_drop_search').text('Destination Location');
        $('#svc_details_section_title').text('4. Ride Vehicle & Seats');
        $('#btn_svc_place_order_text').text('Book Passenger Ride Now');

        if (parcelBox) parcelBox.style.display = 'none';
        if (shipBox) shipBox.style.display = 'none';
        if (passBox) passBox.style.display = 'block';
        if (recipCard) recipCard.style.display = 'none';
        if (timeGroup) timeGroup.style.display = 'none';
    }

    setTimeout(function() {
        if (window.google && window.google.maps && window.svcRouteMap) {
            google.maps.event.trigger(window.svcRouteMap, 'resize');
            var pLat = parseFloat($('#svc_pickup_lat').val());
            var pLng = parseFloat($('#svc_pickup_lng').val());
            if (!isNaN(pLat) && !isNaN(pLng) && pLat !== 0) {
                window.svcRouteMap.setCenter({ lat: pLat, lng: pLng });
            }
        }
    }, 150);

    if (typeof updateSvcRouteAndFare === 'function') {
        updateSvcRouteAndFare();
    }
}
window.switchServiceTab = switchServiceTab;

function initServiceRouteMap() {
    var mapCanvas = document.getElementById('svc_route_map_canvas');
    if (!mapCanvas) return;

    svcGeocoder = new google.maps.Geocoder();
    svcDirectionsService = new google.maps.DirectionsService();
    svcDirectionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: '#0284c7',
            strokeOpacity: 0.85,
            strokeWeight: 5
        }
    });

    var centerLoc = { lat: defaultStoreLocation.lat, lng: defaultStoreLocation.lng };
    svcRouteMap = new google.maps.Map(mapCanvas, {
        center: centerLoc,
        zoom: 13,
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true
    });

    svcDirectionsRenderer.setMap(svcRouteMap);

    var dropInitLoc = { lat: defaultStoreLocation.lat + 0.02, lng: defaultStoreLocation.lng + 0.02 };

    svcPickupMarker = new google.maps.Marker({
        position: centerLoc,
        map: svcRouteMap,
        draggable: true,
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
        },
        title: 'Pickup Location (P)'
    });

    svcDropMarker = new google.maps.Marker({
        position: dropInitLoc,
        map: svcRouteMap,
        draggable: true,
        animation: google.maps.Animation.DROP,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
        },
        title: 'Drop Destination (D)'
    });

    setSvcPickupLocation(centerLoc.lat, centerLoc.lng);
    setSvcDropLocation(dropInitLoc.lat, dropInitLoc.lng);

    google.maps.event.addListener(svcPickupMarker, 'dragend', function() {
        var p = svcPickupMarker.getPosition();
        setSvcPickupLocation(p.lat(), p.lng());
    });

    google.maps.event.addListener(svcDropMarker, 'dragend', function() {
        var p = svcDropMarker.getPosition();
        setSvcDropLocation(p.lat(), p.lng());
    });

    var pickupInput = document.getElementById('svc_map_pickup_search');
    if (pickupInput && google.maps.places) {
        svcPickupAutocomplete = new google.maps.places.Autocomplete(pickupInput);
        svcPickupAutocomplete.bindTo('bounds', svcRouteMap);
        svcPickupAutocomplete.addListener('place_changed', function() {
            var place = svcPickupAutocomplete.getPlace();
            if (place && place.geometry && place.geometry.location) {
                var loc = place.geometry.location;
                setSvcPickupLocation(loc.lat(), loc.lng(), place.formatted_address || place.name);
                svcRouteMap.panTo(loc);
            }
        });
    }

    var dropInput = document.getElementById('svc_map_drop_search');
    if (dropInput && google.maps.places) {
        svcDropAutocomplete = new google.maps.places.Autocomplete(dropInput);
        svcDropAutocomplete.bindTo('bounds', svcRouteMap);
        svcDropAutocomplete.addListener('place_changed', function() {
            var place = svcDropAutocomplete.getPlace();
            if (place && place.geometry && place.geometry.location) {
                var loc = place.geometry.location;
                setSvcDropLocation(loc.lat(), loc.lng(), place.formatted_address || place.name);
                svcRouteMap.panTo(loc);
            }
        });
    }
}

// Global callback for Google Maps API
window.initDeliveryMap = function() {
    initFoodMap();
    initServiceRouteMap();
};


$(document).ready(function(){
    var cartItems = {}; // key: variant_id, value: { variant_id, product_id, product_name, measurement, price, qty }
    var loadedProducts = [];

    // Move modal directly under <body> to prevent AdminLTE wrapper stacking-context from trapping it under backdrop
    $('#orderSuccessModal').appendTo('body');

    // Clean up backdrop & body classes when modal is hidden
    $('#orderSuccessModal').on('hidden.bs.modal', function () {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // GPS Location Detector Button
    $('#btn_detect_gps').on('click', function() {
        if (navigator.geolocation) {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            navigator.geolocation.getCurrentPosition(function(pos) {
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-success"></i> GPS');
                var gpsLat = pos.coords.latitude;
                var gpsLng = pos.coords.longitude;
                setDeliveryLocation(gpsLat, gpsLng);
            }, function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-success"></i> GPS');
                alert('Could not detect GPS position: ' + err.message);
            }, { timeout: 10000, enableHighAccuracy: true });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    });

    // 1. Phone number lookup (on blur or typing 10 digits)
    $('#cust_mobile').on('keyup change blur', function(){
        var mobile = $(this).val().trim();
        if(mobile.length === 10){
            lookupCustomer(mobile);
        } else {
            $('#cust_lookup_badge').hide();
        }
    });

    $('#btn_lookup_phone').on('click', function(){
        var mobile = $('#cust_mobile').val().trim();
        if(mobile.length >= 10){
            lookupCustomer(mobile);
        } else {
            alert('Please enter at least a 10-digit mobile number.');
        }
    });

    function lookupCustomer(mobile){
        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: { action: 'get_customer_by_phone', mobile: mobile },
            dataType: 'json',
            success: function(resp){
                if(!resp.error && resp.found && resp.user){
                    $('#cust_name').val(resp.user.name || '');
                    $('#cust_address').val(resp.user.address || '');
                    $('#cust_lookup_badge').text('Existing Customer: ' + (resp.user.name || 'Found')).fadeIn();

                    // If user has saved lat/lng, center map on them
                    if(resp.user.latitude && resp.user.longitude && parseFloat(resp.user.latitude) != 0){
                        var uLat = parseFloat(resp.user.latitude);
                        var uLng = parseFloat(resp.user.longitude);
                        if(!isNaN(uLat) && !isNaN(uLng)){
                            setDeliveryLocation(uLat, uLng, resp.user.address);
                        }
                    }
                } else {
                    $('#cust_lookup_badge').hide();
                }
            }
        });
    }

    // 2. Zone change -> Pan map to Zone & Load Sellers
    $('#order_zone').on('change', function(){
        var zoneId = $(this).val();
        resetSellerAndProducts();
        
        if(!zoneId){
            $('#order_seller').html('<option value="">-- First Select a Zone above --</option>').prop('disabled', true);
            return;
        }

        // Center map to zone center if available
        var selOpt = $('#order_zone option:selected');
        var zLat = parseFloat(selOpt.data('lat'));
        var zLng = parseFloat(selOpt.data('lng'));
        if(!isNaN(zLat) && !isNaN(zLng) && zLat != 0){
            setDeliveryLocation(zLat, zLng);
            if (deliveryMap) {
                deliveryMap.setZoom(14);
            }
        }

        $('#order_seller').html('<option value="">Loading restaurants in this zone...</option>').prop('disabled', true);
        
        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: { action: 'get_sellers_by_zone', zone_id: zoneId },
            dataType: 'json',
            success: function(resp){
                if(!resp.error && resp.sellers && resp.sellers.length > 0){
                    var optHtml = '<option value="">-- Select Restaurant / Store (' + resp.sellers.length + ' available) --</option>';
                    resp.sellers.forEach(function(s){
                        var statusTag = s.is_open ? ' [OPEN]' : ' [CLOSED]';
                        optHtml += '<option value="' + s.id + '" data-open="' + (s.is_open ? '1' : '0') + '" data-address="' + (s.address_display || '') + '" data-lat="' + (s.latitude || '0') + '" data-lng="' + (s.longitude || '0') + '">' + s.display_name + statusTag + ' - ' + (s.address_display || '') + '</option>';
                    });
                    $('#order_seller').html(optHtml).prop('disabled', false);
                } else {
                    $('#order_seller').html('<option value="">No active restaurants found in this zone</option>').prop('disabled', true);
                }
            },
            error: function(){
                $('#order_seller').html('<option value="">Error loading restaurants</option>').prop('disabled', true);
            }
        });
    });

    // 3. Seller change -> Load Products
    $('#order_seller').on('change', function(){
        var sellerId = $(this).val();
        clearCart();
        isManualDeliveryCharge = false;
        
        if(!sellerId){
            $('#products_container').html('<div class="empty-cart-state"><i class="fa fa-store"></i><p>Select a restaurant to view food menu.</p></div>');
            $('#product_search_filter').prop('disabled', true).val('');
            $('#product_count_badge').text('0 Products');
            $('#seller_status_notice').hide();
            $('#txt_delivery_distance').empty();
            return;
        }

        recalculateDeliveryAndFees();

        var isOpen = $('#order_seller option:selected').data('open') == '1';
        if(!isOpen){
            $('#seller_status_notice').html('<span class="label label-warning"><i class="fa fa-warning"></i> Note: This store is currently outside operating hours, but orders can still be placed by admin.</span>').show();
        } else {
            $('#seller_status_notice').hide();
        }

        $('#products_container').html('<div style="text-align:center;padding:30px;"><i class="fa fa-spinner fa-spin fa-2x text-muted"></i><p style="margin-top:10px;">Loading restaurant products...</p></div>');
        $('#product_search_filter').prop('disabled', true);

        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: { action: 'get_products_by_seller', seller_id: sellerId },
            dataType: 'json',
            success: function(resp){
                if(!resp.error && resp.products && resp.products.length > 0){
                    loadedProducts = resp.products;
                    $('#product_count_badge').text(loadedProducts.length + ' Products');
                    $('#product_search_filter').prop('disabled', false).val('');
                    renderProducts(loadedProducts);
                } else {
                    loadedProducts = [];
                    $('#product_count_badge').text('0 Products');
                    $('#products_container').html('<div class="empty-cart-state"><i class="fa fa-exclamation-circle"></i><p>No available products found for this restaurant.</p></div>');
                }
            },
            error: function(){
                $('#products_container').html('<div class="empty-cart-state text-danger"><i class="fa fa-times"></i><p>Error loading menu products.</p></div>');
            }
        });
    });

    // 4. Product Search Filter
    $('#product_search_filter').on('keyup', function(){
        var q = $(this).val().toLowerCase().trim();
        if(!q){
            renderProducts(loadedProducts);
            return;
        }
        var filtered = loadedProducts.filter(function(p){
            return p.product_name.toLowerCase().indexOf(q) !== -1;
        });
        renderProducts(filtered);
    });

    function renderProducts(list){
        if(!list || list.length === 0){
            $('#products_container').html('<div class="empty-cart-state"><i class="fa fa-search"></i><p>No products match your search keyword.</p></div>');
            return;
        }

        var html = '';
        list.forEach(function(p){
            var imgHtml = p.image ? '<img src="' + p.image + '" class="product-thumbnail" onerror="this.src=\'dist/img/default-product.png\';">' : '<div class="product-thumbnail" style="display:flex;align-items:center;justify-content:center;color:#aaa;"><i class="fa fa-cutlery"></i></div>';

            html += '<div class="product-card-item">';
            html += '  <div style="display:flex;align-items:flex-start;">';
            html += imgHtml;
            html += '    <div style="flex-grow:1;">';
            html += '      <strong style="font-size:14px;color:#222;">' + escapeHtml(p.product_name) + '</strong>';
            
            // Variants
            if(p.variants && p.variants.length > 0){
                html += '  <div style="margin-top:6px;">';
                p.variants.forEach(function(v){
                    var priceText = '&#8377; ' + v.effective_price.toFixed(2);
                    var strikeText = '';
                    if(v.price > v.effective_price){
                        strikeText = ' <small style="text-decoration:line-through;color:#999;">&#8377;' + v.price.toFixed(2) + '</small>';
                    }
                    var offerTag = v.is_offer ? ' <span class="label label-danger" style="font-size:9px;">OFFER</span>' : '';

                    html += '<div style="display:flex;justify-content:space-between;align-items:center;background:#fff;border:1px solid #eee;border-radius:4px;padding:5px 8px;margin-bottom:4px;">';
                    html += '  <div>';
                    html += '    <span style="font-size:12px;font-weight:600;">' + escapeHtml(v.measurement) + '</span>' + offerTag + '<br>';
                    html += '    <span style="color:#2e7d32;font-weight:700;">' + priceText + '</span>' + strikeText;
                    html += '  </div>';
                    html += '  <div>';
                    html += '    <button type="button" class="btn btn-xs btn-primary btn-add-variant" data-vid="' + v.variant_id + '" data-pid="' + p.product_id + '" data-name="' + escapeHtml(p.product_name) + '" data-measurement="' + escapeHtml(v.measurement) + '" data-price="' + v.effective_price + '"><i class="fa fa-plus"></i> Add</button>';
                    html += '  </div>';
                    html += '</div>';
                });
                html += '  </div>';
            }
            html += '    </div>';
            html += '  </div>';
            html += '</div>';
        });

        $('#products_container').html(html);
    }

    // 5. Add Variant to Cart
    $(document).on('click', '.btn-add-variant', function(){
        var vid = $(this).data('vid');
        var pid = $(this).data('pid');
        var name = $(this).data('name');
        var measurement = $(this).data('measurement');
        var price = parseFloat($(this).data('price')) || 0;

        if(cartItems[vid]){
            cartItems[vid].qty += 1;
        } else {
            cartItems[vid] = {
                variant_id: vid,
                product_id: pid,
                product_name: name,
                measurement: measurement,
                price: price,
                qty: 1
            };
        }

        renderCart();
    });

    // 6. Quantity Controls in Cart
    $(document).on('click', '.btn-qty-minus', function(){
        var vid = $(this).data('vid');
        if(cartItems[vid]){
            if(cartItems[vid].qty > 1){
                cartItems[vid].qty -= 1;
            } else {
                delete cartItems[vid];
            }
            renderCart();
        }
    });

    $(document).on('click', '.btn-qty-plus', function(){
        var vid = $(this).data('vid');
        if(cartItems[vid]){
            cartItems[vid].qty += 1;
            renderCart();
        }
    });

    $(document).on('change', '.cart-qty-input', function(){
        var vid = $(this).data('vid');
        var val = parseInt($(this).val()) || 0;
        if(val <= 0){
            delete cartItems[vid];
        } else {
            cartItems[vid].qty = val;
        }
        renderCart();
    });

    $(document).on('click', '.btn-remove-item', function(){
        var vid = $(this).data('vid');
        delete cartItems[vid];
        renderCart();
    });

    $('#btn_clear_cart').on('click', function(){
        if(confirm('Are you sure you want to clear all items in this order?')){
            clearCart();
        }
    });

    function clearCart(){
        cartItems = {};
        renderCart();
    }

    function resetSellerAndProducts(){
        $('#order_seller').val('').prop('disabled', true);
        $('#seller_status_notice').hide();
        $('#txt_delivery_distance').empty();
        $('#btn_reset_delivery_charge').hide();
        isManualDeliveryCharge = false;
        loadedProducts = [];
        $('#product_count_badge').text('0 Products');
        $('#product_search_filter').prop('disabled', true).val('');
        $('#products_container').html('<div class="empty-cart-state"><i class="fa fa-store"></i><p>Select a zone and restaurant to view their food menu.</p></div>');
        clearCart();
    }

    // 7. Render Cart & Calculate Fees
    function renderCart(){
        var keys = Object.keys(cartItems);
        if(keys.length === 0){
            $('#cart_table').hide();
            $('#cart_empty_state').show();
            $('#price_breakdown_box').hide();
            $('#payment_method_section').hide();
            $('#btn_clear_cart').hide();
            return;
        }

        var tbodyHtml = '';
        var subtotal = 0.0;

        keys.forEach(function(vid){
            var itm = cartItems[vid];
            var lineTotal = itm.price * itm.qty;
            subtotal += lineTotal;

            tbodyHtml += '<tr>';
            tbodyHtml += '  <td><strong>' + escapeHtml(itm.product_name) + '</strong><br><small class="text-muted">' + escapeHtml(itm.measurement) + '</small></td>';
            tbodyHtml += '  <td style="text-align:right;">&#8377; ' + itm.price.toFixed(2) + '</td>';
            tbodyHtml += '  <td style="text-align:center;">';
            tbodyHtml += '    <button type="button" class="btn btn-default btn-xs cart-qty-btn btn-qty-minus" data-vid="' + vid + '">-</button>';
            tbodyHtml += '    <input type="number" class="cart-qty-input" value="' + itm.qty + '" min="1" data-vid="' + vid + '">';
            tbodyHtml += '    <button type="button" class="btn btn-default btn-xs cart-qty-btn btn-qty-plus" data-vid="' + vid + '">+</button>';
            tbodyHtml += '  </td>';
            tbodyHtml += '  <td style="text-align:right;font-weight:600;">&#8377; ' + lineTotal.toFixed(2) + '</td>';
            tbodyHtml += '  <td style="text-align:center;"><a href="javascript:void(0);" class="text-danger btn-remove-item" data-vid="' + vid + '" title="Remove item"><i class="fa fa-trash"></i></a></td>';
            tbodyHtml += '</tr>';
        });

        $('#cart_items_tbody').html(tbodyHtml);
        $('#cart_table').show();
        $('#cart_empty_state').hide();
        $('#price_breakdown_box').show();
        $('#payment_method_section').show();
        $('#btn_clear_cart').show();

        computeTotals(subtotal);
    }

    var isManualDeliveryCharge = false;
    var calcFeesAjax = null;

    function recalculateDeliveryAndFees(){
        var subtotal = 0.0;
        Object.keys(cartItems).forEach(function(vid){
            subtotal += cartItems[vid].price * cartItems[vid].qty;
        });
        computeTotals(subtotal, isManualDeliveryCharge);
    }

    // 8. Compute Totals (via Backend API)
    function computeTotals(subtotal, isManual){
        if(typeof isManual === 'undefined'){
            isManual = isManualDeliveryCharge;
        }
        var zoneId = $('#order_zone').val() || 0;
        var sellerId = $('#order_seller').val() || 0;
        var deliveryCharge = $('#input_delivery_charge').val() || 0;
        var userLat = $('#cust_latitude').val() || 0;
        var userLng = $('#cust_longitude').val() || 0;

        $('#txt_subtotal').html('&#8377; ' + subtotal.toFixed(2));

        if(calcFeesAjax && calcFeesAjax.readyState !== 4){
            calcFeesAjax.abort();
        }

        if(sellerId && userLat && userLng && parseFloat(userLat) !== 0 && !isManual){
            $('#txt_delivery_distance').html('<small class="text-muted" style="margin-left: 5px; font-weight: normal;"><i class="fa fa-spinner fa-spin"></i> calculating...</small>');
        }

        calcFeesAjax = $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: {
                action: 'calculate_fees',
                zone_id: zoneId,
                seller_id: sellerId,
                subtotal: subtotal,
                delivery_charge: deliveryCharge,
                latitude: userLat,
                longitude: userLng,
                manual_delivery: isManual ? 1 : 0
            },
            dataType: 'json',
            success: function(resp){
                if(!resp.error){
                    // Update delivery charge input if not manually typed by admin
                    if(!isManual && resp.delivery_charge !== undefined){
                        $('#input_delivery_charge').val(resp.delivery_charge);
                    }

                    // Display distance in delivery charge row
                    if(resp.distance_str && resp.distance_km > 0){
                        $('#txt_delivery_distance').html('<span class="label label-info" style="font-size: 11px; font-weight: 600; margin-left: 5px;"><i class="fa fa-road"></i> ' + resp.distance_str + '</span>');
                        var coordsText = 'Lat: ' + parseFloat(userLat).toFixed(5) + ', Lng: ' + parseFloat(userLng).toFixed(5) + ' | Distance: ' + resp.distance_str;
                        $('#coords_indicator').text(coordsText);
                    } else {
                        $('#txt_delivery_distance').empty();
                    }

                    if(isManual){
                        $('#btn_reset_delivery_charge').show();
                    } else {
                        $('#btn_reset_delivery_charge').hide();
                    }

                    $('#txt_platform_fee').html('&#8377; ' + resp.platform_fee.toFixed(2));
                    $('#txt_convenience_fee').html('&#8377; ' + resp.convenience_fee.toFixed(2));
                    if(resp.tax_amount > 0){
                        $('#txt_tax_amount').html('&#8377; ' + resp.tax_amount.toFixed(2));
                        $('#row_tax').show();
                    } else {
                        $('#row_tax').hide();
                    }
                    $('#txt_final_total').html('&#8377; ' + resp.final_total.toFixed(2));
                }
            }
        });
    }

    $('#input_delivery_charge').on('input', function(){
        isManualDeliveryCharge = true;
        $('#btn_reset_delivery_charge').show();
        var subtotal = 0.0;
        Object.keys(cartItems).forEach(function(vid){
            subtotal += cartItems[vid].price * cartItems[vid].qty;
        });
        computeTotals(subtotal, true);
    });

    $('#btn_reset_delivery_charge').on('click', function(){
        isManualDeliveryCharge = false;
        $(this).hide();
        recalculateDeliveryAndFees();
    });

    // When delivery location is pinned or moved on the map
    $(document).on('delivery_location_changed', function(e, lat, lng){
        isManualDeliveryCharge = false;
        recalculateDeliveryAndFees();
    });

    // 9. Payment Option Card Selection
    $('input[name="payment_method"]').on('change', function(){
        $('.payment-option-card').removeClass('selected');
        $(this).closest('.payment-option-card').addClass('selected');
    });

    // 10. Place Order Handler
    $('#btn_place_order').on('click', function(){
        var name = $('#cust_name').val().trim();
        var mobile = $('#cust_mobile').val().trim();
        var address = $('#cust_address').val().trim();
        var zoneId = $('#order_zone').val();
        var sellerId = $('#order_seller').val();
        var notes = $('#cust_notes').val().trim();
        var deliveryCharge = $('#input_delivery_charge').val() || 0;
        var paymentMethod = $('input[name="payment_method"]:checked').val() || 'Paid to Admin';
        var latitude = $('#cust_latitude').val() || '0';
        var longitude = $('#cust_longitude').val() || '0';

        if(!mobile || mobile.length < 10){
            alert('Please enter a valid 10-digit customer mobile number.');
            $('#cust_mobile').focus();
            return;
        }
        if(!name){
            alert('Please enter customer name.');
            $('#cust_name').focus();
            return;
        }
        if(!address){
            alert('Please select or enter the delivery address.');
            $('#cust_address').focus();
            return;
        }
        if(!zoneId){
            alert('Please select a Delivery Zone.');
            $('#order_zone').focus();
            return;
        }
        if(!sellerId){
            alert('Please select a Restaurant / Store.');
            $('#order_seller').focus();
            return;
        }

        var itemsArr = [];
        Object.keys(cartItems).forEach(function(vid){
            itemsArr.push({
                variant_id: parseInt(vid),
                quantity: cartItems[vid].qty
            });
        });

        if(itemsArr.length === 0){
            alert('Your cart is empty. Please add items to place the order.');
            return;
        }

        var confirmMsg = 'Place this order for ' + name + ' with payment method: ' + paymentMethod + '?';
        if(!confirm(confirmMsg)){
            return;
        }

        var $btn = $('#btn_place_order');
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Placing Order, please wait...');

        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: {
                action: 'place_admin_order',
                name: name,
                mobile: mobile,
                address: address,
                zone_id: zoneId,
                seller_id: sellerId,
                notes: notes,
                delivery_charge: deliveryCharge,
                payment_method: paymentMethod,
                latitude: latitude,
                longitude: longitude,
                items: JSON.stringify(itemsArr)
            },
            dataType: 'json',
            success: function(resp){
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Place Food Order Now');
                if(!resp.error && resp.order_id){
                    $('#modal_order_id').text('#' + resp.order_id);
                    $('#modal_order_details').html('Payment Mode: <strong>' + escapeHtml(resp.payment_method) + '</strong> | Total: <strong>&#8377; ' + parseFloat(resp.final_total).toFixed(2) + '</strong>');
                    $('#modal_view_order_btn').attr('href', 'order-detail.php?id=' + resp.order_id);
                    $('#orderSuccessModal').modal('show');
                } else {
                    alert('Order creation failed: ' + (resp.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error){
                $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Place Food Order Now');
                alert('Server error occurred while creating order: ' + error);
            }
        });
    });

    $('#modal_create_another_btn').on('click', function(){
        $('#orderSuccessModal').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
        // Reset food inputs
        $('#cust_name').val('');
        $('#cust_mobile').val('');
        $('#cust_address').val('');
        $('#cust_notes').val('');
        $('#map_place_search').val('');
        $('#cust_lookup_badge').hide();
        clearCart();

        // Reset service inputs
        $('#svc_cust_name').val('');
        $('#svc_cust_mobile').val('');
        $('#svc_pickup_address').val('');
        $('#svc_drop_address').val('');
        $('#svc_map_pickup_search').val('');
        $('#svc_map_drop_search').val('');
        $('#svc_recipient_name').val('');
        $('#svc_recipient_phone').val('');
        $('#svc_parcel_notes').val('');
        $('#svc_shipping_notes').val('');
        $('#svc_passenger_notes').val('');
        $('#svc_shipping_weight').val('');
        $('#svc_custom_item_name').val('');
        $('#group_other_item_type').hide();
        $('#svc_cust_lookup_badge').hide();
        $('#svc_image_preview_parcel').empty();
        $('#svc_image_preview_shipping').empty();
        $('#svc_parcel_images').val('');
        $('#svc_shipping_images').val('');
        isManualTariff = false;
        $('#svc_input_tariff').prop('readonly', true);
        $('#svc_btn_edit_tariff').removeClass('btn-primary').addClass('btn-default');
        updateSvcRouteAndFare();
    });

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }

    // ==============================================================
    // SERVICE ORDER JAVASCRIPT LOGIC (PARCEL, SHIPPING, PASSENGER)
    // ==============================================================

    // Tab Button Click
    $(document).on('click', '.service-nav-tab, .service-pill', function(e) {
        e.preventDefault();
        var s = $(this).data('tab') || $(this).data('service');
        if (s) {
            switchServiceTab(s);
        }
    });



    // Customer lookup for Service Order
    $('#svc_cust_mobile').on('keyup change blur', function() {
        var mobile = $(this).val().trim();
        if (mobile.length === 10) {
            lookupSvcCustomer(mobile);
        } else {
            $('#svc_cust_lookup_badge').hide();
        }
    });

    $('#svc_btn_lookup_phone').on('click', function() {
        var mobile = $('#svc_cust_mobile').val().trim();
        if (mobile.length >= 10) {
            lookupSvcCustomer(mobile);
        } else {
            alert('Please enter at least a 10-digit mobile number.');
        }
    });

    function lookupSvcCustomer(mobile) {
        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: { action: 'get_customer_by_phone', mobile: mobile },
            dataType: 'json',
            success: function(resp) {
                if (!resp.error && resp.found && resp.user) {
                    $('#svc_cust_name').val(resp.user.name || '');
                    $('#svc_cust_lookup_badge').text('Customer: ' + (resp.user.name || 'Found')).fadeIn();
                    if (resp.user.address && !$('#svc_pickup_address').val()) {
                        $('#svc_pickup_address').val(resp.user.address);
                    }
                    if (resp.user.latitude && resp.user.longitude && parseFloat(resp.user.latitude) != 0) {
                        var uLat = parseFloat(resp.user.latitude);
                        var uLng = parseFloat(resp.user.longitude);
                        if (!isNaN(uLat) && !isNaN(uLng)) {
                            setSvcPickupLocation(uLat, uLng, resp.user.address);
                        }
                    }
                } else {
                    $('#svc_cust_lookup_badge').hide();
                }
            }
        });
    }

    // Service Zone Change
    $('#svc_order_zone').on('change', function() {
        updateSvcRouteAndFare();
    });

    // Parcel Item Types
    $('#parcel_item_type_chips .item-chip').on('click', function() {
        $('#parcel_item_type_chips .item-chip').removeClass('active');
        $(this).addClass('active');
        var id = $(this).data('id');
        var name = $(this).data('name');
        $('#svc_item_type_id').val(id);
        $('#svc_item_type_name').val(name);
        if (name === 'Others') {
            $('#group_other_item_type').slideDown();
            $('#svc_custom_item_name').focus();
        } else {
            $('#group_other_item_type').slideUp();
        }
    });

    // Shipping Vehicles
    $('#shipping_vehicle_grid .vehicle-select-card').on('click', function() {
        $('#shipping_vehicle_grid .vehicle-select-card').removeClass('active shipping-active');
        $(this).addClass('active shipping-active');
        var vid = $(this).data('id');
        var vname = $(this).data('name');
        $('#svc_shipping_vehicle_id').val(vid);
        $('#svc_shipping_vehicle_name').val(vname);
        updateSvcRouteAndFare();
    });

    // Shipping Helpers
    $('#helper_btn_group .helper-btn').on('click', function() {
        $('#helper_btn_group .helper-btn').removeClass('active');
        $(this).addClass('active');
        var count = $(this).data('count');
        $('#svc_helper_count').val(count);
        updateSvcRouteAndFare();
    });

    // Passenger Vehicles
    $('#passenger_vehicle_grid .vehicle-select-card').on('click', function() {
        $('#passenger_vehicle_grid .vehicle-select-card').removeClass('active passenger-active');
        $(this).addClass('active passenger-active');
        var v = $(this).data('vehicle');
        var maxPass = parseInt($(this).data('max')) || 1;
        $('#svc_passenger_vehicle').val(v);

        var curPass = parseInt($('#svc_passenger_count').val()) || 1;
        if (curPass > maxPass) {
            curPass = maxPass;
            $('#svc_passenger_count').val(curPass);
            $('#svc_pass_count_display').text(curPass);
        }
        updateSvcRouteAndFare();
    });

    // Passenger Counter
    $('#svc_btn_pass_plus').on('click', function() {
        var v = $('#svc_passenger_vehicle').val() || 'bike';
        var maxSeats = (v === 'bike') ? 1 : (v === 'auto' ? 3 : 4);
        var cur = parseInt($('#svc_passenger_count').val()) || 1;
        if (cur < maxSeats) {
            cur++;
            $('#svc_passenger_count').val(cur);
            $('#svc_pass_count_display').text(cur);
        } else {
            alert('Maximum capacity for ' + v.toUpperCase() + ' is ' + maxSeats + ' seat(s).');
        }
    });

    $('#svc_btn_pass_minus').on('click', function() {
        var cur = parseInt($('#svc_passenger_count').val()) || 1;
        if (cur > 1) {
            cur--;
            $('#svc_passenger_count').val(cur);
            $('#svc_pass_count_display').text(cur);
        }
    });

    // Pickup Timing Preset
    $('#svc_pickup_time_preset').on('change', function() {
        var val = $(this).val();
        if (val === 'custom') {
            $('#svc_pickup_time').val('').focus();
        } else {
            $('#svc_pickup_time').val(val);
        }
    });

    // GPS Detector for Service Pickup
    $('#svc_btn_detect_gps').on('click', function() {
        if (navigator.geolocation) {
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
            navigator.geolocation.getCurrentPosition(function(pos) {
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-success"></i> GPS');
                setSvcPickupLocation(pos.coords.latitude, pos.coords.longitude);
                if (svcRouteMap) {
                    svcRouteMap.panTo({ lat: pos.coords.latitude, lng: pos.coords.longitude });
                }
            }, function(err) {
                $btn.prop('disabled', false).html('<i class="fa fa-crosshairs text-success"></i> GPS');
                alert('Could not detect GPS position: ' + err.message);
            }, { timeout: 10000, enableHighAccuracy: true });
        } else {
            alert('Geolocation is not supported by your browser.');
        }
    });

    // Automatic Geocoding when user manually types address text
    $('#svc_map_pickup_search, #svc_pickup_address').on('change blur', function() {
        var addr = $(this).val().trim();
        if (addr && addr.length >= 3 && window.google && window.google.maps && svcGeocoder) {
            svcGeocoder.geocode({ address: addr }, function(results, status) {
                if (status === 'OK' && results && results[0] && results[0].geometry) {
                    var loc = results[0].geometry.location;
                    setSvcPickupLocation(loc.lat(), loc.lng(), results[0].formatted_address || addr);
                    if (svcRouteMap) svcRouteMap.panTo(loc);
                }
            });
        }
    });

    $('#svc_map_drop_search, #svc_drop_address').on('change blur', function() {
        var addr = $(this).val().trim();
        if (addr && addr.length >= 3 && window.google && window.google.maps && svcGeocoder) {
            svcGeocoder.geocode({ address: addr }, function(results, status) {
                if (status === 'OK' && results && results[0] && results[0].geometry) {
                    var loc = results[0].geometry.location;
                    setSvcDropLocation(loc.lat(), loc.lng(), results[0].formatted_address || addr);
                    if (svcRouteMap) svcRouteMap.panTo(loc);
                }
            });
        }
    });

    // Manual Tariff Override
    $('#svc_btn_edit_tariff').on('click', function() {
        isManualTariff = true;
        $('#svc_input_tariff').prop('readonly', false).focus().select();
        $(this).addClass('btn-primary').removeClass('btn-default');
    });

    $('#svc_input_tariff').on('change keyup', function() {
        if (!isManualTariff) return;
        var manualTariff = parseFloat($(this).val()) || 0;
        var pFee = parseFloat($('#svc_txt_platform_fee').text().replace(/[^0-9.]/g, '')) || 0;
        var cFee = parseFloat($('#svc_txt_convenience_fee').text().replace(/[^0-9.]/g, '')) || 0;
        var tax = parseFloat($('#svc_txt_tax_amount').text().replace(/[^0-9.]/g, '')) || 0;
        var gTotal = manualTariff + pFee + cFee + tax;
        $('#svc_txt_grand_total').html('&#8377; ' + gTotal.toFixed(2));
    });

    // Driver Assignment Mode Toggle
    $('input[name="svc_driver_mode"]').on('change', function() {
        var mode = $(this).val();
        if (mode === 'direct') {
            $('#svc_driver_select_group').slideDown();
        } else {
            $('#svc_driver_select_group').slideUp();
        }
    });

    // Service Payment Method
    $('input[name="svc_payment_method"]').on('change', function() {
        $('#card_svc_pay_admin, #card_svc_pay_cod').removeClass('selected');
        $(this).closest('.payment-option-card').addClass('selected');
    });

    // Image Previews
    function handleSvcImagePreview(inputEl, $previewEl) {
        $previewEl.empty();
        if (inputEl.files) {
            var files = Array.from(inputEl.files).slice(0, 5);
            files.forEach(function(file) {
                if (file.type.match('image.*')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('<img>').attr('src', e.target.result).appendTo($previewEl);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    $('#svc_parcel_images').on('change', function() {
        handleSvcImagePreview(this, $('#svc_image_preview_parcel'));
    });

    $('#svc_shipping_images').on('change', function() {
        handleSvcImagePreview(this, $('#svc_image_preview_shipping'));
    });

    // Place Service Order (Parcel / Shipping / Passenger)
    $('#btn_svc_place_order').on('click', function() {
        var custName = $('#svc_cust_name').val().trim();
        var custMobile = $('#svc_cust_mobile').val().trim();
        var pickupAddr = $('#svc_pickup_address').val().trim();
        var pickupLat = $('#svc_pickup_lat').val() || '0';
        var pickupLng = $('#svc_pickup_lng').val() || '0';
        var dropAddr = $('#svc_drop_address').val().trim();
        var dropLat = $('#svc_drop_lat').val() || '0';
        var dropLng = $('#svc_drop_lng').val() || '0';
        var zoneId = $('#svc_order_zone').val();
        var pickupTime = $('#svc_pickup_time').val() || 'Immediate / Now';

        if (!custMobile || custMobile.length < 10) {
            alert('Please enter a valid 10-digit customer mobile number.');
            $('#svc_cust_mobile').focus();
            return;
        }
        if (!custName) {
            alert('Please enter customer full name.');
            $('#svc_cust_name').focus();
            return;
        }
        if (!pickupAddr) {
            alert('Please specify the pickup address.');
            $('#svc_pickup_address').focus();
            return;
        }
        if (!dropAddr) {
            alert('Please specify the drop address.');
            $('#svc_drop_address').focus();
            return;
        }

        var recipientName = '';
        var recipientPhone = '';
        if (currentActiveService !== 'passenger') {
            recipientName = $('#svc_recipient_name').val().trim();
            recipientPhone = $('#svc_recipient_phone').val().trim();
            if (!recipientName) {
                alert('Please enter the recipient name.');
                $('#svc_recipient_name').focus();
                return;
            }
            if (!recipientPhone || recipientPhone.length < 10) {
                alert('Please enter a valid 10-digit recipient phone number.');
                $('#svc_recipient_phone').focus();
                return;
            }
        }

        var itemTypeId = 0;
        var itemTypeName = '';
        var weight = '';
        var notes = '';
        var shippingVehicleId = 0;
        var goodsType = '';
        var helperCount = 0;
        var passengerVehicle = '';
        var passengerCount = 1;

        if (currentActiveService === 'parcel') {
            itemTypeId = $('#svc_item_type_id').val() || 0;
            itemTypeName = $('#svc_item_type_name').val();
            if (itemTypeName === 'Others') {
                itemTypeName = $('#svc_custom_item_name').val().trim();
                if (!itemTypeName) {
                    alert('Please enter custom item description.');
                    $('#svc_custom_item_name').focus();
                    return;
                }
            }
            weight = $('#svc_weight_kg').val() || '1.0';
            notes = $('#svc_parcel_notes').val().trim();
        } else if (currentActiveService === 'shipping') {
            shippingVehicleId = $('#svc_shipping_vehicle_id').val() || 0;
            goodsType = $('#svc_goods_type').val();
            if (!goodsType) {
                alert('Please select the Goods / Cargo Type.');
                $('#svc_goods_type').focus();
                return;
            }
            helperCount = $('#svc_helper_count').val() || 0;
            weight = $('#svc_shipping_weight').val().trim();
            notes = $('#svc_shipping_notes').val().trim();
        } else if (currentActiveService === 'passenger') {
            passengerVehicle = $('#svc_passenger_vehicle').val() || 'bike';
            passengerCount = $('#svc_passenger_count').val() || 1;
            notes = $('#svc_passenger_notes').val().trim();
        }

        var driverMode = $('input[name="svc_driver_mode"]:checked').val() || 'broadcast';
        var deliveryBoyId = 0;
        if (driverMode === 'direct') {
            deliveryBoyId = $('#svc_assign_delivery_boy').val();
            if (!deliveryBoyId) {
                alert('Please select a driver to assign.');
                $('#svc_assign_delivery_boy').focus();
                return;
            }
        }

        var paymentMethod = $('input[name="svc_payment_method"]:checked').val() || 'Paid to Admin';
        var tariff = $('#svc_input_tariff').val() || 0;

        var confirmMsg = 'Create this ' + currentActiveService.toUpperCase() + ' order for ' + custName + ' (' + custMobile + ')?\nPayment: ' + paymentMethod;
        if (!confirm(confirmMsg)) {
            return;
        }

        var formData = new FormData();
        formData.append('action', 'place_admin_service_order');
        formData.append('service_type', currentActiveService);
        formData.append('customer_name', custName);
        formData.append('customer_mobile', custMobile);
        formData.append('pickup_address', pickupAddr);
        formData.append('pickup_lat', pickupLat);
        formData.append('pickup_lng', pickupLng);
        formData.append('pickup_latitude', pickupLat);
        formData.append('pickup_longitude', pickupLng);
        formData.append('drop_address', dropAddr);
        formData.append('drop_lat', dropLat);
        formData.append('drop_lng', dropLng);
        formData.append('drop_latitude', dropLat);
        formData.append('drop_longitude', dropLng);
        formData.append('zone_id', zoneId);
        formData.append('pickup_time', pickupTime);
        formData.append('recipient_name', recipientName);
        formData.append('recipient_phone', recipientPhone);
        formData.append('item_type_id', itemTypeId);
        formData.append('item_type_name', itemTypeName);
        formData.append('shipping_vehicle_id', shippingVehicleId);
        formData.append('goods_type', goodsType);
        formData.append('helper_count', helperCount);
        formData.append('weight', weight);
        formData.append('passenger_vehicle', passengerVehicle);
        formData.append('passenger_count', passengerCount);
        formData.append('notes', notes);
        formData.append('tariff', tariff);
        formData.append('delivery_charge', tariff);
        formData.append('payment_method', paymentMethod);
        formData.append('delivery_boy_mode', driverMode);
        formData.append('delivery_boy_id', deliveryBoyId);

        var photoInput = (currentActiveService === 'parcel') ? document.getElementById('svc_parcel_images') : (currentActiveService === 'shipping' ? document.getElementById('svc_shipping_images') : null);
        if (photoInput && photoInput.files) {
            for (var p = 0; p < photoInput.files.length; p++) {
                formData.append('photos[]', photoInput.files[p]);
            }
        }

        var $btn = $('#btn_svc_place_order');
        var origHtml = $btn.html();
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Placing Order, please wait...');

        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(resp) {
                $btn.prop('disabled', false).html(origHtml);
                if (!resp.error && resp.order_id) {
                    $('#modal_order_id').text('#' + resp.order_id);
                    var detailsHtml = 'Service: <strong style="text-transform: capitalize;">' + escapeHtml(resp.service_title || currentActiveService) + '</strong>';
                    if (resp.pickup_otp) {
                        detailsHtml += ' &bull; Pickup OTP: <strong style="color: #d97706; font-size: 19px; letter-spacing: 2px;">' + resp.pickup_otp + '</strong>';
                    }
                    detailsHtml += '<br>Mode: <strong>' + escapeHtml(resp.payment_method) + '</strong> &bull; Total: <strong>&#8377; ' + parseFloat(resp.final_total).toFixed(2) + '</strong>';
                    if (resp.assigned_boy) {
                        detailsHtml += '<div style="margin-top: 8px;"><span class="label label-success" style="font-size: 12px; padding: 4px 8px;"><i class="fa fa-motorcycle"></i> Assigned: ' + escapeHtml(resp.assigned_boy.name) + ' (' + escapeHtml(resp.assigned_boy.mobile) + ')</span></div>';
                    } else {
                        detailsHtml += '<div style="margin-top: 8px;"><span class="label label-info" style="font-size: 12px; padding: 4px 8px;"><i class="fa fa-bullhorn"></i> Broadcasted to nearby drivers</span></div>';
                    }
                    $('#modal_order_details').html(detailsHtml);

                    var targetUrl = 'parcel-requests.php';
                    if (currentActiveService === 'shipping') targetUrl = 'shipping-requests.php';
                    if (currentActiveService === 'passenger') targetUrl = 'passenger-rides.php';
                    $('#modal_view_order_btn').attr('href', targetUrl);

                    $('#orderSuccessModal').modal('show');
                } else {
                    alert('Order creation failed: ' + (resp.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error) {
                $btn.prop('disabled', false).html(origHtml);
                alert('Server error occurred while creating order: ' + error);
            }
        });
    });

    // Auto-switch tab if url parameter specified
    if (currentActiveService && currentActiveService !== 'food') {
        switchServiceTab(currentActiveService);
    }
});
</script>
