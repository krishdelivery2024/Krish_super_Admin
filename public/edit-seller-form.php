<?php
// Get seller ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    die("Seller ID is missing.");
}

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
    <h2 class="text-center">Edit Seller</h2>
    <form id="editSellerForm" method="post" enctype="multipart/form-data" class="form-horizontal">
        <input type="hidden" class="form-control" name="id" value="<?= $seller['id'] ?>">

        <?php
        function renderInput($label, $name, $value, $type = "text", $required = false, $readonly = false) {
            $req = $required ? 'required' : '';
            $ro = $readonly ? 'readonly' : '';
            echo "
            <div class='form-group'>
                <label class='col-md-2'>$label</label>
                <div class='col-md-8'>
                    <input type='$type' name='$name' class='form-control' value=\"$value\" $req $ro>
                </div>
            </div>";
        }

        renderInput("Name", "name", $seller['name'], true);
        renderInput("Mobile", "mobile", $seller['mobile'], true);
        renderInput("Email", "email", $seller['email'], true);
        renderInput("Company Name", "company_name", $seller['company_name'], true);
        renderInput("Company Legal Name", "company_legal_name", $seller['company_legal_name'], true);
        renderInput("Personal Address", "personal_address", $seller['personal_address']);
        renderInput("Company Address", "company_address", $seller['company_address']);
        ?>

        <div class="form-group">
            <label class="col-md-2 control-label">Company Logo</label>
            <div class="col-md-8">
                <?php if (!empty($seller['image'])): ?>
                    <div style="margin-bottom: 8px;">
                        <img id="preview_seller_image" src="<?= 'upload/sellers/' . $seller['image'] ?>" alt="Company Logo" style="max-height: 100px; max-width: 200px; border-radius: 4px; border: 1px solid #ddd; padding: 3px; display: block;">
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 8px;">
                        <img id="preview_seller_image" src="" alt="Company Logo Preview" style="max-height: 100px; max-width: 200px; border-radius: 4px; border: 1px solid #ddd; padding: 3px; display: none;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" id="seller_image" class="form-control" accept="image/*">
                <small class="text-muted">Upload to update logo (PNG, JPG, WEBP). Automatically compressed to under 300 KB.</small>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-2 control-label">Company Banner</label>
            <div class="col-md-8">
                <?php if (!empty($seller['banner'])): ?>
                    <div style="margin-bottom: 8px;">
                        <img id="preview_seller_banner" src="<?= 'upload/sellers/' . $seller['banner'] ?>" alt="Company Banner" style="max-height: 120px; max-width: 100%; border-radius: 4px; border: 1px solid #ddd; padding: 3px; display: block;">
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 8px;">
                        <img id="preview_seller_banner" src="" alt="Company Banner Preview" style="max-height: 120px; max-width: 100%; border-radius: 4px; border: 1px solid #ddd; padding: 3px; display: none;">
                    </div>
                <?php endif; ?>
                <input type="file" name="banner" id="seller_banner" class="form-control" accept="image/*">
                <small class="text-muted">Upload to update banner (PNG, JPG, WEBP). Automatically compressed to under 300 KB.</small>
            </div>
        </div>
        <?php
        renderInput("DOB", "dob", $seller['dob']);
        renderInput("Account Details", "account_details", $seller['account_details']);
        renderInput("GST No", "gst_no", $seller['gst_no']);
        renderInput("PAN No", "pan_no", $seller['pan_no']);
         renderInput("Opening Time", "opening_time", $seller['opening_time'] ?? '09:00', "time");
        renderInput("Closing Time", "closing_time", $seller['closing_time'] ?? '21:00', "time");
        renderInput("Preparation Time (mins)", "preparation_time", $seller['preparation_time'] ?? '20', "number");
        renderInput("Date Created", "date_created", $seller['date_created'], "text", false, true);
        ?>

        <?php $seller_lat = $seller['latitude'] ?? ''; $seller_lng = $seller['longitude'] ?? ''; ?>
        <input type="hidden" id="latitude" name="latitude" value="<?= $seller_lat ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?= $seller_lng ?>">

        <div class="form-group">
            <label class="col-md-2">Store Address</label>
            <div class="col-md-8">
                <input type="text" name="store_address" class="form-control" value="<?= htmlspecialchars($seller['store_address'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group" style="display:none;">
            <div class="col-md-8">
                <input type="hidden" name="city_id" value="<?= $seller['city_id'] ?>">
                <input type="hidden" name="state_id" value="<?= $seller['state_id'] ?? 0 ?>">
            </div>
        </div>

        <div class="form-group" style="display:none;">
            <div class="col-md-8">
                <input type="hidden" name="area_id" value="<?= $seller['area_id'] ?>">
            </div>
        </div>

        <div class="form-group">
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

        <div class="form-group">
            <label class="col-md-2">Store Status</label>
            <div class="col-md-8">
                <select name="store_status" class="form-control" required>
                        <option value="true" <?= ('true'== $seller['store_status']) ? 'selected' : '' ?>>Open</option>
                        <option value="false" <?= ('false' == $seller['store_status']) ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="col-md-2">Status</label>
            <div class="col-md-8">
                <select name="status" class="form-control" required>
                        <option value="0" <?= (0 == $seller['status']) ? 'selected' : '' ?>>Inactive</option>
                        <option value="1" <?= (1 == $seller['status']) ? 'selected' : '' ?>>Active</option>
                        <option value="2" <?= (2 == $seller['status']) ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
        </div>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'super admin'): ?>
        <div class="form-group">
            <label class="col-md-2">Commission (%)</label>
            <div class="col-md-8">
                <input type="number" step="0.01" min="0" max="100" name="commission" class="form-control" placeholder="Enter Commission %" value="<?= htmlspecialchars($seller['commission'] ?? '0') ?>">
                <p class="help-block" style="color: #737373; font-size: 12px; margin-top: 5px;">Commission percentage for this seller (only visible to Super Admin).</p>
            </div>
        </div>
        <?php endif; ?>
        <br><br>

        <div class="form-group text-center">
            <div class="col-md-offset-2 col-md-3">
                <button type="submit" class="btn btn-primary">Update Seller</button>
            </div>
        </div>
    </form>
</div>

<!-- JS -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js"></script>

<script>
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

$(document).ready(function () {
    $('select').selectize({ sortField: 'text' });

    $('#seller_image').on('change', function () {
        var file = this.files[0];
        if (file && /^image\//.test(file.type)) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview_seller_image').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#seller_banner').on('change', function () {
        var file = this.files[0];
        if (file && /^image\//.test(file.type)) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#preview_seller_banner').attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });

    $('#editSellerForm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        var $btn = $(form).find('button[type="submit"]');
        var originalBtnText = $btn.html();
        $btn.prop('disabled', true).html('Preparing images...');

        prepareFormData(form, function (formData) {
            $btn.html('Updating...');
            $.ajax({
                url: 'public/update-seller.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (!response.error) {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 413) {
                        alert('Images are too large. Please upload smaller photos.');
                    } else {
                        alert('Something went wrong.');
                    }
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalBtnText);
                }
            });
        });
    });
});
</script>

