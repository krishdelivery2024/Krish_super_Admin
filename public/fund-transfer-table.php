<?php 

include_once('includes/crud.php');

$db = new Database();
$db->connect();

$db->sql("SET NAMES 'utf8'");

include('includes/variables.php');

include_once('includes/custom-functions.php');

$fn = new custom_functions;

$config = $fn->get_configurations();

// ================= DELIVERY BOYS =================

$sql = "SELECT id,name FROM delivery_boys ORDER BY name ASC";

$db->sql($sql);

$delivery_boys = $db->getResult();

?>

<!-- Main row -->

<div class="row">

    <div class="col-xs-12">

        <div class="box">

            <div class="box-header">

                <h3 class="box-title">
                    Fund Transfers
                </h3>

                <br><br>

                <!-- FILTER -->

                <div class="row">

                    <div class="col-md-4">

                        <select id="delivery_boy_filter"
                                class="form-control">

                            <option value="">
                                All Delivery Boys
                            </option>

                            <?php foreach($delivery_boys as $boy){ ?>

                                <option value="<?php echo $boy['id']; ?>">

                                    <?php echo $boy['name']; ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                </div>

            </div>

            <div class="box-body table-responsive">

                <table class="table table-hover"

                    data-toggle="table"

                    id="fund-transfers"

                    data-url="api-firebase/get-bootstrap-table-data.php?table=fund-transfers"

                    data-page-list="[5, 10, 20, 50, 100, 200]"

                    data-show-refresh="true"

                    data-show-columns="true"

                    data-show-export="true"

                    data-export-data-type="all"

                    data-export-types='["excel","csv","pdf","txt"]'

                    data-side-pagination="server"

                    data-pagination="true"

                    data-search="true"

                    data-trim-on-search="false"

                    data-query-params="queryParams"

                    data-sort-name="id"

                    data-sort-order="desc">

                    <thead>

                    <tr>

                        <th data-field="id" data-sortable="true">
                            ID
                        </th>

                        <th data-field="delivery_boy_id" data-sortable="true">
                            Delivery Boy ID
                        </th>

                        <th data-field="name" data-sortable="true">
                            Name
                        </th>

                        <th data-field="mobile" data-sortable="true">
                            Mobile
                        </th>

                        <th data-field="address" data-sortable="true">
                            Address
                        </th>

                        <th data-field="opening_balance" data-sortable="true">
                            Opening Balance
                        </th>

                        <th data-field="closing_balance" data-sortable="true">
                            Closing Balance
                        </th>

                        <th data-field="message" data-sortable="true">
                            Message
                        </th>

                        <th data-field="status" data-sortable="true">
                            Status
                        </th>

                        <th data-field="date_created" data-sortable="true">
                            Date Created
                        </th>

                    </tr>

                    </thead>

                </table>

            </div>

        </div>

    </div>

    <div class="separator"></div>

</div>

<script>

function queryParams(params){

    params.delivery_boy_id =
        $('#delivery_boy_filter').val();

    return params;
}

// ================= FILTER CHANGE =================

$('#delivery_boy_filter').change(function(){

    $('#fund-transfers').bootstrapTable('refresh');

});

</script>