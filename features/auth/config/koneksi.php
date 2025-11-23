<?php
$host = "localhost";
$user = "root";       // username MySQL kamu
$pass = "";           // password MySQL kamu
$db   = "restaurant";   // nama database

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
