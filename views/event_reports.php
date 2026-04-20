<?php
session_start();
require_once "../db.php";
include "../includes/header.php";

$result = $conn->query("
    SELECT e.id AS event_id, e.title, e.event_date
    FROM events e
    INNER JOIN post_event_reports r ON r.event_id = e.id
    ORDER BY e.event_date DESC
");

$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
?>

<div class="page-container">
    <h2 class="page-title">Event Reports</h2>
    <p class="page-subtitle">Post-event academic documentation</p>

<?php
$currentMonth = "";

foreach ($reports as $r):
    $monthLabel = date("F Y", strtotime($r['event_date']));

    if ($monthLabel !== $currentMonth):
        if ($currentMonth !== "") echo "</div>";
        echo "<h3 class='month-title'>$monthLabel</h3>";
        echo "<div class='report-list'>";
        $currentMonth = $monthLabel;
    endif;
?>
    <div class="report-item"
         onclick="window.location.href='view_event_summary.php?event_id=<?= $r['event_id'] ?>'">
        <div class="report-title"><?= htmlspecialchars($r['title']) ?></div>
        <small><?= date("d M Y", strtotime($r['event_date'])) ?></small>
    </div>

<?php endforeach; ?>

<?php if ($currentMonth !== "") echo "</div>"; ?>
</div>

<?php include "../includes/footer.php"; ?>
