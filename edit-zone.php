<?php $page="Edit Zone";
include"header.php";?>
<?php
include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");
include_once('includes/custom-functions.php');
$fn = new custom_functions;
include_once('includes/zone-functions.php');
$zone_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$zone = null; $polygon_value = '[]';
if ($zone_id > 0) {
	$db->sql("SELECT * FROM zone WHERE id=".$zone_id);
	$res = $db->getResult();
	if (isset($res[0])) { $zone = $res[0]; }
}
if (empty($zone)) {
	echo '<div class="content-wrapper"><div class="alert alert-danger">Zone not found!</div></div>';
	include"footer.php";
	exit;
}
$polygon_value = !empty($zone['polygon']) ? $zone['polygon'] : '[]';?>
<?php
	// Fetch the zone to edit.
	$zone_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
	$zone = null;
	if ($zone_id > 0) {
		$db->sql("SELECT * FROM zone WHERE id=".$zone_id);
		$res = $db->getResult();
		$zone = isset($res[0]) ? $res[0] : null;
	}
	if (empty($zone)) {
		echo '<div class="content-wrapper"><section class="content"><div class="alert alert-danger">Zone not found.</div></section></div>';
		include"footer.php";
		exit;
	}
	$polygon_value = !empty($zone['polygon']) ? $zone['polygon'] : '[]';
?>
	<div class="content-wrapper">
		<section class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="box box-primary">
						<div class="box-header with-border">
							<h3 class="box-title">Edit Zone</h3>
						</div>
						<form method="post" action="public/db-operation.php" class="form-horizontal">
							<input type="hidden" name="update_zone" value="1">
							<input type="hidden" name="zone_id" value="<?php echo $zone['id'];?>">
							<div class="box-body">
								<div class="form-group">
									<label class="col-sm-2 control-label">Zone Name</label>
									<div class="col-sm-8">
										<input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($zone['name']);?>" required>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Boundary Polygon (JSON)</label>
									<div class="col-sm-8">
										<div id="edit-zone-map" style="width:100%;height:360px;border:1px solid #d2d6de;border-radius:4px;"></div>
										<div class="clearfix" style="margin-top:6px;">
											<button type="button" class="btn btn-sm btn-default" id="edit-zone-undo-btn">Undo Last Point</button>
											<button type="button" class="btn btn-sm btn-default" id="edit-zone-clear-btn">Clear</button>
											<span class="help-block" style="margin:8px 0 0 0;">Click the map to add polygon vertices. Ends are joined automatically. Undo removes the last placed point.</span>
										</div>
										<textarea class="form-control" name="polygon" id="edit-zone-polygon" rows="5" required><?php echo htmlspecialchars($polygon_value);?></textarea>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">Status</label>
									<div class="col-sm-8">
										<select class="form-control" name="status">
											<option value="1" <?php if($zone['status']==1){echo "selected";}?>>Enabled</option>
											<option value="0" <?php if($zone['status']==0){echo "selected";}?>>Disabled</option>
										</select>
									</div>
								</div>
							</div>
							<div class="box-footer">
								<button type="submit" class="btn btn-primary">Update Zone</button>
								<a href="zones.php" class="btn btn-default">Cancel</a>
							</div>
						</form>
					</div>
				</div>
			</div>
		</section>
	</div>
<?php
	$map_key='';
	$db->sql("SELECT value FROM settings WHERE variable='store_map_api'");
	$mk_res=$db->getResult();
	if(!empty($mk_res)){ $map_key=$mk_res[0]['value']; }
	if(empty($map_key)){ $map_key='AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I'; }
?>
<script>
window.__editZonePolygon = null;
(function(){
	var zs = document.getElementById('edit-zone-polygon');
	if(zs && zs.value){
		try{ var pr = JSON.parse(zs.value); if(Array.isArray(pr)){ window.__editZonePolygon = pr; } }catch(e){}
	}
})();
</script>
<script>
var ezMapStarted = false;
var ezMap = null;
var ezPts = [];
var ezPoly = null;
var ezMarkers = [];

function ezRedraw(){
	var vc = '#3c8dbc';
	if(ezPoly){ ezPoly.setMap(null); }
	if(!ezMap){ return; }
	if(ezPts.length > 0){
		ezPoly = new google.maps.Polygon({
			paths: ezPts, map: ezMap,
			fillColor: vc, fillOpacity: 0.35,
			strokeColor: vc, strokeWeight: 2
		});
	} else { ezPoly = null; }
	for(var i=0;i<ezMarkers.length;i++){ ezMarkers[i].setMap(null); }
	ezMarkers = [];
	for(var v=0; v<ezPts.length; v++){
		addEzMarker(v, ezPts[v]);
	}
	document.getElementById('edit-zone-polygon').value = JSON.stringify(ezPts);
}

function addEzMarker(index, pos){
	var m = new google.maps.Marker({
		position: pos, map: ezMap, draggable: true,
		icon: {path: google.maps.SymbolPath.CIRCLE, scale: 5, fillColor: '#dd4b39', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2}
	});
	m.addListener('dragend', function(e){
		ezPts[index] = {lat: e.latLng.lat(), lng: e.latLng.lng()};
		ezRedraw();
	});
	ezMarkers.push(m);
}

function initEditZoneMap(){
	if(typeof google === 'undefined' || !document.getElementById('edit-zone-map')){ return; }
	if(ezMapStarted){ return; }
	ezMapStarted = true;

	var loaded = (window.__editZonePolygon && Array.isArray(window.__editZonePolygon)) ? window.__editZonePolygon : [];
	ezPts = [];
	for(var i=0;i<loaded.length;i++){
		ezPts.push({lat: parseFloat(loaded[i].lat), lng: parseFloat(loaded[i].lng)});
	}

	var center = {lat:12.9716, lng:77.5946};
	var bounds = new google.maps.LatLngBounds();
	if(ezPts.length > 0){
		for(var b=0;b<ezPts.length;b++){ bounds.extend(ezPts[b]); }
		center = bounds.getCenter();
	}

	ezMap = new google.maps.Map(document.getElementById('edit-zone-map'), {
		center: center,
		zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	if(ezPts.length >= 3){ ezRedraw(); }

	google.maps.event.addListener(ezMap, 'click', function(e){
		ezPts.push({lat: e.latLng.lat(), lng: e.latLng.lng()});
		ezRedraw();
	});

	document.getElementById('edit-zone-undo-btn').onclick = function(){
		if(ezPts.length > 0){ ezPts.pop(); ezRedraw(); }
	};
	document.getElementById('edit-zone-clear-btn').onclick = function(){
		ezPts = []; ezRedraw();
	};

	if(ezPts.length >= 3){
		google.maps.event.addListenerOnce(ezMap, 'idle', function(){
			ezMap.fitBounds(bounds);
		});
	} else if(ezPts.length === 0 && navigator.geolocation){
		navigator.geolocation.getCurrentPosition(function(pos){
			ezMap.setCenter({lat: pos.coords.latitude, lng: pos.coords.longitude});
			ezMap.setZoom(14);
		}, function(){}, {timeout: 8000, maximumAge: 60000});
	} else {
		setTimeout(function(){ google.maps.event.trigger(ezMap, 'resize'); }, 250);
	}
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=$map_key;?>&callback=initEditZoneMap" async defer></script>
<?php include"footer.php";?>
