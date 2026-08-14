<?php
session_start();

ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

require_once 'includes/crud.php';
$db_con = new Database();
$db_con->connect();
require_once 'includes/functions.php';

$fnc = new functions;

include_once('includes/custom-functions.php');

$fn = new custom_functions;

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        "error" => true,
        "message" => "<span class='label label-danger'>Not authenticated.</span>"
    ]);
    exit;
}

$permissions = $fn->get_permissions($_SESSION['id']);

$response = array();

function send_response($resp) {
    header('Content-Type: application/json');
    echo json_encode($resp);
    exit;
}

define('NOTIF_IMAGE_MAX_BYTES', 2 * 1024 * 1024);

define('FIREBASE_SERVICE_ACCOUNT_PATH', __DIR__ . '/includes/firebase-service-account.json');

function get_firebase_service_account() {
    if (!file_exists(FIREBASE_SERVICE_ACCOUNT_PATH)) {
        error_log('Firebase service account file not found at: ' . FIREBASE_SERVICE_ACCOUNT_PATH);
        return null;
    }
    $json = file_get_contents(FIREBASE_SERVICE_ACCOUNT_PATH);
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE || empty($data['private_key']) || empty($data['client_email'])) {
        error_log('Firebase service account file is malformed.');
        return null;
    }
    return $data;
}

function get_fcm_access_token($service_account) {
    $cache_file = sys_get_temp_dir() . '/fcm_token_cache.json';

    if (file_exists($cache_file)) {
        $cached = json_decode(file_get_contents($cache_file), true);
        if (!empty($cached['access_token']) && !empty($cached['expires_at']) && $cached['expires_at'] > (time() + 60)) {
            return $cached['access_token'];
        }
    }

    $now = time();
    $header = ['alg' => 'RS256', 'typ' => 'JWT'];
    $claims = [
        'iss'   => $service_account['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'iat'   => $now,
        'exp'   => $now + 3600,
    ];

    $b64 = function ($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    };

    $segments = [$b64(json_encode($header)), $b64(json_encode($claims))];
    $signing_input = implode('.', $segments);

    $private_key = openssl_pkey_get_private($service_account['private_key']);
    if ($private_key === false) {
        error_log('Failed to load Firebase private key: ' . openssl_error_string());
        return null;
    }

    $signature = '';
    $ok = openssl_sign($signing_input, $signature, $private_key, 'sha256WithRSAEncryption');
    if (!$ok) {
        error_log('Failed to sign FCM JWT: ' . openssl_error_string());
        return null;
    }

    $segments[] = $b64($signature);
    $jwt = implode('.', $segments);

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

    if ($resp === false || $http_code !== 200) {
        error_log('FCM token request failed (' . $http_code . '): ' . $resp . ' curl_err=' . $curl_err);
        return null;
    }

    $token_data = json_decode($resp, true);
    if (empty($token_data['access_token'])) {
        error_log('FCM token response missing access_token: ' . $resp);
        return null;
    }

    file_put_contents($cache_file, json_encode([
        'access_token' => $token_data['access_token'],
        'expires_at'   => $now + intval($token_data['expires_in'] ?? 3600),
    ]));

    return $token_data['access_token'];
}


function send_fcm_v1_notification($service_account, $access_token, $target, $title, $body, $image_url = null, $data = []) {
    $project_id = $service_account['project_id'];

    $url = "https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send";

    if (!isset($target['token'])) {
        return [false, 'No valid FCM target specified'];
    }

    $payload_data = array_merge([
        'title' => (string) $title,
        'body'  => (string) $body,
        'image' => (string) ($image_url ?? ''),
    ], $data);

    $payload_data = array_map('strval', $payload_data);

    $message = [
        'token' => $target['token'],
        'data'  => $payload_data,
        'android' => [
            'priority' => 'high',
        ],
        'apns' => [
            'headers' => [
                'apns-priority' => '10',
            ],
            'payload' => [
                'aps' => [
                    'content-available' => 1,
                ],
            ],
        ],
    ];

    $payload = json_encode(['message' => $message]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT    => 15,
    ]);
    $resp = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err = curl_error($ch);
    curl_close($ch);

   
    error_log('FCM send -> token=' . substr($target['token'], 0, 12) . '... http_code=' . $http_code
        . ' payload=' . $payload
        . ' response=' . $resp
        . ($curl_err ? (' curl_err=' . $curl_err) : ''));

    if ($resp === false || $http_code < 200 || $http_code >= 300) {
        return [false, $resp ?: $curl_err];
    }

    return [true, $resp];
}

function get_target_fcm_tokens($db_con, $type, $id) {
    $tokens = [];

    $sql = "SELECT fcm_id FROM `users` WHERE fcm_id IS NOT NULL AND fcm_id != ''";

    $db_con->sql($sql);
    $rows = $db_con->getResult();

    if (!empty($rows)) {
        foreach ($rows as $row) {
            if (!empty($row['fcm_id'])) {
                $tokens[] = $row['fcm_id'];
            }
        }
    }

    return $tokens;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (empty($permissions) || (isset($permissions['send_notification']) && !$permissions['send_notification'])) {
        send_response([
            "error" => true,
            "message" => "<span class='label label-danger'>You do not have permission to send notifications.</span>"
        ]);
    }

    if (isset($_POST['title']) && isset($_POST['message']) && isset($_POST['type'])) {

        if (strlen($_POST['title']) === 0 || strlen($_POST['title']) > 255) {
            send_response([
                "error" => true,
                "message" => "<span class='label label-danger'>Title must be between 1 and 255 characters.</span>"
            ]);
        }
        if (strlen($_POST['message']) === 0 || strlen($_POST['message']) > 2000) {
            send_response([
                "error" => true,
                "message" => "<span class='label label-danger'>Message must be between 1 and 2000 characters.</span>"
            ]);
        }

        $sms_limit_query = "SELECT value FROM settings WHERE variable = 'Fire_Base_Notifications'";
        $db_con->sql($sms_limit_query);
        $sms_result = $db_con->getResult();
        $sms_count = !empty($sms_result) ? intval($sms_result[0]['value']) : 0;
        $sms_max_limit_count = push_notificastion_max_limit_count;

        if ($sms_count >= $sms_max_limit_count) {
            send_response([
                "error" => true,
                "message" => "<span class='label label-danger'>Notification limit reached. Please try again later.</span>"
            ]);
        }

        $title_clean   = $fn->xss_clean($_POST['title']);
        $message_clean = $fn->xss_clean($_POST['message']);
        $type          = $db_con->escapeString($fn->xss_clean($_POST['type']));

        $allowed_types = array('default', 'user', 'category', 'product', 'vendor');
        if (!in_array($type, $allowed_types, true)) {
            send_response([
                "error" => true,
                "message" => "<span class='label label-danger'>Invalid notification type.</span>"
            ]);
        }

        if ($type != 'default') {
            if (!isset($_POST[$type]) || trim($_POST[$type]) === '') {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Missing value for selected type.</span>"
                ]);
            }

            $raw_id = trim($_POST[$type]);
            if (!ctype_digit($raw_id)) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Invalid ID for selected type.</span>"
                ]);
            }
            $id = $raw_id;
        } else {
            $id = "0";
        }

        $title   = $db_con->escapeString($title_clean);
        $message = $db_con->escapeString($message_clean);

        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $url .= $_SERVER['SERVER_NAME'];
        $url .= $_SERVER['REQUEST_URI'];
        $server_url = dirname($url) . '/';

        $include_image = (isset($_POST['include_image']) && $fn->xss_clean($_POST['include_image']) == 'on') ? TRUE : FALSE;
        $full_path = null;
        $image_public_url = null;

        if ($include_image) {

            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Image upload failed.</span>"
                ]);
            }

            if ($_FILES['image']['size'] > NOTIF_IMAGE_MAX_BYTES) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Image exceeds maximum allowed size (2MB).</span>"
                ]);
            }

            $allowedExts = array("gif", "jpeg", "jpg", "png");
            $extensionParts = explode(".", $_FILES["image"]["name"]);
            $extension = strtolower(end($extensionParts));

            $allowedMimeTypes = array(
                'image/gif',
                'image/jpeg',
                'image/png'
            );

            
            $detectedMime = null;
            $imgInfo = @getimagesize($_FILES["image"]["tmp_name"]);
            if ($imgInfo !== false && isset($imgInfo['mime'])) {
                $detectedMime = $imgInfo['mime'];
            }

            if ($detectedMime === null || !in_array($extension, $allowedExts, true) || !in_array($detectedMime, $allowedMimeTypes, true)) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Image type is invalid.</span>"
                ]);
            }

            if (@getimagesize($_FILES["image"]["tmp_name"]) === false) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Uploaded file is not a valid image.</span>"
                ]);
            }

            $target_path = 'upload/notifications/';
            if (!is_dir($target_path)) {
                mkdir($target_path, 0755, true);
            }

            $filename  = uniqid('notif_', true) . '.' . $extension;
            $full_path = $target_path . $filename;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $full_path)) {
                send_response([
                    "error" => true,
                    "message" => "<span class='label label-danger'>Could not save uploaded image.</span>"
                ]);
            }

            $image_public_url = $server_url . $full_path;

            $sql = "INSERT INTO `notifications`(`title`, `message`, `type`, `type_id`, `image`) VALUES 
			('" . $title . "','" . $message . "','" . $type . "','" . $id . "','" . $db_con->escapeString($full_path) . "')";
        } else {
            $sql = "INSERT INTO `notifications`(`title`, `message`, `type`, `type_id`) VALUES 
			('" . $title . "','" . $message . "','" . $type . "','" . $id . "')";
        }

        $db_con->sql($sql);
        $db_con->getResult();

        $service_account = get_firebase_service_account();
        $sent_count = 0;
        $failed_count = 0;
        $skipped_no_token = false;

        if ($service_account !== null) {
            $tokens = get_target_fcm_tokens($db_con, $type, $id);

            if (empty($tokens)) {
                $skipped_no_token = true;
                error_log('FCM push skipped: no fcm_id found for type=' . $type . ' id=' . $id);
            } else {
                $access_token = get_fcm_access_token($service_account);

                if ($access_token === null) {
                    error_log('FCM push aborted: could not obtain access token');
                } else {
                    foreach ($tokens as $device_token) {
                        list($ok, $detail) = send_fcm_v1_notification(
                            $service_account,
                            $access_token,
                            ['token' => $device_token],
                            $title_clean,
                            $message_clean,
                            $image_public_url
                        );
                        if ($ok) {
                            $sent_count++;
                        } else {
                            $failed_count++;
                            error_log('FCM push failed for token ending in ...' . substr($device_token, -8) . ': ' . $detail);
                        }
                    }
                }
            }
        } else {
            error_log('FCM push skipped: service account not loaded');
        }

        $response['error'] = false;
        $response["message"] = "<span class='label label-success'>Notification Sent Successfully!</span>";
        $response['fcm_sent_count'] = $sent_count;
        $response['fcm_failed_count'] = $failed_count;
        $response['fcm_no_token'] = $skipped_no_token;

    } else {
        $response['error'] = true;
        $response['message'] = 'Parameters missing';
    }
} else {
    $response['error'] = true;
    $response['message'] = 'Invalid request';
}

header('Content-Type: application/json');
echo(json_encode($response));