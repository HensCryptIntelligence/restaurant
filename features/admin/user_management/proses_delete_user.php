<?php
session_start();

// hanya admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

require_once "../../auth/config/koneksi.php";

$id = $_GET['id'];

// admin tidak boleh hapus dirinya sendiri
if ($id == $_SESSION['id_user']) {
    die("Tidak boleh menghapus akun sendiri!");
}

$query = "DELETE FROM users WHERE id_user = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
$success = mysqli_stmt_execute($stmt);

if ($success) {
    header("Location: user_management.php?success=User berhasil dihapus");
} else {
    echo "Gagal menghapus user: " . mysqli_error($conn);
}
?>
