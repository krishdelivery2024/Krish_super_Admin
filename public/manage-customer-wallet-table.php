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
<?php if($permissions['customers']['read']==1){?>
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <!-- form start -->
                <form  method="post" id="wallet_form" action="public/db-operation.php">
                    <input type="hidden" id="user_id" name="user_id" aria-required="true">
                    <input type="hidden" id="manage_customer_wallet" name="manage_customer_wallet" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="">Customer</label>
                      <input type="text" id="details" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                      <label for="">Select Type</label>
                      <select name="type" id="type" class="form-control">
                        <option value="">Select</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                          
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="">Amount</label>
                      <input type="text" class="form-control"  name="amount">
                    </div>
                    <label for="">Message</label>
                    <div class="form-group">
                      
                      <textarea name="message" id="message" style=" min-width:500px; max-width:100%;min-height:100px;height:100%;width:100%;"></textarea>
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
        <!-- Left col -->
        <div class="col-xs-6">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Customers</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="users"
                        data-toggle="table"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=users"
                        data-click-to-select="true"
                        data-side-pagination="server"
                        data-pagination="true"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-search="true" data-trim-on-search="false"
                        data-show-refresh="true" data-show-columns="true"
                        data-sort-name="id" data-sort-order="asc"
                        data-mobile-responsive="true"
                        data-toolbar="#toolbar" data-show-export="true"
                        data-maintain-selected="true"
                        data-export-types='["txt","excel"]'
                        data-export-options='{
                            "fileName": "users-list-<?=date('d-m-y')?>",
                            "ignoreColumn": ["state"]   
                        }'
                        >
                        <thead>
                        <tr>
                            <th data-field="state" data-radio="true"></th>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="balance" data-sortable="true">Balance</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>
</div>
<?php } else { ?>
            <div class="alert alert-danger" style="margin-top: 20px;">You have no permission to manage customer wallet</div>
        <?php } ?>
