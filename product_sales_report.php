<?php $page="Month wise Product Sales Report";
include"header.php";?>
<?php
    header("Expires: on, 01 Jan 1970 00:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    
    include_once('includes/crud.php');
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $permissions = $fn->get_permissions($_SESSION['id']);
    
    $db = new Database();
    $db->connect();
    
    if(isset($_POST['update_products_order']) && $_POST['update_products_order'] == 1){
        if($permissions['products_order']['update']==1){
        $id_ary = explode(",",$_POST["row_order"]);
        for($i=0;$i<count($id_ary);$i++){
            $sql = "UPDATE `products` SET row_order='" . $i . "' WHERE id=". $id_ary[$i];
            // echo $sql;
            $db->sql($sql);
            $res = $db->getResult();
        }
        echo "<p class='alert alert-success'>Product order updated!</p>";
        return false;
        }else{
        echo "<p class='alert alert-danger'>You have no permission to update products order</p>";
        return false;
        }
    }
?>


                <div class='row'>
                    
                    <div class='col-md-5'>

                        <label class="control-label">Product</label>
                        <select name="product_id" id="product_id" class=" select2 form-control">
                             <?php

                                    $sql = "SELECT * FROM `products` ORDER BY `id` ASC";
                                
                                echo $sql;
                            $db->sql($sql);
                            $res_inner = $db->getResult();
                            foreach($res_inner as $products){ ?>
                            <option value='<?=$products['id']?>'><?=$products['name']?></option>

                            <?php } ?>
                        </select>
                    </div>
                    <div class='col-md-1'>

                        <label class="control-label">Year</label>
                        <select name="year" id="year" class="form-control select2">
                             <?php

                            $sql = "SELECT DISTINCT YEAR(date_added) AS year FROM orders";
                            $db->sql($sql);
                            $res_inner = $db->getResult();
                            foreach($res_inner as $ress){ ?>
                            <option value='<?=$ress['year']?>'><?=$ress['year']?></option>

                            <?php }  ?>
                        </select>
                    </div>
                    <div class='col-md-1'>
                        <input style="margin-top:30px" type="submit" class="btn btn-primary" name="btn_search" id="btn_search" value="SHOW">
                    </div>
                    <br>
                    <br>
                    <br>
                    <br>
                    <div class='col-md-12 col-sm-12 col-xs-12 text-center'>
                        </select>
                    </div>
                    <br><br>
                </div> 
  <?php
   if((isset($_GET['product_id'])&&($_GET['product_id']!='')) && isset($_GET['year'])&&($_GET['year']!='')){  
        $month=array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
        $chart_data="'Month'/'Qty'";
        for($i=0;$i<count($month);$i++){
            $sql = "SELECT
                products.`id`,
            	products.`name`, 
            	product_variant.measurement, 
            	product_variant.measurement_unit_id, 
            	unit.`name` AS unit_name, 
            	SUM(order_items.quantity) AS qty, 
            	order_items.price, 
            	order_items.discounted_price,
            	DATE_FORMAT(order_items.date_added,'%Y') AS year1,
            	DATE_FORMAT(order_items.date_added,'%b') AS month1
            FROM
            	order_items
            	INNER JOIN
            	product_variant
            	ON 
            		order_items.product_variant_id = product_variant.id
            	INNER JOIN
            	products
            	ON 
            		product_variant.product_id = products.id
            	INNER JOIN
            	unit
            	ON 
            		product_variant.measurement_unit_id = unit.id
            		WHERE DATE_FORMAT(order_items.date_added,'%Y')='".$_GET['year']."' AND DATE_FORMAT(order_items.date_added,'%b')='".$month[$i]."' AND products.`id`=".$_GET['product_id']." AND (order_items.active_status!='cancelled' ) 
            		GROUP BY product_variant.product_id ,DATE_FORMAT(order_items.date_added,'%Y'),DATE_FORMAT(order_items.date_added,'%b')";
            	//	echo $sql;
                    $db->sql($sql);
                    $res = $db->getResult();
                    if(empty($res)){
                        $chart_data.=" | '".$month[$i]."'/0";
                    }else{
                        $chart_data.=" | '".$month[$i]."'/".$res[0]['qty'];
                    }
        }
        //print_r($chart_data);
        //$chart_data="'Year'/'Statistics' | '2010'/75 | '2011'/42 | '2012'/75 | '2013'/38 | '2014'/19 | '2015'/93";
       
       ?>
		<div class="row small-spacing">
			<div class="col-lg-12 col-md-12">
				<div class="box-content">
					<h4 class="box-title">Product Sales</h4>
					<!-- /.dropdown js__dropdown -->
					<div class="content">
						<div id="chart-1" class="js__chart" data-type="column" data-chart="<?=$chart_data?>"></div>
					</div>
					<!-- /.content -->
				</div>
				<!-- /.box-content -->
			</div>
			<!-- /.col-lg-6 col-xs-12 -->
       </div>
       <?php
    }
    

?>
<?php include"footer.php";?>
<!-- jQuery -->

<script>
    
$(document).ready(function() {
    $('.select2').select2();
});
    product_id = '<?=isset($_GET['product_id']) ? $db->escapeString($_GET['product_id']):'' ?>';
     $('#product_id').val(product_id);
    // if(category_id == '' && subcategory_id == ''){

         //redirect_to_url('products-order.php?category_id=''&subcategory_id=''');
    // }


    
    $('#btn_search').on('click',function(){


        redirect_to_url('product_sales_report.php?product_id='+$('#product_id').val()+'&year='+$('#year').val());
    });
    function redirect_to_url(url){
        window.location.href = url;


    }
</script>
