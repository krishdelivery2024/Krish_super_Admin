<?php 
header('Access-Control-Allow-Origin: *');
require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
$db_con->sql("SET NAMES utf8");
?> 
<?php

if(isset($_POST)){
    //echo '<pre>';print_r($_POST);die();
    //$delivery_method=json_decode($delivery_method);
    $storepickup = $_POST['storepickup'];
    $ipd = $_POST['inpersion_delivery'];
    $in_persion_delivery=$_POST['inpersion'];
    $Delivery_by_courier=$_POST['Delivery_by_courier'];
    $dunzo=$_POST['dunzo'];
    if(!empty($storepickup)){
        $sql="UPDATE delivery_method SET storepickup='1' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }
    if(is_null($storepickup)){
        $sql="UPDATE delivery_method SET storepickup='0' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }
    
    if(!empty($Delivery_by_courier)){
        $sql="SELECT * FROM product_variant WHERE weight IS NULL OR weight=0";
        $db_con->sql($sql);
        $res = $db_con->getResult();
        $num_rows = $db_con->numRows($res);
		if($num_rows > 0){
		    $response['error'] = true;
	        $response['message'] = "Weight required";
	        $output = json_encode($response);
            echo $output;die();
		}
        $zone1 = $_POST['zone1'];
        $zone2 = $_POST['zone2'];
        $zone3 = $_POST['zone3'];
        $zone4 = $_POST['zone4'];
        $zonedata = array('zone1'=>$zone1,'zone2'=>$zone2,'zone3'=>$zone3,'zone4'=>$zone4);
        $dataencode=json_encode($zonedata);
        $sql="UPDATE delivery_method SET Delivery_by_courier='1',courier_data='".$dataencode."' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }
    
    if(is_null($Delivery_by_courier)){
        $sql="UPDATE delivery_method SET Delivery_by_courier='0' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }
    if(!empty($in_persion_delivery)){
        $inpersion_delivery = $_POST['inpersion_delivery'];
        if($inpersion_delivery == 'dunzo'){
            $dunzo_client_id = $_POST['dunzo_client_id'];
            $dunzo_secret_key = $_POST['dunzo_secret_key'];
            $data = [
            'dunzo_client_id'=>$dunzo_client_id,
            'dunzo_secret_key'=>$dunzo_secret_key
            ];
            $data_decode=json_encode($data);
            $sql="UPDATE delivery_method SET in_persion_delivery='1',dunzo='1',dunzo_data='".$data_decode."' WHERE id=1";
            $db_con->sql($sql);
            $res = $db_con->getResult();
        }
        if($inpersion_delivery == 'own'){
            $first_km = $_POST['first_km'];
            $first_km_amount = $_POST['first_km_amount'];
            $rest_km_amount = $_POST['rest_km_amount'];
            $data = [
            'first_km'=>$first_km,
            'first_km_amount'=>$first_km_amount,
            'rest_km_amount'=>$rest_km_amount
            ];
            $dataecode=json_encode($data);
            $sql="UPDATE delivery_method SET in_persion_delivery='1',dunzo='0',in_persion_data='".$dataecode."' WHERE id=1";
            $db_con->sql($sql);
            $res = $db_con->getResult();
        }
        
    }
    if(is_null($in_persion_delivery)){ 
        $sql="UPDATE delivery_method SET in_persion_delivery='0' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }
       
}   
$sql = "SELECT * FROM delivery_method WHERE id=1";
$db_con->sql($sql);
$res = $db_con->getResult();  

if(!empty($res)){
    $response['error'] = false;
    $response['data'] = $res;
}else{
    $response['error'] = true;
	$response['message'] = "No data found!";
}   
$output = json_encode($response);

echo $output;
    
?>