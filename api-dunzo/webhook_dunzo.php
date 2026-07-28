<?php
include_once('../includes/crud.php');
include_once('../includes/custom-functions.php');

include_once('dunzo-api.php');
$dunzo= new dunzo();
//$db->sql("SELECT * FROM dunzo_task WHERE status!='delivered'");
//$get_dunzo=$db->getResult();
//$data=$dunzo->track_status('8dbe06e3-5257-4ab0-9e5b-de7f26041566');

$myfile = fopen("dunzo_webhook.txt", "a") or die("Unable to open file!");
$txt = file_get_contents('php://input');
fwrite($myfile, "\n".date('d-m-Y h:i A')." - ". $txt);
fclose($myfile);
$data = json_decode($txt);

$task_id = $data->task_id;
$function = new custom_functions();
$db = new Database();
$db->connect();
date_default_timezone_set('Asia/Kolkata');
$db->sql("SELECT * FROM dunzo_task WHERE task_id='".$task_id."'");
$get_order_id=$db->getResult();
$order_id=$get_order_id[0]['order_id'];

$db->sql("SELECT *,(SELECT name FROM users WHERE users.id=orders.user_id) AS name FROM orders WHERE id='".$order_id."'");
$order_data=$db->getResult();
$runner=$data->runner;
if($data->state=='runner_accepted' || $data->state=='reached_for_pickup'){
    $runner=$data->runner;
    $db->sql("UPDATE dunzo_task SET runner_name='".$runner->name."',runner_mobile='".$runner->phone_number."' WHERE task_id='".$task_id."'");
    $message="Order No. ".$order_data[0]['id'].", Dunzo Delivery Partner assigned. Partner Name".$runner->name.", Contact No.".$runner->phone_number;
    $function->send_new_order_notification("Dunzo Delivery Partner assigned",$message,'order');
}

$db->sql("UPDATE dunzo_task SET status='".$data->state."' WHERE task_id='".$task_id."'");

if($data->state=='pickup_complete' || $data->state=='started_for_delivery'){
    $status = json_decode($order_data[0]['status']);
    $status[] = array('shipped',date("d-m-Y h:i:sa"));
    $data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => 'shipped'
        	        );
        	        
    $db->update('orders',$data_order,'id='.$order_id);
    $db->update('order_items',$data_order,'order_id='.$order_id);
    $message="Order No. ".$order_data[0]['id'].", Dunzo Delivery Partner Picked Up package.";
    $function->send_new_order_notification("Package Picked Up by Dunzo Delivery Partner",$message,'order');
    $message = "Hello, Dear ".ucwords($order_data[0]['name']).", Here is the new update on your order for the order ID : #".$order_id.". Your order has been ".ucwords('shipped').". Person Name: ".$get_order_id[0]['runner_name'].", Contact No: ".$get_order_id[0]['runner_mobile'].". Please take a note of it.";
    $function->send_order_update_notification($order_data[0]['user_id'],"Your order has been ".ucwords('shipped'),$message,'order');
}

if($data->state=='delivered'){
    $status = json_decode($order_data[0]['status']);
    array_push($status,array('delivered',date("d-m-Y h:i:sa")));
    //$status[] = array('delivered',date("d-m-Y h:i:sa"));
    $data_order = array(
            	    'status' => $db->escapeString(json_encode($status)),
            		'active_status' => 'delivered'
        	        );
    $db->update('orders',$data_order,'id='.$order_id);
    $db->update('order_items',$data_order,'order_id='.$order_id);
    $message = "Hello, Dear ".ucwords($order_data[0]['name']).", Here is the new update on your order for the order ID : #".$order_id.". Your order has been ".ucwords('delivered').". Please take a note of it.";
    $function->send_order_update_notification($order_data[0]['user_id'],"Your order has been ".ucwords('delivered'),$message,'order');
}
if($data->state=='cancelled'){
    $data_order = array(
            	    'delivery_boy_id' => 0
        	        );
    $db->update('orders',$data_order,'id='.$order_id);
}