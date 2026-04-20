<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_role = $_SESSION['role'];
$user_id   = $_SESSION['user_id'];

// Fetch events
if ($user_role === "admin") {
    $query = $conn->query("SELECT * FROM events ORDER BY event_date DESC");
} elseif ($user_role === "faculty") {
    $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date DESC");
    $stmt->execute();
    $query = $stmt->get_result();
} else {
    $query = $conn->query("SELECT * FROM events WHERE status='active' ORDER BY event_date DESC");
}

include "../includes/header.php";
?>
<div class="page-container">
    <h2 class="page-title">Event List</h2>
    <p class="page-subtitle">Institutional event records</p>

    <div class="event-list-card">
        <!-- your existing table stays -->
         <table class="event-table">
    <thead>
    <tr>
        <th style="width:45%">Event</th>
        <th style="width:20%">Date</th>
        <th style="width:20%">Status</th>
        <th style="width:15%; text-align:center;">Action</th>
    </tr>
</thead>

    <tbody>

<?php while ($event = $query->fetch_assoc()): ?>

<?php
    $row_class = "";
    $status_label = "";

    $eventEnd = new DateTime($event['event_date'] . ' ' . $event['time_to']);
    $now = new DateTime();
    $event_has_ended = ($now > $eventEnd);

    if ($event['status'] === "cancelled") {
        $row_class = "row-cancelled";
        $status_label = "<span class='status-pill cancelled'>Cancelled</span>";

    } elseif ($event['cancel_request'] === "requested") {
        $row_class = "row-pending";
        $status_label = "<span class='status-pill pending'>Cancellation Pending</span>";

    } elseif ($event_has_ended) {
        $row_class = "row-ended";
        $status_label = "<span class='status-pill ended'>Completed</span>";

    } else {
        $status_label = "<span class='status-pill active'>Active</span>";
    }
?>

<tr class="<?= $row_class ?>"
    onclick="window.location.href='view_event.php?id=<?= $event['id'] ?>'">

<td class="event-title"><?= htmlspecialchars($event['title']) ?></td>

<td class="event-date-cell">
    <?= date("d M Y", strtotime($event['event_date'])) ?>
</td>

<td class="status-cell">
    <?= $status_label ?>
</td>

<td class="action-cell">
    <a href="view_event.php?id=<?= $event['id'] ?>" class="table-link">
        View →
    </a>
</td>

</tr>


<?php endwhile; ?>

    </tbody>
</table>

</div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
