<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    echo "<script>alert('Akses ditolak!'); window.location='login.php';</script>";
    exit;
}
?>
