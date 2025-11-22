<?php
session_start();

// ==========================================
// KONEKSI DATABASE (XAMPP DEFAULT)
// ==========================================
$host = 'localhost';
$dbname = 'restaurant';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ DATABASE CONNECTION FAILED: " . $e->getMessage() . "<br><br>
         <strong>SOLUSI:</strong><br>
         1. Pastikan XAMPP MySQL sudah running<br>
         2. Buka phpMyAdmin: <a href='http://localhost/phpmyadmin'>http://localhost/phpmyadmin</a><br>
         3. Database akan otomatis dibuat saat pertama kali dijalankan");
}
?>