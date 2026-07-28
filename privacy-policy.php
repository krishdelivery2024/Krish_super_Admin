<?php 
   	$page="Privacy Policy";
    include"header.php";?>
        <?php 
            	$sql = "SELECT * FROM settings WHERE id=8 OR id=9 OR id=10";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
                     if($permissions['settings']['update']==1){
            		if(!empty($_POST['privacy_policy']) && !empty($_POST['terms_conditions'])){

            			$privacy_policy = $db->escapeString($fn->xss_clean($_POST['privacy_policy']));
                        $terms_conditions=$db->escapeString($fn->xss_clean($_POST['terms_conditions']));
                        $terms_in_inovice=$db->escapeString($fn->xss_clean($_POST['terms_in_inovice']));
            			
            			
            			//Update privacy_policy - id = 9
            			$sql = "UPDATE `settings` SET `value`='".$privacy_policy."' WHERE `variable` = 'privacy_policy'";
            // 			echo $sql;
                        
            			$db->sql($sql);
                        
                        $sql = "UPDATE `settings` SET `value`='".$terms_conditions."' WHERE `variable` = 'terms_conditions'";
                       
                        $db->sql($sql);
                        
            			$sql = "UPDATE `settings` SET `value`='".$terms_in_inovice."' WHERE `variable` = 'terms_in_inovice'";
                       
                        $db->sql($sql);
            			
            			$sql = "SELECT * FROM settings WHERE id=8 OR id=9 OR id=10";
            			$db->sql($sql);
            			$res = $db->getResult();
                        $message .= "<div class='alert alert-success'> Information Updated Successfully!</div>";
            			
            		}
                    }else{
                    $message .= "<label class='alert alert-danger'>You have no permission to update settings</label>";

                }
            	}
            ?>
					<div class="col-md-4" style="margin-bottom:10px;">
                        <?php if($permissions['settings']['read']==1){?>
						<a href='play-store-privacy-policy.php' target='_blank' class='btn btn-primary btn-sm'>Privacy Policy Page for Play Store</a>
                        <?php } ?>
				</div>
                <div class="row">
                    
                    <div class="col-md-12">
                        <?php if($permissions['settings']['read']==1){
                        if($permissions['settings']['update']==0) { ?>
                            <div class="alert alert-danger">You have no permission to update settings</div>
                        <?php } ?>
                       
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Privacy Policy</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            
                            
                            <form  method="post" enctype="multipart/form-data">
                                 
                                <div class="box-body">
                                    
                                    <div class="form-group">
                                        <label for="app_name">Privacy Policy:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="privacy_policy" id="privacy_policy" required><?=$res[1]['value']?></textarea>
                                    </div>
                                    <div class="box-header with-border">
                                <h3 class="box-title">Update Terms Conditions</h3>
                            </div>
                            <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">Terms & Conditions:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="terms_conditions" id="terms_conditions" required><?=$res[2]['value']?></textarea>
                                    </div>
                                </div>
                                 <div class="box-header with-border">
                                <h3 class="box-title">Update Terms Conditions For Invoice</h3>
                            </div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">Terms & Conditions For Invoice:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="terms_in_inovice" id="terms_in_inovice" required><?=$res[0]['value']?></textarea>
                                    </div>
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
                        
                        <!-- /.box -->
                    </div>
                    </div>
            <div class="separator"> </div>
      
  
<?php include"footer.php";?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript">CKEDITOR.replace('privacy_policy');CKEDITOR.replace('terms_conditions');CKEDITOR.replace('terms_in_inovice');</script>
