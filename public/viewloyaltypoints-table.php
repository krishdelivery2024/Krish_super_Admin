<?php 

    include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include('includes/variables.php');
    include_once('includes/custom-functions.php');
    
    $fn = new custom_functions;
    $config = $fn->get_configurations();
    
        $sql = "SELECT * FROM `Loyalty_Points_Transaction` where user_id=".$_GET['id'];
		$db->sql($sql);
		$res = $db->getResult();
// 		echo"<pre>";
//         print_r($res);
// 		exit;
		?>
		 <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Each Order Details</h3>
<!--                            <h3 class="box-title">Total_Sale : <?php // echo $total_daily; ?></h3>-->
         <!--                   <div class="box-tools">-->
         <!--                       <form  method="get">-->
         <!--                           <ul class="list-inline margin-bottom-0">-->
									<!--	<li class="form-group">-->
									<!--		<input type="text" name="keyword" class="form-control input-sm" placeholder="Search">-->
									<!--	</li>-->
									<!--	<li class="form-group">-->
									<!--	<button type="submit" class="btn-sm"><i class="fa fa-search"></i></button>-->
									<!--	</li>-->
									<!--</ul>-->
         <!--                       </form>-->
         <!--                   </div>-->
                        </div><!-- /.box-header -->
                        <div class="box-body table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <th>ID</th>
                                    <th>ORDER ID</th>
                                    <th>MESSAGE</th>
                                    <th>REDEEM LOYALTY POINTS</th>
                                    <th>EARNED LOYALTY POINTS</th>
                                    <th>TRANSACTION DATE</th>
                                    
                                    
                                
                                    

                                </tr>
                                <?php
                                $count = 1;
                                foreach($res as $row) {
                                
                                    ?>
                                    <tr>
                                        <td><?php echo $count; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['message']; ?></td>
                                   
                                        <td><?php echo $row['Redeem_Loyalty_Points']; ?></td>
                                        <td><?php echo $row['earned_loyalty_points']; ?></td>
                                        <td><?php echo $row['transaction_update']; ?></td>
                                    </tr>
                                    <?php
                                    $count++;
                                }
                                
                                
    
                            
                            ?>
                            
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
  
