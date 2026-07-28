<?php 
header('Access-Control-Allow-Origin: *');
    //Function to connect to SMS sending server using HTTP GET
function sendSms($recipients, $messagetext) {
 $curl = curl_init();
 $api_secret = "6ba4cb0bc029ae6bec3a07b0982a786a";
 $sender = "SPIKRT";
 curl_setopt_array($curl, array(
  CURLOPT_URL => "http://sms5.mobidrive.in/api/v2/sms/send",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "access_token=".$api_secret."&message=".$messagetext."&sender=".$sender."&to=".$recipients."&service=T",
  CURLOPT_HTTPHEADER => array(
    "Content-Type: application/x-www-form-urlencoded"
  ),
));
 $response = curl_exec($curl);
    // print_r($response);
 curl_close($curl);
 return $response;
}

function sendSmsCommon($recipients, $messagetext, $template_id){
  $messagetext = urlencode( $messagetext );
  $url = "http://sms.spiderindia.com/api/smsapi?key=76a4a331953994b26514dbee1a9b275c&route=2&sender=INSTNE&number=".$recipients."&templateid=".$template_id."&sms=".$messagetext;
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
      'Cookie: ci_session=viia3405mloi1n822d0fm99i4sucl3rq'
    ),
  ));

  $response = curl_exec($curl);

  curl_close($curl);
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