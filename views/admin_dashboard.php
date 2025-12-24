<?php
session_start();
require_once "../db.php";
require_once "../includes/flash.php";

// Block if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Only allow admin
if ($_SESSION['role'] !== 'admin') {
    echo "Unauthorized Access!";
    exit;
}
?>

<?php
// Count pending cancellation requests
$count_req = $conn->query("SELECT COUNT(*) AS total FROM events WHERE cancel_request='requested'")
                  ->fetch_assoc()['total'];
?>


<h1>Welcome, <?= $_SESSION['user_name']; ?> (Admin)</h1>

<ul>
    <li><a href="../views/calendar.php">Create Event</a></li>
    <li><a href="event_list.php">View All Events</a></li>
    <li><a href="../views/summary_list.php">Summary</a></li>

    <!-- NEW: Cancellation Requests -->
    <li>
    <a href="../views/cancel_requests.php">
        Cancellation Requests
        <?php if ($count_req > 0): ?>
            <span style="color:red; font-weight:bold;">
                (<?= $count_req; ?>)
            </span>
        <?php endif; ?>
    </a>
</li>

</ul>


<hr>

<?php
// FETCH ALL CANCELLATION REQUESTS
$pending = $conn->query("SELECT * FROM events WHERE cancel_request = 'requested'");
?>

<h2>⚠ Event Cancellation Requests</h2>

<?php if ($pending->num_rows > 0): ?>

<table border="1" cellpadding="8" cellspacing="0" width="80%">
    <tr>
        <th>Title</th>
        <th>Date</th>
        <th>Requested By</th>
        <th>Actions</th>
    </tr>

    <?php while ($row = $pending->fetch_assoc()): ?>

        <?php
        // fetch creator name
        $creator_id = $row['created_by'];
        $u = $conn->query("SELECT name FROM users WHERE id = $creator_id")->fetch_assoc();
        ?>

        <tr>
            <td><?= $row['title']; ?></td>
            <td><?= $row['event_date']; ?></td>
            <td><?= $u['name']; ?></td>

            <td>
                <a href="../controllers/approve_cancel.php?id=<?= $row['id']; ?>">Approve</a> |
                <a href="../controllers/reject_cancel.php?id=<?= $row['id']; ?>">Reject</a>
            </td>
        </tr>

    <?php endwhile; ?>

</table>

<?php else: ?>

<p>No cancellation requests.</p>

<?php endif; ?>
