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
    
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Time Slot</h3>

                </div><!-- /.box-header -->
                <?php if($permissions['settings']['update']==0) { ?>
                        <div class="alert alert-danger">You have no permission to add time slot</div>
                        <?php }  ?>
                <!-- form start -->
                <form  method="post" id="add_form" action="public/db-operation.php">
                    <input type="hidden" id="add_time_slot" name="add_time_slot" required="" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="">Title</label>
                      <input type="text" class="form-control"  name="title" placeholder="Morning 9AM to 12PM">
                    </div>
                    <div class="form-group">
                      <label for="">From Time</label>
                      <input type="text" class="form-control"  name="from_time" placeholder="09:00:00">
                    </div>
                    <div class="form-group">
                      <label for="">To Time</label>
                      <input type="text" class="form-control"  name="to_time" placeholder="12:00:00">
                    </div>
                    <div class="form-group">
                      <label for="">Last Order Time</label>
                      <input type="text" class="form-control"  name="last_order_time" placeholder="11:00:00">
                    </div> 
                    <div class="form-group">
                      <label for="">Status</label>
                      <select name="status" class="form-control">
                          <option value="">Select</option>
                          <option value="1">Active</option>
                          <option value="0">Deactive</option>
                      </select>
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
             <?php if($permissions['settings']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Time Slots</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="time-slots"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=time-slots"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="title" data-sortable="true">Title</th>
                            <th data-field="from_time" data-sortable="true">From Time</th>
                            <th data-field="to_time" data-sortable="true">To Time</th>
                            <th data-field="last_order_time" data-sortable="true">Last Order Time</th>
                            <th data-field="status">Status</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <?php } else {?>
            <div class="alert alert-danger">You have no permission to view settings</div>
        <?php }?>
        <div class="separator"> </div>
    </div>