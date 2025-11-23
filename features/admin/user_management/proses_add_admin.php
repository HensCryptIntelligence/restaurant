<?php
session_start();
require "../../auth/config/koneksi.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/views/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = $_POST['fullname'];
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];
    $role     = $_POST['role'];

    if ($password !== $confirm) {
        echo "<script>alert('Password tidak sama!'); window.history.back();</script>";
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (fullname, password_hash, role) 
              VALUES ('$fullname', '$hash', '$role')";

    if (mysqli_query($conn, $query)) {
        header("Location: user_management.php?success=User ditambahkan");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
