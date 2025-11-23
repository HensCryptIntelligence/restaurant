<?php
    require '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitehive - Forgot Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Bitehive</h1>
        </div>
        
        <div class="login-header">
            <h2>Forgot your password</h2>
            <p>Please enter your fullname below.</p>
        </div>
        
        <form action="../controllers/proses_forgot.php" method="POST">
            <div class="form-group">
                <label>Fullname</label>
                <input type="text" name="fullname" class="form-control" placeholder="Enter your fullname" required>
            </div>
            <button type="submit" class="btn-login-forgot">Reset Password</button>
        </form>
        
        <div class="footer">
            <p>&copy; 2025 Bitehive. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
