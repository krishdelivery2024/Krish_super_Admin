<?php
$page="Zones";
include_once('includes/crud.php');
$db=new Database();
$db->connect();
include_once('includes/custom-functions.php');
$fn=new custom_functions;
$permissions=$fn->get_permissions();
$zones=array();
$db->sql("SELECT * FROM zone ORDER BY id DESC");
$zones=$db->getResult();
?>
<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Zones</h3>
		<div class="box-tools pull-right">
			<?php if($permissions['zones']['create']==1){?>
				<a href="add-zone.php" class="btn btn-primary"><i class="fa fa-plus"></i> Add Zone</a>
			<?php } ?>
		</div>
	</div>
	<div class="box-body table-responsive">
		<?php if($permissions['zones']['read']==1){?>
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Polygon Points</th>
						<th>Status</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($zones as $row){ 
						$points='';
						if(!empty($row['polygon'])):
							$poly=json_decode($row['polygon'],true);
							$points=is_array($poly)?count($poly).' point(s)':'invalid';
						endif;
					?>
						<tr>
							<td><?php echo $row['id'];?></td>
							<td><?php echo htmlspecialchars($row['name']);?></td>
							<td><?php echo $points;?></td>
							<td><?php echo ($row['status']==1)?'<span class="label label-success">Enabled</span>':'<span class="label label-danger">Disabled</span>';?></td>
							<td>
								<a href="edit-zone.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i></a>
								<a href="public/db-operation.php?delete_zone=1&id=<?php echo $row['id'];?>" onclick="return confirm('Delete this zone?');"><i class="fa fa-trash" style="color:#f44336;"></i></a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<div class="alert alert-danger">You have no permission to view zones.</div>
		<?php } ?>
	</div>
</div>
