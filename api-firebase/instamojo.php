<?php

class Instamojo
{
    private $client_id;
    private $client_secret;
    private $url='';
    private $purl = "https://api.instamojo.com/";
    private $turl = "https://test.instamojo.com/";
    private $env = "production";
	
    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
    }

    public function getToken() {
        if (substr( $this->client_id, 0, 5 ) === "test_") {
            $this->url = $this->turl."oauth2/token/";
            $this->env = "test";
        }else{
            $this->url = $this->purl."oauth2/token/";
            $this->env = "production";
        }
        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, rawurldecode(http_build_query(array(
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'client_credentials'
        ))));
        $json = json_decode(curl_exec($curl));
        if(curl_error($curl))
        {
            echo 'error:' . curl_error($curl);
        }
        if (isset($json->error)) {
            return "Error: " . $json->error;
            throw new \Exception("Error: " . $json->error);
        }
        $this->token = $json;
        return $json->access_token;
    }
	
	public function create_order($name,$email,$mobile,$amount){
		$currency="INR";
		$trasaction_id=time().uniqid(mt_rand());
		
		
		
		if (substr( $this->client_id, 0, 5 ) === "test_") {
            $url = $this->turl."v2/payment_requests/";
            $env = "test";
            $redirect=$this->turl."integrations/android/redirect/";
            $order_url=$this->turl."v2/gateway/orders/payment-request/";
        }else{
            $url = $this->purl."v2/payment_requests/";
            $env = "production";
            $redirect=$this->purl."integrations/android/redirect/";
            $order_url=$this->purl."v2/gateway/orders/payment-request/";
           //$order_url=$this->purl."v2/payment_requests/";
        }
      
		$curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
               $token=$this->getToken();
                $headers = [
                    'Authorization:Bearer '.$token,
                    'Content-Type:application/json'
                ]; 
		curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
        $atr=array(
            'purpose' => 'Payment For Order',
            'phone' => $mobile,
            'amount' => $amount,
            'buyer_name' => $name,
            'redirect_url' => $redirect, 
            'send_email' => false,
            'webhook' => DOMAIN_URL.'api-firebase/return_instamojo_order.php',
            'send_sms' => false,
            'email' => $email,
            'allow_repeated_payments' => false
        );
        
        curl_setopt($curl, CURLOPT_POST, count($atr));
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($atr));
        $json = json_decode(curl_exec($curl));
        if(curl_error($curl))
        {
            echo 'error:' . curl_error($curl);
        }
        if (isset($json->error)) {
            return "Error: " . $json->error;
            throw new \Exception("Error: " . $json->error);
        }
        $payement_request_id=$json->id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $order_url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array('Authorization: Bearer '.$token));
        
        $payload = Array(
            'id' => $payement_request_id
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        $response = curl_exec($ch);
        curl_close($ch); 
        return json_decode($response);
        
	}
}

include '../includes/crud.php';
$db = new Database();
$db->connect();
$sql = "SELECT * FROM settings WHERE variable='payment_methods'";
		$db->sql($sql);
		$res = $db->getResult();
		$payment_methods=$res[0]['value'];
		$payment_method=json_decode($payment_methods);
		$client_key=$payment_method->instamojo_client_id;
		$client_secret=$payment_method->instamojo_client_secret;
		
$instamojo = new Instamojo($client_key, $client_secret);
if(isset($_POST['user_id'])){
    $name=$_POST['name'];
    $email=$_POST['email'];
    $mobile=$_POST['mobile'];
    $amount=$_POST['amount'];
    $order=$instamojo->create_order($name,$email,$mobile,$amount);
}else{
    $order=array("success"=>false,"message"=>"");
}
print_r(json_encode($order));
		
	
?>