<?php include('Crypto.php');
    include_once('../../includes/crud.php');
    $db=new Database();
	$db->connect();
    include_once('../../includes/custom-functions.php');
	$fn = new custom_functions;
?>
<?php
    $myfile = fopen("instamojo.txt", "a") or die("Unable to open file!");
    $txt = file_get_contents('php://input');
    fwrite($myfile, "\n".date('d-m-Y h:i A')." - ". $txt);
    fclose($myfile);
	error_reporting(0);

	$data = $fn->get_settings('payment_methods',true);
	$workingKey=$data['ccavenue_working_key'];

	$encResponse=$_POST["encResp"];			//This is the response sent by the CCAvenue Server
	$sql="INSERT INTO `cclogs`(`logs`) VALUES ('$encResponse')";
	$db->sql($sql_query);
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
    
   

    echo "<script type='text/javascript'>
         Android.onResponse('$r')
    </script>";
   
 	

?>

