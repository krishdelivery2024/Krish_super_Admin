<?php 
$page="Home";
include"header.php";?>
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
		<div class="row small-spacing">
			<div class="col-lg-4 col-xs-12">
				<a href="orders.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-notepad text-inverse"></i>
						<h2 class="counter text-inverse"><?=$function->orders_count();?></h2>
						<p class="text">Orders</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<div class="col-lg-4 col-xs-12">
				<a href="products.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-dropbox text-success"></i>
						<h2 class="counter text-success"><?= ($_SESSION['role'] == 'seller') ? $function->rows_count('products', '*', "seller_id = '$seller_id'") : $function->rows_count('products') ?></h2>
						<p class="text">Products</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<div class="col-lg-4 col-xs-12">

				<a href="customers.php"><div class="box-content">
					<div class="statistics-box with-icon">
						<i class="ico ti-user text-primary"></i>
						<h2 class="counter text-primary"><?=$function->rows_count('users');?></h2>
						<p class="text">Customers</p>
					</div>
					<!-- .statistics-box .with-icon -->
				</div></a>
				<!-- /.box-content -->
			</div>
			<!-- /.col-lg-3 col-xs-12 -->
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
					
					 $sql="SELECT `name`,(SELECT count(id) from `products` p WHERE p.category_id = c.id ) as `product_count` FROM `category` c $chart_where";
						$db->sql($sql);
						$result_products = $db->getResult(); ?>
						<div class="tile-stats" style="padding:10px;">
							<div id="piechart" style="width:100%;height:350px;"></div>
						</div>
				</div>
				
			</div>
		</div>
		<!-- /.row small-spacing -->

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
        var data = google.visualization.arrayToDataTable([
            ['Date', 'Total Sale In <?=$settings['currency']?>'],
            <?php foreach($result_order as $row){
                $date = date('d-M', strtotime($row['order_date']));
                echo "['".$date."',".$row['total_sale']."],"; 
            } ?>]);
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