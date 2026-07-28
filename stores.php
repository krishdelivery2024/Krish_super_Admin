<?php
    // start session
    session_start();
    
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    
    // if session not set go to login page
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }
  $page="Store Details";
    ?>
<?php include"header.php";
  $sql_query = "SELECT id, name FROM area ORDER BY id ASC";
			
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res_area=$db->getResult();	
			?>
        <style type="text/css">
            .container{
            width: 950px;
            margin: 0 auto;
            padding: 0;
            }
            h1 .send_btn
            {
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -webkit-linear-gradient(0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -moz-linear-gradient(center top, #0096FF, #005DFF);
            background: linear-gradient(#0096FF, #005DFF);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
            border-radius: 3px;
            color: #fff;
            padding: 3px;
            }
            div.clear{
            clear: both;
            }
            ul.devices{
            margin: 0;
            padding: 0;
            }
            ul.devices li{
            float: left;
            list-style: none;
            border: 1px solid #dedede;
            padding: 10px;
            margin: 0 15px 25px 0;
            border-radius: 3px;
            -webkit-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            -moz-box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.35);
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #555;
            width:100%;
            height:150px;
            background-color:#ffffff;
            }
            ul.devices li label, ul.devices li span{
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            font-style: normal;
            font-variant: normal;
            font-weight: bold;
            color: #393939;
            display: block;
            float: left;
            }
            ul.devices li label{
            height: 25px;
            width: 50px;                
            }
            ul.devices li textarea{
            float: left;
            resize: none;
            }
            ul.devices li .send_btn{
            background: -webkit-gradient(linear, 0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -webkit-linear-gradient(0% 0%, 0% 100%, from(#0096FF), to(#005DFF));
            background: -moz-linear-gradient(center top, #0096FF, #005DFF);
            background: linear-gradient(#0096FF, #005DFF);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.3);
            border-radius: 7px;
            color: #fff;
            padding: 4px 24px;
            }
            a{text-decoration:none;color:rgb(245,134,52);}
        </style>
    </head>
    <body>
        <?php
            include_once('includes/functions.php');
            ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Store Details<small>&nbsp(Allowed Only 3 Stores)</small></h3>
                        </div>
                        <form id="notification_form" method="post" action="add_store.php" enctype="multipart/form-data">
                            <input type="hidden" name="sid" id="sid" />
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="sname">Store Name :</label>
                                    <input type="text" name="sname" id="sname" class="form-control" placeholder="Store Name" required/>
                                </div>
                               <div class="form-group">
                                    <label for="address">Address :</label>
                                    <textarea rows="2" name="address" id="address" cols="70" class="form-control" placeholder="Store Address" required></textarea>
                                </div>
                                 <div class="form-group">
						<label for="exampleInputEmail1">Area :</label><?php echo isset($error['area_ID']) ? $error['area_ID'] : '';?>
						<select name="area" id="area" class="form-control" required>
						<option value=''>Select Your Area</option>

						<?php 
							foreach($res_area as $row){ ?>
							<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
							<?php } ?>
						
						
						</select>
					</div>
                    <div class="form-group">
                                    <label for="pincode">Pincode: </label>
                                    <input type="text" name="pincode" id="pincode" class="form-control" placeholder="Pincode" onkeypress="return isNumberKey(event)" maxlength=7 required/>
                                </div>
                                <div class="form-group">
                                    <label for="cname">Contact Person Name :</label>
                                    <input type="text" name="cname" id="cname" class="form-control" placeholder="Contact Person Name" required/>
                                </div>
                                <div class="form-group">
                                    <label for="cmobile">Contact Mobile :</label>
                                    <input type="text" name="cmobile" id="cmobile" class="form-control" placeholder="Contact Mobile" onkeypress="return isNumberKey(event)" maxlength="10" maxlength="15" required/>
                                </div>
                                <div class="form-group">
                                    <label for="cemail">Contact Email :</label>
                                    <input type="email" name="cemail" id="cemail" class="form-control" placeholder="Contact Email" required/>
                                </div>
                                <div class="form-group">
                                    <label for="uname">User Name :</label>
                                    <input type="text" name="uname" id="uname" class="form-control" placeholder="User Name" maxlength=25 required/>
                                </div>
                                <div class="form-group">
                                    <label for="password">Password :</label>
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required/>
                                </div>
                                <div class="form-group" style="display:none">
                                    <label for="lat">Latitude</label>
                                    <input type="hidden" name="lat" id="lat" class="form-control" placeholder="Enter Latitude" />
                                </div>
                                <div class="form-group" style="display:none">
                                    <label for="long">Longitude</label>
                                    <input type="hidden" name="long" id="long" class="form-control" placeholder="Enter Longitude" />
                                </div>
                            </div>
                            <div class="box-footer">
                                <input type="submit" id="submit_btn" class="btn-primary btn" value="Submit"/>&nbsp;
                            </div>
                        </form>
                        
                        <div id="result"></div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Stores</h3>
                        </div>
                        <table id="stores_table" class="table table-hover" data-toggle="table" 
                            data-url="api-firebase/get-bootstrap-table-data.php?table=stores"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-show-refresh="true" data-show-columns="true"
                            data-side-pagination="server" data-pagination="true"
                            data-search="true" data-trim-on-search="false"
                            data-sort-name="id" data-sort-order="desc">
                            <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="name" data-sortable="true">Name</th>
                                <th data-field="area">Area</th>
                                <th data-field="pincode">Pincode</th>
                                <th data-field="mobile">Mobile</th>
                                <th data-field="email">Email</th>
                                <th data-field="uname">UserName</th>
                                <th data-field="operate">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
    <script>
     $('#notification_form').on('submit',function(e){
        e.preventDefault();
        var formData = new FormData(this);
            $.ajax({
            type:'POST',
            url: $(this).attr('action'),
            data:formData,
            dataType:'json',
            beforeSend:function(){$('#submit_btn').val('Please wait..').attr('disabled',true);},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                console.log(result);
               //location.reload();
               $('#sid').val('');
                $('#result').html(result.message);
                $('#result').show().delay(6000).fadeOut();
  
                $( '#notification_form' ).each(function(){
                this.reset();
                });
                $('#submit_btn').val('Send').attr('disabled',false);
                $('#stores_table').bootstrapTable('refresh');
            },
            error:function(result){
                console.log(result);
            }
            });
        
    }); 
    </script>
    <script>
    function edit_store(id){
         //   id = $(this).data("id");
            $.ajax({
                url : 'add_store.php',
                type: "post",
                data: 'id='+id+'&edit_store=1',
                success: function(result){
                    //console.log(result);
                    var obj = JSON.parse(result);
                    $('#sid').val(obj.id);
                    $('#sname').val(obj.sname);
                    $('#address').val(obj.address);
                    $('#area').val(obj.area);
                  //  $('#area option[value=obj.area]').attr("selected",true);
                    $('#pincode').val(obj.pincode);
                    $('#cname').val(obj.cname);
                    $('#cmobile').val(obj.cmobile);
                    $('#cemail').val(obj.cemail);
                    $('#uname').val(obj.username);
                    $('#password').val(obj.password);  
                }
            });
    }
    function isNumberKey(evt){
    var charCode = (evt.which) ? evt.which : evt.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
    return true;
}
    </script>

</body>
</html>
<?php include"footer.php"; ?>