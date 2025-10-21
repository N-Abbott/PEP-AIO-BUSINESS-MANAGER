<?php
$servername = "localhost"; // Hostinger default
$username = "u915985959_PetrongoloData"; // From Hostinger panel
$password = "P_Database1"; // From Hostinger panel
$dbname = "u915985959_PetrongoloData"; // e.g., petrongolo_db

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  // This will show connection errors
}
?>