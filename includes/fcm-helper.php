<?php

if (!defined('FIREBASE_SERVICE_ACCOUNT_PATH')) {
    define('FIREBASE_SERVICE_ACCOUNT_PATH', __DIR__ . '/firebase-service-account.json');
}

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
    if ($service_account === null) {
        return null;
    }

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
    if ($access_token === null || empty($target['token'])) {
        return [false, 'Missing access token or device token'];
    }

    $project_id = $service_account['project_id'];
    $url = "https://fcm.googleapis.com/v1/projects/{$project_id}/messages:send";

    
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


function fcm_push_to_table($db_con, $table, $id, $title, $body, $service_account, $access_token, $image_url = null, $data = [], $id_column = 'id', $token_column = 'fcm_id') {
    $safe_table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    $safe_id_column = preg_replace('/[^a-zA-Z0-9_]/', '', $id_column);
    $safe_token_column = preg_replace('/[^a-zA-Z0-9_]/', '', $token_column);
    $safe_id = intval($id);

    if ($service_account === null || $access_token === null) {
        return [false, 'Firebase not configured'];
    }

    $sql = "SELECT `{$safe_token_column}` AS fcm_token FROM `{$safe_table}` WHERE `{$safe_id_column}` = {$safe_id} AND `{$safe_token_column}` IS NOT NULL AND `{$safe_token_column}` != '' LIMIT 1";
    $db_con->sql($sql);
    $rows = $db_con->getResult();

    if (empty($rows) || empty($rows[0]['fcm_token'])) {
        error_log("FCM push skipped: no {$safe_token_column} found in {$safe_table} for {$safe_id_column}={$safe_id}");
        return [false, 'No FCM token on file'];
    }

    return send_fcm_v1_notification(
        $service_account,
        $access_token,
        ['token' => $rows[0]['fcm_token']],
        $title,
        $body,
        $image_url,
        $data
    );
}