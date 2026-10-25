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
</style>

<div class="content-wrapper" style="padding: 20px;">
    <section class="content-header" style="margin-bottom: 20px; padding: 0;">
        <h1>
            <i class="fa fa-shopping-basket" style="color: #3051d3;"></i> Create Food Order
            <small>Order for unknown / walk-in / phone-in customers directly from admin panel</small>
        </h1>
        <ol class="breadcrumb" style="background: transparent; padding: 0; top: 15px;">
            <li><a href="home.php"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="customers.php">Customers</a></li>
            <li class="active">Create Order</li>
        </ol>
    </section>

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
                        <span>Delivery Charge:</span>
                        <div style="display: flex; align-items: center; gap: 4px;">
                            <span>&#8377;</span>
                            <input type="number" id="input_delivery_charge" class="form-control input-sm" style="width: 80px; text-align: right; height: 26px; padding: 2px 6px;" value="<?=$default_delivery_charge?>" min="0" step="1">
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
</div>

<!-- SUCCESS MODAL -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 8px; text-align: center; padding: 25px 20px;">
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
}

// Global callback for Google Maps API
window.initDeliveryMap = function() {
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

    // Map click -> Move marker and reverse geocode
    google.maps.event.addListener(deliveryMap, 'click', function(e) {
        setDeliveryLocation(e.latLng.lat(), e.latLng.lng());
    });

    // Marker dragend -> Reverse geocode
    google.maps.event.addListener(deliveryMarker, 'dragend', function() {
        var p = deliveryMarker.getPosition();
        setDeliveryLocation(p.lat(), p.lng());
    });

    // Google Places Autocomplete on map_place_search input
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
};

$(document).ready(function(){
    var cartItems = {}; // key: variant_id, value: { variant_id, product_id, product_name, measurement, price, qty }
    var loadedProducts = [];

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
        
        if(!sellerId){
            $('#products_container').html('<div class="empty-cart-state"><i class="fa fa-store"></i><p>Select a restaurant to view food menu.</p></div>');
            $('#product_search_filter').prop('disabled', true).val('');
            $('#product_count_badge').text('0 Products');
            $('#seller_status_notice').hide();
            return;
        }

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

    // 8. Compute Totals (via Backend API)
    function computeTotals(subtotal){
        var zoneId = $('#order_zone').val() || 0;
        var sellerId = $('#order_seller').val() || 0;
        var deliveryCharge = $('#input_delivery_charge').val();

        $('#txt_subtotal').html('&#8377; ' + subtotal.toFixed(2));

        $.ajax({
            url: 'api-firebase/create-admin-order.php',
            type: 'POST',
            data: {
                action: 'calculate_fees',
                zone_id: zoneId,
                seller_id: sellerId,
                subtotal: subtotal,
                delivery_charge: deliveryCharge
            },
            dataType: 'json',
            success: function(resp){
                if(!resp.error){
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

    $('#input_delivery_charge').on('input change', function(){
        var subtotal = 0.0;
        Object.keys(cartItems).forEach(function(vid){
            subtotal += cartItems[vid].price * cartItems[vid].qty;
        });
        computeTotals(subtotal);
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
        // Reset inputs
        $('#cust_name').val('');
        $('#cust_mobile').val('');
        $('#cust_address').val('');
        $('#cust_notes').val('');
        $('#map_place_search').val('');
        $('#cust_lookup_badge').hide();
        clearCart();
    });

    function escapeHtml(text) {
        if (!text) return '';
        return $('<div>').text(text).html();
    }
});
</script>
