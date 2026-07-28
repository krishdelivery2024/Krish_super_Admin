<?php
session_start();
include('../includes/crud.php');
include('../api-firebase/send-email.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");
$auth_username = $db->escapeString($_SESSION["user"]);

include_once('../includes/custom-functions.php');
$fn = new custom_functions;
$function = new custom_functions;
$permissions = $fn->get_permissions($_SESSION['id']);
$config = $fn->get_configurations();
if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
    date_default_timezone_set($config['system_timezone']);
    $db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
}else{
    date_default_timezone_set('Asia/Kolkata');
    $db->sql("SET `time_zone` = '+05:30'");
}
if(isset($_POST['type']) && isset($_POST['id']) && isset($_POST['value'])){
	$id = $db->escapeString($fn->xss_clean($_POST['id']));
	$type = $db->escapeString($fn->xss_clean($_POST['type']));
	$value = $db->escapeString($fn->xss_clean($_POST['value']));
	if($type=='name'){
		$db->sql('SELECT product_id FROM product_variant WHERE id='.$id);
		$product=$db->getResult();
		$product_id=$product[0]['product_id'];
		$data=array('name'=>$value);
		$db->update('products',$data,'id='.$product_id);
		echo 1;
	}else if($type=='cat'){
		$db->sql('SELECT product_id FROM product_variant WHERE id='.$id);
		$product=$db->getResult();
		$product_id=$product[0]['product_id'];
		$data=array('category_id'=>$value);
		$db->update('products',$data,'id='.$product_id);
		echo 1;
	}else if($type=='subcat'){
		$db->sql('SELECT product_id FROM product_variant WHERE id='.$id);
		$product=$db->getResult();
		$product_id=$product[0]['product_id'];
		$data=array('subcategory_id'=>$value);
		$db->update('products',$data,'id='.$product_id);
		echo 1;
	}else if($type=='m_unit'){
		$data=array('measurement_unit_id'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='stock'){
		$data=array('stock'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='s_unit'){
		$data=array('stock_unit_id'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='s_for'){
		$data=array('serve_for'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='mes'){
		$data=array('measurement'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='price'){
		$data=array('price'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='v_price'){
		$data=array('vendor_price'=>$value);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else if($type=='d_price'){
		$db->sql('SELECT product_id FROM product_variant WHERE id='.$id);
		$product=$db->getResult();
		$product_id=$product[0]['product_id'];
		
	    $db->sql('SELECT sgst,cgst,igst FROM products WHERE id='.$product_id);
		$gst=$db->getResult();
		
		$item_sgst=!empty($gst[0]['sgst'])?round($value*$gst[0]['sgst']/(100+$gst[0]['sgst']), 2):'';
		$item_cgst=!empty($gst[0]['cgst'])?round($value*$gst[0]['cgst']/(100+$gst[0]['cgst']), 2):'';
		$item_igst=!empty($gst[0]['igst'])?round($value*$gst[0]['sgst']/(100+$gst[0]['igst']), 2):'';
		
		$tax_amount=!empty($gst[0]['sgst']) ||!empty($gst[0]['sgst']) ||!empty($gst[0]['sgst'])?$item_sgst+$item_cgst+$item_igst:0;
		$product_price=$value-$tax_amount;
		$data=array('discounted_price'=>$value,
		'product_price'=>$product_price,
		'item_sgst'=>$item_sgst,
		'item_cgst'=>$item_cgst,
		'item_igst'=>$item_igst
		);
		$db->update('product_variant',$data,'id='.$id);
		echo 1;
	}else{
		echo 0;
	}
}else{
	echo 0;
}
?>