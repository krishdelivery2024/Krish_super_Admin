<?php $page="Zone Settings";
include"header.php";?>
<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if(!$is_sub_admin || $permissions['zones']['read']==1){ ?>
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Zone Settings</h3>
                            <span class="help-block" style="margin:0 0 0 8px;display:inline-block;">Per-zone delivery fees and the announcement banner. Click <b>Edit Settings</b> to change a zone. Customers in a zone see its banner right below the app's home slider.</span>
                        </div>
                        <div class="box-body table-responsive">
                            <table class="table table-hover" data-toggle="table" id="zone-settings"
                                data-url="api-firebase/get-bootstrap-table-data.php?table=zone&source=zone_settings"
                                data-page-list="[10, 20, 50, 100]"
                                data-show-refresh="true" data-show-columns="true"
                                data-side-pagination="server" data-pagination="true"
                                data-search="true" data-trim-on-search="false"
                                data-sort-name="id" data-sort-order="desc">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">ID</th>
                                        <th data-field="name" data-sortable="true">Zone</th>
                                        <th data-field="status" data-sortable="false">Status</th>
                                        <th data-field="announcement" data-sortable="false">Announcement Banner</th>
                                        <th data-field="auto_weather" data-sortable="false">Auto on Rain</th>
                                        <th data-field="platform_fee" data-sortable="false">Platform Fee</th>
                                        <th data-field="convenience_fee" data-sortable="false">Convenience Fee</th>
                                        <th data-field="nearby_radius" data-sortable="false">Nearby Radius</th>
                                        <th data-field="service_time" data-sortable="false">Service Time</th>
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
<div class="modal fade" id="edit-zone-settings-modal" tabindex="-1" role="dialog" aria-labelledby="EditZoneSettingsModal">
    <div class="modal-dialog modal-lg" role="document">
        <form method="post" id="edit_zone_settings_form" action="public/db-operation.php" class="modal-content">
            <input type="hidden" name="update_zone_settings" value="1">
            <input type="hidden" id="zs_zone_id" name="zone_id" value="0">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Zone Settings</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><b>Fees</b></h5>
                        <div class="form-group">
                            <label for="zs_platform_fee">Platform Fee (&#8377;)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="zs_platform_fee" name="platform_fee" placeholder="Leave blank to use the global fee">
                            <small class="text-muted d-block">Flat fee added to every parcel and food order picked up in this zone.</small>
                        </div>
                        <div class="form-group">
                            <label for="zs_convenience_fee">Convenience Fee (%)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="zs_convenience_fee" name="convenience_fee" placeholder="Leave blank to use the global fee">
                            <small class="text-muted d-block">Percentage of the item subtotal. Set it to 0 to charge no convenience fee here.</small>
                        </div>
                        <div class="form-group">
                            <label for="zs_nearby_radius_km">Nearby Store Radius (km)</label>
                            <input type="number" step="0.1" min="0" class="form-control" id="zs_nearby_radius_km" name="nearby_radius_km" placeholder="Leave blank to use the global radius">
                            <small class="text-muted d-block">Customers in this zone see home stores within this distance. Empty = inherit the global setting (default 4 km).</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5><b>Announcement Banner</b></h5>
                        <div class="form-group">
                            <label for="zs_announcement_text">Announcement / Notice Text</label>
                            <textarea class="form-control" id="zs_announcement_text" name="announcement_text" rows="3" placeholder="e.g. Heavy rain expected in this area — deliveries may be delayed."></textarea>
                            <small class="text-muted d-block">Shown to this zone's customers below the home slider. Leave empty to hide the banner.</small>
                        </div>
                        <div class="form-group">
                            <label>Show Announcement Banner</label><br>
                            <input type="checkbox" id="zs-banner-button" class="js-switch">
                            <input type="hidden" id="zs_announcement_enabled" name="announcement_enabled" value="0">
                            <small class="text-muted d-block">When ON, always show the banner for this zone (provided text is set).</small>
                        </div>
                        <div class="form-group">
                            <label>Auto-enable on Rain</label><br>
                            <input type="checkbox" id="zs-auto-weather-button" class="js-switch">
                            <input type="hidden" id="zs_announcement_auto_weather" name="announcement_auto_weather" value="0">
                            <small class="text-muted d-block">When ON and Open-Meteo reports rain for this zone, the banner shows even if the switch above is OFF.</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h5><b>Daily Service Window</b></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zs_service_open_time">Service Starts At</label>
                                    <input type="time" class="form-control" id="zs_service_open_time" name="service_open_time">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zs_service_close_time">Service Ends At</label>
                                    <input type="time" class="form-control" id="zs_service_close_time" name="service_close_time">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block" style="margin-top:-8px;margin-bottom:10px;">Customers in this zone can order daily within this window. Outside it, the user app shows a "Sorry, we are closed for the day" popup with the slot. Leave both blank for no restriction. Overnight windows (e.g. 18:00 to 02:00) are supported.</small>
                    </div>
                </div>
                <div id="zs_result" class="alert alert-success" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="submit" id="btn_zs_save" class="btn btn-primary">Save Zone Settings</button>
            </div>
        </form>
    </div>
</div>
<script>
$('#edit-zone-settings-modal').appendTo('body');
var zsBannerSwitch = new Switchery(document.querySelector('#zs-banner-button'));
var zsAutoSwitch = new Switchery(document.querySelector('#zs-auto-weather-button'));
document.querySelector('#zs-banner-button').onchange = function(){ $('#zs_announcement_enabled').val(this.checked ? 1 : 0); };
document.querySelector('#zs-auto-weather-button').onchange = function(){ $('#zs_announcement_auto_weather').val(this.checked ? 1 : 0); };

function editZoneSettings(id){
    var row = null;
    $.each($('#zone-settings').bootstrapTable('getData'), function(i, r){ if(parseInt(r.id) === parseInt(id)){ row = r; } });
    if(!row){ return; }
    $('#zs_zone_id').val(row.id);
    $('#zs_platform_fee').val(row.zone_platform_fee);
    $('#zs_convenience_fee').val(row.zone_convenience_fee);
    $('#zs_nearby_radius_km').val(row.zone_nearby_radius_km);
    $('#zs_service_open_time').val(row.zone_service_open_time || '');
    $('#zs_service_close_time').val(row.zone_service_close_time || '');
    $('#zs_announcement_text').val(row.zone_announcement_text || '');
    zsBannerSwitch.setPosition(parseInt(row.zone_announcement_enabled) === 1);
    document.querySelector('#zs-banner-button').checked = (parseInt(row.zone_announcement_enabled) === 1);
    $('#zs_announcement_enabled').val(parseInt(row.zone_announcement_enabled) === 1 ? 1 : 0);
    zsAutoSwitch.setPosition(parseInt(row.zone_announcement_auto_weather) === 1);
    document.querySelector('#zs-auto-weather-button').checked = (parseInt(row.zone_announcement_auto_weather) === 1);
    $('#zs_announcement_auto_weather').val(parseInt(row.zone_announcement_auto_weather) === 1 ? 1 : 0);
    $('#zs_result').hide();
    $('#edit-zone-settings-modal').modal('show');
}

$('#edit_zone_settings_form').on('submit', function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        type:'POST',
        url:'public/db-operation.php',
        data:formData,
        beforeSend:function(){ $('#btn_zs_save').html('Please wait...'); },
        cache:false,
        contentType:false,
        processData:false,
        success:function(result){
            $('#zs_result').html(result).show().delay(5000).fadeOut();
            $('#btn_zs_save').html('Save Zone Settings');
            $('#zone-settings').bootstrapTable('refresh');
        }
    });
});
</script>
<?php include"footer.php";?>