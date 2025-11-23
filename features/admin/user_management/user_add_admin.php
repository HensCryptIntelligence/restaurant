<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/views/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Add New Admin</h2>

    <form action="process_add_admin.php" method="POST">
        <label>Full Name</label>
        <input type="text" name="fullname" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn-add">Add Admin</button>
    </form>
</div>

</body>
</html>
