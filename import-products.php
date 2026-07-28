<?php

	include_once('includes/crud.php');
	include_once('library/PHPExcel.php');
    $db = new Database();
    $db->connect();
    $db->sql("SET NAMES 'utf8'");
include_once('includes/functions.php'); 
	date_default_timezone_set('Asia/Kolkata');
	$function = new functions;
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $sql = "SELECT * FROM delivery_method WHERE id=1";
    $db->sql($sql);
    $res2 = $db->getResult(); 
    $Delivery_by_courier = $res2[0]['Delivery_by_courier'];
    
$csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
	if($_FILES['file']['name']==='') {
		$error = "File Upload Failed";
	} else {
		if(in_array($_FILES['file']['type'],$csvMimes)){
			if(is_uploaded_file($_FILES['file']['tmp_name'])){
				
				// check file size
				if(filesize($_FILES['file']['tmp_name']) > 2000000) {
					$error = "File Size Exceed";
				} else {
					$filename = $_FILES["file"]["name"];
			$defaultimage = 'default_image.jpg';
			if(!empty($filename))
			{
				$uploadedStatus = 0;
				if ( isset($_FILES["file"])) {
					//if there was an error uploading the file
					if ($_FILES["file"]["error"] > 0) {
						echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
					}
					else {
						if (file_exists($_FILES["file"]["name"])) {
							unlink($_FILES["file"]["name"]);
						}
						$storagename = "upload.xls";
						move_uploaded_file($_FILES["file"]["tmp_name"],  $storagename);
						$uploadedStatus = 1;
					}
				}
				
				$file=$storagename;
				$objPHPExcel = PHPExcel_IOFactory::load($file);
 
				//get only the Cell Collection
				$cell_collection = $objPHPExcel->getActiveSheet()->getCellCollection();
				$k=1;
				//extract to a PHP readable array format
				foreach ($cell_collection as $cell) {
					$column = $objPHPExcel->getActiveSheet()->getCell($cell)->getColumn();
					$row = $objPHPExcel->getActiveSheet()->getCell($cell)->getRow();
					$data_value = $objPHPExcel->getActiveSheet()->getCell($cell)->getValue();
				 
					//header will/should be in row 1 only. of course this can be modified to suit your need.
					if(!empty($column)){
						    $k++;
						}
					if ($row == 1) {
						$header[$row][$column] = $data_value;
					} else {
					    if(!empty($data_value)){   
						    $arr_data[$row][$column] = $data_value;
					    }
					}
				}
				//send the data in an array format
				$data['header'] = $header;
				$data['values'] = $arr_data;
				$j=count($arr_data);
				
				$sql="SELECT COUNT(id) AS count from products";
                $db->sql($sql);
                $res_count=$db->getResult();
                //$res_count1=$res_count[0]['count']+$j;
                $res_count1=$res_count[0]['count'];
                //print_r($arr_data);die;
            //If($res_count1<=2001){
				$j=$j+2;
				for($i=2;$i<$j;$i++){
					if (array_key_exists("A",$arr_data[$i])){
						$category_name=$db->escapeString($arr_data[$i]['A']);
						$sql="SELECT id from category WHERE name='".$category_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						if(!empty($res)){
							$category_id=($res[0]['id']);
						}else{
							$sql="INSERT INTO category(name,subtitle,image)VALUES('".$category_name."','".$category_name."','')";
							$db->sql($sql);
							$res=$db->getResult();
							$sql="SELECT id from category WHERE name='".$category_name."'";
							$db->sql($sql);
							$res=$db->getResult();
							$category_id=($res[0]['id']);
						}
					}else{
						break;
					}
					
					if (array_key_exists("B",$arr_data[$i])){
						$sub_category_name=$db->escapeString($arr_data[$i]['B']);
						$sql="SELECT id from subcategory WHERE name='".$sub_category_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						if(!empty($res)){
							$subcategory_id=($res[0]['id']);
						}else{
							$slug = $function->slugify($fn->xss_clean($sub_category_name));
							$sql="INSERT INTO subcategory(category_id,name,slug,subtitle,image)VALUES(".$category_id.",'".$sub_category_name."','".$slug."','".$sub_category_name."','')";
							$db->sql($sql);
							$res=$db->getResult();
							$sql="SELECT id from subcategory WHERE name='".$sub_category_name."'";
							$db->sql($sql);
							$res=$db->getResult();
							$subcategory_id=($res[0]['id']);
						}
					}else{
						$subcategory_id=0;
					}
					
					$product_name=$db->escapeString($arr_data[$i]['C']);
					//$hsn=!empty($arr_data[$i]['K'])?$db->escapeString($arr_data[$i]['K']):'';
					$hsn='';
					$price_type= !empty($arr_data[$i]['L'])?$db->escapeString($arr_data[$i]['L']):'';
					$sgst=!empty($arr_data[$i]['M'])?$db->escapeString($arr_data[$i]['M']):'';
					$cgst=!empty($arr_data[$i]['N'])?$db->escapeString($arr_data[$i]['N']):'';
					$igst=!empty($arr_data[$i]['0'])?$db->escapeString($arr_data[$i]['0']):'';
					$description = !empty($arr_data[$i]['R'])?$db->escapeString($arr_data[$i]['R']):'';
					
					//$image=$db->escapeString($arr_data[$i]['L']);
					$sql="SELECT id,min_stock from products WHERE name='".$product_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						
						if(!empty($res)){
							$product_id=($res[0]['id']);
							$min_stock=($res[0]['min_stock']);
							if($min_stock<$arr_data[$i]['J']){
							  //  echo $i." - ".arr_data[$i]['I']."<br>";
						//	  if (array_key_exists("L",$arr_data[$i])){
							     // $image=$db->escapeString($arr_data[$i]['L']);
							    //  if(!empty($image)){
							   //       $data = array(
                               //         'image'=>$image,
                               //             "min_stock_notified"=>0
                               //             
                               //         );
							   //   }
							      
						//	  }else{
							      $data = array(
                                        "min_stock_notified"=>0
                                        
                                    );
						//	  }
                                
                                $db->update('products',$data,'id='.$product_id);
                                $db->getResult();
                            }
						}else{
						    $res_count1=$res_count1+1;
						    if($res_count1<2000){
						        $image='';
    							$slug = $function->slugify($fn->xss_clean($product_name));
    							$sql="INSERT INTO products (name,slug,category_id,subcategory_id,hsn,price_type,sgst,cgst,igst,image,other_images,description) VALUES('$product_name','$slug','$category_id','$subcategory_id','$hsn','$price_type','$sgst','$cgst','$igst','$image','','$description')";
    							$db->sql($sql);
    							$res=$db->getResult();
    							//var_dump($res);die;
    							$sql="SELECT id from products WHERE name='".$product_name."'";
    							$db->sql($sql);
    							$res=$db->getResult();
    							
    							$product_id=($res[0]['id']);
						    }else{
						        $error = "<div class='content-header'>
                                                <span class='label label-success'>Only 2000 Products Allowed</span>
                                              <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                               </div>";
                                               exit;
						    }
						}
						if($Delivery_by_courier == '1'){ 
						    
                    	   if(array_key_exists("P",$arr_data[$i])){
                							$weight=$db->escapeString($arr_data[$i]['P']);
                			}else{
                			    $error = "<div class='content-header'>
                                                <span class='label label-error'> Weight Required </span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                        </div>";
                				    break;
                				}
        	            }else{
        	                $weight=!empty($arr_data[$i]['P'])?$db->escapeString($arr_data[$i]['P']):0;
        	            }
						$price=$db->escapeString($arr_data[$i]['G']);
						if(array_key_exists("H",$arr_data[$i])){
							$discounted_price=$db->escapeString($arr_data[$i]['H']);
						}else{
							$discounted_price=$price;
						}
                        $gst=0;
						$gst=!empty($sgst)?$sgst:0;
						$gst+=!empty($cgst)?$cgst:0;
						$gst+=!empty($igst)?$igst:0;
						$product_price=0;
						$product_price=round((100*$discounted_price/(100+$gst)),2);

						$item_sgst=!empty($sgst)?round(($product_price*$sgst/100),2):'';
						$item_cgst=!empty($cgst)?round(($product_price*$cgst/100),2):'';
						$item_igst=!empty($igst)?round(($product_price*$igst/100),2):'';
						
					$type=$db->escapeString($arr_data[$i]['D']);
					if($type == 'Packet' || $type == 'packet'){
					    $type='packet';
					    $barcode_data="";
						$measurement=$db->escapeString($arr_data[$i]['E']);
						$measurement_unit_name=$db->escapeString($arr_data[$i]['F']);
						$sql="SELECT id from unit WHERE name='".$measurement_unit_name."' OR short_code='".$measurement_unit_name."'";
						
						$db->sql($sql);
						$res=$db->getResult();
						$measurement_unit_id=$res[0]['id'];
						//print_r($sql);
						
						$sql="SELECT id FROM product_variant WHERE product_id=".$product_id." AND measurement='".$measurement."' AND measurement_unit_id=".$measurement_unit_id;
						$db->sql($sql);
						$product_variant_check = $db->getResult(); 
						
						
						$stock=$db->escapeString($arr_data[$i]['I']);
						$stock_unit_name=$db->escapeString($arr_data[$i]['J']);
						$serve_for=$db->escapeString($arr_data[$i]['K']);
						$sql="SELECT id from unit WHERE name='".$stock_unit_name."' OR short_code='".$stock_unit_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						$stock_unit_id=$res[0]['id'];
						if(!empty($product_variant_check)){
						    $product_variant_id=$product_variant_check[0]['id'];
						    $sql="UPDATE product_variant SET type='".$type."',weight='".$weight."', price='".$price."',discounted_price='".$discounted_price."',product_price='".$product_price."',item_sgst='".$item_sgst."',item_cgst='".$item_cgst."',item_igst='".$item_igst."',serve_for='".$serve_for."',stock='".$stock."',stock_unit_id=".$stock_unit_id.",barcode_data='".$barcode_data."' WHERE id=".$product_variant_id;
    						$db->sql($sql);
    						$product_variant = $db->getResult(); 
						}else{
    						$sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,weight,price,discounted_price,product_price,item_sgst,item_cgst,item_igst,serve_for,stock,stock_unit_id,barcode_data) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$weight','$price','$discounted_price','$product_price','$item_sgst','$item_cgst','$item_igst','$serve_for','$stock','$stock_unit_id','$barcode_data')";
    						$db->sql($sql);
    						$product_variant = $db->getResult(); 
						}  
					}
					else if($type == 'Loose' || $type == 'loose'){
					    $type='loose';
					    $barcode_data="";
						$measurement=$db->escapeString($arr_data[$i]['E']);
						$measurement_unit_name=$db->escapeString($arr_data[$i]['F']);
						$sql="SELECT id from unit WHERE name='".$measurement_unit_name."' OR short_code='".$measurement_unit_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						$measurement_unit_id=$res[0]['id'];
						$sql="SELECT id FROM product_variant WHERE product_id=".$product_id." AND measurement='".$measurement."' AND measurement_unit_id=".$measurement_unit_id;
						$db->sql($sql);
						$product_variant_check = $db->getResult(); 
						
						$stock=$db->escapeString($arr_data[$i]['I']);
						$stock_unit_name=$db->escapeString($arr_data[$i]['J']);
						$serve_for=$db->escapeString($arr_data[$i]['K']);
						$sql="SELECT id from unit WHERE name='".$stock_unit_name."' OR short_code='".$stock_unit_name."'";
						$db->sql($sql);
						$res=$db->getResult();
						$stock_unit_id=$res[0]['id'];
						
						if(!empty($product_variant_check)){
						    $product_variant_id=$product_variant_check[0]['id'];
						    $sql="UPDATE product_variant SET type='".$type."',weight='".$weight."', price='".$price."',discounted_price='".$discounted_price."',product_price='".$product_price."',item_sgst='".$item_sgst."',item_cgst='".$item_cgst."',item_igst='".$item_igst."',serve_for='".$serve_for."',stock='".$stock."',stock_unit_id=".$stock_unit_id.",barcode_data='".$barcode_data."' WHERE id=".$product_variant_id;
    						$db->sql($sql);
    						$product_variant = $db->getResult(); 
						}else{
    						$sql="INSERT INTO product_variant (product_id,type,measurement,measurement_unit_id,weight,price,discounted_price,product_price,item_sgst,item_cgst,item_igst,serve_for,stock,stock_unit_id,barcode_data) VALUES('$product_id','$type','$measurement','$measurement_unit_id','$weight','$price','$discounted_price','$product_price','$item_sgst','$item_cgst','$item_igst','$serve_for','$stock','$stock_unit_id','$barcode_data')";
    						$db->sql($sql);
    						$product_variant = $db->getResult(); 
						}
					}
				}
				if(empty($error)){
				    	$error="<div class='content-header'>
                                                <span class='label label-success'>Product Updated Successfully</span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                    </div>";
				}
			
		//	}else{
			 //   $error = "<div class='content-header'>
                //                                <span class='label label-success'>Only 2000 Products Allowed</span>
                  //                              <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                 //                               </div>";
		//	}
			}else{
				$error = "<div class='content-header'>
                                                <span class='label label-success'>File is Invalid</span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                                                </div>";
			}	
				}					
				}
			
		}else{
			$error = "<div class='content-header'>
                                                <span class='label label-success'>File is Invalid</span>
                                                <h4><small><a  href='products.php'><i class='fa fa-angle-double-left'></i>&nbsp;&nbsp;&nbsp;Back to Products</a></small></h4>
                                                
                                                </div>";
		}
		echo $error;
	} 
	?>