<?php $page="New Offers Images";
include"header.php";?>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
            
            });
        </script>
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
        <?php
            include_once('includes/functions.php');
            

            
          
            ?>
            <div class="row">
                <div class="col-md-6">
                    <?php if($permissions['new_offers']['create']==0) { ?>
                        <div class="alert alert-danger">You have no permission to create new offers</div>
                    <?php } ?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Add New Offers Images here</h3>
                        </div>
                        <form id="offer_form" method="post" action="api-firebase/offer-images.php" enctype="multipart/form-data">
                            <div class="box-body">
                                <input type='hidden' name='accesskey' id='accesskey' value='90336'/>
                                <input type='hidden' name='add-image' id='add-image' value='1'/>
                                <input type='hidden' name='ajax-call' value='1'/>


            

                                <div class="form-group">
                                    <label for="image">Offer Image :</label>
                                    <input type='file' name="image" id="image" required/>
                                    <p class="help-block">Images above 700 KB are compressed automatically before upload, so large photos are fine.</p>
                                </div>
                            </div>
                            <div class="box-footer">
                                <input type="submit" id="submit_btn" class="btn-primary btn" value="Upload"/>
                            </div>
                        </form>
                        <div id="result"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <?php if($permissions['new_offers']['read']==1){?>
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">New Offer Images</h3>
                        </div>
                        <table id="offers_table" class="table table-hover" data-toggle="table" 
                            data-url="api-firebase/get-bootstrap-table-data.php?table=offers"
                            data-page-list="[5, 10, 20, 50, 100, 200]"
                            data-show-refresh="true" data-show-columns="true"
                            data-side-pagination="server" data-pagination="true"
                            data-search="true" data-trim-on-search="false"
                            data-sort-name="id" data-sort-order="desc">
                            <thead>
                            <tr>
                                <th data-field="id" data-sortable="true">ID</th>
                                <th data-field="image">Image</th>
                                <th data-field="date_created" data-visible="false">Date Created</th>
                                <th data-field="operate">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                    <?php } else { ?>
                <div class="alert alert-danger">You have no permission to view new offer images.</div>
            <?php } ?>
                </div>
            </div>
    <script>
// The upload has to be shrunk in the browser: nginx rejects an oversized request
// with 413 before the server side compression ever gets to run.
var OFFER_MAX_BYTES = 700 * 1024;
var OFFER_MAX_EDGE = 1600;

/**
 * Re-encodes an image file as JPEG until it fits inside maxBytes.
 *
 * Quality is stepped down first and the dimensions are only reduced once the
 * quality floor is reached, which keeps the picture sharp for as long as
 * possible. The callback always receives something usable - the original file
 * is handed back if the browser cannot decode it or encode it.
 */
function compressImageToTarget(file, maxBytes, done) {
    var reader = new FileReader();

    reader.onerror = function () {
        done(file);
    };

    reader.onload = function (e) {
        var img = new Image();

        img.onerror = function () {
            done(file);
        };

        img.onload = function () {
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            var scale = Math.min(1, OFFER_MAX_EDGE / Math.max(img.width, img.height));
            var width = Math.max(1, Math.round(img.width * scale));
            var height = Math.max(1, Math.round(img.height * scale));
            var quality = 0.92;
            var qualityFloor = 0.4;

            function attempt() {
                canvas.width = width;
                canvas.height = height;
                // Flatten transparency onto white, otherwise the png area would
                // turn black in the jpeg.
                ctx.fillStyle = '#FFFFFF';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function (blob) {
                    if (!blob) {
                        done(file);
                        return;
                    }

                    if (blob.size <= maxBytes) {
                        done(blob);
                        return;
                    }

                    if (quality > qualityFloor) {
                        quality -= 0.12;
                        attempt();
                        return;
                    }

                    // Quality exhausted, so shrink further and start over.
                    if (width > 400 && height > 400) {
                        width = Math.round(width * 0.8);
                        height = Math.round(height * 0.8);
                        quality = 0.92;
                        attempt();
                        return;
                    }

                    done(blob);
                }, 'image/jpeg', quality);
            }

            attempt();
        };

        img.src = e.target.result;
    };

    reader.readAsDataURL(file);
}
    </script>
    <script>


      $('#offer_form').on('submit',function(e){
        e.preventDefault();
        var form = this;
        var input = document.getElementById('image');
        var selected = input.files && input.files[0];

        function post(formData){
            $.ajax({
            type:'POST',
            url: $(form).attr('action'),
            data:formData,
            dataType:'json',
            beforeSend:function(){$('#submit_btn').val('Please wait..').attr('disabled',true);},
            cache:false,
            contentType: false,
            processData: false,
            success:function(result){
                $('#result').html(result.message);
                $('#result').show().delay(2000).fadeOut();
                $('#submit_btn').val('Upload').attr('disabled',false);
                // $('#offers_table').bootstrapTable('refresh');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            },
            error:function(){
                // nginx answers an oversized upload with a 413 html page, which
                // cannot be parsed as json, so report it instead of hanging on a
                // disabled button.
                $('#submit_btn').val('Upload').attr('disabled',false);
                $('#result').html("<p class='alert alert-danger'>Upload failed. Please try a smaller image.</p>");
                $('#result').show().delay(4000).fadeOut();
            }
            });
        }

        function upload(file){
            var formData = new FormData(form);
            if(file){
                // A File chosen in the input cannot be swapped for the compressed
                // blob, so the image part has to be set explicitly.
                formData.set('image', file, file.name || 'offer.jpg');
            }
            post(formData);
        }

        if(!selected){
            upload(null);
            return;
        }

        if(selected.size <= OFFER_MAX_BYTES){
            upload(selected);
            return;
        }

        $('#submit_btn').val('Compressing..').attr('disabled',true);
        compressImageToTarget(selected, OFFER_MAX_BYTES, function(compressed){
            upload(compressed);
        });
    }); 
    </script>
    <script>
    $(document).on('click','.delete-offer',function(){
        if(confirm('Are you sure?')){
            id = $(this).data("id");
            image = $(this).data("image");
            $.ajax({
                url : 'api-firebase/offer-images.php',
                type: "get",
                data: 'accesskey=90336&id='+id+'&image='+image+'&type=delete-offer',
                success: function(result){
                    if(result==1){
                        $('#offers_table').bootstrapTable('refresh');
                    }
                    if(result==2){
                        alert('You have no permission to delete new offers');
                    }if(result==0){
                        alert('Error! offer could not be deleted');

                    }
                        
                }
            });
        }
    });
    </script>
<?php include"footer.php"; ?>