<?php
require '../config/koneksi.php';

$fullname = $_POST['fullname'];

$sql = "SELECT * FROM users WHERE fullname = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fullname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Nama tidak ditemukan!'); window.location='forgot_password.php';</script>";
    exit;
}

header("Location: ../views/new_password.php?fullname=" . urlencode($fullname));
exit;
?>
