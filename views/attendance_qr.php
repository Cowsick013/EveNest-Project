<?php
session_start();
require_once "../db.php";
date_default_timezone_set("Asia/Kolkata");

$ATTENDANCE_WINDOW = 15 * 60; // 15 minutes in seconds
/* ===============================
   AUTH CHECK
================================ */


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'faculty') {
    die("Unauthorized");
}

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) {
    die("Invalid event");
}

$stmt = $conn->prepare(" 
    SELECT title, event_date, time_to
    FROM events
    WHERE id = ?
");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if (!$event) {
    die("Event not found");
}

$eventEnd = strtotime($event['event_date'] . " " . $event['time_to']);
$attendanceClose = $eventEnd + $ATTENDANCE_WINDOW;
$serverNow = time();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

$qrData = "https://saxicolous-deacon-nontangential.ngrok-free.dev/evenest/views/mark_attendance.php?event_id=" . $event_id;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance QR</title>
    <style>
        body { font-family: Arial; text-align:center; }
        #timer { color:red; font-size:20px; margin:15px 0; }
    </style>
</head>
<body>

<h2>📸 Attendance QR</h2>
<h3><?= htmlspecialchars($event['title']) ?></h3>

<div id="timer"></div>

<img src="https://api.qrserver.com/v1/create-qr-code/?size=260x260&data=<?= urlencode($qrData) ?>">

<p><b>Instructions:</b></p>
<ul style="list-style:none;padding:0;">
    <li>📍 Location must be ON</li>
    <li>🕒 Attendance closes automatically</li>
    <li>🚫 Withdrawn students are blocked</li>
</ul>

<a href="view_event.php?id=<?= $event_id ?>">⬅ Back to Event</a>

<script>
let serverNow = <?= $serverNow ?> * 1000;
const eventEnd = <?= $eventEnd ?> * 1000;
const attendanceClose = <?= $attendanceClose ?> * 1000;

function updateTimer() {
    serverNow += 1000;

    if (serverNow < eventEnd) {
        document.getElementById("timer").innerText =
            "⏳ Attendance not open yet";
        return;
    }

    let diff = Math.floor((attendanceClose - serverNow) / 1000);

    if (diff <= 0) {
        document.getElementById("timer").innerText =
            "⛔ Attendance window closed";
        return;
    }

    // Clamp to max 30 minutes (safety)
    diff = Math.min(diff, 15 * 60);

    const m = Math.floor(diff / 60);
    const s = diff % 60;

    document.getElementById("timer").innerText =
        `⏱ Attendance closes in ${m}m ${s}s`;
}

setInterval(updateTimer, 1000);
updateTimer();
</script>

</body>
</html>
