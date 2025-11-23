<?php

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>


<div class="container">
    <h2>User Management</h2>

    <div class="filter-container">
        <a href="?filter=all" class="btn <?= $filter === 'all' ? 'active' : '' ?>">All Users</a>
        <a href="?filter=customer" class="btn <?= $filter === 'customer' ? 'active' : '' ?>">User</a>
        <a href="?filter=admin" class="btn <?= $filter === 'admin' ? 'active' : '' ?>">Admin</a>
        <button class="btn-add" onclick="openPopup()">Add User</button>
    </div>

    <table class="user-table">
        <thead>
            <tr>
                <th>#ID</th>
                <th>Full Name</th>
                <th>Password Hash</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td>#<?= $row['id_user'] ?></td>
                <td><?= htmlspecialchars($row['fullname']) ?></td>
                <td><?= $row['password_hash'] ?></td>
                <td><?= ucfirst($row['role']) ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="proses_delete_user.php?id=<?= $row['id_user'] ?>" 
                       class="delete-btn"
                       onclick="return confirm('Yakin ingin menghapus user ini?')">🗑</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "popup_add_admin.php"; ?>

</body>
</html>
