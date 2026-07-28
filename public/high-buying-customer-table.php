<script>
    $(document).ready(function () {
        var date = new Date();
        var currentMonth = date.getMonth() - 10;
        var currentDate = date.getDate();
        var currentYear = date.getFullYear() - 10;

        
    });
</script>
<script>
    $(document).ready(function () {
        var date = new Date();
        var currentMonth = date.getMonth() - 10;
        var currentDate = date.getDate();
        var currentYear = date.getFullYear() - 10;

       
    });
</script>
<script language="javascript">
    function printpage()
    {
        window.print();
    }
    function submitForm(){ 
  // Call submit() method on <form id='myform'>
  document.getElementById('myform').submit(); 
} 
</script>
<!-- Main row -->

<div class="row">
    <!-- Left col -->
    <div class="col-xs-12">
        <?php if($permissions['reports']['read']==1){?>
            <div class="box-header">
                <div class="box-tools">
                    <ul class="list-inline margin-bottom-0">
                    <form style="display: inline-block;" method="post" action="high-buying-customer.php" name="form1">
                        <ul class="list-inline margin-bottom-0">
							<li class="form-group">
                            From<input class="form-control" type="date" id="from" name="start_date" placeholder="YYYY/MM/DD" autocomplete="off" value="<?php if(isset($_POST['start_date'])){echo $_POST['start_date'] ; } ?>" required/>
							</li>
							<li class="form-group">
                            To<input class="form-control" type="date" id="to" name="end_date" placeholder="YYYY/MM/DD" autocomplete="off" value="<?php if(isset($_POST['end_date'])){echo $_POST['end_date'] ; } ?>"  required/>
							</li>
							<li class="form-group">
							<button type="submit" class="form-control btn btn-default"><i class="fa fa-search"></i></button>
							</li>
						</ul>
                    </form>
                    <form method="post" style="display: inline-block;" action="high-buying-customer.php" id="myform">
                        <ul class="list-inline margin-bottom-0">
							<li class="form-group">
							    <select class="form-control" name="filter_by" onchange="submitForm()">
							        <option value="">SELECT</option>
							       <option value="day"   <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'day'){echo 'selected' ; } ?>>Today</option>
							       <option value="week"  <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'week'){echo 'selected' ; } ?>>This Week</option>
							       <option value="month" <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'day'){echo 'month' ; } ?>>This Month</option>
							    </select>
							</li>
							</ul>
							 </form>
						</ul>
                </div>
            </div>
            <?php } else { ?>
        <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view reports</div>
            <?php } ?>
    </div>
</div>

<?php
if (isset($_POST)) {
    if(isset($_POST['start_date']) && isset($_POST['end_date'])){
    $month = $_POST['start_date'];
    $daysago = $_POST['end_date'];
    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }
    if (empty($keyword)) {
        	$sql_query="SELECT
        	users.`name`, 
        	users.email, 
        	users.id, 
        	users.country_code, 
        	users.mobile, 
        	COUNT(orders.id) AS orders_count,
        	SUM(orders.final_total) as amount
            FROM
        	users
        	INNER JOIN
        	orders
        	ON 
        		users.id = orders.user_id
        		WHERE orders.date_added < '" . $daysago . "' and orders.date_added >'" . $month . "'
        		GROUP BY orders.user_id
        		ORDER BY orders_count DESC";
        		 $sql_daily = "SELECT
	COUNT(orders.id) AS num
        FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE orders.date_added < '" . $daysago . "' and orders.date_added >'" . $month . "'
		GROUP BY orders.user_id";
    $db->sql($sql_daily);
    $total_daily = $db->getResult();
    $total_daily = $total_daily[0]['num'];
    } else {
		$sql_query="SELECT
    	users.`name`, 
    	users.email, 
    	users.id, 
    	users.country_code, 
    	users.mobile, 
    	COUNT(orders.id) AS orders_count,
    	SUM(orders.final_total) as amount
    FROM
    	users
    	INNER JOIN
    	orders
    	ON 
    		users.id = orders.user_id
    		WHERE orders.date_added < '" . $daysago . "' and orders.date_added >'" . $month . "'
    		AND name LIKE '%".$keyword."%' 
    		GROUP BY orders.user_id
    		ORDER BY orders_count DESC";
     }
    }else if(isset($_POST['filter_by'])){
        $cdate=date('Y-m-d');
            if($_POST['filter_by']=='day'){
                $sql_query="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added > '" . $cdate . "' 
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }else if($_POST['filter_by']=='week'){
                $odate=date('Y-m-d',strtotime('last sunday'));
                $sql_query="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added >'" . $odate . "'
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }else if($_POST['filter_by']=='month'){
                $odate=date('Y-m-01');
                $sql_query="SELECT
                    	users.`name`, 
                    	users.email, 
                    	users.id, 
                    	users.country_code, 
                    	users.mobile, 
                    	COUNT(orders.id) AS orders_count,
                    	SUM(orders.final_total) AS amt,
                    	FORMAT(SUM(orders.final_total),0) as amount
                    FROM
                    	users
                    	INNER JOIN
                    	orders
                    	ON 
                    		users.id = orders.user_id
                        	WHERE orders.active_status!='cancelled' AND orders.date_added >'" . $odate . "'
                        	GROUP BY orders.user_id
		                    ORDER BY amt DESC";
            }
             $sql_daily = "SELECT
	COUNT(orders.id) AS num
        FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE orders.date_added >'" . $odate . "'
		GROUP BY orders.user_id";
    $db->sql($sql_daily);
    $total_daily = $db->getResult();
    $total_daily = $total_daily[0]['num'];
    }
        // Execute query
        //echo $sql_query;
       // exit;
        $db->sql($sql_query);
        // store result 
        $res=$db->getResult();  
        // get total records
        $total_records = $db->numRows();
    // check page parameter
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    // number of data that will be display per page
    $offset = 20;

    //lets calculate the LIMIT for SQL, and save it $from
    if ($page) {
        $from = ($page * $offset) - $offset;
    } else {
        //if nothing was given in page request, lets load the first page
        $from = 0;
    }
    // get all data from pemesanan table
  /*  if (empty($keyword)) {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE orders.date_added < '" . $daysago . "' and orders.date_added >'" . $month . "'
		GROUP BY orders.user_id
		ORDER BY orders_count DESC
                LIMIT ".$from.", ".$offset."";
    } else {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE orders.date_added < '" . $daysago . "' and orders.date_added >'" . $month . "'
		AND name LIKE '%".$keyword."%' 
		GROUP BY orders.user_id
		ORDER BY orders_count DESC
                LIMIT ".$from.", ".$offset."";
    }*/

        $db->sql($sql_query);
        $res=$db->getResult();

        // for paging purpose
        $total_records_paging = $total_records;

    // if no data on database show "Tidak Ada Pemesanan"
    if ($total_records_paging == 0) {
        ?>
        <div class="content-header">
            <h1>There is No Records for this date</h1>
            <hr />
        </div>
        <?php
        // otherwise, show data
    } else {
        $row_number = $from + 1;
        ?>
        <div class="content-header">
            <hr/>
        </div>

        <!-- search form -->
        <div id="wrapper">
            <!-- Main row -->
<div class="row small-spacing">
                <!-- Left col -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Records</h3>
<!--                            <h3 class="box-title">Total_Sale : <?php // echo $total_daily; ?></h3>-->
                            <div class="box-tools">
                                <form  method="get">
                                    <ul class="list-inline margin-bottom-0">
										<li class="form-group">
											<input type="text" name="keyword" class="form-control input-sm" placeholder="Search">
										</li>
										<li class="form-group">
										<button type="submit" class="btn-sm"><i class="fa fa-search"></i></button>
										</li>
									</ul>
                                </form>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
									<th>Orders Count</th>
                                    <th>Total Amount</th>
									<th style="text-align:right">Orders</th>
                                    

                                </tr>
                                <?php
                                $count = 1;
                                foreach($res as $row) {
                                
                                    ?>
                                    <tr>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['mobile']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['orders_count']; ?></td>
                                        <td style="text-align:right"><?php echo number_format($row['amount'],2); ?></td>
                                       
										<td><a class="btn btn-sm" href="get-orders-by-user.php?id=<?=$row['id'];?>"> Last 3 Months Orders</a></td>
                                    </tr>
                                    <?php
                                    $count++;
                                }
                                }
                                
    
                            
                            ?>
                            
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-sx-12">
                <h4>
                    <?php
                    // for pagination purpose
                 //  $function->doPages($offset, 'sales-report.php', '', $total_records, $keyword);
                    ?>
                </h4>
            </div>
            <div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
        </div><!-- /.row (main row) -->

    </div><!-- /.content --> 
    <?php
   $db->disconnect();
}else{

    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }

    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		GROUP BY orders.user_id
		ORDER BY orders_count DESC";
    } else {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		WHERE name LIKE '%".$keyword."%' 
		GROUP BY orders.user_id
		ORDER BY orders_count DESC";
    }
        // Execute query
        $db->sql($sql_query);
        // store result 
        $res=$db->getResult();
        
        // get total records
        $total_records = $db->numRows();
        // echo $total_records;
    

    // check page parameter
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    } else {
        $page = 1;
    }

    // number of data that will be display per page
    $offset = 20;

    //lets calculate the LIMIT for SQL, and save it $from
    if ($page) {
        $from = ($page * $offset) - $offset;
    } else {
        //if nothing was given in page request, lets load the first page
        $from = 0;
    }
                                          
    $sql_daily = "SELECT
	COUNT(orders.id) AS num
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		GROUP BY orders.user_id";
    $db->sql($sql_daily);
    $total_daily = $db->getResult();
    $total_daily = $total_daily[0]['num'];
   
    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		GROUP BY orders.user_id
		ORDER BY orders_count DESC
                LIMIT ".$from.", ".$offset."";
    } else {
        $sql_query = "SELECT
	users.`name`, 
	users.email, 
	users.id, 
	users.country_code, 
	users.mobile, 
	COUNT(orders.id) AS orders_count,
	SUM(orders.final_total) as amount
FROM
	users
	INNER JOIN
	orders
	ON 
		users.id = orders.user_id
		AND users.name LIKE '%".$keyword."%' 
		GROUP BY orders.user_id
		ORDER BY orders_count DESC
                LIMIT ".$from.", ".$offset."";
    }
    $db->sql($sql_query);
    $res=$db->getResult();
    $total_records_paging = 1;
        // for paging purpose
        $total_records_paging = $total_records;
        
    

    // if no data on database show "Tidak Ada Pemesanan"
    if ($total_records_paging == 0) {
        ?>
        <div class="content-header">
            <h1>There is No Records</h1>
            <hr />
        </div>
        <?php
        // otherwise, show data
    } else {
        $row_number = $from + 1;
        ?>
        <!-- search form -->
        <div id="wrapper">
            <!-- Main row -->

            <div class="row small-spacing">
                <!-- Left col -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Records</h3>
<!--                            <h3 class="box-title">Total_Sale : <?php //  echo $total_daily; ?></h3>-->
                            <div class="box-tools">
                                <form  method="get">
                                    <ul class="list-inline margin-bottom-0">
										<li class="form-group">
											<input type="text" name="keyword" class="form-control input-sm" placeholder="Search">
										</li>
										<li class="form-group">
										<button type="submit" class="btn-sm"><i class="fa fa-search"></i></button>
										</li>
									</ul>
                                </form>
                            </div>
                        </div><!-- /.box-header -->
                        <div class="box-body table-responsive">
                            <table class="table table-hover">
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
									<th>Orders Count</th>
                                    <th style="text-align:right">Total Amount</th>
                                    <th>Orders</th>

                                </tr>
                                <?php
                                $count = 1;
                                    foreach($res as $row){
                                   
                                    ?>
                                    <tr>
                                         <td><?php echo $row['name']; ?></td>
                                        <td><?php echo $row['mobile']; ?></td>
                                        <td><?php echo $row['email']; ?></td>
                                        <td><?php echo $row['orders_count']; ?></td>
                                   
                                        <td style="text-align:right"><?php echo number_format($row['amount'],2); ?></td>
										<td><a class="btn btn-xs" href="get-orders-by-user.php?id=<?=$row['id'];?>"> Last 3 Months Orders</a></td>
                                        
                                    </tr>
                                    <?php
                                    $count++;
                                
                            }
 
                            
    }
                            ?>
                        </table>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div>
            <div class="col-sx-12">
                <h4>
                    <?php
                    // for pagination purpose
//                    $function->doPages($offset, 'sales-report.php', '', $total_records, $keyword);
                    ?>
                </h4>
            </div>
            <div class="separator"> </div>
            <!-- right col (We are only adding the ID to make the widgets sortable)-->
        </div><!-- /.row (main row) -->

    </div><!-- /.content --> 
    <?php
     $db->disconnect();
}
?>
