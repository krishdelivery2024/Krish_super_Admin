<?php
include_once('includes/functions.php');
?>
<?php
// create object of functions class
$function = new functions;
include_once('includes/custom-functions.php');
$fn = new custom_functions;

// create array variable to store data from database
$data = array();
if (isset($_GET['keyword'])) {
    // check value of keyword variable
    $keyword = $function->sanitize($_GET["keyword"]);
} else {
    $keyword = "";
}

/* Zone filter + sub admin zone scope */
$my_zone_scope = $fn->get_zone_scope(isset($_SESSION['id']) ? $_SESSION['id'] : 0);
$selected_zone = (isset($_GET['zone_id']) && is_numeric($_GET['zone_id'])) ? (int)$_GET['zone_id'] : 0;
if(!empty($my_zone_scope) && !in_array($selected_zone, $my_zone_scope)){
    $selected_zone = 0;
}
$txn_zone_sql = ($selected_zone > 0) ? " AND o.`zone_id`='".$selected_zone."'" : "";
if(!empty($my_zone_scope)){
    $txn_zone_sql .= " AND o.`zone_id` IN (".implode(',', array_map('intval', $my_zone_scope)).")";
}
$txn_from = "FROM transactions t INNER JOIN users u ON t.user_id = u.id LEFT JOIN orders o ON o.id = t.order_id";
$db->sql("SELECT id, name, status FROM zone ORDER BY name ASC");
$res_zone = $db->getResult();
if(empty($res_zone)) $res_zone = array();
if(!empty($my_zone_scope)){
    $res_zone = array_values(array_filter($res_zone, function($z) use ($my_zone_scope){ return in_array((int)$z['id'], $my_zone_scope); }));
}
$zone_names = array();
foreach($res_zone as $zr){ $zone_names[(int)$zr['id']] = $zr['name']; }

if (empty($keyword)) {
    $sql_query = "SELECT count(*) as total_records ".$txn_from." WHERE 1=1".$txn_zone_sql;
} else {
    $sql_query = "SELECT count(*) as total_records ".$txn_from." WHERE (t.id LIKE '%".$keyword."%' OR t.user_id LIKE '%".$keyword."%' OR u.name LIKE '%".$keyword."%' OR o.`zone_id` LIKE '%".$keyword."%')".$txn_zone_sql;
}
$db->sql($sql_query);
    $res = $db->getResult();
    foreach($res as $row){
        $total_records = $row['total_records'];
    }
// check page parameter
if (isset($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

// number of data that will be display per page     
$offset = 10;

//lets calculate the LIMIT for SQL, and save it $from
if ($page) {
    $from = ($page * $offset) - $offset;
} else {
    //if nothing was given in page request, lets load the first page
    $from = 0;
}

// get all data from reservation table

if (empty($keyword)) {
                     $sql_query = "SELECT t.id,u.name,t.user_id,t.order_id,t.type,t.payu_txn_id,t.amount,t.status,t.message,t.transaction_date,o.`zone_id` ".$txn_from." WHERE 1=1".$txn_zone_sql." ORDER BY t.id ASC ";
} else {
    $sql_query = "SELECT t.id,u.name,t.user_id,t.order_id,t.type,t.payu_txn_id,t.amount,t.status,t.message,t.transaction_date,o.`zone_id` ".$txn_from." WHERE (t.id LIKE '%".$keyword."%' OR t.user_id LIKE '%".$keyword."%' OR u.name LIKE '%".$keyword."%' OR o.`zone_id` LIKE '%".$keyword."%')".$txn_zone_sql." ORDER BY t.id ASC";
                    
            //$sql_query = "SELECT t.id,u.name,user_id,order_id,type,payu_txn_id, amount, t.status, message, transaction_date FROM transactions t INNER JOIN users u ON t.user_id = u.id where t.id LIKE '%".$keyword."%' ORDER BY id ASC LIMIT ".$from.",".$offset."";
}
    // Execute query
    $db->sql($sql_query);
    // store result 
    $res = $db->getResult();

    // for paging purpose
    $total_records_paging = $total_records;



// if no data on database show "No Reservation is Available"
    if($permissions['transactions']['read']==1){
if ($total_records_paging == 0) {
    ?>
    <div class="content-header">
        <h1>
            Transaction Not Available
        </h1>
        <hr />
        <?php
        // otherwise, show data
    } else {
        $row_number = $from + 1;
        ?>
            <!-- Main row -->

            <div class="row">
                <!-- Left col -->
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">

                            <div class="box-tools">

                            </div>
                        </div><!-- /.box-header -->

                        <div class="box-body table-responsive">
                            <div class="form-group" style="display:inline-block;margin-bottom:10px;">
                                <label for="trans_txn_zone" class="control-label">Filter By Zone:</label>
                                <select id="trans_txn_zone" class="form-control" style="width:220px;display:inline-block;margin-left:5px;">
                                    <option value="">All Zones</option>
                                    <?php if(!empty($res_zone)){ foreach($res_zone as $tz){ ?>
                                        <option value="<?=$tz['id']?>" <?=($selected_zone==$tz['id'])?'selected':''?>><?=htmlspecialchars($tz['name'])?></option>
                                    <?php }} ?>
                                </select>
                            </div>
                            <table class="table table-hover" data-toggle="table"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true"
                        data-side-pagination="client" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-show-export="true"
                        data-export-types='["excel","pdf"]'
                        >
                                <thead>
                                    <th>Transaction ID</th>
                                    <th>User Name</th>
                                    <th>Zone</th>
                                    <th>Order ID</th>
                                    <th>Type</th>
                                    <th>TXN ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                    <th>Transaction Date</th>
                                </thead>
                                <tbody>
                                <?php
                                // get all data using foreach loop
                            
                                $count = 1;
                                
                                foreach($res as $row){?>
                                       
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['name']; ?></td>
                                        <td><?php echo isset($zone_names[(int)$row['zone_id']]) ? $zone_names[(int)$row['zone_id']] : '-'; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['type']; ?></td>
                                        <td><?php echo $row['payu_txn_id']; ?></td>
                                        <td><?php echo $row['amount']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td><?php echo $row['message']; ?></td>
                                        <td><?php echo $row['transaction_date']; ?></td>
                                    </tr>
                                    <?php
                                    $count++;
                                }?>
                               </tbody>

                            </table>
                            <script>
                            $(document).ready(function(){
                                $('#trans_txn_zone').on('change', function(){
                                    var z = $(this).val();
                                    window.location.href = 'transaction.php' + (z ? '?zone_id=' + z : '');
                                });
                            });
                            </script>
                        </div><!-- /.box-body -->
                    </div><!-- /.box -->
                </div>
				
                <div class="col-sx-12">
                    <h4>
                        <?php
                        // for pagination purpose
                    //    $function->doPages($offset, 'transaction.php', '', $total_records, $keyword);
                        ?>
                    </h4>
                </div>
                <div class="separator"> </div>
                <!-- right col (We are only adding the ID to make the widgets sortable)-->
            </div><!-- /.row (main row) -->


   <?php } } else{ ?>

            <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view transactions</div>
        <?php } ?>
    <?php
   $db->disconnect();
    ?>
                    
