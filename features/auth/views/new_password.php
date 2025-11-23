<?php
    require '../config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitehive - New Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-container">

        <div class="logo">
            <h1>Bitehive</h1>
        </div>

        <div class="login-header">
            <h2>New Password</h2>
            <p>Create your new password below.</p>
        </div>

        <form action="../controllers/proses_new_password.php" method="POST">

            <input type="hidden" name="fullname" value="<?php echo $_GET['fullname']; ?>">


            <div class="form-group">
                <label for="password1">New Password</label>
                <input type="password" name="password1" id="password1" class="form-control" required placeholder="Enter new password">
            </div>

            <div class="form-group">
                <label for="password2">Confirm Password</label>
                <input type="password" name="password2" id="password2" class="form-control" required placeholder="Re-enter password">
            </div>

            <button type="submit" class="btn-login">Save Password</button>

            <div class="Regist">
                <p><a href="login.php">Back to login</a></p>
            </div>

        </form>

        <div class="footer">
            <p>&copy; 2025 Bitehive. All rights reserved.</p>
        </div>

    </div>

</body>
</html>
