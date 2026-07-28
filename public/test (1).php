<?php
include_once('../includes/variables.php');
include_once('../includes/crud.php');
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=salesreport.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID','Mobile', 'Address', 'Order Date','Final Total']);
$db = new Database();
    $db->connect();

     $newmonth = $_GET['start'];
     $monthformate=explode("/",$newmonth);
     $month=$monthformate[2]."/".$monthformate[1]."/".$monthformate[0];
     
     $newdaysago = $_GET['end'];
     $monthformate=explode("/",$newdaysago);
     $daysago=$monthformate[2]."/".$monthformate[1]."/".$monthformate[0];
     
    // $month ="2022/04/06";
    // $daysago = "2022/04/21";
    
    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }
    if (empty($keyword)) {
        $sql_query = "SELECT id, mobile, address,date_added,final_total
                FROM orders WHERE date_added < '" . $daysago . "' and date_added >'" . $month . "'
                ORDER BY id DESC";
    } else {
        $sql_query = "SELECT id, mobile,address,date_added,final_total
                FROM orders WHERE date_added < '" . $daysago . "' and date_added >'" . $month . "'
                AND mobile LIKE '%".$keyword."%' 
                ORDER BY id DESC";
    }
        // Execute query
        $db->sql($sql_query);
        // store result 
        $rows=$db->getResult();  
        
// $db->sql($sql);
// $rows = $db->getResult();

foreach ($rows as $line) {
  fputcsv($output, $line);
}
?>