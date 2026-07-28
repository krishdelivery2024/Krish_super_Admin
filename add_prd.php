<?php $page="Add Product";
include"header.php";?>

      
<?php 
    
    include_once('includes/functions.php'); 
	date_default_timezone_set('Asia/Kolkata');
	$function = new functions;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    
    $sql_query = "SELECT id, name 
    	FROM category 
    	ORDER BY id ASC";	
    	// Execute query
    	$db->sql($sql_query);
    	// store result 
    	$res=$db->getResult();
    	$sql_query = "SELECT id, name FROM brand ORDER BY id ASC";	
    	// Execute query
    	$db->sql($sql_query);
    	// store result 
    	$brand_data=$db->getResult();
    $sql_query = "SELECT value FROM settings WHERE variable = 'Currency'";
  
        $db->sql($sql_query);
    	// store result 
    	
        $res_cur=$db->getResult();
    	
    	
    if(isset($_POST['btnAdd'])){
        if($permissions['products']['create']==1){
        // print_r($_POST);
		$name = $db->escapeString($fn->xss_clean($_POST['name']));
		$sql='INSERT INTO prods (name,name1) VALUES("'.$name.'","'.$name.'")';
                //print_r($sql);die;
                $db->sql($sql);
    			$product_id = $db->getResult();
    	// create array variable to handle error
    	$error = array();
    	
    	
        }else{
        $error['check_permission'] = " <div class='content-header'>
                                                <span class='label label-danger'>You have no permission to create product</span>
                                                
                                                
                                                </div>";

        
    }
    }
    ?>
    <div class="row">
        <div class="col-md-12">
		
    <?php echo isset($error['add_menu']) ? $error['add_menu'] : '';?>
             <?php if(!isset($permissions['products']['create']) || $permissions['products']['create']==0) { ?>
                <div class="alert alert-danger">You have no permission to create product.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Add Product</h3>
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form id='add_product_form' method="post" enctype="multipart/form-data">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="exampleInputEmail1">Products Name</label><?php echo isset($error['name']) ? $error['name'] : '';?>
                            <input type="text" class="form-control"  name="name" required>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="submit" class="btn-primary btn" value="Add" name="btnAdd" />&nbsp;
                        <input type="reset" class="btn-danger btn" value="Clear"/>
                        <!--<div  id="res"></div>-->
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
    </div>
<div class="separator"> </div>
      
  
<?php include"footer.php";?>