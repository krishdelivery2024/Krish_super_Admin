
<?php $page="Terms Conditions";
include"header.php";?>
<?php 
            	$sql = "SELECT * FROM settings";
                $db->sql($sql);
                $res = $db->getResult();
            	$message = '';
            	if(isset($_POST['btn_update'])){
            		if(!empty($_POST['terms_conditions'])){
            			
            			$terms_conditions = $db->escapeString($_POST['terms_conditions']);			
            			//Update terms_conditions - id = 10
            			$sql = "UPDATE `settings` SET `value`='".$terms_conditions."' WHERE `id` = 10";
            			$db->sql($sql);

            			$sql = "SELECT * FROM settings";
            			$db->sql($sql);
            			$res = $db->getResult();
            			$message .= "Terms Conditions Updated Successfully";
            			
            		}
            	}
            ?>
                <div class="row">
                    <div class="col-md-12">
                        <!-- general form elements -->
                        <div class="box box-primary">
                            <div class="box-header with-border">
                                <h3 class="box-title">Update Terms Conditions</h3>
                            </div>
                            <!-- /.box-header -->
                            <!-- form start -->
                            <form  method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="app_name">Terms & Conditions:</label>
                                        <textarea rows="10" cols="10" class="form-control" name="terms_conditions" id="terms_conditions" required><?=$res[9]['value']?></textarea>
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
      
  </body>
</html>
<?php include"footer.php";?>
<script type="text/javascript" src="dist/plugin/ckeditor/ckeditor.js"></script>
<script type="text/javascript">CKEDITOR.replace('terms_conditions');</script>
