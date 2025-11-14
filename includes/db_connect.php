<?php 
$servername = "localhost";
$username = "root";   // change if needed
$password = "";       // change if you set password
$dbname = "attendance_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>