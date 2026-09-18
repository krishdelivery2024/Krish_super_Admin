<?php
$page="Update Main Category Status";
include"header.php";

include_once('includes/functions.php');
include_once('includes/custom-functions.php');
$fn = new custom_functions;

if($permissions['categories']['update']==1){

	if(isset($_GET['id']) && isset($_GET['status'])){
		$ID = $db->escapeString($fn->xss_clean($_GET['id']));
		$status = $db->escapeString($fn->xss_clean($_GET['status']));
		if($status == 1 || $status == 0){
			$sql_query = "UPDATE main_category SET status = '$status' WHERE id = ".$ID;
			$db->sql($sql_query);
			$db->getResult();
			$msg = ($status == 1) ? "Main Category enabled successfully." : "Main Category disabled successfully.";
			$type = "success";
		}else{
			$msg = "Invalid status value.";
			$type = "danger";
		}
	}else{
		$msg = "Missing id or status.";
		$type = "danger";
	}

	if(isset($_GET['return'])){
		$redirect = $db->escapeString($fn->xss_clean($_GET['return']));
	}else{
		$redirect = "main_categories.php";
	}
	?>
	<div class="content-header">
		<span class="label label-<?php echo $type; ?>"><?php echo $msg; ?></span>
		<h4><small><a href="<?php echo $redirect; ?>"><i class="fa fa-angle-double-left"></i>&nbsp;&nbsp;&nbsp;Back to Main Categories</a></small></h4>
	</div>
	<script>setTimeout(function(){ window.location.href = "<?php echo $redirect; ?>"; }, 1500);</script>
	<?php
}else{
	?>
	<div class="content-header">
		<span class="label label-danger">You have no permission to update category</span>
		<h4><small><a href="main_categories.php"><i class="fa fa-angle-double-left"></i>&nbsp;&nbsp;&nbsp;Back to Main Categories</a></small></h4>
	</div>
	<?php
}

include"footer.php";
?>