<?php 
	$page="View State City";
    include 'header.php';?>
	<?php 
		
		
			if(isset($_GET['id'])){
				$ID = $_GET['id'];
			}else{
				$ID = "";
			}
			// get image file from table
			// get image file from table
					$db->select('city','*',null,"state_id = $ID and name!='Choose Your City'");
					$res=$db->getResult();
			// create array variable to store menu image
			?>
			<?php
			if($db->numRows($result)==0)
			{?>
			<div class="content-wrapper">
				<div class="content-header">
          <h1>
            No Cities Available
            <small><a  href='state.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to States</a></small>
          </h1>
        </div>
			</div>
			<?php }
			else{
			?>
			
			<div class="content-wrapper">
			<div id="wrapper">
          <!-- Main row -->
		 
          <div class="row">
            <!-- Left col -->
				<div class="col-xs-12">
					<?php if($permissions['locations']['read']==1){?>
              <div class="box">
                <div class="box-header">
                  <h3 class="box-title">State_ID : <?php echo $ID;?></h3>
                  <div class="box-tools">
                  </div>
                </div><!-- /.box-header -->
                <div class="box-body table-responsive">
                  <table class="table table-hover">
                    <tr>
					<th>No.</th>
						<th>City Name</th>
						<th>Action</th>
                    </tr>
					<?php 
		$count=1;
			// delete all menu image files from directory
			 foreach($res as $row){
		 ?>
		<tr>
			<td><?php echo $count; ?></td>
			<td><?php echo $row['name'];?></td>
			<td><a href="edit-city.php?id=<?php echo $row['id'];?>"><i class="fa fa-edit"></i>Edit</a></td>
		</tr>
					<?php $count++; } ?>
                  </table>
                </div><!-- /.box-body -->
              </div><!-- /.box -->
               <?php } else { ?>
          	<div class="alert alert-danger">You have no permission to view cities</div>
				<?php } ?>
				<a  href='state.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to states</a>
            </div>
		 <!-- right col (We are only adding the ID to make the widgets sortable)-->
          </div><!-- /.row (main row) -->

        </div><!-- /.content -->
			</div>
			<?php }?>
<?php include 'footer.php'; ?>