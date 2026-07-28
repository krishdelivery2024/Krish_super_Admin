<?php
    header('Access-Control-Allow-Origin: *');
	include_once('../includes/crud.php');
	include_once('../includes/variables.php');
	include_once('verify-token.php');
	// $function = new custom_functions;
    $db = new Database();
    $db->connect();
    date_default_timezone_set('Asia/Kolkata');
    include_once('../includes/custom-functions.php');
    $fn = new custom_functions;
    if(!verify_token()){
    	return false;
    }
	if(!isset($_POST['accesskey'])) {
	$output = json_encode(array('error' => true,
	'message' => 'accesskey are required.'));
    echo $output;
	$db->disconnect();
    die;
	}
    $access_key_received = $db->escapeString($fn->xss_clean($_POST['accesskey']));
    if($access_key_received != $access_key){
	$output = json_encode(array('error' => true,
	'message' => 'accesskey is incorrect.'));
    echo $output;
	$db->disconnect();
    die;
	}
    
	if(isset($_POST['type'])  && $_POST['type'] == "add-cart"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $seller_id = (isset($_POST['seller_id']))?$db->escapeString($fn->xss_clean($_POST['seller_id'])):"";
        $product_id = (isset($_POST['product_id']))?$db->escapeString($fn->xss_clean($_POST['product_id'])):"";
        $product_variant_id = (isset($_POST['product_variant_id']))?$db->escapeString($fn->xss_clean($_POST['product_variant_id'])):"";

        if(empty($user_id) || empty($seller_id) || empty($product_id) || empty($product_variant_id)){
            	$output = json_encode(array('error' => true,
                'message' => 'user_id or seller_id or product_id or product_variant_id is empty.'));
                echo $output;
                $db->disconnect();
                die;
        }
            // One selller validation
        $sql="SELECT * FROM `carts` WHERE user_id='$user_id'";  
        $db->sql($sql);
        $result=$db->getResult();
        $num_rows = $db->numRows($result);
        if($num_rows > 0){
            $old_seller_id = $result[0]['seller_id'];
            if($old_seller_id != $seller_id){
                $output = json_encode(array('error' => true,
                'message' => 'At the time you can buy from one vendor or please empty your old carts.'));
                echo $output;
                $db->disconnect();
                die;
            }
        }
        $sql="SELECT stock,serve_for FROM `product_variant` WHERE id='$product_variant_id'";  
        $db->sql($sql);
        $result=$db->getResult();
        $num_rows = $db->numRows($result);
        if($num_rows <= 0){
                $output = json_encode(array('error' => true,
                'message' => 'Invalid Product Details'));
                echo $output;
                $db->disconnect();
                die;            
        }
        $stock = $result[0]['stock'];
        $serve_for = $result[0]['serve_for'];       


        $sql_query="SELECT * FROM `carts` WHERE user_id='$user_id' AND product_variant_id='$product_variant_id'";  
        $db->sql($sql_query);
        $res=$db->getResult();
        $num = $db->numRows($res);
		if($num > 0){
          $id = $res[0]['id'];
          $quantity = $res[0]['quantity'] + 1;

          if($stock < $quantity || $serve_for == "Sold Out"){
                $output = json_encode(array('error' => true,
                'message' => 'Sold Out'));
                echo $output;
                $db->disconnect();
                die;  
            }
          
          $sql =  "UPDATE `carts` SET `quantity`='$quantity' WHERE `id`='$id'";
          $db->sql($sql);
        }else{    
            if($stock < 1 || $serve_for == "Sold Out"){
                $output = json_encode(array('error' => true,
                'message' => 'Sold Out'));
                echo $output;
                $db->disconnect();
                die;  
            }
            
            $sql="INSERT INTO `carts`(`user_id`, `seller_id`, `product_id`, `product_variant_id`, `quantity`) VALUES ('$user_id','$seller_id','$product_id','$product_variant_id','1')";  
            $db->sql($sql);
        }
        
        // $sql = "SELECT COUNT(*) AS total_products, SUM(quantity) AS total_quantity 
        //         FROM carts 
        //         WHERE user_id='$user_id'";
        // $db->sql($sql);
        // $cart = $db->getResult();
        
        // $total_products = $cart[0]['total_products'];
        // $total_quantity = $cart[0]['total_quantity'];
        
        $output = json_encode(array('error' => false,
        'message' => "Cart added successfully!",
        // 'total_products' => $total_products,   // distinct items
        // 'total_quantity' => $total_quantity    // total item count
        ));
        echo $output;	
        $db->disconnect();
        die;  

    }
    
    if(isset($_POST['type'])  && $_POST['type'] == "cart-count"){
        
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        
        $sql = "SELECT COUNT(*) AS total_products, SUM(quantity) AS total_quantity 
                FROM carts 
                WHERE user_id='$user_id'";
        $db->sql($sql);
        $cart = $db->getResult();
        
        $total_products = $cart[0]['total_products'];
        $total_quantity = $cart[0]['total_quantity'];
        
        $output = json_encode(array('error' => false,
            'message' => "Successfully!",
            'total_products' => $total_products,   // distinct items
            'total_quantity' => $total_quantity    // total item count
        ));
        echo $output;	
        $db->disconnect();
        die;  
        
    }

    if(isset($_POST['type'])  && $_POST['type'] == "decrement-cart"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $seller_id = (isset($_POST['seller_id']))?$db->escapeString($fn->xss_clean($_POST['seller_id'])):"";
        $product_id = (isset($_POST['product_id']))?$db->escapeString($fn->xss_clean($_POST['product_id'])):"";
        $product_variant_id = (isset($_POST['product_variant_id']))?$db->escapeString($fn->xss_clean($_POST['product_variant_id'])):"";

        if(empty($user_id) || empty($seller_id) || empty($product_id) || empty($product_variant_id)){
            	$output = json_encode(array('error' => true,
                'message' => 'user_id or seller_id or product_id or product_variant_id is empty.'));
                echo $output;
                $db->disconnect();
                die;
        }
        $sql_query="SELECT * FROM `carts` WHERE user_id='$user_id' AND product_variant_id='$product_variant_id'";  
        $db->sql($sql_query);
        $res=$db->getResult();
        $num = $db->numRows($res);
		if($num > 0){
          $id = $res[0]['id'];
          $quantity = $res[0]['quantity'] - 1;
          
          if($res[0]['quantity'] == 1){
          $sql =  "DELETE FROM `carts` WHERE `id`='$id'";
          $db->sql($sql);
          }else{
          $sql =  "UPDATE `carts` SET `quantity`='$quantity' WHERE `id`='$id'";
          $db->sql($sql);
          }
            $output = json_encode(array('error' => false,
            'message' => "Cart removed successfully!"));
            echo $output;	
            $db->disconnect();
             die;  

        }else{
            $output = json_encode(array('error' => true,
            'message' => "Invalid details"));
            echo $output;	
            $db->disconnect();
             die;  
        }      


    }

    if(isset($_POST['type'])  && $_POST['type'] == "delete-cart"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $cart_id = (isset($_POST['cart_id']))?$db->escapeString($fn->xss_clean($_POST['cart_id'])):"";
        if(empty($user_id) || empty($cart_id)){
            	$output = json_encode(array('error' => true,
                'message' => 'user_id or cart_id is empty.'));
                echo $output;
                $db->disconnect();
                die;
        }
        $sql_query="SELECT * FROM `carts` WHERE user_id='$user_id' AND id='$cart_id'";  
        $db->sql($sql_query);
        $res=$db->getResult();
        $num = $db->numRows($res);
		if($num > 0){

          $sql =  "DELETE FROM `carts` WHERE `id`='$cart_id'";
          $db->sql($sql);

        $output = json_encode(array('error' => false,
        'message' => "Cart deleted successfully!"));
        echo $output;	
        $db->disconnect();
         die;  

        }else{
        $output = json_encode(array('error' => true,
        'message' => "Invalid details"));
        echo $output;	
        $db->disconnect();
         die;  
        }      


    }

    if(isset($_POST['type'])  && $_POST['type'] == "over-right-cart"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        $seller_id = (isset($_POST['seller_id']))?$db->escapeString($fn->xss_clean($_POST['seller_id'])):"";
        $product_id = (isset($_POST['product_id']))?$db->escapeString($fn->xss_clean($_POST['product_id'])):"";
        $product_variant_id = (isset($_POST['product_variant_id']))?$db->escapeString($fn->xss_clean($_POST['product_variant_id'])):"";

        if(empty($user_id) || empty($seller_id) || empty($product_id) || empty($product_variant_id)){
            	$output = json_encode(array('error' => true,
                'message' => 'user_id or seller_id or product_id or product_variant_id is empty.'));
                echo $output;
                $db->disconnect();
                die;
        }

            $sql =  "DELETE FROM `carts` WHERE `user_id`='$user_id'";
            $db->sql($sql);

            $sql="INSERT INTO `carts`(`user_id`, `seller_id`, `product_id`, `product_variant_id`, `quantity`) VALUES ('$user_id','$seller_id','$product_id','$product_variant_id','1')";  
            $db->sql($sql);
        
        
        $output = json_encode(array('error' => false,
        'message' => "Cart added successfully!"));
        echo $output;	
        $db->disconnect();
         die;  

    }

    if(isset($_POST['type'])  && $_POST['type'] == "list-cart"){
    	
        $user_id = (isset($_POST['user_id']))?$db->escapeString($fn->xss_clean($_POST['user_id'])):"";
        if(empty($user_id)){
            $output = json_encode(array('error' => true,
            'message' => 'user_id is empty.'));
            echo $output;
            $db->disconnect();
            die;
        }   

            $sql="SELECT c.*,p.name as product_name,p.image,pv.type,pv.measurement,pv.weight,pv.price,pv.discounted_price,pv.product_price,pv.stock,pv.stock_unit_id,u.name as unit_name,u.short_code as unit_short_code
            FROM carts c
            INNER JOIN products p ON c.product_id = p.id
            INNER JOIN product_variant pv ON c.product_variant_id = pv.id
            INNER JOIN unit u ON pv.measurement_unit_id = u.id
            WHERE c.user_id = '$user_id' AND p.is_active ='1'";  
            $db->sql($sql);
            $res = $db->getResult();
            $num = $db->numRows($res);

		    if($num > 0){   
                for($i=0;$i<count($res);$i++){
				$res[$i]['image'] = (!empty($res[$i]['image']))?DOMAIN_URL.''.$res[$i]['image']:'';
			}     
                $output = json_encode(array('error' => false,
                'data' => $res,
                'message' => "Cart list successfully!"));
                echo $output;	
                $db->disconnect();
                 die;  
            }else{
                $output = json_encode(array('error' => false,
                'data' => [],
                'message' => "Cart list successfully!"));
                echo $output;	
                $db->disconnect();
                 die;  
            }

    }
	
	//to check if the string is json or not
	function isJSON($string){
		return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
	}
?>