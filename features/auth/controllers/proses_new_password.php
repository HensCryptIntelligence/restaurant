<?php
require '../config/koneksi.php';

$fullname = $_POST['fullname'];
$pass1 = $_POST['password1'];
$pass2 = $_POST['password2'];

if ($pass1 !== $pass2) {
    echo "<script>alert('Password tidak sama!'); history.back();</script>";
    exit;
}

$hashed = password_hash($pass1, PASSWORD_DEFAULT);

$sql = "UPDATE users SET password_hash = ? WHERE fullname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $hashed, $fullname);

if ($stmt->execute()) {
    echo "<script>alert('Password berhasil diperbarui!'); window.location='../views/login.php';</script>";
} else {
    echo "Gagal update password.";
}
?>
