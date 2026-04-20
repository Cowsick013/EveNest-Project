<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminUserController.php";

$result = getAllUsers($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<div class="main admin-container">

    <div class="admin-header">
        <h1>User Management</h1>
        <p>Manage system users, roles and access control</p>
    </div>

    <div class="table-card">
        <table class="admin-table">

            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>

            <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?= $row['id'] ?></td>

                <td>
                    <div class="user-email">
                        <?= htmlspecialchars($row['email']) ?>
                    </div>
                </td>

                <td>
                    <span class="role-tag"><?= ucfirst($row['role']) ?></span>
                </td>

                <td>
                    <span class="badge <?= $row['status'] ?>">
                        <?= ucfirst($row['status']) ?>
                    </span>
                </td>

                <td style="text-align:right;">
                    <?php if($row['status'] == 'active'): ?>
                        <a class="action-btn suspend"
                           href="controllers/AdminUserController.php?action=suspend&id=<?= $row['id'] ?>">
                           Suspend
                        </a>
                    <?php else: ?>
                        <a class="action-btn activate"
                           href="controllers/AdminUserController.php?action=activate&id=<?= $row['id'] ?>">
                           Activate
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>

        </table>
    </div>
</div>

</body>
</html>