<?php
include_once('../includes/crud.php');

class dunzo{
	protected $db;
	protected $client_id;
	protected $client_secret;
	protected $d_url;
	protected $token;
	
    function __construct(){
        $this->db = new Database();
        $this->db->connect();
        date_default_timezone_set('Asia/Kolkata');
        
        $dunzo_data_query=$this->db->sql("SELECT * FROM delivery_method WHERE id='1'");
        $res=$this->db->getResult();
        $this->dunzo_data=$res[0]['dunzo_data'];
        $this->dunz = json_decode($this->dunzo_data);
        $this->client_id=$this->dunz->dunzo_client_id;
        $this->client_secret=$this->dunz->dunzo_secret_key;
        
        $env="live";  // live (or) test
        // $this->client_id="7653d191-0b49-45fe-9d75-22c42e0d0a64";
        // $this->client_secret="f2cbe476-b1ed-417b-95d1-c029d1651306";
       
        $token_data_query=$this->db->sql("SELECT value FROM settings WHERE variable='dunzo_token'");
        $token_data=$this->db->getResult();
        $this->token=$token_data[0]['value'];
        if($env=="live"){
            $this->d_url="https://api.dunzo.in/";
        }else{
            $this->d_url="https://apis-staging.dunzo.in/";
        }
    }
    
    public function call_api($url, $headers,$fields='',$method='get'){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if($method=="post"){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        }else{
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }
        $result = curl_exec($ch);
        $myfile = fopen("dunzo_api.txt", "w");
        fwrite($myfile, "\n".date('d-m-Y h:i A')." - ". $result." - ".json_encode($fields));
        fclose($myfile);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpcode=='401'){
            $this->generate_token();
            return 'retry';die;
        }
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        $result1=json_decode($result);
        if(!$result1->code){
            $log=array(
                'url'=>$url,
                'fields'=>$this->db->escapeString(json_encode($fields)),
                'response'=>$result
                );
                $this->db->insert('dunzo_log',$log);
           // echo "Something Went Wrong";   die;
        }
        return $result1;
    }
    
    public function generate_token(){
        $url=$this->d_url.'api/v1/token';
        $headers=array(
                    "client-id:".$this->client_id,
                    "client-secret:".$this->client_secret,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $token_data=$this->call_api($url,$headers);
        $this->db->sql("UPDATE settings SET value='".$token_data->token."' WHERE variable='dunzo_token'");
        return $token_data->token;
        
    }
    
    public function create_quote($fields){
        $token=$this->token;
        $url=$this->d_url.'api/v2/quote';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        
        $result=$this->call_api($url,$headers,$fields,'post');
        if($result=='retry'){
            $result=$this->call_api($url,$headers,$fields,'post');
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function create_task($fields){
        $token=$this->token;
        $url=$this->d_url.'api/v2/tasks';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        
        $result=$this->call_api($url,$headers,$fields,'post');
        if($result=='retry'){
            $result=$this->call_api($url,$headers,$fields,'post');
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function cancel_task($fields){
        $token=$this->token;
        $url=$this->d_url.'api/v2/cancel-drops';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        
        $result=$this->call_api($url,$headers,$fields,'post');
        if($result=='retry'){
            $result=$this->call_api($url,$headers,$fields,'post');
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function track_status($task_id){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/'.$task_id.'/status';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        
        $result=$this->call_api($url,$headers);
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function cancel_task1($task_id,$canceled_reference_ids){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/'.$task_id.'/cancel_drops';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields=array("canceled_reference_id"=>$canceled_reference_ids);
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function cancel_task2($task_id,$reason){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/'.$task_id.'/_cancel';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields=array("cancellation_reason"=>$reason);
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function raise_support_ticket($task_id,$description,$contact_no){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/'.$task_id.'/support';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields=array("issue_description"=>$description,"contact_number"=>$contact_no);
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function email_report($from_time_epoch,$to_time_epoch,$email_ids){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/email-report';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields=array("from_time_epoch"=>$from_time_epoch,"to_time_epoch"=>$to_time_epoch,"email_ids"=>$email_ids);
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function task_statement($month,$year){
        $token=$this->token;
        $url=$this->d_url.'api/v1/tasks/email-report';
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields=array("month"=>$month,"year"=>$year);
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
    public function create_quote_line($order_id){
        $order_data_query=$this->db->sql("SELECT o.*,(SELECT name FROM users u WHERE u.id=o.user_id) AS name,(SELECT latitude FROM users u WHERE u.id=o.user_id) AS u_latitude,(SELECT longitude FROM users u WHERE u.id=o.user_id) AS u_longitude FROM orders o WHERE o.id=".$order_id);
        $order_data=$this->db->getResult();
        if(!empty($order_data)){
            $order_id=$order_data[0]['id'];
            $pickup_details=array(
                    "lng"=> 80.275927,
                    "lat"=> 13.0861047
            );
            $drop_details=array(
                    "lng"=> (float)$order_data[0]['u_longitude'],
                    "lat"=> (float)$order_data[0]['u_latitude'],
                    "reference_id"=>$order_id
                    );
            $fields=array(
                "pickup_details"=>$pickup_details,
                "optimised_route"=> true,
                "drop_details"=>$drop_details
            );
            return $this->create_quote($fields);
        }
    }
    
    public function create_task_line($order_id){
        $order_data_query=$this->db->sql("SELECT o.*,(SELECT name FROM users u WHERE u.id=o.user_id) AS name,(SELECT latitude FROM users u WHERE u.id=o.user_id) AS u_latitude,(SELECT longitude FROM users u WHERE u.id=o.user_id) AS u_longitude FROM orders o WHERE o.id=".$order_id);
        $order_data=$this->db->getResult();
        if(!empty($order_data)){
            $dunzo_ref_query=$this->db->sql("SELECT value FROM settings WHERE variable='dunzo_reference_id'");
        $dunzo_ref=$this->db->getResult();
        $dunzo_ref_id=$dunzo_ref[0]['value'];
        $this->db->sql("UPDATE settings SET value=".($dunzo_ref_id+1)." WHERE variable='dunzo_reference_id'");
            $order_id=$order_data[0]['id'];
            $pickup_details=array(array(
                "reference_id"=> $dunzo_ref_id,
                "address"=> array(
                    "apartment_address"=> "Viveka Essence Mart,",
                    "street_address_1"=> "Plot No: 128/285, Wall Tax Rd, Edapalaiyam",
                    "street_address_2"=> "Park Town",
                    "landmark"=> "Near Chennai Central Railway Station",
                    "city"=> "Chennai",
                    "state"=> "Tamil Nadu",
                    "pincode"=> "600003",
                    "country"=> "India",
                    "lng"=> 80.275927,
                    "lat"=> 13.0861047,
                    "contact_details"=> array("name"=> "Vivek rao","phone_number"=> "9940042823")
                )
            ));
            if($order_data[0]['payment_method']=='cod'){
                $drop_details=array(array(
                    "reference_id"=> $dunzo_ref_id,
                    "address"=> array(
                        "street_address_1"=> $order_data[0]['address'],
                        "lng"=> (float)$order_data[0]['u_longitude'],
                        "lat"=> (float)$order_data[0]['u_latitude'],
                        "contact_details"=> array("name"=> $order_data[0]['name'],"phone_number"=> $order_data[0]['mobile'])
                    ),
                    "payment_data"=> array(
                        "payment_method"=> "COD",
                        "amount"=> (float)$order_data[0]['final_total']
                    )
                ));
            }else{
                $drop_details=array(array(
                    "reference_id"=> $dunzo_ref_id,
                    "address"=> array(
                        "street_address_1"=> $order_data[0]['address'],
                        "lng"=> (float)$order_data[0]['u_longitude'],
                        "lat"=> (float)$order_data[0]['u_latitude'],
                        "contact_details"=> array("name"=> $order_data[0]['name'],"phone_number"=> $order_data[0]['mobile'])
                    )
                ));
            }
            $fields=array(
                "request_id"=>$dunzo_ref_id,
                "pickup_details"=>$pickup_details,
                "optimised_route"=> true,
                "drop_details"=>$drop_details,
                "payment_method"=> "DUNZO_CREDIT"
            );
            return $this->create_task($fields);
        }
    }
    
    public function cancel_task_line($order_id){
        $order_data_query=$this->db->sql("SELECT task_id FROM dunzo_task  WHERE order_id=".$order_id);
        $order_data=$this->db->getResult();
        if(!empty($order_data[0]['task_id'])){
            $task_id = $order_data[0]['task_id'];
            $reason = "Admin cancelled the order";
            return $this->cancel_task2($task_id,$reason);
        }
    }
    
    public function test_dunzo_apis($task_id,$api_type){
        if($api_type=='next'){
            $api_url='next_state';
        }else if($api_type=='driver_cancel'){
            $api_url='runner_cancel';
        }
        
        $token=$this->token;
        $url=$this->d_url.'api/v1/test/tasks/'.$task_id.'/'.$api_url;
        $headers=array(
                    "client-id:".$this->client_id,
                    "Authorization:".$token,
                    "Accept-Language:"."en_US",
                    "Content-Type:"."application/json"
                );
        $fields='';
        $result=$this->call_api($url,$headers,$fields,"post");
        if($result=='retry'){
            $result=$this->call_api($url,$headers);
            if($result=='retry'){
                die;
            }
        }
        return $result;
    }
    
}


//$dunzo= new dunzo();
//print_r($dunzo->track_status('ca141262-da77-46e2-bf89-a784c5dad874'));
//print_r($dunzo->test_dunzo_apis('a364ec2b-d21e-4336-9263-918ea50a2af3','driver_cancel'));


