<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: ../public/login.php");
    exit;
}
?>

<h1>Welcome Admin!</h1>
<a href="../controllers/logout.php">Logout</a>
