<?php

// $instanceId = "4bd38ab5-67ee-4567-9916-d85495ef8c93";
// $secretKey = "75EC2694CFEF70E9F017122475A88697A6D0C683BC912C8DEE10379A8360E6FD";

$instanceId = "5ab3483a-d917-4bfd-a2a1-03a0d2e8ce1d";
$secretKey = "20C4127F60A5A8BA9B39AC1927A7B6970CC6FFC1854E5529E9F4715C14A8EF0E";

$url = "https://" . $instanceId . ".pushnotifications.pusher.com/publish_api/v1/instances/" . $instanceId . "/publishes";

$data = [
    "interests" => [
        "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoiMjIiLCJleHAiOjE3Nzk0NTgyMzl9.Q59xaxAWsRkaeaNfxGxP602aT1XFRmliZmaAW83DIpE"
    ],
    "fcm" => [
        "priority" => "high",
        "data" => [
            "id" => "0",
            "type" => "",
            "image" => "https://spiderekart.in/chopchop/dist/img/logo.jpg",
            "title" => "Aravindan test",
            "message" => "Get premium-quality meat"
        ]
    ]
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n" .
                     "Authorization: Bearer $secretKey\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    die('Error sending notification');
}

echo "Notification sent successfully: " . $response;

?>
