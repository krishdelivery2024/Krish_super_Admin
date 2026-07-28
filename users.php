<?php 
$page="Create or Edit Users";
include"header.php";?>



<style>
table {
font-family: arial, sans-serif;
border-collapse: collapse;
width: 100%;
}

td, th {
border: 1px solid #dddddd;
text-align: left;
padding: 8px;
}

tr:nth-child(even) {
background-color: #dddddd;
}
</style>
<?php
if($_SESSION['role']=='editor'){
  echo "<p class='alert alert-danger topmargin-sm'>Access denied - You are not authorized to access this page.</p>";
  return false;
}
?>
<!-- Main row -->
<div class="row">
    <div class="col-md-6">
        <?php if($_SESSION['role']!='editor'){?>
          <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Product master Users</h3>
                </div>
                  <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="system-users"
                        data-url="api-firebase/get-bootstrap-table-data.php?table=system-users"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="asc">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">SL. No</th>
                            <th data-field="username" data-sortable="true">Name</th>
                            <th data-field="mobile" data-sortable="true">Mobile</th>
                            <th data-field="email" data-sortable="true">Email</th>
                            <th data-field="role" data-sortable="true" data-visible="false">Role</th>
                            <th data-field="city_ids" data-sortable="true" data-visible="false">City Id(s)</th>
                            <th data-field="cities" data-sortable="true" data-visible="false">Cities</th>
                            <th data-field="created_by" data-sortable="true" data-visible="false">Created By</th>
                            <th data-field="created_by_id" data-sortable="true" data-visible="false">Created By Id</th>
                            <th data-field="date_created" data-visible="false">Date Created</th>
                            <th data-field="operate" data-events="actionEvents">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
          <?php } ?>
          </div>
          
    <div class="col-md-6">
          <!-- general form elements -->
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Add Product Master User</h3>

            </div><!-- /.box-header -->
            <!-- form start -->
            <form  method="post" id="add_form" action="public/db-operation.php">
                <input type="hidden" id="add_system_user" name="add_system_user" value="1">
                <input type="hidden" id="user_id" name="user_id" value="1">
              <div class="box-body">
                <div class="form-group">
                  <label for="">Name</label>
                  <input type="text" class="form-control"  id="user_name"  name="username">
                </div>
                          <div class="form-group">
                  <label for="">Email</label>
                  <input type="text" class="form-control"  name="email">
                </div>
                <div class="form-group">
                  <label for="">Mobile</label>
                  <input type="text" class="form-control"  name="mobile">
                </div>
                <div class="form-group">
                  <label for="">Password</label>
                  <input type="password" class="form-control"  name="password" id="password">
                </div>
                <div class="form-group">
                  <label for="">Confirm Password</label>
                  <input type="password" class="form-control"  name="confirm_password">
                </div>
                <div style="display:none" class="form-group">
                  <label for="">Role</label>
                  <select name="role" class="form-control">
                  	<option value="">---Select---</option>
                    <option value="admin">Admin</option>
                  	<option value="editor" selected>Editor</option>
                  </select>
                </div>
                
              </div><!-- /.box-body -->


              <div class="box-footer">
                <button type="submit" class="btn btn-primary" id="submit_btn" name="btnAdd">Add</button>
                <input type="reset" class="btn-warning btn" value="Clear"/>
            
              </div>
              <div class="form-group">
                  
                  <div id="result" style="display: none;"></div>
                </div>
            
          </div><!-- /.box -->
          
         </div>
         <div class="col-xs-6" >
        <div class="box box-primary">
            <table>
        <tr>
          <th>Module/Permissions</th>
          <th>Create</th>
          <th>Read</th>
          <th>Update</th>
          <th>Delete</th>
        </tr>
        <tr>
          <td>Orders</td>
          <td><input type="checkbox" id="create-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-order" name="is-create-order" value="1">
                 </td>
          <td><input type="checkbox" id="read-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-order" name="is-read-order" value="1">
                 </td>
                 <td><input type="checkbox" id="update-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-order" name="is-update-order" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-order" name="is-delete-order" value="1">
                 </td>
              </tr>
              <tr>
          <td>Categories</td>
          <td><input type="checkbox" id="create-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-category" name="is-create-category" value="1">
                 </td>
          <td><input type="checkbox" id="read-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-category" name="is-read-category" value="1">
                 </td>
                 <td><input type="checkbox" id="update-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-category" name="is-update-category" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-category-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-category" name="is-delete-category" value="1">
                 </td>
              </tr>
              <tr>
          <td>Subcategories</td>
          <td><input type="checkbox" id="create-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-subcategory" name="is-create-subcategory" value="1">
                 </td>
          <td><input type="checkbox" id="read-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-subcategory" name="is-read-subcategory" value="1">
                 </td>
                 <td><input type="checkbox" id="update-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-subcategory" name="is-update-subcategory" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-subcategory-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-subcategory" name="is-delete-subcategory" value="1">
                 </td>
                </tr>
              <tr>
          <td>Products</td>
          <td><input type="checkbox" id="create-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-product" name="is-create-product" value="1">
                 </td>
          <td><input type="checkbox" id="read-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-product" name="is-read-product" value="1">
                 </td>
                 <td><input type="checkbox" id="update-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-product" name="is-update-product" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-product-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-product" name="is-delete-product" value="1">
                 </td>
                </tr>
              <tr>
                <td>Products Order</td>
                <td>-</td>
                <td><input type="checkbox" id="read-products-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-products-order" name="is-read-products-order" value="1">
                 </td>
                 <td><input type="checkbox" id="update-products-order-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-products-order" name="is-update-products-order" value="1">
                 </td>
                 <td>-</td>
                </tr>
              <tr>
          <td>Home Slider Images</td>
          <td><input type="checkbox" id="create-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-home-slider" name="is-create-home-slider" value="1">
                 </td>
          <td><input type="checkbox" id="read-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-home-slider" name="is-read-home-slider" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-home-slider-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-home-slider" name="is-delete-home-slider" value="1">
                 </td>
              </tr>
              <tr>
                <td>New Offer Images</td>
                <td><input type="checkbox" id="create-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-new-offer" name="is-create-new-offer" value="1">
                 </td>
                <td><input type="checkbox" id="read-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-new-offer" name="is-read-new-offer" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-new-offer-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-new-offer" name="is-delete-new-offer" value="1">
                 </td>
              </tr>
              <tr>
                <td>Promo Codes</td>
                <td><input type="checkbox" id="create-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-promo" name="is-create-promo" value="1">
                 </td>
                <td><input type="checkbox" id="read-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-promo" name="is-read-promo" value="1">
                 </td>
                 <td><input type="checkbox" id="update-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-promo" name="is-update-promo" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-promo-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-promo" name="is-delete-promo" value="1">
                 </td>
                </tr>
              <tr>
                <td>Featured Section</td>
                <td><input type="checkbox" id="create-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-featured" name="is-create-featured" value="1">
                 </td>
                <td><input type="checkbox" id="read-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-featured" name="is-read-featured" value="1">
                 </td>
                 <td><input type="checkbox" id="update-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-featured" name="is-update-featured" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-featured-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-featured" name="is-delete-featured" value="1">
                 </td>
               </tr>
               <tr>
                <td>Customers</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="read-customers-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-customers" name="is-read-customers" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Payment Requests</td>
                <td>-
                 </td>
                <td><input type="checkbox" id="read-payment-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-payment" name="is-read-payment" value="1">
                 </td>
                 <td><input type="checkbox" id="update-payment-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-payment" name="is-update-payment" value="1">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Return Requests</td>
                <td>-
                <td><input type="checkbox" id="read-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-return" name="is-read-return" value="1">
                 </td>
                 <td><input type="checkbox" id="update-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-return" name="is-update-return" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-return-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-return" name="is-delete-return" value="1">
                 </td>
               </tr>
               <tr>
                <td>Delivery Boys</td>
                <td><input type="checkbox" id="create-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-delivery" name="is-create-delivery" value="1">
                 </td>
                <td><input type="checkbox" id="read-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-delivery" name="is-read-delivery" value="1">
                 </td>
                 <td><input type="checkbox" id="update-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-delivery" name="is-update-delivery" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-delivery-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-delivery" name="is-delete-delivery" value="1">
                 </td>
               </tr>
               <tr>
                <td>Notifications</td>
                <td><input type="checkbox" id="create-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-notification" name="is-create-notification" value="1">
                 </td>
                 <td><input type="checkbox" id="read-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-notification" name="is-read-notification" value="1">
                 </td>
                 <td>-</td>
                 <td><input type="checkbox" id="delete-notification-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-notification" name="is-delete-notification" value="1">
                 </td>
               </tr>
               <tr>
                <td>Transactions</td>
                <td>-
                 </td>
                 <td><input type="checkbox" id="read-transaction-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-transaction" name="is-read-transaction" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>System settings</td>
                <td>-
                 </td>
                  <td><input type="checkbox" id="read-settings-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-settings" name="is-read-settings" value="1">
                 </td>
                 <td><input type="checkbox" id="update-settings-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-settings" name="is-update-settings" value="1">
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Locations</td>
                <td><input type="checkbox" id="create-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-location" name="is-create-location" value="1">
                 </td>
                <td><input type="checkbox" id="read-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-location" name="is-read-location" value="1">
                 </td>
                 <td><input type="checkbox" id="update-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-location" name="is-update-location" value="1">
                 </td>
                 <td><input type="checkbox" id="delete-location-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-location" name="is-delete-location" value="1">
                 </td>
               </tr>
               <tr>
                <td>Reports</td>
                <td><input type="checkbox" id="create-report-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-report" name="is-create-report" value="1">
                 </td>
                <td><input type="checkbox" id="read-report-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-report" name="is-read-report" value="1">
                 </td>
                 <td>-
                 </td>
                 <td>-
                 </td>
               </tr>
               <tr>
                <td>Faqs</td>
                <td><input type="checkbox" id="create-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-create-faq" name="is-create-faq" value="1">
                 </td>
                <td><input type="checkbox" id="read-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-read-faq" name="is-read-faq" value="1">
                 </td>
                 <td><input type="checkbox" id="update-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-update-faq" name="is-update-faq" value="1">
                 </td>
                <td><input type="checkbox" id="delete-faq-button" class="js-switch" checked>
                     <input type="hidden" id="is-delete-faq" name="is-delete-faq" value="1">
                 </td>
               </tr>

      </table>
            
        </div>
        </form>
    </div>
    <!-- Left col -->
    
    <div class="separator"> </div>
</div>

<?php include"footer.php";?>