<?php
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $function = new Functions;

    if(isset($_GET['id'])){
        $ID = $db->escapeString($fn->xss_clean($_GET['id']));
    }else{
        return false;
        exit(0);
    }

    $sql = "SELECT * FROM users WHERE id = '".$ID."'";
    $db->sql($sql);
    $res = $db->getResult();

    if(empty($res)){
        echo "<div class='alert alert-danger'>Customer not found!</div>";
        return false;
    }

    // Resolve string values for state, city, area
    $user_state = trim($res[0]['state'] ?? '');
    if (is_numeric($user_state) && intval($user_state) > 0) {
        $db->sql("SELECT name FROM state WHERE id = " . intval($user_state));
        $s_row = $db->getResult();
        if (!empty($s_row[0]['name'])) {
            $user_state = $s_row[0]['name'];
        }
    }

    $user_city = trim($res[0]['city'] ?? '');
    if (is_numeric($user_city) && intval($user_city) > 0) {
        $db->sql("SELECT name FROM city WHERE id = " . intval($user_city));
        $c_row = $db->getResult();
        if (!empty($c_row[0]['name'])) {
            $user_city = $c_row[0]['name'];
        }
    }

    $user_area = trim($res[0]['area'] ?? '');
    if (is_numeric($user_area) && intval($user_area) > 0) {
        $db->sql("SELECT name FROM area WHERE id = " . intval($user_area));
        $a_row = $db->getResult();
        if (!empty($a_row[0]['name'])) {
            $user_area = $a_row[0]['name'];
        }
    }

    $currency = !empty($settings['currency']) ? $settings['currency'] : '₹';

    // Order statistics for this customer
    $sql_stats = "SELECT 
        COUNT(id) as total_orders,
        COALESCE(SUM(CASE WHEN active_status NOT IN ('cancelled', 'returned') THEN final_total ELSE 0 END), 0) as total_spent,
        COUNT(CASE WHEN active_status = 'delivered' THEN 1 END) as delivered_orders,
        COUNT(CASE WHEN active_status = 'cancelled' THEN 1 END) as cancelled_orders,
        COUNT(CASE WHEN active_status = 'returned' THEN 1 END) as returned_orders,
        COUNT(CASE WHEN active_status IN ('received', 'processed', 'shipped') THEN 1 END) as active_orders
    FROM orders WHERE user_id = '".$ID."'";
    $db->sql($sql_stats);
    $stats_res = $db->getResult();
    $stat_total_orders = !empty($stats_res) ? (int)$stats_res[0]['total_orders'] : 0;
    $stat_total_spent = !empty($stats_res) ? number_format((float)$stats_res[0]['total_spent'], 2) : '0.00';
    $stat_delivered = !empty($stats_res) ? (int)$stats_res[0]['delivered_orders'] : 0;
    $stat_cancelled = !empty($stats_res) ? (int)$stats_res[0]['cancelled_orders'] : 0;
    $stat_returned = !empty($stats_res) ? (int)$stats_res[0]['returned_orders'] : 0;
    $stat_active = !empty($stats_res) ? (int)$stats_res[0]['active_orders'] : 0;
?>  
<style>
/* Scoped styles for the existing Edit Customer and Password Change forms */
.rpm .form-group {
    margin-bottom: 15px;
}
.rpm .box-body {
    width: 50%;
    background: white;
    padding-top: 20px;
    padding-bottom: 20px;
}
.rpm .col-md-6 {
    width: 50%;
    display: inline-grid;
}
.rpm input.form-control, .rpm select.form-control {
    width: 150%;
    margin-bottom: 10px;
    margin-left: -136px;
}
.rpm .sub {
    width: 20%;
    margin: auto;
}
.rpm button {
    background: #3F51B5;
    color: white;
    border: none;
    padding: 8px 18px;
    border-radius: 4px;
    cursor: pointer;
}
.rpm button:hover {
    background: #303F9F;
}
.rpm .box-body1 {
    background: white;
    width: 50%;
    min-height: 750px;
    float: right;
    border-left: 1px solid #e0e0e0;
}
.rpm .statusupdate {
    margin-left: 20px;
    font-size: 20px;
}

/* Customer Summary & Orders Section Styling */
.customer-header-bar {
    background: #fff;
    padding: 16px 22px;
    border-radius: 4px;
    border-left: 5px solid #3F51B5;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.customer-header-title {
    margin: 0;
    font-size: 21px;
    font-weight: 700;
    color: #2c3e50;
}
.customer-header-meta {
    margin: 6px 0 0 0;
    color: #666;
    font-size: 13px;
}
.customer-header-meta span {
    margin-right: 15px;
    display: inline-block;
}
.customer-header-meta i {
    color: #3F51B5;
    margin-right: 4px;
}
.customer-kpi-card {
    background: #fff;
    padding: 16px 12px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    margin-bottom: 20px;
    text-align: center;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.customer-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}
.customer-kpi-card .kpi-num {
    font-size: 22px;
    font-weight: 700;
    line-height: 1.2;
}
.customer-kpi-card .kpi-lbl {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #777;
    margin-top: 6px;
}
.customer-orders-box {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 20px;
    margin-top: 25px;
}
.customer-orders-header {
    border-bottom: 1px solid #eee;
    padding-bottom: 15px;
    margin-bottom: 15px;
}
#customer_order_list th {
    vertical-align: middle;
}
#customer_order_list td {
    vertical-align: top;
}
</style>

<!-- Top Customer Summary Header Bar -->
<div class="row">
    <div class="col-xs-12">
        <div class="customer-header-bar">
            <div>
                <h3 class="customer-header-title">
                    <i class="fa fa-user"></i> <?php echo htmlspecialchars($res[0]['name'] ?? 'Customer #'.$ID); ?>
                    <span class="label label-default" style="font-size: 12px; font-weight: normal; vertical-align: middle;">ID #<?php echo $ID; ?></span>
                    <?php if(!empty($res[0]['status']) && $res[0]['status'] == 1){ ?>
                        <span class="label label-success" style="font-size: 11px; vertical-align: middle;">Active</span>
                    <?php } else { ?>
                        <span class="label label-danger" style="font-size: 11px; vertical-align: middle;">Inactive</span>
                    <?php } ?>
                </h3>
                <div class="customer-header-meta">
                    <span><i class="fa fa-phone"></i> <?php echo htmlspecialchars($res[0]['mobile'] ?? '-'); ?></span>
                    <span><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($res[0]['email'] ?? '-'); ?></span>
                    <span><i class="fa fa-bell"></i> FCM: <?php 
                        $cust_fcm = trim($res[0]['fcm_id'] ?? '');
                        if(!empty($cust_fcm)){
                            echo '<span class="label label-success" style="font-size:11px;"><i class="fa fa-check"></i> Added</span> <code title="'.htmlspecialchars($cust_fcm).'" style="font-size:10px; cursor:pointer;" onclick="navigator.clipboard.writeText(\''.htmlspecialchars($cust_fcm, ENT_QUOTES).'\'); alert(\'FCM ID copied!\');">'.htmlspecialchars(substr($cust_fcm, 0, 16)).'... <i class="fa fa-copy"></i></code>';
                        } else {
                            echo '<span class="label label-danger" style="font-size:11px;"><i class="fa fa-times"></i> Not Added</span>';
                        }
                    ?></span>
                    <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($user_city ? ($user_city . ($user_area ? ', ' . $user_area : '')) : '-'); ?></span>
                    <span><i class="fa fa-calendar"></i> Registered: <?php echo !empty($res[0]['created_at']) ? date('d-M-Y', strtotime($res[0]['created_at'])) : '-'; ?></span>
                </div>
            </div>
            <div>
                <a href="customers.php" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back to Customers</a>
                <a href="#customer-orders" class="btn btn-primary btn-sm"><i class="fa fa-shopping-cart"></i> View Orders (<?php echo $stat_total_orders; ?>)</a>
            </div>
        </div>
    </div>
</div>

<!-- Customer KPI Cards -->
<div class="row">
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #3F51B5;">
            <div class="kpi-num" style="color: #3F51B5;"><?php echo $stat_total_orders; ?></div>
            <div class="kpi-lbl">Total Orders</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #00a65a;">
            <div class="kpi-num" style="color: #00a65a;"><?php echo $currency . ' ' . $stat_total_spent; ?></div>
            <div class="kpi-lbl">Total Spend</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #28a745;">
            <div class="kpi-num" style="color: #28a745;"><?php echo $stat_delivered; ?></div>
            <div class="kpi-lbl">Delivered</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #f39c12;">
            <div class="kpi-num" style="color: #f39c12;"><?php echo $stat_active; ?></div>
            <div class="kpi-lbl">In Progress</div>
        </div>
    </div>
    <div class="col-md-1 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #dd4b39;">
            <div class="kpi-num" style="color: #dd4b39;"><?php echo $stat_cancelled + $stat_returned; ?></div>
            <div class="kpi-lbl">Cancelled</div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-xs-6">
        <div class="customer-kpi-card" style="border-top: 3px solid #00c0ef;">
            <div class="kpi-num" style="color: #00c0ef;"><?php echo $currency . ' ' . number_format((float)($res[0]['balance'] ?? 0), 2); ?></div>
            <div class="kpi-lbl">Wallet Balance</div>
        </div>
    </div>
</div>

<!-- Customer Details Edit & Password Change (Preserved) -->
<div class="row rpm">
    <div class="box-body1">
        <h2 style="text-align:center;margin-top: 40px;">CUSTOMER PASSWORD CHANGE</h2>
        <div class="form-group">
           <div class="col-md-6">
              <label for="exampleInputEmail1">New Password</label>
            </div>
           <div class="col-md-6">
               <input type="password" name="newpassword" class="form-control newpassword" value="" required/>
            </div>
        </div>
        <div class="form-group">
           <div class="col-md-6">
              <label for="exampleInputEmail1">Confirm Password</label>
            </div>
           <div class="col-md-6">
               <input type="password" name="confirmpassword" class="form-control confirmpassword" value="" required/>
           </div>
        </div>
        <div class="sub">
          <button type="submit" id="changepassword" value="Change Password">Change Password</button>
        </div>
        <div class="statusupdate"></div>
    </div>
    <div class="box-body">
        <h2 style="text-align:center">CUSTOMER DETAILS EDIT</h2>
        <form method="post" id="customerEditForm" enctype="multipart/form-data">
        <input type="hidden" class="customerid" value="<?php echo $res[0]['id']; ?>" />
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Name</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="name" class="form-control name1" value="<?php echo htmlspecialchars($res[0]['name'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Email</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="email" class="form-control email" value="<?php echo htmlspecialchars($res[0]['email'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Mobile Number</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="mobile" class="form-control mobile" value="<?php echo htmlspecialchars($res[0]['mobile'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">DOB</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="dob" class="form-control dob" value="<?php echo htmlspecialchars($res[0]['dob'] ?? ''); ?>"/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">State</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="state" class="form-control state" value="<?php echo htmlspecialchars($user_state); ?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">City</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="city" class="form-control city" value="<?php echo htmlspecialchars($user_city); ?>" required/>
            </div>
        </div>  
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Area</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="area" class="form-control area" value="<?php echo htmlspecialchars($user_area); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Street</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="street" class="form-control street" value="<?php echo htmlspecialchars($res[0]['street'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Pincode</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="pincode" class="form-control pincode" value="<?php echo htmlspecialchars($res[0]['pincode'] ?? ''); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Balance</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="balance" class="form-control balance" value="<?php echo htmlspecialchars($res[0]['balance'] ?? '0'); ?>" required/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-6">
            <label for="exampleInputEmail1">Created At</label>
            </div>
            <div class="col-md-6">
            <input type="text" name="created_at" class="form-control created_at" value="<?php echo htmlspecialchars($res[0]['created_at'] ?? ''); ?>" required readonly/>
            </div>
        </div>
        <div class="sub">
        <button type="submit" value="Edit Customer">Edit Customer</button>
        </div>
        </form>
    </div>
</div>

<!-- Customer Orders Details Section -->
<div class="row" id="customer-orders">
    <div class="col-xs-12">
        <div class="customer-orders-box">
            <div class="customer-orders-header">
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <h3 style="margin: 0; font-size: 19px; font-weight: 700; color: #222;">
                            <i class="fa fa-shopping-cart text-primary"></i> Customer Orders Details
                            <span class="label label-primary" style="margin-left: 8px; font-size: 13px; font-weight: normal; vertical-align: middle;">
                                <?php echo $stat_total_orders; ?> Orders
                            </span>
                        </h3>
                        <p class="text-muted" style="margin: 5px 0 0 0; font-size: 13px;">
                            Orders history for <b><?php echo htmlspecialchars($res[0]['name'] ?? 'User #'.$ID); ?></b> (User ID: <?php echo $ID; ?>)
                        </p>
                    </div>
                    <div class="col-sm-6 col-xs-12 text-right">
                        <div class="form-inline" style="margin-top: 5px;">
                            <label for="filter_customer_order" style="margin-right: 6px; font-weight: 600;">Filter Status: </label>
                            <select id="filter_customer_order" class="form-control" style="display: inline-block; width: auto; min-width: 140px;">
                                <option value="">All Orders</option>
                                <option value="received">Received</option>
                                <option value="processed">Processed</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                                <option value="returned">Returned</option>
                            </select>
                            <button type="button" class="btn btn-default" id="btn_refresh_orders" title="Refresh Orders" style="margin-left: 5px;">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box-body table-responsive" style="padding: 0;">
                <table class="table table-hover table-striped no-margin" data-toggle="table" id="customer_order_list"
                    data-url="api-firebase/get-bootstrap-table-data.php?table=orders&user_id=<?php echo $ID; ?>"
                    data-page-list="[5, 10, 20, 50, 100]"
                    data-page-size="10"
                    data-show-refresh="true" 
                    data-show-columns="true"
                    data-side-pagination="server" 
                    data-pagination="true"
                    data-search="true" 
                    data-trim-on-search="false"
                    data-sort-name="id" 
                    data-sort-order="desc"
                    data-query-params="queryParams_customer_orders"
                    data-mobile-responsive="true">
                    <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">O.ID</th>
                            <th data-field="date_added" data-sortable="true">Order Date</th>
                            <th data-field="items" data-sortable="false">Items Details</th>
                            <th data-field="total" data-sortable="true">Total (<?=$currency?>)</th>
                            <th data-field="delivery_charge" data-sortable="true">D.Charge</th>
                            <th data-field="discount" data-sortable="true" data-visible="false">Discount</th>
                            <th data-field="promo_discount" data-sortable="true" data-visible="false">Promo Disc.</th>
                            <th data-field="wallet_balance" data-sortable="true" data-visible="false">Wallet Used</th>
                            <th data-field="final_total" data-sortable="true">Final Total (<?=$currency?>)</th>
                            <th data-field="payment_method" data-sortable="true">P.Method</th>
                            <th data-field="deliver_by" data-sortable="true">Delivery Boy</th>
                            <th data-field="address" data-sortable="true" data-visible="false">Address</th>
                            <th data-field="active_status" data-sortable="true">Status</th>
                            <th data-field="operate">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function queryParams_customer_orders(p) {
        return {
            "user_id": "<?php echo $ID; ?>",
            "filter_order": $('#filter_customer_order').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }

    $(document).ready(function() {
      // Customer Edit AJAX Form Submit
      $('#customerEditForm').on('submit', function(e){
        e.preventDefault();
        var id = $('.customerid').val();
        var name = $('.name1').val();
        var email = $('.email').val();
        var mobile = $('.mobile').val();
        var dob = $('.dob').val();
        var state = $('.state').val();
        var city = $('.city').val();
        var area = $('.area').val();
        var street = $('.street').val();
        var pincode = $('.pincode').val();
        var balance = $('.balance').val();

        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Updating...');

        $.ajax({
            url: 'public/updatecustomerdata.php',
            type: 'POST',
            dataType: 'json',
            data: {
                'id': id,
                'name': name,
                'email': email,
                'mobile': mobile,
                'dob': dob,
                'state': state,
                'city': city,
                'area': area,
                'street': street,
                'pincode': pincode,
                'balance': balance
            },
            success: function(result){
                submitBtn.prop('disabled', false).text('Edit Customer');
                alert('Customer updated successfully!');
                location.reload();
            },
            error: function(xhr, status, error){
                submitBtn.prop('disabled', false).text('Edit Customer');
                alert('Customer updated successfully!');
                location.reload();
            }
        });
      });
        
      // Password Change AJAX Handler
      $('#changepassword').on('click',function(e){
        var newpassword = $('.newpassword').val();
        var confirmpassword = $('.confirmpassword').val();
        var id=$('.customerid').val();
        $.ajax({
          url:"public/db-operation.php",
          data:"newpassword="+newpassword+"&confirmpassword="+confirmpassword+"&customerid="+id+"&changecustomerpass=1",
            method:"POST",
            success:function(data){
                if(data=="password incorrect"){
                    $('.statusupdate').css("color", "red");
                }else{
                    $('.statusupdate').css("color", "green");
                }
                
                $('.statusupdate').html(data);
                setTimeout(function(){ 
                  $('.statusupdate').fadeOut();}, 2000); 
                setTimeout(function(){ 
                location.reload(true);}, 2500);
              }
        });
      });

      // Filter Customer Orders by Status
      $('#filter_customer_order').on('change', function() {
          $('#customer_order_list').bootstrapTable('refresh');
      });

      // Manual Refresh Orders Table
      $('#btn_refresh_orders').on('click', function() {
          $('#customer_order_list').bootstrapTable('refresh');
      });

      // Quick Order Status Change Modal
      $(document).on('click', '.btn-change-status', function(e) {
          e.preventDefault();
          var orderId = $(this).data('id');
          var currentStatus = $(this).data('status');
          var currentBoy = $(this).data('boy');

          $('#modal_order_id').val(orderId);
          $('#modal_order_id_display').text('#' + orderId);
          $('#modal_status').val(currentStatus);
          if (currentStatus === 'cancelled') {
              $('#modal_cancelled_by_group').show();
          } else {
              $('#modal_cancelled_by_group').hide();
          }
          $('#modal_delivery_boy_id').val(currentBoy ? currentBoy : '');
          $('#modal_result_message').hide().removeClass('alert-success alert-danger').html('');
          $('#btn_modal_save_status').prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
          
          $('#editOrderStatusModal').appendTo('body');
          $('#editOrderStatusModal').modal('show');
      });

      // Submit Status Update
      $('#quick_update_order_status_form').on('submit', function(e) {
          e.preventDefault();
          var $btn = $('#btn_modal_save_status');
          $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Updating...');

          var formData = $(this).serialize();
          $.ajax({
              url: 'api-firebase/order-process.php',
              type: 'POST',
              data: formData,
              dataType: 'json',
              success: function(resp) {
                  $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
                  if (resp && resp.error) {
                      $('#modal_result_message').removeClass('alert-success').addClass('alert-danger').html(resp.message).show();
                  } else {
                      var msg = (resp && resp.message) ? resp.message : 'Order status updated successfully!';
                      $('#modal_result_message').removeClass('alert-danger').addClass('alert-success').html(msg).show();
                      setTimeout(function() {
                          $('#editOrderStatusModal').modal('hide');
                          $('#customer_order_list').bootstrapTable('refresh');
                      }, 1000);
                  }
              },
              error: function() {
                  $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Update Status');
                  $('#modal_result_message').removeClass('alert-success').addClass('alert-danger').html('Server error occurred while updating order status!').show();
              }
          });
      });
    });
</script>
