<?php
    header('Access-Control-Allow-Origin: *');
	header("Content-Type: application/json");
    header("Expires: 0");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
	
	include_once('../includes/crud.php');
	$db=new Database();
	$db->connect(); 
	include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('../includes/variables.php');
	include_once('verify-token.php');

	if(isset($_POST['ajaxCall']) && !empty($_POST['ajaxCall'])){
		$request_type = 'webrequest';
		$accesskey="90336";	
	}else{
		$request_type = 'apprequest';
		if(isset($_POST['accesskey']) && $_POST['accesskey'] != ''){
			$accesskey = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		}else{
			$response['error']= true;
			$response['message']="accesskey required";
			print_r(json_encode($response));
			return false;
		}
		
	}
	
	if($access_key != $accesskey){
		$response['error']= true;
		$response['message']="invalid accesskey";
		print_r(json_encode($response));
		return false;
	}
	
	/* accesskey:90336 
		city_id:24 */
	if(!verify_token()){
		return false;
	}
	if(isset($_POST['accesskey'])) {
		$access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
		if($access_key_received == $access_key){
			$sql = "SELECT * FROM delivery_method WHERE id=1";
            $db->sql($sql);
            $res = $db->getResult();  
            
            $delivery_method = new stdClass();
            $delivery_method->in_persion_delivery =$res[0]['in_persion_delivery'];
            $delivery_method->Delivery_by_courier =$res[0]['Delivery_by_courier'];
            $delivery_method->storepickup =$res[0]['storepickup'];
            $delivery_method->dunzo =$res[0]['dunzo'];

            // Zone aware overrides. The app sends the checkout address coordinates,
            // so the flags above can be replaced by the ones configured for the zone
            // that address falls in. Coordinates are optional: an older build that
            // sends only the accesskey keeps getting the global settings, which is
            // also what an address that cannot be attributed to a zone falls back to.
            $zone_id = 0;
            $zone_name = '';

            $lat = isset($_POST['latitude']) ? trim((string)$_POST['latitude']) : '';
            $lng = isset($_POST['longitude']) ? trim((string)$_POST['longitude']) : '';

            if($lat !== '' && $lng !== '' && is_numeric($lat) && is_numeric($lng)
                && (float)$lat != 0 && (float)$lng != 0){
                $resolved_zone = $fn->get_zone_id_from_latlng((float)$lat, (float)$lng);
                if($resolved_zone !== null && (int)$resolved_zone > 0){
                    $zone_id = (int)$resolved_zone;

                    $zone_cfg = $fn->get_zone_delivery_config($zone_id, $res[0]);
                    // Cast to string so the app's "== 1" style checks keep working
                    // against the same shape the global values have.
                    $delivery_method->in_persion_delivery = (string)$zone_cfg['in_person_delivery'];
                    $delivery_method->storepickup = (string)$zone_cfg['storepickup'];
                    // courier is deliberately left on the global value: it has no
                    // charge path of its own and is not configurable per zone.

                    $db->sql("SELECT name FROM zone WHERE id=".$zone_id." LIMIT 1");
                    $zone_row = $db->getResult();
                    if(!empty($zone_row[0]['name'])){
                        $zone_name = $zone_row[0]['name'];
                    }
                }
            }

            // Echoed back so the app can log which zone the methods came from and
            // show a clearer message when that zone cannot be served.
            $delivery_method->zone_id = $zone_id;
            $delivery_method->zone_name = $zone_name;
            $delivery_method->serviceable = (($delivery_method->storepickup == '1'
                || $delivery_method->in_persion_delivery == '1'
                || $delivery_method->Delivery_by_courier == '1'
                || $delivery_method->dunzo == '1') ? '1' : '0');

            $myJSON = json_encode($delivery_method);
            
            echo $myJSON;

		}else{
			die('accesskey is incorrect.');
		}
	} else {
		die('accesskey is required.');
	}
	$db->disconnect(); 
?>