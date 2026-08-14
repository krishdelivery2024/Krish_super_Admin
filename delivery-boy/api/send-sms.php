<?php
header('Access-Control-Allow-Origin: *');


function sendSmsCommon($recipients, $messagetext, $template_id) {

    $api_key   = "8b0db147ea9900ebc6e97aa6b2310b8d";
    $sender_id = "KRISB";
    $route     = "2";

    $encoded_message = urlencode($messagetext);

    $url = "https://site.ping4sms.com/api/smsapi"
        . "?key=" . $api_key
        . "&route=" . $route
        . "&sender=" . $sender_id
        . "&number=" . $recipients
        . "&sms=" . $encoded_message
        . "&templateid=" . $template_id;
        

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_SSL_VERIFYPEER => true,
    ));

    $response  = curl_exec($curl);
    $curl_err  = curl_error($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($curl_err) {
        error_log("[sendSmsCommon] cURL error for {$recipients}: {$curl_err}");
    } else {
        error_log("[sendSmsCommon] HTTP {$http_code} response for {$recipients}: {$response}");
    }

    return $response;
}

function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}
?>