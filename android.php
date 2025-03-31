<?php
$host = "192.168.113.122"; 
$dbname = "teamtrack";
$username = "root";
$password = "";
$conn = new mysqli($host, $username, $password, $dbname, 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>