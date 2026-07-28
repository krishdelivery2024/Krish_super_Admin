<?php ob_start(); ?>

<?php $page="Add State";
include"header.php";?>
 <?php 
	include_once('includes/functions.php'); 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
?>
	<?php 
			$sql_query = "SELECT v.*,p.sgst,p.cgst,p.igst FROM `product_variant`AS v JOIN products as p ON p.id=v.product_id ORDER BY id ASC";
            	$db->sql($sql_query); 
            	$res = $db->getResult();
				if(!empty($res)){
				    foreach($res as $row){
				        $varid = $row['id'];
				       
				        $sgstp = !empty($row['sgst'])?$row['sgst']:0;
                        $cgstp = !empty($row['cgst'])?$row['cgst']:0;
                        $igstp = !empty($row['igst'])?$row['igst']:0;
                        
                        $discounted_price=$row['discounted_price']!=0?$row['discounted_price']:$row['price'];
                        
                        $gst=$sgstp+$cgstp;
                        $taxable_amount=round((100*$discounted_price/(100+$gst))*1,2);
                        $sgst=!empty($sgstp)?round(($taxable_amount*$sgstp)/100,2):0;
                        $cgst=!empty($cgstp)?round(($taxable_amount*$cgstp)/100,2):0;
                        $igst=$sgst+$cgst;
						
						$sql_query = "UPDATE product_variant 
							SET product_price = '".$taxable_amount."',item_sgst = '".$sgst."',item_cgst = '".$cgst."',item_igst = '".$igst."'
							WHERE id =".$varid;
    					$db->sql($sql_query); 
    					$result = $db->getResult();
				    }
				    
				}
				
				
	
	?>
	 
	
<?php $db->disconnect(); ?>
	
<?php include"footer.php";?>