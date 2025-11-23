<?php
require '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validasi password
    if ($password !== $confirm) {
        echo "<script>alert('Password tidak sama!'); window.location='../public/register.php';</script>";
        exit;
    }

    // Cek fullname duplikat
    $cek = $conn->prepare("SELECT * FROM users WHERE fullname = ?");
    $cek->bind_param("s", $fullname);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Nama sudah digunakan!'); window.location='../views/register.php';</script>";
        exit;
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert ke database (role otomatis customer)
    $sql = "INSERT INTO users (fullname, password_hash) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fullname, $hashed_password);

    if ($stmt->execute()) {
        echo "<script>alert('Registrasi berhasil!'); window.location='../views/login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
