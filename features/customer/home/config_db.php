<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'restaurant';

// Create connection
$connection = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($connection, "utf8mb4");
?>