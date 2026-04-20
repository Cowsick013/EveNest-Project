<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";
include "../includes/header.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'principal'])) {
    die("Unauthorized access");
}

$query = $conn->query("
    SELECT e.id, e.title, e.event_date, u.name AS requester_name
    FROM events e
    LEFT JOIN users u ON e.created_by = u.id
    WHERE e.cancel_request = 'requested'
    ORDER BY e.event_date DESC
");
?>

<div class="page-container">
    <h2 class="page-title">Event Cancellation Requests</h2>
    <p class="page-subtitle">Approve or deny event cancellation requests</p>

    <?php show_flash(); ?>

    <div class="card">

<?php if ($query->num_rows === 0): ?>
    <p class="center text-muted">No pending cancellation requests.</p>
<?php else: ?>

<table class="event-table">
    <thead>
        <tr>
            <th>Event</th>
            <th>Date</th>
            <th>Requested By</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

<?php while ($row = $query->fetch_assoc()): ?>
<tr>
    <td class="event-title"><?= htmlspecialchars($row['title']) ?></td>
    <td><?= date("d M Y", strtotime($row['event_date'])) ?></td>
    <td><?= htmlspecialchars($row['requester_name'] ?? 'Unknown') ?></td>
    <td class="action-cell">
        <a class="table-link" href="../controllers/approve_cancel.php?id=<?= $row['id'] ?>">Approve</a> |
        <a class="table-link" href="../controllers/deny_cancel.php?id=<?= $row['id'] ?>">Deny</a>
    </td>
</tr>
<?php endwhile; ?>

    </tbody>
</table>

<?php endif; ?>

    </div>
</div>
<a href="principal_dashboard.php" class="btn secondary">
    ← Back to Dashboard
</a>

<?php include "../includes/footer.php"; ?>
