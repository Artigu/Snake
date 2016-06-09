<?php

function check_string($string){
	return addslashes($string);
}

session_start();
	
$mysql_host = "localhost";
$mysql_username = "username"; // Place your mysql username here 
$mysql_password = "password"; // Place your mysql password here 
$mysql_db = "snake"; 
$connection = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_db);
$loggedin = FALSE;

if(mysqli_connect_errno()){
	die(mysqli_connect_error());
}

if(isset($_SESSION['id']) && isset($_SESSION['username']) && isset($_SESSION['password'])){
	$loggedin = TRUE;
}


?>
