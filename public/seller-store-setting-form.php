<?php
// Get seller ID

if($_SESSION['role'] != 'seller'){
die("Only seller can access this apge.");
}
$ID = $_SESSION['id'];

// Fetch seller
$sql = "SELECT * FROM seller WHERE id = " . $ID;
$db->sql($sql);
$res = $db->getResult();
if (empty($res)) {
    die("Seller not found.");
}
$seller = $res[0];

// Fetch cities
$db->sql("SELECT id, name FROM city WHERE name != 'Choose Your City' ORDER BY name ASC");
$res_city = $db->getResult();

// Fetch areas
$db->sql("SELECT id, name FROM area ORDER BY name ASC");
$res_area_whole = $db->getResult();

$db->sql("SELECT id, name FROM main_category ORDER BY name ASC");
$res_main_category = $db->getResult();
?>


    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.bootstrap3.min.css" rel="stylesheet" />

<div class="container">
    <h2 class="text-center">Seller Store Details</h2>
    <form id="editSellerForm" method="post" class="form-horizontal">
        <input type="hidden" class="form-control" name="id" value="<?= $seller['id'] ?>">

        <?php

        function renderInput($label, $name, $value, $type = "text", $required = false, $readonly = false, $id = "") {

            $req = $required ? 'required' : '';
            $ro = $readonly ? 'readonly' : '';
            $id_attr = $id ? "id='$id'" : "";

            echo "
            <div class='form-group'>
                <label class='col-md-2 control-label'>$label</label>
                <div class='col-md-8'>
                    <input type='$type' name='$name' $id_attr class='form-control' value=\"$value\" $req $ro>
                </div>
            </div>";
        }

        renderInput("Name", "name", $seller['name'], true);
        renderInput("Mobile", "mobile", $seller['mobile'],"text", true, true);
        renderInput("Email", "email", $seller['email'], true);
        renderInput("Company Name", "company_name", $seller['company_name'], true);
        renderInput("Company Legal Name", "company_legal_name", $seller['company_legal_name'], true);
        renderInput("Personal Address", "personal_address", $seller['personal_address']);
        renderInput(
            "Company Address",
            "company_address",
            $seller['company_address'],
            "text",
            false,
            false,
            "company_address"
        );
        ?> 

        <input type="hidden" id="latitude" name="latitude" value="<?= $seller['latitude'] ?? '' ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?= $seller['longitude'] ?? '' ?>">

        <?php $seller_lat = $seller['latitude'] ?? ''; $seller_lng = $seller['longitude'] ?? ''; ?>

        <div class="form-group">
            <label class="col-md-2">Store Address</label>
            <div class="col-md-8">
                <input type="text" id="store_address" name="store_address" class="form-control" readonly
                       value="<?= htmlspecialchars($seller['store_address'] ?? '') ?>"
                       placeholder="Auto-filled from map — click map, drag pin, or use Get My Current Location">
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-2">Map Location</label>
            <div class="col-md-8">
                <div id="seller-store-map" style="width:100%;height:300px;border:1px solid #d2d6de;"></div>
                <button type="button" id="seller-store-locate" class="btn btn-info btn-sm" style="margin-top:6px;">
                    <i class="fa fa-location-arrow"></i> Get My Current Location
                </button>
                <span id="seller-store-msg" class="help-block"></span>
            </div>
        </div>

        <?php
        renderInput("DOB", "dob", $seller['dob'], "date");
        renderInput("Account Details", "account_details", $seller['account_details']);
        renderInput("GST No", "gst_no", $seller['gst_no']);
        renderInput("PAN No", "pan_no", $seller['pan_no']);
         renderInput("Opening Time", "opening_time", $seller['opening_time'] ?? '09:00', "time");
        renderInput("Closing Time", "closing_time", $seller['closing_time'] ?? '21:00', "time");
        renderInput("Preparation Time (mins)", "preparation_time", $seller['preparation_time'] ?? '20', "number");
        renderInput("Date Created", "date_created", $seller['date_created'], "text", false, true);
        ?>


        <div class="form-group" style="display:none;">
            <div class="col-md-8">
                <select name="city_id" class="form-control">
                    <option value="">Select City (auto)</option>
                    <?php foreach ($res_city as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $seller['city_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" name="state_id" value="<?= $seller['state_id'] ?? 0 ?>">
            </div>
        </div>

        <div class="form-group" style="display:none;">
            <div class="col-md-8">
                <select name="area_id" class="form-control">
                    <option value="">Select Area (auto)</option>
                    <?php foreach ($res_area_whole as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $seller['area_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-2">Store Status</label>
            <div class="col-md-8">
                <select name="store_status" class="form-control" required>
                        <option value="true" <?= ('true'== $seller['store_status']) ? 'selected' : '' ?>>Open</option>
                        <option value="false" <?= ('false' == $seller['store_status']) ? 'selected' : '' ?>>Closed</option>
                </select>
            </div><br><br>

        <div class="form-group" style="display:none;">
            <label class="col-md-2">Main Category</label>
            <div class="col-md-8">
                <select name="main_cat_id" class="form-control" required>
                    <option value="">Select Main Category</option>
                    <?php foreach ($res_main_category as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $seller['main_cat_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group" style="display:none;">
            <label class="col-md-2">Status</label>
            <div class="col-md-8">
                <select name="status" class="form-control" required>
                        <option value="0" <?= (0 == $seller['status']) ? 'selected' : '' ?>>Inactive</option>
                        <option value="1" <?= (1 == $seller['status']) ? 'selected' : '' ?>>Active</option>
                        <option value="2" <?= (2 == $seller['status']) ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
        </div><br><br>

        <div class="form-group text-center">
            <div class="col-md-offset-2 col-md-3">
                <button type="submit" class="btn btn-primary">Update Details</button>
            </div>
        </div>
    </form>
</div>

<!-- JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>


<script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I&callback=initSellerStoreMap"></script>

<script>
$(document).ready(function () {
    $('select').selectize({ sortField: 'text' });

    $('#editSellerForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: 'public/update-seller.php',
            type: 'POST',
            dataType: 'json',
            data: $('#editSellerForm').serialize(),
            success: function (response) {
                if (!response.error) {
                    alert(response.message);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function () {
                alert('Something went wrong.');
            }
        });

    });
});
</script>


<script>
var storeMap, storeMarker, storeGeocoder;

function initSellerStoreMap() {
    var savedLat = parseFloat(document.getElementById('latitude').value);
    var savedLng = parseFloat(document.getElementById('longitude').value);

    var defaultLoc = { lat: savedLat || 13.0827, lng: savedLng || 80.2707 };

    storeGeocoder = new google.maps.Geocoder();
    storeMap = new google.maps.Map(document.getElementById('seller-store-map'), {
        center: defaultLoc,
        zoom: 14
    });

    storeMarker = new google.maps.Marker({ map: storeMap, position: defaultLoc, draggable: true });
    if (savedLat) fillStoreAddress(savedLat, savedLng);

    google.maps.event.addListener(storeMap, 'click', function (e) {
        storeMarker.setPosition(e.latLng);
        document.getElementById('latitude').value = e.latLng.lat();
        document.getElementById('longitude').value = e.latLng.lng();
        fillStoreAddress(e.latLng.lat(), e.latLng.lng());
    });

    google.maps.event.addListener(storeMarker, 'dragend', function () {
        var p = storeMarker.getPosition();
        document.getElementById('latitude').value = p.lat();
        document.getElementById('longitude').value = p.lng();
        fillStoreAddress(p.lat(), p.lng());
    });

    document.getElementById('seller-store-locate').addEventListener('click', function () {
        if (!navigator.geolocation) {
            document.getElementById('seller-store-msg').textContent = 'Geolocation not supported in this browser.';
            return;
        }
        document.getElementById('seller-store-msg').textContent = 'Locating…';
        navigator.geolocation.getCurrentPosition(function (pos) {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            storeMarker.setPosition({ lat: lat, lng: lng });
            storeMap.setCenter({ lat: lat, lng: lng });
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            fillStoreAddress(lat, lng);
        }, function () {
            document.getElementById('seller-store-msg').textContent = 'Could not get your current location. Click the map or drag the pin instead.';
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
    });
}

function fillStoreAddress(lat, lng) {
    storeGeocoder.geocode({ location: { lat: lat, lng: lng } }, function (results, status) {
        if (status !== 'OK' || !results[0]) {
            document.getElementById('seller-store-msg').textContent = 'Location set, but address lookup failed (Geocoding API). Coordinates saved.';
            return;
        }
        var cityTxt = '', areaTxt = '';
        results[0].address_components.forEach(function (c) {
            if (c.types.indexOf('locality') !== -1) cityTxt = c.long_name;
            if (c.types.indexOf('sublocality_level_1') !== -1) areaTxt = c.long_name;
        });
        [['city_id', cityTxt], ['area_id', areaTxt]].forEach(function (pair) {
            var sel = document.querySelector('select[name="' + pair[0] + '"]');
            if (sel && pair[1]) {
                for (var i = 0; i < sel.options.length; i++) {
                    if (sel.options[i].text.trim() === pair[1].trim()) {
                        sel.selectedIndex = i;
                        break;
                    }
                }
            }
        });
        document.getElementById('store_address').value = results[0].formatted_address;
        document.getElementById('seller-store-msg').textContent = 'Location set. Store address, City & Area updated.';
    });
}
</script>

