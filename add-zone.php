<?php $page="Add Zone";
include"header.php";?>
<?php
include_once('includes/crud.php');
$db = new Database();
$db->connect();
$db->sql("SET NAMES 'utf8'");
include_once('includes/custom-functions.php');
$fn = new custom_functions;
$map_key='';
$db->sql("SELECT value FROM settings WHERE variable='store_map_api'");
$mk_res=$db->getResult();
if(!empty($mk_res)){ $map_key=$mk_res[0]['value']; }
if(empty($map_key)){ $map_key='AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I'; }
?>
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
								<label class="col-sm-2 control-label">Boundary Polygon</label>
								<div class="col-sm-8">
									<div id="add-zone-map" style="width:100%;height:360px;border:1px solid #d2d6de;border-radius:4px;"></div>
									<div class="clearfix" style="margin-top:6px;">
										<button type="button" class="btn btn-sm btn-default" id="add-zone-my-location-btn">Use My Location</button>
										<button type="button" class="btn btn-sm btn-default" id="add-zone-undo-btn">Undo Last Point</button>
										<button type="button" class="btn btn-sm btn-default" id="add-zone-clear-btn">Clear</button>
										<span class="help-block" style="margin:8px 0 0 0;">Click the map to add polygon vertices. Drag any marker to rearrange it. Ends are joined automatically. Undo removes the last placed point.</span>
									</div>
									<textarea class="form-control" name="polygon" id="add-zone-polygon" rows="5" placeholder="Coordinates will appear here as you click or drag points on the map." required></textarea>
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
<script>
var azMapStarted = false;
var azMap = null;
var azPts = [];
var azPoly = null;
var azMarkers = [];
var azLocMarker = null;

function azRedraw(){
	var vc = '#3c8dbc';
	if(azPoly){ azPoly.setMap(null); }
	if(!azMap){ return; }
	if(azPts.length > 0){
		azPoly = new google.maps.Polygon({
			paths: azPts, map: azMap,
			fillColor: vc, fillOpacity: 0.35,
			strokeColor: vc, strokeWeight: 2
		});
	} else { azPoly = null; }
	for(var i=0;i<azMarkers.length;i++){ azMarkers[i].setMap(null); }
	azMarkers = [];
	for(var v=0; v<azPts.length; v++){
		addAzMarker(v, azPts[v]);
	}
	document.getElementById('add-zone-polygon').value = JSON.stringify(azPts);
}

function addAzMarker(index, pos){
	var m = new google.maps.Marker({
		position: pos, map: azMap, draggable: true,
		icon: {path: google.maps.SymbolPath.CIRCLE, scale: 5, fillColor: '#dd4b39', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2}
	});
	m.addListener('dragend', function(e){
		azPts[index] = {lat: e.latLng.lat(), lng: e.latLng.lng()};
		azRedraw();
	});
	azMarkers.push(m);
}

function azStartAt(center){
	if(azMap){ return; }
	azMap = new google.maps.Map(document.getElementById('add-zone-map'), {
		center: center, zoom: 13,
		mapTypeId: google.maps.MapTypeId.ROADMAP
	});

	google.maps.event.addListener(azMap, 'click', function(e){
		azPts.push({lat: e.latLng.lat(), lng: e.latLng.lng()});
		azRedraw();
	});

	document.getElementById('add-zone-undo-btn').onclick = function(){
		if(azPts.length > 0){ azPts.pop(); azRedraw(); }
	};
	document.getElementById('add-zone-clear-btn').onclick = function(){
		azPts = []; azRedraw();
	};
	document.getElementById('add-zone-my-location-btn').onclick = function(){
		if(azMap && navigator.geolocation){
			navigator.geolocation.getCurrentPosition(function(pos){
				var loc = {lat: pos.coords.latitude, lng: pos.coords.longitude};
				azMap.setCenter(loc); azMap.setZoom(14);
				if(azLocMarker){ azLocMarker.setMap(null); }
				azLocMarker = new google.maps.Marker({position: loc, map: azMap, title: 'My Location'});
				azPts.push(loc);
				azRedraw();
			});
		}
	};
}

function initAddZoneMap(){
	if(typeof google === 'undefined' || !document.getElementById('add-zone-map')){ return; }
	if(azMapStarted){ return; }
	azMapStarted = true;

	var defaultCenter = {lat:12.9716, lng:77.5946};
	if(navigator.geolocation){
		navigator.geolocation.getCurrentPosition(function(pos){
			azStartAt({lat: pos.coords.latitude, lng: pos.coords.longitude});
		}, function(){
			azStartAt(defaultCenter);
		}, {timeout: 8000, maximumAge: 60000});
	} else {
		azStartAt(defaultCenter);
	}
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=$map_key;?>&callback=initAddZoneMap" async defer></script>
<?php include"footer.php";?>