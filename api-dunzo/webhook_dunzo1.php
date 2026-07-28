<?php 
header('Access-Control-Allow-Origin: *');
$json = file_get_contents('php://input');
        $data = json_decode($json);
echo "hi";
print_r($data);die;