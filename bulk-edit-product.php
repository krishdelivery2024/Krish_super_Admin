<?php ob_start(); ?>

<?php $page="Bulk Upload Products";
include"header.php";?>
<style>
.form-control{
	background-color: #f5f7fa !important;
}
.pt-3-half { padding-top: 1.4rem; width:10% }
/* select {
    -webkit-appearance: none;
    -moz-appearance: none;
    text-indent: 1px;
    text-overflow: '';
} */
</style>
<?php
 $Query="SELECT * FROM `category` WHERE `main_cat` = $main_cat_id";
$db->sql($Query);
$category_result=$db->getResult();
$Query="select name, id, category_id from subcategory";
$db->sql($Query);
$subcategory_result=$db->getResult();
$Query="select * from unit";
$db->sql($Query);
$unit_result=$db->getResult();
	$offset = 0; $limit = 5000;
		$sort = 'id'; $order = 'ASC';
		$where = "WHERE p.`seller_id` = $seller_id";
		
		if(isset($_GET['offset']))
			$offset = $_GET['offset'];
		if(isset($_GET['limit']))
			$limit = $_GET['limit'];
		
		if(isset($_GET['sort']))
		if($_GET['sort']=='id'){
		    $sort="id";
		}else{
			$sort = $_GET['sort'];
		}
		if(isset($_GET['order']))
			$order = $_GET['order'];
		
		if(isset($_GET['search']) AND $_GET['search']!=''){
			$search = $_GET['search'];
			$where .= " and (p.`id` like '%".$search."%' OR p.`name` like '%".$search."%' OR pv.`measurement` like '%".$search."%' OR u.`short_code` like '%".$search."%' )";
		}

		if(isset($_POST['category_id']) && $_POST['category_id'] !=''){
			$category_id = $_POST['category_id'];
			if(isset($_GET['search']) AND $_GET['search']!='')
				$where .=' and p.`category_id`='.$category_id;
			else
				$where .=' and p.`category_id`='.$category_id;
		}
		
		$join = "JOIN `product_variant` pv ON pv.product_id = p.id";
		
		$sql = "SELECT COUNT(p.id) as `total` FROM `products` p $join ".$where."" ;
// 		echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		foreach($res as $row)
			$total = $row['total'];
		
// 		$sql = "SELECT * FROM products ".$where." ORDER BY ".$sort." ".$order." LIMIT ".$offset.", ".$limit;
        $sql = "SELECT p.id AS id,pv.id AS variant_id, pv.type,p.name, p.category_id, p.subcategory_id, pv.price, pv.discounted_price,pv.vendor_price, pv.measurement, pv.serve_for, pv.stock,pv.barcode_data, pv.measurement_unit_id,pv.stock_unit_id 
            FROM `products` p
            $join 
            $where ORDER BY $sort $order LIMIT $offset, $limit";
        // echo $sql;
		$db->sql($sql);
		$res = $db->getResult();
		// print_r($res);
		$bulkData = array();
		$bulkData['total'] = $total;
		$rows = array();
		$tempRow = array();
		
		$currency = $fn->get_settings('currency',false);
		
		foreach($res as $row){
			
			$operate = '<a href="view-product-variants.php?id='.$row['id'].'"><i class="fa fa-folder-open"></i>View</a>';
			$operate .= ' <a href="edit-product.php?id='.$row['id'].'"><i class="fa fa-edit"></i>Edit</a>';
			$operate .= ' <a class="btn-xs btn-danger" href="delete-product.php?id='.$row['id'].'"><i class="fa fa-trash-o"></i>Delete</a>';
			
			$tempRow['id'] = $row['id'];
			if($row['barcode_data']!=''){
				//$tempRow['barcode_data'] = "<div style='text-align:center'><img alt='' src='barcode/barcode.php?codetype=Code39&size=40&text=".$row['barcode_data']."&print=true'/><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div>";
			 //   $tempRow['barcode_data'] = "<div style='text-align:center'><svg class='barcode'  jsbarcode-value='".$row['barcode_data']."'  jsbarcode-textmargin='0' jsbarcode-height='50'  jsbarcode-fontoptions='bold'></svg></div><div style='text-align:center'><a href='javascript:void(0)' onclick=print_barcode('".$row['barcode_data']."')>print</a></div><script>JsBarcode('.barcode').init();</script>";
			    
			}else{
				
			//	$tempRow['barcode_data'] = "No Barcode";
			}
			$tempRow['name'] = $row['name'];
			$tempRow['measurement'] = $row['measurement'];
			$tempRow['measurement_unit_id'] = $row['measurement_unit_id'];
			$tempRow['price'] = $currency." ".$row['price'];
			$tempRow['vendor_price'] = $currency." ".$row['vendor_price'];
			$tempRow['discounted_price'] = $currency." ".$row['discounted_price'];
			$tempRow['serve_for'] = $row['serve_for'];
			$tempRow['stock'] = $row['stock'];
			$tempRow['stock_unit_id'] = $row['stock_unit_id'];
			$rows[] = $tempRow;
		}
		$bulkData['rows'] = $rows;
		//print_r(json_encode($bulkData));

?>


<!-- Editable table -->
<div class="card">
  <h3 class="card-header text-center font-weight-bold text-uppercase py-4">
    Edit Products
  </h3>
  <h6 class="card-header text-center font-weight-bold text-uppercase py-4">(Products auto update after Edit)</h6>
  <div class="card-body">
  <div class="row" style="margin-bottom:20px">
	<div class="col-md-4">
		 <a href="products.php">Back to Products</a>
	</div>
	<div class="col-md-4">
	<form method="post">
		<select id="category_id" onchange="this.form.submit()" name="category_id" placeholder="Select Category" required class="form-control">
                                <?php
                                    $Query = "SELECT * FROM `category` WHERE `main_cat` = $main_cat_id";
                                    $db->sql($Query);
									// var_dump($Query);exit;
                                    $result=$db->getResult();
                                    if($result)
                                    {
                                    ?>
                                <option value="">All Products</option>
                                <?php foreach($result as $row){
                                    
                                ?>
                                <option value='<?=$row['id']?>' <?php if(isset($_POST['category_id']) && $_POST['category_id']==$row['id']){ echo "selected"; } ?> ><?=$row['name']?></option>
                                <?php }}   
                                ?>
                            </select>
							</form>
	</div>
	<div class="col-md-4">
		<input class="form-control input-search" id="txt_searchall" placeholder="Search Products">
	</div>
	</div>
    <div  id="table" class="table-editable">
	
      <table id="editableTable" class="table table-bordered">
        <thead>
          <tr>
            <th class="text-center">Product Name</th>
            <th class="text-center">Category</th>
            <!-- <th class="text-center">Sub Category</th> -->
           <!-- <th class="text-center">Product Type</th>-->
            <th class="text-center">Meas.</th>
            <th class="text-center">Measure. Unit</th>
			<th class="text-center">MRP</th>
			<th class="text-center">Vendor Price</th>
			<th class="text-center">Selling Price</th>
            <th class="text-center">Stock</th>
			<th class="text-center">Stock Unit</th>
			<th class="text-center">Avalablity</th>
          </tr>
        </thead>
        <tbody>
		<?php foreach($res as $row){?>
          <tr id="<?=$row['variant_id']?>">
            <td class="pt-3-half editable_name" contenteditable="true"><?=$row['name']?></td>
            <td class="pt-3-half" contenteditable="false">
			<select class="form-control select_cat">
			<?php foreach($category_result as $category){?>
			<option value="<?=$category['id']?>" <?=($row['category_id']==$category['id'])?'selected':'' ?>><?=$category['name']?></option>
			<?php } ?>
			</select>
			</td>
            <!-- <td class="pt-3-half" contenteditable="false"><select class="form-control select_subcat">
			<?php foreach($subcategory_result as $subcategory){ if($subcategory['category_id']==$row['category_id']){?>
			<option value="<?=$subcategory['id']?>" <?=($row['subcategory_id']==$subcategory['id'])?'selected':'' ?>><?=$subcategory['name']?></option>
			<?php } }?></select></td> -->
           <!-- <td class="pt-3-half" contenteditable="false">
			<select class="form-control select_type">
			<option value="packet" <?=($row['type']=='packet')?'selected':'' ?>>Packet</option>
			<option value="loose" <?=($row['type']=='loose')?'selected':'' ?>>Loose</option>
			</select>
			</td>-->
            <td class="pt-3-half editable_mes" contenteditable="true"><?=$row['measurement']?></td>
            <td class="pt-3-half" contenteditable="false">
			<select class="form-control select_m_unit">
				<?php foreach($unit_result as $unit){?>
				<option value="<?=$unit['id']?>" <?=($row['measurement_unit_id']==$unit['id'])?'selected':'' ?>><?=$unit['short_code']?></option>
				<?php } ?>
			</select>
			</td>
            <td class="pt-3-half editable_price" contenteditable="true"><?=$row['price']?></td>
            <td class="pt-3-half editable_v_price" contenteditable="true"><?=$row['vendor_price']?></td>
            <td class="pt-3-half editable_d_price" contenteditable="true"><?=$row['discounted_price']?></td>
            <td class="pt-3-half editable_stock" contenteditable="true"><?=$row['stock']?></td>
            <td class="pt-3-half" contenteditable="false">
			<select class="form-control select_s_unit">
				<?php foreach($unit_result as $unit){?>
				<option value="<?=$unit['id']?>" <?=($row['stock_unit_id']==$unit['id'])?'selected':'' ?>><?=$unit['short_code']?></option>
				<?php } ?>
			</select>
			</td>
            <td class="pt-3-half" contenteditable="false">
			<select class="form-control select_s_for">
			<option value="Available" <?=($row['serve_for']=='Available')?'selected':'' ?>>Available</option>
			<option value="Sold Out" <?=($row['serve_for']=='Sold Out')?'selected':'' ?>>Sold Out</option>
			</select>
			</td>
          </tr>
		<?php } ?>
          
        </tbody>
      </table>
    </div>
  </div>
</div>
<script>
var js_array =<?php echo json_encode($subcategory_result );?>;
$('.editable_name').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('name',id,value);
});
$('.editable_mes').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('mes',id,value);
});
$('.editable_price').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('price',id,value);
});
$('.editable_v_price').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('v_price',id,value);
});
$('.editable_d_price').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('d_price',id,value);
});
$('.editable_stock').on('focusout', function() {
    var value=$(this).html();
	id=$(this).closest("tr").attr("id");
	update_product('stock',id,value);
});

$('.select_cat').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	//console.log($(this).closest("tr").find("td").eq(2).find('.select_subcat').html());
	var html="<option value='0'>Choose Subcategory</option>";
	for(i=0;i<js_array.length;i++){
		if(js_array[i].category_id == value){
			html=html+"<option value='"+js_array[i].id+"'>"+js_array[i].name+"</option>";
		}
	}
	$(this).closest("tr").find("td").eq(2).find('.select_subcat').html(html);
	update_product('cat',id,value);
});
$('.select_subcat').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	update_product('subcat',id,value);
});
/* $('.select_type').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	update_product('type',id,value);
}); */
$('.select_m_unit').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	update_product('m_unit',id,value);
});
$('.select_s_unit').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	update_product('s_unit',id,value);
});
$('.select_s_for').on('change', function() {
    var value=$(this).val();
	id=$(this).closest("tr").attr("id");
	update_product('s_for',id,value);
});
function update_product(type,id,value){
	var dataString ='type='+type+'&id='+id+'&value='+value;
	//console.log(dataString);
	$.ajax({        
        url: "public/update-product.php",
        type: "POST",
        data: dataString,
        dataType: "json",
		async:false,
        success: function (data) {
			console.log(data);
        }
    });
}


$(document).ready(function(){

  // Search all columns
  $('#txt_searchall').keyup(function(){
    // Search Text
     var searchText = $(this).val().toLowerCase();
    $.each($("#table tbody tr"), function() {
        if($(this).text().toLowerCase().indexOf(searchText) === -1)
           $(this).hide();
        else
           $(this).show();                
    });
    });
});
if ( window.history.replaceState ) {
  window.history.replaceState( null, null, window.location.href );
}
</script>
<!-- Editable table -->
<?php include"footer.php";?>