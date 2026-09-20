<?php $page="Zones";
include"header.php";
$map_key='';
$db->sql("SELECT value FROM settings WHERE variable='store_map_api'");
$mk_res=$db->getResult();
if(!empty($mk_res)){ $map_key=$mk_res[0]['value']; }
if(empty($map_key)){ $map_key='AIzaSyDYXBYj5sA6nxiNvUsSrQKWSvytDzVRM7I'; }
        $db->sql("SELECT id, name, latitude, longitude, zone_id, status, company_name, store_address FROM seller WHERE latitude IS NOT NULL AND latitude != '' AND longitude IS NOT NULL AND longitude != ''");
        $sellers_locations = $db->getResult();
        $db->sql("SELECT id, name, polygon, status FROM zone");
        $zones_map = $db->getResult();
        ?>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <section class="content">
                <div class="row">
                    <div class="col-md-12">
                        <?php if(!$is_sub_admin || $permissions['zones']['read']==1){ ?>
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Seller Locations & Zones</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-default btn-xs" onclick="fitSellerMap()"><i class="fa fa-crosshairs"></i> Fit to Sellers</button>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div id="seller-locations-map" style="width:100%;height:450px;border:1px solid #d2d6de;border-radius:4px;"></div>
                                    <span class="help-block">Green markers = sellers inside a zone. Red markers = sellers outside all zones. Click a marker for seller details.</span>
                                </div>
                            </div>
                            <div class="box">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Zones</h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" <?php if($is_sub_admin && $permissions['zones']['create']==0){echo "disabled";} ?> class="btn btn-primary" data-toggle="modal" data-target="#add-zone-modal">
                                            <i class="fa fa-plus"></i> Add Zone
                                        </button>
                                    </div>
                                </div>
                                <div class="box-body table-responsive">
                                    <table class="table table-hover" data-toggle="table" id="zones"
                                        data-url="api-firebase/get-bootstrap-table-data.php?table=zone"
                                        data-page-list="[5, 10, 20, 50, 100, 200]"
                                        data-show-refresh="true" data-show-columns="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-search="true" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">ID</th>
                                                <th data-field="name" data-sortable="true">Name</th>
                                                <th data-field="no_of_points" data-sortable="true">Polygon Points</th>
                                                <th data-field="status" data-sortable="true">Status</th>
                                                <th data-field="operate">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-danger">You have no permission to view zones</div>
                        <?php } ?>
                    </div>
                </div>
            </section>
        </div>
        <div class="modal fade" id="add-zone-modal" tabindex="-1" role="dialog" aria-labelledby="AddZoneModal">
            <div class="modal-dialog modal-lg" role="document">
                <form method="post" id="add_zone_form" action="public/db-operation.php" class="modal-content">
                    <input type="hidden" id="add_zone" name="add_zone" value="1">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Add / Edit Zone</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="zone_name">Zone Name</label>
                            <input type="text" class="form-control" id="zone_name" name="name" placeholder="e.g. North Krishna" required>
                        </div>
                        <div class="form-group">
                            <label for="zone-map-draw">Draw Zone Boundary</label>
                            <div id="zone-map-draw" style="width:100%;height:360px;border:1px solid #d2d6de;border-radius:4px;"></div>
                            <div class="clearfix" style="margin-top:6px;">
                                <button type="button" class="btn btn-sm btn-default" id="zone-undo-btn">Undo Last Point</button>
                                <button type="button" class="btn btn-sm btn-default" id="zone-clear-btn">Clear</button>
                                <span class="help-block" style="margin:8px 0 0 0;">Click the map to place polygon vertices. Ends are joined automatically. Undo removes the last placed point.</span>
                            </div>
                            <input type="hidden" id="polygon" name="polygon" value="<?php echo isset($_GET['polygon']) ? htmlspecialchars($_GET['polygon'],ENT_QUOTES) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="zone_status">Status</label>
                            <select class="form-control" id="zone_status" name="status">
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Zone</button>
                    </div>
                </form>
            </div>
        </div>
<script>
    window.__zoneEditPolygon = null;
    window.__sellersLoc = <?php echo json_encode($sellers_locations); ?>;
    window.__zonesPool = <?php echo json_encode($zones_map); ?>;
    (function(){
        var zs = document.getElementById('polygon');
        if(zs && zs.value){
            try{ var pr = JSON.parse(zs.value); if(Array.isArray(pr)){ window.__zoneEditPolygon = pr; } }catch(e){}
        }
    })();
</script>
<script>
    var __zoneMapStarted = false;
    var map;
    var pts = [];
    var poly = null;
    var markers = [];

    function redraw(){
        var vertexColor = '#3c8dbc';
        if(poly){ poly.setMap(null); }
        if(!map){ return; }
        if(pts.length>0){
            poly = new google.maps.Polygon({
                paths: pts,
                map: map,
                fillColor: vertexColor,
                fillOpacity: 0.35,
                strokeColor: vertexColor,
                strokeWeight: 2
            });
        } else { poly = null; }
        for(var i=0;i<markers.length;i++){ markers[i].setMap(null); }
        markers = [];
        for(var v=0; v<pts.length; v++){
            markers.push(new google.maps.Marker({
                position: pts[v], map: map,
                icon: {path: google.maps.SymbolPath.CIRCLE, scale: 5, fillColor: '#dd4b39', fillOpacity: 1, strokeColor: '#ffffff', strokeWeight: 2}
            }));
        }
        document.getElementById('polygon').value = JSON.stringify(pts);
        if(pts.length >= 3 && !window.__zoneValidNotified){
            window.__zoneValidNotified = true;
            alert('Polygon is valid (' + pts.length + ' points). You can now click Save Zone.');
        } else if(pts.length < 3){
            window.__zoneValidNotified = false;
        }
    }

    function startDrawEditor(){
        if(typeof google==='undefined' || !document.getElementById('zone-map-draw')){ return; }
        if(__zoneMapStarted){ return; }
        __zoneMapStarted = true;
        
        map = new google.maps.Map(document.getElementById('zone-map-draw'), {
            center: {lat:12.9716, lng:77.5946},
            zoom: 13,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        
        google.maps.event.addListener(map, 'click', function(e){
            pts.push({lat: e.latLng.lat(), lng: e.latLng.lng()});
            redraw();
        });
        
        document.getElementById('zone-undo-btn').onclick = function(){ if(pts.length>0){ pts.pop(); redraw(); } };
        document.getElementById('zone-clear-btn').onclick = function(){ pts=[]; redraw(); };
        
        if(typeof window.__zoneEditPolygon !== 'undefined' && window.__zoneEditPolygon && Array.isArray(window.__zoneEditPolygon)){
            pts = window.__zoneEditPolygon;
            redraw();
        }
        
        setTimeout(function(){ google.maps.event.trigger(map, 'resize'); }, 250);
    }

    function __deferZoneMapStart(){
        $('#add-zone-modal').appendTo('body');
        $('#add-zone-modal').on('shown.bs.modal', function(){
            startDrawEditor();
        });
    }

    var sellerMap = null;
    var sellerMapBounds = null;

    function buildSellerMap(){
        var el = document.getElementById('seller-locations-map');
        if(typeof google==='undefined' || !el || sellerMap){ return; }
        sellerMap = new google.maps.Map(el, {
            center: {lat:12.9716, lng:77.5946},
            zoom: 11,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        sellerMapBounds = new google.maps.LatLngBounds();

        var zones = (typeof window.__zonesPool !== 'undefined') ? window.__zonesPool : [];
        var zonePolys = [];
        for(var z=0; z<zones.length; z++){
            var zp = null;
            try{ zp = JSON.parse(zones[z]['polygon']); }catch(e){ zp = null; }
            if(!zp || !Array.isArray(zp) || zp.length < 3){ continue; }
            var path = [];
            for(var p=0; p<zp.length; p++){
                path.push({lat: parseFloat(zp[p].lat), lng: parseFloat(zp[p].lng)});
            }
            var colors = ['#3c8dbc','#00a65a','#f39c12','#605ca8','#e91e63','#00c0ef','#d81b60'];
            var fill = colors[(zones[z]['id']||0) % colors.length];
            new google.maps.Polygon({
                paths: path,
                map: sellerMap,
                strokeColor: fill,
                strokeWeight: 2,
                fillColor: fill,
                fillOpacity: 0.15
            });
            zonePolys.push({
                id: zones[z]['id'],
                path: path
            });
            for(var k=0;k<path.length;k++){ sellerMapBounds.extend(path[k]); }
        }

        function pointInZone(lat, lng, path){
            var inside = false;
            var j = path.length - 1;
            for(var i=0;i<path.length;i++){
                var xi = path[i].lng, yi = path[i].lat;
                var xj = path[j].lng, yj = path[j].lat;
                var intersect = ((yi > lat) !== (yj > lat)) && (lng < (xj - xi) * (lat - yi) / (yj - yi) + xi);
                if(intersect){ inside = !inside; }
                j = i;
            }
            return inside;
        }

        var sellers = (typeof window.__sellersLoc !== 'undefined') ? window.__sellersLoc : [];
        for(var i=0; i<sellers.length; i++){
            var lat = parseFloat(sellers[i]['latitude']);
            var lng = parseFloat(sellers[i]['longitude']);
            if(isNaN(lat) || isNaN(lng)){ continue; }
            if(lat < -90 || lat > 90 || lng < -180 || lng > 180){ continue; }
            var inZone = false;
            for(var zi=0; zi<zonePolys.length && !inZone; zi++){
                inZone = pointInZone(lat, lng, zonePolys[zi].path);
            }
            var label = (sellers[i]['company_name'] || sellers[i]['name'] || 'Seller').toString();
            var content = '<div style="min-width:150px;"><b>' + label + '</b><br>'
                + 'Zone: ' + (inZone ? 'Inside zone' : 'Outside all zones') + '<br>'
                + (sellers[i]['store_address'] ? sellers[i]['store_address'] : '') + '</div>';
            var marker = new google.maps.Marker({
                position: {lat: lat, lng: lng},
                map: sellerMap,
                title: label,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: inZone ? '#00a65a' : '#dd4b39',
                    fillOpacity: 1,
                    strokeColor: '#ffffff',
                    strokeWeight: 2
                }
            });
            var info = new google.maps.InfoWindow({ content: content });
            google.maps.event.addListener(marker, 'click', function(iw){ return function(){ iw.open(sellerMap, this); }; }(info));
            sellerMapBounds.extend({lat: lat, lng: lng});
        }

        if(sellers.length > 0){
            google.maps.event.addListenerOnce(sellerMap, 'idle', function(){
                sellerMap.fitBounds(sellerMapBounds);
            });
        }
    }

    function fitSellerMap(){
        if(sellerMap){ sellerMap.fitBounds(sellerMapBounds); }
    }

    function initZoneMap(){
        if(typeof google==='undefined'){ return; }
        buildSellerMap();
        if(document.getElementById('zone-map-draw')){
            window.__zoneEditPolygon = null;
            var zs = document.getElementById('polygon');
            if(zs && zs.value){
                try{ var pr = JSON.parse(zs.value); if(Array.isArray(pr)){ window.__zoneEditPolygon = pr; } }catch(e){}
            }
            __deferZoneMapStart();
        }
    }

    if(typeof google!=='undefined' && google.maps){ __deferZoneMapStart(); }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=$map_key;?>&callback=initZoneMap" async defer></script>
<?php include"footer.php";?>
