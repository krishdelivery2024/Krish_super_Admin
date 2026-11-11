<?php
$page = "Product Order";
include "header.php";

header("Expires: on, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

include_once('includes/crud.php');
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);

$db = new Database();
$db->connect();

// Handle Save Order Request
if (isset($_POST['update_products_order']) && $_POST['update_products_order'] == 1) {
    if ($permissions['products_order']['update'] == 1) {
        $raw_ids = isset($_POST["row_order"]) ? explode(",", $_POST["row_order"]) : array();
        $id_ary = array_filter(array_map('intval', $raw_ids));

        if (!empty($id_ary)) {
            $cat_filter = isset($_POST['category_id']) ? trim($_POST['category_id']) : '';
            if (!empty($cat_filter)) {
                // Category-specific reorder: keep relative slot ordering
                $id_str = implode(",", $id_ary);
                $db->sql("SELECT row_order FROM products WHERE id IN ($id_str) ORDER BY row_order ASC");
                $existing_orders = $db->getResult();
                $order_values = !empty($existing_orders) ? array_column($existing_orders, 'row_order') : array();

                $has_non_zero = false;
                foreach ($order_values as $v) {
                    if ((int) $v > 0) {
                        $has_non_zero = true;
                        break;
                    }
                }

                $i = 0;
                foreach ($id_ary as $pid) {
                    $val = ($has_non_zero && isset($order_values[$i])) ? (int) $order_values[$i] : $i;
                    $db->sql("UPDATE `products` SET row_order='" . $val . "' WHERE id=" . (int) $pid);
                    $i++;
                }
            } else {
                // Global reorder: sequential 0, 1, 2, ...
                $i = 0;
                foreach ($id_ary as $pid) {
                    $db->sql("UPDATE `products` SET row_order='" . $i . "' WHERE id=" . (int) $pid);
                    $i++;
                }
            }
        }
        echo "<div class='alert alert-success'><i class='fa fa-check-circle'></i> Product order updated successfully! Mobile user app will display products in this order.</div>";
        exit();
    } else {
        echo "<div class='alert alert-danger'><i class='fa fa-times-circle'></i> You have no permission to update products order.</div>";
        exit();
    }
}

// Determine if logged in as a seller or super admin
$is_seller = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');
if ($is_seller) {
    $seller_id = (int) $_SESSION['id'];
} else {
    if (isset($_GET['seller_id']) && !empty($_GET['seller_id'])) {
        $seller_id = (int) $_GET['seller_id'];
    } elseif (isset($_POST['seller_id']) && !empty($_POST['seller_id'])) {
        $seller_id = (int) $_POST['seller_id'];
    } else {
        $seller_id = 0;
    }
}

// Fetch list of sellers for admin dropdown
$sellers = array();
if (!$is_seller) {
    $db->sql("SELECT id, name, company_name FROM seller WHERE status = '1' ORDER BY company_name ASC, name ASC");
    $sellers = $db->getResult();
}

$categories = array();
$main_cat_id = 0;
$seller_name = '';

if ($seller_id > 0) {
    $db->sql("SELECT main_cat_id, name, company_name FROM seller WHERE id = '$seller_id'");
    $s_info = $db->getResult();
    if (!empty($s_info)) {
        $main_cat_id = (int) $s_info[0]['main_cat_id'];
        $seller_name = !empty($s_info[0]['company_name']) ? $s_info[0]['company_name'] : $s_info[0]['name'];
    }

    // Categories where this seller actually has products
    $sql = "SELECT c.id, c.name, c.cat_priority FROM category c 
        INNER JOIN products p ON p.category_id = c.id 
        WHERE p.seller_id = '$id' 
        GROUP BY c.id, c.name, c.cat_priority 
        ORDER BY c.cat_priority ASC, c.name ASC";
    $db->sql($sql);
    $categories = $db->getResult();

    if (empty($categories) && !empty($main_cat_id)) {
        $sql = "SELECT id, name FROM category WHERE main_cat = '$main_cat_id' ORDER BY cat_priority ASC, name ASC";
        $db->sql($sql);
        $categories = $db->getResult();
    }
}

// Category filter
$selected_category = (isset($_GET['category_id']) && $_GET['category_id'] !== '') ? $db->escapeString($_GET['category_id']) : '';

// Fetch products
$res = array();
if ($seller_id > 0) {
    $where = " WHERE p.seller_id = '$seller_id' ";
    if ($selected_category !== '') {
        $where .= " AND p.category_id = '$selected_category' ";
    }
    $sql = "SELECT p.*, (SELECT c.name FROM category c WHERE c.id = p.category_id) as category_name 
            FROM `products` p 
            $where 
            ORDER BY p.row_order ASC, p.id ASC";
    $db->sql($sql);
    $res = $db->getResult();
}
?>

<style>
    #sortable-row {
        list-style: none;
        padding-left: 0;
    }

    #sortable-row li {
        margin-bottom: 8px;
        padding: 10px 14px;
        background-color: #fff;
        cursor: grab;
        border: 1px solid #e1e4e8;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }

    #sortable-row li:hover {
        border-color: #3c8dbc;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }

    #sortable-row li.ui-sortable-helper {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: grabbing;
        background-color: #f7fbfe;
    }

    #sortable-row li.ui-state-highlight {
        height: 52px;
        background-color: #f4f6f9;
        border: 2px dashed #3c8dbc;
        border-radius: 6px;
        margin-bottom: 8px;
    }
</style>

<section class="content-header">
    <h1>Products Order
        <small><?= $is_seller ? 'Reorder your store products' : 'Reorder seller products for mobile app display' ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="home.php"><i class="fa fa-home"></i> Home</a></li>
        <li class="active">Product Order</li>
    </ol>
</section>

<section class="content">
    <?php if ($permissions['products_order']['read'] == 1) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Select Seller & Category</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php if (!$is_seller) { ?>
                                <div class="col-md-4 col-sm-6">
                                    <label class="control-label">Select Seller / Store <span
                                            class="text-danger">*</span></label>
                                    <select name="seller_id" id="seller_id" class="form-control select2" style="width:100%;">
                                        <option value="">-- Select Seller --</option>
                                        <?php foreach ($sellers as $s) {
                                            $label = !empty($s['company_name']) ? $s['company_name'] . ' (' . $s['name'] . ')' : $s['name'];
                                            ?>
                                            <option value="<?= $s['id'] ?>" <?= $seller_id == $s['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>

                            <div class="col-md-4 col-sm-6">
                                <label class="control-label">Filter by Category</label>
                                <select name="category_id" id="category_id" class="form-control" style="width:100%;">
                                    <option value="">All Categories</option>
                                    <?php if ($permissions['categories']['read'] == 1) { ?>
                                        <?php foreach ($categories as $category) { ?>
                                            <option value="<?= $category['id'] ?>" <?= ($selected_category == $category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-4 col-sm-12">
                                <label class="control-label" style="display:block;">&nbsp;</label>
                                <button type="button" class="btn btn-primary" id="btn_search"><i class="fa fa-search"></i>
                                    Filter</button>
                                <?php if ($seller_id > 0 && !$is_seller) { ?>
                                    <a href="products-order.php" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-sm-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-sort"></i>
                            <?php if (!empty($seller_name)) { ?>
                                Reorder Products for <strong><?= htmlspecialchars($seller_name) ?></strong>
                            <?php } else { ?>
                                Product Reordering List
                            <?php } ?>
                        </h3>
                    </div>
                    <div class="box-body">
                        <?php if ($permissions['products_order']['update'] == 0) { ?>
                            <div class="alert alert-danger"><i class="fa fa-ban"></i> You do not have permission to update
                                products order.</div>
                        <?php } ?>

                        <?php if ($seller_id == 0) { ?>
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> Please select a seller from the dropdown above to view and
                                reorder their products.
                            </div>
                        <?php } elseif (empty($res)) { ?>
                            <div class="alert alert-warning">
                                <i class="fa fa-exclamation-triangle"></i> No products found for this
                                seller<?= ($selected_category !== '') ? ' in the selected category' : '' ?>.
                            </div>
                        <?php } else { ?>
                            <p class="text-muted" style="margin-bottom:15px;">
                                <i class="fa fa-arrows"></i> Drag and drop products below to set their order, then click
                                <strong>Save Order</strong>. The mobile user app will display products in this exact sequence.
                                Showing <strong><?= count($res) ?></strong> products.
                            </p>

                            <form id="products_form" method="POST" action="products-order.php" class="form-horizontal">
                                <input type="hidden" id="update_products_order" name="update_products_order" value="1" />
                                <input type="hidden" name="seller_id" value="<?= $seller_id ?>" />
                                <input type="hidden" name="category_id" value="<?= htmlspecialchars($selected_category) ?>" />
                                <input type="hidden" name="row_order" id="row_order" required readonly />

                                <div class="form-group" style="overflow-y:auto; max-height:550px; padding: 0 10px;">
                                    <ol id="sortable-row">
                                        <?php foreach ($res as $product) { ?>
                                            <li id="<?= $product["id"] ?>">
                                                <div style="display:flex; align-items:center; gap:12px;">
                                                    <i class="fa fa-bars text-muted" style="font-size:16px;"></i>
                                                    <?php if (!empty($product["image"])) { ?>
                                                        <img src="<?= htmlspecialchars($product['image']) ?>"
                                                            style="height:38px; width:38px; object-fit:cover; border-radius:4px; border:1px solid #ddd;"
                                                            onerror="this.src='images/logo.png'">
                                                    <?php } else { ?>
                                                        <img src="images/logo.png"
                                                            style="height:38px; width:38px; object-fit:contain; border:1px solid #ddd; border-radius:4px;">
                                                    <?php } ?>
                                                    <div>
                                                        <span
                                                            style="font-weight:600; font-size:14px; color:#333;"><?= htmlspecialchars($product["name"]) ?></span>
                                                        <?php if (!empty($product['category_name'])) { ?>
                                                            <span class="text-muted" style="font-size:12px; display:block;"><i
                                                                    class="fa fa-tag"></i>
                                                                <?= htmlspecialchars($product['category_name']) ?></span>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div style="display:flex; align-items:center; gap:8px;">
                                                    <span class="badge order-badge"
                                                        style="background-color:#3c8dbc; font-size:12px; padding:5px 9px;">Order:
                                                        <?= $product["row_order"] ?></span>
                                                    <?php if (isset($product['is_active']) && $product['is_active'] == 0) { ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php } ?>
                                                </div>
                                            </li>
                                        <?php } ?>
                                    </ol>
                                </div>

                                <div class="box-footer" style="padding-top:15px; border-top:1px solid #eee;">
                                    <?php if ($permissions['products_order']['update'] == 1) { ?>
                                        <button type="submit" id="submit_btn" class="btn btn-success btn-lg">
                                            <i class="fa fa-save"></i> Save Order
                                        </button>
                                    <?php } ?>
                                    <div id="result" style="margin-top:12px;"></div>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="alert alert-danger"><i class="fa fa-ban"></i> You have no permission to view products order.</div>
    <?php } ?>
</section>

<?php include "footer.php"; ?>

<script>
    $(function () {
        $("#sortable-row").sortable({
            placeholder: "ui-state-highlight",
            update: function (event, ui) {
                // Live update the order badge numbers on drag
                $('#sortable-row li').each(function (index) {
                    $(this).find('.order-badge').text('Order: ' + index);
                });
            }
        });

        $('#products_form').on('submit', function (e) {
            e.preventDefault();
            var selectedIds = new Array();
            $('ol#sortable-row li').each(function () {
                selectedIds.push($(this).attr("id"));
            });

            if (selectedIds.length === 0) {
                alert('No products to reorder.');
                return false;
            }

            $("#row_order").val(selectedIds.join(','));
            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                beforeSend: function () {
                    $('#submit_btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving Order...');
                },
                cache: false,
                contentType: false,
                processData: false,
                success: function (result) {
                    $('#result').html(result).show().delay(4500).fadeOut();
                    $('#submit_btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Order');
                },
                error: function () {
                    $('#result').html('<div class="alert alert-danger"><i class="fa fa-times"></i> Error saving product order. Please try again.</div>').show().delay(4500).fadeOut();
                    $('#submit_btn').prop('disabled', false).html('<i class="fa fa-save"></i> Save Order');
                }
            });
        });

        $('#btn_search').on('click', function () {
            var url = 'products-order.php?';
            <?php if (!$is_seller) { ?>
                var s_id = $('#seller_id').val();
                if (!s_id) {
                    alert('Please select a seller.');
                    return false;
                }
                url += 'seller_id=' + encodeURIComponent(s_id) + '&';
            <?php } ?>
            url += 'category_id=' + encodeURIComponent($('#category_id').val());
            window.location.href = url;
        });

        <?php if (!$is_seller) { ?>
            $('#seller_id').on('change', function () {
                var s_id = $(this).val();
                if (s_id) {
                    window.location.href = 'products-order.php?seller_id=' + encodeURIComponent(s_id);
                }
            });
        <?php } ?>
    });
</script>