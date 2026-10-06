<?php 
header('Access-Control-Allow-Origin: *');
require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
$db_con->sql("SET NAMES utf8");
?> 
<?php

/**
 * Blank input means "inherit the global value", which is stored as NULL.
 * Anything non-blank must be numeric, so a stray string cannot reach the
 * tariff calculation later on.
 */
function zone_blank_to_null($value)
{
    if ($value === null) {
        return null;
    }
    $value = trim((string)$value);
    if ($value === '') {
        return null;
    }
    return is_numeric($value) ? (float)$value : null;
}

if(isset($_POST)){
    // A checkbox that is not ticked is simply absent from the payload, so every
    // read has to go through isset() rather than assuming the key exists.
    $storepickup           = isset($_POST['storepickup']) ? 1 : 0;
    $in_persion_delivery   = isset($_POST['inpersion']) ? 1 : 0;
    $Delivery_by_courier   = isset($_POST['Delivery_by_courier']) ? 1 : 0;
    $dunzo                 = isset($_POST['dunzo']) ? 1 : 0;

    if($Delivery_by_courier){
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
    }

    $sql="UPDATE delivery_method SET storepickup='".$storepickup."' WHERE id=1";
    $db_con->sql($sql);
    $res = $db_con->getResult();

    if($Delivery_by_courier){
        $zone1 = isset($_POST['zone1']) ? $_POST['zone1'] : '';
        $zone2 = isset($_POST['zone2']) ? $_POST['zone2'] : '';
        $zone3 = isset($_POST['zone3']) ? $_POST['zone3'] : '';
        $zone4 = isset($_POST['zone4']) ? $_POST['zone4'] : '';
        $zonedata = array('zone1'=>$zone1,'zone2'=>$zone2,'zone3'=>$zone3,'zone4'=>$zone4);
        $dataencode=json_encode($zonedata);
        $sql="UPDATE delivery_method SET Delivery_by_courier='1',courier_data='".$dataencode."' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }else{
        $sql="UPDATE delivery_method SET Delivery_by_courier='0' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }

    if($in_persion_delivery){
        $inpersion_delivery = isset($_POST['inpersion_delivery']) ? $_POST['inpersion_delivery'] : 'own';
        if($inpersion_delivery == 'dunzo'){
            $dunzo_client_id = isset($_POST['dunzo_client_id']) ? $_POST['dunzo_client_id'] : '';
            $dunzo_secret_key = isset($_POST['dunzo_secret_key']) ? $_POST['dunzo_secret_key'] : '';
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
            $first_km = isset($_POST['first_km']) ? $_POST['first_km'] : '';
            $first_km_amount = isset($_POST['first_km_amount']) ? $_POST['first_km_amount'] : '';
            $rest_km_amount = isset($_POST['rest_km_amount']) ? $_POST['rest_km_amount'] : '';
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
        
    }else{
        $sql="UPDATE delivery_method SET in_persion_delivery='0',dunzo='".$dunzo."' WHERE id=1";
        $db_con->sql($sql);
        $res = $db_con->getResult();
    }

    // Per-zone delivery method and charge. One row per zone; a blank charge is
    // stored as NULL so the zone keeps inheriting the global tariff.
    if(isset($_POST['zone_id']) && is_array($_POST['zone_id'])){
        foreach($_POST['zone_id'] as $zone_id){
            $zone_id = (int)$zone_id;
            if($zone_id <= 0){
                continue;
            }

            $z_storepickup = isset($_POST['z_storepickup'][$zone_id]) ? 1 : 0;
            $z_inperson    = isset($_POST['z_inperson'][$zone_id]) ? 1 : 0;
            $z_active      = isset($_POST['z_active'][$zone_id]) ? 1 : 0;

            $z_first_km = isset($_POST['z_first_km'][$zone_id])
                ? zone_blank_to_null($_POST['z_first_km'][$zone_id]) : null;
            $z_first_km_amount = isset($_POST['z_first_km_amount'][$zone_id])
                ? zone_blank_to_null($_POST['z_first_km_amount'][$zone_id]) : null;
            $z_rest_km_amount = isset($_POST['z_rest_km_amount'][$zone_id])
                ? zone_blank_to_null($_POST['z_rest_km_amount'][$zone_id]) : null;

            // courier is left at 0: it has no distinct charge path, because
            // courier orders are priced on the in-person per-km tariff.
            $sql="INSERT INTO zone_delivery_method
                    (zone_id, storepickup, in_person_delivery, courier,
                     first_km, first_km_amount, rest_km_amount, is_active)
                  VALUES
                    ('".$zone_id."', '".$z_storepickup."', '".$z_inperson."', '0',
                     ".($z_first_km === null ? 'NULL' : $z_first_km).",
                     ".($z_first_km_amount === null ? 'NULL' : $z_first_km_amount).",
                     ".($z_rest_km_amount === null ? 'NULL' : $z_rest_km_amount).",
                     '".$z_active."')
                  ON DUPLICATE KEY UPDATE
                    storepickup=VALUES(storepickup),
                    in_person_delivery=VALUES(in_person_delivery),
                    courier=0,
                    first_km=VALUES(first_km),
                    first_km_amount=VALUES(first_km_amount),
                    rest_km_amount=VALUES(rest_km_amount),
                    is_active=VALUES(is_active)";
            $db_con->sql($sql);
            $res = $db_con->getResult();
        }
    }
       
}   
$sql = "SELECT * FROM delivery_method WHERE id=1";
$db_con->sql($sql);
$res = $db_con->getResult();  

if(!empty($res)){
    // Every active zone is returned, matching the admin screen, so a zone can be
    // switched off with "Service Enabled" instead of being hidden from the list.
    $db_con->sql("SELECT z.id AS zone_id, z.name AS zone_name, z.status AS zone_status,
                         zdm.storepickup, zdm.in_person_delivery, zdm.courier,
                         zdm.first_km, zdm.first_km_amount, zdm.rest_km_amount, zdm.is_active
                  FROM zone z
                  LEFT JOIN zone_delivery_method zdm ON zdm.zone_id = z.id
                  WHERE z.status = 1
                  ORDER BY z.name");
    $zone_rows = $db_con->getResult();

    $response['error'] = false;
    $response['data'] = $res;
    $response['zones'] = $zone_rows;
}else{
    $response['error'] = true;
	$response['message'] = "No data found!";
}   
$output = json_encode($response);

echo $output;
    
?>