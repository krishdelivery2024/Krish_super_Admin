<?php $page="Delete Brand";
include"header.php";?>      
        <div id="content" class="container col-md-12">
	<?php 
		include_once('includes/custom-functions.php');
    $fn = new custom_functions;
		if(isset($_POST['btnDelete'])){
		    
			if(isset($_GET['id'])){
				$ID = $db->escapeString($fn->xss_clean($_GET['id']));
				// get image file from table
			$sql_query = "SELECT image FROM brand WHERE id =".$ID;
				// Execute query
				$db->sql($sql_query);
				// // store result 
				$res=$db->getResult();
			// delete image file from directory
				unlink($res[0]['image']);
			
			// delete data from menu table
			$sql_query = "DELETE FROM brand WHERE id =".$ID;
			echo $sql_query;
				// Execute query
				$db->sql($sql_query);
				// store result 
				$delete_category_result = $db->getResult();
				header("location: brands.php");
			}else{
				$ID = "";
			}
			
		}		
		
		if(isset($_POST['btnNo'])){
			header("location: brands.php");
		}
		if(isset($_POST['btncancel'])){
			header("location: brands.php");
		}
		
	?>
	<h1>Confirm Action</h1>
	<?php 
	if($permissions['categories']['delete']==1){?>
	<hr />
	<form method="post">
		<p>Are you sure want to delete this Brand?.</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
	<?php } else { ?>
	<div class="alert alert-danger topmargin-sm">You have no permission to delete Brand.</div>
	<form method="post">
	<input type="submit" class="btn btn-danger" value="Back" name="btncancel"/>
	</form>
	<?php } ?>
</div>
			
<?php $db->disconnect(); ?>
<?php include"footer.php";?>