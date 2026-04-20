<?php
require_once "includes/admin_auth.php";
require_once "../db.php";
require_once "controllers/AdminEventController.php";

$events = getAllEvents($conn);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Monitoring</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "includes/sidebar.php"; ?>

<div class="main">
    <h1>Event Monitoring Panel</h1>

    <div class="card">
        <table border="1" width="100%" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Faculty</th>
                <th>Participants</th>
                <th>Attendance</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while($row = mysqli_fetch_assoc($events)): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['title'] ?></td>
                <td><?= $row['created_by'] ?></td>
                <td><?= $row['participant_count'] ?></td>
                <td><?= $row['attendance_count'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <a href="view_event.php?id=<?= $row['id'] ?>">View</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
    </div>
</div>

</body>
</html>