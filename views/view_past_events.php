<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['date'])) {
    echo "Invalid date";
    exit;
}

$date = $_GET['date'];

$stmt = $conn->prepare("SELECT * FROM events WHERE event_date = ? ORDER BY time_from ASC");
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

$events = $result->fetch_all(MYSQLI_ASSOC);

if (empty($events)) {
    echo "<p style='text-align:center;'>No events held on this date.</p>";
    exit;
}

$current = isset($_GET['i']) ? intval($_GET['i']) : 0;
$current = max(0, min($current, count($events)-1));
$event = $events[$current];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Past Events</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .box {
            width:60%; margin:auto; background:#fff;
            padding:20px; border-radius:10px;
            box-shadow:0 0 8px #aaa;
        }
        .btn {
            padding:10px 16px;
            background:#1976d2;
            color:white;
            text-decoration:none;
            border-radius:6px;
            margin:5px;
            display:inline-block;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Events on <?= htmlspecialchars($date) ?></h2>

<div class="box">
    <h3><?= htmlspecialchars($event['title']) ?></h3>

    <p><b>Time:</b> <?= $event['time_from'] ?> - <?= $event['time_to'] ?></p>
    <p><b>Venue:</b> <?= htmlspecialchars($event['venue']) ?></p>
    <p><b>Description:</b> <?= nl2br(htmlspecialchars($event['description'])) ?></p>

    <hr>

    <a class="btn" href="view_event_summary.php?event_id=<?= $event['id'] ?>">
        View Summary / Report
    </a>
</div>

<div style="text-align:center; margin-top:20px;">
    <?php if ($current > 0): ?>
        <a class="btn" href="view_past_events.php?date=<?= $date ?>&i=<?= $current-1 ?>">◀ Previous</a>
    <?php endif; ?>

    <?php if ($current < count($events)-1): ?>
        <a class="btn" href="view_past_events.php?date=<?= $date ?>&i=<?= $current+1 ?>">Next ▶</a>
    <?php endif; ?>
</div>

</body>
</html>
