<?php 
ob_start();
session_start();
// print_r($_SESSION);
	include_once('includes/crud.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
    include_once ('includes/custom-functions.php');
    include_once ('includes/functions.php');
    $function = new custom_functions;
    // set time for session timeout
    $currentTime = time() + 25200;
    $expired = 3600;
    if (isset($_SESSION['id']) && "super admin" == $_SESSION['role'] ) {
        $sql_login="select id from `admin` where id=".$_SESSION['id']." AND web_login='".$_SESSION['secretkey']."'";
        $db->sql($sql_login);
        $sql_login=$db->getResult();  
        if(count($sql_login)==0){
            echo "<script>alert('This login is accessed in Another place. Please Re-login');</script>";
            session_destroy();
            header("location:index.php");
        }
    }
    
    // if session not set go to login page
    if (!isset($_SESSION['user'])) {
        header("location:index.php");
    }
   
		if ($currentTime > $_SESSION['timeout']) {
			if($_SESSION['role'] == 'seller'){
			$redirect ='seller-login.php';
			}else{
			$redirect ='index.php';
			}	
			session_destroy();
			header("location:$redirect");
		}
    // destroy previous session timeout and create new one
    unset($_SESSION['timeout']);
    $_SESSION['timeout'] = $currentTime + $expired;
    $permissions = $function->get_permissions($_SESSION['id']);
    
	include('includes/variables.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $permissions = $fn->get_permissions($_SESSION['id']);
    // print_r($permissions);
    $config = $fn->get_configurations();
    if(isset($config['system_timezone']) && isset($config['system_timezone_gmt'])){
        date_default_timezone_set($config['system_timezone']);
        $db->sql("SET `time_zone` = '".$config['system_timezone_gmt']."'");
    }else{
        date_default_timezone_set('Asia/Kolkata');
        $db->sql("SET `time_zone` = '+05:30'");
    }
    
    $settings['app_name'] = $config['app_name'];
    $store_state = $config['store_state'];
    $words = explode(" ", $settings['app_name']);
    $acronym = "";
    foreach ($words as $w) {
      //  $acronym .= $w[0];
    }
    $currency = $fn->get_settings('currency');
    $settings['currency'] = $currency;
    $role = $fn->get_role($_SESSION['id']);
    
    $sql_logo="select value from `settings` where variable='Logo' OR variable='logo'";
    $db->sql($sql_logo);
    $res_logo=$db->getResult();
// Seller Details
	if($_SESSION['role'] == 'seller'){
		$main_cat_id = $_SESSION['main_cat_id'];
		$seller_id = $_SESSION['id'];
	}else{
		$main_cat_id = 0;
		$seller_id = 0;
	}

		$cur_date = date('Y-m-d');
	$cur_date = $cur_date . " 00:00:00";

	$sql222 = "
		SELECT
			SUM(
				CASE 
					WHEN (o.payment_method = 'cod' OR o.payment_method = 'wallet' OR o.payment_method = 'LoyaltyPoints' OR o.payment_status = 1)
					THEN 1 
					ELSE 0 
				END
			) AS active_orders_count,
			SUM(
				CASE 
					WHEN (o.payment_method != 'cod' AND o.payment_method != 'wallet' AND o.payment_method != 'LoyaltyPoints' AND o.payment_status = 0)
					THEN 1 
					ELSE 0 
				END
			) AS inactive_orders_count
		FROM `orders` o
		WHERE o.date_added >= '$cur_date' AND o.seller_id = '$seller_id' 
	";

	$db->sql($sql222);
	$res11 = $db->getResult();

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title><?=$settings['app_name']?></title>

	<!-- Main Styles -->
	<link rel="stylesheet" href="dist/styles/style.min.css">
	<link rel="stylesheet" href="dist/fonts/themify-icons/themify-icons.css">
	<link rel="stylesheet" href="dist/plugin/mCustomScrollbar/jquery.mCustomScrollbar.min.css">
	<link rel="stylesheet" href="dist/plugin/waves/waves.min.css">
	<link rel="stylesheet" href="dist/plugin/sweet-alert/sweetalert.css">
	<link rel="stylesheet" href="dist/plugin/percircle/css/percircle.css">
	<link rel="stylesheet" href="dist/plugin/chart/chartist/chartist.min.css">
	<link rel="stylesheet" href="dist/plugin/fullcalendar/fullcalendar.min.css">
	<link rel="stylesheet" href="dist/plugin/fullcalendar/fullcalendar.print.css" media='print'>
	<link rel="stylesheet" href="dist/plugin/bootstrap-table/bootstrap-table.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="dist/plugin/datepicker/css/bootstrap-datepicker.min.css">
	<link rel="stylesheet" href="dist/plugin/daterangepicker/daterangepicker.css">
	<link rel="stylesheet" href="dist/plugin/switchery/switchery.css">
	<link rel="stylesheet" href="dist/plugin/select2/css/select2.min.css">
	<script src="dist/scripts/jquery.min.js"></script>
	<script src="dist/plugin/jquery-ui/jquery-ui.min.js"></script>
	<script src="dist/scripts/jquery.validate.min.js"></script>
	<script src="dist/plugin/switchery/switchery.js"></script>	
	<script src="JsBarcode.all.min.js"></script>
	<link rel="stylesheet" href="dist/styles/mystyle.css">

	<script>
    $(document).ready(function () {
        var $activeMenuItem = $('.navigation .current, .navigation .active');
        if ($activeMenuItem.length > 0) {
            var $container = $('.navigation');
            var scrollTop = $activeMenuItem.offset().top - $container.offset().top + $container.scrollTop() - 100;
            $container.animate({ scrollTop: scrollTop }, 400);
			}
		});
	</script>
<?php if($page=="Store Settings"){ ?>
    <script type="module" src="map.js"></script>
<?php } ?>
<?php if($_SESSION['role'] =='seller'){ ?>
<style>
	.fixed-navbar, .navigation .menu>li.current>a, .header .logo {background: #fb8b40;}
	.menu-mobile-button{background: #e55c00; }
	.navigation .menu a.active, .navigation .menu a.current {
    background: #fb8b40;
    color: white;
	}
	.seller-title {
	margin-left: 200px; 
	}
</style>
<?php } ?>

<!-- ==================== New Order Notification (styles) ==================== -->
<style>
.new-order-popup {
    display: none;
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 99999;
    width: 340px;
    max-width: calc(100% - 40px);
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    animation: newOrderSlideIn 0.35s ease;
}
.new-order-popup.show { display: block; }
@keyframes newOrderSlideIn {
    from { transform: translateX(120%); opacity: 0; }
    to   { transform: translateX(0); opacity: 1; }
}
.new-order-popup__inner { padding: 20px; position: relative; text-align: center; }
.new-order-popup__close {
    position: absolute; top: 6px; right: 10px; border: none; background: none;
    font-size: 22px; line-height: 1; cursor: pointer; color: #999;
}
.new-order-popup__close:hover { color: #333; }
.new-order-popup__icon { font-size: 40px; margin-bottom: 4px; }
.new-order-popup__title { margin: 0 0 4px; font-size: 17px; font-weight: 700; }
.new-order-popup__list { list-style: none; padding: 0; margin: 10px 0; max-height: 160px; overflow-y: auto; text-align: left; }
.new-order-popup__list li { padding: 8px 10px; border-bottom: 1px solid #f1f1f1; font-size: 13px; }
.new-order-popup__list li:last-child { border-bottom: none; }
.new-order-popup__btn {
    display: inline-block; margin-top: 6px; padding: 8px 20px; border: none;
    border-radius: 6px; background: #fb8b40; color: #fff; font-weight: 600;
    cursor: pointer; font-size: 13px;
}
.new-order-popup__btn:hover { background: #e55c00; }

/* ---- Sound-enable banner (shown only if browser blocks autoplay) ---- */
.sound-enable-banner {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 999999;
    background: #fb8b40;
    color: #fff;
    text-align: center;
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}
.sound-enable-banner.show { display: block; }
.sound-enable-banner span { text-decoration: underline; }
</style>
</head>

<body>

<!-- Sound enable banner: shown only if the browser blocks autoplay -->
<div id="soundEnableBanner" class="sound-enable-banner">
    🔔 Click here to enable order notification sound <span>Enable Sound</span>
</div>

<div class="main-menu">
	<header class="header">
		<a href="home.php" class="logo"><i class="ico ti-shopping-cart"></i><?=$settings['app_name']?></a>
		<button type="button" class="button-close fa fa-times js__menu_close"></button>
	</header>
	<!-- /.header -->
	<div class="content">

		<div class="navigation">
			<h5 class="title">Navigation</h5>
			<!-- /.title -->
			<ul class="menu js__accordion">
			
				<li <?php if($page=="Home"){?> class="current" <?php } ?>>			
					<a class="waves-effect" href="home.php"><i class="menu-icon ti-dashboard"></i><span>Dashboard</span></a>
				</li>

				<li <?php if($page=="Orders"){?> class="current" <?php } ?>>		
					<a class="waves-effect" href="orders.php"><i class="menu-icon ti-shopping-cart"></i><span>Order</span><span class="notice notice-blue"><?php if($res11[0]['active_orders_count'] == ''){echo '0';}else{ echo $res11[0]['active_orders_count']; }?></span></a>
				</li>
				<!-- Seller links -->
	<?php if($_SESSION['role'] =='seller'){ ?>
				<li <?php if($page=="Incomplete Orders"){?> class="current" <?php } ?>>
					<a class="waves-effect" href="incomplete-orders.php"><i class="menu-icon ti-shopping-cart"></i><span>Incomplete Order</span><span class="notice notice-blue"><?php if($res11[0]['inactive_orders_count'] == ''){echo '0';}else{ echo $res11[0]['inactive_orders_count']; }?></span></a>
				</li>

				<li <?php if($page=="Add Product" || $page=="Products" || $page=="Product Order" || $page=="Min. Stock Products" || $page=="Bulk Upload Products"){?> class="active" <?php } ?>>
				<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-package"></i><span>Products</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Add Product" || $page=="Products" || $page=="Product Order" || $page=="Min. Stock Products" || $page=="Bulk Upload Products"){?> style="display: block;" <?php }?>>
						<li <?php if($page=="Add Product"){?> class="current"<?php } ?>>				
							<a href="add-product.php" <?php if($page=="Add Product"){?>class="active"<?php } ?>>Add Product</a>
						</li>
						<li <?php if($page=="Products"){?>class="current" <?php }?>>
							<a href="products.php" <?php if($page=="Products"){?>class="active"<?php }?>>Manage Products</a>
						</li>			
						<li <?php if($page=="Min. Stock Products"){?>class="current"<?php } ?>>
							<a href="min-stock-products.php" <?php if($page=="Min. Stock Products"){?>class="active"<?php } ?>>Min. Stock Products</a>
						</li>	
						<li <?php if($page=="Product Order"){?>class="current"<?php } ?>>
							<a href="products-order.php" <?php if($page=="Product Order"){?>class="active"<?php } ?>>Products Order</a>
						</li>
						<li <?php if($page=="Bulk Upload Products"){?>class="current"<?php } ?>>
							<a href="bulk-edit-product.php" <?php if($page=="Bulk Upload Products"){?>class="active"<?php } ?>>Bulk Edit Products</a>
						</li>
					</ul>
				</li>				

	<?php }else{ ?>

				<li <?php if($page=="Main Categories Order" || $page=="Main Category" || $page=="Add Main Category" || $page=="Edit Main Category"){?> class="active" <?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-view-list-alt"></i><span>Main Categories</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Main Categories Order" || $page=="Main Category" || $page=="Add Main Category"  || $page=="Edit Main Category"){?> style="display: block;" <?php } ?>>				
						<li <?php if($page=="Main Category" || $page=="Add Main Category"  || $page=="Edit Main Category"){?> class="current" <?php }?>>
							<a class="waves-effect" href="main_categories.php"><i class="menu-icon ti-direction"></i><span>Manage Categories</span></a>
						</li>
						
						<li <?php if($page=="Main Categories Order"){?>class="current" <?php } ?>>
							<a class="waves-effect" href="maincategoriesorder.php"><i class="menu-icon ti-direction"></i><span>Main Categories Order</span></a>
						</li>
					</ul>

				
				<li <?php if($page=="Categories" || $page=="Categories Order"){?>class="active" <?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-view-list-alt"></i><span>Categories</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content"  <?php if($page=="Categories" || $page=="Categories Order"){?>style="display: block;"<?php } ?>>					
						<li <?php if($page=="Categories"){?> class="current" <?php } ?>>
							<a class="waves-effect" href="categories.php"><i class="menu-icon ti-direction"></i><span>Manage Categories</span></a>
						</li>				
						<li <?php if($page=="Categories Order"){?>class="current" <?php } ?>>
							<a class="waves-effect" href="categoriesorder.php"><i class="menu-icon ti-direction"></i><span>Categories Order</span></a>
						</li>
					</ul>	

					<li <?php if($page=="Brands"){?> class="current" <?php } ?>>     			
						<a class="waves-effect" href="brands.php"><i class="menu-icon ti-apple"></i><span>Brands</span></a>
					</li>				
				
				<li <?php if($page=="Delivery Method"){?>class="current" <?php } ?>>    			
					<a class="waves-effect" href="delivery_method.php"><i class="menu-icon ti-truck"></i><span>Delivery Method</span></a>
				</li>

				<li <?php if($page=="Customers" || $page=="Manage Customer Wallet" || $page=="Customer Referral Report" || $page=="Loyalty Points"){?>class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-user"></i><span>Customers</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Customers" || $page=="Manage Customer Wallet" || $page=="Customer Referral Report"  || $page=="Loyalty Points"){?>style="display: block;"<?php } ?>>				
						<li <?php if($page=="Customers"){?>class="current"<?php } ?>>
							<a  <?php if($page=="Customers"){?>class="active" <?php } ?> href="customers.php">Customers</a>
						</li>					
						<li <?php if($page=="Manage Customer Wallet"){?>class="current"<?php } ?>>
							<a <?php if($page=="Manage Customer Wallet"){?>class="active"<?php } ?> href="manage-customer-wallet.php">Manage Customer Wallet</a>
						</li>
						<li <?php if($page=="Loyalty Points"){?>class="current"<?php } ?>>
							<a <?php if($page=="Loyalty Points"){?>class="active"<?php } ?> href="loyaltypoint.php">Loyalty Points</a>
						</li>					
						<li <?php if($page=="Customer Referral Report"){?>class="current"<?php } ?>>
							<a <?php if($page=="Customer Referral Report"){?>class="active"<?php } ?> href="customer_refferal_report.php">Customer Referral Report</a>
						</li>
					</ul>
				</li>

				<li <?php if($page=="Products Unlisted"){?> class="current" <?php } ?>>     			
						<a class="waves-effect" href="products_unlisted.php"><i class="menu-icon ti-view-list-alt"></i><span>Unlisted Products</span></a>
					</li>	
				
	<?php }?>
			</ul>

	<?php if($_SESSION['role'] !='seller'){ ?>
			<h5 class="title">User Interface</h5>
			<ul class="menu js__accordion">
				
				<li <?php if($page=="Main Slider Images"){?>class="current" <?php } ?>>			
					<a class="waves-effect" href="main-slider.php"><i class="menu-icon ti-image"></i><span>Home Slider Images</span></a>
				</li>				
				<li <?php if($page=="Promo Code"){?>class="current" <?php } ?>>
					<a class="waves-effect" href="promo-code.php"><i class="menu-icon ti-wand"></i><span>Promo code</span></a>
				</li>				
					
				<li <?php if($page=="Sellers" || $page=="Edit Seller" || $page=="Seller Order"){?> class="active" <?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-user"></i><span>Sellers</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Sellers" || $page=="Edit Seller" || $page=="Seller Order"){?> style="display: block;" <?php } ?>>
					<li <?php if($page=="Sellers" || $page=="Edit Seller"){?> class="current" <?php } ?>>
						<a class="waves-effect" <?php if($page=="Sellers" || $page=="Edit Seller"){?> class="active" <?php } ?> href="sellers.php"><i class="menu-icon ti-direction"></i><span>Manage Sellers</span></a>
					</li>
					<li <?php if($page=="Seller Order"){?> class="current" <?php } ?>>
						<a class="waves-effect" <?php if($page=="Seller Order"){?> class="active" <?php } ?> href="seller-order.php"><i class="menu-icon ti-bar-chart-alt"></i><span>Seller Order</span></a>
					</li>
				</ul>
				
				<li <?php if($page=="Delivery Boys" || $page=="Fund Transfer"){?> class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-user"></i><span>Delivery Boys</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Delivery Boys" || $page=="Fund Transfer"){?>style="display: block;"<?php } ?>>
						<li <?php if($page=="Delivery Boys"){?>class="current"<?php } ?>><a <?php if($page=="Delivery Boys"){?>class="active"<?php } ?> href="delivery-boys.php">Manage Delivery Boys</a></li>
						<li <?php if($page=="Fund Transfer"){?>class="current"<?php } ?>><a  <?php if($page=="Fund Transfer"){?>class="active"<?php } ?> href="fund-transfers.php">Fund Transfers</a></li>
					</ul>
				</li>
				
				<li <?php if($page=="Fire Base Notifications"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="notification.php"><i class="menu-icon ti-bell"></i><span>Send notification</span></a>
				</li>				
				<li <?php if($page=="Transactions"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="transaction.php"><i class="menu-icon ti-list"></i><span>Transaction</span></a>
				</li>				
				<li <?php if($page=="Wallet Transactions"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="wallet-transactions.php"><i class="menu-icon ti-gift"></i><span>Wallet Transactions</span></a>
				</li>				
				
				<li <?php if($page=="Store Settings" || $page=="Payment Methods Settings" || $page=="Time Slots" || $page=="Notification Settings" || $page=="Contact Us" || $page=="Privacy Policy" || $page=="About US"){?>class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-settings"></i><span>System</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Store Settings" || $page=="Payment Methods Settings" || $page=="Time Slots" || $page=="Notification Settings" || $page=="Contact Us" || $page=="Privacy Policy" || $page=="About US"){?>style="display: block;"<?php } ?>>
						<li <?php if($page=="Store Settings"){?>class="current"<?php } ?>><a <?php if($page=="Store Settings"){?>class="active"<?php } ?> href="settings.php">Store Settings</a></li>
						<li <?php if($page=="Payment Methods Settings"){?>class="current"<?php } ?>><a <?php if($page=="Payment Methods Settings"){?>class="active"<?php } ?> href="payment-methods-setting.php">Payment Methods</a></li>
						<li <?php if($page=="Time Slots"){?>class="current"<?php } ?>><a <?php if($page=="Time Slots"){?>class="active"<?php } ?> href="time-slots.php">Time slots</a></li>
						<li <?php if($page=="Notification Settings"){?>class="current"<?php } ?>><a <?php if($page=="Notification Settings"){?>class="active"<?php } ?> href="notification-settings.php">Notification Settings</a></li>
						<li <?php if($page=="Contact Us"){?>class="current"<?php } ?>><a <?php if($page=="Contact Us"){?>class="active"<?php } ?> href="contact-us.php">Contact Us</a></li>
						<li <?php if($page=="Privacy Policy"){?>class="current"<?php } ?>><a <?php if($page=="Privacy Policy"){?>class="active"<?php } ?> href="privacy-policy.php">Privacy Policy</a></li>
						<li <?php if($page=="About US"){?>class="current"<?php } ?>><a <?php if($page=="About US"){?>class="active"<?php } ?> href="about-us.php">About Us</a></li>
					</ul>
				</li>
				
				<li <?php if($page=="Cities" || $page=="Areas" || $page=="State" || $page=="Routes"){?>class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-location-pin"></i><span>Location</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Cities" || $page=="Areas" || $page=="State" || $page=="Routes"){?>style="display: block;"<?php } ?>>
						<li <?php if($page=="State"){?>class="current"<?php } ?>><a <?php if($page=="State"){?>class="active"<?php } ?> href="state.php">States</a></li>				
						<li <?php if($page=="Cities"){?>class="current"<?php } ?>><a <?php if($page=="Cities"){?>class="active"<?php } ?> href="city.php">Cities</a></li>
					    <li <?php if($page=="Areas"){?>class="current"<?php } ?>><a <?php if($page=="Areas"){?>class="active"<?php } ?> href="areas.php">Area</a></li>
					</ul>
				</li>
				
				<li <?php if($page=="Sales Report" || $page=="Delivery Boy Report"){?>class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-agenda"></i><span>Reports</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Sales Report" || $page=="Invoice Reports" || $page=="High Buying Customers Report" || $page=="Highest Selling Products Report" || $page=="Month wise Product Sales Report"){?>style="display: block;"<?php } ?>>
						<li <?php if($page=="Sales Report"){?>class="current"<?php }?>><a <?php if($page=="Sales Report"){?>class="active"<?php }?> href="sales-report.php">Sales Report</a></li>
						<li <?php if($page=="Delivery Boy Report"){?>class="current"<?php }?>><a <?php if($page=="Delivery Boy Report"){?>class="active"<?php }?> href="delivery-boy-report.php">Delivery Boy Report</a></li>
					</ul>
				</li>
				
				<li <?php if($page=="Frequently Asked Questions"){?>class="current" <?php } ?>>
					<a class="waves-effect" href="faq.php"><i class="menu-icon ti-info"></i><span>FAQs</span>
					<?php 
                            $query="select * from faq where status=1 ";
                            $db->sql($query);
                            $result=$db->getResult();
                            $count=$db->numRows($result);
                            if($count)
                            { ?>
                        <span class="notice notice-blue"><?php echo $count; ?></span>
                        <?php	} ?>
						</a>
				</li>
			</ul>
	<?php } ?>
	<?php if($_SESSION['role'] =='seller'){ ?>
			<h5 class="title">Additions</h5>
			<ul class="menu js__accordion">		
				<li <?php if($page=="Promo Code"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="promo-code.php"><i class="menu-icon ti-wand"></i><span>Promo code</span></a>
				</li>	
				<li <?php if($page=="Product Rating"){?> class="current" <?php } ?>>    			
					<a class="waves-effect" href="product_rating.php"><i class="menu-icon ti-star"></i><span>Product Rating</span></a>
				</li>	
				<li <?php if($page=="Return Requests"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="return-requests.php"><i class="menu-icon ti-truck"></i><span>Return Requests</span></a>
				</li>	
				<li <?php if($page=="Seller Store Setting"){?>class="current"<?php } ?>>
					<a class="waves-effect" href="seller-store-settings.php"><i class="menu-icon ti-settings"></i><span>Settings</span></a>
				</li>						
				<li <?php if($page=="Sales Report" || $page=="Invoice Reports" || $page=="High Buying Customers Report" || $page=="Highest Selling Products Report" || $page=="Month wise Product Sales Report"){?>class="active"<?php } ?>>
					<a class="waves-effect parent-item js__control" href="#"><i class="menu-icon ti-agenda"></i><span>Reports</span><span class="menu-arrow fa fa-angle-down"></span></a>
					<ul class="sub-menu js__content" <?php if($page=="Sales Report" || $page=="Invoice Reports" || $page=="High Buying Customers Report" || $page=="Highest Selling Products Report" || $page=="Month wise Product Sales Report"){?>style="display: block;"<?php } ?>>
						<li <?php if($page=="Sales Report"){?>class="current"<?php }?>><a <?php if($page=="Sales Report"){?>class="active"<?php }?> href="sales-report.php">Sales Report</a></li>
						<li <?php if($page=="Invoice Reports"){?>class="current"<?php }?>><a <?php if($page=="Invoice Reports"){?>class="active"<?php }?> href="invoices.php">Invoice Report</a></li>
						<li <?php if($page=="High Buying Customers Report"){?>class="current"<?php }?>><a <?php if($page=="High Buying Customers Report"){?>class="active"<?php }?> href="high-buying-customer.php">High Buying Customers</a></li>
						<li <?php if($page=="Highest Selling Products Report"){?>class="current"<?php }?>><a  <?php if($page=="Highest Selling Products Report"){?>class="active"<?php } ?> href="high-sell-products.php">Highest Selling Products</a></li>
					</ul>
				</li>
			</ul>
	<?php } ?>
		</div>
		<!-- /.navigation -->
	</div>
	<!-- /.content -->
</div>
<!-- /.main-menu -->
<div class="fixed-navbar">
	<div class="pull-left">
		<button type="button" class="menu-mobile-button glyphicon glyphicon-menu-hamburger js__menu_mobile"></button>
		<h1 class="page-title"><?=$page?> <span class="seller-title"><?php if($_SESSION['role'] =='seller'){ echo $_SESSION['user']; } ?></span></h1>
	</div>
	<div class="pull-right">
		<div class="ico-item">
			<?=$_SESSION['role'];?> <i class="ti-user"></i>
			<ul class="sub-ico-item">
				<?php if($_SESSION['role'] !='seller'){ ?>
				<li><a class="" href="admin-profile.php">Edit Profile</a></li>
				<li><a href="settings.php">Settings</a></li>
				<?php }?>
				<?php if($_SESSION['role'] =='seller' && $_SESSION['secretlogin'] =='yes'){ ?>
				<li><a href="login_as_seller.php?id=<?php echo $_SESSION['id']; ?>">Back To Admin</a></li>
				<?php }?>
				<li><a href="logout.php">Log Out</a></li>
			</ul>
		</div>
	</div>
</div>

<!-- ==================== New Order Notification (markup) ==================== -->
<!-- SIMPLE MP3 METHOD: just point src at your mp3 file -->
<audio id="newOrderSound" preload="auto">
    <source src="assets/sounds/notification.mp3" type="audio/mpeg">
</audio>

<div id="newOrderPopup" class="new-order-popup">
    <div class="new-order-popup__inner">
        <button type="button" class="new-order-popup__close" onclick="closeNewOrderPopup()">&times;</button>
        <div class="new-order-popup__icon">🛒</div>
        <h3 class="new-order-popup__title">New Order Received!</h3>
        <ul id="newOrderList" class="new-order-popup__list"></ul>
        <button type="button" class="new-order-popup__btn" onclick="goToNewOrders()">View Orders</button>
    </div>
</div>

<script>
(function () {
    // Unique per logged-in user so multiple accounts on the same
    // browser don't clash with each other's "last seen" order id.
    var STORAGE_KEY        = 'lastOrderId_<?php echo (int) $_SESSION['id']; ?>_<?php echo $_SESSION['role']; ?>';
    var SOUND_UNLOCKED_KEY = 'soundUnlocked_<?php echo (int) $_SESSION['id']; ?>';
    var CHECK_INTERVAL     = 5000; // 5 seconds
    var initialized        = false;
    var soundUnlocked      = sessionStorage.getItem(SOUND_UNLOCKED_KEY) === '1';

    function getLastId() {
        var v = localStorage.getItem(STORAGE_KEY);
        return v ? parseInt(v, 10) : 0;
    }
    function setLastId(id) {
        localStorage.setItem(STORAGE_KEY, id);
    }

    function playSound() {
        var audio = document.getElementById('newOrderSound');
        if (!audio) return;

        audio.currentTime = 0;
        var playPromise = audio.play();

        if (playPromise !== undefined) {
            playPromise.then(function () {
                soundUnlocked = true;
                sessionStorage.setItem(SOUND_UNLOCKED_KEY, '1');
            }).catch(function (err) {
                console.warn('Notification sound blocked by browser:', err);
                showSoundBanner();
            });
        }
    }

    function showSoundBanner() {
        if (soundUnlocked) return;
        document.getElementById('soundEnableBanner').classList.add('show');
    }

    function unlockSound() {
        var audio = document.getElementById('newOrderSound');
        if (!audio) return;
        audio.play().then(function () {
            audio.pause();
            audio.currentTime = 0;
            soundUnlocked = true;
            sessionStorage.setItem(SOUND_UNLOCKED_KEY, '1');
            document.getElementById('soundEnableBanner').classList.remove('show');
        }).catch(function (err) {
            console.warn('Still could not unlock sound:', err);
        });
    }

    // Clicking the banner unlocks + tests sound immediately
    document.getElementById('soundEnableBanner').addEventListener('click', unlockSound);

    // Any click anywhere on the page also unlocks it (extra safety net)
    document.addEventListener('click', function once() {
        unlockSound();
        document.removeEventListener('click', once);
    }, { once: true });

    function showPopup(orders) {
        var list = document.getElementById('newOrderList');
        list.innerHTML = '';
        orders.forEach(function (o) {
            var li = document.createElement('li');
            var line = '<strong>Order #' + o.order_number + '</strong>';
            if (o.customer_name) { line += '<br>' + o.customer_name; }
            if (o.amount !== '' && o.amount !== null) { line += ' &mdash; ' + o.amount; }
            li.innerHTML = line;
            list.appendChild(li);
        });
        document.getElementById('newOrderPopup').classList.add('show');
    }

    window.closeNewOrderPopup = function () {
        document.getElementById('newOrderPopup').classList.remove('show');
    };
    window.goToNewOrders = function () {
        window.location.href = 'orders.php';
    };

    function checkNewOrders() {
        $.ajax({
            url: 'check-new-order.php',
            method: 'GET',
            data: { last_id: getLastId() },
            dataType: 'json',
            success: function (res) {
                if (!res) return;

                // First run on this page load: just record the current
                // baseline so we don't alert for orders already known.
                if (!initialized) {
                    initialized = true;
                    setLastId(res.last_id);
                    return;
                }

                if (res.count > 0) {
                    playSound();
                    showPopup(res.new_orders);

                    // Bump the sidebar badge(s) next to Order / Incomplete Order
                    $('.navigation a[href="orders.php"] .notice, .navigation a[href="incomplete-orders.php"] .notice')
                        .each(function () {
                            var current = parseInt($(this).text(), 10) || 0;
                            $(this).text(current + res.count);
                        });
                }

                setLastId(res.last_id);
            }
        });
    }

    $(document).ready(function () {
        checkNewOrders(); // establish baseline immediately
        setInterval(checkNewOrders, CHECK_INTERVAL);
    });
})();
</script>

<div id="wrapper">
	<div class="main-content">