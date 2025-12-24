<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../includes/flash.php";

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch events
if ($user_role === "admin") {
    $query = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
} elseif ($user_role === "faculty") {
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date DESC");
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    // Students only see active events
    $query = $conn->query("SELECT * FROM events WHERE status='active' ORDER BY event_date DESC");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event List</title>
    <style>
        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border: 1px solid #777;
            text-align: center;
        }
        tr.cancelled {
            background: #ffcdd2;
        }
        tr.pending {
            background: #ffe0b2;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 6px;
            color: white;
            font-size: 14px;
        }
        .active-badge { background: #4caf50; }
        .pending-badge { background: #ff9800; }
        .cancelled-badge { background: #f44336; }
    </style>

    <style>
    tr.ended {
        background: #e0e0e0;
    }
    .ended-badge {
        background: #616161;
    }
    </style>

</head>
<body>

<h2 style="text-align:center;">Event List</h2>

<table>
    <tr>
        <th>Title</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php while ($event = $query->fetch_assoc()): ?>

<?php
    // Default row class & label
    $row_class = "";
    $status_label = "";

    // Determine if event has ended
    // Determine if event has ended using DateTime
    $eventEnd = new DateTime($event['event_date'] . ' ' . $event['time_to']);
    $now = new DateTime();
    $event_has_ended = ($now > $eventEnd);

if ($event['status'] === "cancelled") {
    $row_class = "cancelled";
    $status_label = "<span class='badge cancelled-badge'>Cancelled</span>";

} elseif ($event['cancel_request'] === "requested") {
    $row_class = "pending";
    $status_label = "<span class='badge pending-badge'>Cancellation Pending</span>";

} elseif ($event_has_ended) {
    $row_class = "ended";
    $status_label = "<span class='badge ended-badge'>Ended</span>";

} else {
    $status_label = "<span class='badge active-badge'>Active</span>";
}

?>


    <tr class="<?= $row_class ?>">
        <td><?= $event['title']; ?></td>
        <td><?= $event['event_date']; ?></td>
        <td><?= $status_label ?></td>
        <td>
            <a href="view_event.php?id=<?= $event['id']; ?>">View</a>
        </td>
    </tr>

    <?php endwhile; ?>
</table>

</body>
</html>
