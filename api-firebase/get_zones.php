<?php
include_once('../includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SELECT id, name, polygon FROM zone WHERE status=1");
$zones = $db->getResult();
header('Content-Type: application/json');
if(!$zones){
    echo json_encode(['error'=>true,'message'=>'No zones found']);
    exit;
}
// Ensure polygon is valid JSON; if stored as string, decode and re-encode
foreach($zones as &$z){
    if(is_string($z['polygon'])){
        $decoded = json_decode($z['polygon'], true);
        $z['polygon'] = $decoded ? $decoded : $z['polygon'];
    }
}
echo json_encode(['data'=>$zones]);
?>
