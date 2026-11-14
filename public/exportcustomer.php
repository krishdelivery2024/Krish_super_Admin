<?php
include_once('../includes/variables.php');
include_once('../includes/crud.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customerdata.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Name', 'Email', 'Country Code','Mobile Number','Referral code','friends_code','DOB','Street','Pincode','Balance','Area name','City name','Registered Date']);
$db = new Database();
$db->connect();
    
        $where = '';
        if(isset($_GET['start_date']) && !empty($_GET['start_date']) && isset($_GET['end_date']) && !empty($_GET['end_date'])){
            $start_date = $db->escapeString(trim($_GET['start_date']));
            $end_date = $db->escapeString(trim($_GET['end_date']));
            $where = " WHERE DATE(u.created_at) >= '$start_date' AND DATE(u.created_at) <= '$end_date'";
        } elseif(isset($_GET['start_date']) && !empty($_GET['start_date'])){
            $start_date = $db->escapeString(trim($_GET['start_date']));
            $where = " WHERE DATE(u.created_at) = '$start_date'";
        } elseif(isset($_GET['end_date']) && !empty($_GET['end_date'])){
            $end_date = $db->escapeString(trim($_GET['end_date']));
            $where = " WHERE DATE(u.created_at) <= '$end_date'";
        }
		
		$sql = "SELECT COUNT(*) as total FROM `users` u ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		$total = !empty($res) ? $res[0]['total'] : 0;
		
		$sql = "SELECT u.id,u.name,u.email,u.country_code,u.mobile,u.referral_code,u.friends_code,u.dob,u.street,u.pincode,u.balance,
			CASE WHEN u.area REGEXP '^[0-9]+$' THEN (SELECT name FROM area a WHERE a.id=u.area) ELSE u.area END as area_name,
			CASE WHEN u.city REGEXP '^[0-9]+$' THEN (SELECT name FROM city c WHERE c.id=u.city) ELSE u.city END as city_name,
            u.created_at
			FROM `users` u " . $where . " ORDER BY u.id DESC";
		$db->sql($sql);
		$res = $db->getResult();
        foreach ($res as $line) {
            fputcsv($output, $line);
        }
?>