<?php
require_once "../db.php";

// Current date and time
$today = date("Y-m-d");
$nowTime = date("H:i");

// Fetch ALL events (not only past ones)
$sql = "SELECT * FROM events ORDER BY event_date DESC, time_from DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$eventsResult = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Summaries</title>
    <style>
        body {
            font-family: Arial;
            background: #f3f3f3;
            padding: 20px;
        }
        h1 { margin-bottom: 20px; }

        .event-card {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px #bbb;
        }

        .event-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .event-date {
            color: #555;
            margin-bottom: 10px;
        }

        .label {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            margin-right: 8px;
            margin-bottom: 10px;
        }

        .upcoming { background: #bbdefb; color: #0d47a1; }
        .ongoing { background: #ffe082; color: #e65100; }
        .ended { background: #c8e6c9; color: #1b5e20; }

        .pending { background: #ffcdd2; color: #b71c1c; }
        .completed { background: #c8e6c9; color: #2e7d32; }

        .actions { margin-top: 15px; }
        .actions a {
            padding: 8px 14px;
            text-decoration: none;
            background: #007BFF;
            color: white;
            border-radius: 6px;
            margin-right: 10px;
        }
        .actions a.view {
            background: #28a745;
        }
    </style>
</head>
<body>

<h1>Event Summaries</h1>

<?php
if ($eventsResult->num_rows === 0) {
    echo "<p>No events found.</p>";
}

while ($event = $eventsResult->fetch_assoc()) {

    $event_id = $event["id"];
    $event_name = $event["title"];
    $event_date = $event["event_date"];
    $time_from = $event["time_from"];
    $time_to = $event["time_to"];

    // Determine event status (upcoming, ongoing, ended)
    if ($event_date > $today) {
        $eventStatus = "Upcoming";
        $statusClass = "upcoming";
    } elseif ($event_date == $today && $nowTime < $time_from) {
        $eventStatus = "Upcoming";
        $statusClass = "upcoming";
    } elseif ($event_date == $today && $nowTime >= $time_from && $nowTime <= $time_to) {
        $eventStatus = "Ongoing";
        $statusClass = "ongoing";
    } else {
        $eventStatus = "Ended";
        $statusClass = "ended";
    }

    // Summary check
    $summarySql = "SELECT * FROM post_event_reports WHERE event_id = ?";
    $stmt2 = $conn->prepare($summarySql);
    $stmt2->bind_param("i", $event_id);
    $stmt2->execute();
    $summaryResult = $stmt2->get_result();

    $summaryExists = $summaryResult->num_rows > 0;
?>
    <div class="event-card">

        <div class="event-title"><?= htmlspecialchars($event_name) ?></div>
        <div class="event-date"><?= htmlspecialchars($event_date) ?> (<?= $time_from ?> - <?= $time_to ?>)</div>

        <span class="label <?= $statusClass ?>"><?= $eventStatus ?></span>

        <?php if ($eventStatus === "Ended"): ?>
            <span class="label ended">Ended on <?= htmlspecialchars($event_date) ?> at <?= $time_to ?></span>
        <?php endif; ?>

        <span class="label <?= $summaryExists ? 'completed' : 'pending' ?>">
            <?= $summaryExists ? 'Summary Completed' : 'Summary Pending' ?>
        </span>

        <div class="actions">
            <?php if ($eventStatus === "Ended" && !$summaryExists): ?>
                <a href="submit_post_event.php?event_id=<?= $event_id ?>">Add Summary</a>
            <?php endif; ?>

            <?php if ($summaryExists): ?>
                <a class="view" href="view_event_summary.php?event_id=<?= $event_id ?>">View Summary</a>
            <?php endif; ?>
        </div>

    </div>

<?php } ?>

</body>
</html>
