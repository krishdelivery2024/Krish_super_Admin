<?php $page="Add Zone";
include"header.php";?>
<div class="content-wrapper">
	<section class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-primary">
					<div class="box-header with-border"><h3 class="box-title">Add Zone</h3></div>
					<form method="post" action="public/db-operation.php" class="form-horizontal" enctype="multipart/form-data">
						<input type="hidden" name="add_zone" value="1">
						<div class="box-body">
							<div class="form-group">
								<label class="col-sm-2 control-label">Zone Name</label>
								<div class="col-sm-8">
									<input type="text" class="form-control" name="name" placeholder="e.g. North Krishna" required>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">Boundary Polygon (JSON)</label>
								<div class="col-sm-8">
									<textarea class="form-control" name="polygon" rows="5" placeholder='[{"lat":12.9716,"lng":77.5946},{"lat":12.9816,"lng":77.6046},{"lat":12.9616,"lng":77.6146}]' required></textarea>
									<p class="help-block">Zone boundary as an array of point objects (lat/lng). A map draw editor can be wired to the store_map_api key later.</p>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-2 control-label">Status</label>
								<div class="col-sm-8">
									<select class="form-control" name="status">
										<option value="1">Enabled</option>
										<option value="0">Disabled</option>
									</select>
								</div>
							</div>
						</div>
						<div class="box-footer">
							<button type="submit" class="btn btn-primary">Save Zone</button>
							<a href="zones.php" class="btn btn-default">Cancel</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</section>
</div>
<?php include"footer.php";?>
