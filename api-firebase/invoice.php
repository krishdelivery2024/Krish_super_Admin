<?php 
include_once('../includes/custom-functions.php');
	$fn = new custom_functions;
	include_once('../includes/crud.php');
	include_once('../includes/variables.php');
	$db = new Database();
	$db->connect();
	$config = $fn->get_configurations();
	$currency = $fn->get_settings('currency');
	
	if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
		date_default_timezone_set($config['system_timezone']);
		$db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
	}else{
    	date_default_timezone_set('Asia/Kolkata');
    	$db->sql("SET `time_zone` = '+05:30'");
    }
    
    if (isset($_GET['id'])) {
    $ID = $db->escapeString($fn->xss_clean($_GET['id']));
} else {
    $ID = "";
}
 $sql="SELECT oi.*,oi.id AS order_item_id,v.product_id, v.measurement,o.*,o.total as order_total,o.comments,o.wallet_balance,oi.active_status as oi_active_status,u.email,u.name as uname,u.country_code,o.status as order_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name 
        FROM `order_items` oi
        JOIN users u ON u.id=oi.user_id
        JOIN product_variant v ON oi.product_variant_id=v.id
        JOIN products p ON p.id=v.product_id
        JOIN orders o ON o.id=oi.order_id
    WHERE o.id=".$ID;
    $db->sql($sql);
    $res=$db->getResult();
    $items=[];
    if(!empty($res)){
        foreach($res as $row){
            $data=array($row['product_id'],$row['product_variant_id'],$row['pname'],$row['measurement'],$row['mesurement_unit_name'],$row['quantity'],$row['discounted_price'],$row['price'],$row['oi_active_status'],$row['order_item_id']);
            array_push($items, $data);
        }
    }
       // print_r($res[0]);
?>
<html>

<body style="background-color:#e2e1e0;font-family: Open Sans, sans-serif;font-size:100%;font-weight:400;line-height:1.4;color:#000;">
  <table style="max-width:670px;margin:50px auto 10px;background-color:#fff;padding:50px;-webkit-border-radius:3px;-moz-border-radius:3px;border-radius:3px;-webkit-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);-moz-box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24);box-shadow:0 1px 3px rgba(0,0,0,.12),0 1px 2px rgba(0,0,0,.24); border-top: solid 10px green;">
    <thead>
      <tr>
	  
		 <th  colspan="3" style="text-align:left;">  <h1>
            Invoice
           
                 
                ID: #<?php echo $res[0]['id']; ?>
            
        </h1></th>
        
       <!-- <th  style="text-align:right;font-weight:400;"><a href="#" data-title="Print">
                    
                    Print
                </a></th>
				<th style="text-align:right;font-weight:400;"><a href="#" data-title="Print">
                    
                    Export
                </a></th>-->
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="height:35px;"></td>
      </tr>
      <tr>
        <td colspan="2">
         
          
         <th style="text-align:left;"><img style="max-width: 150px;" src="<?=DOMAIN_URL?>dist/img/logo.png" alt="<?php print_r($config['app_name']);?>"></th>
        </td>
      </tr>
      <tr>
        <td style="height:35px;"></td>
      </tr>
	  
      <tr>
        <td colspan="3" style="width:50%;padding:20px;vertical-align:top">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px">To: </span><?php echo $res[0]['uname']; ?></p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?php echo $res[0]['address']; ?></span>  </p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?php echo $res[0]['email']; ?></span>  </p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?php echo $res[0]['mobile']; ?></span>  </p>
        </td>
        <td  colspan="9" style="width:50%;padding:20px;vertical-align:top">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Invoice</span> </p>
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">ID: #<?php echo $res[0]['id']; ?></span></p>
		  <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Issue Date: <?php echo date("M d, Y",strtotime($res[0]['date_added'])); ?></span></p>
          <!--<p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"> Status: Unpaid</span> </p>-->
        </td>
      </tr>
	  
     <thead>
                        <tr style="background-color: #dddddd;
">
                            <th style="text-align: left;
  padding: 10px;">#</th>
                            <th style="text-align: left;
  padding: 10px;">Description</th>
                            <th style="text-align: left;
  padding: 10px;">Qty</th>
                            <th style="text-align: left;
  padding: 10px;">Unit Price</th>
                            <th style="text-align: left;
  padding: 10px;">Amount</th>
                        </tr>
                    </thead>
	                <tbody class="text-95 text-secondary-d3">
                        <tr></tr>
						
						<?php $qty = 0;$total=0;$i=1; foreach($items as $item){ ?>
                        <tr style="background-color: #dddddd;
">
                            <td style="border: 2px solid #dddddd;
  text-align: left;
  padding: 10px;"><?=$i?></td>
                            <td style="border: 2px solid #dddddd;
  text-align: left;
  padding: 10px;"><?=$item[2].' - '.$item[3].' '.$item[4]?></td>
                            <td style="border: 2px solid #dddddd;
  text-align: left;
  padding: 10px;"><?=$item[5]?></td>
                            <td style="border: 2px solid #dddddd;
  text-align: left;
  padding: 10px;"><?=$currency?><?=$item[6]?></td>
                            <td style="border: 2px solid #dddddd;
  text-align: right;
  padding: 10px;"><?=$currency?><?=$item[6]*$item[5]?></td>
                        </tr> 
						
						<?php $qty = $qty+$item[2]; $i++; $total+=$item[7];} ?>
						
						
                    </tbody>
					<?php
                                        $sql_total = 'select total from orders where id='.$ID;
                                        $db->sql($sql_total);
                                        $res_total = $db->getResult();
                                    ?>
					 
    </tbody>
    <tfooter>
       <tr>
        <td colspan="4" style="font-size:14px;text-align:right;">
      <!--    <strong style="display:block;margin:0 0 10px 0;">Extra note such as company or payment information...</strong>   -->
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">SubTotal </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$res_total[0]['total'];?></span> </p></td></tr>
		  <?php if($res[0]['discount']>0){
                                $discounted_amount = $res_total[0]['total'] * $res[0]['discount'] / 100; /*  */
                        	    $final_total = $res_total[0]['total'] - $discounted_amount;
                                $discount_in_rupees = $res_total[0]['total']-$final_total;
                                $discount_in_rupees = $discount_in_rupees;
                                // echo $discount_in_rupees;
                            } else {
                                $discount_in_rupees = 0;
                            }?>
							 <?php if($res[0]['delivery_charge']>0){ ?>
							<tr> <td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Delivery Charge </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$res[0]['delivery_charge'];?></span></p></td></tr>
		  <?php } ?>
                            <?php if($res[0]['tax_amount']>0){ ?>
                           <tr> <td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Tax (<?=$res[0]['tax_percentage']?>%) </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$res[0]['tax_amount'];?></span></p></td></tr>
		  <?php } ?>
                            <?php if($res[0]['discount']>0){ ?>
                          <tr>  <td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Discount (<?=$res[0]['discount']?>%) </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$discount_in_rupees;?></span></p></td></tr>
		  
                            <?php } ?>
                            <?php if($res[0]['promo_discount']>0){ ?>
                           <tr> <td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"> Promo-(<?=$res[0]['promo_code']?>) Discount</span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$res[0]['promo_discount'];?></span></p></td></tr>
		  
                            <?php } ?>
                            <?php if($res[0]['wallet_balance']>0){ ?>
                           <tr> <td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
          <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Wallet Used </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$res[0]['wallet_balance'];?></span></p></td></tr>
		  <?php } ?>
                            <?php
                            $total = $res_total[0]['total'];
                            $delivery_charge = $res[0]['delivery_charge'];
                            $tax_amount = $res[0]['tax_amount'];
                            $promo_discount = $res[0]['promo_discount'];
                            $wallet = $res[0]['wallet_balance'];
                            $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                            
                        ?>
                        <tr><td  colspan="4" style="width:100%;vertical-align:top;text-align:right;">
		  <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;">Final Total  </span> </p> </td><td style="width:100%;vertical-align:top;text-align:right;"><p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><?=$currency.' '.$final_total;?></span></p>
           
        </td>
        </td>
      </tr>
	  <hr style="border-bottom: 2px solid #ccc;">
	   <tr>
        <td colspan="2" style="font-size:14px;padding:50px 15px 0 15px;">
          <strong style="display:block;margin:0 0 10px 0;">Thank you</strong>  
            <td  colspan="4" style="width:50%;padding:20px;vertical-align:top">
         <!-- <p style="margin:0 0 10px 0;padding:0;font-size:14px;"><span style="display:block;font-weight:bold;font-size:13px;"><a href="#" class="btn btn-info btn-bold px-4 float-right mt-3 mt-lg-0" style="float: right;">Pay Now</a></span> </p>-->
          
           
        </td>
        </td>
      </tr>
    </tfooter>
  </table>
</body>

</html>