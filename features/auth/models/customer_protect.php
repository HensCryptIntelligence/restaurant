<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "customer") {
    echo "<script>alert('Akses customer saja!'); window.location='login.php';</script>";
    exit;
}
?>
