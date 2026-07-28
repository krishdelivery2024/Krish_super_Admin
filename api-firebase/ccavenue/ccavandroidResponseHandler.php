<?php include('Crypto.php');
    include_once('../../includes/custom-functions.php');
	$fn = new custom_functions;
?>
<?php

	error_reporting(0);
	$data = $fn->get_settings('payment_methods',true);
	$workingKey=$data['ccavenue_working_key'];
	
	$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
	$rcvdString=decrypt($encResponse,$workingKey);		//Crypto Decryption used as per the specified working key.
	$order_status="";
	$decryptValues=explode('&', $rcvdString);
	
	$dataSize=sizeof($decryptValues);
	

	for($i = 0; $i < $dataSize; $i++) 
	{
		$information=explode('=',$decryptValues[$i]);
		
		if($i==0){
		    $res['order_id']=$information[1];
		}
		if($i==1){
		    $res['txn_id']=$information[1];
		}
		if($i==2){
		    $res['bank_ref']=$information[1];
		}
		if($i==3){
		    $order_status=$information[1];
    		if($order_status==="Success")
         	{
         	    $res['order_status']=true;
         	}else
            {
                $res['order_status']=false;
            }
		}
	}
    $r=json_encode($res);
 	
	echo "<script type='text/javascript'>
         Android.onResponse('$r')
    </script>"; 	
 	

?>

