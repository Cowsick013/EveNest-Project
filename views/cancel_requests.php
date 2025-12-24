<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

// Only admin can view
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo "Unauthorized access!";
    exit;
}

require_once "../includes/flash.php"; // Load popup system

// Fetch pending requests
$query = $conn->query("
    SELECT events.*, users.name AS requester_name 
    FROM events
    LEFT JOIN users ON events.created_by = users.id
    WHERE cancel_request='requested'
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cancellation Requests</title>
    <style>
        table {
            width: 80%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #777;
        }
        a.btn {
            padding: 6px 12px;
            text-decoration: none;
            margin-right: 8px;
            display: inline-block;
        }
        .approve { background: #4CAF50; color: white; }
        .deny { background: #d9534f; color: white; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Event Cancellation Requests</h2>

<table>
    <tr>
        <th>Event Title</th>
        <th>Requested By</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $query->fetch_assoc()): ?>
    <tr>
        <td><?= $row['title'] ?></td>
        <td><?= $row['requester_name'] ?></td>
        <td><?= $row['event_date'] ?></td>
        <td>
            <a class="btn approve" href="../controllers/approve_cancel.php?id=<?= $row['id'] ?>">
                Approve
            </a>
            <a class="btn deny" href="../controllers/deny_cancel.php?id=<?= $row['id'] ?>">
                Deny
            </a>
        </td>
    </tr>
    <?php endwhile; ?>

</table>

</body>
</html>
