<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['date'])) {
    die("Invalid date");
}

$date = $_GET['date'];
$today = date("Y-m-d");
$current_time = date("H:i");

/* ============================
   SAFE QUERY (NO STATUS DEP)
============================ */
$sql = "
    SELECT id, title, time_from, time_to, venue, is_participatable
    FROM events
    WHERE event_date = ?
    ORDER BY time_from
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL ERROR: " . $conn->error);
}

$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Events on <?= htmlspecialchars($date) ?></title>
    <style>
        body {
            font-family: Arial;
            background:#f4f4f4;
            padding:20px;
        }
        .card {
            background:#fff;
            padding:20px;
            margin-bottom:15px;
            border-radius:10px;
            box-shadow:0 0 6px #aaa;
        }
        .badge {
            display:inline-block;
            padding:5px 10px;
            border-radius:20px;
            font-size:13px;
            margin-top:10px;
        }
        .yes {
            background:#c8e6c9;
            color:#256029;
        }
        .no {
            background:#ffcdd2;
            color:#b71c1c;
        }
        .btn {
            display:inline-block;
            margin-top:10px;
            padding:8px 14px;
            background:#1976d2;
            color:#fff;
            text-decoration:none;
            border-radius:6px;
        }
    </style>
</head>
<body>

<h2>Events on <?= htmlspecialchars($date) ?></h2>

<?php if ($result->num_rows === 0): ?>
    <p>No events scheduled for this date.</p>
<?php endif; ?>

<?php while ($e = $result->fetch_assoc()): ?>
<?php
    $event_has_ended = (
        $date < $today ||
        ($date === $today && $current_time > $e['time_to'])
    );
?>
<div class="card">
    <h3><?= htmlspecialchars($e['title']) ?></h3>

    <p><b>Time:</b> <?= $e['time_from'] ?> – <?= $e['time_to'] ?></p>
    <p><b>Venue:</b> <?= htmlspecialchars($e['venue']) ?></p>

    <?php if (
        $_SESSION['role'] === 'student' &&
        !empty($e['is_participatable']) &&
        !$event_has_ended
    ): ?>
        <span class="badge yes">Participation Open</span><br>
        <a class="btn" href="register_event.php?id=<?= $e['id'] ?>">
            Register
        </a>

    <?php elseif ($event_has_ended): ?>
        <span class="badge no">Event Completed</span>

    <?php else: ?>
        <span class="badge no">Participation Closed</span>
    <?php endif; ?>
</div>
<?php endwhile; ?>

</body>
</html>
