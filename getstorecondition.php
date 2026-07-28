<?php 
require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
?> 
<?php
// print_r($_POST['storecondition']);

// $sql="UPDATE admin SET web_login='".$secretkey."' WHERE id=".$res[0]['id'];
// 					$db->sql($sql);
// 					$db->getResult();
if(isset($_POST['storeswitch'])){
    $storecondition=$_POST['storeswitch'];
    $datetime=$_POST['date_time'];
    // $storecondition=json_decode($storecondition);
    // $storecondition=$storecondition[0];
$sql="UPDATE storecondition SET storecondition='".$storecondition."',reopen_datetime='".$datetime."' WHERE id=1";
//echo $sql;die;
$db_con->sql($sql);
$db_con->getResult();         
}   
$sql = "SELECT * FROM storecondition WHERE id=1";
$db_con->sql($sql);
$res = $db_con->getResult();  

$storecondition = new stdClass();
$storecondition->storecondition =$res[0]['storecondition'];
$storecondition->store_reopen_datetime =$res[0]['reopen_datetime'];
$myJSON = json_encode($storecondition);

echo $myJSON;
    
?>