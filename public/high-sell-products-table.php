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
</script>
<!-- Main row -->

<div class="row">
    <!-- Left col -->
    <div class="col-xs-12">
        <?php if($permissions['reports']['read']==1){?>
            <div class="box-header">
                <div class="box-tools">
                    <form method="post" action="high-sell-products.php" name="form1">
                        <ul class="list-inline margin-bottom-0">
							<li class="form-group">
                            From<input class="form-control" type="date" id="from" name="start_date" placeholder="YYYY/MM/DD" autocomplete="off" required/>
							</li>
							<li class="form-group">
                            To<input class="form-control" type="date" id="to" name="end_date" placeholder="YYYY/MM/DD" autocomplete="off" required/>
							</li>
							<li class="form-group">
							<button type="submit" class="form-control btn btn-default"><i class="fa fa-search"></i></button>
							</li>
						</ul>
                    </form>
                </div>
            </div>
            <?php } else { ?>
        <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view reports</div>
            <?php } ?>
    </div>
</div>

<?php
if (isset($_POST) && isset($_POST['start_date']) && isset($_POST['end_date'])) {
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
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled' AND oi.date_added < '" . $daysago . "' and oi.date_added >'" . $month . "'
	GROUP BY v.id
	ORDER BY qty DESC";
    } else {
		$sql_query="SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled' AND oi.date_added < '" . $daysago . "' and oi.date_added >'" . $month . "'
	AND p.name LIKE '%".$keyword."%' 
	GROUP BY v.id
	ORDER BY qty DESC";
     }
        // Execute query
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
    
    $month = $_POST['start_date'];
    $daysago = $_POST['end_date'];
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
     
    
    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled' AND oi.date_added < '" . $daysago . "' and oi.date_added >'" . $month . "'
	GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$from.", ".$offset."";
    } else {
        $sql_query = "SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled' AND oi.date_added < '" . $daysago . "' and oi.date_added >'" . $month . "'
	AND p.name LIKE '%".$keyword."%' 
	GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$from.", ".$offset."";
    }

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
                                    <th>Product Name</th>
									<th>Unit</th>
                                    <th>quantity</th>
                                    <th>Order Count</th>
                                    
                                    

                                </tr>
                                <?php
                                $count = 1;
                                foreach($res as $row) {
                                
                                    ?>
                                    <tr>
                                        <td><?php echo $row['pname']; ?></td>
                                        <td><?php echo $row['measurement']." ".$row['mesurement_unit_name']; ?></td>
                                        <td><?php echo $row['qty']; ?></td>
                                        <td><?php echo $row['order_count']; ?></td>                                       
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
} else {

    if (isset($_GET['keyword'])) {
        // check value of keyword variable
        $keyword = $_GET['keyword'];
    } else {
        $keyword = "";
    }

    // get all data from pemesanan table
    if (empty($keyword)) {
        $sql_query = "SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled'
	GROUP BY v.id
	ORDER BY qty DESC";
    } else {
        $sql_query = "SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled'
	AND p.name LIKE '%".$keyword."%' 
	GROUP BY v.id
	ORDER BY qty DESC";
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
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled'
	GROUP BY v.id
	ORDER BY qty DESC
                LIMIT ".$from.", ".$offset."";
    } else {
        $sql_query = "SELECT
	p.name,
	v.product_id,
	sum(oi.quantity) AS qty,
	v.measurement,
	count(o.id) AS order_count,
	p.NAME AS pname,(
	SELECT
		short_code 
	FROM
		unit un 
	WHERE
		un.id = v.measurement_unit_id 
	) AS mesurement_unit_name 
FROM
	`order_items` oi
	JOIN orders o ON o.id = oi.order_id
	JOIN product_variant v ON oi.product_variant_id = v.id
	JOIN products p ON p.id = v.product_id
	WHERE oi.active_status!='cancelled'
	AND p.name LIKE '%".$keyword."%' 
	GROUP BY v.id
	ORDER BY qty DESC
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
                                    <th>Unit</th>
                                    <th>Quantity</th>
                                    <th>Order Count</th>
                                    

                                </tr>
                                <?php
                                $count = 1;
                                    foreach($res as $row){
                                   
                                    ?>
                                    <tr>
                                         <td><?php echo $row['pname']; ?></td>
                                        <td><?php echo $row['measurement']." ".$row['mesurement_unit_name']; ?></td>
                                        <td><?php echo $row['qty']; ?></td>
                                        <td><?php echo $row['order_count']; ?></td>
                                        
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
