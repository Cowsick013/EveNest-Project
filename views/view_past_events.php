<?php
session_start();
require_once "../db.php";

if (!isset($_GET['date'])) {
    echo "Invalid date";
    exit;
}

$date = $_GET['date'];
$user_role = $_SESSION['role'];

$stmt = $conn->prepare("SELECT * FROM events WHERE event_date = ?");
$stmt->bind_param("s", $date);
$stmt->execute();
$events = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events on <?= $date ?></title>
    <style>
        .event-box {
            width: 60%;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px #ccc;
        }
        .nav-btn {
            padding: 10px 20px;
            background: #1976d2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .nav-btn:disabled {
            background: #aaa;
        }
    </style>
</head>
<body>

<h2 style="text-align:center;">Events on <?= $date ?></h2>

<?php
$event_list = $events->fetch_all(MYSQLI_ASSOC);

if (empty($event_list)) {
    echo "<p style='text-align:center;'>No events were held on this day.</p>";
    exit;
}

$current = isset($_GET['i']) ? intval($_GET['i']) : 0;
$event = $event_list[$current];
?>

<div class="event-box">
    <h3><?= $event['title'] ?></h3>

    <p><b>Time:</b> <?= $event['time_from'] ?> - <?= $event['time_to'] ?></p>
    <p><b>Venue:</b> <?= $event['venue'] ?></p>
    <p><b>Description:</b> <?= $event['description'] ?></p>

    <hr>

    <!-- Button to view the full report -->
    <a href="view_event_report.php?id=<?= $event['id'] ?>" class="nav-btn">View Report</a>
</div>

<br>

<div style="text-align:center;">
    <!-- Previous event -->
    <?php if ($current > 0): ?>
        <a class="nav-btn"
           href="view_past_events.php?date=<?= $date ?>&i=<?= $current-1 ?>">◀ Previous</a>
    <?php endif; ?>

    <!-- Next event -->
    <?php if ($current < count($event_list)-1): ?>
        <a class="nav-btn"
           href="view_past_events.php?date=<?= $date ?>&i=<?= $current+1 ?>">Next ▶</a>
    <?php endif; ?>
</div>

</body>
</html>
