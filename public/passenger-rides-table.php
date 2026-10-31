<?php
    include_once('includes/functions.php');
    $fn_rides = new custom_functions;
    $my_zone_scope = $fn_rides->get_zone_scope(isset($_SESSION['id']) ? $_SESSION['id'] : 0);

    // Fetch zones
    $db->sql("SELECT id, name FROM zone ORDER BY name ASC");
    $res_zone = $db->getResult();
    if(empty($res_zone)) $res_zone = array();
    if(!empty($my_zone_scope)){
        $res_zone = array_values(array_filter($res_zone, function($z) use ($my_zone_scope){ return in_array((int)$z['id'], $my_zone_scope); }));
    }

    // Fetch active delivery boys
    $db->sql("SELECT id, name, mobile FROM delivery_boys WHERE status = 1 ORDER BY name ASC");
    $res_delivery_boys = $db->getResult();
    if(empty($res_delivery_boys)) $res_delivery_boys = array();

    // Fetch passenger vehicle types
    $db->sql("SELECT vehicle_type, display_name FROM service_pricing WHERE service_type = 'passenger' ORDER BY id ASC");
    $res_vehicles = $db->getResult();
    if(empty($res_vehicles)){
        $res_vehicles = array(
            array('vehicle_type' => 'bike', 'display_name' => 'Passenger Bike Taxi'),
            array('vehicle_type' => 'auto', 'display_name' => 'Passenger Auto Rickshaw'),
            array('vehicle_type' => 'cab', 'display_name' => 'Passenger Cab / Car'),
        );
    }
?>
    <!-- Main row -->
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">🚗 Passenger Rides</h3>
                    <h4 class="box-subtitle">Live passenger ride bookings, dispatched drivers, fare distribution, and status.</h4>
                </div>

                <div class="box-body" style="padding-bottom: 0;">
                    <form id="ride_filter_form" method="GET" style="margin-bottom: 15px;">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="ride_date" class="control-label"><i class="fa fa-calendar"></i> From &amp; To Date:</label>
                                <input type="text" id="ride_date" name="ride_date" class="form-control" placeholder="Select date range" autocomplete="off" />
                                <input type="hidden" id="ride_start_date" name="ride_start_date" />
                                <input type="hidden" id="ride_end_date" name="ride_end_date" />
                            </div>

                            <div class="col-md-2 form-group">
                                <label for="ride_vehicle" class="control-label"><i class="fa fa-car"></i> Vehicle Type:</label>
                                <select id="ride_vehicle" name="ride_vehicle" class="form-control">
                                    <option value="">All Vehicles</option>
                                    <?php foreach($res_vehicles as $v){ 
                                        $v_label = !empty($v['display_name']) ? ucfirst($v['vehicle_type']) . ' (' . trim(str_ireplace('Passenger', '', $v['display_name'])) . ')' : ucfirst($v['vehicle_type']);
                                    ?>
                                        <option value="<?=htmlspecialchars($v['vehicle_type'])?>"><?=htmlspecialchars($v_label)?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-2 form-group">
                                <label for="ride_status" class="control-label"><i class="fa fa-flag"></i> Status:</label>
                                <select id="ride_status" name="ride_status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="searching">Searching Driver</option>
                                    <option value="accepted">Driver On Way</option>
                                    <option value="arrived">Driver Arrived</option>
                                    <option value="started">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            <div class="col-md-2 form-group">
                                <label for="ride_zone" class="control-label"><i class="fa fa-map-marker"></i> Zone:</label>
                                <select id="ride_zone" name="ride_zone" class="form-control">
                                    <option value="">All Zones</option>
                                    <?php foreach($res_zone as $rz){ ?>
                                        <option value="<?=$rz['id']?>"><?=htmlspecialchars($rz['name'])?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="col-md-3 form-group">
                                <label for="ride_delivery_boy" class="control-label"><i class="fa fa-motorcycle"></i> Delivery Boy:</label>
                                <select id="ride_delivery_boy" name="ride_delivery_boy" class="form-control">
                                    <option value="">All Delivery Boys</option>
                                    <?php foreach($res_delivery_boys as $dboy){ ?>
                                        <option value="<?=$dboy['id']?>"><?=htmlspecialchars($dboy['name'])?> (<?=$dboy['mobile']?>)</option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label for="ride_payment_method" class="control-label"><i class="fa fa-credit-card"></i> Payment:</label>
                                <select id="ride_payment_method" name="ride_payment_method" class="form-control">
                                    <option value="">All Methods</option>
                                    <option value="cash">Cash / COD</option>
                                    <option value="online">Online / Paid</option>
                                </select>
                            </div>

                            <div class="col-md-9 text-right" style="margin-top: 25px;">
                                <button type="button" id="btn_clear_ride_filters" class="btn btn-default btn-sm"><i class="fa fa-refresh"></i> Reset Filters</button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="box-body table-responsive">
                    <table class="table table-hover table-striped" data-toggle="table" id="passenger_rides_list"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=passenger_rides"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-show-export="true"
                        data-export-types='["excel","pdf","csv"]'
                        data-export-data-type="all"
                        data-show-footer="true"
                        data-export-footer="true"
                        data-export-options='{"fileName": "passenger-rides-<?=date('d-m-Y')?>", "ignoreColumn": ["operate"]}'
                        data-query-params="passenger_ride_query_params">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true" data-footer-formatter="rideTotalLabel">Ride ID</th>
                            <th data-field="created_at" data-sortable="true">Date &amp; Time</th>
                            <th data-field="zone_name" data-sortable="true">Zone</th>
                            <th data-field="vehicle">Vehicle</th>
                            <th data-field="passenger_name">Passenger</th>
                            <th data-field="pickup_location" data-sortable="true">Pickup Location</th>
                            <th data-field="drop_location" data-sortable="true">Drop Location</th>
                            <th data-field="distance_km" data-sortable="true">Distance</th>
                            <th data-field="trip_fare" data-sortable="true" data-align="right" data-footer-formatter="rideMoneyFormatter">Trip Fare</th>
                            <th data-field="platform_fee" data-sortable="true" data-align="right" data-footer-formatter="rideMoneyFormatter">Platform Fee</th>
                            <th data-field="convenience_fee" data-sortable="true" data-align="right" data-footer-formatter="rideMoneyFormatter">Convenience Fee</th>
                            <th data-field="grand_total" data-sortable="true" data-align="right" data-footer-formatter="rideMoneyFormatter">Grand Total</th>
                            <th data-field="driver_commission" data-sortable="true" data-align="right" data-footer-formatter="rideMoneyFormatter">Driver Comm.</th>
                            <th data-field="driver_name">Assigned Driver</th>
                            <th data-field="otp">Start OTP</th>
                            <th data-field="status">Trip Status</th>
                            <th data-field="payment">Payment</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>

<script>
function rideNum(v) {
    if (!v) return 0;
    var s = String(v).replace(/\u20B9|&#8377;/g, '');
    var n = parseFloat(s.replace(/[^0-9.\-]/g, ''));
    return isNaN(n) ? 0 : n;
}

function rideTotalLabel() {
    return '<span style="color:green;font-weight:bold;font-size:large;">TOTAL</span>';
}

function rideMoneyFormatter(data) {
    var field = this.field;
    var sum = 0;
    if (data && data.length) {
        for (var i = 0; i < data.length; i++) {
            var row = data[i];
            if (row && row[field] !== undefined) {
                sum += rideNum(row[field]);
            }
        }
    }
    return '<span style="color:green;font-weight:bold;font-size:large;">\u20B9 ' + sum.toFixed(2) + '</span>';
}

function passenger_ride_query_params(p) {
    return {
        "start_date": $('#ride_start_date').val(),
        "end_date": $('#ride_end_date').val(),
        "vehicle_type": $('#ride_vehicle').val(),
        "status": $('#ride_status').val(),
        "filter_zone": $('#ride_zone').val(),
        "delivery_boy_id": $('#ride_delivery_boy').val(),
        "payment_method": $('#ride_payment_method').val(),
        limit: p.limit,
        sort: p.sort,
        order: p.order,
        offset: p.offset,
        search: p.search
    };
}

$(document).ready(function() {
    var exportConfig = {
        fileName: 'passenger-rides-' + (typeof moment !== 'undefined' ? moment().format('DD-MM-YYYY') : '<?=date("d-m-Y")?>'),
        ignoreColumn: ['operate'],
        onCellHtmlData: function(cell, row, col, data) {
            if (cell.is('th')) {
                return cell.find('.th-inner').text().trim();
            }
            if (cell.is('td')) {
                var html = data || '';
                var clean = html.replace(/<br\s*[\/]?>/gi, ' ');
                var txt = $('<div>').html(clean).text().trim();
                return txt
                    .replace(/\u20B9|&#8377;/g, 'Rs. ')
                    .replace(/\u2014|\u2013/g, '-')
                    .replace(/&mdash;/g, '-')
                    .replace(/&nbsp;|\u00a0/g, ' ')
                    .replace(/\s+/g, ' ');
            }
            return data;
        },
        jspdf: {
            orientation: 'landscape',
            format: 'a4',
            autotable: {
                theme: 'striped',
                styles: {
                    fontSize: 6,
                    cellPadding: 1.5,
                    overflow: 'linebreak'
                },
                headStyles: {
                    fillColor: [52, 73, 94],
                    textColor: 255,
                    fontSize: 6.5,
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [248, 249, 250]
                }
            }
        }
    };

    if ($.fn.bootstrapTable && $.fn.bootstrapTable.defaults) {
        $.fn.bootstrapTable.defaults.exportOptions = $.extend(true, {}, $.fn.bootstrapTable.defaults.exportOptions || {}, exportConfig);
    }
    if (typeof $('#passenger_rides_list').bootstrapTable === 'function') {
        $('#passenger_rides_list').bootstrapTable('refreshOptions', {
            exportOptions: exportConfig
        });
    }

    if ($.fn.daterangepicker) {
        $('#ride_date').daterangepicker({
            autoUpdateInput: false,
            showDropdowns: true,
            alwaysShowCalendars: true,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                cancelLabel: 'Clear'
            }
        });

        $('#ride_date').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
            $('#ride_start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#ride_end_date').val(picker.endDate.format('YYYY-MM-DD'));
            $('#passenger_rides_list').bootstrapTable('refresh');
        });

        $('#ride_date').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            $('#ride_start_date').val('');
            $('#ride_end_date').val('');
            $('#passenger_rides_list').bootstrapTable('refresh');
        });
    }

    $('#ride_vehicle, #ride_status, #ride_zone, #ride_delivery_boy, #ride_payment_method').on('change', function() {
        $('#passenger_rides_list').bootstrapTable('refresh');
    });

    $('#btn_clear_ride_filters').on('click', function() {
        $('#ride_filter_form')[0].reset();
        $('#ride_date').val('');
        $('#ride_start_date').val('');
        $('#ride_end_date').val('');
        $('#ride_vehicle').val('');
        $('#ride_status').val('');
        $('#ride_zone').val('');
        $('#ride_delivery_boy').val('');
        $('#ride_payment_method').val('');
        $('#passenger_rides_list').bootstrapTable('refresh');
    });
});
</script>
