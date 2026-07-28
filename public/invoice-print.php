<?php
include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$function = new custom_functions;
$settings = $fn->get_configurations();
$db->sql("SELECT value FROM settings WHERE variable='terms_in_inovice'");
$terms_in_inovice1=$db->getResult();
$terms_in_inovice=$terms_in_inovice1[0]['value'];
$sql = "select value from `settings` where variable='Logo' OR variable='logo'";
$db->sql($sql);
$res_logo = $db->getResult();
$sql = "select value from `settings` where variable='Store_seal' OR variable='store_seal'";
$db->sql($sql);
$res_store_seal = $db->getResult();
$currency = $fn->get_settings('currency');
?>

<?php
if (isset($_GET['id'])) {
    $ID = $db->escapeString($function->xss_clean($_GET['id']));
} else {?>
    <script>
        window.location.href="invoices.php";
    </script>
    <?php
}
$sql_outer="SELECT oi.*,oi.id AS order_item_id,v.product_id, v.measurement,u.*,o.*,u.name as uname,d.name as delivery_boy,o.status as order_status,oi.active_status as order_item_status,p.name as pname,p.hsn as hsn ,oi.sgst as sgstp,oi.cgst as cgstp,oi.igst as igstp,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name FROM `order_items` oi JOIN users u ON u.id=oi.user_id JOIN product_variant v ON oi.product_variant_id=v.id JOIN products p ON p.id=v.product_id JOIN orders o ON o.id=oi.order_id LEFT JOIN delivery_boys d ON o.delivery_boy_id=d.id WHERE o.id=".$ID;
    // Execute query
    $db->sql($sql_outer);
    // store result 
    $res_outer=$db->getResult();
     //print_r($res_outer);die;
      $items=[];
    foreach($res_outer as $row){
    //         $discounted_price=$row['discounted_price']!=0?$row['discounted_price']:$row['price'];
		  //  $gst=!empty($row['sgstp']) ||!empty($row['cgstp']) ||!empty($row['igstp'])?$row['sgstp']+$row['cgstp']+$row['igstp']:0;
    //         $taxable_amount=round((100*$discounted_price/(100+$gst))*$row['quantity'],2);
    //         $sgst=!empty($row['sgstp'])?round(($taxable_amount*$row['sgstp'])/100,2):0;
    //         $cgst=!empty($row['cgstp'])?round(($taxable_amount*$row['cgstp'])/100,2):0;
    //         $igst=!empty($row['igstp'])?round(($taxable_amount*$row['igstp'])/100,2):0;
    
            $sgstp = !empty($row['sgstp'])?$row['sgstp']:0;
            $cgstp = !empty($row['cgstp'])?$row['cgstp']:0;
            $igstp = !empty($row['igstp'])?$row['igstp']:0;
            
            $discounted_price=$row['discounted_price']!=0?$row['discounted_price']:$row['price'];
            if($store_state == $row['delivery_state']){
                $gst=$sgstp+$cgstp;
                $taxable_amount=round((100*$discounted_price/(100+$gst))*$row['quantity'],2);
                $sgst=!empty($row['sgstp'])?round(($taxable_amount*$row['sgstp'])/100,2):0;
                $cgst=!empty($row['cgstp'])?round(($taxable_amount*$row['cgstp'])/100,2):0;
            }else{
                $gst = $igstp;
                $taxable_amount=round((100*$discounted_price/(100+$gst))*$row['quantity'],2);
                $igst=!empty($row['igstp'])?round(($taxable_amount*$row['igstp'])/100,2):0;
            }
            $sgst=!empty($sgst)?$sgst:0;
            $cgst=!empty($cgst)?$cgst:0;
            $igst=!empty($igst)?$igst:0;
            
            $data=array($row['product_id'],$row['pname'],$row['hsn'],$row['quantity'],$row['measurement'],$row['mesurement_unit_name'],$discounted_price,$taxable_amount,$row['discount'],$sgst,$row['sgstp'],$cgst,$row['cgstp'],$igst,$row['igstp'],$row['sub_total'],$row['order_item_status']);
            array_push($items, $data);
        }
         // print_r($items); 
        $encoded_items=$db->escapeString(json_encode($items));
$id = $res_outer[0]['id'];
$sql = "SELECT COUNT(id) as total FROM `invoice` where order_id=".$id;
$db->sql($sql);
$res=$db->getResult();
$total=$res[0]['total'];
if ($total == 0) {

    $invoicedate = date('Y-m-d');
    $id = $res_outer[0]['id'];
    $name=$res_outer[0]['uname'];
    $email=$res_outer[0]['email'];
    $address = $res_outer[0]['address'];
    $phone = $res_outer[0]['mobile'];
    $orderdate = $res_outer[0]['date_added'];
    $order_list = $encoded_items;
    $discount = $res_outer[0]['discount'];
    $final_total=$res_outer[0]['final_total'];
    $total_payble = $res_outer[0]['price'];
    $shipping_charge=$res_outer[0]['delivery_charge'];
    $payment = $res_outer[0]['final_total'];
    $data = array(
        'invoice_date' => $invoicedate,
        'order_id' => $id,
        'name' => $name,
        'address' => $address,
        'order_date' => $orderdate,
        'phone_number' => $phone,
        'order_list' => $encoded_items,
        'email' => $email,
        'discount' => $discount,
        'total_sale' => $total_payble,
        'shipping_charge' => $shipping_charge,
        'payment' => $payment,
    );
    // print_r($data);
    $db->insert('invoice',$data);
    $res=$db->getResult();
}

$sql_invoice = "SELECT id, invoice_date FROM invoice WHERE order_id =" . $id;

    // Execute query
    $db->sql($sql_invoice);
    // store result 
    $res_invoice=$db->getResult();
    $order_list = $encoded_items;
    
    $t=1;
    foreach(array_unique(array_filter(array_column($items,10))) as $r){
        $tax_details[$t]['type']='SGST';
        $tax_details[$t]['rate']=$r;
        $tax_details[$t]['amount'] = 0; // Initialize amount
        $tax_details[$t]['tax'] = 0;  
        foreach($items as $v){
            if($v['10']==$r){
                $tax_details[$t]['amount']+=$v['7'];
                $tax_details[$t]['tax']+=$v['9'];
            }
            
        }
        
       $t++; 
    }
    foreach(array_unique(array_filter(array_column($items,12))) as $r){
        $tax_details[$t]['type']='CGST';
        $tax_details[$t]['rate']=$r;
        $tax_details[$t]['amount'] = 0; // Initialize amount
        $tax_details[$t]['tax'] = 0;  
        foreach($items as $v){
            if($v['12']==$r){
                $tax_details[$t]['amount']+=$v['7'];
                $tax_details[$t]['tax']+=$v['11'];
            }
            
        }
        
       $t++; 
    }
    
        foreach(array_unique(array_filter(array_column($items,14))) as $r){
            $tax_details[$t]['type']='IGST';
            $tax_details[$t]['rate']=$r;
            $tax_details[$t]['amount'] = 0; // Initialize amount
            $tax_details[$t]['tax'] = 0;  
            foreach($items as $v){
                if($v['14']==$r){
                    $tax_details[$t]['amount']+=$v['7'];
                    $tax_details[$t]['tax']+=$v['13'];
                }
                
            }
            
           $t++;
        }
    

    if(!empty(array_filter(array_column($items, 14)))){
        $igst_show = true;
    }else{
        $igst_show=false;
    }


?>

<style>
@page { size: auto;  margin: 0mm; }
</style>

<style>
    
.borderless td{
    border: none!important;
    padding: 0px!important;
}


@media print{
    .main-menu{display:none;}
    .fixed-navbar{display:none;}
    .no-print{display:none;}
}
    .heading th{
    border: none!important;
    padding: 3px!important;
}


</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <?php if($permissions['reports']['create']==0){?>
    <div class="alert alert-danger topmargin-sm">You have no permission to generate invoice</div>
    <?php exit(); } ?>
    
    <div id="wrapper">
        <div id="printable" class="box sp">
            <div class="box-header">
                <table  style="width:100%;">
                    <tr style="border:1px solid #ddd">
                        <td style="padding-left:5px;vertical-align:center;">
                            <div style="max-width:200px">
                            <h2>TAX INVOICE</h2>    
                            <img src="<?=DOMAIN_URL.'dist/img/'.$res_logo[0]['value']?>" title='<?=$data['app_name']?> - Logo' alt='<?=(isset($data['app_name']))?$data['app_name']:"";?> - Logo'  />
                            </div>
                        <td>
                        <td class="text-right" style="padding:25px;vertical-align:center;">
                            <h2 class="text-right">
                                <h5 class=""> <?=$settings['app_name'];?> </h5>
                                <?=$settings['address'];?><br>
                                Phone No: +91 <?=$settings['support_number'];?> <br> Email: <?=$settings['support_email'];?><br>
                                GSTIN: <?=$settings['gst_no'];?><br>
                                FSSAI Licence: <?=$settings['fssai_no'];?>
                            </h2>
                        </td>
                    </tr>
                </table>
                <table style="width:100%;">
                    <tr style="border:1px solid #ddd">
                        <td style="padding:25px;vertical-align:center;border-right:1px solid #ddd">
                            To
                            <address>
                                <strong><?php echo $res_outer[0]['uname']; ?></strong><br>
                                <?php echo $res_outer[0]['address'];?><br>
                                Mobile :<?php echo $res_outer[0]['mobile']; ?><br>
                                <?=!empty($res_outer[0]['email'])?'Email : '.$res_outer[0]['email'].'<br>':''; ?>
                                <?=!empty($res_outer[0]['gst_no'])?'GSTIN : '.$res_outer[0]['gst_no']:''; ?>
                                 
                            </address>
            
                        </td><!-- /.col -->
                        
                        <td style="padding:25px;vertical-align:center;">
                                <b>Invoice No : </b>#<?php echo $res_invoice[0]['id']; ?>
                                <br>
                                <b>Invoice Date: </b><?php echo date('d-m-Y',strtotime($res_invoice[0]['invoice_date'])); ?>
                                <br>
                            <b>Order ID: </b>#<?php echo $res_outer[0]['id']; ?>
                            <br>
                            <b>Order Date: </b><?php echo date('d-m-Y h:i A',strtotime($res_outer[0]['date_added'])); ?>
                        </td>
                    </tr>
                </table><!-- /.row -->
            </div>
                    <!-- Table row -->
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 table-responsive">
                            <table class="table borderless">
                                <thead class="text-center">
                                    <tr>
                                        <th>#</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>HSN</th>
                                        <th>Unit</th>
                                        <th>Qty</th>
                                        <th>Price/U</th>
                                        <th>Taxable Amt</th>
                                        <?php if($store_state == $row['delivery_state']){?>
                                        <th>SGST</th>
                                        <th>CGST</th>
                                        <?php }else{?>
                                           <th>IGST</th> 
                                        <?php  } ?>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $decoded_items=json_decode(stripSlashes($order_list));
                                    // print_r($decoded_items);
                                        $qty = 0;
                                        $i=1;
                                        $total=0;
                                        $taxabletotal=0;
                                        $sgsttotal=0;
                                        $cgsttotal=0;
                                        $igsttotal=0;
                                        foreach ($decoded_items as $item) {
                                            if($item[16]!='cancelled' && $item[16]!='returned'){
                                        ?>
                                    <tr>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$i?></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$item[0] ?></td>
                                            <td><?=$item[1] ?></td>
                                            <td><?=$item[2] ?></td>
                                            <td><?=$item[4]." ".$item[5] ?></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$item[3] ?></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$currency;?> <?=$item[6] ?></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$currency;?> <?=$item[7] ?></td>
                                            <?php if($store_state == $row['delivery_state']){?>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($item[10])?$currency.''.$item[9].'('.$item[10].'%)':'-'?></td>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($item[12])?$currency.''.$item[11].'('.$item[12].'%)':'-'?></td>
                                            <?php }else{?>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($item[14])?$currency.''.$item[13].'('.$item[14].'%)':'-'?></td>
                                            <?php  } ?>
                                            <td>&nbsp;&nbsp;&nbsp;&nbsp;<?=$currency;?> <?=$item[15]?></td>
                                            
                                    </tr>
                                    <?php $qty = $qty+$item[3];
                                    $taxabletotal+=$item[7];
                                    $sgsttotal+=$item[9];
                                    $cgsttotal+=$item[11];
                                    $igsttotal+=$item[13];
                                    $i++;
                                    $total+=$item[15];
                                    
                                } }?>
                                <?php
                                    $sql_total = 'select total from orders where id='.$ID;
                                    $db->sql($sql_total);
                                    $res_total = $db->getResult();
                                ?>
                                
                                    <tr style="border-top:2px solid #ddd;line-height:50px;padding-bottom: 2em; font-weight:bold;">
                                            <td></td>
                                        <td style="text-align:right" colspan="4">Total</td>
                                       <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$qty?></td>
                                       <td></td>
                                       <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$currency.''.$taxabletotal?></td>
                                       <?php if($store_state == $row['delivery_state']){?>
                                       <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($sgsttotal)?$currency.''.$sgsttotal:'-'?></td>
                                       <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($cgsttotal)?$currency.''.$cgsttotal:'-'?></td>
                                        <?php }else{?>
                                           <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=!empty($igsttotal)?$currency.''.$igsttotal:'-'?></td>
                                        <?php  } ?>
                                       <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$currency.''.$total;?></td>
                                    </tr>
                                </tbody>
                            </table>

                                    <?php if($res_outer[0]['discount']>0){
                                        $discounted_amount = $res_total[0]['total'] * $res_outer[0]['discount'] / 100; /*  */
                                	    $final_total = $res_total[0]['total'] - $discounted_amount;
                                        $discount_in_rupees = $res_total[0]['total']-$final_total;
                                        $discount_in_rupees = $discount_in_rupees;
                                        // echo $discount_in_rupees;
                                    } else {
                                        $discount_in_rupees = 0;
                                    }?>
                            <table style="width:100%">
                                    <tr style="border-top:1px solid #ddd">
                                        <td style="padding:15px;">
                                            <table style="width:100%">
                                               <?php if(!empty($tax_details)){ ?>
                                                <tr style="font-weight:bold"><td >Type</td><td class="text-right">Taxable Amount</td><td class="text-right">Rate(%)</td><td class="text-right">Tax Amount</td></tr>
                                                  <?php foreach($tax_details as $tax){ ?>
                                                  <?php if($store_state == $row['delivery_state']){
                                                    if($tax['type'] == "SGST" || $tax['type'] == "CGST"){ ?>
                                                        <tr><td><?=$tax['type']?></td><td class="text-right"><?=$currency;?> <?=$tax['amount']?></td><td class="text-right"><?=$tax['rate']?>%</td><td class="text-right"><?=$currency;?> <?=$tax['tax']?></td></tr>
                                                    <?php }
                                                   }else{
                                                      if($tax['type'] == "IGST"){ ?> 
                                                      <tr><td><?=$tax['type']?></td><td class="text-right"><?=$currency;?> <?=$tax['amount']?></td><td class="text-right"><?=$tax['rate']?>%</td><td class="text-right"><?=$currency;?> <?=$tax['tax']?></td></tr>
                                                   <?php } }
                                                } }?>
                                               
                                               
                                            </table>  
                                        </td>
                                        <td style="padding:15px;padding-left:75px;">
                                            <table >          
                                                <tr >
                                                    <td style="font-weight:bold"  class="text-right">Taxable Amount : </td>
                                                    <td style="min-width:20px;"></td>
                                                    <td class="text-right">&nbsp;<?php echo '+ '.$currency.' '.$taxabletotal; ?></td>
                                                </tr>
                                                <tr >
                                                    <td style="font-weight:bold"  class="text-right">Tax Amount : </td>
                                                    <td style="min-width:20px;"></td>
                                                    <td class="text-right">&nbsp;<?php echo '+ '.$currency.' '.$res_outer[0]['tax_amount']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold"  class="text-right">Delivery Charge :</td>
                                                    <td></td>
                                                    <td class="text-right">&nbsp;<?='+ '.$currency.' '.$res_outer[0]['delivery_charge'];?></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold"  class="text-right">Discount : </td>
                                                    <td></td>
                                                    <td class="text-right">&nbsp;<?='- '.$currency.' '.$discount_in_rupees.' ('.$res_outer[0]['discount'].'%)'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold"  class="text-right">Promo (<?=$res_outer[0]['promo_code'];?>) Discount : </td>
                                                    <td></td>
                                                    <td class="text-right">&nbsp;<?='- '.$currency.' '. $res_outer[0]['promo_discount'];?></td>
                                                </tr>
                                                <tr>
                                                    <td style="font-weight:bold"  class="text-right">Wallet Used : </td>
                                                    <td></td>
                                                    <td class="text-right">&nbsp;<?='- '.$currency.' '.$res_outer[0]['wallet_balance']; ?></td>
                                                </tr>
                                                <td style="font-weight:bold"  class="text-right">Final Total : </td>
                                                <td></td>
                                                <?php
                                                    $total = $res_total[0]['total'];
                                                    $delivery_charge = $res_outer[0]['delivery_charge'];
                                                    $tax_amount = $res_outer[0]['tax_amount'];
                                                    $promo_discount = $res_outer[0]['promo_discount'];
                                                    $wallet = $res_outer[0]['wallet_balance'];
                                                    $final_total = $total+$delivery_charge-$discount_in_rupees-$promo_discount-$wallet;
                                                    
                                                ?>
                                                <td class="text-right">&nbsp;<?='= '.$currency.' '.ceil($final_total);?></td>
                                                </tr>
                                            </table>  
                                        </td>
                                    </tr> 
                            </table>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                        <table  style="width:100%;">
                            <tr style="border:1px solid #ddd">
                                <td style="padding:25px;vertical-align:center;">
                                    <?php //$terms_in_inovice; ?>
                                <td>
                                <td class="text-center" style="padding:25px;max-width:100px;vertical-align:center;border-left:1px solid #ddd">
                                   
                                        for, <?=$settings['app_name'];?><br>
                                        <img src="<?=DOMAIN_URL.'dist/img/'.$res_store_seal[0]['value']?>" title='<?=$data['app_name']?> - Logo' alt='<?=(isset($data['app_name']))?$data['app_name']:"";?> - Logo'  style="max-width:150px"/><br>
                                        Authorized Signatory
                                   
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>    
            </div>    

        <!-- this row will not appear when printing -->
        <div class="row no-print">
            <div class="col-xs-12">
                <form>
                    <button type='button' value='Print this page' onclick='printpage();' class="btn btn-default"><i class="fa fa-print"></i> Print</button>
                    <button type='button' value='Print this page' onclick='invdownload();' class="btn btn-default"><i class="fa fa-print"></i> Download Invoice</button>                           
                </form>
                <script language="javascript">
                    function printpage()
                    {
                        window.print();
                    }

                    function invdownload()
                    {
                        var element = document.getElementById('printable');
                        var opt = {
                        
                        filename:     'invoice.pdf',
                        image:        { type: 'jpeg', quality: 0.98 },
                        html2canvas:  { scale: 2 ,scrollY: 0},
                        jsPDF:        { unit: 'in', format: 'A4', orientation: 'portrait' }
                        };

                        // New Promise-based usage:
                        html2pdf().set(opt).from(element).save();
                    }

                </script>
            </div>
        </div>