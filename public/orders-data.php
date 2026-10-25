<?php
include_once('includes/variables.php');
include_once('includes/crud.php');
include_once('includes/custom-functions.php');
$function = new custom_functions();
if (isset($_GET['id'])) {
    $ID = $db->escapeString($function->xss_clean($_GET['id']));
} else {
    $ID = "";
}
// create array variable to handle error
$update_order_permission = (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin' || $_SESSION['role'] == 'seller')) ? 1 : (!empty($permissions['orders']['update']) ? 1 : 0);
$error = array();
if (isset($_POST['update_order_status'])) {
    $process = $db->escapeString($function->xss_clean($_POST['status']));
}
    $sql="SELECT oi.*,oi.id AS order_item_id,v.product_id, v.measurement,o.*,o.total as order_total,o.wallet_balance,oi.active_status as oi_active_status,u.email,u.name as uname,u.country_code,o.status as order_status,p.name as pname,p.hsn as hsn ,oi.sgst as sgstp,oi.cgst as cgstp,oi.igst as igstp,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name 
        FROM `order_items` oi
        JOIN users u ON u.id=oi.user_id
        JOIN product_variant v ON oi.product_variant_id=v.id
        JOIN products p ON p.id=v.product_id
        JOIN orders o ON o.id=oi.order_id
    WHERE o.id=".$ID;
    $db->sql($sql);
    $res=$db->getResult();
    $items=[];
    foreach($res as $row){
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
            $data=array($row['product_id'],$row['product_variant_id'],$row['pname'],$row['hsn'],$row['measurement'],$row['mesurement_unit_name'],$row['quantity'],$discounted_price,$taxable_amount,$sgst,$row['sgstp'],$cgst,$row['sgstp'],$igst,$row['igstp'],$row['sub_total'],$row['oi_active_status'],$row['order_item_id']);
            array_push($items, $data);
        }
      
        if(!empty(array_filter(array_column($items, 14)))){
            $igst_show = true;
        }else{
            $igst_show=false;
        } 
        if($res[0]['payment_method']=='cod' || $res[0]['payment_method']=='wallet' || $res[0]['payment_method']=='LoyaltyPoints' || $res[0]['payment_status']==1){ 
            $payment_status=1;
        }
        
        
?>

<div id="wrapper">
    <div class="row">
        <div class="col-md-12">
            <?php
            if(!empty($permissions['orders']['read']) || (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin' || $_SESSION['role'] == 'seller'))) { 
                if($update_order_permission == 0){?>
                <div class="alert alert-danger topmargin-sm">You have no permission to update orders.</div>
            <?php } ?>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Order Detail</h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
<!--                    <form  id="update_status_form">-->
                        <table class="table table-bordered">
                            <tr>
                                <input type="hidden" name="hidden" id="order_id" value="<?php echo $res[0]['id']; ?>">
                                <th style="width: 10px">ID</th>
                                <td><?php echo $res[0]['id']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Name</th>
                                <td><?php echo $res[0]['uname']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Email</th>
                                <td><?php echo $res[0]['email']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Contact</th>
                                <td><?php echo $res[0]['mobile']; ?></td>
                            </tr>
                             <tr>
                                <th style="width: 10px">Items</th>
                                <td><?php $total = 0;
                                    $t ='<table class="table"><tr><th>Product Id</th><th>Name</th><th>HSN</th><th>Unit</th><th>Qty</th><th>Price</th><th>Taxable Amount</th>';
                                     if($store_state == $row['delivery_state']){
                                         $t.='<th>SGST</th><th>CGST</th>';
                                     }else{
                                         $t.='<th>IGST</th>';
                                     }
                                    $t.='<th>Subtotal</th><th>Status</th><th>Action</th></tr>';
                                     echo $t;
                                    foreach ($items as $item) {
										echo "<tr>";
                                        if($item[16]=='received'){
                                            $active_status = '<label class="label label-primary">'.$item[16].'</label>';
                                            $cancel_item = '<button class="btn btn-xs btn-danger update_item_status" data-id="'.$item[17].'"><i class="fa fa-trash" title="Cancel Item"></i></button>';
                                        }
                                        if($item[16]=='processed'){
                                            $active_status = '<label class="label label-info">'.$item[16].'</label>';
                                            $cancel_item = '<button class="btn btn-xs btn-danger update_item_status" data-id="'.$item[17].'"><i class="fa fa-trash" title="Cancel Item"></i></button>';
                                        }
                                        if($item[16]=='shipped'){
                                            $active_status = '<label class="label label-warning">'.$item[16].'</label>';
                                            $cancel_item = '<button class="btn btn-xs btn-danger update_item_status" data-id="'.$item[17].'"><i class="fa fa-trash" title="Cancel Item"></i></button>';
                                        }
                                        if($item[16]=='delivered'){
                                            $active_status = '<label class="label label-success">'.$item[16].'</label>';
                                            $cancel_item = '<button class="btn btn-xs btn-danger update_item_status" data-id="'.$item[17].'"><i class="fa fa-trash" title="Cancel Item"></i></button>';
                                        }
                                        if($item[16]=='returned' || $item[16]=='cancelled'){
                                            $active_status = '<label class="label label-danger">'.$item[16].'</label>';
                                            $cancel_item = '';
                                        }
                                        //$total += $item[9];
                                        echo "<td>" . $item[0]."</td>";
                                      //  echo "<td>" . $item[1]."</td>";
                                        echo " <td>" . $item[2]."</td>";
                                        echo " <td>" . $item[3]."</td>";
                                        echo " <td>" . $item[4]." ".$item[5]."</td>";
                                        echo " <td>" . $item[6]."</td>";
                                        echo " <td>" .$settings['currency']. $item[7]."</td>";
                                        echo " <td>" .$settings['currency']. $item[8]."</td>";
                                       if($store_state == $row['delivery_state']){ 
                                        if(!empty($item[9])){echo " <td>" .$settings['currency']. $item[9]."(".$item[10]."%)</td>";}else{echo "<td>-</td>";}
                                        if(!empty($item[11])){echo " <td>" .$settings['currency']. $item[11]."(".$item[12]."%)</td>";}else{echo "<td>-</td>";}
                                        }else{
                                            if(!empty($item[13])){echo " <td>" .$settings['currency']. $item[13]."(".$item[14]."%)</td>";}else{echo "<td>-</td>";}
                                        }
                                        echo " <td>" .$settings['currency']. $item[15]."</td>";
                                        echo " <td>" . $active_status."</td>";
                                        echo " <td>" . $cancel_item."</td>";
                                        echo "</tr>";
                                    }?>
									</table>
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Total (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['order_total']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">D.Charge (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['delivery_charge']; ?></td>

                            </tr>
                            
                             <?php if($res[0]['discount']>0){
                                $discounted_amount = $res[0]['total'] * $res[0]['discount'] / 100; /*  */
                        	    $final_total = $res[0]['total'] - $discounted_amount;
                                $discount_in_rupees = $res[0]['total']-$final_total;
                                $discount_in_rupees = $discount_in_rupees;
                                // echo $discount_in_rupees;
                            } else {
                                $discount_in_rupees = 0;
                            }?>
                            <tr>
                                <th style="width: 10px">Disc. <?=$settings['currency']?>(%)</th>
                                <td ><?php echo  $discount_in_rupees.'('.$res[0]['discount'].'%)'; ?></td>
                            </tr>
                             
                            <tr>
                                <th style="width: 10px">Promo Disc. (<?=$settings['currency']?>)</th>
                                <td ><?php echo $res[0]['promo_discount']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Wallet Used</th>
                                <td ><?php echo $res[0]['wallet_balance']; ?></td>
                            </tr>
                            
                            
                            <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $res[0]['order_total'];?>">
                            <input type="hidden" name="delivery_charge" id="delivery_charge" value="<?php echo $res[0]['delivery_charge'];?>">
                            <input type="hidden" name="tax_amount" id="tax_amount" value="<?php echo $res[0]['tax_amount'];?>">
                            <input type="hidden" name="promo_discount" id="promo_discount" value="<?php echo $res[0]['promo_discount'];?>">
                            <input type="hidden" name="wallet_balance" id="wallet_balance" value="<?php echo $res[0]['wallet_balance'];?>">
                            <?php
                                $total = $res[0]['total'];
                                $delivery_charge = $res[0]['delivery_charge'];
                                $tax_amount = $res[0]['tax_amount'];
                                $promo_discount = $res[0]['promo_discount'];
                                $wallet = $res[0]['wallet_balance'];
                                $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                                $f_total = $total+$delivery_charge+$tax_amount-$promo_discount-$wallet;
                            ?>
                            <input type="hidden" name="final_amount" id="final_amount" value="<?=$f_total;?>">
                            
                            
                             <tr>
                                <th style="width: 10px">Discount %</th>
                                <td ><input type="number" class="form-control" id="input_discount" name="input_discount" value="<?php echo $res[0]['discount']; ?>" min=0 max=100></td>
                                <td><a href="#" title='save_discout' class="btn btn-primary form-control update_order_total_payable" data-id='<?=$row['id'];?>'>Save</a></td>
                            </tr>
                            
                            
                            <tr>
                                <th style="width: 10px">Payable Total(<?=$settings['currency']?>)</th>
                                <td ><input type="text" class="form-control" id="final_total" name="final_total" value="<?=$res[0]['final_total'];?>" disabled ></td>
                            </tr>
                            <?php if(!empty($payment_status) && $payment_status==1){ ?>
                            <tr>
                                <th >Deliver By</th>
                                <td>
                                <?php  
                                      if($res[0]['delivery_method'] == 'store_pick_up'){
                                          echo 'Store Pickup';
                                      }elseif($res[0]['delivery_method'] == 'courier'){
                                          echo 'Courier';
                                      }else{
                                
                                        $sql = "SELECT * FROM delivery_method WHERE id=1";
                                        $db->sql($sql);
                                        $resut = $db->getResult();
                                        $dunzo = $resut[0]['dunzo'];
                                        $ipd = $resut[0]['in_persion_delivery'];
                                        if($dunzo == '1' && $ipd == '1'){
                                        $sql="SELECT id,name FROM delivery_boys WHERE status=1 and name = 'dunzo'";
                                        }else{
                                           $sql="SELECT id,name FROM delivery_boys WHERE status=1 and name <> 'dunzo'"; 
                                        }
                                        $db->sql($sql);
                                        $result=$db->getResult();
                                        
                                    if($dunzo == '1' && $ipd == '1'){ ?>
                                        <select id='deliver_by' name='deliver_by' class='form-control col-md-7 col-xs-12' required>
                                            <?php foreach($result as $row1){
                                                if($res[0]['delivery_boy_id'] == $row1['id']){?>
                                                    <option value='<?=$row1['id']?>' selected><?=$row1['name']?></option>
                                               <?php } else{
                                                if($dunzo == '1' && $row1['name'] == 'dunzo'){ ?>
                                                    <option value='<?=$row1['id']?>'><?=$row1['name']?></option>
                                               <?php } } } ?>
                                        </select>
                                        
                                    <?php }else{?>

                                    <select id='deliver_by' name='deliver_by' class='form-control col-md-7 col-xs-12' required>
                                    <option value=''>Select Delivery Boy</option>
                                    <?php foreach($result as $row1){
                                        
                                        if($res[0]['delivery_boy_id'] == $row1['id']){?>
                                            <option value='<?=$row1['id']?>' selected><?=$row1['name']?></option>
                                       <?php } else{
                                       if($row1['name'] != 'dunzo'){
                                       ?>
                                       <option value='<?=$row1['id']?>'><?=$row1['name']?></option> 
                                    <?php }} }?>
                                </select>
                                <?php } ?>
                                
                                <?php } ?>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <th style="width: 10px">Payment Method</th>
                                <td ><?php echo $res[0]['payment_method']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Promo Code</th>
                                <td ><?=(!empty($res[0]['promo_code']) || $res[0]['promo_code'] != null)?$res[0]['promo_code']:""; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Address</th>
                                <td ><?php echo $res[0]['address']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Delivery Time</th>
                                <td ><?php echo $row['delivery_time']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Order Date</th>
                                <td ><?php echo date('d-m-Y',strtotime($row['date_added'])); ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Notes</th>
                                <td ><?php echo $res[0]['notes']; ?></td>
                            </tr>
                            <?php 
                            $status = json_decode($res[0]['order_status']);
                                    $i = count($status);
                                    $currentStatus = $status[$i - 1][0];
                            if(!empty($payment_status) && $payment_status==1){ ?>
                            <tr>
                                <th >Status</th>
                                <td>

                                    <select name="status" id="status" class="form-control">
                                        <option value="received" <?=($currentStatus == 'received' || $res[0]['active_status'] == 'received') ? 'selected' : '';?>>Received</option>
                                        <option value="processed" <?=($currentStatus == 'processed' || $res[0]['active_status'] == 'processed') ? 'selected' : '';?>>Processed</option>
                                        <option value="shipped" <?=($currentStatus == 'shipped' || $res[0]['active_status'] == 'shipped') ? 'selected' : '';?>>Shipped</option>
                                        <option value="delivered" <?=($currentStatus == 'delivered' || $res[0]['active_status'] == 'delivered') ? 'selected' : '';?>>Delivered</option>
                                        <option value="cancelled" <?=($currentStatus == 'cancelled' || $res[0]['active_status'] == 'cancelled') ? 'selected' : '';?>>Cancel</option>
                                        <option value="returned" <?=($currentStatus == 'returned' || $res[0]['active_status'] == 'returned') ? 'selected' : '';?>>Returned</option>
                                    </select>
                                </td>
                            </tr>
                            <?php } ?>
                            
                        </table>
                        

                        <!-- /.box-body -->
                        <div class="alert alert-danger" id="result_fail" style="display:none"></div>
                        <div class="alert alert-success" id="result_success" style="display:none"></div>
                        <div class="box-footer clearfix">
                            <?php $whatsapp_message = "Hello ".ucwords($res[0]['uname']).", Your order with ID : ".$res[0]['id']." is ".ucwords($currentStatus).". Please take a note of it. If you have further queries feel free to contact us. Thank you.";?>
                            <?php if(!empty($payment_status) && $payment_status==1){ ?>
                            <a href="#" title='update' id="submit_btn" class="btn btn-primary update_order_status" data-id='<?=$res[0]['id'];?>'>Update</a>
                            <?php } else{ ?>
                            <a class="btn btn-primary mt-3 update_payment_status" href="javascript:void(0)" data-id='<?=$res[0]['id'];?>'><i class="fa fa-arrow-up"></i> Update Payment</a>
                            <?php } ?>
                            <a href="https://api.whatsapp.com/send?phone=<?='+'.$res[0]['country_code'].' '.$res[0]['mobile'];?>&text=<?=$whatsapp_message;?>" target='_blank' title="Send Whatsapp Notification" class="btn btn-success"><i class="fa fa-whatsapp"></i> Send Whatsapp Notification</a>
                        </div>
<!--                    </form>-->
                </div>
               
               <?php 
            //   if($res[0]['delivery_boy_id']!=0){
                if(!empty($payment_status) && $payment_status==1){ 
                   if ($currentStatus == "received") { ?>
                        <button class="btn btn-primary pull-right" onclick="myfunction()"  style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i>Generate Invoice</button>
                    <?php } elseif ($currentStatus == "processed") { ?>
                        <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                    <?php } elseif ($currentStatus == "shipped") { ?>
                        <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                    <?php } elseif ($currentStatus == "delivered") { ?>
                        <button class="btn btn-primary pull-right" onclick="myfunction()" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                    <?php } else { ?>
                        <button class="btn btn-primary disabled pull-right" style="margin-right: 5px; margin-top: -45px;"><i class="fa fa-download"></i> Generate Invoice</button>
                    <?php } }?>
                    
            </div>
            <?php } else {?>
            <div class="alert alert-danger">You have no permission to view orders</div>
            <?php }  ?>
            <!-- /.box -->

            <?php
            $refund_details = $function->get_order_refund_details($ID, 'food');
            $show_refund_card = ($refund_details['is_cancelled'] || $refund_details['refund_state'] !== 'none');
            if($show_refund_card){
            ?>
            <div class="box box-warning" id="refund_tracking_box" style="margin-top:20px;border-top:3px solid #f39c12;">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-refresh"></i> Razorpay Refund & Cancellation Tracking</h3>
                    <div class="box-tools pull-right">
                        <?php if(!empty($refund_details['can_sync'])){ ?>
                            <button type="button" class="btn btn-sm btn-info btn-sync-refund-food" data-order-id="<?=$ID?>" data-query-id="<?=!empty($refund_details['refund_id']) ? $refund_details['refund_id'] : $refund_details['payment_id']?>"><i class="fa fa-refresh"></i> Check Live Razorpay Status</button>
                        <?php } ?>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 35%;">Order Status</th>
                                    <td><span class="label label-danger">Cancelled</span></td>
                                </tr>
                                <tr>
                                    <th>Payment Method</th>
                                    <td><strong><?=htmlspecialchars($refund_details['payment_method'])?></strong></td>
                                </tr>
                                <tr>
                                    <th>Razorpay Payment ID</th>
                                    <td>
                                        <?php if(!empty($refund_details['payment_id'])){ ?>
                                            <code><?=htmlspecialchars($refund_details['payment_id'])?></code>
                                        <?php } else { ?>
                                            <span class="text-muted">Not detected</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Refund ID</th>
                                    <td>
                                        <?php if(!empty($refund_details['refund_id'])){ ?>
                                            <strong id="food_refund_id"><?=htmlspecialchars($refund_details['refund_id'])?></strong>
                                        <?php } else { ?>
                                            <span class="text-muted" id="food_refund_id">-</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Refund Amount</th>
                                    <td><strong>Rs. <?=number_format((float)$refund_details['refund_amount'], 2)?></strong></td>
                                </tr>
                                <?php if(!empty($refund_details['rrn'])){ ?>
                                <tr>
                                    <th>Bank RRN (Tracking #)</th>
                                    <td><code style="font-weight:bold;color:#0073b7;"><?=htmlspecialchars($refund_details['rrn'])?></code> <small class="text-muted">(Customer bank reference)</small></td>
                                </tr>
                                <?php } ?>
                                <?php if(!empty($refund_details['refund_date'])){ ?>
                                <tr>
                                    <th>Recorded Date</th>
                                    <td><?=htmlspecialchars($refund_details['refund_date'])?></td>
                                </tr>
                                <?php } ?>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div style="padding:15px; background:#f9f9f9; border-radius:4px; border:1px solid #e3e3e3;">
                                <h4>Refund Status</h4>
                                <div id="food_refund_badge_wrap" style="margin-bottom:12px;">
                                    <?php if($refund_details['refund_state'] === 'processing'){ ?>
                                        <span class="label label-warning" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-clock-o"></i> Razorpay: Refund Processing</span>
                                        <div class="alert alert-warning" style="margin-top:12px;margin-bottom:0;padding:10px;">
                                            <strong>Bank Settlement In Progress:</strong> Razorpay has accepted the refund request. Customer's bank typically takes <strong>5 to 7 business days</strong> to credit the customer's account statement.
                                            <br><small>Status in local database: <code>process</code></small>
                                        </div>
                                    <?php } elseif($refund_details['refund_state'] === 'processed'){ ?>
                                        <span class="label label-success" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-check"></i> Razorpay: Refund Settled / Completed</span>
                                        <div class="alert alert-success" style="margin-top:12px;margin-bottom:0;padding:10px;">
                                            <strong>Completed:</strong> The refund has been settled to the customer's payment account.
                                        </div>
                                    <?php } elseif($refund_details['refund_state'] === 'wallet_refunded'){ ?>
                                        <span class="label label-info" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-credit-card"></i> Refunded to User Wallet</span>
                                        <p class="text-muted" style="margin-top:8px;">Amount was credited back to user's app wallet balance.</p>
                                    <?php } elseif($refund_details['refund_state'] === 'cod_skipped'){ ?>
                                        <span class="label label-default" style="font-size:14px;padding:6px 12px;display:inline-block;">COD (Cash on Delivery)</span>
                                        <p class="text-muted" style="margin-top:8px;">No online payment was collected, so no refund is required.</p>
                                    <?php } else { ?>
                                        <span class="label label-danger" style="font-size:14px;padding:6px 12px;display:inline-block;"><i class="fa fa-exclamation-triangle"></i> Not Refunded / Refund Failed</span>
                                        <div class="alert alert-danger" style="margin-top:12px;margin-bottom:0;padding:10px;">
                                            <?=!empty($refund_details['refund_message']) ? htmlspecialchars($refund_details['refund_message']) : 'Order was cancelled, but the online refund was not processed yet.'?>
                                        </div>
                                    <?php } ?>
                                </div>

                                <?php if($refund_details['can_retry']){ ?>
                                    <div style="margin-top:15px;padding-top:12px;border-top:1px dashed #ccc;">
                                        <label><i class="fa fa-paper-plane"></i> Initiate / Retry Refund Manually</label>
                                        <div class="input-group">
                                            <input type="text" id="manual_food_payment_id" class="form-control" placeholder="Razorpay Payment ID (pay_...)" value="<?=$refund_details['payment_id']?>">
                                            <span class="input-group-btn">
                                                <button class="btn btn-warning btn-manual-refund-food" type="button" data-order-id="<?=$ID?>"><i class="fa fa-undo"></i> Initiate Refund Now</button>
                                            </span>
                                        </div>
                                        <p class="help-block" style="margin-top:5px;font-size:11px;">If the payment ID was not captured automatically, copy the <code>pay_...</code> ID from your Razorpay Dashboard and paste it here.</p>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php if(!empty($permissions['orders']['read']) || (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'super admin' || $_SESSION['role'] == 'seller'))){?>
        <div class="col-md-3">
            <ul class="timeline">
            <?php foreach($status as $s){ ?>
                <!-- timeline time label -->
                <li class="time-label">
                    <span class="bg-blue">
                        <?=$s[0];?>
                    </span>
                </li>
                <!-- /.timeline-label -->
                <!-- timeline item -->
                <li>
                    <!-- timeline icon -->
                    <!--<i class="fa fa-circle bg-blue"></i>-->
                    <div class="timeline-item">
                        <!--<span class="time"><i class="fa fa-clock-o"></i> 12:05</span>-->
                        <h5 class="timeline-header"><?=$s[1];?></h5>
                        <div class="timeline-body">
                        </div>
                    </div>
                </li>
                <!-- timeline time label -->
            <!-- /.timeline-label -->
            <!-- timeline item -->
            <!-- END timeline item -->
        <?php } ?>
        </ul>
        </div>
        <?php } ?>
    </div>
</div>

<script>
    $(document).on('click','.update_payment_status',function(){
       var id = $(this).data('id');
       if(confirm('Are you sure want to update payment status as Success for order?')){
        $.ajax({
            type:'POST',
            url: 'public/db-operation.php',
            data:'id='+id+'&update_payment_status=1',
            success:function(result){
                if(result==0){
                    location.reload();
                }
                if(result==1){
                    alert('Error! Payment could not be Updated.');
                }
                if(result==2){
                    alert('You have no permission to Update Payment');
                }
            }
        });
    }
});

// Live Razorpay sync for food orders
$(document).on('click', '.btn-sync-refund-food', function(e){
    e.preventDefault();
    var $btn = $(this);
    var orderId = $btn.data('order-id');
    var queryId = $btn.data('query-id');
    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking Razorpay...');

    $.ajax({
        url: 'api-firebase/order-process.php',
        type: 'POST',
        data: {
            sync_refund_status: 1,
            order_id: orderId,
            order_type: 'food',
            query_id: queryId
        },
        dataType: 'json',
        success: function(resp){
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check Live Razorpay Status');
            if(!resp.error && resp.data){
                alert(resp.message);
                location.reload();
            } else {
                alert('Razorpay Check: ' + (resp.message || 'Unable to fetch status.'));
            }
        },
        error: function(){
            $btn.prop('disabled', false).html('<i class="fa fa-refresh"></i> Check Live Razorpay Status');
            alert('Server error occurred while checking Razorpay status.');
        }
    });
});

// Manual refund for food orders
$(document).on('click', '.btn-manual-refund-food', function(e){
    e.preventDefault();
    var $btn = $(this);
    var orderId = $btn.data('order-id');
    var paymentId = $('#manual_food_payment_id').val().trim();

    if(!confirm('Are you sure you want to initiate Razorpay refund for Order #' + orderId + (paymentId ? (' using Payment ID: ' + paymentId) : '') + '?')){
        return;
    }

    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Initiating...');

    $.ajax({
        url: 'api-firebase/order-process.php',
        type: 'POST',
        data: {
            process_refund: 1,
            order_id: orderId,
            order_type: 'food',
            payment_id: paymentId
        },
        dataType: 'json',
        success: function(resp){
            $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Initiate Refund Now');
            alert(resp.message);
            location.reload();
        },
        error: function(){
            $btn.prop('disabled', false).html('<i class="fa fa-undo"></i> Initiate Refund Now');
            alert('Server error while processing refund.');
        }
    });
});
</script>
<?php $db->disconnect(); ?>