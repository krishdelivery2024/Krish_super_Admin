<?php
    include_once('includes/functions.php');
?>
<?php
    if($permissions['categories']['read']==1) { 
        $sql_query = "SELECT id, name FROM main_category ORDER BY name ASC";
        $db->sql($sql_query);
        $res_main_category = $db->getResult();

        $selected_main_cat = '';
        if(isset($_SESSION['role']) && $_SESSION['role'] == 'seller' && !empty($_SESSION['main_cat_id'])){
            $selected_main_cat = $_SESSION['main_cat_id'];
        }
?>

<div class="content-header">
    <ol class="breadcrumb">
        <a class="btn btn-block btn-default" href="add-category.php"><i class="fa fa-plus-square"></i> Add New Category</a>
    </ol>
</div>
    <!-- Main row -->
    <div class="row">
        <!-- Left col -->
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Categories</h3>
                </div>
                <div class="box-body">
                    <form method="POST" id="filter_form" name="filter_form">
                        <div class="row">
                            <div class="col-md-4 col-sm-6 col-xs-12">
                                <label for="filter_main_cat" class="control-label">Filter by Main Category:</label>
                                <select id="filter_main_cat" name="filter_main_cat" class="form-control">
                                    <option value="">All Main Categories</option>
                                    <?php if(!empty($res_main_category)){ foreach($res_main_category as $mc){ ?>
                                        <option value="<?=$mc['id']?>" <?=($selected_main_cat == $mc['id']) ? 'selected' : ''?>><?=htmlspecialchars($mc['name'])?></option>
                                    <?php }} ?>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-hover" data-toggle="table" id="cateory_list" 
                        data-url="api-firebase/get-bootstrap-table-data.php?table=category"
                        data-page-list="[5, 10, 20, 50, 100, 200]"
                        data-show-refresh="true" data-show-columns="true"
                        data-side-pagination="server" data-pagination="true"
                        data-search="true" data-trim-on-search="false"
                        data-sort-name="id" data-sort-order="desc"
                        data-query-params="queryParams_category">
                        <thead>
                        <tr>
                            <th data-field="id" data-sortable="true">ID</th>
                            <th data-field="name" data-sortable="true">Name</th>
                            <th data-field="subtitle" data-sortable="true">Subtitle</th>
                            <th data-field="main_category_name" data-sortable="true">Main Category</th>
                            <th data-field="image">Image</th>
                            <th data-field="operate">Action</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="separator"> </div>
    </div>
    <script>
    function queryParams_category(p){
        return {
            "main_cat_id": $('#filter_main_cat').val(),
            limit: p.limit,
            sort: p.sort,
            order: p.order,
            offset: p.offset,
            search: p.search
        };
    }
    function queryParams_1(p){
        return queryParams_category(p);
    }
    $(document).ready(function(){
        $('#filter_main_cat').on('change', function(){
            $('#cateory_list').bootstrapTable('refresh');
        });
    });
    </script>
<?php } else { ?>
<div class="alert alert-danger topmargin-sm" style="margin-top: 20px;">You have no permission to view categories.</div>
<?php } ?>
