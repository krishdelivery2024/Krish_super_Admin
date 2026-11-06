<?php
/**
 * Razorpay & Order Processing Helper
 * Provides shared logic for order placement, webhook handling, and orphan payment recovery.
 */

if (!function_exists('log_razorpay_webhook')) {
    function log_razorpay_webhook($message) {
        $logPath = __DIR__ . "/razorpay_webhook.log";
        $myfile = @fopen($logPath, "a");
        if ($myfile) {
            fwrite($myfile, date('Y-m-d H:i:s') . " - " . $message . "\n");
            fclose($myfile);
        } else {
            error_log("Could not open razorpay_webhook.log at " . $logPath);
        }
    }
}

if (!function_exists('init_razorpay_orders_table')) {
    function init_razorpay_orders_table($db) {
        static $initialized = false;
        if ($initialized) return true;

        $sql = "CREATE TABLE IF NOT EXISTS `razorpay_orders` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `razorpay_order_id` VARCHAR(100) NOT NULL,
          `user_id` INT DEFAULT 0,
          `seller_id` INT DEFAULT 0,
          `amount` DECIMAL(10,2) NOT NULL,
          `receipt` VARCHAR(100) DEFAULT '',
          `order_payload` LONGTEXT DEFAULT NULL,
          `payment_id` VARCHAR(100) DEFAULT NULL,
          `local_order_id` INT DEFAULT 0,
          `status` VARCHAR(50) DEFAULT 'initiated',
          `notes` TEXT DEFAULT NULL,
          `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
          `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          UNIQUE KEY `idx_rp_order_id` (`razorpay_order_id`),
          KEY `idx_rp_payment_id` (`payment_id`),
          KEY `idx_rp_user_id` (`user_id`),
          KEY `idx_rp_local_order_id` (`local_order_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $db->sql($sql);
        $initialized = true;
        return true;
    }
}

if (!function_exists('execute_order_placement')) {
    /**
     * Executes order placement given a standardized parameters array.
     * Can be invoked either by app POST request or Razorpay Webhook.
     */
    function execute_order_placement($params, $db, $function) {
        init_razorpay_orders_table($db);

        $user_id = isset($params['user_id']) ? $db->escapeString($function->xss_clean($params['user_id'])) : 0;
        $seller_id = isset($params['seller_id']) ? $db->escapeString($function->xss_clean($params['seller_id'])) : 0;
        $mobile = isset($params['mobile']) ? $db->escapeString($function->xss_clean($params['mobile'])) : '';
        $name = isset($params['name']) ? $db->escapeString($function->xss_clean($params['name'])) : '';
        $email = isset($params['email']) ? $db->escapeString($function->xss_clean($params['email'])) : '';

        // Alternate mobile
        $alternate_mobile = isset($params['alternate_mobile']) ? $db->escapeString($function->xss_clean($params['alternate_mobile'])) : '';
        if (empty($alternate_mobile) && !empty($user_id)) {
            $db->sql("SELECT alternate_mobile FROM user_address WHERE user_id = '$user_id' AND alternate_mobile != '' ORDER BY is_default DESC LIMIT 1");
            $alt_res = $db->getResult();
            if (!empty($alt_res) && !empty($alt_res[0]['alternate_mobile'])) {
                $alternate_mobile = $alt_res[0]['alternate_mobile'];
            }
        }

        // Fill user details from users table if empty (e.g. from webhook recovery)
        if ((empty($name) || empty($mobile)) && !empty($user_id)) {
            $db->sql("SELECT name, mobile, email FROM users WHERE id = '$user_id' LIMIT 1");
            $u_res = $db->getResult();
            if (!empty($u_res)) {
                if (empty($name)) $name = $u_res[0]['name'];
                if (empty($mobile)) $mobile = $u_res[0]['mobile'];
                if (empty($email)) $email = $u_res[0]['email'];
            }
        }

        $store_id = (isset($params['store_id']) && is_numeric($params['store_id'])) ? $db->escapeString($function->xss_clean($params['store_id'])) : 0;
        $wallet_balance = (isset($params['wallet_balance']) && is_numeric($params['wallet_balance'])) ? $db->escapeString($function->xss_clean($params['wallet_balance'])) : 0;
        $wallet_used = (isset($params['wallet_used']) && $params['wallet_used'] == 'true') ? 'true' : 'false';

        // Parse items
        $items_raw = isset($params['product_variant_id']) ? $params['product_variant_id'] : '';
        if (is_array($items_raw)) {
            $items = $db->escapeString(json_encode($items_raw));
            $variant_ids_arr = $items_raw;
        } else {
            $items = $db->escapeString(stripslashes($function->xss_clean($items_raw)));
            $variant_ids_arr = json_decode($items_raw, true);
        }

        if (empty($variant_ids_arr)) {
            return array('error' => true, 'order_id' => 0, 'message' => 'Order without items cannot be placed!');
        }

        // Parse quantities
        $qty_raw = isset($params['quantity']) ? $params['quantity'] : '';
        if (is_array($qty_raw)) {
            $quantity_arr = $qty_raw;
        } else {
            $quantity_arr = json_decode($function->xss_clean($qty_raw), true);
        }

        $total = isset($params['total']) ? $db->escapeString($function->xss_clean($params['total'])) : 0;
        $delivery_method = isset($params['delivery_method']) ? $db->escapeString($function->xss_clean($params['delivery_method'])) : '';
        $delivery_charge = isset($params['delivery_charge']) ? $db->escapeString($function->xss_clean($params['delivery_charge'])) : 0;
        $discount = isset($params['discount']) ? $db->escapeString($function->xss_clean($params['discount'])) : 0;
        $tax_amount = (isset($params['tax_amount']) && is_numeric($params['tax_amount'])) ? $db->escapeString($function->xss_clean($params['tax_amount'])) : 0;
        $final_total = isset($params['final_total']) ? $db->escapeString($function->xss_clean($params['final_total'])) : 0;
        $payment_method = isset($params['payment_method']) ? $db->escapeString($function->xss_clean($params['payment_method'])) : 'Online Payment';
        $address = isset($params['address']) ? $db->escapeString($function->xss_clean($params['address'])) : '';
        $delivery_city = (isset($params['delivery_city']) && is_numeric($params['delivery_city'])) ? $db->escapeString($function->xss_clean($params['delivery_city'])) : 0;
        $delivery_state = isset($params['delivery_state']) ? $db->escapeString($function->xss_clean($params['delivery_state'])) : '';
        $delivery_zone = (isset($params['delivery_zone']) && is_numeric($params['delivery_zone'])) ? $db->escapeString($function->xss_clean($params['delivery_zone'])) : 0;
        $gst_no = isset($params['gst_no']) ? $db->escapeString($function->xss_clean($params['gst_no'])) : '';
        $delivery_time = isset($params['delivery_time']) ? $db->escapeString($function->xss_clean($params['delivery_time'])) : '';
        $latitude = isset($params['latitude']) ? $db->escapeString($function->xss_clean($params['latitude'])) : '';
        $longitude = isset($params['longitude']) ? $db->escapeString($function->xss_clean($params['longitude'])) : '';
        $promo_code = (isset($params['promo_code']) && !empty($params['promo_code'])) ? $db->escapeString($function->xss_clean($params['promo_code'])) : '-';
        $promo_discount = (isset($params['promo_discount']) && !empty($params['promo_discount'])) ? $db->escapeString($function->xss_clean($params['promo_discount'])) : 0;
        $notes = isset($params['notes']) ? $db->escapeString($params['notes']) : '';

        // If address is empty, fetch default address for user
        if (empty($address) && !empty($user_id)) {
            $db->sql("SELECT address, latitude, longitude FROM user_address WHERE user_id = '$user_id' ORDER BY is_default DESC LIMIT 1");
            $addr_res = $db->getResult();
            if (!empty($addr_res)) {
                $address = $addr_res[0]['address'];
                if (empty($latitude)) $latitude = $addr_res[0]['latitude'];
                if (empty($longitude)) $longitude = $addr_res[0]['longitude'];
            }
        }

        // Zone detection
        $order_zone_id = null;
        if (!empty($latitude) && !empty($longitude)) {
            $order_zone_id = $function->get_zone_id_from_latlng($latitude, $longitude);
        }

        // Zone fees
        $db->sql("SELECT value FROM `settings` WHERE variable = 'system_timezone'");
        $fee_cfg = $db->getResult();
        $fee_settings = isset($fee_cfg[0]['value']) ? json_decode($fee_cfg[0]['value'], true) : array();

        $order_zone_fees = $function->split_zone_fees((float)$total, $order_zone_id, $fee_settings);
        $order_platform_fee = isset($order_zone_fees['platform_fee']) ? $order_zone_fees['platform_fee'] : 0.0;
        $order_convenience_fee = isset($order_zone_fees['convenience_fee']) ? $order_zone_fees['convenience_fee'] : 0.0;

        // Seller & delivery commissions
        $order_seller_commission = 0.0;
        if (!empty($seller_id)) {
            $db->sql("SELECT commission FROM `seller` WHERE id = '" . (int)$seller_id . "' LIMIT 1");
            $s_comm_res = $db->getResult();
            if (!empty($s_comm_res) && isset($s_comm_res[0]['commission']) && $s_comm_res[0]['commission'] !== '' && floatval($s_comm_res[0]['commission']) > 0) {
                $order_seller_commission = floatval($s_comm_res[0]['commission']);
            } else {
                $order_seller_commission = isset($fee_settings['seller_commission']) && $fee_settings['seller_commission'] !== '' ? floatval($fee_settings['seller_commission']) : 0.0;
            }
        }
        $order_db_commission = isset($fee_settings['delivery_boy_commission']) && $fee_settings['delivery_boy_commission'] !== '' ? floatval($fee_settings['delivery_boy_commission']) : 5.0;

        $item_details = $function->get_product_by_variant_id($items);
        if (empty($item_details)) {
            return array('error' => true, 'order_id' => 0, 'message' => 'Product variant details not found.');
        }

        $status = array();
        $status[] = array('received', date("d-m-Y h:i:sa"));

        $order_data = array(
            'user_id' => $user_id,
            'seller_id' => $seller_id,
            'store_id' => $store_id,
            'mobile' => $mobile,
            'alternate_mobile' => $alternate_mobile,
            'name' => $name,
            'email' => $email,
            'delivery_method' => $delivery_method,
            'delivery_charge' => $delivery_charge,
            'platform_fee' => $order_platform_fee,
            'convenience_fee' => $order_convenience_fee,
            'seller_commission' => $order_seller_commission,
            'delivery_boy_commission' => $order_db_commission,
            'wallet_balance' => ($wallet_used == 'true') ? $wallet_balance : 0,
            'total' => $total,
            'tax_amount' => $tax_amount,
            'final_total' => $final_total,
            'payment_method' => $payment_method,
            'payment_status' => 1,
            'address' => $address,
            'delivery_city' => $delivery_city,
            'delivery_state' => $delivery_state,
            'delivery_zone' => $delivery_zone,
            'zone_id' => $order_zone_id,
            'gst_no' => $gst_no,
            'delivery_time' => $delivery_time,
            'status' => $db->escapeString(json_encode($status)),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'promo_code' => $promo_code,
            'promo_discount' => $promo_discount,
            'discount' => $discount,
            'notes' => $notes,
            'active_status' => 'received',
            'date_added' => date("Y-m-d H:i:s")
        );

        $db->insert('orders', $order_data);
        $insert_res = $db->getResult();
        $order_id = isset($insert_res[0]) ? $insert_res[0] : 0;

        if (!$order_id) {
            return array('error' => true, 'order_id' => 0, 'message' => 'Could not insert order into database.');
        }

        log_razorpay_webhook("Order #$order_id inserted successfully for user #$user_id (seller #$seller_id).");

        // Insert order items
        for ($i = 0; $i < count($item_details); $i++) {
            $product_id = $item_details[$i]['product_id'];
            $measurement = $item_details[$i]['measurement'];
            $product_variant_id = $item_details[$i]['id'];
            $price = $item_details[$i]['price'];
            $discounted_price = $item_details[$i]['discounted_price'];
            $vendor_price = $item_details[$i]['vendor_price'];
            $is_offer = isset($item_details[$i]['is_offer']) ? (int)$item_details[$i]['is_offer'] : 0;
            $offer_price = isset($item_details[$i]['offer_price']) ? (float)$item_details[$i]['offer_price'] : 0;
            $sgstp = isset($item_details[$i]['sgst']) ? $item_details[$i]['sgst'] : 0;
            $cgstp = isset($item_details[$i]['cgst']) ? $item_details[$i]['cgst'] : 0;
            $igstp = isset($item_details[$i]['igst']) ? $item_details[$i]['igst'] : 0;
            $type = isset($item_details[$i]['type']) ? $item_details[$i]['type'] : '';
            $total_stock = isset($item_details[$i]['stock']) ? $item_details[$i]['stock'] : 0;
            $quantity = isset($quantity_arr[$i]) ? $quantity_arr[$i] : 1;

            if ($is_offer == 1 && $offer_price > 0) {
                $unit_price_used = $offer_price;
            } else {
                $unit_price_used = ($discounted_price != 0) ? $discounted_price : $price;
            }
            $sub_total = $unit_price_used * $quantity;

            $item_data = array(
                'user_id' => $user_id,
                'order_id' => $db->escapeString($order_id),
                'product_variant_id' => $db->escapeString($product_variant_id),
                'product_name' => isset($item_details[$i]['name']) ? $db->escapeString($item_details[$i]['name']) : '',
                'measurement' => isset($item_details[$i]['measurement']) ? $db->escapeString($item_details[$i]['measurement']) : '',
                'unit' => isset($item_details[$i]['measurement_unit_name']) ? $db->escapeString($item_details[$i]['measurement_unit_name']) : '',
                'quantity' => $db->escapeString($quantity),
                'price' => $db->escapeString($price),
                'discounted_price' => ($is_offer == 1 && $offer_price > 0) ? $db->escapeString($offer_price) : $db->escapeString($discounted_price),
                'vendor_price' => $db->escapeString($vendor_price),
                'discount' => $discount,
                'sgst' => $sgstp,
                'cgst' => $cgstp,
                'igst' => $igstp,
                'sub_total' => $db->escapeString($sub_total),
                'status' => $db->escapeString(json_encode($status)),
                'active_status' => 'received'
            );
            $db->insert('order_items', $item_data);

            // Stock decrement
            if ($type == 'packet') {
                $stock = $total_stock - $quantity;
                $sql = "UPDATE product_variant SET stock = $stock WHERE id = $product_variant_id";
                $db->sql($sql);
            } elseif ($type == 'loose') {
                $sql = "SELECT id FROM unit WHERE name = '" . $item_details[$i]['measurement_unit_name'] . "'";
                $db->sql($sql);
                $unit = $db->getResult();
                $stock = $quantity;
                if (!empty($unit)) {
                    $stock = $function->convert_to_parent(($measurement * $quantity), $unit[0]['id']);
                }
                $sql = "UPDATE product_variant SET stock = stock - $stock WHERE product_id = $product_id AND type='loose'";
                $db->sql($sql);
            }
        }

        // Clear user cart
        if (!empty($user_id)) {
            $db->sql("DELETE FROM carts WHERE user_id = '$user_id'");
        }

        // Send notifications
        $message = "New order ID : #" . $order_id . " received, please take note of it and proceed further.";
        try {
            $function->send_new_order_notification("New Order Received", $message, 'order');
        } catch (Exception $e) {
            log_razorpay_webhook("Notification exception: " . $e->getMessage());
        } catch (Error $e) {
            log_razorpay_webhook("Notification error: " . $e->getMessage());
        }

        if (!empty($seller_id)) {
            try {
                $function->send_notification_to_seller($seller_id, "New Order Received", $message, 'order', $order_id);
            } catch (Exception $e) {
                log_razorpay_webhook("Seller notification exception: " . $e->getMessage());
            } catch (Error $e) {
                log_razorpay_webhook("Seller notification error: " . $e->getMessage());
            }
        }

        return array(
            'error' => false,
            'order_id' => $order_id,
            'message' => 'Order placed successfully.'
        );
    }
}

if (!function_exists('process_razorpay_captured_payment')) {
    /**
     * Handles captured payment from Razorpay Webhook or manual resolution.
     */
    function process_razorpay_captured_payment($payment_id, $rp_order_id, $amount, $contact = '', $email = '', $notes = array(), $db, $function) {
        init_razorpay_orders_table($db);

        log_razorpay_webhook("Processing captured payment: $payment_id, rp_order: $rp_order_id, amount: $amount");

        // 1. Check if transaction is already recorded
        $db->sql("SELECT id, order_id FROM transactions WHERE txn_id = '$payment_id' LIMIT 1");
        $existing_txn = $db->getResult();
        if (!empty($existing_txn)) {
            log_razorpay_webhook("Payment $payment_id already exists in transactions (ID: " . $existing_txn[0]['id'] . ", Order: " . $existing_txn[0]['order_id'] . ").");
            // If linked to an order, ensure payment_status = 1
            if (!empty($existing_txn[0]['order_id'])) {
                $db->sql("UPDATE orders SET payment_status = 1 WHERE id = '" . (int)$existing_txn[0]['order_id'] . "'");
            }
            return array('status' => 'already_processed', 'order_id' => $existing_txn[0]['order_id']);
        }

        // 2. Check razorpay_orders table for initiated order record
        $rp_record = null;
        if (!empty($rp_order_id)) {
            $db->sql("SELECT * FROM razorpay_orders WHERE razorpay_order_id = '$rp_order_id' LIMIT 1");
            $res = $db->getResult();
            if (!empty($res)) {
                $rp_record = $res[0];
            }
        }

        // Case A: razorpay_orders record found
        if ($rp_record) {
            $local_order_id = (int)$rp_record['local_order_id'];

            // Case A1: App already placed the order, just link the transaction
            if ($local_order_id > 0) {
                $db->sql("UPDATE orders SET payment_status = 1 WHERE id = '$local_order_id'");
                $txn_data = array(
                    'user_id' => $rp_record['user_id'],
                    'order_id' => $local_order_id,
                    'type' => 'razorpay',
                    'txn_id' => $payment_id,
                    'amount' => $amount,
                    'status' => 'success',
                    'message' => 'Payment captured via Razorpay Webhook',
                    'transaction_date' => date('Y-m-d H:i:s')
                );
                $db->insert('transactions', $txn_data);
                $db->sql("UPDATE razorpay_orders SET payment_id = '$payment_id', status = 'order_placed' WHERE id = '" . $rp_record['id'] . "'");
                log_razorpay_webhook("Payment $payment_id linked to existing local order #$local_order_id.");
                return array('status' => 'order_linked', 'order_id' => $local_order_id);
            }

            // Case A2: App crashed/closed, but order_payload was saved! Place order now!
            if (!empty($rp_record['order_payload'])) {
                $payload = json_decode($rp_record['order_payload'], true);
                if (!empty($payload)) {
                    $payload['payment_method'] = 'Online Payment';
                    $place_res = execute_order_placement($payload, $db, $function);
                    if (!$place_res['error'] && $place_res['order_id'] > 0) {
                        $new_order_id = $place_res['order_id'];
                        // Record transaction
                        $txn_data = array(
                            'user_id' => $rp_record['user_id'],
                            'order_id' => $new_order_id,
                            'type' => 'razorpay',
                            'txn_id' => $payment_id,
                            'amount' => $amount,
                            'status' => 'success',
                            'message' => 'Order created automatically via Razorpay Webhook',
                            'transaction_date' => date('Y-m-d H:i:s')
                        );
                        $db->insert('transactions', $txn_data);
                        $db->sql("UPDATE razorpay_orders SET payment_id = '$payment_id', local_order_id = '$new_order_id', status = 'order_placed' WHERE id = '" . $rp_record['id'] . "'");
                        log_razorpay_webhook("Order #$new_order_id placed successfully via webhook payload for payment $payment_id.");
                        return array('status' => 'order_created', 'order_id' => $new_order_id);
                    } else {
                        log_razorpay_webhook("Failed placing order from saved payload: " . $place_res['message']);
                    }
                }
            }
        }

        // Case B: Orphan payment / No saved payload (like pay_TwQMmnBuHG67w4)
        // Find user by phone number or email
        $user_id = 0;
        if (!empty($notes['user_id'])) {
            $user_id = (int)$notes['user_id'];
        }

        if (empty($user_id) && !empty($contact)) {
            $clean_phone = substr(preg_replace('/[^0-9]/', '', $contact), -10);
            if (!empty($clean_phone)) {
                $db->sql("SELECT id FROM users WHERE mobile LIKE '%$clean_phone' LIMIT 1");
                $u_res = $db->getResult();
                if (!empty($u_res)) {
                    $user_id = (int)$u_res[0]['id'];
                }
            }
        }

        if (empty($user_id) && !empty($email)) {
            $db->sql("SELECT id FROM users WHERE email = '" . $db->escapeString($email) . "' LIMIT 1");
            $u_res = $db->getResult();
            if (!empty($u_res)) {
                $user_id = (int)$u_res[0]['id'];
            }
        }

        // Record captured payment in transactions table with order_id = 0
        $txn_data = array(
            'user_id' => $user_id,
            'order_id' => 0,
            'type' => 'razorpay',
            'txn_id' => $payment_id,
            'amount' => $amount,
            'status' => 'success',
            'message' => 'Payment captured via Razorpay (App disconnected before order placement)',
            'transaction_date' => date('Y-m-d H:i:s')
        );
        $db->insert('transactions', $txn_data);

        // Record in razorpay_orders as captured_unlinked
        $rp_id_to_store = !empty($rp_order_id) ? $rp_order_id : ('unlinked_' . $payment_id);
        $notes_json = $db->escapeString(json_encode(array(
            'contact' => $contact,
            'email' => $email,
            'raw_notes' => $notes
        )));

        $db->sql("INSERT INTO razorpay_orders (razorpay_order_id, user_id, amount, payment_id, status, notes)
                  VALUES ('$rp_id_to_store', '$user_id', '$amount', '$payment_id', 'captured_unlinked', '$notes_json')
                  ON DUPLICATE KEY UPDATE payment_id = '$payment_id', status = 'captured_unlinked', notes = '$notes_json'");

        log_razorpay_webhook("Recorded orphan payment $payment_id for user #$user_id as captured_unlinked.");

        return array('status' => 'recorded_unlinked', 'user_id' => $user_id);
    }
}

