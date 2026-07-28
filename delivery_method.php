<?php $page="Delivery Method";
include"header.php";

require_once 'includes/crud.php';
$db_con=new Database();
$db_con->connect();
$db_con->sql("SET NAMES 'utf8'");
$sql="SELECT * FROM settings WHERE  variable='system_timezone'";
$db_con->sql($sql);
$res_time = $db_con->getResult();
if(!empty($res_time)){
    foreach ($res_time as $row){
        $data = json_decode($row['value'], true);
    }
}

if(!empty($data['store_state'])){
    $sql = "SELECT * FROM state WHERE id='".$data['store_state']."'";
    $db_con->sql($sql);
    $res1 = $db_con->getResult();  
    $state_name = $res1[0]['name'];
}

if(!empty($data['store_city'])){
    $sql = "SELECT * FROM city WHERE id='".$data['store_city']."'";
    $db_con->sql($sql);
    $res = $db_con->getResult(); 
    $city_name = $res[0]['name'];
}
if(!empty($data['store_zone'])){
    $sql = "SELECT * FROM zone WHERE id='".$data['store_zone']."'";
    $db_con->sql($sql);
    $res = $db_con->getResult(); 
    $zone_name = $res[0]['name'];
}

$sql = "SELECT * FROM delivery_method WHERE id=1";
$db_con->sql($sql);
$res = $db_con->getResult(); 
//echo '<pre>'; print_r($res);
// exit;

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
                    }else{
                        alert('Data updated successfully');
                    }
                    
                }
                
            });
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
