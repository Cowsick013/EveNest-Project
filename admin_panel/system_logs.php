<?php
require_once "includes/admin_auth.php";
require_once "../db.php";

$logs = mysqli_query($conn, "
    SELECT a.*, u.email 
    FROM audit_logs a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>System Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">
    <h1>System Audit Logs</h1>

    <div class="card">
        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Time</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($logs)): ?>
            <tr>
                <td><?= $row['email'] ?></td>
                <td><?= $row['action'] ?></td>
                <td><?= $row['description'] ?></td>
                <td><?= $row['created_at'] ?></td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>
</div>

</body>
</html>