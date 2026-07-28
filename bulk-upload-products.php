<?php $page="Bulk Upload Products";
include"header.php";?>
    <div class="row">
        <div class="col-md-6">
		
    <?php echo isset($error['add_menu']) ? $error['add_menu'] : '';?>
             <?php if(!isset($permissions['products']['create']) || $permissions['products']['create']==0) { ?>
                <div class="alert alert-danger">You have no permission to create product.</div>
            <?php } ?>
            <!-- general form elements -->
            <div class="box box-primary">
			<div class="box-header with-border">
			<a href="upload.xls" class="btn btn-info"> <i class="fa fa-download"></i> Download Sample Excel File </a>
			<a href="download-product-excel.php" style="margin-top: 20px;" class="mt-5 btn btn-success"> <i class="fa fa-download"></i> Download All Products In Excel File </a>
                </div>
   
	<form action="import-products.php" method="post" enctype="multipart/form-data">
    <div class="box-body">
	<!--<h4 class="">Upload Only CSV, XLS Extensions Files</h4>
    <h4 class="">Refer or Use Sample Excel File To Upload</h4>-->
                        <div class="form-group">
          <fieldset class="form-group">
            <label for="logo">Upload File</label>
            <input type="file" class="form-control-file" id="file" name="file">
            <small>Allowed Extensions: csv, xls</small>
          </fieldset>
		
      <div class="form-actions"> <button type='submit' class="btn btn-primary"><i class="fa fa fa-check-square-o"></i>Upload</button></div>
      </div>
    
    </div>
    
    </form>
            <!-- /.box -->
        </div>
    </div>
<div class="separator"> </div>
      
  
<?php include"footer.php";?>