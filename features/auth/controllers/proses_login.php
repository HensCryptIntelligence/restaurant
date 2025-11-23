<?php
session_start();
require '../config/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname']);
    $password = $_POST['password'];

    /* =====================================================
       1. LOGIN ADMIN HARDCODE
       ===================================================== */
    if ($fullname === "admin" && $password === "admin123") {

        $_SESSION['id_user'] = 0;
        $_SESSION['fullname'] = "Administrator";
        $_SESSION['role'] = "admin";

        header("Location: ../../admin/user_management/user_management.php");
        exit;
    }

    /* =====================================================
       2. CEK USER DI DATABASE
       ===================================================== */
    $sql = "SELECT id_user, fullname, password_hash, role FROM users WHERE fullname = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $fullname);
    $stmt->execute();
    $result = $stmt->get_result();

    // user tidak ditemukan
    if ($result->num_rows === 0) {
        echo "<script>alert('User tidak ditemukan!'); window.location='../views/login.php';</script>";
        exit;
    }

    $data = $result->fetch_assoc();

    /* =====================================================
       3. VALIDASI PASSWORD HASH
       ===================================================== */
    if (!password_verify($password, $data['password_hash'])) {
        echo "<script>alert('Password salah!'); window.location='../views/login.php';</script>";
        exit;
    }

    /* =====================================================
       4. SET SESSION
       ===================================================== */
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['fullname'] = $data['fullname'];
    $_SESSION['role'] = $data['role'];

    /* =====================================================
       5. REDIRECT BERDASARKAN ROLE
       ===================================================== */
    if ($data['role'] === "admin") {
        header("Location: ../../admin/user_management/user_management.php");
    } else {
        header("Location: ../views/client_dashboard.php");
    }
    exit;
}
?>
