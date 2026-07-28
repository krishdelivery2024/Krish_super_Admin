<?php 
$page="Customer Order List";
include"header.php";?>
<?php
if(isset($_GET) && isset($_GET['id'])){
	$end_date=date('Y-m-d');
	$start_date = date('Y-m-d', strtotime("-3 months", strtotime($end_date)));
	$sql="SELECT o.id, o.mobile, o.total as order_total,o.wallet_balance,o.date_added,u.email,u.name as uname,u.country_code,o.status as order_status
        FROM `orders` o
        JOIN users u ON u.id=o.user_id
    WHERE u.id=".$_GET['id']." AND o.date_added < '" . $end_date . "' and o.date_added >'" . $start_date. "'";
    $db->sql($sql);
    $res=$db->getResult();
	?>
        <div id="wrapper">
            <!-- Main row -->

            <div class="row small-spacing">
                <!-- Left col -->
				<div class="col-md-9">
            <?php
            if($permissions['orders']['read']==1) {  ?>
            <div class="box">
                <div class="box-header with-border">
				<h4>Order Count: <?=count($res)?></h4>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
<!--                    <form  id="update_status_form">-->

<?php foreach($res as $row){ 
	 $sql="SELECT oi.*,p.*,v.product_id, v.measurement,o.*,o.total as order_total,o.wallet_balance,oi.active_status as oi_active_status,u.email,u.name as uname,u.country_code,o.status as order_status,p.name as pname,(SELECT short_code FROM unit un where un.id=v.measurement_unit_id)as mesurement_unit_name 
        FROM `order_items` oi
        JOIN users u ON u.id=oi.user_id
        JOIN product_variant v ON oi.product_variant_id=v.id
        JOIN products p ON p.id=v.product_id
        JOIN orders o ON o.id=oi.order_id
    WHERE o.id=".$row['id'];
    $db->sql($sql);
    $res1=$db->getResult();
    $items=[];
    foreach($res1 as $row){
            $data=array($row['product_id'],$row['product_variant_id'],$row['pname'],$row['measurement'],$row['mesurement_unit_name'],$row['quantity'],$row['discounted_price'],$row['price'],$row['oi_active_status']);
            array_push($items, $data);
        }
?>
<div class='p-15'>
                        <table class="table table-bordered">
                            <tr>
                                <input type="hidden" name="hidden" id="order_id" value="<?php echo $row['id']; ?>">
                                <th style="width: 10px;">Order ID</th>
                                <td style="font-weight:bold"><?php echo $row['id']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Name</th>
                                <td><?php echo $row['uname']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Email</th>
                                <td><?php echo $row['email']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Contact</th>
                                <td><?php echo $row['mobile']; ?></td>
                            </tr>
                             <tr>
                                <th style="width: 10px">Items</th>
                                <td><?php $total = 0;
                                echo '<table class="table"><tr><th>Product Id</th><th>Variant Id</th><th>Name</th><th>Unit</th><th>Qty</th><th>Price</th><th>Discounted Price</th><th>Subtotal</th><th>Status</th></tr>';
                                    foreach ($items as $item) {
                                        // echo $item[8];
										echo "<tr>";
                                        if($item[8]=='received'){
                                            $active_status = '<label class="label label-primary">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='processed'){
                                            $active_status = '<label class="label label-info">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='shipped'){
                                            $active_status = '<label class="label label-warning">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='delivered'){
                                            $active_status = '<label class="label label-success">'.$item[8].'</label>';
                                        }
                                        if($item[8]=='returned' || $item[8]=='cancelled'){
                                            $active_status = '<label class="label label-danger">'.$item[8].'</label>';
                                        }
                                        $total += $subtotal = ($item[6] != 0 && $item[6] < $item[7])?($item[6]*$item[5]) : ($item[7]*$item[5]);
                                        echo "<td>" . $item[0]."</td>";
                                        echo "<td>" . $item[1]."</td>";
                                        echo " <td>" . $item[2]."</td>";
                                        echo " <td>" . $item[3]." ".$item[4]."</td>";
                                        echo " <td>" . $item[5]."</td>";
                                        echo " <td>" . $item[7]."</td>";
                                        echo " <td>" . $item[6]."</td>";
                                        echo " <td>" . $subtotal."</td>";
                                        echo " <td>" . $active_status."</td>";
                                        echo "</tr>";
                                    }?>
									</table>

                                </td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Total (<?=$settings['currency']?>)</th>
                                <td ><?php echo $row['order_total']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">D.Charge (<?=$settings['currency']?>)</th>
                                <td ><?php echo $row['delivery_charge']; ?></td>

                            </tr>
                            <tr>
                                <th style="width: 10px">Tax <?=$settings['currency']?>(%)</th>
                                <td ><?php echo $row['tax_amount'].'('.$row['tax_percentage'].'%)'; ?></td>
                            </tr>
                            
                             <?php if($row['discount']>0){
                                $discounted_amount = $row['total'] * $row['discount'] / 100; /*  */
                        	    $final_total = $row['total'] - $discounted_amount;
                                $discount_in_rupees = $row['total']-$final_total;
                                $discount_in_rupees = $discount_in_rupees;
                                // echo $discount_in_rupees;
                            } else {
                                $discount_in_rupees = 0;
                            }?>
                            <tr>
                                <th style="width: 10px">Disc. <?=$settings['currency']?>(%)</th>
                                <td ><?php echo  $discount_in_rupees.'('.$row['discount'].'%)'; ?></td>
                            </tr>
                             
                            <tr>
                                <th style="width: 10px">Promo Disc. (<?=$settings['currency']?>)</th>
                                <td ><?php echo $row['promo_discount']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Wallet Used</th>
                                <td ><?php echo $row['wallet_balance']; ?></td>
                            </tr>
                            
                            
                            <input type="hidden" name="total_amount" id="total_amount" value="<?php echo $row['order_total'];?>">
                            <input type="hidden" name="delivery_charge" id="delivery_charge" value="<?php echo $row['delivery_charge'];?>">
                            <input type="hidden" name="tax_amount" id="tax_amount" value="<?php echo $row['tax_amount'];?>">
                            <input type="hidden" name="promo_discount" id="promo_discount" value="<?php echo $row['promo_discount'];?>">
                            <input type="hidden" name="wallet_balance" id="wallet_balance" value="<?php echo $row['wallet_balance'];?>">
                            <?php
                                $total = $row['total'];
                                $delivery_charge = $row['delivery_charge'];
                                $tax_amount = $row['tax_amount'];
                                $promo_discount = $row['promo_discount'];
                                $wallet = $row['wallet_balance'];
                                $final_total = $total+$delivery_charge+$tax_amount-$discount_in_rupees-$promo_discount-$wallet;
                                $f_total = $total+$delivery_charge+$tax_amount-$promo_discount-$wallet;
                            ?>
                            <input type="hidden" name="final_amount" id="final_amount" value="<?=$f_total;?>">
                            
                            
                             <tr>
                                <th style="width: 10px">Discount %</th>
                                <td ><?php echo $row['discount']; ?></td>
                             </tr>
                            
                            
                            <tr>
                                <th >Deliver By</th>
                                <td>
                                <?php
                                        $sql="SELECT id,name FROM delivery_boys WHERE id=".$row['id'];
                                        $db->sql($sql);
                                        $result=$db->getResult();
                                    ?>

                                    <?php if(isset($result[0]['name'])){
										echo $result[0]['name'];
									}?>
                                </td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Payment Method</th>
                                <td ><?php echo $row['payment_method']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Promo Code</th>
                                <td ><?=(!empty($row['promo_code']) || $row['promo_code'] != null)?$row['promo_code']:""; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Address</th>
                                <td ><?php echo $row['address']; ?></td>
                            </tr>
                            <tr>
                                <th style="width: 10px">Order Date</th>
                                <td ><?php echo date('d-m-Y',strtotime($row['date_added'])); ?></td>
                            </tr>
                            <tr>
                                <th >Status</th>
                                <td>
                                <?php
                                    $status = json_decode($row['order_status']);
                                    $i = count($status);
                                    $currentStatus = $status[$i - 1][0];
									if($currentStatus=='received'){
                                            $active_status = '<label class="label label-primary">'.$item[8].'</label>';
                                        }
                                        if($currentStatus=='processed'){
                                            $active_status = '<label class="label label-info">'.$item[8].'</label>';
                                        }
                                        if($currentStatus=='shipped'){
                                            $active_status = '<label class="label label-warning">'.$item[8].'</label>';
                                        }
                                        if($currentStatus=='delivered'){
                                            $active_status = '<label class="label label-success">'.$item[8].'</label>';
                                        }
                                        if($currentStatus=='returned' || $item[8]=='cancelled'){
                                            $active_status = '<label class="label label-danger">'.$item[8].'</label>';
                                        }
									echo $active_status;
                                    ?>

                                   
                                </td>
                            </tr>
                            
                        </table>
						</div>
                   <?php } ?>     

                        <!-- /.box-body -->
                        
<!--                    </form>-->
                </div>
               
           
            </div>
            <?php } else {?>
            <div class="alert alert-danger">You have no permission to view orders</div>
            <?php }  ?>
            <!-- /.box -->
        </div>
    <?php
     $db->disconnect();
}
?>

<?php include"footer.php";?>