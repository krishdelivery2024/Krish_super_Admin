<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;

$seller_id = 0;
$is_seller_logged_in = (isset($_SESSION['role']) && $_SESSION['role'] == 'seller');

if ($is_seller_logged_in) {
    $seller_id = (int)$_SESSION['id'];
} elseif (isset($_GET['seller_id']) && is_numeric($_GET['seller_id'])) {
    $seller_id = (int)$_GET['seller_id'];
}

// Fetch all sellers for admin dropdown selector if admin is viewing
$all_sellers = array();
if (!$is_seller_logged_in) {
    $db->sql("SELECT s.id, s.name, s.company_name, mc.name AS main_category_name 
              FROM seller s 
              LEFT JOIN main_category mc ON mc.id = s.main_cat_id 
              ORDER BY s.name ASC");
    $all_sellers = $db->getResult();
    if ($seller_id <= 0 && !empty($all_sellers)) {
        $seller_id = (int)$all_sellers[0]['id'];
    }
}

// Fetch current seller data
$seller = null;
if ($seller_id > 0) {
    $db->sql("SELECT s.*, mc.name AS main_category_name 
              FROM seller s 
              LEFT JOIN main_category mc ON mc.id = s.main_cat_id 
              WHERE s.id = $seller_id LIMIT 1");
    $s_res = $db->getResult();
    if (!empty($s_res)) {
        $seller = $s_res[0];
    }
}

$main_cat_id = $seller ? (int)$seller['main_cat_id'] : 0;
$main_cat_name = ($seller && !empty($seller['main_category_name'])) ? $seller['main_category_name'] : 'Not Assigned';

// Fetch available categories under this Business Category
$available_categories = array();
if ($main_cat_id > 0) {
    $db->sql("SELECT id, name, subtitle, image, status 
              FROM category 
              WHERE main_cat = $main_cat_id AND status = 1 
              ORDER BY name ASC");
    $available_categories = $db->getResult();
}

// Fetch already selected category IDs for this seller
$selected_category_ids = array();
if ($seller_id > 0) {
    $db->sql("SELECT category_id FROM seller_categories WHERE seller_id = $seller_id");
    $sc_res = $db->getResult();
    if (!empty($sc_res)) {
        foreach ($sc_res as $r) {
            $selected_category_ids[] = (int)$r['category_id'];
        }
    } elseif (!empty($seller['categories'])) {
        $parts = explode(',', $seller['categories']);
        foreach ($parts as $p) {
            $cid = (int)trim($p);
            if ($cid > 0) $selected_category_ids[] = $cid;
        }
    }
}
$selected_category_ids = array_unique($selected_category_ids);
?>

<style>
.manage-cat-wrapper {
    padding: 15px 0 30px 0;
}
.seller-cat-header-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 18px 22px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 20px;
    border-left: 5px solid #fb8b40;
}
.business-cat-badge {
    background: #eef2ff;
    color: #3b82f6;
    font-size: 13px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-block;
}
.cat-stats-badge {
    background: #f0fdf4;
    color: #16a34a;
    font-size: 13px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-block;
}
.cat-search-box {
    border-radius: 6px;
    padding: 8px 14px;
    border: 1px solid #d1d5db;
    box-shadow: none;
    font-size: 14px;
    width: 100%;
}
.category-card-item {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 15px;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    position: relative;
    user-select: none;
}
.category-card-item:hover {
    border-color: #fb8b40;
    box-shadow: 0 4px 12px rgba(251, 139, 64, 0.15);
    transform: translateY(-2px);
}
.category-card-item.selected {
    border-color: #10b981;
    background: #f0fdf4;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}
.cat-card-img {
    width: 50px;
    height: 50px;
    border-radius: 6px;
    object-fit: cover;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
}
.cat-card-checkbox {
    width: 22px;
    height: 22px;
    accent-color: #10b981;
    cursor: pointer;
}
.cat-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
}
.cat-card-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
    line-height: 1.3;
}
.cat-status-pill {
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
}
.cat-status-pill.active-pill {
    background: #dcfce7;
    color: #15803d;
}
.cat-status-pill.inactive-pill {
    background: #f3f4f6;
    color: #9ca3af;
}
.save-bar-sticky {
    position: sticky;
    bottom: 15px;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    margin-top: 20px;
}
</style>

<div class="content-wrapper manage-cat-wrapper">
    <section class="content">
        <div class="container-fluid">

            <!-- Admin Seller Selector (Shown only when Super Admin/Admin visits) -->
            <?php if (!$is_seller_logged_in): ?>
            <div class="box box-default" style="border-radius: 8px; margin-bottom: 15px;">
                <div class="box-body" style="padding: 15px 20px;">
                    <form method="GET" action="manage-category.php" class="form-inline" id="admin_seller_select_form">
                        <label for="seller_id" style="font-size: 14px; margin-right: 10px;">Select Seller Store:</label>
                        <select name="seller_id" id="seller_id" class="form-control" style="min-width: 280px;" onchange="this.form.submit()">
                            <?php foreach ($all_sellers as $s): ?>
                                <option value="<?= $s['id'] ?>" <?= ($s['id'] == $seller_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['company_name']) ?>) - [<?= htmlspecialchars($s['main_category_name'] ?? 'N/A') ?>]
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-muted" style="margin-left: 15px; font-size: 13px;">Managing categories on behalf of seller</span>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!$seller): ?>
                <div class="alert alert-warning" style="border-radius: 8px;">
                    <i class="fa fa-exclamation-triangle"></i> Seller store not found. Please verify the seller ID.
                </div>
            <?php else: ?>

            <!-- Header Card -->
            <div class="seller-cat-header-card">
                <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                    <div class="col-md-8 col-sm-12">
                        <h3 style="margin: 0 0 6px 0; font-weight: 700; color: #111827;">
                            <i class="fa fa-tags text-primary" style="margin-right: 8px;"></i>Manage Store Categories
                        </h3>
                        <p style="margin: 0; color: #4b5563; font-size: 14px;">
                            Store: <strong><?= htmlspecialchars($seller['name']) ?></strong> (<?= htmlspecialchars($seller['company_name']) ?>)
                            &nbsp;&bull;&nbsp;
                            Business Category: <span class="business-cat-badge"><i class="fa fa-briefcase"></i> <?= htmlspecialchars($main_cat_name) ?></span>
                        </p>
                    </div>
                    <div class="col-md-4 col-sm-12 text-right" style="margin-top: 8px;">
                        <span class="cat-stats-badge" id="stats_selected_count">
                            <i class="fa fa-check-circle"></i> <span id="txt_selected_count"><?= count($selected_category_ids) ?></span> / <?= count($available_categories) ?> Selected
                        </span>
                    </div>
                </div>
                <div style="margin-top: 12px; font-size: 13px; color: #6b7280; border-top: 1px dashed #e5e7eb; padding-top: 10px;">
                    <i class="fa fa-info-circle text-info"></i> <strong>How it works:</strong> Check the categories your store deals in. <strong>Only the category names you select here will be shown in your Seller Mobile App Inventory</strong>, keeping your inventory clear and focused.
                </div>
            </div>

            <!-- Toolbar (Search, Select All, Deselect All) -->
            <div class="row" style="margin-bottom: 15px;">
                <div class="col-md-6 col-sm-12" style="margin-bottom: 8px;">
                    <div style="position: relative;">
                        <i class="fa fa-search" style="position: absolute; left: 14px; top: 12px; color: #9ca3af;"></i>
                        <input type="text" id="cat_search_input" class="cat-search-box" style="padding-left: 36px;" placeholder="Search categories by name or subtitle...">
                    </div>
                </div>
                <div class="col-md-6 col-sm-12 text-right" style="margin-bottom: 8px;">
                    <button type="button" class="btn btn-default btn-sm" id="btn_select_all" style="border-radius: 6px; font-weight: 600; margin-right: 6px;">
                        <i class="fa fa-check-square-o text-success"></i> Select All
                    </button>
                    <button type="button" class="btn btn-default btn-sm" id="btn_deselect_all" style="border-radius: 6px; font-weight: 600; margin-right: 6px;">
                        <i class="fa fa-square-o text-muted"></i> Deselect All
                    </button>
                    <button type="button" class="btn btn-primary btn-sm btn-save-categories" style="border-radius: 6px; font-weight: 600; background: #fb8b40; border-color: #fb8b40;">
                        <i class="fa fa-save"></i> Save Changes
                    </button>
                </div>
            </div>

            <!-- Alert Notification Area -->
            <div id="save_alert_box" style="display: none; margin-bottom: 15px;"></div>

            <!-- Categories Grid -->
            <?php if (empty($available_categories)): ?>
                <div class="alert alert-info" style="border-radius: 8px;">
                    <i class="fa fa-info-circle"></i> No categories have been created under your Business Category (<strong><?= htmlspecialchars($main_cat_name) ?></strong>) yet. Please contact admin to add categories under this Business Category.
                </div>
            <?php else: ?>
                <form id="seller_categories_form" method="POST">
                    <input type="hidden" name="action" value="save_seller_categories">
                    <input type="hidden" name="seller_id" value="<?= $seller_id ?>">

                    <div class="row" id="categories_grid_container">
                        <?php foreach ($available_categories as $cat): 
                            $is_selected = in_array((int)$cat['id'], $selected_category_ids);
                            $img_url = !empty($cat['image']) ? $cat['image'] : 'dist/img/default-50x50.gif';
                            if (!empty($cat['image']) && strpos($cat['image'], 'http') !== 0 && strpos($cat['image'], '/') !== 0) {
                                $img_url = $cat['image'];
                            }
                        ?>
                        <div class="col-md-4 col-sm-6 col-xs-12 cat-card-wrapper" data-name="<?= strtolower(htmlspecialchars($cat['name'])) ?>" data-sub="<?= strtolower(htmlspecialchars($cat['subtitle'] ?? '')) ?>">
                            <div class="category-card-item <?= $is_selected ? 'selected' : '' ?>" data-id="<?= $cat['id'] ?>">
                                <span class="cat-status-pill <?= $is_selected ? 'active-pill' : 'inactive-pill' ?>" id="pill_<?= $cat['id'] ?>">
                                    <?= $is_selected ? '<i class="fa fa-check"></i> Enabled' : 'Disabled' ?>
                                </span>

                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <input type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>" class="cat-card-checkbox" <?= $is_selected ? 'checked' : '' ?>>
                                    
                                    <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="cat-card-img" onerror="this.src='dist/img/default-50x50.gif';">
                                    
                                    <div style="flex: 1; min-width: 0;">
                                        <h4 class="cat-card-title text-truncate" title="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></h4>
                                        <p class="cat-card-subtitle text-truncate" title="<?= htmlspecialchars($cat['subtitle'] ?? '') ?>">
                                            <?= !empty($cat['subtitle']) ? htmlspecialchars($cat['subtitle']) : 'Category ID: #' . $cat['id'] ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Sticky Bottom Action Bar -->
                    <div class="save-bar-sticky">
                        <div class="row" style="display: flex; align-items: center; flex-wrap: wrap;">
                            <div class="col-md-7 col-xs-12" style="margin-bottom: 6px;">
                                <span style="font-size: 14px; color: #374151;">
                                    <strong><span id="bottom_txt_selected_count"><?= count($selected_category_ids) ?></span></strong> categories selected for store <strong><?= htmlspecialchars($seller['name']) ?></strong>.
                                </span>
                            </div>
                            <div class="col-md-5 col-xs-12 text-right">
                                <button type="button" class="btn btn-success btn-save-categories" style="padding: 8px 24px; font-size: 14px; font-weight: 700; border-radius: 6px; background: #10b981; border-color: #10b981; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
                                    <i class="fa fa-check"></i> Save Categories for Seller App
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>

            <?php endif; ?>

        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // 1. Category Card click toggle
    $('.category-card-item').on('click', function(e) {
        // If clicking directly on checkbox, let checkbox change handler handle it
        if ($(e.target).is('input[type="checkbox"]')) {
            return;
        }
        var $cb = $(this).find('.cat-card-checkbox');
        $cb.prop('checked', !$cb.prop('checked')).trigger('change');
    });

    // 2. Checkbox change event
    $('.cat-card-checkbox').on('change', function() {
        var isChecked = $(this).prop('checked');
        var $card = $(this).closest('.category-card-item');
        var catId = $card.data('id');
        var $pill = $('#pill_' + catId);

        if (isChecked) {
            $card.addClass('selected');
            $pill.removeClass('inactive-pill').addClass('active-pill').html('<i class="fa fa-check"></i> Enabled');
        } else {
            $card.removeClass('selected');
            $pill.removeClass('active-pill').addClass('inactive-pill').text('Disabled');
        }
        updateSelectedCounter();
    });

    // 3. Update counter badges
    function updateSelectedCounter() {
        var count = $('.cat-card-checkbox:checked').length;
        $('#txt_selected_count').text(count);
        $('#bottom_txt_selected_count').text(count);
    }

    // 4. Select All
    $('#btn_select_all').on('click', function() {
        // Only select currently visible cards if search is active
        $('.cat-card-wrapper:visible .cat-card-checkbox').each(function() {
            $(this).prop('checked', true).trigger('change');
        });
    });

    // 5. Deselect All
    $('#btn_deselect_all').on('click', function() {
        $('.cat-card-wrapper:visible .cat-card-checkbox').each(function() {
            $(this).prop('checked', false).trigger('change');
        });
    });

    // 6. Live Search / Filter
    $('#cat_search_input').on('keyup search', function() {
        var query = $(this).val().toLowerCase().trim();
        if (!query) {
            $('.cat-card-wrapper').show();
            return;
        }
        $('.cat-card-wrapper').each(function() {
            var name = $(this).data('name') || '';
            var sub = $(this).data('sub') || '';
            if (name.indexOf(query) !== -1 || sub.indexOf(query) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // 7. Save Categories via AJAX
    $('.btn-save-categories').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var originalHtml = $btn.html();
        $('.btn-save-categories').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        var selectedIds = [];
        $('.cat-card-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        var sellerId = $('input[name="seller_id"]').val();

        $.ajax({
            url: 'api-admin/update-seller-categories.php',
            type: 'POST',
            data: {
                action: 'save_seller_categories',
                seller_id: sellerId,
                category_ids: selectedIds
            },
            dataType: 'json',
            success: function(resp) {
                $('.btn-save-categories').prop('disabled', false).html(originalHtml);
                if (!resp.error) {
                    $('#save_alert_box')
                        .removeClass('alert-danger')
                        .addClass('alert alert-success')
                        .html('<i class="fa fa-check-circle"></i> <strong>Success!</strong> ' + (resp.message || 'Categories saved successfully!') + ' (Total: ' + resp.count + ' categories active for seller mobile app inventory)')
                        .slideDown();
                    
                    // Smoothly scroll to alert if needed
                    $('html, body').animate({ scrollTop: $('#save_alert_box').offset().top - 80 }, 300);

                    setTimeout(function() {
                        $('#save_alert_box').slideUp();
                    }, 5000);
                } else {
                    $('#save_alert_box')
                        .removeClass('alert-success')
                        .addClass('alert alert-danger')
                        .html('<i class="fa fa-times-circle"></i> <strong>Error:</strong> ' + (resp.message || 'Failed to save categories.'))
                        .slideDown();
                }
            },
            error: function(xhr, status, err) {
                $('.btn-save-categories').prop('disabled', false).html(originalHtml);
                $('#save_alert_box')
                    .removeClass('alert-success')
                    .addClass('alert alert-danger')
                    .html('<i class="fa fa-times-circle"></i> <strong>Request Failed:</strong> ' + err)
                    .slideDown();
            }
        });
    });
});
</script>
