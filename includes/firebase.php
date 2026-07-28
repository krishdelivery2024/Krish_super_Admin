<?php

class Firebase {

    private $project_id;
    private $service_account_file;
    private $token_cache_file;
    private $scope = 'https://www.googleapis.com/auth/firebase.messaging';

    public function __construct() {
        $this->service_account_file = __DIR__ . '/firebase.json';
        $this->token_cache_file     = __DIR__ . '/firebase-token-cache.json';

        if (!file_exists($this->service_account_file)) {
            throw new Exception('Firebase service account file not found at: ' . $this->service_account_file);
        }

        $service_account = json_decode(file_get_contents($this->service_account_file), true);

        if (empty($service_account['project_id'])) {
            throw new Exception('Invalid Firebase service account file: missing project_id');
        }

        $this->project_id = $service_account['project_id'];
    }

    
    private function getAccessToken() {

        if (file_exists($this->token_cache_file)) {
            $cached = json_decode(file_get_contents($this->token_cache_file), true);
            if (!empty($cached['access_token']) && !empty($cached['expires_at']) && $cached['expires_at'] > (time() + 60)) {
                return $cached['access_token'];
            }
        }

        $service_account = json_decode(file_get_contents($this->service_account_file), true);

        $now    = time();
        $expiry = $now + 3600;

        $header = ['alg' => 'RS256', 'typ' => 'JWT'];

        $claim = [
            'iss'   => $service_account['client_email'],
            'scope' => $this->scope,
            'aud'   => 'https://oauth2.googleapis.com/token',
            'exp'   => $expiry,
            'iat'   => $now
        ];

        $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
        $base64UrlClaim  = $this->base64UrlEncode(json_encode($claim));
        $signatureInput  = $base64UrlHeader . '.' . $base64UrlClaim;

        $private_key = openssl_pkey_get_private($service_account['private_key']);
        if ($private_key === false) {
            error_log('FCM: failed to load private key from service account file');
            return null;
        }

        $signature = '';
        openssl_sign($signatureInput, $signature, $private_key, 'sha256WithRSAEncryption');
        $base64UrlSignature = $this->base64UrlEncode($signature);

        $jwt = $signatureInput . '.' . $base64UrlSignature;

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response   = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('FCM OAuth token request failed: ' . $curl_error);
            return null;
        }

        $result = json_decode($response, true);

        if (empty($result['access_token'])) {
            error_log('FCM OAuth token response invalid: ' . $response);
            return null;
        }

        file_put_contents($this->token_cache_file, json_encode([
            'access_token' => $result['access_token'],
            'expires_at'   => $now + intval($result['expires_in'])
        ]));

        return $result['access_token'];
    }

    private function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    
    public function send($devicetoken, $mPushNotification) {

        $access_token = $this->getAccessToken();

        if (empty($access_token)) {
            return ['success' => 0, 'failure' => 0, 'failures' => [], 'error' => 'Could not obtain FCM access token'];
        }

        $tokens = is_array($devicetoken) ? $devicetoken : [$devicetoken];
        $url = 'https://fcm.googleapis.com/v1/projects/' . $this->project_id . '/messages:send';

        $success_count = 0;
        $failure_count = 0;
        $failures = [];

        foreach ($tokens as $token) {

            if (empty($token)) {
                continue;
            }
            $notif_title = $mPushNotification['title'] 
                ?? $mPushNotification['name'] 
                ?? '';
            
            $notif_body = $mPushNotification['body'] 
                ?? $mPushNotification['message'] 
                ?? $mPushNotification['text'] 
                ?? '';
            
            $message = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => (string) $notif_title,
                        'body'  => (string) $notif_body
                    ],
                    'data' => !empty($mPushNotification['data']) ? $mPushNotification['data'] : new stdClass()
                ]
            ];

            if (!empty($mPushNotification['image'])) {
                $message['message']['notification']['image'] = $mPushNotification['image'];
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json; charset=UTF-8',
                'Authorization: Bearer ' . $access_token
            ]);
            $response   = curl_exec($ch);
            $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($http_code == 200) {
                $success_count++;
            } else {
                $failure_count++;
                $failures[] = [
                    'token'    => $token,
                    'response' => $response !== false ? $response : $curl_error
                ];
                error_log('FCM v1 send failed for token ' . substr($token, 0, 12) . '...: ' . ($response !== false ? $response : $curl_error));
            }
        }

        return [
            'success'  => $success_count,
            'failure'  => $failure_count,
            'failures' => $failures
        ];
    }
}