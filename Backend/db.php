<?php
$host = "localhost";   // server (XAMPP runs locally)
$user = "root";        // default MySQL username in XAMPP
$pass = "";            // default MySQL password is empty
$db   = "quicknest_db"; // your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
