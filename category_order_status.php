<?php
session_start();
include_once('includes/crud.php');
    include_once('includes/functions.php');
    include_once('includes/custom-functions.php');
    $fn = new custom_functions;
    $permissions = $fn->get_permissions($_SESSION['id']);
    
    $db = new Database();
    $db->connect();
    
if(isset($_POST['change']))
    {   
        // print_r("ram");
        // exit;
        
        $change =$_POST['change']; 
        $updateRecordsArray1 = $_POST['recordsArray1'];
        // print_r($updateRecordsArray1);
        // exit;

        if ($change == "updatemaincattop")
        {           
            $listingCounter1 = 1;
            foreach ($updateRecordsArray1 as $recordIDValue1)
            {       
                $query1 = "UPDATE main_category SET cat_priority = " . $listingCounter1 . " WHERE id = " . $recordIDValue1;              
                
            $db->sql($query1);
            $res = $db->getResult();
                $listingCounter1 = $listingCounter1 + 1;
            }           

        }
        if ($change == "updatesubtop")
        {           
            $listingCounter1 = 1;
            foreach ($updateRecordsArray1 as $recordIDValue1)
            {       
                $query1 = "UPDATE category SET cat_priority = " . $listingCounter1 . " WHERE id = " . $recordIDValue1;              
                
            $db->sql($query1);
            $res = $db->getResult();
                $listingCounter1 = $listingCounter1 + 1;
            }           

        }
        if($change == "updatesubcatorder"){
              $listingCounter1 = 1;
            foreach ($updateRecordsArray1 as $recordIDValue1)
            {       
                $query1 = "UPDATE subcategory SET sub_cat_priority = " . $listingCounter1 . " WHERE id = " . $recordIDValue1;              
                
            $db->sql($query1);
            $res = $db->getResult();
                $listingCounter1 = $listingCounter1 + 1;
            } 
        }
    }
?>