<?php
$jsonString = file_get_contents("php://input");
$myFile = "instamojo.txt";
file_put_contents($myFile,$jsonString);
echo '{ "success": true }';
?>