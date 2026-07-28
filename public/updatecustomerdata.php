<?php
include_once('../includes/variables.php');
include_once('../includes/crud.php');

    $db = new Database();
    $db->connect();
    $id=$_POST['id'];
    
    if(isset($_POST['cityid'])){
        $cityid=$_POST['cityid'];
        $sql_query = "SELECT id, name FROM area where city_id =".$cityid;
	   
     	$db->sql($sql_query);
	    $res_area=$db->getResult();	
	    echo json_encode($res_area);
	    exit;
    }
    $name=$_POST['name'];
    $email=$_POST['email'];
    $dob=$_POST['dob'];
    $city=$_POST['city'];
    $area=$_POST['area'];
    $street=$_POST['street'];
    $pincode=$_POST['pincode'];
    $balance=$_POST['balance'];
    $mobile=$_POST['mobile'];

    // print_r($street); exit;
    $sql_query = "UPDATE users 
    SET name = '".$name."', email = '".$email."', dob = '".$dob."',city = ".$city.",street = '".$street."',pincode = ".$pincode.",balance = ".$balance.",mobile = '".$mobile."',area=".$area."
    WHERE id =".$id;
    // Execute query
    $db->sql($sql_query);
    // store result 
    $update_result = $db->getResult();
    if(!empty($update_result)){
       echo $update_result=0;
    }
    else{
       echo $update_result=1;
    }
    
?>  
