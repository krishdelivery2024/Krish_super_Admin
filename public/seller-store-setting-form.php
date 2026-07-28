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

        renderInput("Latitude", "latitude", $seller['latitude'], "text", false, false, "latitude");

        renderInput("Longitude", "longitude", $seller['longitude'], "text", false, false, "longitude");


        renderInput("DOB", "dob", $seller['dob'], "date");
        renderInput("Account Details", "account_details", $seller['account_details']);
        renderInput("GST No", "gst_no", $seller['gst_no']);
        renderInput("PAN No", "pan_no", $seller['pan_no']);
        renderInput("Date Created", "date_created", $seller['date_created'], "text", false, true);
        ?>


        <div class="form-group">
            <label class="col-md-2">City</label>
            <div class="col-md-8">
                <select name="city_id" class="form-control" required>
                    <option value="">Select City</option>
                    <?php foreach ($res_city as $row): ?>
                        <option value="<?= $row['id'] ?>" <?= ($row['id'] == $seller['city_id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-2">Area</label>
            <div class="col-md-8">
                <select name="area_id" class="form-control" required>
                    <option value="">Select Area</option>
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


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAqHagQKzukWU9FVLLvrrwWZ_V6Vvj-Nfs&libraries=places"></script>

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

function initAutocomplete() {

    var input = document.getElementById('company_address');

    var autocomplete = new google.maps.places.Autocomplete(input);

    autocomplete.addListener('place_changed', function () {

        var place = autocomplete.getPlace();

        if (!place.geometry) {
            return;
        }

        var lat = place.geometry.location.lat();
        var lng = place.geometry.location.lng();

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

    });
}

google.maps.event.addDomListener(window, 'load', initAutocomplete);

</script>

