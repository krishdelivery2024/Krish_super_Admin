<?php $page="Delivery Method";
include"header.php";

require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
$db_con->sql("SET NAMES 'utf8'");
$sql="SELECT * FROM settings WHERE  variable='system_timezone'";
$db_con->sql($sql);
$res_time = $db_con->getResult();
$data = array();
if(!empty($res_time)){
    foreach ($res_time as $row){
        $data = json_decode($row['value'], true);
    }
}

// These ids come from the settings row and can point at a zone/state/city that no
// longer exists, so every lookup is treated as optional.
$state_name = $city_name = $zone_name = '';

if(!empty($data['store_state'])){
    $sql = "SELECT * FROM state WHERE id='".$data['store_state']."'";
    $db_con->sql($sql);
    $res1 = $db_con->getResult();  
    $state_name = !empty($res1[0]['name']) ? $res1[0]['name'] : '';
}

if(!empty($data['store_city'])){
    $sql = "SELECT * FROM city WHERE id='".$data['store_city']."'";
    $db_con->sql($sql);
    $res = $db_con->getResult(); 
    $city_name = !empty($res[0]['name']) ? $res[0]['name'] : '';
}
if(!empty($data['store_zone'])){
    $sql = "SELECT * FROM zone WHERE id='".$data['store_zone']."'";
    $db_con->sql($sql);
    $res = $db_con->getResult(); 
    $zone_name = !empty($res[0]['name']) ? $res[0]['name'] : '';
}

$sql = "SELECT * FROM delivery_method WHERE id=1";
$db_con->sql($sql);
$res = $db_con->getResult(); 

// Every active zone is listed here and can be configured. To take a zone out of
// service, untick "Service Enabled" in its row rather than hiding it: the row stays
// visible, the setting is auditable, and it can be switched back on at any time.

// Per-zone delivery configuration. A zone with no row here inherits the global
// settings above, which is also what an address that cannot be attributed to a
// zone falls back to.
$sql = "SELECT z.id AS zone_id, z.name AS zone_name, z.status AS zone_status,
               zdm.storepickup, zdm.in_person_delivery, zdm.courier,
               zdm.first_km, zdm.first_km_amount, zdm.rest_km_amount, zdm.is_active
        FROM zone z
        LEFT JOIN zone_delivery_method zdm ON zdm.zone_id = z.id
        WHERE z.status = 1
        ORDER BY z.name";
$db_con->sql($sql);
$zone_delivery_rows = $db_con->getResult();
if (!is_array($zone_delivery_rows)) {
    $zone_delivery_rows = array();
}

//echo '<pre>'; print_r($zone_delivery_rows);

$first_km=$first_km_amount=$rest_km_amount=null;
if(!empty($res[0]['in_persion_data'])){
    $persion_data = json_decode($res[0]['in_persion_data']);
    $first_km=$persion_data->first_km;
    $first_km_amount=$persion_data->first_km_amount;
    $rest_km_amount=$persion_data->rest_km_amount;
}
$zone1=$zone2=$zone3=$zone4=null;
if(!empty($res[0]['courier_data'])){
    $zine = json_decode($res[0]['courier_data']);
    $zone1=$zine->zone1;
    $zone2=$zine->zone2;
    $zone3=$zine->zone3;
    $zone4=$zine->zone4;
}
$client_id=$secret_key=null;
if(!empty($res[0]['dunzo_data'])){
    $dunz = json_decode($res[0]['dunzo_data']);
    $client_id=$dunz->dunzo_client_id;
    $secret_key=$dunz->dunzo_secret_key;
}
?>  
<style>
.js-switch{
margin-left: 20px;
cursor: pointer;
height: 30px;
width: 50px;
}
.displayinline{
    display: inline-flex;
}
</style>

<h4>Delivery METHOD</h4><hr>
<form id="delivery">
<div class="form-group displayinline">
    <?php if($res[0]['storepickup']==0){ ?>
    <input type="checkbox" id="storepickup" data-opt="opt0" name="storepickup" class="js-switch dopt">
    <?php }else{ ?>
    <input type="checkbox" id="storepickup" data-opt="opt0" name="storepickup" class="js-switch dopt" checked>
    <?php } ?>
    <label for="refer-earn-system" style="padding-top: 5px;">Store Pickup</label>
</div>
<!--<div class="form-group displayinline">-->
<!--    <?php if($res[0]['Delivery_by_courier']==0){ ?>-->
<!--    <input type="checkbox" id="Delivery_by_courier" data-opt="opt2" name="Delivery_by_courier" class="js-switch dopt">-->
<!--    <?php }else{ ?>-->
<!--    <input type="checkbox" id="Delivery_by_courier" data-opt="opt2" name="Delivery_by_courier" class="js-switch dopt" checked>-->
<!--    <?php } ?>-->
<!--    <label for="refer-earn-system" style="padding-top: 5px;">Delivery By Courier</label>-->
<!--</div>-->
<div class="form-group displayinline">
    <?php if($res[0]['in_persion_delivery']==0){ ?>
    <input type="checkbox" id="inpersion" data-opt="opt5" name="inpersion" class="js-switch dopt">
    <?php }else{ ?>
    <input type="checkbox" id="inpersion" data-opt="opt5" name="inpersion" class="js-switch dopt" checked>
    <?php } ?>
    <label for="refer-earn-system" style="padding-top: 5px;">In/Person Delivery</label>
</div>

<div class="opt opt2" style="display:none; margin-top:5px;">
    <h4>Delivery By Courier</h4>
    <div class="row" style="margin-bottom:2px;">
        <div class="col-md-3">
            <div class="form-group">
            <label for="email">Zone 1(<?=!empty($city_name)?$city_name:'';?>):</label>
            <input type="text" name="zone1" class="form-control" placeholder="" id="" value="<?=!empty($zone1)?$zone1:'';?>">
          </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
            <label for="email">Zone 2(<?=!empty($state_name)?$state_name:'';?>):</label>
            <input type="text" name="zone2" class="form-control" placeholder="" id="" value="<?=!empty($zone2)?$zone2:'';?>">
          </div>
        </div>
        </div>
        <div class="row" style="margin-bottom:2px;">
        <div class="col-md-3">
            <div class="form-group">
            <label for="email">Zone 3(<?=!empty($zone_name)?$zone_name:'';?>):</label>
            <input type="text" name="zone3" class="form-control" placeholder="" id="" value="<?=!empty($zone3)?$zone3:'';?>">
          </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
            <label for="email">Zone 4(Pan India):</label>
            <input type="text" name="zone4" class="form-control" placeholder="" id="" value="<?=!empty($zone4)?$zone4:'';?>">
          </div>
        </div>
        
    </div>
</div>
<div class="opt opt5" style="display:none; margin-top:5px;">
    <h4>In/Person Delivery</h4>
    <div class="form-group displayinline">
        <?php if($res[0]['Delivery_by_courier']==0 && $res[0]['dunzo']=='1'){ ?>
            <input type="radio" id="own_delivery"  data-ipd="inp1" value="own" name="inpersion_delivery" class="js-switch inp">
        <?php }else{ ?>
            <input type="radio" id="own_delivery"  data-ipd="inp1" value="own" name="inpersion_delivery" class="js-switch inp" checked>
        <?php } ?>
            <label for="refer-earn-system" style="padding-top: 5px;">Own Delivery Boy</label>
    </div>
    <!--<div class="form-group displayinline">-->
    <!--    <?php if($res[0]['dunzo']==0){ ?>-->
    <!--        <input type="radio" id="dunzo" name="inpersion_delivery" value="dunzo" data-ipd="inp2" class="js-switch inp">-->
    <!--    <?php }else{ ?>-->
    <!--        <input type="radio" id="dunzo" name="inpersion_delivery" value="dunzo" data-ipd="inp2" class="js-switch inp" checked>-->
    <!--    <?php } ?>-->
        
    <!--    <label for="refer-earn-system" style="padding-top: 5px;">Dunzo</label>-->
    <!--</div>-->
    <div class="ipd inp1" style="display:none; margin-top:5px;">
        <div class="row" style="margin-bottom:2px;">
            <div class="col-md-4">
                <div class="form-group">
                <label for="email">Delivery Charge for first <span><input type="text" value="<?=!empty($first_km)?$first_km:'';?>" name="first_km" class="form-control" style="width:45px;display:inline;height: 28px;"></span> Km (<?=$settings['currency']?>):</label>
                <input type="text" name="first_km_amount" class="form-control" placeholder="" id="" value="<?=isset($first_km_amount)?$first_km_amount:'';?>" style="width:70%;">
              </div>
            </div>
        </div>   
        <div class="row" style="margin-bottom:2px;">
            <div class="col-md-4">
                <div class="form-group">
                <label for="email">Delivery Charge for rest of the kilometers(<?=$settings['currency']?>):</label>
                <input type="email" name="rest_km_amount" class="form-control" placeholder="" id="" value="<?=isset($rest_km_amount)?$rest_km_amount:'';?>" style="width:70%;">
              </div>
            </div>
        </div>
        
    </div>
    <div class="ipd inp2" style="display:none; margin-top:5px;">
        <div class="row" style="margin-bottom:2px;">
            <div class="col-md-3">
                <div class="form-group"> 
                <label for="email">Client ID:</label>
                <input type="text" name="dunzo_client_id" class="form-control" placeholder="" id="" value="<?=!empty($client_id)?$client_id:'';?>">
              </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                <label for="email">Client Secretkey:</label>
                <input type="text" name="dunzo_secret_key" class="form-control" placeholder="" id="" value="<?=!empty($secret_key)?$secret_key:'';?>">
              </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <input type="button" class="btn btn-primary sbtn" value="Update" style="display:none;">
    </div>
</div>

<hr>
<h4>ZONE WISE DELIVERY</h4>
<p class="text-muted">
    One row per zone. Leave a charge blank to inherit the default set above; untick a
    method to stop that delivery type in that zone only. Orders are matched to a zone
    by the delivery pin, so a zone with no method ticked will not accept orders.
</p>

<?php if (empty($zone_delivery_rows)) { ?>
    <div class="alert alert-warning">No active zones found. Add a zone first.</div>
<?php } else { ?>
<div class="table-responsive">
<table class="table table-bordered table-condensed" id="zoneTable" style="margin-bottom:5px;">
    <thead>
        <tr style="background:#f5f5f5;">
            <th style="min-width:120px;">Zone</th>
            <th style="text-align:center;">Store<br>Pickup</th>
            <th style="text-align:center;">In/Person<br>Delivery</th>
            <th style="text-align:center;">Service<br>Enabled</th>
            <th style="text-align:center;" title="Blank inherits the default">First<br>KM</th>
            <th style="text-align:center;" title="Blank inherits the default">First KM<br>Charge</th>
            <th style="text-align:center;" title="Blank inherits the default">Per KM<br>Charge</th>
        </tr>
    </thead>
    <tbody>
<?php
$def_fkm  = !empty($first_km)     ? $first_km     : '';
$def_famt = isset($first_km_amount) ? $first_km_amount : '';
$def_ramt = isset($rest_km_amount)  ? $rest_km_amount  : '';

foreach ($zone_delivery_rows as $zrow) {
    $zid   = (int)$zrow['zone_id'];
    $zname = htmlspecialchars($zrow['zone_name'], ENT_QUOTES, 'UTF-8');
    // No row for the zone means it inherits the global switches.
    $z_sp  = $zrow['storepickup'] !== null
        ? (int)$zrow['storepickup'] : (int)$res[0]['storepickup'];
    $z_ipd = $zrow['in_person_delivery'] !== null
        ? (int)$zrow['in_person_delivery'] : (int)$res[0]['in_persion_delivery'];
    $z_on  = $zrow['is_active'] !== null ? (int)$zrow['is_active'] : 1;

    $z_fkm  = ($zrow['first_km']         === null || $zrow['first_km']         === '') ? '' : $zrow['first_km'];
    $z_famt = ($zrow['first_km_amount'] === null || $zrow['first_km_amount'] === '') ? '' : $zrow['first_km_amount'];
    $z_ramt = ($zrow['rest_km_amount']   === null || $zrow['rest_km_amount']   === '') ? '' : $zrow['rest_km_amount'];
?>
        <tr>
            <td>
                <input type="hidden" name="zone_id[]" value="<?= $zid; ?>">
                <?= $zname; ?>
            </td>
            <td style="text-align:center;">
                <input type="checkbox" class="zm-service" name="z_storepickup[<?= $zid; ?>]" value="1" <?= $z_sp == 1 ? 'checked' : ''; ?>>
            </td>
            <td style="text-align:center;">
                <input type="checkbox" class="zm-service" name="z_inperson[<?= $zid; ?>]" value="1" <?= $z_ipd == 1 ? 'checked' : ''; ?>>
            </td>
            <td style="text-align:center;">
                <input type="checkbox" class="zm-active" name="z_active[<?= $zid; ?>]" value="1" <?= $z_on == 1 ? 'checked' : ''; ?>>
            </td>
            <td style="text-align:center;">
                <input type="text" class="form-control input-sm" style="width:70px;display:inline-block;" name="z_first_km[<?= $zid; ?>]" value="<?= htmlspecialchars($z_fkm, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= htmlspecialchars($def_fkm, ENT_QUOTES, 'UTF-8'); ?>">
            </td>
            <td style="text-align:center;">
                <input type="text" class="form-control input-sm" style="width:80px;display:inline-block;" name="z_first_km_amount[<?= $zid; ?>]" value="<?= htmlspecialchars($z_famt, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= htmlspecialchars($def_famt, ENT_QUOTES, 'UTF-8'); ?>">
            </td>
            <td style="text-align:center;">
                <input type="text" class="form-control input-sm" style="width:80px;display:inline-block;" name="z_rest_km_amount[<?= $zid; ?>]" value="<?= htmlspecialchars($z_ramt, ENT_QUOTES, 'UTF-8'); ?>" placeholder="<?= htmlspecialchars($def_ramt, ENT_QUOTES, 'UTF-8'); ?>">
            </td>
        </tr>
<?php } ?>
    </tbody>
</table>
</div>
<p class="text-muted" style="font-size:11px;">
    Grey figures are placeholders, not saved values - a blank box inherits the default.
</p>
<?php } ?>

<div class="row">
    <div class="col-md-3">
        <input type="button" class="btn btn-primary sbtn" value="Update">
    </div>
</div>

</form>


<script>

    $(document).ready(function(){

        // ✅ Show checked options on page load
        $('.dopt:checked').each(function(){
            var data = $(this).data('opt');
            $('.' + data).show();
        });

        $('.inp:checked').each(function(){
            var data = $(this).data('ipd');
            $('.' + data).show();
        });

        $("body").on('click', '.dopt', function(){
            var data = $(this).data('opt');
            $('.opt').hide();
            $('.'+data).show();
            $('.sbtn').show();

            if (!$(this).is(":checked")) {
                $('.'+data).hide();
            }
        });

        $("body").on('click', '.inp', function(){
            var data = $(this).data('ipd');
            $('.ipd').hide();
            $('.'+data).show();
            $('.sbtn').show();
        });


        $("body").on('click', '.sbtn', function(){
            $.ajax({
                url:'getdeliverymethod.php',
                type: 'POST',
                dataType: 'json',
                data: $("form").serialize(),
                success: function(result){
                    if(result.message == 'Weight required'){
                        alert('Error!, Weight required for all products');
                    }else if(result.error){
                        alert('Error!, ' + (result.message ? result.message : 'could not save'));
                    }else{
                        alert('Data updated successfully');
                    }
                },
                error: function(){
                    alert('Error!, the server did not respond');
                }
            });
        });

        // Any zone change means there is something to save.
        $("body").on('change', '#zoneTable input', function(){
            $('.sbtn').show();
        });

    });

// // $(".js-switch").click(function(){
// $("body").on('click', '.dopt', function(){
// var white = $(this);
// var data = $(this).data('opt');
// $('.opt').hide();
// $('.'+data).show();
// $('.sbtn').show();
// var Delivery_by=$(this).attr('id');
// if(Delivery_by == 'Delivery_by_courier'){
//     if (!$(this).is(":checked")) {
//         $('.'+data).hide();
//         $('.sbtn').show();
//     }
// }

// if(Delivery_by == 'inpersion'){
//     if (!$(this).is(":checked")) {
//         $('.'+data).hide();
//         $('.sbtn').show();
//     }
// }
// });

// $("body").on('click', '.inp', function(){
//     var data = $(this).data('ipd');
//     $('.ipd').hide();
//     $('.'+data).show();
//     $('.sbtn').show();
// });

// $("body").on('click', '.sbtn', function(){
//     $.ajax({
//         url:'getdeliverymethod.php',
//         type: 'POST',
//         dataType: 'json',
//         data: $("form").serialize(),
//         success: function(result){
//             if(result.message == 'Weight required'){
//                 alert('Error!, Weight required for all products');
//             }else{
//                 alert('Data updated successfully');
//             }
            
//         }
        
//     });
// });

</script>

<?php include"footer.php";?>
