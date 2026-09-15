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
            <?php if($permissions['delivery_boys']['create']==0){?>
                <div class="alert alert-danger">You have no permission to create delivery boy</div>
            <?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Delivery Boy</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" id="add_form" action="public/db-operation.php" enctype="multipart/form-data">
                    <input type="hidden" id="add_delivery_boy" name="add_delivery_boy" required="" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="">Name</label>
                      <input type="text" class="form-control"  name="name">
                    </div>
                              <div class="form-group">
                      <label for="">Mobile</label>
                      <input type="text" class="form-control"  name="mobile">
                    </div>
                    <div class="form-group">
                      <label for="">Password</label>
                      <input type="password" class="form-control"  name="password" id="password">
                    </div>
                    <div class="form-group">
                      <label for="">Confirm Password</label>
                      <input type="password" class="form-control"  name="confirm_password">
                    </div>
                    
                      <label for="">Address</label>
                    <div class="form-group">
                      
                      <textarea name="address" id="address" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="">Bonus (%)</label>
                      <input type="number" class="form-control"  name="bonus" id="bonus" value="<?=$config['delivery-boy-bonus-percentage']?>">
                    </div>
                    <div class="form-group">
                      <label for="">Service Type</label>
                      <select class="form-control" name="service_type">
                        <option value="both">Both (Food &amp; Parcel)</option>
                        <option value="food">Food Only</option>
                        <option value="parcel">Parcel Only</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="">Aadhaar Number <span style="color:red;">*</span></label>
                      <input type="text" class="form-control" name="aadhaar" id="aadhaar" maxlength="12" placeholder="12-digit Aadhaar number">
                    </div>
                    <div class="form-group">
                      <label for="">Driving License Number <span style="color:red;">*</span></label>
                      <input type="text" class="form-control" name="driving_license" id="driving_license" placeholder="e.g. MH12 20250012345">
                    </div>
                    <div class="form-group">
                      <label for="">Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="profile" id="profile" accept="image/*">
                    </div>
                    <div class="form-group">
                      <label for="">Aadhaar Card Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="aadhaar_image" id="aadhaar_image" accept="image/*">
                    </div>
                    <div class="form-group">
                      <label for="">Driving License Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="driving_license_image" id="driving_license_image" accept="image/*">
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
            <?php if($permissions['delivery_boys']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Delivery Boys</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="delivery-boys"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=delivery-boys"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile</th>
                            <th data-field="address" data-sortable="true">Address</th>
                            <th data-field="bonus" data-sortable="true">Bonus(%)</th>
                            <th data-field="balance" data-sortable="true">Balance</th>
                            <th data-field="service_type" data-sortable="true">Service Type</th>
                            <th data-field="aadhaar" data-sortable="true">Aadhaar</th>
                            <th data-field="driving_license" data-sortable="true">DL No</th>
                            <th data-field="profile">Photo</th>
                            <th data-field="aadhaar_image">Aadhaar Photo</th>
                            <th data-field="driving_license_image">DL Photo</th>
                            <th data-field="status">Status</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view delivery boys</div>
            <?php }?>
        </div>
        <div class="separator"> </div>
    </div>
