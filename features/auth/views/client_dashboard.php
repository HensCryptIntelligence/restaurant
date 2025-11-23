<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "customer") {
    header("Location: login.php");
    exit;
}
?>
<h1>Selamat datang Customer!</h1>
