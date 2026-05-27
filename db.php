<?php 

$host = "localhost";
$dbname = "safariZone";
$username = "root";
$password = "";

$conn = mysqli_connect($host, $username, $password, $dbname);

if(!$conn){
    die("connection failed".mysqli_connect_error());
}
?>