<?php 

    include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    
    include('includes/variables.php');
    include_once('includes/custom-functions.php');
    
    $fn = new custom_functions;
    $config = $fn->get_configurations();
    
    $sql = "SELECT id, name FROM zone WHERE status=1 ORDER BY id DESC";
    $db->sql($sql);
    $zones = $db->getResult();

    $my_zone_scope = $fn->get_zone_scope(isset($_SESSION['id']) ? $_SESSION['id'] : 0);
    if(!empty($my_zone_scope)){
        $zones = array_values(array_filter($zones, function($z) use ($my_zone_scope){ return in_array((int)$z['id'], $my_zone_scope); }));
    }
    ?>
    <!-- Main row -->
    <div class="row">
        <div class="col-md-6">
            <?php if($permissions['delivery_boys']['create']==0){?>
                <div class="alert alert-danger">You have no permission to create delivery boy</div>
            <?php } ?>
              <!-- general form elements -->
              <div class="box box-primary">
                <div class="box-header with-border">
                  <h3 class="box-title">Add Delivery Boy</h3>

                </div><!-- /.box-header -->
                <!-- form start -->
                <form  method="post" id="add_form" action="public/db-operation.php" enctype="multipart/form-data">
                    <input type="hidden" id="add_delivery_boy" name="add_delivery_boy" required="" value="1" aria-required="true">
                  <div class="box-body">
                    <div class="form-group">
                      <label for="">Name</label>
                      <input type="text" class="form-control"  name="name">
                    </div>
                              <div class="form-group">
                      <label for="">Mobile</label>
                      <input type="text" class="form-control"  name="mobile">
                    </div>
                    <div class="form-group">
                      <label for="">Password</label>
                      <input type="password" class="form-control"  name="password" id="password">
                    </div>
                    <div class="form-group">
                      <label for="">Confirm Password</label>
                      <input type="password" class="form-control"  name="confirm_password">
                    </div>
                    
                      <label for="">Address</label>
                    <div class="form-group">
                      
                      <textarea name="address" id="address" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                      <label for="">Bonus (%)</label>
                      <input type="number" class="form-control"  name="bonus" id="bonus" value="<?=$config['delivery-boy-bonus-percentage']?>">
                    </div>
                    <div class="form-group">
                      <label for="">Service Type</label>
                      <select class="form-control" name="service_type">
                        <option value="both">Both (Food &amp; Parcel)</option>
                        <option value="food">Food Only</option>
                        <option value="parcel">Parcel Only</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="">Zone</label>
                      <select class="form-control" name="zone_id" id="zone_id">
                          <option value="">Select Zone (Leave empty for All Zones)</option>
                          <?php foreach($zones as $zone){ ?>
                              <option value="<?=$zone['id']?>"><?=$zone['name']?></option>
                          <?php } ?>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="">Aadhaar Number <span style="color:red;">*</span></label>
                      <input type="text" class="form-control" name="aadhaar" id="aadhaar" maxlength="12" placeholder="12-digit Aadhaar number">
                    </div>
                    <div class="form-group">
                      <label for="">Driving License Number <span style="color:red;">*</span></label>
                      <input type="text" class="form-control" name="driving_license" id="driving_license" placeholder="e.g. MH12 20250012345">
                    </div>
                    <div class="form-group">
                      <label for="">Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="profile" id="profile" accept="image/*">
                    </div>
                    <div class="form-group">
                      <label for="">Aadhaar Card Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="aadhaar_image" id="aadhaar_image" accept="image/*">
                    </div>
                    <div class="form-group">
                      <label for="">Driving License Photo <span style="color:red;">*</span></label>
                      <input type="file" class="form-control" name="driving_license_image" id="driving_license_image" accept="image/*">
                    </div>
                    
                    
                  </div><!-- /.box-body -->

                  <div class="box-footer">
                    <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                    <input type="reset" class="btn-warning btn" value="Clear"/>
                
                  </div>
                  <div class="form-group">
                      
                      <div id="result" style="display: none;"></div>
                    </div>
                </form>
              </div><!-- /.box -->
             </div>
        <!-- Live delivery boy locations (OpenStreetMap) -->
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Live Delivery Boy Locations</h3>
                    <div class="box-tools pull-right">
                        <span class="label label-success" id="db-live-count">0 online</span>
                        <button type="button" class="btn btn-default btn-sm" id="db-live-refresh" title="Refresh now">
                            <i class="fa fa-refresh"></i>
                        </button>
                    </div>
                </div><!-- /.box-header -->
                <div class="box-body">
                    <div id="delivery-boys-live-map" style="height:500px; width:100%;"></div>
                    <p class="help-block" id="delivery-boys-live-status" style="margin-top:8px;">Loading online delivery boy locations&hellip;</p>
                </div>
            </div><!-- /.box -->
        </div>
        <!-- Left col -->
        <div class="col-md-12">
            <?php if($permissions['delivery_boys']['read']==1){?>
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Delivery Boys</h3>
                    <div class="box-tools pull-right">
                        <select id="online_filter" class="form-control input-sm" style="width:150px;">
                            <option value="">All (Online &amp; Offline)</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="delivery-boys"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=delivery-boys"
                        data-query-params="deliveryBoyQueryParams"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile</th>
                            <th data-field="address" data-sortable="true">Address</th>
                            <th data-field="bonus" data-sortable="true">Bonus(%)</th>
                            <th data-field="balance" data-sortable="true">Balance</th>
                            <th data-field="service_type" data-sortable="true">Service Type</th>
                            <th data-field="aadhaar" data-sortable="true">Aadhaar</th>
                            <th data-field="driving_license" data-sortable="true">DL No</th>
                            <th data-field="profile">Photo</th>
                            <th data-field="aadhaar_image">Aadhaar Photo</th>
                            <th data-field="driving_license_image">DL Photo</th>
                            <th data-field="online_status">Online Status</th>
                            <th data-field="status">Status</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view delivery boys</div>
            <?php }?>
        </div>
        <div class="separator"> </div>
    </div>
    <script>
    // Sends the selected online/offline filter along with each table request.
    // bootstrap-table resolves the data-query-params attribute to this global
    // function, so the filter survives paging, sorting and refreshing.
    window.deliveryBoyQueryParams = function (p) {
        var online = $('#online_filter').val();
        if (online) {
            p.online = online;
        }
        return p;
    };
    $(document).on('change', '#online_filter', function () {
        $('#delivery-boys').bootstrapTable('refresh');
    });
    </script>

    <!-- OpenStreetMap (Leaflet) - served locally -->
    <link rel="stylesheet" href="dist/plugin/leaflet/leaflet.css" />
    <script src="dist/plugin/leaflet/leaflet.js"></script>

    <!-- Live online delivery boy markers, refreshed every 20 seconds -->
    <script>
    (function () {
        var mapEl = document.getElementById('delivery-boys-live-map');
        if (!mapEl || typeof L === 'undefined') { return; }

        var fallbackLat = 20.5937, fallbackLng = 78.9629;
        var map = L.map('delivery-boys-live-map').setView([fallbackLat, fallbackLng], 5);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var markers = {};      // delivery boy id -> L.Marker
        var statusEl = document.getElementById('delivery-boys-live-status');
        var countEl = document.getElementById('db-live-count');
        var fitted = false;

        function esc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
            });
        }

        function popupHtml(boy) {
            var seen = (boy.age_seconds <= 5) ? 'just now' : (boy.age_seconds + 's ago');
            return '<strong>' + esc(boy.name) + '</strong><br>' +
                   'Mobile: ' + esc(boy.mobile) + '<br>' +
                   'Updated: ' + esc(seen);
        }

        function refresh() {
            fetch('api-firebase/get-delivery-boy-locations.php', { credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) {
                        throw new Error('HTTP ' + r.status);
                    }
                    return r.json();
                })
                .then(function (res) {
                    if (!res || res.error) {
                        console.warn('Live location endpoint error:', res);
                        if (statusEl) { statusEl.innerHTML = 'Could not load live locations.'; }
                        return;
                    }
                    var boys = res.data || [];
                    var seenIds = {};

                    boys.forEach(function (boy) {
                        seenIds[boy.id] = true;
                        var latlng = [boy.latitude, boy.longitude];
                        if (markers[boy.id]) {
                            markers[boy.id].setLatLng(latlng);
                            markers[boy.id].setPopupContent(popupHtml(boy));
                        } else {
                            markers[boy.id] = L.marker(latlng).addTo(map).bindPopup(popupHtml(boy));
                        }
                    });

                    // Remove markers for boys who went offline or stopped reporting.
                    Object.keys(markers).forEach(function (id) {
                        if (!seenIds[id]) {
                            map.removeLayer(markers[id]);
                            delete markers[id];
                        }
                    });

                    if (countEl) { countEl.innerHTML = boys.length + ' online'; }

                    if (statusEl) {
                        statusEl.innerHTML = boys.length
                            ? ('Showing ' + boys.length + ' online delivery boy' + (boys.length === 1 ? '' : 's') + '. Refreshes every 20 seconds.')
                            : 'No online delivery boys are reporting a location right now.';
                    }

                    // Fit the view to the markers the first time we actually can.
                    // If invalidateSize/fitBounds throws (container not sized yet)
                    // we keep fitted=false so the next refresh retries.
                    if (boys.length && !fitted) {
                        try {
                            map.invalidateSize();
                            var pts = boys.map(function (b) { return [b.latitude, b.longitude]; });
                            map.fitBounds(pts, { padding: [30, 30], maxZoom: 15 });
                            fitted = true;
                        } catch (e) {
                            console.warn('Live map not ready yet, retrying on next refresh', e);
                        }
                    }
                })
                .catch(function (err) {
                    console.error('Live delivery boy locations failed:', err);
                    if (statusEl) {
                        statusEl.innerHTML = 'Could not load live locations. If this persists, open the browser console (F12) for details.';
                    }
                });
        }

        refresh();
        setInterval(refresh, 20000);
        document.getElementById('db-live-refresh').addEventListener('click', function () {
            refresh();
        });
        window.addEventListener('load', function () { map.invalidateSize(); });
        // Absorbs slow layouts (fonts, AdminLTE) that can size the box late.
        var sizeTimer = 0;
        var fixSize = function () {
            map.invalidateSize();
            if (!fitted) { refresh(); }
        };
        while (sizeTimer <= 3) {
            setTimeout(fixSize, sizeTimer * 1000);
            sizeTimer += 1;
        }
    })();
    </script>
