<?php
session_start();
require_once "../db.php";

// Only students can mark attendance
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    die("Unauthorized access");
}

if (!isset($_GET['event_id'])) {
    die("Invalid event");
}

$event_id   = intval($_GET['event_id']);
$student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
    <style>
        body {
            font-family: Arial;
            background:#f4f4f4;
            padding:40px;
        }
        .box {
            background:#fff;
            max-width:450px;
            margin:auto;
            padding:25px;
            text-align:center;
            border-radius:10px;
            box-shadow:0 0 8px #aaa;
        }
        button {
            padding:12px 20px;
            background:#1976d2;
            color:#fff;
            border:none;
            border-radius:6px;
            cursor:pointer;
            font-size:16px;
        }
        button:disabled {
            background:#aaa;
            cursor:not-allowed;
        }
        .msg {
            margin-top:20px;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="box">
    <h2>📍 Event Attendance</h2>
    <p>Please allow location access to mark your attendance.</p>

    <button id="attBtn" onclick="markAttendance()">Mark Attendance</button>

    <div class="msg" id="msg"></div>
</div>

<script>
function markAttendance() {
    const msg = document.getElementById("msg");
    const btn = document.getElementById("attBtn");

    btn.disabled = true;
    msg.style.color = "black";
    msg.innerText = "📍 Fetching your location...";

    if (!navigator.geolocation) {
        msg.innerText = "❌ Geolocation is not supported on this device.";
        btn.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function success(position) {
            const latitude  = position.coords.latitude;
            const longitude = position.coords.longitude;

            msg.innerText = "✅ Location verified. Marking attendance...";

            fetch("../controllers/process_attendance.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    event_id: <?= $event_id ?>,
                    latitude: latitude,
                    longitude: longitude
                })
            })
            .then(res => res.text())
.then(text => {
    console.log("RAW RESPONSE:", text);

    try {
        const data = JSON.parse(text);
        msg.innerText = data.message;
        msg.style.color = data.success ? "green" : "red";
    } catch (e) {
        msg.innerText = "❌ Server error (invalid JSON)";
        msg.style.color = "red";
    }
});

        },
        function geoError(error) {
            let message = "❌ Unable to fetch location.";

            if (error.code === 1) message = "❌ Location permission denied.";
            if (error.code === 2) message = "❌ Location unavailable.";
            if (error.code === 3) message = "❌ Location request timed out.";

            msg.innerText = message;
            msg.style.color = "red";
            btn.disabled = false;
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
