<?php 
    $page="Contact Us";
    include"header.php";?>
        <?php 
            	$sql = "SELECT * FROM settings WHERE variable='contact_us'";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
            		if(!empty($_POST['contact_us'])){
            			
            			$contact_us = $db->escapeString($fn->xss_clean($_POST['contact_us']));
            			
            			$sql = "UPDATE `settings` SET `value`='".$contact_us."' WHERE `variable` = 'contact_us'";
            			$db->sql($sql);
            			
            			$sql = "SELECT * FROM settings WHERE `variable`='contact_us'";
            			$db->sql($sql);
            			$res = $db->getResult();
            			$message .= "<div class='alert alert-success'> Information Updated Successfully!</div>";
            			
            		}
            	}
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Information</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form  method="post" enctype="multipart/form-data">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">Contact US :</label>
                                        <textarea rows="10" cols="10" class="form-control" name="contact_us" id="contact_us" required><?=$res[0]['value']?></textarea>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <input type="submit" class="btn-primary btn" value="Update" name="btn_update"/>
                                </div>
                            </form>
                        </div>
                        <!-- /.box -->
                    </div>
                </div>
            <div class="separator"> </div>
      
  
<?php include"footer.php";?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript">CKEDITOR.replace('contact_us');</script>
