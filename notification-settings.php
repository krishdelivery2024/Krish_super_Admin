<?php 
    $page="Notification Settings";
    include"header.php";?>
      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <?php 
            	$sql = "SELECT * FROM settings WHERE variable='fcm_server_key'";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
                    if($permissions['settings']['update']==1){
            		// if(!empty($_POST['fcm_server_key'])){
            			
            			$fcm_server_key = $db->escapeString($_POST['fcm_server_key']);
            			
            			//Update privacy_policy - id = 9
            		
            			//Update contact_us - id = 12
            			$sql = "UPDATE `settings` SET `value`='".$fcm_server_key."' WHERE `variable` = 'fcm_server_key'";
                   
            			$db->sql($sql);
            			
            			$sql = "SELECT * FROM settings WHERE variable='fcm_server_key'";
            			$db->sql($sql);
            			$res = $db->getResult();
                      
            			$message .= "<div class='alert alert-success'> FCM Server Key Updated Successfully!</div>";
                        }else{
                        $message .= "<label class='alert alert-danger'>You have no permission to update settings</label>";

                    }

            			
            		// }
            	}
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php if($permissions['settings']['read']==1){
                        if($permissions['settings']['update']==0) { ?>
                            <div class="alert alert-danger">You have no permission to update settings</div>
                        <?php } ?>
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Notification Settings</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form  method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    
                                        <div class="form-group">
                                        <label for="fcm_server_key">FCM Server Key : </label>
                                        <textarea class="form-control" name="fcm_server_key" placeholder='FCM Server Key' rows="5"><?=$res[0]['value']?></textarea>
                                    </div>
                                </div>
                                
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update"/>
                                </div>
                            </form>
                            <?php } else { ?>
                                <div class="alert alert-danger">You have no permission to view settings</div>
                             <?php } ?>
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            <div class="separator"> </div>
      
  </body>
</html>
<?php include"footer.php";?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript">CKEDITOR.replace('contact_us');</script>
