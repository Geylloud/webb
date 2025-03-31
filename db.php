<?php

$host = "localhost"; 
$dbname = "teamtrack"; 
$username = "root"; 
$password = ""; 


$conn = new mysqli($host, $username, $password, $dbname, 3307);



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
