<?php 

    include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include('includes/variables.php');
    include_once('includes/custom-functions.php');
    
    $fn = new custom_functions;
    $config = $fn->get_configurations();
    ?>
    <script src="http://ajax.aspnetcdn.com/ajax/jquery.validate/1.11.1/jquery.validate.min.js"></script>
    <!-- Main row -->
    <div class="row">
      <?php if($_SESSION['role'] != 'seller'){ ?>
        <div class="col-md-6">
           <?php if($permissions['promo_codes']['create']==0){?>
          <div class="alert alert-danger">You have no permission to create promo code.</div>
        <?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Manage Promo Code</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" id="add_form" action="public/db-operation.php" enctype="multipart/form-data">
                    <input type="hidden" id="add_promo_code" name="add_promo_code" required="" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group col-md-6">
                      <label for="">Promo Code</label>
                      <input type="text" class="form-control"  name="promo_code">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">Message</label>
                      <input type="text" class="form-control"  name="message">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">Start Date</label>
                      <input type="date" class="form-control"  name="start_date" id="start_date">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">End Date</label>
                      <input type="date" class="form-control"  name="end_date" id="end_date">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">No. Of Users</label>
                      <input type="text" class="form-control"  name="no_of_users">
                    </div>
                     <div class="form-group col-md-6">
                      <label for="">Minimum Order Amount</label>
                      <input type="text" class="form-control"  name="minimum_order_amount">
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">Discount</label>
                      <input type="text" class="form-control"  name="discount" id="discount">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Discount Type</label>
                        <select name="discount_type" class="form-control">
                            <option value="">Select</option>
                            <option value="percentage">Percentage</option>
                            <option value="amount">Amount</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                      <label for="">Max Discount Amount</label>
                      <input type="text" class="form-control"  name="max_discount_amount" id="max_discount_amount">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Repeat Usage</label>
                        <select name="repeat_usage" id="repeat_usage" class="form-control">
                            <option value="">Select</option>
                            <option value="1">Allowed</option>
                            <option value="0">Not Allowed</option>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                      <label for="exampleInputFile">Image</label>
                      <input type="file" name="category_image" id="category_image" required/>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">Select</option>
                            <option value="1">Active</option>
                            <option value="0">Deactive</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="repeat_usage_block" style="display:none">
                      <label for="">No. Of Repeat Usage</label>
                      <input type="text" class="form-control"  name="no_of_repeat_usage" id="no_of_repeat_usage">
                    </div>
                    
                    
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                    <input type="reset" class="btn-warning btn" value="Clear"/>
                
                  </div>
                  <div class="form-group">
                      
                      <div id="result" style="display: none;"></div>
                    </div>
                </form>
              </div><!-- /.box -->
             </div>
             <?php } ?>
        <!-- Left col -->
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Promo Codes </h3>
					
                </div>
                <?php if($permissions['promo_codes']['read']==1){?>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="promo-codes"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=promo-codes"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <?php if($_SESSION['role'] == 'seller'){ ?>
                            <th data-field="active">Enable</th>
                            <?php } ?>
                            <th data-field="promo_code" data-sortable="true">Promo Code</th>
                            <th data-field="image">Image</th>
                            <th data-field="message" data-sortable="true">Message</th>
                            <th data-field="start_date" data-sortable="true">Start Date</th>
                            <th data-field="end_date" data-sortable="true">End Date</th>
                            <th data-field="no_of_users" data-sortable="true">No Of Users</th>
                            <th data-field="minimum_order_amount" data-sortable="true">Minimum Order Amount</th>
                            <th data-field="discount" data-sortable="true">Discount</th>
                            <th data-field="discount_type" data-sortable="true">Discount Type</th>
                            <th data-field="max_discount_amount" data-sortable="true" data-visible="false">Max Discount Amount</th>
                            <th data-field="repeat_usage" data-sortable="true" data-visible="false">Repeat Usage</th>
                            <th data-field="no_of_repeat_usage" data-sortable="true" data-visible="false">No. Of Repeat Usage</th>
                            <th data-field="status">Status</th>
                            <th data-field="date_created">Date Created</th>
                            <?php if($_SESSION['role'] != 'seller'){ ?>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                            <?php } ?>
                        </tr>
                        </thead>
                    </table>
                </div>
                <?php }else { ?>
                <div class="alert alert-danger">You have no permission to view promo codes</div>
                <?php }?>
            </div>
        </div>
        <div class="separator"> </div>
    </div>

<style>
/* Hide default checkbox */
.custom-switch {
    position: relative;
    width: 40px;
    height: 22px;
    -webkit-appearance: none;
    background: #c6c6c6;
    outline: none;
    border-radius: 50px;
    transition: background 0.3s;
    cursor: pointer;
}

/* Circle inside */
.custom-switch::before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    background: #fff;
    transition: 0.3s;
}

/* When checked */
.custom-switch:checked {
    background: #4cd964;
}

.custom-switch:checked::before {
    left: 20px;
}
</style>

<script>
document.querySelectorAll('.custom-switch').forEach(function(switchEl) {
    switchEl.addEventListener('change', function() {
        const id = this.id;
        const status = this.checked ? 1 : 0;
        console.log(`Toggle ${id}: ${status}`);
    });
});


$(document).on('change', '.update-seller', function () {
    var checkbox = $(this);
    var promoId = checkbox.data('id');
    var currentSellerIds = checkbox.data('sellerids');
    var sellerId = <?=$seller_id;?>;

    $.ajax({
        url: 'public/db-operation.php',
        type: 'POST',
        data: {
            active_coupon: true,
            id: promoId,
            seller_id: sellerId,
            seller_ids: currentSellerIds
        },
        dataType: 'json',
        success: function (response) {
            console.log('Updated seller_ids:', response.new_seller_ids);

            // ✅ Update the data attribute with new seller_ids (so it stays accurate)
            checkbox.attr('data-sellerids', response.new_seller_ids);
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
});


</script>



