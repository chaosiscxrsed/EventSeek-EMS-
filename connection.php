<?php
$server = "127.0.0.1";
$user = "root";
$db_password = "";
$database = "ems";

$conn= new mysqli($server, $user, $password, $database);
if($conn-> connect_error)
die("connection error");
?>