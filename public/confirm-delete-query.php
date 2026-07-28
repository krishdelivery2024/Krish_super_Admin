<div id="content" class="container col-md-12">
	<?php 
	include_once('includes/custom-functions.php');
    $fn = new custom_functions;
		if(isset($_POST['btnDelete'])){
		    
			if(isset($_GET['id'])){
				$ID = $db->escapeString($fn->xss_clean($_GET['id']));
			}else{
				$ID = "";
			}
			// get image file from table
			// delete data from menu table
			$sql_query = "DELETE FROM faq 
					WHERE id =".$ID;
				
				// Execute query
				$db->sql($sql_query);
				// store result 
				$delete_query_result=$db->getResult();
				if(!empty($delete_query_result)){
					$delete_query_result=0;
				}
					$delete_query_result=1;
			
				
			// if delete data success back to reservation page
			if($delete_query_result==1){
				$url="faq.php";
				echo '<script type="text/javascript">';
				echo 'window.location.href="'.$url.'";';
				echo '</script>';
				echo '<noscript>';
				echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
				echo '</noscript>'; exit;
			}
		}		
		
		if(isset($_POST['btnNo'])){
			$url="faq.php";
				echo '<script type="text/javascript">';
				echo 'window.location.href="'.$url.'";';
				echo '</script>';
				echo '<noscript>';
				echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
				echo '</noscript>'; exit;
		}
		if(isset($_POST['btncancel'])){
			$url="faq.php";
				echo '<script type="text/javascript">';
				echo 'window.location.href="'.$url.'";';
				echo '</script>';
				echo '<noscript>';
				echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
				echo '</noscript>'; exit;
		}
		
	?>
	
<div id="wrapper">
	<div class="main-content">
		<div class="row small-spacing">
			<div class="col-lg-6 col-xs-12">
				<div class="box-content card white">
	<?php if($permissions['faqs']['delete']==1){?>
	<h4 class="box-title">Confirm Delete</h4>
					<!-- /.box-title -->
					<div class="card-content">
	<form method="post">
		<p>Are you sure want to delete this query?</p>
		<input type="submit" class="btn btn-primary" value="Delete" name="btnDelete"/>
		<input type="submit" class="btn btn-danger" value="Cancel" name="btnNo"/>
	</form>
	<div class="separator"> </div></div>
	<?php } else { ?>
		<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to delete faq.</div>
		<form method="post">
		<input type="submit" class="btn btn-danger" value="Back" name="btncancel"/>
	</form>
	<?php } ?>
</div>
</div>
</div>
</div>
</div>
</div>
			
<?php $db->disconnect(); ?>