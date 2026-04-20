<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized");
}

if (!isset($_GET['event_id'])) {
    die("Invalid event");
}

$event_id = intval($_GET['event_id']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
</head>
<body>

<h2>📍 Mark Attendance</h2>
<p>Please allow location access.</p>

<button onclick="markAttendance()">Mark Attendance</button>

<p id="result"></p>

<script>
function markAttendance() {
    const result = document.getElementById("result");
    result.innerText = "Fetching your location...";

    navigator.geolocation.getCurrentPosition(
        pos => {
            fetch("../controllers/prototype_attendance.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    eventId: <?= $event_id ?>,
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude
                })
            })
            .then(r => r.json())
            .then(d => {
                result.innerText = d.message;
                result.style.color =
                    d.status === "present" ? "green" : "orange";
            })
            .catch(err => {
                result.innerText = "JS error;"+ err.message;
                result.style.color = "red";
            });
        },
        err => {
            result.innerText = "Location error: " + err.message;
            result.style.color = "red";
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}
</script>

</body>
</html>
