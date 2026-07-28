<?php include('Crypto.php');
    include_once('../../includes/crud.php');
    $db=new Database();
	$db->connect();
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
		    $order_id=$information[1];
		}
		if($i==1){
		    $txn_id=$information[1];
		}
		if($i==2){
		    $res['bank_ref']=$information[1];
		}
		if($i==3){
		    $order_status=$information[1];
    		if($order_status==="Success")
         	{
         	    $status=true;
         	}else
            {
                $status=false;
            }
		}
		if($i==4){
		    $failure_message=$information[1];
		}
	
	    if($i==10){
		    $amount=$information[1];
		}
		
		if($i==40){
		    $tran_date=$information[1];
		}
		
	}
	
    $r=json_encode($res);
    
    $sql_select="SELECT user_id 
					FROM orders 
					WHERE id = ".$order_id;
	$db->sql($sql_select);				
	$res_order=$db->getResult();

	/*add data to transaction table*/
	$message=!empty($status)?"Order Placed Successfully":$failure_message;

	
	$data = array(
	    'user_id'=>$res_order[0]['user_id'],
		'order_id' =>$order_id,
		'type' => 'CCAvenue',
		'txn_id' => $txn_id,
		'amount' =>$amount,
		'status' =>$order_status,
		'message' =>$message,
		'transaction_date' => !empty($tran_date)?date('Y-m-d H:i:s',strtotime($tran_date)):date('Y-m-d H:i:s')
	);	
	$db->insert('transactions',$data);  // Table name, column names and respective values
	$res = $db->getResult();
	
	
	if(!empty($status)){
	    #Update Payment Status
    	$pdata=array(
    		'payment_status'=>1
    	);
    	
    	$db->update('orders',$pdata,'id='.$order_id);
    	
        echo "<script type='text/javascript'>
             window.location.href = 'https://spiderekart.in/order_conformation/".$order_id."';
        </script>";
	}else{
	    echo "<script type='text/javascript'>
         window.location.href = 'https://spiderekart.in/order_pending';
        </script>";
	}
	
    
 	

?>

