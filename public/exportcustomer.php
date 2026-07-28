<?php
include_once('../includes/variables.php');
include_once('../includes/crud.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customerdata.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Name', 'Email', 'Country Code','Mobile Number','Referral code','friends_code','DOB','Street','Pincode','Balance','Area name','City name']);
$db = new Database();
$db->connect();
    
        $offset = 0; $limit = 10;
		$sort = 'id'; $order = 'DESC';
		$where = '';
		
		
		$sql = "SELECT COUNT(*) as total FROM `users` ".$where;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		foreach($res as $row)
			$total = $row['total'];
		
		$sql = "SELECT id,name,email,country_code,mobile,referral_code,friends_code,dob,street,pincode,balance,(SELECT name FROM area a WHERE a.id=u.area) as area_name,(SELECT name FROM city c WHERE c.id=u.city) as city_name FROM `users` u ";
		$db->sql($sql);
		$res = $db->getResult();
        foreach ($res as $line) {
            fputcsv($output, $line);
          }
?>