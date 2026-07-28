<?php $page="Delete Route";
include"header.php";?>      
        <div id="content" class="container col-md-12">
	<?php 
		
		if(isset($_POST['btnDelete'])){
		    include_once('includes/custom-functions.php');
    		$fn = new custom_functions;
			if(isset($_GET['id'])){
				$ID = $db->escapeString($fn->xss_clean($_GET['id']));
			}else{
				$ID = "";
			}
			// get image file from table
			$sql_query = "SELECT short_code 
					FROM routes 
					WHERE id =".$ID;
				// Execute query
				$db->sql($sql_query);
				// store result 
				$res=$db->getResult();
				$sql_query = "DELETE FROM routes 
					WHERE id =".$ID;
				// Execute query
				$db->sql($sql_query);
				// store result
				$delete_route_result = $db->getResult();
				if(!empty($delete_route_result)){
					$delete_route_result =0;
				}else{
					$delete_route_result =1;
				}
			
				
			// if delete data success back to reservation page
            if($delete_route_result==1 ){
				header("location: routes.php");
			}
		
		}		
		
		if(isset($_POST['btnNo'])){
			header("location: routes.php");
		}
		if(isset($_POST['btncancel'])){
			header("location: routes.php");
		}
		
	?>
	<?php if($permissions['locations']['delete']==1){?>
	<h1>Confirm Action</h1>
	<hr />
	<form method="post">
		<p>Are you sure want to delete this route</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm">You have no permission to delete Route.</div>
		<form method="post">
		<input type="submit" class="btn btn-danger" value="Back" name="btncancel"/>
	</form>
	<?php } ?>
</div>
			
<?php $db->disconnect(); ?>
      
  
<?php include"footer.php";?>
    	