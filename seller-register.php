<?php
session_start();
ob_start();
include_once('includes/crud.php');
include_once('includes/custom-functions.php');

$db = new Database;
$fn = new custom_functions();
$db->connect();

date_default_timezone_set('Asia/Kolkata');

// App settings
$sql = "SELECT * FROM settings";
$db->sql($sql);
$res = $db->getResult();
$settings = json_decode($res[6]['value'], true);
$logo = $fn->get_settings('logo');

$sql_logo = "SELECT value FROM settings WHERE variable='Logo' OR variable='logo'";
$db->sql($sql_logo);
$res_logo = $db->getResult();

$db->sql("SELECT id, name FROM city WHERE name != 'Choose Your City' ORDER BY name ASC");
$res_city = $db->getResult();

// Fetch areas
$db->sql("SELECT id, name FROM area ORDER BY name ASC");
$res_area_whole = $db->getResult();

$db->sql("SELECT id, name FROM main_category ORDER BY name ASC");
$res_main_category = $db->getResult();

if (isset($_SESSION['id'])) {
    header("location:home.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ["error" => true, "message" => "Something went wrong"];

    // Escape and sanitize input
    $name = $db->escapeString($_POST['name'] ?? '');
    $mobile = $db->escapeString($_POST['mobile'] ?? '');
    $email = $db->escapeString($_POST['email'] ?? '');
    $main_cat_id = $db->escapeString($_POST['main_cat_id'] ?? '');
    $company_name = $db->escapeString($_POST['company_name'] ?? '');
    $company_legal_name = $db->escapeString($_POST['company_legal_name'] ?? '');
    $personal_address = $db->escapeString($_POST['personal_address'] ?? '');
    $company_address = $db->escapeString($_POST['company_address'] ?? '');
    $store_address = $db->escapeString($_POST['store_address'] ?? '');
    $latitude = $db->escapeString($_POST['latitude'] ?? '');
    $longitude = $db->escapeString($_POST['longitude'] ?? '');
    $state_id = $db->escapeString($_POST['state_id'] ?? '');
    $city_id = $db->escapeString($_POST['city_id'] ?? '');
    $area_id = $db->escapeString($_POST['area_id'] ?? '');
    $dob = $db->escapeString($_POST['dob'] ?? '');
    $account_details = $db->escapeString($_POST['account_details'] ?? '');
    $gst_no = $db->escapeString($_POST['gst_no'] ?? '');
    $pan_no = $db->escapeString($_POST['pan_no'] ?? '');
    $opening_time = $db->escapeString($_POST['opening_time'] ?? '09:00');
    $closing_time = $db->escapeString($_POST['closing_time'] ?? '21:00');
    $preparation_time = $db->escapeString($_POST['preparation_time'] ?? '20');
    $status = 0;
    $date_created = date('Y-m-d H:i:s');
    $zone_id = $fn->get_zone_id_from_latlng($latitude, $longitude);

    // Validate required fields
    if (empty($name) || empty($mobile) || empty($email) || empty($main_cat_id) || empty($company_name) || empty($company_legal_name) || empty($personal_address) || empty($store_address) || empty($latitude) || empty($longitude) ) {
        $response['message'] = "Please fill in all required fields.";
         ob_clean();
        echo json_encode($response);
        exit;
    }

    // Check duplicate mobile
    $db->sql("SELECT id FROM seller WHERE mobile = '$mobile'");
    if ($db->numRows($db->getResult()) > 0) {
           ob_clean();
        echo json_encode(["error" => true, "message" => "Mobile number already registered. Please login."]);
        exit;
    }

    // Check duplicate email
    $db->sql("SELECT id FROM seller WHERE email = '$email'");
    if ($db->numRows($db->getResult()) > 0) {
           ob_clean();
        echo json_encode(["error" => true, "message" => "Email address already registered. Please login."]);
        exit;
    }

    // Handle file upload (image)
    $target_dir = "upload/sellers/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid("img_") . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $image_name);
    }

    $banner_name = '';
    if (isset($_FILES['banner']) && $_FILES['banner']['error'] === 0) {
        $ext = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
        $banner_name = uniqid("banner_") . "." . $ext;
        move_uploaded_file($_FILES['banner']['tmp_name'], $target_dir . $banner_name);
    }

    // Insert into DB
    $seller_data = [
        'name' => $name,
        'mobile' => $mobile,
        'email' => $email,
        'main_cat_id' => $main_cat_id,
        'company_name' => $company_name,
        'company_legal_name' => $company_legal_name,
        'personal_address' => $personal_address,
        'company_address' => $company_address,
        'store_address' => $store_address,
        'latitude' => $latitude,
        'longitude' => $longitude,
        'state_id' => $state_id ? $state_id : 0,
        'city_id' => $city_id,
        'area_id' => $area_id,
        'dob' => $dob,
        'account_details' => $account_details,
        'gst_no' => $gst_no,
        'pan_no' => $pan_no,
           'opening_time' => $opening_time,
        'closing_time' => $closing_time,
        'preparation_time' => $preparation_time,
        'status' => $status,
        'image' => $image_name,
        'banner' => $banner_name,
        'zone_id' => $zone_id !== null ? $zone_id : 0,
        'date_created' => $date_created
    ];

    $db->insert('seller', $seller_data);
    $result = $db->getResult();

      ob_clean();
    echo json_encode([
        "error" => false,
        "message" => "Registration successful! Please wait for admin approval."
    ]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Seller Registration - <?= $settings['app_name'] ?></title>
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <link rel="icon" type="image/ico" href="<?= 'dist/img/' . $logo ?>">
    <link rel="stylesheet" href="dist/styles/style.min.css">
    <link rel="stylesheet" href="dist/plugin/waves/waves.min.css">
    <style>
        .frm-submit1 { width: 45% !important; }
        .error-msg { color: red; }
        .success-msg { color: green; }
        .frm-single {max-width: 100%;}
        .frm-single .title {margin-bottom: 30px;}
        span.required {color: red;}
    </style>
</head>
<body>

<div id="single-wrapper">
    <form id="registerForm" method="post" class="frm-single" onsubmit="return false;">
        <div class="inside">
            <div class="title text-center">
                <img src="<?= isset($res_logo[0]['value']) ? 'dist/img/' . $res_logo[0]['value'] : '' ?>" style="height: 120px">
                <h3>Register as Seller - <?= $settings['app_name'] ?></h3>
            </div>

            <div class="row">
                <div class="frm-input col-md-4">
                    <label>Full Name <span class="required">*</span></label>
                    <input type="text" name="name" placeholder="Full Name" class="frm-inp" required>
                </div>

                <div class="frm-input col-md-4">
                    <label>Mobile Number <span class="required">*</span></label>
                    <input type="tel" name="mobile" placeholder="Mobile Number" class="frm-inp" required>
                </div>

                <div class="frm-input col-md-4">
                    <label>Email Address <span class="required">*</span></label>
                    <input type="email" name="email" placeholder="Email Address" class="frm-inp" required>
                </div>

                <div class="frm-input col-md-4">
                    <label>Date of Birth</label>
                    <input type="date" name="dob"  class="frm-inp">
                </div>

                <div class="frm-input col-md-4">
                    <label>Personal Address <span class="required">*</span></label>
                    <input type="text" name="personal_address" placeholder="Personal Address" class="frm-inp" required>
                </div>
                
                <!-- Main Category Dropdown -->
                <div class="frm-input col-md-4">
                    <label>Select Business Category <span class="required">*</span></label>
                    <select name="main_cat_id" class="frm-inp" required>
                        <option value="">Select Business Category</option>
                        <?php foreach ($res_main_category as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="frm-input col-md-4">
                    <label>Company Name <span class="required">*</span></label>
                    <input type="text" name="company_name" placeholder="Company Name" class="frm-inp" required>
                </div> 
                
                <div class="frm-input col-md-4">
                    <label>Company Legal Name <span class="required">*</span></label>
                    <input type="text" name="company_legal_name" placeholder="Company Legal Name" class="frm-inp" required>
                </div> 

                <div class="frm-input col-md-4">
                    <label>Company Address <span class="required">*</span></label>
                    <input type="text" name="company_address" placeholder="Company Address" class="frm-inp" required>
                </div>                

                <div class="frm-input col-md-4">
                    <label>Account Details </label>
                    <input type="text" name="account_details" placeholder="Account Details" class="frm-inp">
                </div>

                <div class="frm-input col-md-4">
                    <label>GST Number </label>
                    <input type="text" name="gst_no" placeholder="GST Number" class="frm-inp">
                </div>

                <div class="frm-input col-md-4">
                    <label>PAN Number </label>
                    <input type="text" name="pan_no" placeholder="PAN Number" class="frm-inp">
                </div>

                <!-- City dropdown (hidden — auto-filled from map, posts numeric city_id) -->
                <div class="frm-input col-md-3" style="display:none;">
                    <label>City</label>
                    <select name="city_id" class="frm-inp">
                        <option value="" data-selected-done>Select City (auto-filled)</option>
                        <?php foreach ($res_city as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Area dropdown (hidden — auto-filled from map, posts numeric area_id) -->
                <div class="frm-input col-md-3" style="display:none;">
                    <label>Area</label>
                    <select name="area_id" class="frm-inp">
                        <option value="">Select Area</option>
                        <?php foreach ($res_area_whole as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Store Address (visible, read-only — auto-filled from map) -->
                <div class="frm-input col-md-6">
                    <label>Store Address <span class="required">*</span></label>
                    <input type="text" id="store_address" name="store_address" class="frm-inp" readonly
                           placeholder="Auto-filled from map — drag pin or use Get My Current Location" required>
                </div>

                <div class="frm-input col-md-3">
                    <label>Company Logo <span class="required">*</span></label>
                    <input type="file" name="image" class="frm-inp" required>
                </div> 

                <div class="frm-input col-md-3">
                    <label>Company Banner <span class="required">*</span></label>
                    <input type="file" name="banner" class="frm-inp" required>
                </div>   
                 <div class="frm-input col-md-3">
                    <label>Opening Time <span class="required">*</span></label>
                    <input type="time" name="opening_time" class="frm-inp" value="09:00" required>
                </div>

                <div class="frm-input col-md-3">
                    <label>Closing Time <span class="required">*</span></label>
                    <input type="time" name="closing_time" class="frm-inp" value="21:00" required>
                </div>

                <div class="frm-input col-md-3">
                    <label>Preparation Time (mins) <span class="required">*</span></label>
                    <input type="number" name="preparation_time" class="frm-inp" value="20" required>
                </div>             

                <div class="frm-input col-md-12">
                    <label>Store Location <span class="required">*</span></label>
                    <div id="seller-reg-map" style="width:100%;height:320px;border:1px solid #d2d6de;border-radius:4px;"></div>
                    <button type="button" id="seller-reg-locate" class="btn btn-info btn-sm" style="margin-top:8px;">
                        <i class="fa fa-location-arrow"></i> Get Current Location
                    </button>
                    <span id="seller-reg-msg" class="help-block"></span>
                    <input type="hidden" id="latitude" name="latitude" value="">
                    <input type="hidden" id="longitude" name="longitude" value="">
                </div>

                <script>
                (function () {
                    var gg = document.createElement('script');
                    gg.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I&callback=initSellerRegMap';
                    gg.async = true;
                    gg.defer = true;
                    document.head.appendChild(gg);

                    window.initSellerRegMap = function () {
                        var defaultLoc = { lat: 20.5937, lng: 78.9629 };
                        var map = new google.maps.Map(document.getElementById('seller-reg-map'), {
                            center: defaultLoc,
                            zoom: 5
                        });
                        var marker = new google.maps.Marker({ map: map, position: defaultLoc, draggable: true });

                        google.maps.event.addListener(map, 'click', function (e) {
                            setPin(e.latLng.lat(), e.latLng.lng());
                            fillCityArea(e.latLng.lat(), e.latLng.lng());
                        });

                        function setPin(lat, lng) {
                            var loc = { lat: lat, lng: lng };
                            marker.setPosition(loc);
                            map.setCenter(loc);
                            map.setZoom(15);
                            document.getElementById('latitude').value = lat;
                            document.getElementById('longitude').value = lng;
                        }

                        function fillCityArea(geocodeLat, geocodeLng) {
                            var geocoder = new google.maps.Geocoder();
                            geocoder.geocode({ location: { lat: geocodeLat, lng: geocodeLng } }, function (results, status) {
                                if (status === 'OK' && results && results.length) {
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
                                    var sa = document.getElementById('store_address');
                                    if (sa) sa.value = results[0].formatted_address;
                                }
                            });
                        }

                        google.maps.event.addListener(marker, 'dragend', function () {
                            var p = marker.getPosition();
                            setPin(p.lat(), p.lng());
                            fillCityArea(p.lat(), p.lng());
                        });

                        document.getElementById('seller-reg-locate').addEventListener('click', function () {
                            if (!navigator.geolocation) {
                                document.getElementById('seller-reg-msg').textContent = 'Geolocation not supported in this browser.';
                                return;
                            }
                            document.getElementById('seller-reg-msg').textContent = 'Locating…';
                            navigator.geolocation.getCurrentPosition(function (pos) {
                                var lat = pos.coords.latitude, lng = pos.coords.longitude;
                                setPin(lat, lng);
                                fillCityArea(lat, lng);
                                document.getElementById('seller-reg-msg').textContent = 'Location set. City & Area filled from map.';
                            }, function () {
                                document.getElementById('seller-reg-msg').textContent = 'Could not get your current location. Drag the pin instead.';
                            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
                        });
                    };
                })();
                </script>

            </div>

            <br>

            <button type="submit" name="submit" id="registerBtn" class="frm-submit">
                Register <i class="fa fa-check-circle"></i>
            </button>
            <p id="message" class="text-center"></p>

            <div class="text-center">
                <p>Already have an account? <a href="seller-login.php">Login here</a></p>
            </div>
        </div>
    </form>

</div>

<!-- JS -->
<script src="dist/scripts/jquery.min.js"></script>
<script src="dist/plugin/bootstrap/js/bootstrap.min.js"></script>
<script src="dist/plugin/waves/waves.min.js"></script>
<script src="dist/scripts/main.min.js"></script>
<script>
// Shrink large images before they leave the browser. The request body is capped
// by the web server in front of the application, and a 413 is returned before PHP
// ever runs, so this is the only reliable place to control the size. Anything
// already within budget is sent untouched.
var SELLER_MAX_BYTES = 300 * 1024;
var SELLER_MAX_EDGE = 1600;

function compressImageToTarget(file, maxBytes, done) {
    if (file.size <= maxBytes) {
        done(file);
        return;
    }

    var reader = new FileReader();

    reader.onerror = function () {
        done(file);
    };

    reader.onload = function (e) {
        var img = new Image();

        img.onerror = function () {
            done(file);
        };

        img.onload = function () {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var scale = Math.min(1, SELLER_MAX_EDGE / Math.max(img.width, img.height));
            var width = Math.max(1, Math.round(img.width * scale));
            var height = Math.max(1, Math.round(img.height * scale));
            var quality = 0.85;
            var qualityFloor = 0.4;

            function attempt() {
                canvas.width = width;
                canvas.height = height;
                // Flatten transparency onto white, otherwise png transparency
                // would come through as black in the jpeg.
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        done(file);
                        return;
                    }
                    if (blob.size <= maxBytes) {
                        done(blob);
                        return;
                    }
                    if (quality > qualityFloor) {
                        quality -= 0.1;
                        attempt();
                        return;
                    }
                    // Quality exhausted: shrink and start over. This keeps going
                    // until the image is tiny, so the 300 KB budget is a hard
                    // guarantee rather than a best effort - otherwise a wide
                    // banner would stall as soon as its height dropped and
                    // could still be over budget when given up on.
                    if (width > 160 || height > 160) {
                        width = Math.max(64, Math.round(width * 0.8));
                        height = Math.max(64, Math.round(height * 0.8));
                        quality = 0.85;
                        attempt();
                        return;
                    }
                    done(blob);
                }, 'image/jpeg', quality);
            }

            attempt();
        };

        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
}

function prepareFormData(form, done) {
    var formData = new FormData(form);
    var fields = ['image', 'banner'].filter(function (name) {
        var value = formData.get(name);
        return value instanceof File && value.size > 0 && /^image\//.test(value.type);
    });

    if (!fields.length) {
        done(formData);
        return;
    }

    var pending = fields.length;

    fields.forEach(function (fieldName) {
        var original = formData.get(fieldName);

        compressImageToTarget(original, SELLER_MAX_BYTES, function (blob) {
            // The server takes the stored extension from the uploaded file name,
            // so a jpeg that was just encoded has to be named .jpg to match.
            if (blob === original) {
                formData.set(fieldName, blob, original.name);
            } else {
                var base = original.name.replace(/\.[^.]+$/, '') || fieldName;
                formData.set(fieldName, blob, base + '.jpg');
            }

            console.log(fieldName + ': ' + original.size + ' -> ' + blob.size + ' bytes'
                + (blob.size > SELLER_MAX_BYTES ? '  OVER BUDGET' : ''));

            if (--pending === 0) {
                done(formData);
            }
        });
    });
}

document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = document.getElementById('registerForm');
    const msgEl = document.getElementById('message');
    const btn = document.getElementById('registerBtn');
    const btnLabel = btn.innerHTML;

    msgEl.innerText = '';
    btn.disabled = true;
    btn.innerHTML = 'Preparing images...';

    prepareFormData(form, function (formData) {
        btn.innerHTML = 'Registering...';

        var bytes = 0;
        for (var i = 0; i < formData.length; i++) {
            var value = formData.get(i);
            if (value && typeof value.size === 'number') bytes += value.size;
        }
        console.log('seller registration payload: ' + bytes + ' bytes');
        msgEl.style.color = '#777';
        msgEl.innerText = 'Uploading ' + (bytes / 1024).toFixed(0) + ' KB...';

        fetch('seller-register.php', {
            method: 'POST',
            body: formData
        })
        .then(resp => {
            if (resp.status === 413) {
                const err = new Error('413');
                err.tooLarge = true;
                throw err;
            }
            return resp.json();
        })
        .then(data => {
            msgEl.innerText = data.message;
            msgEl.style.color = data.error ? 'red' : 'green';
            if (!data.error) form.reset();
        })
        .catch(err => {
            if (err && err.tooLarge) {
                msgEl.innerText = "Images are still too large. Please upload smaller photos.";
            } else {
                msgEl.innerText = "Something went wrong. Try again later.";
            }
            msgEl.style.color = 'red';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = btnLabel;
        });
    });
});
</script>

</body>
</html>
