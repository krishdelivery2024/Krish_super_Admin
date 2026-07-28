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
            $url = $this->turl."v2/gateway/orders/";
            $env = "test";
            $redirect=$this->turl."integrations/android/redirect/";
        }else{
            $url = $this->purl."v2/gateway/orders/";
            $env = "production";
            $redirect=$this->purl."integrations/android/redirect/";
        }
        //print_r($this->getToken());exit;
		$curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$header=array("Authorization:Bearer ".$this->getToken(),
               "Content-Type:application/json");
                $headers = [
                    'Authorization:Bearer '.$this->getToken(),
                    'Content-Type:application/json'
                ]; 
        //return $header;die;
		curl_setopt($curl, CURLOPT_HTTPHEADER,$headers);
        $atr=array(
            'currency' => $currency,
            'name' => $name,
            'email' => $email,
            'phone' => $mobile,
            'amount' => $amount,
            'transaction_id'=>$trasaction_id,
            'redirect_url'=>$redirect
        );
        
        curl_setopt($curl, CURLOPT_POST, count($atr));
        curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($atr));
        $json = json_decode(curl_exec($curl));
       // return curl_exec($curl); die;
        if(curl_error($curl))
        {
            echo 'error:' . curl_error($curl);
        }
        if (isset($json->error)) {
            return "Error: " . $json->error;
            throw new \Exception("Error: " . $json->error);
        }
        return $json;
	}
}

$instamojo = new Instamojo("test_m1IXhK1labDsg8tnjJYRq4jPLwJHPWGJufQ", "test_8BCaHVPlf8JFLHPr1NNi76B9nXvWYDcpo2JagPruflqo6Pv3qIVACxZvbWczDTqvsZFoEpKYyvE027hGUPxALsJUNT6VIc3Ftq0QJnEgcdnVKXowfevmR2vCdbA");

//echo $instamojo->getToken();
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