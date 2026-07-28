<?php 
$page="Highest Selling Products Report";
include"header.php";?>
     <?php
    if($permissions['reports']['read']==1) { 
?>

    <!-- Main content -->
    <div class="row small-spacing">
        
        <div class="col-xs-12">
            
			<div class="box-content">
			    
                <h3 class="box-title">Products Report</h3>
                    
        
                <form method="POST" id="filter_form" name="filter_form">
                    
				    <div class="row">
				        
					     <div class="col-md-6 form-group form-inline">
					         
						     <label for="date" class="control-label">From & To Date</label>
						     
						     <input type="text" style="width:80%" class="form-control" id="sales_date" name="date" autocomplete="off" />
				    	</div>
				    	
				    	<!--<div class="col-md-3 form-group form-inline">
						     <select class="form-control" name="filter_by" onchange="submitForm()">
							        <option value="">SELECT</option>
							       <option value="day"   <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'day'){echo 'selected' ; } ?>>Today</option>
							       <option value="week"  <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'week'){echo 'selected' ; } ?>>This Week</option>
							       <option value="month" <?php if(isset($_POST['end_date']) && $_POST['end_date'] == 'day'){echo 'month' ; } ?>>This Month</option>
							    </select>
				    	</div>-->
				    	
					    <input type="hidden" id="start_from" name="start_date">
					    
				        <input type="hidden" id="end_to" name="end_date">
				       	     
					   <div class="col-md-3 pull-right form-group">
					       <input type="text" class="form-control" id="sales_keyword" name="search" placeholde="Enter Keyword" autocomplete="off" />
					    </div>
					   
				      </div>
				    
                </form>

                <!-- /.box-header -->
                <div class="box-body table-responsive">
                    
                    
                    <table id='sales_table' class="table table-hover" data-toggle="table" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=products_report"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="false"
                        data-search="false" data-trim-on-search="false"
                        data-filter-control="true" 
                        data-query-params="queryParams_sales"
                        data-sort-name="id"
                        data-sort-order="desc"
                        data-show-export="true"
                        data-export-types='["excel"]'
                        >
                        <thead>
                        <tr>
                            <th data-field="name" data-sortable="true">Product Name</th>
                            <th data-field="unit" data-sortable="true">Unit</th>
                            <th data-field="qty" data-sortable="true">quantity</th>
                            <th data-field="orders_count" data-sortable="true">Orders Count</th> 
                            
                        </tr>
                        </thead>
                    </table>
                </div>
                <!-- /.box-body -->
                
            </div>
            <!-- /.box -->
        </div>
        <div class="separator"> </div>
    </div>
    <!-- /.row (main row) -->


<?php } else { ?>
    <div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view reports</div>
<?php } ?>








      
  
<?php include"footer.php";?>

<script src="dist/plugin/bootstrap-table/bootstrap-table-print.min.js"></script>
<link href="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/tableExport.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF/jspdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.10.21/libs/jsPDF-AutoTable/jspdf.plugin.autotable.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/bootstrap-table.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.18.3/dist/extensions/export/bootstrap-table-export.min.js"></script>


<script>


  var $table = $('#sales_table')

  $(function() {
      
    $('#toolbar').find('select').change(function () {
        
      $table.bootstrapTable('destroy').bootstrapTable({
          
        exportDataType: $(this).val(),
        exportTypes: ['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'pdf'],
        columns: [
          {
            field: 'state',
            checkbox: true,
            visible: $(this).val() === 'selected'
          },
          {
            field: 'id',
            title: 'ID'
          }, {
            field: 'mobile',
            title: 'Mobile'
          }, {
            field: 'address',
            title: 'Address'
          },{
            field: 'order_date',
            title: 'Order Date'
          },{
            field: 'final_total',
            title: 'Final Total'
          },{
            field: 'payment_method',
            title: 'Payment Method'
          }
        ]
      })
    }).trigger('change')
  });
  
  
  
  
  
  	$(document).ready(function(){
      
    	$('#sales_date').daterangepicker({
			"autoApply": true,
			"showDropdowns": true,
            "alwaysShowCalendars":true,
			"startDate":moment(),
			"endDate":moment(),
			"locale": {
				"format": "DD/MM/YYYY",
				"separator": " - "
			},
		});

        $('#sales_date').on('apply.daterangepicker', function(ev, picker) {
			var drp = $('#sales_date').data('daterangepicker');
			$('#start_from').val(drp.startDate.format('YYYY-MM-DD'));
			$('#end_to').val(drp.endDate.format('YYYY-MM-DD'));
		});
			
        $('#sales_date').on('apply.daterangepicker', function(ev, picker) {
			var drp = $('#sales_date').data('daterangepicker');
			$('#start_from').val(drp.startDate.format('YYYY-MM-DD'));
			$('#end_to').val(drp.endDate.format('YYYY-MM-DD'));
            $('#sales_table').bootstrapTable('refresh');
		});
		
		$(document).on('change' , '#filter_by' , function(){
           $('#sales_table').bootstrapTable('refresh');
        });
        $(document).on('keyup' , '#sales_keyword' , function(){
           $('#sales_table').bootstrapTable('refresh');
        });
			
    });	
    
    function queryParams_sales(p){
        
		return {
			"start_date": $('#start_from').val(),
			"end_date": $('#end_to').val(),
		//	"filter_by": $('#filter_by').val(),
			"search": $('#sales_keyword').val(),
			limit:p.limit,
			sort:p.sort,
			order:p.id,
			offset:p.offset,
		};
	}
	
		
		

	
  

  
</script>



