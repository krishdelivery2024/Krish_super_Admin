<?php
include_once(__DIR__ . '/../includes/variables.php');
include_once(__DIR__ . '/../includes/crud.php');

$db = new Database();
$db->connect();

if(!isset($_POST['id'])){
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Customer ID is required"]);
    exit;
}

$id = $db->escapeString($_POST['id']);

if(isset($_POST['cityid'])){
    $cityid = $db->escapeString($_POST['cityid']);
    $sql_query = "SELECT id, name FROM area WHERE city_id = '" . $cityid . "'";
    $db->sql($sql_query);
    $res_area = $db->getResult();	
    header('Content-Type: application/json');
    echo json_encode($res_area);
    exit;
}

$name    = isset($_POST['name']) ? $db->escapeString($_POST['name']) : '';
$email   = isset($_POST['email']) ? $db->escapeString($_POST['email']) : '';
$dob     = isset($_POST['dob']) ? $db->escapeString($_POST['dob']) : '';
$state   = isset($_POST['state']) ? $db->escapeString($_POST['state']) : '';
$city    = isset($_POST['city']) ? $db->escapeString($_POST['city']) : '';
$area    = isset($_POST['area']) ? $db->escapeString($_POST['area']) : '';
$street  = isset($_POST['street']) ? $db->escapeString($_POST['street']) : '';
$pincode = isset($_POST['pincode']) ? $db->escapeString($_POST['pincode']) : '';
$balance = isset($_POST['balance']) ? $db->escapeString($_POST['balance']) : '0';
$mobile  = isset($_POST['mobile']) ? $db->escapeString($_POST['mobile']) : '';

$set_clauses = [
    "name = '" . $name . "'",
    "email = '" . $email . "'",
    "dob = '" . $dob . "'",
    "city = '" . $city . "'",
    "area = '" . $area . "'",
    "street = '" . $street . "'",
    "pincode = '" . $pincode . "'",
    "balance = '" . $balance . "'",
    "mobile = '" . $mobile . "'"
];

if ($state !== '') {
    $set_clauses[] = "state = '" . $state . "'";
}

$sql_query = "UPDATE users SET " . implode(", ", $set_clauses) . " WHERE id = '" . $id . "'";

$db->sql($sql_query);
$update_result = $db->getResult();

header('Content-Type: application/json');
echo json_encode(["status" => true, "result" => $update_result]);
?>
