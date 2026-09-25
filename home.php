<?php 
$page="Home";
include"header.php";

/* ---------- Zone filter + zone wise data ---------- */
$my_zone_scope = $fn->get_zone_scope($_SESSION['id']);
$selected_zone = isset($_GET['zone_id']) ? (int)$_GET['zone_id'] : 0;
if(!empty($my_zone_scope) && !in_array($selected_zone, $my_zone_scope)){
    $selected_zone = 0;
}
$scope_sql = !empty($my_zone_scope) ? " IN (".implode(',',$my_zone_scope).")" : "";
if($selected_zone > 0){
    $zone_where = " AND zone_id='".$selected_zone."'";
} else if(!empty($my_zone_scope)){
    $zone_where = " AND zone_id".$scope_sql;
} else {
    $zone_where = "";
}

$db->sql("SELECT id, name, status FROM zone ORDER BY name ASC");
$zones_list = $db->getResult();
if(empty($zones_list)) $zones_list = array();
if(!empty($my_zone_scope)){
    $zones_list = array_values(array_filter($zones_list, function($z) use ($my_zone_scope){ return in_array((int)$z['id'], $my_zone_scope); }));
}

/* Orders aggregated per zone */
$sql_zone_orders = "SELECT zone_id, COUNT(*) AS total_orders,
		SUM(active_status='delivered') AS delivered_orders,
		SUM(active_status='cancelled') AS cancelled_orders,
		SUM(CASE WHEN active_status='delivered' THEN final_total ELSE 0 END) AS sales
		FROM orders WHERE 1=1 ".$zone_where." GROUP BY zone_id";
$db->sql($sql_zone_orders);
$res_zone_orders = $db->getResult();
$zone_orders = array();
if($res_zone_orders){
	foreach($res_zone_orders as $row){
		$zone_orders[$row['zone_id']] = $row;
	}
}

/* Sellers aggregated per zone */
$sql_zone_sellers = "SELECT zone_id, COUNT(*) AS total_sellers FROM seller WHERE 1=1 ".$zone_where." GROUP BY zone_id";
$db->sql($sql_zone_sellers);
$res_zone_sellers = $db->getResult();
$zone_sellers = array();
if($res_zone_sellers){
	foreach($res_zone_sellers as $row){
		$zone_sellers[$row['zone_id']] = $row['total_sellers'];
	}
}

/* Delivery boys aggregated per zone */
$sql_zone_dbs = "SELECT zone_id, COUNT(*) AS total_dbs FROM delivery_boys WHERE 1=1 ".$zone_where." GROUP BY zone_id";
$db->sql($sql_zone_dbs);
$res_zone_dbs = $db->getResult();
$zone_boys = array();
if($res_zone_dbs){
	foreach($res_zone_dbs as $row){
		$zone_boys[$row['zone_id']] = $row['total_dbs'];
	}
}

/* Parcel requests aggregated per zone */
$sql_zone_parcels = "SELECT zone_id, COUNT(*) AS total_parcels FROM parcel_requests WHERE 1=1 ".$zone_where." GROUP BY zone_id";
$db->sql($sql_zone_parcels);
$res_zone_parcels = $db->getResult();
$zone_parcels = array();
if($res_zone_parcels){
	foreach($res_zone_parcels as $row){
		$zone_parcels[$row['zone_id']] = $row['total_parcels'];
	}
}

/* Food orders KPI - orders table (zone filtered when a zone is selected) */
if($selected_zone > 0){
	$food_kpi = $function->rows_count('orders', '*', "zone_id='".$selected_zone."'");
} else if(!empty($my_zone_scope)){
	$food_kpi = $function->rows_count('orders', '*', "zone_id".$scope_sql);
} else if($_SESSION['role'] == 'seller'){
	$food_kpi = $function->orders_count();
} else {
	$food_kpi = $function->rows_count('orders');
}

/* Products KPI - filtered through seller.zone_id */
if($selected_zone > 0){
	$products_kpi = $function->rows_count('products', '*', "seller_id IN (SELECT id FROM seller WHERE zone_id='".$selected_zone."')");
} else if(!empty($my_zone_scope)){
	$products_kpi = $function->rows_count('products', '*', "seller_id IN (SELECT id FROM seller WHERE zone_id".$scope_sql.")");
} else {
	$products_kpi = ($_SESSION['role'] == 'seller') ? $function->rows_count('products', '*', "seller_id = '$seller_id'") : $function->rows_count('products');
}

/* Customers KPI - distinct users having an address in the zone */
if($selected_zone > 0){
	$db->sql("SELECT COUNT(DISTINCT user_id) AS total FROM user_address WHERE zone_id='".$selected_zone."'");
	$res_cus = $db->getResult();
	$customers_kpi = isset($res_cus[0]['total']) ? $res_cus[0]['total'] : 0;
} else if(!empty($my_zone_scope)){
	$db->sql("SELECT COUNT(DISTINCT user_id) AS total FROM user_address WHERE zone_id".$scope_sql);
	$res_cus = $db->getResult();
	$customers_kpi = isset($res_cus[0]['total']) ? $res_cus[0]['total'] : 0;
} else {
	$customers_kpi = $function->rows_count('users');
}

/* Parcel Orders KPI - zone filtered */
if($selected_zone > 0){
	$parcel_kpi = $function->rows_count('parcel_requests', '*', "zone_id='".$selected_zone."'");
} else if(!empty($my_zone_scope)){
	$parcel_kpi = $function->rows_count('parcel_requests', '*', "zone_id".$scope_sql);
} else {
	$parcel_kpi = $function->rows_count('parcel_requests');
}

/* Total Orders = Food orders + Parcel orders */
$orders_kpi = $food_kpi + $parcel_kpi;
?>
<style>
.row.storeswitch {
    display: none;
}
</style>

<div class="row storeswitch">
               <span class="ss">Store Switch</span>
               <span>
               <label class="switch">
                 
               </label>
               </span>
         </div>
		<?php if($_SESSION['role'] != 'seller'){ ?>
		<div class="row small-spacing">
			<div class="col-xs-12">
				<div class="box box-default">
					<div class="box-header with-border">
						<h3 class="box-title">Zone Filter</h3>
						<form method="get" action="home.php" id="zone_filter_form" class="pull-right" style="margin-bottom:0;">
							<select name="zone_id" id="zone_id" class="form-control" style="width: 260px;" onchange="document.getElementById('zone_filter_form').submit();">
								<option value="">All Zones</option>
								<?php foreach($zones_list as $z){ ?>
									<option value="<?=$z['id']?>" <?=($selected_zone==$z['id']) ? 'selected' : ''?>><?=htmlspecialchars($z['name'])?></option>
								<?php } ?>
							</select>
							<noscript><button type="submit" class="btn btn-primary">Filter</button></noscript>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="row small-spacing" style="display:flex;flex-wrap:wrap;">
			<div class="col-xs-6" style="flex:1 1 0;min-width:190px;">
				<a href="orders.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-notepad text-inverse"></i>
						<h2 class="counter text-inverse"><?=$orders_kpi;?></h2>
						<p class="text">Orders</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<?php if($_SESSION['role'] != 'seller'){ ?>
			<div class="col-xs-6" style="flex:1 1 0;min-width:190px;">
				<a href="orders.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-shopping-cart text-inverse"></i>
						<h2 class="counter text-inverse"><?=$food_kpi;?></h2>
						<p class="text">Food Orders</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<?php } ?>
			<div class="col-xs-6" style="flex:1 1 0;min-width:190px;">
				<a href="products.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-dropbox text-success"></i>
						<h2 class="counter text-success"><?=$products_kpi;?></h2>
						<p class="text">Products</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<div class="col-xs-6" style="flex:1 1 0;min-width:190px;">

				<a href="customers.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-user text-primary"></i>
						<h2 class="counter text-primary"><?=$customers_kpi;?></h2>
						<p class="text">Customers</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<?php if($_SESSION['role'] != 'seller'){ ?>
			<div class="col-xs-6" style="flex:1 1 0;min-width:190px;">
				<a href="parcel-requests.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-package text-warning"></i>
						<h2 class="counter text-warning"><?=$parcel_kpi;?></h2>
						<p class="text">Parcel Orders</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<!-- /.col-lg-3 col-xs-12 -->
			<?php } ?>
			</div>
			<!-- /.row small-spacing (KPI Cards) -->
			<div class="row small-spacing">
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<div class="box box-info">
						<div class="box-header with-border">
							<h3 class="box-title">Weekly Sales</h3>
				
							</div>
					 <?php 
					 $chart_where = '';
					 $graph_where = '';
					if($_SESSION['role'] == 'seller'){
						$chart_where = "WHERE c.main_cat='$main_cat_id'";
						$graph_where = "AND seller_id='$seller_id'";
					}
					if($selected_zone > 0){
						$graph_where .= " AND zone_id='".$selected_zone."'";
					} else if(!empty($my_zone_scope)){
						$graph_where .= " AND zone_id".$scope_sql;
					}
					 $year = date("Y");
						$curdate = date('Y-m-d');
					  $sql = "SELECT SUM(final_total) AS total_sale,DATE(date_added) AS order_date FROM orders WHERE active_status = 'delivered' AND YEAR(date_added) = '$year' AND DATE(date_added)<'$curdate' $graph_where GROUP BY DATE(date_added) ORDER BY DATE(date_added) DESC  LIMIT 0,7";
						$db->sql($sql);
						$result_order = $db->getResult(); ?>
						<div class="tile-stats" style="padding:10px;">
							<div id="earning_chart" style="width:100%;height:350px;"></div>
						</div>
				</div>
				
			</div>
			<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
				<div class="box box-danger">
				<div class="box-header with-border">
								<h3 class="box-title">Category wise Product Count</h3>
				
							</div>
					<?php
					
					 $product_sub_where = '';
					 if($selected_zone > 0){
						$product_sub_where = "AND s.zone_id='".$selected_zone."'";
					 } else if(!empty($my_zone_scope)){
						$product_sub_where = "AND s.zone_id".$scope_sql;
					 }
					 $sql="SELECT `name`,(SELECT count(p.id) from `products` p LEFT JOIN seller s ON s.id=p.seller_id WHERE p.category_id = c.id ".$product_sub_where." ) as `product_count` FROM `category` c $chart_where";
						$db->sql($sql);
						$result_products = $db->getResult(); ?>
						<div class="tile-stats" style="padding:10px;">
							<div id="piechart" style="width:100%;height:350px;"></div>
						</div>
				</div>
				
			</div>
		</div>
		<!-- /.row small-spacing -->

		<?php if($_SESSION['role'] != 'seller'){ ?>
		<div class="row small-spacing">
			<div class="col-lg-12 col-xs-12">
				<div class="box box-success">
					<div class="box-header with-border">
						<h3 class="box-title"><?=($selected_zone > 0) ? 'Zone Wise Data - '.htmlspecialchars($zones_list[0]['name'] ?? '') : 'Zone Wise Data' ?></h3>
						<a href="zones.php" class="btn btn-sm btn-default btn-flat pull-right">Manage Zones</a>
					</div>
					<div class="box-body table-responsive">
						<?php
						$zone_stat = array();
						$col_zones = ($selected_zone > 0)
							? array_filter($zones_list, function($z) use ($selected_zone){ return $z['id'] == $selected_zone; })
							: $zones_list;
						$total_orders = $total_food = $total_delivered = $total_cancelled = $total_processing = $total_sales = $total_sellers = $total_boys = $total_parcels = 0;

						if($selected_zone > 0){
							$z = $col_zones ? array_values($col_zones)[0] : null;
							if($z){
								$o = isset($zone_orders[$z['id']]) ? $zone_orders[$z['id']] : array('total_orders'=>0,'delivered_orders'=>0,'cancelled_orders'=>0,'sales'=>0);
								$par = isset($zone_parcels[$z['id']]) ? $zone_parcels[$z['id']] : 0;
								$zone_stat[] = array(
									'name' => $z['name'],
									'status' => $z['status'],
									'orders_total' => $o['total_orders']+$par,
									'order' => $o['total_orders'],
									'delivered' => $o['delivered_orders'],
									'cancelled' => $o['cancelled_orders'],
									'processing' => $o['total_orders']-$o['delivered_orders']-$o['cancelled_orders'],
									'sales' => $o['sales'],
									'sellers' => isset($zone_sellers[$z['id']]) ? $zone_sellers[$z['id']] : 0,
									'boys' => isset($zone_boys[$z['id']]) ? $zone_boys[$z['id']] : 0,
									'parcels' => $par,
								);
							}
						} else {
							foreach($col_zones as $z){
								$oid = $z['id'];
								$o = isset($zone_orders[$oid]) ? $zone_orders[$oid] : array('total_orders'=>0,'delivered_orders'=>0,'cancelled_orders'=>0,'sales'=>0);
								$par = isset($zone_parcels[$oid]) ? $zone_parcels[$oid] : 0;
								$zone_stat[] = array(
									'name' => $z['name'],
									'status' => $z['status'],
									'orders_total' => $o['total_orders']+$par,
									'order' => $o['total_orders'],
									'delivered' => $o['delivered_orders'],
									'cancelled' => $o['cancelled_orders'],
									'processing' => $o['total_orders']-$o['delivered_orders']-$o['cancelled_orders'],
									'sales' => $o['sales'],
									'sellers' => isset($zone_sellers[$oid]) ? $zone_sellers[$oid] : 0,
									'boys' => isset($zone_boys[$oid]) ? $zone_boys[$oid] : 0,
									'parcels' => $par,
								);
							}
						}
						if(empty($zone_stat)) $zone_stat = array();
						?>
						<table class="table table-bordered table-hover table-striped">
							<thead>
								<tr>
									<th>#</th>
									<th>Zone</th>
									<th>Status</th>
									<th>Orders</th>
									<th>Food Orders</th>
									<th>Parcel</th>
									<th>Delivered</th>
									<th>Processing</th>
									<th>Cancelled</th>
									<th>Sales (<?=htmlspecialchars($settings['currency'])?>)</th>
									<th>Sellers</th>
									<th>Delivery Boys</th>
								</tr>
							</thead>
							<tbody>
								<?php if(empty($zone_stat)){ ?>
									<tr><td colspan="12" class="text-center">No data found.</td></tr>
								<?php } else { $sn = 1; foreach($zone_stat as $st){
									$total_orders += $st['orders_total'];
									$total_food += $st['order'];
									$total_parcels += $st['parcels'];
									$total_delivered += $st['delivered'];
									$total_cancelled += $st['cancelled'];
									$total_processing += $st['processing'];
									$total_sales += $st['sales'];
									$total_sellers += $st['sellers'];
									$total_boys += $st['boys'];
								?>
									<tr>
										<td><?=$sn++;?></td>
										<td><?=htmlspecialchars($st['name'])?></td>
										<td><?=($st['status']==1)?'<span class="label label-success">Active</span>':'<span class="label label-default">Disabled</span>'?></td>
										<td><?=$st['orders_total']?></td>
										<td><?=$st['order']?></td>
										<td><?=$st['parcels']?></td>
										<td><?=$st['delivered']?></td>
										<td><?=$st['processing']?></td>
										<td><?=$st['cancelled']?></td>
										<td><?=number_format($st['sales'],2)?></td>
										<td><?=$st['sellers']?></td>
										<td><?=$st['boys']?></td>
									</tr>
								<?php } } ?>
							</tbody>
							<?php if(!empty($zone_stat)){ ?>
							<tfoot>
								<tr>
									<th colspan="3">Total</th>
									<th><?=$total_orders?></th>
									<th><?=$total_food?></th>
									<th><?=$total_parcels?></th>
									<th><?=$total_delivered?></th>
									<th><?=$total_processing?></th>
									<th><?=$total_cancelled?></th>
									<th><?=number_format($total_sales,2)?></th>
									<th><?=$total_sellers?></th>
									<th><?=$total_boys?></th>
								</tr>
							</tfoot>
							<?php } ?>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>

		<div class="row small-spacing">
			<div <?php if($_SESSION['role'] != 'seller'){?> style="display:none;" <?php }?> id="seller-orders" class="col-lg-12 col-xs-12">
				<div class="box box-info">
							<div class="box-header with-border">
								<h3 class="box-title">Latest Orders</h3>
				<form method="POST" id="filter_form" name="filter_form">
    
                <div class="form-group pull-right">
                    <!--<h3 class="box-title">Filter by status</h4>-->
                        <select id="filter_order" name="filter_order" placeholder="Select Status" required class="form-control" style="width: 300px;">
                            <option value="">All Orders</option>
                            <option value='received'>Received</option>
                            <option value='processed'>Processed</option>
                            <option value='shipped'>Shipped</option>
                            <option value='delivered'>Delivered</option>
                            <option value='cancelled'>Cancelled</option>
                        </select>
                        
                    <!-- <input type="submit" name="filter_btn" id="filter_btn" value="Filter" class="btn btn-primary btn-md"> -->
                </div>
                </form>
							</div>
							<div class="box-body">
								<div id="toolbar">
									<form method="post">
										<select class='form-control' id="category_id" name="category_id" placeholder="Select Category" required style="display: none;">
											<?php
												$Query="select name, id from category";
												$db->sql($Query);
                                                $result=$db->getResult();
												if($result)
												{
												?>
											<option value="">All Products</option>
                                            <?php foreach($result as $row){?>
                                                 <option value='<?=$row['id']?>'><?=$row['name']?></option>
                                                <?php }} 
                                                    ?>
											
										</select>
									</form>
								</div>
								<div class="table-responsive">
									<table class="table no-margin" id='orders_table' data-toggle="table" 
										data-url="api-firebase/get-bootstrap-table-data.php?table=orders"
										data-page-list="[5, 10, 20, 50, 100, 200]"
										data-show-refresh="true" data-show-columns="true"
										data-side-pagination="server" data-pagination="true"
										data-search="true" data-trim-on-search="false"
										data-sort-name="id" data-sort-order="desc"
										data-toolbar="#toolbar" data-query-params="queryParams"
										>
										<thead>
											<tr>
												<th data-field="id" data-sortable='true'>O.ID</th>
												<th data-field="user_id" data-sortable='true' data-visible="false">User ID</th>
												 <th data-field="qty" data-sortable='true' data-visible="false">Qty</th>
                                                <th data-field="name" data-sortable='true'>U.Name</th>
												<th data-field="mobile" data-sortable='true' data-visible="true">Mob.</th>
												<th data-field="items" data-sortable='true' data-visible="false">Items</th>
												<th data-field="total" data-sortable='true' data-visible="true">Total(<?=$settings['currency']?>)</th>
												<th data-field="delivery_charge" data-sortable='true'>D.Chrg</th>
												<th data-field="tax" data-sortable='false'>Tax <?=$settings['currency']?>(%)</th>
												<th data-field="discount" data-sortable='true' data-visible="true">Disc.<?=$settings['currency']?>(%)</th>
												<th data-field="promo_code" data-sortable='true' data-visible="false">Promo Code</th>
												<th data-field="promo_discount" data-sortable='true' data-visible="true">Promo Disc.(<?=$settings['currency']?>)</th>
												<th data-field="wallet_balance" data-sortable='true' data-visible="true">Wallet Used(<?=$settings['currency']?>)</th>
												<th data-field="final_total" data-sortable='true'>F.Total(<?=$settings['currency']?>)</th>
												<th data-field="deliver_by" data-sortable='true' data-visible='false'>Deliver By</th>
												<th data-field="payment_method" data-sortable='true' data-visible="true">P.Method</th>
												<th data-field="address" data-sortable='true' data-visible="false">Address</th>
												<th data-field="delivery_time" data-sortable='true' data-visible='false'>D.Time</th>
												<th data-field="status" data-sortable='true' data-visible='false'>Status</th>
												<th data-field="active_status" data-sortable='true' data-visible='true'>A.Status</th>
												<th data-field="date_added" data-sortable='true' data-visible="false">O.Date</th>
												<th data-field="operate">Action</th>
                                               
												
											</tr>
										</thead>
									</table>
								</div>
							</div>
							<div class="box-footer clearfix">
								<a href="orders.php" class="btn btn-sm btn-default btn-flat pull-right">View All Orders</a>
							</div>
						</div>
			</div>
			<!-- /.col-lg-6 col-xs-12 -->
		</div>
		<!-- /.row -->		
		<script>
	$('#filter_order').on('change',function(){
    $('#orders_table').bootstrapTable('refresh');
    });
</script>
<script>
function queryParams(p){
	return {
		"filter_order": $('#filter_order').val(),
		"filter_zone": <?=($selected_zone > 0) ? "'".$selected_zone."'" : "''"?>,
		limit:p.limit,
		sort:p.sort,
		order:p.order,
		offset:p.offset,
		search:p.search
	};
}
</script>
<script type="text/javascript" src="dist/scripts/chart.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawPieChart);

    function drawPieChart() {

        var data1 = google.visualization.arrayToDataTable([
            ['Product', 'Count'],
            <?php
                foreach($result_products as $row){ echo "['".$row['name']."',".$row['product_count']."],";}
            ?>
        ]);
    
        var options1 = {
          title: 'Category Wise Product\'s Count',
          fontName: 'Poppins',
          fontSize: 14,
          color: '#333',
          bold: false,
          is3D: true
        };
       
    
        var chart1 = new google.visualization.PieChart(document.getElementById('piechart'));
       
        chart1.draw(data1, {fontName:'Poppins'});
    }
</script>

<script>
    google.charts.load('current', {'packages':['bar']});
    google.charts.setOnLoadCallback(drawChart);
    function drawChart() {
        var rows = [];
        <?php foreach($result_order as $row){
            $date = date('d-M', strtotime($row['order_date']));
            echo "rows.push(['".$date."',".$row['total_sale']."]);";
        } ?>
        if (rows.length === 0) {
            document.getElementById('earning_chart').innerHTML = '<div style="text-align:center;padding-top:120px;color:#999;">No delivered sales data for the selected filter.</div>';
            return;
        }
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Total Sale In <?=$settings['currency']?>']
        ].concat(rows));
        var options = {
            chart: {
                title: 'Weekly Sale',
                subtitle: 'Total Sale In Last Week (Month: <?php echo date("M"); ?>)',
				fontName: 'Poppins'
            }
        };
    var chart = new google.charts.Bar(document.getElementById('earning_chart'));
    chart.draw(data,{fontName:'Poppins'});
    }
</script>
<script>

$("body").on('click', '#ch', function(){
    var checkBox = document.getElementById("ch");
   if (checkBox.checked == true){
    var storeswitch=1;
  } else {
    var storeswitch=0;
  }
  var date_time="";

  if(storeswitch == 0){
    var todayDate = new Date();
    var getTodayDate = todayDate.getDate();
    var getTodayMonth =  todayDate.getMonth()+1;
    var getTodayFullYear = todayDate.getFullYear();
    var getCurrentHours = todayDate.getHours();
    var getCurrentMinutes = todayDate.getMinutes();
    var getCurrentAmPm = getCurrentHours >= 12 ? 'PM' : 'AM';
    getCurrentHours = getCurrentHours % 12;
    getCurrentHours = getCurrentHours ? getCurrentHours : 12; 
    getCurrentMinutes = getCurrentMinutes < 10 ? '0'+getCurrentMinutes : getCurrentMinutes;
    var getCurrentDateTime = getTodayDate + '-' + getTodayMonth + '-' + getTodayFullYear + ' ' + getCurrentHours + ':' + getCurrentMinutes + ' ' + getCurrentAmPm;


      var userInput = prompt("Shop reopen date and time:", getCurrentDateTime);
      if(userInput == ""){
          alert('Enter reopen date and time');return false;
      }
      date_time = userInput;
  }
//   alert(storeswitch);
  $.ajax({
    url:'getstorecondition.php',
    type: 'POST',
      dataType: 'json',
      data: {'storeswitch':storeswitch,'date_time':date_time},
      success: function(result){
   }});
  });
  

$(document).ready(function(){ 
    // alert("ram");
    $.ajax({
    url:'getstorecondition.php',
    type: 'POST',
      dataType: 'json',
    //   data: {'storeswitch':storeswitch},
      success: function(result){
        //   var res=JSON.parse(result);
          console.log(result.storecondition);
          if(result.storecondition==0){
            //   alert("ram")
            $('.switch').html('<input type="checkbox" id="ch"><span class="slider round"></span>');
            $('#ch').prop('unchecked', true);
          }else{
            //   alert("prasath");
            $('.switch').html('<input type="checkbox" id="ch" checked><span class="slider round"></span>');
          }
   }});
});
</script>
		<?php include"footer.php";?>