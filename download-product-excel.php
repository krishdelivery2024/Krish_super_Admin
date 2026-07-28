<?php
error_reporting(1);
    include_once('includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('includes/crud.php');
	include_once('includes/variables.php');
	$db = new Database();
	$db->connect();
	$config = $fn->get_configurations();
	if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
		date_default_timezone_set($config['system_timezone']);
		$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
	}else{
    	date_default_timezone_set('Asia/Kolkata');
    	$db->sql("SET `time_zone` = '+05:30'");
    }
     $sql = "SELECT (SELECT c.name FROM category c WHERE c.id=p.category_id) AS category_name,(SELECT s.name FROM subcategory s WHERE s.id=p.subcategory_id) AS subcategory_name,p.id AS id, p.name,p.hsn,p.sgst,p.cgst,p.igst, p.image, pv.type,pv.weight, pv.price, pv.discounted_price, pv.measurement, pv.serve_for, pv.stock,pv.barcode_data,(SELECT un.short_code FROM unit un WHERE un.id=pv.stock_unit_id) AS stock_unit, u.short_code 
            FROM `products` p JOIN `product_variant` pv ON pv.product_id = p.id
            LEFT JOIN `unit` u ON u.id = pv.measurement_unit_id";
    $db->sql($sql);
    
    $res = $db->getResult();
    
    $filename = "Products.xls"; 
	

require 'library/phpoffice/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Category Name');
$sheet->setCellValue('B1', 'Sub Category Name');
$sheet->setCellValue('C1', 'Product Name');
$sheet->setCellValue('D1', 'Type');
$sheet->setCellValue('E1', 'Barcode Data');
$sheet->setCellValue('F1', 'Measurement');
$sheet->setCellValue('G1', 'Unit');
$sheet->setCellValue('H1', 'Weight');
$sheet->setCellValue('I1', 'MRP');
$sheet->setCellValue('J1', 'Selling Price');
$sheet->setCellValue('K1', 'HSN/SAC');
$sheet->setCellValue('L1', 'SGST(%)');
$sheet->setCellValue('M1', 'CGST(%)');
$sheet->setCellValue('N1', 'IGST(%)');
$sheet->setCellValue('O1', 'Stock');
$sheet->setCellValue('P1', 'Stock Unit');
$sheet->setCellValue('Q1', 'Stock Status');
$sheet->setCellValue('R1', 'Image');
$i=2;
foreach($res as $row)
{
	$sheet->setCellValue('A'.$i, $row['category_name']);
	$sheet->setCellValue('B'.$i, $row['subcategory_name']);
	$sheet->setCellValue('C'.$i, $row['name']);
	$sheet->setCellValue('D'.$i, $row['type']);
	$sheet->setCellValue('E'.$i, $row['barcode_data']);
	$sheet->setCellValue('F'.$i, $row['measurement']);
	$sheet->setCellValue('G'.$i, $row['short_code']);
	$sheet->setCellValue('H'.$i, $row['weight']);
	$sheet->setCellValue('I'.$i, $row['price']);
	$sheet->setCellValue('J'.$i, $row['discounted_price']);
	$sheet->setCellValue('K'.$i, $row['hsn']);
	$sheet->setCellValue('L'.$i, $row['sgst']);
	$sheet->setCellValue('M'.$i, $row['cgst']);
	$sheet->setCellValue('N'.$i, $row['igst']);
	$sheet->setCellValue('O'.$i, $row['stock']);
	$sheet->setCellValue('P'.$i, $row['stock_unit']);
	$sheet->setCellValue('Q'.$i, $row['serve_for']);
	$sheet->setCellValue('R'.$i, $row['image']);
	$i++;
}


$writer = new Xls($spreadsheet);

$writer->save('Products.xls');

 if (file_exists($filename)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filename));
    ob_clean();
    flush();
    readfile($filename);
    exit();
}
?>