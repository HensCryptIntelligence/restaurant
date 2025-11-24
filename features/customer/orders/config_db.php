<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'restaurant';

// Create connection
$connection = mysqli_connect($host, $username, $password, $database);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

ini_set('display_errors', 1);

error_reporting(E_ALL);

// Check connection
if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($connection, "utf8mb4");
?>