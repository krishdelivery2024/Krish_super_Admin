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
    $state_id = $db->escapeString($_POST['state_id'] ?? '');
    $city_id = $db->escapeString($_POST['city_id'] ?? '');
    $area_id = $db->escapeString($_POST['area_id'] ?? '');
    $dob = $db->escapeString($_POST['dob'] ?? '');
    $account_details = $db->escapeString($_POST['account_details'] ?? '');
    $gst_no = $db->escapeString($_POST['gst_no'] ?? '');
    $pan_no = $db->escapeString($_POST['pan_no'] ?? '');
    $status = 0;
    $date_created = date('Y-m-d H:i:s');

    // Validate required fields
    if (empty($name) || empty($mobile) || empty($email) || empty($main_cat_id) || empty($company_name) || empty($company_legal_name) || empty($personal_address) || empty($city_id) || empty($area_id)) {
        $response['message'] = "Please fill in all required fields.";
        echo json_encode($response);
        exit;
    }

    // Check duplicate mobile
    $db->sql("SELECT id FROM seller WHERE mobile = '$mobile'");
    if ($db->numRows($db->getResult()) > 0) {
        echo json_encode(["error" => true, "message" => "Mobile number already registered. Please login."]);
        exit;
    }

    // Check duplicate email
    $db->sql("SELECT id FROM seller WHERE email = '$email'");
    if ($db->numRows($db->getResult()) > 0) {
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
        'state_id' => $state_id,
        'city_id' => $city_id,
        'area_id' => $area_id,
        'dob' => $dob,
        'account_details' => $account_details,
        'gst_no' => $gst_no,
        'pan_no' => $pan_no,
        'status' => $status,
        'image' => $image_name,
        'banner' => $banner_name,
        'date_created' => $date_created
    ];

    $db->insert('seller', $seller_data);
    $result = $db->getResult();

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

                <!-- City Dropdown -->
                <div class="frm-input col-md-3">
                    <label>City <span class="required">*</span></label>
                    <select name="city_id" class="frm-inp" required>
                        <option value="">Select City</option>
                        <?php foreach ($res_city as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Area Dropdown -->
                <div class="frm-input col-md-3">
                    <label>Area <span class="required">*</span></label>
                    <select name="area_id" class="frm-inp" required>
                        <option value="">Select Area</option>
                        <?php foreach ($res_area_whole as $row): ?>
                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="frm-input col-md-3">
                    <label>Company Logo <span class="required">*</span></label>
                    <input type="file" name="image" class="frm-inp" required>
                </div> 

                <div class="frm-input col-md-3">
                    <label>Company Banner <span class="required">*</span></label>
                    <input type="file" name="banner" class="frm-inp" required>
                </div>                

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
document.getElementById('registerForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const form = document.getElementById('registerForm');
    const formData = new FormData(form);

    fetch('seller-register.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        const msgEl = document.getElementById('message');
        msgEl.innerText = data.message;
        msgEl.style.color = data.error ? 'red' : 'green';
        if (!data.error) form.reset();
    })
    .catch(() => {
        document.getElementById('message').innerText = "Something went wrong. Try again later.";
    });
});
</script>

</body>
</html>
