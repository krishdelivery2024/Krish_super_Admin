<?php $page="Product Rating";
include"header.php";

require_once 'includes/crud.php';
include_once('includes/functions.php');
// $db_con=new Database();
// $db_con->connect();
// $sql = "SELECT * FROM delivery_method WHERE id=1";
// $db_con->sql($sql);
// $res = $db_con->getResult(); 
// print_r($res);
// exit;
?> 
<div class="row">
        
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Product Rating</h3>
                </div>
                   <!-- <input type="search" class="search" name="search"> -->
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="product_rating_list" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=product_rating"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        >
                        <!-- data-query-params="queryParams_1" -->
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <!--<th data-field="product_id">Product ID</th>-->
                            <th data-field="name">Product Name</th>
                            <!--<th data-field="user_id">User ID</th>-->
                            <th data-field="username">User Name</th>
                            <th data-field="order_id">Order Id</th>
                            <th data-field="rating">Rating</th>
                            <th data-field="rating_desc">Rating Description</th>
                            <th data-field="created_at">Created at</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>
    
    <script>
        // $("body").on('keyup', '.search-input', function(){
        //     // $(".search-input").keyup(function(){
        //     // alert
        //  $searchinput=$(".search-input").val();

        //  $('tbody tr').remove();
        // //  console.log($searchinput);
        // $.ajax({
        // url:'api-firebase/get-bootstrap-table-data.php',
        // type: 'POST',
        // dataType: 'json',
        // data: {'search': $searchinput,'table':"product_rating"},
        // success: function(result){
        //     // alert(result);
        //     console.log(result)
        //     console.log(result.total);
        //     // var tables=JSON.parse(result);
        //     // console.log(tables);
        //     for (var i = 0; i < result.total; i++) {
        //             var html = [];
        //             html.push('<tr><td>',result.rows[i]['id'])
        //             html.push('</td><td>',result.rows[i]['product_id'])
        //             html.push('</td><td>',result.rows[i]['user_id'])
        //             html.push('</td><td>',result.rows[i]['order_id'])
        //             html.push('</td><td>',result.rows[i]['rating'])
        //             html.push('</td><td>',result.rows[i]['rating_desc'])
        //             html.push('</td><td>',result.rows[i]['user_id'])
        //             html.push('</td></tr>')
        //             $("tBody").append(html.join(''));
        //     }
        // }});
        // });
        </script>
<?php include"footer.php";?>