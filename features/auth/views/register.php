<?php
require '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitehive - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Bitehive</h1>
        </div>
        
        <div class="login-header">
            <h2>Register!</h2>
            <p>Please enter your credentials below to continue.</p>
        </div>
        
        <form method="POST" action="../controllers/proses_register.php">
            
            <div class="form-group">
                <label>Fullname</label>
                <input type="text" name="fullname" class="form-control" placeholder="Enter your fullname" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm your password" required>
            </div>
                 
            <button type="submit" class="btn-login-Register">Submit</button>
        </form>

        <div class="Login">
            <p>Do you have an account? <a href="login.php">Login</a></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2025 Bitehive. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
