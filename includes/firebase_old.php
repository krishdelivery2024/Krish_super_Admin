<?php 
include_once('crud.php');

class Firebase {
    function __construct(){
        date_default_timezone_set('Asia/Kolkata');
    }

    // public function send($device_token, $message) {
    //     $responses = [];
    //     $allDeviceTokens = array_filter($device_token, function($value) {
    //         return !empty($value);
    //     });
    //     $chunks = array_chunk($allDeviceTokens, 100);
    //     foreach ($chunks as $chunk) {
    //         $response = $this->pusher_notification($chunk, $message);
    //         $responses[] = $response;
    //     }
        
    //     return $response;
    // }
    
    
    public function send($device_tokens, $message)
    {
        $responses = [];
    
        $allDeviceTokens = array_filter($device_tokens, function ($value) {
            return !empty($value);
        });
    
        $chunks = array_chunk($allDeviceTokens, 100);
    
        foreach ($chunks as $chunk) {
            $response = $this->pusher_notification($chunk, $message);
            $responses[] = $response;
        }
    
        return $responses;
    }
    
    
    private function pusher_notification($interests, $message)
    {
        
        $instanceId = PUSHER_INSTANCE_ID;
        $bearerToken = PUSHER_SECRET_KEY;
    
        $url = "https://{$instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$instanceId}/publishes";
    
        // ✅ SAFE DATA EXTRACTION
        $title = $message['data']['title'] ?? '';
        $body  = $message['data']['message'] ?? '';
        $image = $message['data']['image'] ?? '';
    
        // Base payload
        $payload = [
            "interests" => $interests,
            "fcm" => [
                "notification" => [
                    "title" => $title,
                    "body"  => $body
                ]
            ]
        ];
        
    
        // ✅ Add image ONLY if exists
        if (!empty($image)) {
            $payload['fcm']['notification']['image'] = $image;
        }
    
        $ch = curl_init($url);
    
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$bearerToken}"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);
    
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    
        return [
            'status' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }



    
    
    // function sendPushNotification($title, $body, $interest, $imageUrl = null)
    // {
    //     $instanceId = "45642a5a-5177-4a5e-aeaa-024d4f7bcf84";
    //     $bearerToken = "C72538638C469F68E234DFE6EA095E912B0AE8DD9345D5C63AAF65E62C8F46D0";
    
    //     $url = "https://{$instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$instanceId}/publishes";
    
    //     // Base payload
    //     $payload = [
    //         "interests" => [$interest],
    //         "fcm" => [
    //             "notification" => [
    //                 "title" => $title,
    //                 "body"  => $body
    //             ]
    //         ]
    //     ];
    
    //     // Add image only if provided
    //     if (!empty($imageUrl)) {
    //         $payload["fcm"]["notification"]["image"] = $imageUrl;
    //     }
    
    //     $ch = curl_init($url);
    
    //     curl_setopt_array($ch, [
    //         CURLOPT_POST => true,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_HTTPHEADER => [
    //             "Content-Type: application/json",
    //             "Authorization: Bearer {$bearerToken}"
    //         ],
    //         CURLOPT_POSTFIELDS => json_encode($payload)
    //     ]);
    
    //     $response = curl_exec($ch);
    //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    //     if (curl_errno($ch)) {
    //         curl_close($ch);
    //         return [
    //             "success" => false,
    //             "error" => curl_error($ch)
    //         ];
    //     }
    
    //     curl_close($ch);
    
    //     return [
    //         "success" => ($httpCode >= 200 && $httpCode < 300),
    //         "status" => $httpCode,
    //         "response" => json_decode($response, true)
    //     ];
    // }
    
    
    

    // public function pusher_notification($device_token, $message1) {
        
        
    //     $instanceId = PUSHER_INSTANCE_ID;
    //     $secretKey = PUSHER_SECRET_KEY;
       
        
    //     $url = "https://{$instanceId}.pushnotifications.pusher.com/publish_api/v1/instances/{$instanceId}/publishes";
        
    //     $payload = $message1['data'];
    //     $payload["image"] = $payload["image"] ?? "";
    //     $payload["id"] = $payload["id"] ?? "0";

    //     $post_data = [
    //         "interests" => $device_token,
    //         "fcm" => [
    //             "priority" => "high",
    //             "data" => $payload
    //         ],
    //         "apns" => [
    //             "aps" => [
    //                 "alert" => [
    //                     "title" => $payload['title'],
    //                     "body" => $payload['message']
    //                 ]
    //             ]
    //         ]
    //     ];
        
    //     if (!empty($payload['image'])) {
    //         $post_data['fcm']['data']["imageUrl"] = $payload['image'];
    //         $post_data['apns']['aps']['attachment-url'] = $payload['image'];
    //     }
        

    //     $curl = curl_init();
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => json_encode($post_data),
    //         CURLOPT_HTTPHEADER => [
    //             "Content-Type: application/json",
    //             "Authorization: Bearer $secretKey"
    //         ],
    //     ]);

    //     $response = curl_exec($curl);
        
    //     if (curl_errno($curl)) {
    //         curl_close($curl);
    //         return false;
    //     }
        
    //     curl_close($curl);
    //     return $response;
    // }
}
