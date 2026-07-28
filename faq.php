<?php 
$page="Frequently Asked Questions";
include"header.php";?>
 <?php
// 	  include("includes/functions.php");
		if(isset($_POST['btnAdd'])){
			if($permissions['faqs']['create']==1){
			$query = $db->escapeString($fn->xss_clean($_POST['query']));
			$answer = $db->escapeString($fn->xss_clean($_POST['answer']));
			// $status = $_POST['status'];
			$error = array();
			
				
				// create random image file name
				$function = new functions;
		
				// insert new data to menu table
				$sql_query = "INSERT INTO faq (question, answer)
						VALUES('$query', '$answer')";
						// echo $sql_query;
					// Execute query
					$db->sql($sql_query);

					// store result
					$result=$db->getResult();
					if(!empty($result)){
						$result=0;
					}else{
						$result=1;
					}
					
				
				
				if($result==1){
					$error['add_query'] = "<script>function myFunction() {window.location.reload(true);}</script>";
				}else{
					$error['add_query'] = " <span class='label label-danger'>Failed add query</span>";
				}
				}else{
				echo '<script>alert("You have no permission to create faq")</script>';
			}
			
		
			
		
		}
		// create object of functions class
		$function = new functions;
		
		// create array variable to store data from database
		$data = array();
		
		if(isset($_GET['keyword'])){	
			// check value of keyword variable
			$keyword = $function->sanitize($_GET['keyword']);
			$bind_keyword = "%".$keyword."%";
		}else{
			$keyword = "";
			$bind_keyword = $keyword;
		}
			
		if(empty($keyword)){
			$sql_query = "SELECT id, question, answer
					FROM faq
					ORDER BY id ASC";
		}else{
			$sql_query = "SELECT id, question, answer
					FROM faq where question LIKE ".$bind_keyword." 
					ORDER BY id ASC";
		}
			// Execute query
		$db->sql($sql_query);
			// store result 
		$res=$db->getResult();
			// get total records
			$total_records=$db->numRows($res);
		
			
		// check page parameter
		if(isset($_GET['page'])){
			$page = $db->escapeString($fn->xss_clean($_GET['page']));
		}else{
			$page = 1;
		}
						
		// number of data that will be display per page		
		$offset = 10;
						
		//lets calculate the LIMIT for SQL, and save it $from
		if ($page){
			$from 	= ($page * $offset) - $offset;
		}else{
			//if nothing was given in page request, lets load the first page
			$from = 0;	
		}	
		
		if(empty($keyword)){
			$sql_query = "SELECT id, question, answer
					FROM faq
					ORDER BY id ASC LIMIT ".$from.",".$offset."";
		}else{
			$sql_query = "SELECT id, question, answer
					FROM faq
					WHERE question LIKE '%".$keyword."%' 
					ORDER BY id ASC LIMIT ".$from.",".$offset."";
		}
	//	echo $sql_query;die;
			// Execute query
			$db->sql($sql_query);
			// store result 
			$res=$db->getResult();
			// print_r($res);
			// for paging purpose
			$total_records_paging = $total_records; 
		

		// if no data on database show "No Reservation is Available"
		if($total_records_paging == 0){
			echo "No Records found";
				
		}
	?>
		<div class="row small-spacing">
			<div class="col-lg-12 col-xs-12">
				<div class="box-content card white">
				    <div class="row"><div class=col-md-8>
					<h4 class="box-title">Frequently Asked Questions</h4>
					</div>
				<!--	<div class=col-md-4>
					<form method="get"><input type="text" name="keyword"><button type="submit">Search</button></form>
					</div>-->
					</div>
					<!-- /.box-title -->
					<div class="card-content">
                <div class="box-body">
                  <ol class="products-list product-list-in-box">
				  <?php
					if($permissions['faqs']['read']==1){
					foreach ($res as $row){ ?>
                      <div class="row">
                      <div class="col-md-9">
					  
                        <a href="faq.php" class="product-title"><?php echo $row['question'];?> </a>
                        <p class="product-description">
                          <?php echo $row['answer'];?>
                        </p>
                      </div>
					  <div class="col-md-3">
					  <a href="delete-query.php?id=<?php echo $row['id'];?>" class="product-title"><span class="label label-warning pull-right">Delete</span></a>

					  <a href="edit-query.php?id=<?php echo $row['id'];?>" class="product-title"><span class="label label-success pull-right">Edit | Answer</span></a>					  
					  </div>
					  </div>
					<hr>
					<?php } }  else {?>
						<div class="alert alert-danger">You have no permission to read faq</div>
					<?php } ?>
					<?php $function->doPages($offset, 'faq.php', '', $total_records, $keyword);?>
                  </ol>
                </div><!-- /.box-body -->
		<?php echo isset($error['add_query']) ? $error['add_query'] : '';?>
               <div class="box-footer">
			    <form  method="post" enctype="multipart/form-data">
                  <div class="input-group">
                    <input class="form-control" name="query" placeholder="Add a Query...">
					<?php echo isset($error['query']) ? $error['query'] : '';?>
					<input class="form-control" name="answer" placeholder="Add a Answer..."/>
					<?php echo isset($error['answer']) ? $error['answer'] : '';?>
                    <div class="input-group-btn">
                     <button style="color:white !important" onclick="myFunction()" class="btn btn-primary" type="submit" name="btnAdd"><i class="fa fa-plus"></i></button>
					  <script>
							function myFunction() {
								window.location.reload(true);
							}
							</script>
                    </div>
                  </div>
				</form>
			
                </div><!-- /.box-footer -->
					</div>
					<!-- /.card-content -->
				</div>
				<!-- /.box-content -->
			</div>
			<!-- /.col-lg-6 col-xs-12 -->
			
		</div>
		<!-- /.row small-spacing -->		
		<?php include"footer.php";?>