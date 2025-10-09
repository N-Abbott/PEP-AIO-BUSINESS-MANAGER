<?php
$servername = "localhost"; // Hostinger default
$username = "PetrongoloData"; // From Hostinger panel
$password = "P_Database1"; // From Hostinger panel
$dbname = "u915985959_PetrongoloData"; // e.g., petrongolo_db

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>