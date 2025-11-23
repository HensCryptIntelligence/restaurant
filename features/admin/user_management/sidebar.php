<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <span class="title">ADMIN PANEL</span>
        </div>

        <ul class="sidebar-menu">
            <li><a href="../dashboard/index.php">📊 Dashboard</a></li>
            <li><a href="../../admin/user_management/user_management.php">👥 User</a></li>
            <li><a href="../inventory/index.php">📦 Inventory</a></li>
            <li><a href="../reservation/index.php">📅 Reservation</a></li>
            <li><a href="../transaction/index.php">💳 Transaction</a></li>
            <li><a href="../auditlog/index.php">🗂 Audit Log</a></li>
        </ul>

        <div class="sidebar-logout">
            <a href="../../auth/views/login.php" class="logout-btn">🚪 Logout</a>
        </div>
    </div>

</body>
</html>