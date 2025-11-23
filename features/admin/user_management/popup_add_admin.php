<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <!-- POPUP ADD USER -->
    <div class="popup-overlay" id="popup">
        <div class="popup-box">
            <h2>Add User/Admin</h2>
            <form method="POST" action="proses_add_admin.php">
                <label>Full Name</label>
                <input type="text" name="fullname" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <label>Confirm Password</label>
                <input type="password" name="confirm" required>
                <label>Role</label>
                <select name="role" style="margin-top:8px;padding:10px;background:#2e2e2e;color:#fff;border-radius:6px;">
                    <option value="admin">Admin</option>
                    <option value="customer">Customer</option>
                </select>
                <div class="popup-actions">
                    <span class="cancel-btn" onclick="closePopup()">Cancel</span>
                    <button type="submit" class="btn-submit">Add User</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openPopup(){
        document.getElementById("popup").classList.add("active");
    }
    function closePopup(){
        document.getElementById("popup").classList.remove("active");
    }
    </script>
</body>
</html>