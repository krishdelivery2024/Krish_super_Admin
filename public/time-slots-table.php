<?php 

    include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include('includes/variables.php');
    include_once('includes/custom-functions.php');
    
    $fn = new custom_functions;
    $config = $fn->get_configurations();

    $meal_slots = array(
        'anytime' => 0,
        'breakfast' => array('from_time' => '06:00', 'to_time' => '11:00'),
        'lunch' => array('from_time' => '11:00', 'to_time' => '16:00'),
        'dinner' => array('from_time' => '16:00', 'to_time' => '23:00')
    );

    $sql = "SELECT value FROM settings WHERE variable='meal_time_slots'";
    $db->sql($sql);
    $res_meal = $db->getResult();
    if(!empty($res_meal)){
        $saved = json_decode($res_meal[0]['value'], true);
        if(!empty($saved)){
            $meal_slots['anytime'] = isset($saved['anytime']) ? $saved['anytime'] : 0;
            foreach(array('breakfast','lunch','dinner') as $m){
                if(isset($saved[$m])){
                    $meal_slots[$m]['from_time'] = isset($saved[$m]['from_time']) ? $saved[$m]['from_time'] : $meal_slots[$m]['from_time'];
                    $meal_slots[$m]['to_time'] = isset($saved[$m]['to_time']) ? $saved[$m]['to_time'] : $meal_slots[$m]['to_time'];
                }
            }
        }
    }

    $message = '';
    if(isset($_POST['update_meal_time_slots']) && $_POST['update_meal_time_slots']==1){
        if($permissions['settings']['update']==1){
            $anytime = (isset($_POST['anytime']) && $_POST['anytime']=='1') ? 1 : 0;
            $value = array('anytime' => $anytime);
            foreach(array('breakfast','lunch','dinner') as $m){
                $from_time = $db->escapeString($fn->xss_clean($_POST[$m.'_from_time']));
                $to_time = $db->escapeString($fn->xss_clean($_POST[$m.'_to_time']));
                if(empty($from_time)){ $from_time = $meal_slots[$m]['from_time']; }
                if(empty($to_time)){ $to_time = $meal_slots[$m]['to_time']; }
                $value[$m] = array('from_time' => $from_time, 'to_time' => $to_time);
            }
            $settings_value = json_encode($value);
            $sql = "SELECT id FROM settings WHERE variable='meal_time_slots'";
            $db->sql($sql);
            $res_check = $db->getResult();
            if(!empty($res_check)){
                $sql = "UPDATE settings SET value='".$settings_value."' WHERE variable='meal_time_slots'";
            }else{
                $sql = "INSERT INTO settings (`variable`,`value`) VALUES ('meal_time_slots','".$settings_value."')";
            }
            if($db->sql($sql)){
                $meal_slots = $value;
                $message = "<div class='alert alert-success'>Meal Time Slot Settings Updated Successfully!</div>";
            }else{
                $message = "<div class='alert alert-danger'>Some Error Occurred! Please Try Again.</div>";
            }
        }else{
            $message = "<div class='alert alert-danger'>You have no permission to update settings</div>";
        }
    }
    ?>
    
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Meal Time Slot Settings</h3>

                </div><!-- /.box-header -->
                <?php if($permissions['settings']['update']==0) { ?>
                        <div class="alert alert-danger">You have no permission to update settings</div>
                        <?php }  ?>
                <?php if(!empty($message)){ echo $message; } ?>
                <!-- form start -->
                <form method="post" id="meal_slots_form" action="time-slots.php">
                    <input type="hidden" name="update_meal_time_slots" value="1" required="">
                  <div class="box-body">
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="anytime" value="1" <?= $meal_slots['anytime']==1 ? 'checked' : ''; ?>> 
                            Available Anytime
                        </label>
                        <p class="help-block">When enabled, meals are available all day and the slot timings below are ignored.</p>
                    </div>
                    <hr>
                    <?php foreach(array('breakfast','lunch','dinner') as $m){ ?>
                        <div class="form-group">
                            <label for="<?= $m; ?>"><?= ucfirst($m); ?></label>
                            <div class="row">
                                <div class="col-sm-6">
                                    <input type="time" class="form-control" id="<?= $m; ?>_from_time" name="<?= $m; ?>_from_time" value="<?= $meal_slots[$m]['from_time']; ?>">
                                    <p class="help-block">From Time</p>
                                </div>
                                <div class="col-sm-6">
                                    <input type="time" class="form-control" id="<?= $m; ?>_to_time" name="<?= $m; ?>_to_time" value="<?= $meal_slots[$m]['to_time']; ?>">
                                    <p class="help-block">To Time</p>
                                </div>
                            </div>
                        </div>
                    <?php } ?>            
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submit_meal_btn" name="btn_update_meal_slots">Save</button>
                  </div>
                </form>
              </div><!-- /.box -->

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