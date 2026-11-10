<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
session_start();

include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');
include_once('../includes/variables.php');
include_once('send-email.php');

$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();

$settings = $function->get_settings('system_timezone', true);
$app_name = isset($settings['app_name']) ? $settings['app_name'] : 'Krish Delivery';
$support_email = isset($settings['support_email']) ? $settings['support_email'] : '';
$config = $function->get_configurations();

if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$action = isset($_POST['action']) ? trim($_POST['action']) : '';

// ==============================================================
// 1. LOOKUP CUSTOMER BY PHONE
// ==============================================================
if ($action == 'get_customer_by_phone') {
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    if (empty($mobile)) {
        echo json_encode(array('error' => true, 'message' => 'Mobile number is required'));
        exit;
    }
    $clean_mobile = $db->escapeString($function->xss_clean($mobile));
    $db->sql("SELECT id, name, mobile, address, latitude, longitude FROM users WHERE mobile = '$clean_mobile' LIMIT 1");
    $res = $db->getResult();
    if (!empty($res)) {
        $uid = (int) $res[0]['id'];
        $alt_mobile = '';
        $db->sql("SELECT alternate_mobile, address, latitude, longitude FROM user_address WHERE user_id = $uid ORDER BY id DESC LIMIT 1");
        $adr_res = $db->getResult();
        if (!empty($adr_res)) {
            if (!empty($adr_res[0]['alternate_mobile'])) {
                $alt_mobile = $adr_res[0]['alternate_mobile'];
            }
            if (!empty($adr_res[0]['address'])) {
                $res[0]['address'] = $adr_res[0]['address'];
            }
            if (!empty($adr_res[0]['latitude']) && (float) $adr_res[0]['latitude'] != 0) {
                $res[0]['latitude'] = $adr_res[0]['latitude'];
            }
            if (!empty($adr_res[0]['longitude']) && (float) $adr_res[0]['longitude'] != 0) {
                $res[0]['longitude'] = $adr_res[0]['longitude'];
            }
        }
        echo json_encode(array(
            'error' => false,
            'found' => true,
            'user' => array(
                'id' => $res[0]['id'],
                'name' => $res[0]['name'],
                'mobile' => $res[0]['mobile'],
                'alternate_mobile' => $alt_mobile,
                'address' => $res[0]['address'],
                'latitude' => $res[0]['latitude'],
                'longitude' => $res[0]['longitude']
            )
        ));
    } else {
        echo json_encode(array('error' => false, 'found' => false));
    }
    exit;
}

// ==============================================================
// 2. GET RESTAURANTS (SELLERS) BY ZONE
// ==============================================================
if ($action == 'get_sellers_by_zone') {
    $zone_id = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    if ($zone_id <= 0) {
        echo json_encode(array('error' => true, 'message' => 'Valid Zone ID is required', 'sellers' => array()));
        exit;
    }

    $db->sql("SELECT id, name, company_name, mobile, store_address, personal_address, latitude, longitude, store_status, opening_time, closing_time 
              FROM seller 
              WHERE (zone_id = $zone_id OR FIND_IN_SET($zone_id, zone_id)) AND status = 1 
              ORDER BY company_name ASC");
    $sellers = $db->getResult();
    if (!is_array($sellers)) {
        $sellers = array();
    }

    // Determine current open/close status
    $now_min = ((int) date('G') * 60) + (int) date('i');
    foreach ($sellers as &$s) {
        $is_open = true;
        if (isset($s['store_status']) && $s['store_status'] == 'false') {
            $is_open = false;
        }
        if (!empty($s['opening_time']) && !empty($s['closing_time'])) {
            $parts_o = explode(':', $s['opening_time']);
            $parts_c = explode(':', $s['closing_time']);
            $open_min = ((int) $parts_o[0] * 60) + (int) $parts_o[1];
            $close_min = ((int) $parts_c[0] * 60) + (int) $parts_c[1];
            if ($now_min < $open_min || $now_min >= $close_min) {
                $is_open = false;
            }
        }
        $s['is_open'] = $is_open;
        $s['display_name'] = !empty($s['company_name']) ? $s['company_name'] : $s['name'];
        $s['address_display'] = !empty($s['store_address']) ? $s['store_address'] : $s['personal_address'];
    }

    echo json_encode(array('error' => false, 'sellers' => $sellers));
    exit;
}

// ==============================================================
// 3. GET PRODUCTS & VARIANTS BY RESTAURANT (SELLER)
// ==============================================================
if ($action == 'get_products_by_seller') {
    $seller_id = isset($_POST['seller_id']) ? (int) $_POST['seller_id'] : 0;
    if ($seller_id <= 0) {
        echo json_encode(array('error' => true, 'message' => 'Valid Seller ID is required', 'products' => array()));
        exit;
    }

    $sql = "SELECT p.id as product_id, p.name as product_name, p.image, p.status as product_status,
                   pv.id as variant_id, pv.type, pv.measurement, pv.price, pv.discounted_price, 
                   pv.vendor_price, pv.stock, pv.serve_for, pv.is_offer, pv.offer_price,
                   COALESCE(u.short_code, u.name, '') as unit_name
            FROM products p
            JOIN product_variant pv ON pv.product_id = p.id
            LEFT JOIN unit u ON u.id = pv.measurement_unit_id
            WHERE p.seller_id = $seller_id 
              AND (p.status = '1' OR p.status = 1)
              AND (pv.serve_for != 'Sold Out' OR pv.serve_for IS NULL)
            ORDER BY p.name ASC, pv.id ASC";

    $db->sql($sql);
    $rows = $db->getResult();
    if (!is_array($rows)) {
        $rows = array();
    }

    // Group variants under each product
    $products = array();
    foreach ($rows as $r) {
        $pid = $r['product_id'];
        if (!isset($products[$pid])) {
            $img = !empty($r['image']) ? $r['image'] : '';
            $products[$pid] = array(
                'product_id' => $r['product_id'],
                'product_name' => $r['product_name'],
                'image' => $img,
                'variants' => array()
            );
        }

        $price = (float) $r['price'];
        $discounted_price = (float) $r['discounted_price'];
        $is_offer = (int) $r['is_offer'];
        $offer_price = (float) $r['offer_price'];

        $effective_price = $price;
        if ($is_offer == 1 && $offer_price > 0) {
            $effective_price = $offer_price;
        } elseif ($discounted_price > 0) {
            $effective_price = $discounted_price;
        }

        $measurement_str = trim($r['measurement'] . ' ' . $r['unit_name']);

        $products[$pid]['variants'][] = array(
            'variant_id' => $r['variant_id'],
            'measurement' => $measurement_str,
            'price' => $price,
            'discounted_price' => $discounted_price,
            'is_offer' => $is_offer,
            'offer_price' => $offer_price,
            'effective_price' => $effective_price,
            'stock' => $r['stock'],
            'serve_for' => $r['serve_for']
        );
    }

    echo json_encode(array('error' => false, 'products' => array_values($products)));
    exit;
}

// ==============================================================
// 4. CALCULATE FEES (SUBTOTAL + PLATFORM + CONVENIENCE + DELIVERY)
// ==============================================================
if ($action == 'calculate_fees') {
    $zone_id = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $seller_id = isset($_POST['seller_id']) ? (int) $_POST['seller_id'] : 0;
    $subtotal = isset($_POST['subtotal']) ? (float) $_POST['subtotal'] : 0.0;
    $custom_delivery = isset($_POST['delivery_charge']) ? trim($_POST['delivery_charge']) : '';
    $user_lat = isset($_POST['latitude']) ? (float) $_POST['latitude'] : 0.0;
    $user_lng = isset($_POST['longitude']) ? (float) $_POST['longitude'] : 0.0;
    $manual_delivery = isset($_POST['manual_delivery']) && ($_POST['manual_delivery'] == '1' || $_POST['manual_delivery'] === 'true');

    $fee_settings = $settings;
    $zone_fees = $function->split_zone_fees($subtotal, $zone_id, $fee_settings);
    $platform_fee = $zone_fees['platform_fee'];
    $convenience_fee = $zone_fees['convenience_fee'];
    $tax_amount = isset($zone_fees['gst']) ? $zone_fees['gst'] : 0.0;

    $distance_km = 0.0;
    $distance_str = '';
    $calculated_delivery = null;

    if ($seller_id > 0 && $user_lat != 0 && $user_lng != 0) {
        $db->sql("SELECT latitude, longitude, zone_id FROM seller WHERE id = $seller_id LIMIT 1");
        $seller_res = $db->getResult();
        if (!empty($seller_res)) {
            $store_lat = (float) $seller_res[0]['latitude'];
            $store_lng = (float) $seller_res[0]['longitude'];
            if ($store_lat == 0 && isset($settings['store_lattitude'])) {
                $store_lat = (float) $settings['store_lattitude'];
            }
            if ($store_lng == 0 && isset($settings['store_longitude'])) {
                $store_lng = (float) $settings['store_longitude'];
            }

            if ($store_lat != 0 && $store_lng != 0) {
                $road = $function->get_road_distance_km($store_lat, $store_lng, $user_lat, $user_lng);
                if (isset($road['km'])) {
                    $distance_km = round((float) $road['km'], 2);
                    $distance_str = $distance_km . ' km';

                    $effective_zone = $zone_id > 0 ? $zone_id : (!empty($seller_res[0]['zone_id']) ? (int) $seller_res[0]['zone_id'] : 0);

                    $db->sql("SELECT * FROM delivery_method WHERE id = 1 LIMIT 1");
                    $dm_res = $db->getResult();
                    $dm_row = !empty($dm_res) ? $dm_res[0] : array();
                    $dm_zone_cfg = $function->get_zone_delivery_config($effective_zone, $dm_row);

                    if (!empty($dm_zone_cfg['in_persion_data'])) {
                        $calculated_delivery = (float) $function->food_delivery_tariff_charge($distance_km, $dm_zone_cfg['in_persion_data']);
                    }
                }
            }
        }
    }

    // Delivery charge resolution:
    // If admin explicitly manually entered a delivery charge, respect it;
    // otherwise if distance-based delivery charge was calculated, use that;
    // fallback to provided custom delivery if numeric, else system default.
    $delivery_charge = 0.0;
    if ($manual_delivery && $custom_delivery !== '' && is_numeric($custom_delivery)) {
        $delivery_charge = (float) $custom_delivery;
    } elseif ($calculated_delivery !== null) {
        $delivery_charge = $calculated_delivery;
    } elseif ($custom_delivery !== '' && is_numeric($custom_delivery)) {
        $delivery_charge = (float) $custom_delivery;
    } else {
        $delivery_charge = isset($settings['delivery_charge']) && is_numeric($settings['delivery_charge']) ? (float) $settings['delivery_charge'] : 45.0;
    }

    $final_total = round($subtotal + $delivery_charge + $platform_fee + $convenience_fee + $tax_amount, 2);

    echo json_encode(array(
        'error' => false,
        'subtotal' => round($subtotal, 2),
        'delivery_charge' => round($delivery_charge, 2),
        'distance_km' => round($distance_km, 2),
        'distance_str' => $distance_str,
        'platform_fee' => round($platform_fee, 2),
        'convenience_fee' => round($convenience_fee, 2),
        'tax_amount' => round($tax_amount, 2),
        'final_total' => round($final_total, 2)
    ));
    exit;
}

// ==============================================================
// 5. PLACE ADMIN ORDER
// ==============================================================
if ($action == 'place_admin_order') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $alternate_mobile = isset($_POST['alternate_mobile']) ? trim($_POST['alternate_mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $zone_id = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $seller_id = isset($_POST['seller_id']) ? (int) $_POST['seller_id'] : 0;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Paid to Admin';
    $delivery_charge = isset($_POST['delivery_charge']) ? (float) $_POST['delivery_charge'] : 45.0;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $latitude = isset($_POST['latitude']) ? trim($_POST['latitude']) : '0';
    $longitude = isset($_POST['longitude']) ? trim($_POST['longitude']) : '0';
    $raw_items = isset($_POST['items']) ? $_POST['items'] : '';

    // Validation
    if (empty($name)) {
        echo json_encode(array('error' => true, 'message' => 'Customer name is required!'));
        exit;
    }
    if (empty($mobile) || strlen($mobile) < 10) {
        echo json_encode(array('error' => true, 'message' => 'Valid 10-digit customer mobile number is required!'));
        exit;
    }
    if (empty($address)) {
        echo json_encode(array('error' => true, 'message' => 'Customer delivery address is required!'));
        exit;
    }
    if ($zone_id <= 0) {
        echo json_encode(array('error' => true, 'message' => 'Please select a Delivery Zone!'));
        exit;
    }
    if ($seller_id <= 0) {
        echo json_encode(array('error' => true, 'message' => 'Please select a Restaurant / Seller!'));
        exit;
    }

    $items = is_array($raw_items) ? $raw_items : json_decode($raw_items, true);
    if (empty($items) || !is_array($items)) {
        echo json_encode(array('error' => true, 'message' => 'Please add at least one product to the order!'));
        exit;
    }

    // Normalizing payment method
    if ($payment_method !== 'Paid to Admin' && $payment_method !== 'Cash on Delivery') {
        $payment_method = 'Paid to Admin';
    }

    // 1. Resolve or Create User
    $clean_mobile = $db->escapeString($function->xss_clean($mobile));
    $db->sql("SELECT id, name, mobile, address, latitude, longitude FROM users WHERE mobile = '$clean_mobile' LIMIT 1");
    $user_res = $db->getResult();

    if (!empty($user_res)) {
        $user_id = (int) $user_res[0]['id'];
        $up_data = array();
        if (!empty($name)) {
            $up_data['name'] = $db->escapeString($function->xss_clean($name));
        }
        if (!empty($address)) {
            $up_data['address'] = $db->escapeString($function->xss_clean($address));
        }
        if ($latitude != '0' && !empty($latitude)) {
            $up_data['latitude'] = $db->escapeString($latitude);
        }
        if ($longitude != '0' && !empty($longitude)) {
            $up_data['longitude'] = $db->escapeString($longitude);
        }
        if (!empty($up_data)) {
            $db->update('users', $up_data, "id = $user_id");
        }
    } else {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $referral_code = "";
        for ($i = 0; $i < 10; $i++) {
            $referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $new_user_data = array(
            'name' => $db->escapeString($function->xss_clean($name)),
            'email' => '',
            'password' => '',
            'dob' => '',
            'city' => 0,
            'area' => 0,
            'street' => '',
            'address' => $db->escapeString($function->xss_clean($address)),
            'pincode' => '',
            'friends_code' => '',
            'mobile' => $clean_mobile,
            'country_code' => '+91',
            'fcm_id' => '',
            'apikey' => '',
            'referral_code' => $referral_code,
            'otp' => '000000',
            'latitude' => $db->escapeString($latitude),
            'longitude' => $db->escapeString($longitude),
            'is_logged_in' => 1,
            'status' => 1
        );
        $db->insert('users', $new_user_data);
        $ins_res = $db->getResult();
        $user_id = (int) $ins_res[0];
    }

    // 2. Resolve items & prices
    $item_records = array();
    $total_subtotal = 0.0;

    foreach ($items as $itm) {
        $vid = isset($itm['variant_id']) ? (int) $itm['variant_id'] : 0;
        $qty = isset($itm['quantity']) ? (int) $itm['quantity'] : 0;
        if ($vid <= 0 || $qty <= 0)
            continue;

        $db->sql("SELECT pv.*, p.name as product_name, p.seller_id as p_seller_id, 
                         COALESCE(p.sgst, 0) as p_sgst, COALESCE(p.cgst, 0) as p_cgst, COALESCE(p.igst, 0) as p_igst,
                         COALESCE(u.short_code, u.name, '') as unit_name
                  FROM product_variant pv 
                  JOIN products p ON p.id = pv.product_id 
                  LEFT JOIN unit u ON u.id = pv.measurement_unit_id
                  WHERE pv.id = $vid LIMIT 1");
        $var_res = $db->getResult();
        if (empty($var_res))
            continue;

        $vrow = $var_res[0];
        $price = (float) $vrow['price'];
        $disc_price = (float) $vrow['discounted_price'];
        $vendor_price = (float) $vrow['vendor_price'];
        $is_offer = isset($vrow['is_offer']) ? (int) $vrow['is_offer'] : 0;
        $offer_price = isset($vrow['offer_price']) ? (float) $vrow['offer_price'] : 0.0;

        if ($is_offer == 1 && $offer_price > 0) {
            $unit_price = $offer_price;
        } else {
            $unit_price = ($disc_price > 0) ? $disc_price : $price;
        }

        $line_subtotal = round($unit_price * $qty, 2);
        $total_subtotal += $line_subtotal;

        $unit_str = !empty($vrow['unit_name']) ? $vrow['unit_name'] : '';

        $item_records[] = array(
            'variant_id' => $vid,
            'product_id' => $vrow['product_id'],
            'product_name' => $vrow['product_name'],
            'type' => $vrow['type'],
            'measurement' => $vrow['measurement'],
            'unit' => $unit_str,
            'measurement_unit_id' => $vrow['measurement_unit_id'],
            'stock_unit_id' => $vrow['stock_unit_id'],
            'price' => $price,
            'discounted_price' => ($is_offer == 1 && $offer_price > 0) ? $offer_price : $disc_price,
            'vendor_price' => $vendor_price,
            'sgst' => $vrow['p_sgst'],
            'cgst' => $vrow['p_cgst'],
            'igst' => $vrow['p_igst'],
            'quantity' => $qty,
            'sub_total' => $line_subtotal,
            'stock' => $vrow['stock']
        );
    }

    if (empty($item_records)) {
        echo json_encode(array('error' => true, 'message' => 'No valid products in cart!'));
        exit;
    }

    // 3. Compute Fees
    $fee_settings = $settings;
    $zone_fees = $function->split_zone_fees($total_subtotal, $zone_id, $fee_settings);
    $platform_fee = $zone_fees['platform_fee'];
    $convenience_fee = $zone_fees['convenience_fee'];
    $tax_amount = isset($zone_fees['gst']) ? $zone_fees['gst'] : 0.0;
    $final_total = round($total_subtotal + $delivery_charge + $platform_fee + $convenience_fee + $tax_amount, 2);

    $payment_status = ($payment_method === 'Paid to Admin') ? 1 : 0;
    $order_status_arr = array(array('received', date("d-m-Y h:i:sa")));

    // Snapshot commissions
    $admin_seller_comm = 0.0;
    if (!empty($seller_id)) {
        $db->sql("SELECT commission FROM seller WHERE id = " . (int) $seller_id . " LIMIT 1");
        $asc_res = $db->getResult();
        if (!empty($asc_res) && isset($asc_res[0]['commission']) && $asc_res[0]['commission'] !== '' && floatval($asc_res[0]['commission']) > 0) {
            $admin_seller_comm = floatval($asc_res[0]['commission']);
        } else {
            $admin_seller_comm = isset($settings['seller_commission']) && $settings['seller_commission'] !== '' ? floatval($settings['seller_commission']) : 0.0;
        }
    }
    $admin_db_comm = isset($settings['delivery_boy_commission']) && $settings['delivery_boy_commission'] !== '' ? floatval($settings['delivery_boy_commission']) : 5.0;

    $assign_delivery_boy_id = isset($_POST['delivery_boy_id']) ? (int) $_POST['delivery_boy_id'] : 0;
    $assigned_boy_info = null;
    if ($assign_delivery_boy_id > 0) {
        $db->sql("SELECT id, name, mobile FROM delivery_boys WHERE id = " . $assign_delivery_boy_id . " LIMIT 1");
        $db_boy_res = $db->getResult();
        if (!empty($db_boy_res)) {
            $assigned_boy_info = $db_boy_res[0];
        }
    }

    // 4. Insert Order
    $order_data = array(
        'user_id' => $user_id,
        'seller_id' => $seller_id,
        'store_id' => 0,
        'delivery_boy_id' => $assign_delivery_boy_id,
        'mobile' => $db->escapeString($function->xss_clean($mobile)),
        'alternate_mobile' => $db->escapeString($function->xss_clean($alternate_mobile)),
        'name' => $db->escapeString($function->xss_clean($name)),
        'email' => '',
        'delivery_method' => 'doorstep',
        'delivery_charge' => $delivery_charge,
        'platform_fee' => $platform_fee,
        'convenience_fee' => $convenience_fee,
        'seller_commission' => $admin_seller_comm,
        'delivery_boy_commission' => $admin_db_comm,
        'wallet_balance' => 0,
        'total' => $total_subtotal,
        'tax_amount' => $tax_amount,
        'final_total' => $final_total,
        'payment_method' => $payment_method, // 'Paid to Admin' or 'Cash on Delivery'
        'payment_status' => $payment_status,
        'address' => $db->escapeString($function->xss_clean($address)),
        'delivery_city' => 0,
        'delivery_state' => 0,
        'delivery_zone' => $zone_id,
        'zone_id' => $zone_id,
        'gst_no' => '',
        'delivery_time' => 'Standard Delivery',
        'status' => $db->escapeString(json_encode($order_status_arr)),
        'latitude' => $db->escapeString($latitude),
        'longitude' => $db->escapeString($longitude),
        'promo_code' => '-',
        'promo_discount' => 0,
        'discount' => 0,
        'notes' => $db->escapeString(!empty($notes) ? $notes : 'Created by Admin'),
        'active_status' => 'received',
        'date_added' => date("Y-m-d H:i:s")
    );

    $db->insert('orders', $order_data);
    $order_res = $db->getResult();
    if (empty($order_res) || !isset($order_res[0])) {
        echo json_encode(array('error' => true, 'message' => 'Failed to create order. Please try again.'));
        exit;
    }
    $order_id = $order_res[0];

    // 5. Insert Order Items & Update Stock
    foreach ($item_records as $itm) {
        $item_data = array(
            'user_id' => $user_id,
            'order_id' => $db->escapeString($order_id),
            'product_variant_id' => $itm['variant_id'],
            'quantity' => $itm['quantity'],
            'price' => $itm['price'],
            'discounted_price' => $itm['discounted_price'],
            'vendor_price' => $itm['vendor_price'],
            'discount' => 0,
            'product_name' => $db->escapeString($itm['product_name']),
            'measurement' => $db->escapeString($itm['measurement']),
            'unit' => $db->escapeString($itm['unit']),
            'sgst' => $itm['sgst'],
            'cgst' => $itm['cgst'],
            'igst' => $itm['igst'],
            'sub_total' => $itm['sub_total'],
            'status' => $db->escapeString(json_encode($order_status_arr)),
            'active_status' => 'received'
        );
        $db->insert('order_items', $item_data);

        // Deduct variant stock
        if ($itm['type'] == 'packet') {
            $new_stock = (float) $itm['stock'] - (int) $itm['quantity'];
            $db->sql("UPDATE product_variant SET stock = '$new_stock' WHERE id = " . $itm['variant_id']);
            if ($new_stock <= 0) {
                $db->sql("UPDATE product_variant SET serve_for = 'Sold Out' WHERE id = " . $itm['variant_id']);
            }
        }
    }

    // 6. Send Notifications
    $notif_message = "New order ID : #" . $order_id . " received please take note of it and proceed further";
    try {
        $function->send_new_order_notification("New Order Received", $notif_message, 'order');
    } catch (Exception $e) {
    } catch (Error $e) {
    }

    try {
        if (!empty($seller_id)) {
            $function->send_notification_to_seller($seller_id, "New Order Received", $notif_message, 'order', $order_id);
        }
    } catch (Exception $e) {
    } catch (Error $e) {
    }

    if ($assign_delivery_boy_id > 0) {
        try {
            $db_msg = "Order #" . $order_id . " has been assigned to you by Admin. Address: " . mb_substr($address, 0, 45);
            $function->send_notification_to_delivery_boy($assign_delivery_boy_id, "New Order Assigned by Admin", $db_msg, 'order_assigned', $order_id, 'food');
        } catch (Exception $e) {
        } catch (Error $e) {
        }
    }

    echo json_encode(array(
        'error' => false,
        'message' => "Order #$order_id placed successfully!",
        'order_id' => $order_id,
        'payment_method' => $payment_method,
        'final_total' => $final_total,
        'assigned_boy' => $assigned_boy_info
    ));
    exit;
}

// ==============================================================
// 6. GET SERVICE METADATA (PARCEL, SHIPPING, PASSENGER, DRIVERS)
// ==============================================================
if ($action == 'get_service_meta') {
    // 1. Parcel Settings
    $db->sql("SELECT * FROM parcel_settings ORDER BY id ASC LIMIT 1");
    $ps_res = $db->getResult();
    $parcel_settings = !empty($ps_res) ? $ps_res[0] : array();

    // 2. Parcel Item Types
    $db->sql("SELECT id, name FROM parcel_item_types WHERE status = 1 ORDER BY name ASC");
    $item_types = $db->getResult();
    if (!is_array($item_types)) $item_types = array();

    // 3. Shipping Vehicles
    $db->sql("SELECT id, name, capacity_label, base_price, base_km, per_km_price, helper_price, image FROM shipping_vehicles WHERE status = 1 ORDER BY sort_order ASC, id ASC");
    $vehicles = $db->getResult();
    if (!is_array($vehicles)) $vehicles = array();

    // 4. Shipping Goods Types
    $db->sql("SELECT id, name FROM shipping_goods_types WHERE status = 1 ORDER BY name ASC");
    $goods_types = $db->getResult();
    if (!is_array($goods_types)) $goods_types = array();

    // 5. Passenger Pricing
    $db->sql("SELECT id, vehicle_type, display_name, base_price, base_km, per_km_price, platform_fee, convenience_fee, convenience_fee_type, gst_percent FROM service_pricing WHERE service_type = 'passenger' AND status = 1 ORDER BY id ASC");
    $passenger_pricing = $db->getResult();
    if (!is_array($passenger_pricing)) $passenger_pricing = array();

    // 6. Active Delivery Boys (for manual direct assignment)
    $db->sql("SELECT id, name, mobile, service_type, vehicle_type, zone_id, active_status FROM delivery_boys WHERE status = 1 ORDER BY name ASC");
    $delivery_boys = $db->getResult();
    if (!is_array($delivery_boys)) $delivery_boys = array();

    // 7. Active Zones
    $db->sql("SELECT id, name, polygon FROM zone WHERE status = 1 ORDER BY name ASC");
    $zones = $db->getResult();
    if (!is_array($zones)) $zones = array();

    echo json_encode(array(
        'error' => false,
        'parcel_settings' => $parcel_settings,
        'parcel_item_types' => $item_types,
        'shipping_vehicles' => $vehicles,
        'shipping_goods_types' => $goods_types,
        'passenger_pricing' => $passenger_pricing,
        'delivery_boys' => $delivery_boys,
        'zones' => $zones
    ));
    exit;
}

// ==============================================================
// 7. CALCULATE SERVICE FARE (PARCEL, SHIPPING, PASSENGER)
// ==============================================================
if ($action == 'calculate_service_fare') {
    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : 'parcel';
    $pickup_lat = (isset($_POST['pickup_lat']) && $_POST['pickup_lat'] !== '') ? (float)$_POST['pickup_lat'] : ((isset($_POST['pickup_latitude']) && $_POST['pickup_latitude'] !== '') ? (float)$_POST['pickup_latitude'] : 0.0);
    $pickup_lng = (isset($_POST['pickup_lng']) && $_POST['pickup_lng'] !== '') ? (float)$_POST['pickup_lng'] : ((isset($_POST['pickup_longitude']) && $_POST['pickup_longitude'] !== '') ? (float)$_POST['pickup_longitude'] : 0.0);
    $drop_lat = (isset($_POST['drop_lat']) && $_POST['drop_lat'] !== '') ? (float)$_POST['drop_lat'] : ((isset($_POST['drop_latitude']) && $_POST['drop_latitude'] !== '') ? (float)$_POST['drop_latitude'] : 0.0);
    $drop_lng = (isset($_POST['drop_lng']) && $_POST['drop_lng'] !== '') ? (float)$_POST['drop_lng'] : ((isset($_POST['drop_longitude']) && $_POST['drop_longitude'] !== '') ? (float)$_POST['drop_longitude'] : 0.0);
    $shipping_vehicle_id = isset($_POST['shipping_vehicle_id']) ? (int)$_POST['shipping_vehicle_id'] : 0;
    $helper_count = isset($_POST['helper_count']) ? max(0, (int)$_POST['helper_count']) : 0;
    $passenger_vehicle = isset($_POST['passenger_vehicle']) ? trim($_POST['passenger_vehicle']) : 'bike';
    $custom_tariff = isset($_POST['custom_tariff']) ? trim($_POST['custom_tariff']) : '';
    $manual_tariff = isset($_POST['manual_tariff']) && ($_POST['manual_tariff'] == '1' || $_POST['manual_tariff'] === 'true');

    // If coordinates are missing or zero, resolve fallback from store location
    if ($pickup_lat == 0 || $pickup_lng == 0) {
        $store_lat = isset($settings['store_lattitude']) && !empty($settings['store_lattitude']) ? (float)$settings['store_lattitude'] : 8.701978;
        $store_lng = isset($settings['store_longitude']) && !empty($settings['store_longitude']) ? (float)$settings['store_longitude'] : 77.4195713;
        $pickup_lat = $store_lat;
        $pickup_lng = $store_lng;
    }

    if ($drop_lat == 0 || $drop_lng == 0) {
        $drop_lat = $pickup_lat + 0.025;
        $drop_lng = $pickup_lng + 0.025;
    }

    // Road distance
    $road = $function->get_road_distance_km($pickup_lat, $pickup_lng, $drop_lat, $drop_lng);
    $distance_km = max(0.1, round((float)$road['km'], 2));
    $distance_source = isset($road['source']) ? $road['source'] : 'routes';

    // Zone detection
    $pickup_zone = $function->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
    if ($pickup_zone === null) {
        $db->sql("SELECT id FROM zone WHERE status = 1 ORDER BY id ASC LIMIT 1");
        $fallback_z = $db->getResult();
        if (!empty($fallback_z)) {
            $pickup_zone = (int)$fallback_z[0]['id'];
        }
    }

    $base_price = 0.0;
    $base_km = 1.0;
    $per_km_price = 0.0;
    $helper_charge = 0.0;
    $helper_unit_price = 0.0;
    $tariff = 0.0;

    if ($service_type == 'shipping') {
        $sv_res = array();
        if ($shipping_vehicle_id > 0) {
            $db->sql("SELECT base_price, base_km, per_km_price, helper_price FROM shipping_vehicles WHERE id = $shipping_vehicle_id LIMIT 1");
            $sv_res = $db->getResult();
        }
        if (empty($sv_res)) {
            $db->sql("SELECT base_price, base_km, per_km_price, helper_price FROM shipping_vehicles WHERE status = 1 ORDER BY sort_order ASC LIMIT 1");
            $sv_res = $db->getResult();
        }
        if (!empty($sv_res)) {
            $base_price = (float)$sv_res[0]['base_price'];
            $base_km = max(0.0, (float)$sv_res[0]['base_km']);
            $per_km_price = (float)$sv_res[0]['per_km_price'];
            $helper_unit_price = (float)$sv_res[0]['helper_price'];
        } else {
            $base_price = 150.0;
            $base_km = 2.0;
            $per_km_price = 25.0;
            $helper_unit_price = 100.0;
        }
        $billable_km = max(0.0, $distance_km - $base_km);
        $km_charge = round($billable_km * $per_km_price, 2);
        $helper_charge = round($helper_count * $helper_unit_price, 2);
        $tariff = round($base_price + $km_charge + $helper_charge, 2);

    } else if ($service_type == 'passenger') {
        $vtype = !empty($passenger_vehicle) ? $passenger_vehicle : 'bike';
        $db->sql("SELECT base_price, base_km, per_km_price FROM service_pricing WHERE service_type = 'passenger' AND vehicle_type = '$vtype' LIMIT 1");
        $p_res = $db->getResult();
        if (!empty($p_res)) {
            $base_price = (float)$p_res[0]['base_price'];
            $base_km = max(0.0, (float)$p_res[0]['base_km']);
            $per_km_price = (float)$p_res[0]['per_km_price'];
        } else {
            $base_price = 25.0;
            $base_km = 1.0;
            $per_km_price = 8.0;
        }
        $billable_km = max(0.0, $distance_km - $base_km);
        $km_charge = round($billable_km * $per_km_price, 2);
        $tariff = round($base_price + $km_charge, 2);

    } else {
        // Standard parcel
        $effective_parcel = $function->get_effective_parcel_settings($pickup_zone);
        $base_price = (float)$effective_parcel['base_price'];
        $base_km = 1.0;
        $per_km_price = (float)$effective_parcel['per_km_price'];
        $km_charge = ($distance_km <= 1.0) ? 0.0 : round($distance_km * $per_km_price, 2);
        $tariff = round($base_price + $km_charge, 2);
    }

    // Manual tariff override
    if ($manual_tariff && $custom_tariff !== '' && is_numeric($custom_tariff)) {
        $tariff = (float)$custom_tariff;
    }

    // Zone fees (Platform fee, Convenience fee, GST)
    $sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
    $db->sql($sql_settings);
    $settings_res = $db->getResult();
    $sys_settings = array();
    if (!empty($settings_res)) {
        $sys_settings = json_decode($settings_res[0]['value'], true);
        if (!is_array($sys_settings)) $sys_settings = array();
    }
    $zone_fees = $function->split_zone_fees($tariff, $pickup_zone, $sys_settings);
    $platform_fee = $zone_fees['platform_fee'];
    $convenience_fee = $zone_fees['convenience_fee'];
    $gst = $zone_fees['gst'];
    $grand_total = $zone_fees['grand_total'];

    $zone_name = '';
    if ($pickup_zone > 0) {
        $db->sql("SELECT name FROM zone WHERE id = $pickup_zone LIMIT 1");
        $zn_res = $db->getResult();
        if (!empty($zn_res)) {
            $zone_name = $zn_res[0]['name'];
        }
    }

    echo json_encode(array(
        'error' => false,
        'service_type' => $service_type,
        'distance_km' => $distance_km,
        'distance_str' => $distance_km . ' km',
        'distance_source' => $distance_source,
        'base_price' => round($base_price, 2),
        'base_km' => round($base_km, 2),
        'per_km_price' => round($per_km_price, 2),
        'distance_charge' => round($km_charge, 2),
        'helper_charge' => round($helper_charge, 2),
        'tariff' => round($tariff, 2),
        'platform_fee' => round($platform_fee, 2),
        'convenience_fee' => round($convenience_fee, 2),
        'tax_amount' => round($gst, 2),
        'final_total' => round($grand_total, 2),
        'grand_total' => round($grand_total, 2),
        'zone_id' => $pickup_zone,
        'zone_name' => $zone_name
    ));
    exit;
}

// ==============================================================
// 8. PLACE ADMIN SERVICE ORDER (PARCEL, SHIPPING, PASSENGER)
// ==============================================================
if ($action == 'place_admin_service_order') {
    $service_type = isset($_POST['service_type']) ? trim($_POST['service_type']) : 'parcel';
    if (!in_array($service_type, array('parcel', 'shipping', 'passenger'))) {
        $service_type = 'parcel';
    }

    $sender_name = isset($_POST['sender_name']) ? trim($_POST['sender_name']) : (isset($_POST['customer_name']) ? trim($_POST['customer_name']) : '');
    $sender_phone = isset($_POST['sender_phone']) ? trim($_POST['sender_phone']) : (isset($_POST['customer_mobile']) ? trim($_POST['customer_mobile']) : '');
    $pickup_location = isset($_POST['pickup_location']) ? trim($_POST['pickup_location']) : (isset($_POST['pickup_address']) ? trim($_POST['pickup_address']) : '');
    $pickup_lat = isset($_POST['pickup_lat']) ? trim($_POST['pickup_lat']) : (isset($_POST['pickup_latitude']) ? trim($_POST['pickup_latitude']) : '');
    $pickup_lng = isset($_POST['pickup_lng']) ? trim($_POST['pickup_lng']) : (isset($_POST['pickup_longitude']) ? trim($_POST['pickup_longitude']) : '');
    $pickup_time = isset($_POST['pickup_time']) ? trim($_POST['pickup_time']) : 'Immediate / Now';
    $drop_location = isset($_POST['drop_location']) ? trim($_POST['drop_location']) : (isset($_POST['drop_address']) ? trim($_POST['drop_address']) : '');
    $drop_lat = isset($_POST['drop_lat']) ? trim($_POST['drop_lat']) : (isset($_POST['drop_latitude']) ? trim($_POST['drop_latitude']) : '');
    $drop_lng = isset($_POST['drop_lng']) ? trim($_POST['drop_lng']) : (isset($_POST['drop_longitude']) ? trim($_POST['drop_longitude']) : '');
    $recipient_name = isset($_POST['recipient_name']) ? trim($_POST['recipient_name']) : '';
    $recipient_phone = isset($_POST['recipient_phone']) ? trim($_POST['recipient_phone']) : '';

    $item_type_id = isset($_POST['item_type_id']) ? (int)$_POST['item_type_id'] : 0;
    $item_type_name = isset($_POST['item_type_name']) ? trim($_POST['item_type_name']) : '';
    $weight_kg = isset($_POST['weight_kg']) ? trim($_POST['weight_kg']) : (isset($_POST['weight']) ? trim($_POST['weight']) : '1');

    $shipping_vehicle_id = isset($_POST['shipping_vehicle_id']) ? (int)$_POST['shipping_vehicle_id'] : null;
    $shipping_vehicle_name = isset($_POST['shipping_vehicle_name']) ? trim($_POST['shipping_vehicle_name']) : null;
    if ($shipping_vehicle_id > 0 && empty($shipping_vehicle_name)) {
        $db->sql("SELECT name FROM shipping_vehicles WHERE id = $shipping_vehicle_id LIMIT 1");
        $svn_res = $db->getResult();
        if (!empty($svn_res)) {
            $shipping_vehicle_name = $svn_res[0]['name'];
        }
    }
    $goods_type = isset($_POST['goods_type']) ? trim($_POST['goods_type']) : null;
    $helper_count = isset($_POST['helper_count']) ? max(0, (int)$_POST['helper_count']) : 0;
    $helper_charge = isset($_POST['helper_charge']) ? (float)$_POST['helper_charge'] : 0.0;

    $passenger_vehicle = isset($_POST['passenger_vehicle']) ? trim($_POST['passenger_vehicle']) : 'bike';
    $passenger_count = isset($_POST['passenger_count']) ? max(1, (int)$_POST['passenger_count']) : 1;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    $assign_delivery_boy_id = isset($_POST['delivery_boy_id']) ? (int)$_POST['delivery_boy_id'] : 0;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Paid to Admin';
    $payment_status = ($payment_method === 'Paid to Admin') ? 'paid' : (isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'pending');

    $zone_id = isset($_POST['zone_id']) ? (int)$_POST['zone_id'] : 0;

    // Basic Validations
    if (empty($sender_name)) {
        echo json_encode(array('error' => true, 'message' => 'Sender / Customer name is required!'));
        exit;
    }
    if (empty($sender_phone) || strlen($sender_phone) < 10) {
        echo json_encode(array('error' => true, 'message' => 'Valid 10-digit customer phone number is required!'));
        exit;
    }
    if (empty($pickup_location)) {
        echo json_encode(array('error' => true, 'message' => 'Pickup location address is required!'));
        exit;
    }
    if (empty($drop_location)) {
        echo json_encode(array('error' => true, 'message' => 'Drop location address is required!'));
        exit;
    }

    if (empty($pickup_lat) || empty($pickup_lng) || (float)$pickup_lat == 0) {
        $store_lat = isset($settings['store_lattitude']) && !empty($settings['store_lattitude']) ? (float)$settings['store_lattitude'] : 8.701978;
        $store_lng = isset($settings['store_longitude']) && !empty($settings['store_longitude']) ? (float)$settings['store_longitude'] : 77.4195713;
        $pickup_lat = $store_lat;
        $pickup_lng = $store_lng;
    }
    if (empty($drop_lat) || empty($drop_lng) || (float)$drop_lat == 0) {
        $drop_lat = (float)$pickup_lat + 0.025;
        $drop_lng = (float)$pickup_lng + 0.025;
    }

    if ($service_type == 'passenger') {
        if (empty($recipient_name)) {
            $recipient_name = "Passenger ($sender_name)";
        }
        if (empty($recipient_phone)) {
            $recipient_phone = $sender_phone;
        }
        if (empty($item_type_name)) {
            $item_type_name = "Passenger Ride (" . ucfirst($passenger_vehicle) . ")";
        }
        if (!empty($notes)) {
            $pickup_location = "$pickup_location (Note: $notes)";
        }
    } else {
        if (empty($recipient_name)) {
            echo json_encode(array('error' => true, 'message' => 'Recipient name is required!'));
            exit;
        }
        if (empty($recipient_phone) || strlen($recipient_phone) < 10) {
            echo json_encode(array('error' => true, 'message' => 'Valid 10-digit recipient phone number is required!'));
            exit;
        }
        if ($service_type == 'parcel' && empty($item_type_name)) {
            echo json_encode(array('error' => true, 'message' => 'Please select or enter an Item Type!'));
            exit;
        }
        if ($service_type == 'shipping' && empty($shipping_vehicle_name)) {
            echo json_encode(array('error' => true, 'message' => 'Please select a Shipping Vehicle!'));
            exit;
        }
    }

    // 1. Resolve or Create User
    $clean_mobile = $db->escapeString($function->xss_clean($sender_phone));
    $db->sql("SELECT id, name, mobile, address FROM users WHERE mobile = '$clean_mobile' LIMIT 1");
    $user_res = $db->getResult();
    if (!empty($user_res)) {
        $user_id = (int)$user_res[0]['id'];
        $up_data = array();
        if (!empty($sender_name)) {
            $up_data['name'] = $db->escapeString($function->xss_clean($sender_name));
        }
        if (!empty($up_data)) {
            $db->update('users', $up_data, "id = $user_id");
        }
    } else {
        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $referral_code = "";
        for ($i = 0; $i < 10; $i++) {
            $referral_code .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        $new_user_data = array(
            'name' => $db->escapeString($function->xss_clean($sender_name)),
            'email' => '',
            'password' => '',
            'dob' => '',
            'city' => 0,
            'area' => 0,
            'street' => '',
            'address' => $db->escapeString($function->xss_clean($pickup_location)),
            'pincode' => '',
            'friends_code' => '',
            'mobile' => $clean_mobile,
            'country_code' => '+91',
            'fcm_id' => '',
            'apikey' => '',
            'referral_code' => $referral_code,
            'otp' => '000000',
            'latitude' => $db->escapeString($pickup_lat),
            'longitude' => $db->escapeString($pickup_lng),
            'is_logged_in' => 1,
            'status' => 1
        );
        $db->insert('users', $new_user_data);
        $ins_res = $db->getResult();
        $user_id = (int)$ins_res[0];
    }

    // 2. Zone Detection if missing
    if ($zone_id <= 0) {
        $detected_zone = $function->get_zone_id_from_latlng($pickup_lat, $pickup_lng);
        if ($detected_zone !== null) {
            $zone_id = (int)$detected_zone;
        } else {
            $db->sql("SELECT id FROM zone WHERE status = 1 ORDER BY id ASC LIMIT 1");
            $fallback_z = $db->getResult();
            if (!empty($fallback_z)) {
                $zone_id = (int)$fallback_z[0]['id'];
            }
        }
    }

    // 3. Image uploads (if any)
    $uploaded_images = array();
    $file_field = !empty($_FILES['photos']) ? 'photos' : (!empty($_FILES['parcel_images']) ? 'parcel_images' : null);
    if ($file_field !== null) {
        $target_dir = "../upload/parcel/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $files = $_FILES[$file_field];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        for ($i = 0; $i < $count; $i++) {
            $fname = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $err = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            if ($err === 0 && !empty($tmp) && is_uploaded_file($tmp)) {
                $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                if (in_array($ext, $allowed_ext)) {
                    $new_name = "parcel_admin_" . time() . "_" . rand(100, 999) . "." . $ext;
                    if (move_uploaded_file($tmp, $target_dir . $new_name)) {
                        $uploaded_images[] = "upload/parcel/" . $new_name;
                    }
                }
            }
        }
    }
    $parcel_image_str = !empty($uploaded_images) ? implode(',', $uploaded_images) : (isset($_POST['parcel_image']) ? trim($_POST['parcel_image']) : '');

    // 4. Authoritative fare calculation
    $distance_km = isset($_POST['distance_km']) ? (float)$_POST['distance_km'] : 0.0;
    $base_price = isset($_POST['base_price']) ? (float)$_POST['base_price'] : 0.0;
    $per_km_price = isset($_POST['per_km_price']) ? (float)$_POST['per_km_price'] : 0.0;
    $total_price = isset($_POST['total_price']) ? (float)$_POST['total_price'] : 0.0;
    $platform_fee = isset($_POST['platform_fee']) ? (float)$_POST['platform_fee'] : 0.0;
    $convenience_fee = isset($_POST['convenience_fee']) ? (float)$_POST['convenience_fee'] : 0.0;
    $gst = isset($_POST['gst']) ? (float)$_POST['gst'] : 0.0;
    $grand_total = isset($_POST['grand_total']) ? (float)$_POST['grand_total'] : 0.0;

    if ($distance_km <= 0 || $grand_total <= 0) {
        $road = $function->get_road_distance_km($pickup_lat, $pickup_lng, $drop_lat, $drop_lng);
        $distance_km = max(0.1, round((float)$road['km'], 2));

        if ($service_type == 'shipping') {
            $db->sql("SELECT base_price, base_km, per_km_price, helper_price FROM shipping_vehicles WHERE id = $shipping_vehicle_id LIMIT 1");
            $sv_res = $db->getResult();
            $base_price = !empty($sv_res) ? (float)$sv_res[0]['base_price'] : 150.0;
            $base_km = !empty($sv_res) ? (float)$sv_res[0]['base_km'] : 2.0;
            $per_km_price = !empty($sv_res) ? (float)$sv_res[0]['per_km_price'] : 25.0;
            $helper_price = !empty($sv_res) ? (float)$sv_res[0]['helper_price'] : 100.0;
            $helper_charge = $helper_count * $helper_price;
            $billable_km = max(0.0, $distance_km - $base_km);
            $total_price = round($base_price + ($billable_km * $per_km_price) + $helper_charge, 2);
        } else if ($service_type == 'passenger') {
            $vtype = !empty($passenger_vehicle) ? $passenger_vehicle : 'bike';
            $db->sql("SELECT base_price, base_km, per_km_price FROM service_pricing WHERE service_type = 'passenger' AND vehicle_type = '$vtype' LIMIT 1");
            $p_res = $db->getResult();
            $base_price = !empty($p_res) ? (float)$p_res[0]['base_price'] : 25.0;
            $base_km = !empty($p_res) ? (float)$p_res[0]['base_km'] : 1.0;
            $per_km_price = !empty($p_res) ? (float)$p_res[0]['per_km_price'] : 8.0;
            $billable_km = max(0.0, $distance_km - $base_km);
            $total_price = round($base_price + ($billable_km * $per_km_price), 2);
        } else {
            $effective_parcel = $function->get_effective_parcel_settings($zone_id);
            $base_price = (float)$effective_parcel['base_price'];
            $per_km_price = (float)$effective_parcel['per_km_price'];
            $km_charge = ($distance_km <= 1.0) ? 0.0 : round($distance_km * $per_km_price, 2);
            $total_price = round($base_price + $km_charge, 2);
        }

        $sql_settings = "SELECT value FROM settings WHERE variable = 'system_timezone'";
        $db->sql($sql_settings);
        $settings_res = $db->getResult();
        $sys_settings = !empty($settings_res) ? json_decode($settings_res[0]['value'], true) : array();
        $zone_fees = $function->split_zone_fees($total_price, $zone_id, $sys_settings);
        $platform_fee = $zone_fees['platform_fee'];
        $convenience_fee = $zone_fees['convenience_fee'];
        $gst = $zone_fees['gst'];
        $grand_total = $zone_fees['grand_total'];
    }

    $otp = str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $sys_settings = $function->get_settings('system_timezone', true);
    $request_db_commission = isset($sys_settings['delivery_boy_commission']) && $sys_settings['delivery_boy_commission'] !== '' ? floatval($sys_settings['delivery_boy_commission']) : 5.0;

    if ($assign_delivery_boy_id > 0) {
        $status = ($service_type == 'passenger') ? 'accepted' : 'confirmed';
        $accept_status = 'accepted';
    } else {
        $status = ($service_type == 'passenger') ? 'searching' : 'pending';
        $accept_status = '';
    }

    $insert_data = array(
        'user_id' => $user_id,
        'item_type_id' => $item_type_id,
        'item_type_name' => $db->escapeString($function->xss_clean($item_type_name)),
        'parcel_image' => $db->escapeString($parcel_image_str),
        'weight_kg' => $db->escapeString($weight_kg),
        'distance_km' => $distance_km,
        'per_km_price' => $per_km_price,
        'base_price' => $base_price,
        'total_price' => $total_price,
        'platform_fee' => $platform_fee,
        'convenience_fee' => $convenience_fee,
        'delivery_boy_commission' => $request_db_commission,
        'gst' => $gst,
        'grand_total' => $grand_total,
        'payment_status' => $payment_status,
        'driver_payment_status' => ($payment_status == 'paid') ? 'collected' : 'pending',
        'payment_method' => $db->escapeString($payment_method),
        'payment_id' => ($payment_status == 'paid') ? 'ADMIN_COLLECTED' : '',
        'pickup_location' => $db->escapeString($function->xss_clean($pickup_location)),
        'pickup_lat' => $db->escapeString($pickup_lat),
        'pickup_lng' => $db->escapeString($pickup_lng),
        'sender_name' => $db->escapeString($function->xss_clean($sender_name)),
        'sender_phone' => $db->escapeString($sender_phone),
        'pickup_time' => $db->escapeString($pickup_time),
        'drop_location' => $db->escapeString($function->xss_clean($drop_location)),
        'drop_lat' => $db->escapeString($drop_lat),
        'drop_lng' => $db->escapeString($drop_lng),
        'recipient_name' => $db->escapeString($function->xss_clean($recipient_name)),
        'recipient_phone' => $db->escapeString($recipient_phone),
        'service_type' => $service_type,
        'shipping_vehicle_id' => $shipping_vehicle_id,
        'shipping_vehicle_name' => $shipping_vehicle_name ? $db->escapeString($shipping_vehicle_name) : null,
        'goods_type' => $goods_type ? $db->escapeString($goods_type) : null,
        'helper_count' => $helper_count,
        'helper_charge' => $helper_charge,
        'passenger_vehicle' => $passenger_vehicle ? $db->escapeString($passenger_vehicle) : null,
        'passenger_count' => $passenger_count,
        'delivery_boy_id' => $assign_delivery_boy_id,
        'accept_status' => $accept_status,
        'status' => $status,
        'otp' => $otp,
        'zone_id' => $zone_id,
        'created_at' => date('Y-m-d H:i:s'),
        'dispatch_expires_at' => ($status == 'searching') ? date('Y-m-d H:i:s', time() + 60) : null
    );

    $db->insert('parcel_requests', $insert_data);
    $ins_res = $db->getResult();
    $order_id = !empty($ins_res) ? (int)$ins_res[0] : 0;

    if ($order_id <= 0) {
        echo json_encode(array('error' => true, 'message' => 'Failed to create order request. Please try again.'));
        exit;
    }

    // Record Transaction if Paid to Admin
    if ($payment_status == 'paid') {
        $txn_data = array(
            'user_id' => $user_id,
            'order_id' => $order_id,
            'type' => $payment_method,
            'txn_id' => 'ADMIN_COLLECTED_' . $order_id,
            'amount' => $grand_total,
            'status' => 'success',
            'message' => ucfirst($service_type) . ' order paid to admin',
            'transaction_date' => date('Y-m-d H:i:s')
        );
        $db->insert('transactions', $txn_data);
    }

    // Notifications & Dispatch
    if ($assign_delivery_boy_id > 0) {
        $title = "New " . ucfirst($service_type) . " Assigned by Admin";
        $body = "Order #$order_id has been assigned to you. Pickup: " . mb_substr($pickup_location, 0, 40) . "... Total: Rs. " . number_format($grand_total, 2);
        try {
            $extra = array(
                'order_id' => (string)$order_id,
                'service_type' => $service_type,
                'pickup_location' => $pickup_location,
                'drop_location' => $drop_location,
                'grand_total' => (string)$grand_total
            );
            $function->send_notification_to_delivery_boy($assign_delivery_boy_id, $title, $body, $service_type . '_assigned', $order_id, $service_type, $extra);
        } catch (Exception $e) {}
    } else {
        if ($service_type == 'passenger') {
            $notif_title = "New " . ucfirst($passenger_vehicle) . " Ride Request!";
            $notif_body = "Pickup: " . mb_substr($pickup_location, 0, 40) . "... | Rs. " . number_format($grand_total, 2) . " | Tap to Accept!";
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $extra_data = array(
                'order_id' => (string)$order_id,
                'sender_name' => $sender_name,
                'pickup_location' => $pickup_location,
                'drop_location' => $drop_location,
                'grand_total' => (string)$grand_total,
                'distance_km' => (string)$distance_km,
                'passenger_vehicle' => $passenger_vehicle
            );
            try {
                $function->send_notification_to_delivery_boy(0, $notif_title, $notif_body, 'passenger_ride', $order_id, 'passenger', $extra_data);
            } catch (Exception $e) {}
        } else {
            $msg = "New $service_type request #$order_id is ready. Open app to view details.";
            try {
                $function->dispatch_new_order($service_type, $order_id, "New " . ucfirst($service_type) . " Order", $msg);
            } catch (Exception $e) {}
        }
    }

    echo json_encode(array(
        'error' => false,
        'message' => ucfirst($service_type) . " Order #$order_id created successfully!",
        'order_id' => $order_id,
        'otp' => $otp,
        'service_type' => $service_type,
        'grand_total' => $grand_total,
        'payment_method' => $payment_method
    ));
    exit;
}

echo json_encode(array('error' => true, 'message' => 'Invalid action'));
exit;

