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
        $change = $_POST['change']; 
        $updateRecordsArraySeller = $_POST['recordsArraySeller'];

        if ($change == "updatesellerorder")
        {           
            $listingCounter1 = 1;
            foreach ($updateRecordsArraySeller as $recordIDValue1)
            {       
                $query1 = "UPDATE seller SET sel_priority = " . $listingCounter1 . " WHERE id = " . $recordIDValue1;              
                
                $db->sql($query1);
                $res = $db->getResult();
                $listingCounter1 = $listingCounter1 + 1;
            }           
        }
    }
?>