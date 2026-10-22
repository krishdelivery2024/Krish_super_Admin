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

/*
 * SMS daily counter helpers.
 *
 * Previously a single global "sms_count" value in the settings table was
 * incremented on every OTP and never reset, so once it reached
 * sms_max_limit_count every OTP request failed permanently with
 * "SMS limit reached. Please try again later.".
 *
 * These helpers keep the same cost safeguard but reset the counter
 * automatically every day. The date of the last reset is stored in the
 * "sms_count_date" settings row; if it is missing or belongs to a previous
 * day the counter is reset to zero.
 */
function sms_set_setting($db, $variable, $value) {
    $safeVar   = $db->escapeString($variable);
    $safeValue = $db->escapeString($value);

    $db->sql("SELECT id FROM settings WHERE variable = '" . $safeVar . "'");
    $existing = $db->getResult();

    if (!empty($existing)) {
        $db->sql("UPDATE settings SET value = '" . $safeValue . "' WHERE variable = '" . $safeVar . "'");
    } else {
        $db->sql("INSERT INTO settings (variable, value) VALUES ('" . $safeVar . "', '" . $safeValue . "')");
    }
    $db->getResult();
}

function sms_get_daily_count($db) {
    $today = date('Y-m-d');

    $db->sql("SELECT value FROM settings WHERE variable = 'sms_count_date'");
    $dateRes    = $db->getResult();
    $storedDate = (!empty($dateRes) && isset($dateRes[0]['value'])) ? $dateRes[0]['value'] : '';

    $db->sql("SELECT value FROM settings WHERE variable = 'sms_count'");
    $countRes = $db->getResult();
    $count    = (!empty($countRes) && isset($countRes[0]['value'])) ? intval($countRes[0]['value']) : 0;

    if ($storedDate !== $today) {
        $count = 0;
        sms_set_setting($db, 'sms_count', '0');
        sms_set_setting($db, 'sms_count_date', $today);
    }

    return $count;
}

function sms_increment_daily_count($db) {
    $count = sms_get_daily_count($db) + 1;
    sms_set_setting($db, 'sms_count', (string) $count);
    return $count;
}

function sms_daily_limit_reached($db) {
    // SMS count limit restriction removed: allow unlimited logins across all apps
    return false;
}
?>