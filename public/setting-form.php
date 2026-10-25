
    <div class="row">
        <div class="col-md-8">
            <!-- general form elements -->
            <?php if($permissions['settings']['read']==1){
                    if($permissions['settings']['update']==0) { ?>
                    <div class="alert alert-danger">You have no permission to update settings</div>
                    <?php } ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Update System Settings</h3>
                </div>
                <!-- /.box-header -->
                <?php
                    $db->sql("SET NAMES 'utf8'");
                    $sql="SELECT * FROM settings WHERE  variable='system_timezone'";
                    $db->sql($sql);

                    $res_time = $db->getResult();
                    if(!empty($res_time)){
                            foreach ($res_time as $row){
                                $id = $row['id'];
                                // echo $id;
                                $data = json_decode($row['value'], true);
                            }
                            // print_r($data);
                        }
                    // $time_zone=json_decode($res_time[0]['value'], true);
                    // print_r($time_zone);
                    $sql = "select value from `settings` where variable='Logo' OR variable='logo'";
                    $db->sql($sql);
                    $res_logo = $db->getResult();
                    $sql = "select value from `settings` where variable='Store_seal' OR variable='store_seal'";
                    $db->sql($sql);
                    $res_store_seal = $db->getResult();
                    $sql="SELECT * FROM settings WHERE variable='currency'";
                    $db->sql($sql);
                    $res_currency = $db->getResult();
                ?>
                <!-- form start -->
                <form id="system_configurations_form"  method="post" enctype="multipart/form-data">
                    <input type="hidden" id="system_configurations" name="system_configurations" required="" value="1" aria-required="true">
                        <input type="hidden" id="system_timezone_gmt" name="system_timezone_gmt" value="<?php if(!empty($data['system_timezone_gmt'])){ echo $data['system_timezone_gmt']; } ?>" aria-required="true">
                        <input type="hidden" id="system_configurations_id" name="system_configurations_id" value="<?php if(!empty($id)){ echo $id; } ?>" aria-required="true">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="app_name">App Name:</label>
                            <input type="text" class="form-control" name="app_name" value="<?=(isset($data['app_name']))?$data['app_name']:'';?>" placeholder="Name of the App - used in whole system"/>
                        </div>
                        <div class="form-group">
                            <label for="">Whatsapp Number:</label>
                            <input type="text" class="form-control" name="whatsapp_number" value="<?=(isset($data['whatsapp_number']))?$data['whatsapp_number']:""?>" placeholder="Whatsapp mobile number"/>
                        </div>
                        <div class="form-group">
                            <label for="">Support Number:</label>
                            <input type="text" class="form-control" name="support_number" value="<?=(isset($data['support_number']))?$data['support_number']:""?>" placeholder="Customer support mobile number - used in whole system"/>
                        </div>
                        <div class="form-group">
                            <label for="">Support Email:</label>
                            <input type="text" class="form-control" name="support_email" value="<?=(isset($data['support_email']))?$data['support_email']:""?>" placeholder="Customer support email - used in whole system"/>
                        </div>
                        <div class="form-group">
                            <label for="app_name">Logo:</label>
                            <img src="<?=DOMAIN_URL.'dist/img/'.$res_logo[0]['value']?>" title='<?=$data['app_name']?> - Logo' alt='<?=(isset($data['app_name']))?$data['app_name']:"";?> - Logo' style="max-width:100%"/>
                            <input type='file' name='logo' id='logo' accept="image/*"/>
                        </div>
                        <?php 
		                $sql_query = "SELECT * FROM state ORDER BY id ASC";
            			$db->sql($sql_query);
            			// store result 
            			$res_state=$db->getResult();	
            			?>
                        <div class="form-group">
                            <label for="address">State : </label>
                            <select id="store_state" name="store_state" required class="form-control col-md-12">
                            <option value=""></option>
                                <?php 
							foreach($res_state as $row){ ?>
							<option value="<?php echo $row['id']; ?>" <?=(isset($data['store_state']) && $data['store_state'] == $row['id'])?'selected':'';?>><?php echo $row['name']; ?></option>
							<?php } ?>
                            </select>
                        </div>
                        <?php 
		                $sql_query = "SELECT * FROM city ORDER BY id ASC";
            			$db->sql($sql_query);
            			// store result 
            			$res_state=$db->getResult();	
            			?>
                        <div class="form-group">
                            <label for="address">City : </label>
                            <select id="store_city" name="store_city" required class="form-control col-md-12">
                            <option value=""></option>
                                <?php 
							foreach($res_state as $row){ ?>
							<option value="<?php echo $row['id']; ?>" <?=(isset($data['store_city']) && $data['store_city'] == $row['id'])?'selected':'';?>><?php echo $row['name']; ?></option>
							<?php } ?>
                            </select>
                        </div>
                        <?php 
		                $sql_query = "SELECT * FROM zone ORDER BY id ASC";
            			$db->sql($sql_query);
            			// store result 
            			$res_state=$db->getResult();	
            			?>
                        <div class="form-group">
                            <label for="address">Zone : </label>
                            <select id="store_zone" name="store_zone" required class="form-control col-md-12">
                            <option value=""></option>
                                <?php 
							foreach($res_state as $row){ ?>
							<option value="<?php echo $row['id']; ?>" <?=(isset($data['store_zone']) && $data['store_zone'] == $row['id'])?'selected':'';?>><?php echo $row['name']; ?></option>
							<?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="address">Store Map Api : </label>
                            <input type="text" class="form-control" name="store_map_api" value="<?=(isset($data['store_map_api']))?$data['store_map_api']:'';?>" placeholder=""/>
                        </div>
                        <div class="form-group">
                            <label for="address">Address : </label>
                            <input type="text" class="form-control" id="store_address" name="address" value="<?=(isset($data['address']))?$data['address']:'';?>" placeholder="Address "/>
                        </div>
                        
                        <div id="map" style="width:100%;height:250px;display:none;"></div>
                        <script src="https://maps.googleapis.com/maps/api/js?key=<?=(isset($data['store_map_api']))?$data['store_map_api']:'';?>&callback=initMap&v=weekly&libraries=places" defer></script>
                        <div class="form-group">
                            <label for="address">Store Lattitude : </label>
                            <input type="text" class="form-control" id="store_lattitude" name="store_lattitude" value="<?=(isset($data['store_lattitude']))?$data['store_lattitude']:'';?>" placeholder=""/>
                        </div>
                        <div class="form-group">
                            <label for="address">Store Longitude : </label>
                            <input type="text" class="form-control" id="store_longitude" name="store_longitude" value="<?=(isset($data['store_longitude']))?$data['store_longitude']:'';?>" placeholder=""/>
                        </div>
                        
                        <div class="form-group">
                            <label for="gst_no">GST Number :</label>
                            <input type="text" class="form-control" name="gst_no" value="<?=(isset($data['gst_no']))?$data['gst_no']:'';?>" placeholder="GST Number "/>
                        </div>
                        <div class="form-group">
                            <label for="fssai_no">FSSAI Number :</label>
                            <input type="text" class="form-control" name="fssai_no" value="<?=(isset($data['fssai_no']))?$data['fssai_no']:'';?>" placeholder="FSSAI Number "/>
                        </div>
                        
                        <div class="form-group">
                            <label for="store_seal">Stamp:</label>
                            <img src="<?=DOMAIN_URL.'dist/img/'.$res_store_seal[0]['value']?>" title='<?=$data['app_name']?> - Logo' alt='<?=(isset($data['app_name']))?$data['app_name']:"";?> - Logo' style="max-width:100%"/>
                            <input type='file' name='store_seal' id='store_seal' accept="image/*"/>
                        </div>
                        <h4>App Force Update & Version Settings</h4><hr>

                        <div class="row" style="background:#f9f9ff; padding:12px; margin-bottom:15px; border-radius:6px; border:1px solid #e2e2f0;">
                            <div class="col-md-12"><strong><i class="fa fa-user"></i> Customer App (User)</strong></div>
                            <div class="form-group col-md-4">
                                <label for="">Current Version Of App:</label>
                                <input type="text" class="form-control" name="current_version" value="<?=isset($data['current_version'])?$data['current_version']:''?>" placeholder='e.g. 1.0 or 6'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Minimum Version Required: </label>
                                <input type="text" class="form-control" name="minimum_version_required" value="<?=isset($data['minimum_version_required'])?$data['minimum_version_required']:''?>" placeholder='e.g. 1.0 or 6'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Force Update Status</label><br>
                                <input type="checkbox" id="version-system-button" class="js-switch" <?php if(!empty($data['is-version-system-on']) && $data['is-version-system-on'] == '1'){ echo 'checked'; }?>>
                                <input type="hidden" id="is-version-system-on" name="is-version-system-on" value="<?=(!empty($data['is-version-system-on']))?$data['is-version-system-on']:0;?>">
                            </div>
                        </div>

                        <div class="row" style="background:#f9f9ff; padding:12px; margin-bottom:15px; border-radius:6px; border:1px solid #e2e2f0;">
                            <div class="col-md-12"><strong><i class="fa fa-motorcycle"></i> Delivery Boy App (Partner)</strong></div>
                            <div class="form-group col-md-4">
                                <label for="">Current Version Of Delivery App:</label>
                                <input type="text" class="form-control" name="delivery_boy_current_version" value="<?=isset($data['delivery_boy_current_version'])?$data['delivery_boy_current_version']:''?>" placeholder='e.g. 1.0 or 4'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Minimum Version Required: </label>
                                <input type="text" class="form-control" name="delivery_boy_min_version" value="<?=isset($data['delivery_boy_min_version'])?$data['delivery_boy_min_version']:''?>" placeholder='e.g. 1.0 or 4'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Force Update Status</label><br>
                                <input type="checkbox" id="delivery-boy-version-system-button" class="js-switch" <?php if(!empty($data['is_delivery_boy_version_system_on']) && $data['is_delivery_boy_version_system_on'] == '1'){ echo 'checked'; }?>>
                                <input type="hidden" id="is_delivery_boy_version_system_on" name="is_delivery_boy_version_system_on" value="<?=(!empty($data['is_delivery_boy_version_system_on']))?$data['is_delivery_boy_version_system_on']:0;?>">
                            </div>
                        </div>

                        <div class="row" style="background:#f9f9ff; padding:12px; margin-bottom:15px; border-radius:6px; border:1px solid #e2e2f0;">
                            <div class="col-md-12"><strong><i class="fa fa-cutlery"></i> Seller App (Restaurant Partner)</strong></div>
                            <div class="form-group col-md-4">
                                <label for="">Current Version Of Seller App:</label>
                                <input type="text" class="form-control" name="seller_current_version" value="<?=isset($data['seller_current_version'])?$data['seller_current_version']:''?>" placeholder='e.g. 1.0 or 5'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Minimum Version Required: </label>
                                <input type="text" class="form-control" name="seller_min_version" value="<?=isset($data['seller_min_version'])?$data['seller_min_version']:''?>" placeholder='e.g. 1.0 or 5'/>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Force Update Status</label><br>
                                <input type="checkbox" id="seller-version-system-button" class="js-switch" <?php if(!empty($data['is_seller_version_system_on']) && $data['is_seller_version_system_on'] == '1'){ echo 'checked'; }?>>
                                <input type="hidden" id="is_seller_version_system_on" name="is_seller_version_system_on" value="<?=(!empty($data['is_seller_version_system_on']))?$data['is_seller_version_system_on']:0;?>">
                            </div>
                        </div><hr>
                        
                        <div class="form-group">
                            <label for="currency">Store Currency ( Symbol or Code - $ or USD - Anyone ):</label>
                            <input type="text" class="form-control" name="currency" value="<?=!empty($res_currency)?$res_currency[0]['value']:'';?>" placeholder="Either Symbol or Code - For Example $ or USD"/>
                        </div>
                        <div class="form-group">
                            <label for="tax">Tax ( in percentage % ):</label>
                            <input type="number" class="form-control" name="tax" value="<?=$data['tax']?>" placeholder="Enter only number" min="0"/>
                        </div>
                        <div class="form-group">
                            <label for="delivery_charge">Delivery Charge Amount (<?=$settings['currency']?>)</label>
                            <input type="number" class="form-control" name="delivery_charge" value="<?=$data['delivery_charge']?>" placeholder='Delivery Charge on Shopping' min='0'/>
                        </div>
                        <div class="form-group">
                            <label for="delivery_charge">Minimum Amount for Free Delivery (<?=$settings['currency']?>) <small>( Below this user will be charged based on Delivery Charge)</small></label>
                            <input type="number" class="form-control" name="min_amount" value="<?=$data['min_amount']?>" placeholder='Minimum Order Amount for Free Delivery' min='0'/>
                        </div>
                        <div class="form-group">
                            <label for="platform_fee">Platform Fee (<?=$settings['currency']?>)</label>
                            <input type="number" class="form-control" name="platform_fee" value="<?=$data['platform_fee']?>" placeholder='Platform Fee on Shopping' min='0'/>
                        </div>
                        <div class="form-group">
                            <label for="convenience_fee">Convenience Fee ( in percentage % )</label>
                            <input type="number" class="form-control" name="convenience_fee" value="<?=(isset($data['convenience_fee']))?$data['convenience_fee']:0;?>" placeholder='Convenience Fee in % of item total' min='0'/>
                        </div>
                        
                        <div class="form-group">
                            <label for="delivery_time_per_km">Delivery Time Per Km (Minutes)</label>
                            <input type="number" class="form-control" name="delivery_time_per_km" value="<?=$data['delivery_time_per_km']?>" placeholder='5' min='1'/>
                        </div>

                        <hr>
                        <div class="form-group">
                            <label class="system_timezone" for="system_timezone">System Timezone</label>
                            <select id="system_timezone" name="system_timezone" required class="form-control col-md-12">
                                <?php $options = getTimezoneOptions();
                                foreach($options as $option){?>     
                                <option value="<?=$option[2]?>" data-gmt="<?=$option['1'];?>" <?=(isset($data['system_timezone']) && $data['system_timezone'] == $option[2])?'selected':'';?>><?=$option[2]?> - GMT <?=$option[1]?> - <?=$option[0]?></option>  
                                <?php } ?>
                            </select>
                        </div>
                        <hr>
                        <?php
                           //  print_r($data);
                        ?>
                        <h4>Refer & Earn System</h4><hr>
                        <div class="form-group">
                            <label for="refer-earn-system">Refer & Earn System</label><br>
                            <input type="checkbox" id="refer-earn-system-button" class="js-switch" <?php if(!empty($data['is-refer-earn-on']) && $data['is-refer-earn-on'] == '1'){ echo 'checked'; }?>>
                            <input type="hidden" id="is-refer-earn-on" name="is-refer-earn-on" value="<?=(!empty($data['is-refer-earn-on']))?$data['is-refer-earn-on']:0;?>">
                        </div>
                        <div class="form-group">
                            <label for="">Minimum Refer & Earn Order Amount (<?=$settings['currency']?>)</label>
                            <input type="number" class="form-control" name="min-refer-earn-order-amount" value="<?=$data['min-refer-earn-order-amount']?>" placeholder='Minimum Order Amount' />
                        </div>
                        <div class="form-group">
                            <label for="">Refer & Earn Bonus (<?=$settings['currency']?> OR %)</label>
                            <input type="number" class="form-control" name="refer-earn-bonus" value="<?=$data['refer-earn-bonus']?>" placeholder='Bonus' />
                        </div>
                        <div class="form-group">
                            <label for="">Refer & Earn Method</label>
                            <select name="refer-earn-method" class="form-control">
                                <option value="">Select</option>
                                <option value="percentage" <?=(isset($data['refer-earn-method']) && $data['refer-earn-method']=='percentage')?"selected":""?> >Percentage</option>
                                <option value="rupees" <?=(isset($data['refer-earn-method']) && $data['refer-earn-method']=='rupees')?"selected":""?>>Rupees</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Maximum Refer & Earn Amount (<?=$settings['currency']?>)</label>
                            <input type="number" class="form-control" name="max-refer-earn-amount" value="<?=$data['max-refer-earn-amount']?>" placeholder='Maximum Refer & Earn Amount' />
                        </div>
                        <div class="form-group">
                            <label for="">Minimum Withdrawal Amount</label>
                            <input type="number" class="form-control" name="minimum-withdrawal-amount" value="<?=$data['minimum-withdrawal-amount']?>" placeholder='Minimum Withdrawal Amount' />
                        </div>
                        <div class="form-group">
                            <label for="">Max days to return item</label>
                            <input type="number" class="form-control" name="max-product-return-days" value="<?=(isset($data['max-product-return-days']))?$data['max-product-return-days']:'';?>" placeholder='Max days to return item' />
                        </div>
                        <div class="form-group">
                            <label for="delivery_boy_commission">Delivery Boy Commission (₹ per order)</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="delivery_boy_commission" value="<?=(isset($data['delivery_boy_commission']))?$data['delivery_boy_commission']:(isset($data['delivery-boy-bonus-percentage'])?$data['delivery-boy-bonus-percentage']:'5');?>" placeholder='e.g. 5' />
                            <input type="hidden" name="delivery-boy-bonus-percentage" value="<?=(isset($data['delivery_boy_commission']))?$data['delivery_boy_commission']:(isset($data['delivery-boy-bonus-percentage'])?$data['delivery-boy-bonus-percentage']:'5');?>" />
                        </div>
                        <div class="form-group">
                            <label for="seller_commission">Global Seller Commission (%)</label>
                            <input type="number" step="0.01" class="form-control" name="seller_commission" value="<?=(isset($data['seller_commission']))?$data['seller_commission']:'0';?>" placeholder='Global Seller Commission' />
                        </div>

                        <div class="form-group">
                            <label for="parcel_zone_drop_max_km">Max Parcel Drop Distance from Zone Boundary (km)</label>
                            <input type="number" class="form-control" name="parcel_zone_drop_max_km" value="<?=(isset($data['parcel_zone_drop_max_km']) && $data['parcel_zone_drop_max_km'] !== '') ? $data['parcel_zone_drop_max_km'] : 5; ?>" placeholder='5' min='0' step='0.5'/>
                            <small class="text-muted">Parcel drop/delivery location is allowed up to this many km outside the nearest zone boundary. Set to 0 to require drop inside a zone.</small>
                        </div>

                        <div class="form-group">
                            <label for="nearby_store_radius_km">Nearby Store Radius (km)</label>
                            <input type="number" class="form-control" name="nearby_store_radius_km" value="<?=(isset($data['nearby_store_radius_km']) && $data['nearby_store_radius_km'] !== '') ? $data['nearby_store_radius_km'] : 4; ?>" placeholder='4' min='0' step='0.5'/>
                            <small class="text-muted">Default distance for the home "Stores Near You" list. Each zone can override it in Zone Settings; leaving the zone field empty uses this global value (default 4 km).</small>
                        </div>
                        
                        <h4>App Download Links</h4>
                        <p class="text-muted">These links are shown on the public landing page. Fill only the stores where each app is available.</p>
                        <hr>
                        <div class="form-group col-md-6">
                            <label for="">Customer App - Play Store URL</label>
                            <input type="url" class="form-control" name="customer_play_store_url" value="<?=(isset($data['customer_play_store_url']))?$data['customer_play_store_url']:'';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Customer App - App Store URL (iOS)</label>
                            <input type="url" class="form-control" name="customer_app_store_url" value="<?=(isset($data['customer_app_store_url']))?$data['customer_app_store_url']:'';?>" placeholder="https://apps.apple.com/app/id..."/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Seller App - Play Store URL</label>
                            <input type="url" class="form-control" name="seller_play_store_url" value="<?=(isset($data['seller_play_store_url']))?$data['seller_play_store_url']:'';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Seller App - App Store URL (iOS)</label>
                            <input type="url" class="form-control" name="seller_app_store_url" value="<?=(isset($data['seller_app_store_url']))?$data['seller_app_store_url']:'';?>" placeholder="https://apps.apple.com/app/id..."/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Delivery Boy App - Play Store URL</label>
                            <input type="url" class="form-control" name="delivery_play_store_url" value="<?=(isset($data['delivery_play_store_url']))?$data['delivery_play_store_url']:'';?>" placeholder="https://play.google.com/store/apps/details?id=..."/>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">Delivery Boy App - App Store URL (iOS)</label>
                            <input type="url" class="form-control" name="delivery_app_store_url" value="<?=(isset($data['delivery_app_store_url']))?$data['delivery_app_store_url']:'';?>" placeholder="https://apps.apple.com/app/id/..."/>
                        </div>
                        <div class="clearfix"></div>

                        <h4>Mail Settings</h4><hr>
                        <div class="form-group ">
                            <label for="from_mail">From eMail ID: <small>( This email ID will be used in Mail System )</small></label>
                            <input type="email" class="form-control" name="from_mail" value="<?=$data['from_mail']?>" placeholder='From Email ID'/>
                        </div>
                        <div class="form-group">
                            <label for="reply_to">Reply To eMail Name: <small>( This email ID will be used in Mail System )</small></label>
                            <input type="email" class="form-control" name="reply_to" value="<?=$data['reply_to']?>" placeholder='From Email ID'/>
                        </div>
                        <div class="form-group">
                            <label for="mail_password">Username: <small></small></label>
                            <input type="text" class="form-control" name="smtp_username" value="<?=$data['smtp_username'] ?? '';?>" placeholder='info@spiderekart.in'/>
                        </div>
                        <div class="form-group">
                            <label for="mail_password">Mail Password: <small>( Mail Password for SMTP )</small></label>
                            <input type="password" class="form-control" name="mail_password" value="<?=$data['mail_password']?>" placeholder='*****'/>
                        </div>
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host: <small>( SMTP Server )</small></label>
                            <input type="text" class="form-control" name="smtp_host" value="<?=$data['smtp_host']?>" placeholder='smtp.example.com'/>
                        </div>
                        <div class="form-group">
                            <label for="">SMTP Secure</label>
                            <select name="smtp_secure" class="form-control">
                                <option value="tls" <?=(isset($data['smtp_secure']) && $data['smtp_secure']=='tls')?"selected":""?> >TLS</option>
                                <option value="ssl" <?=(isset($data['smtp_secure']) && $data['smtp_secure']=='ssl')?"selected":""?>>SSL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="smtp_port">SMTP PORT: <small>( PORT Number )</small></label>
                            <input type="text" class="form-control" name="smtp_port" value="<?=$data['smtp_port']?>" placeholder=''/>
                        </div>
						<div class="form-group" <?php if($_SESSION['role']!='super admin'){ echo "style='display:none'"; } ?> >
							<input type="checkbox" class="form-check-input" id="two_auth" name="two_auth" value="on" <?=(isset($data['two_auth']) && $data['two_auth']=='on')?"checked":""?>>
                            <label class="form-check-label" for="two_auth">Login With SMS OTP</label>
						</div>
                    </div>
                    <!-- /.box-body -->
                    <div id="result"></div>
                    <div class="box-footer">
                        <input type="submit" id="btn_update" class="btn-primary btn" value="Update" name="btn_update"/>
                        <!-- <input type="submit" class="btn-danger btn" value="Cancel" name="btn_cancel"/> -->
                    </div>
                </form>
                <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view settings</div>
                <?php } ?>
            </div>
            <!-- /.box -->
        </div>
        
        
    </div>
<div class="separator"> </div>
<?php
function getTimezoneOptions(){
	$list = DateTimeZone::listAbbreviations();
	$idents = DateTimeZone::listIdentifiers();
	
	$data = $offset = $added = array();
	foreach ($list as $abbr => $info) {
		foreach ($info as $zone) {
			if ( ! empty($zone['timezone_id'])
				AND
				! in_array($zone['timezone_id'], $added)
				AND 
				  in_array($zone['timezone_id'], $idents)) {
				$z = new DateTimeZone($zone['timezone_id']);
				$c = new DateTime(null, $z);
				$zone['time'] = $c->format('H:i a');
				$offset[] = $zone['offset'] = $z->getOffset($c);
				$data[] = $zone;
				$added[] = $zone['timezone_id'];
			}
		}
	}

	array_multisort($offset, SORT_ASC, $data);
	/*$options = array();
	foreach ($data as $key => $row) {
		$options[$row['timezone_id']] = $row['time'] . ' - '
			. formatOffset($row['offset']). ' ' . $row['timezone_id'];
	}*/
	$i = 0;$temp = array();
	foreach ($data as $key => $row) {
		$temp[0] = $row['time'];
		$temp[1] = formatOffset($row['offset']);
		$temp[2] = $row['timezone_id'];
		$options[$i++] = $temp;
	}
	
	// echo "<pre>";
	// print_r($options);
	return $options;
}
function formatOffset($offset) {
	$hours = $offset / 3600;
	$remainder = $offset % 3600;
	$sign = $hours > 0 ? '+' : '-';
	$hour = (int) abs($hours);
	$minutes = (int) abs($remainder / 60);

	if ($hour == 0 AND $minutes == 0) {
		$sign = ' ';
	}
	return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT).':'. str_pad($minutes,2, '0');
}
?>
