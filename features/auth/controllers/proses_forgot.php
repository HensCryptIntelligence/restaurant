<?php
require '../config/koneksi.php';

if (!isset($_POST['fullname'])) {
    // Jika form tidak di-submit, langsung kembali ke login
    header("Location: ../../views/login.php");
    exit;
}

$fullname = trim($_POST['fullname']); // Bersihkan input

$sql = "SELECT * FROM users WHERE fullname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fullname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Jika nama tidak ditemukan, tampilkan alert dan redirect menggunakan JavaScript
    echo "<script>
            alert('Nama tidak ditemukan!');
            window.location.href='../views/login.php';
          </script>";
    exit;
}

// Jika ditemukan, lanjut ke halaman reset password
header("Location: ../views/new_password.php?fullname=" . urlencode($fullname));
exit;
?>
