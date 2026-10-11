<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

/**
 * Two-stage dispatch worker.
 *
 * Phase-1 notifications are sent the moment an order becomes delivery-ready
 * (food: seller marks it processed; parcel: request created) and only reach
 * online boys within 4 km of the pickup. This worker is the Phase-2 pump: it
 * scans for orders that have sat in Phase-1 for more than 60 seconds without
 * being accepted and broadcasts them to every online boy. The guarded state
 * transition makes escalation idempotent, so running this on a cron every
 * minute (alongside push-notify-worker.php) is safe.
 *
 * Command line:
 *   php dispatch-worker.php
 */
if (PHP_SAPI === 'cli') {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    $_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
    $_SERVER['HTTPS'] = '';
}

include_once('includes/crud.php');
include_once('includes/custom-functions.php');
include_once('includes/variables.php');

$db = new Database();
$db->connect();
$db->sql("SET NAMES utf8");
$function = new custom_functions();

$config = $function->get_configurations();
if (isset($config['system_timezone']) && isset($config['system_timezone_gmt'])) {
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '" . $config['system_timezone_gmt'] . "'");
} else {
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}

$response = array();
$response['error'] = false;
$response['escalated'] = 0;
$response['message'] = 'No Phase-2 escalations due.';

$wait = (int)custom_functions::DISPATCH_PHASE1_WAIT_SECONDS;
$due = "dispatch_stage = 'phase1'
        AND delivery_boy_id = '0'
        AND dispatch_phase1_at IS NOT NULL
        AND dispatch_phase1_at <= NOW() - INTERVAL $wait SECOND";

// Food orders still waiting to be accepted.
$db->sql("SELECT id FROM orders WHERE $due AND active_status NOT IN ('delivered','cancelled','returned') ORDER BY id ASC");
$due_orders = $db->getResult();
foreach ($due_orders as $row) {
    if ($function->escalate_due_dispatch('food', (int)$row['id'])) {
        $response['escalated']++;
        $response['message'] = "Escalated food order #" . $row['id'] . " to Phase-2.";
    }
}

// Parcel pickups still waiting to be accepted.
$db->sql("SELECT id FROM parcel_requests WHERE $due AND status IN ('pending','accepted') ORDER BY id ASC");
$due_parcels = $db->getResult();
foreach ($due_parcels as $row) {
    if ($function->escalate_due_dispatch('parcel', (int)$row['id'])) {
        $response['escalated']++;
        $response['message'] = "Escalated parcel #" . $row['id'] . " to Phase-2.";
    }
}

echo json_encode($response);