<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo "Only students can mark attendance.";
    exit;
}

if (!isset($_GET['event_id'])) {
    echo "Invalid event.";
    exit;
}

$event_id = intval($_GET['event_id']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <style>
        body { font-family: Arial; background:#f5f5f5; text-align:center; padding:40px; }
        button { padding:12px 20px; font-size:16px; cursor:pointer; }
        .msg { margin-top:20px; }
    </style>
</head>
<body>

<h2>Event Attendance</h2>
<p>Please allow location access to mark your attendance.</p>

<button onclick="markAttendance()">Mark Attendance</button>

<div class="msg" id="result"></div>

<script>
function markAttendance() {
    const result = document.getElementById("result");
    result.innerHTML = "Getting location...";

    if (!navigator.geolocation) {
        result.innerHTML = "Geolocation not supported.";
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            fetch("../controllers/submit_attendance.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    event_id: <?= $event_id ?>,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                })
            })
            .then(res => res.json())
            .then(data => {
                result.innerHTML = data.message;
            });
        },
        function() {
            result.innerHTML = "Location permission denied.";
        }
    );
}
</script>

</body>
</html>
