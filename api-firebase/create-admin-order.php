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

    $fee_settings = $settings;
    $zone_fees = $function->split_zone_fees($subtotal, $zone_id, $fee_settings);
    $platform_fee = $zone_fees['platform_fee'];
    $convenience_fee = $zone_fees['convenience_fee'];
    $tax_amount = isset($zone_fees['gst']) ? $zone_fees['gst'] : 0.0;

    // Delivery charge resolution
    $delivery_charge = 0.0;
    if ($custom_delivery !== '' && is_numeric($custom_delivery)) {
        $delivery_charge = (float) $custom_delivery;
    } else {
        // Default delivery charge from system settings
        $delivery_charge = isset($settings['delivery_charge']) && is_numeric($settings['delivery_charge']) ? (float) $settings['delivery_charge'] : 45.0;
    }

    $final_total = round($subtotal + $delivery_charge + $platform_fee + $convenience_fee + $tax_amount, 2);

    echo json_encode(array(
        'error' => false,
        'subtotal' => round($subtotal, 2),
        'delivery_charge' => round($delivery_charge, 2),
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

    // 4. Insert Order
    $order_data = array(
        'user_id' => $user_id,
        'seller_id' => $seller_id,
        'store_id' => 0,
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

    echo json_encode(array(
        'error' => false,
        'message' => "Order #$order_id placed successfully!",
        'order_id' => $order_id,
        'payment_method' => $payment_method,
        'final_total' => $final_total
    ));
    exit;
}

echo json_encode(array('error' => true, 'message' => 'Invalid action'));
exit;
