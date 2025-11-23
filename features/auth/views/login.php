<?php require '../config/koneksi.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitehive - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="logo"><h1>Bitehive</h1></div>

    <div class="login-header">
        <h2>Login!</h2>
        <p>Please enter your credentials below to continue.</p>
    </div>

    <form method="POST" action="../controllers/proses_login.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="checkbox-group">
            <a href="ForgotPassword.php" class="forgot_Password">Forgot your password?</a>
        </div>
                 
        <button type="submit" class="btn-login">Login</button>
    </form>

    <div class="Regist">
        <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>

    <div class="footer">
        <p>&copy; 2025 Bitehive. All rights reserved.</p>
    </div>
</div>

</body>
</html>
