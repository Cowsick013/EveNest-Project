<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','faculty'])) {
    header("Location: login.php");
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
    <title>Set Event Location</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; padding:20px; }
        .box {
            background:#fff; padding:20px; border-radius:8px;
            width:400px; margin:auto; box-shadow:0 0 6px #aaa;
        }
        input, button {
            width:100%; padding:8px; margin-top:10px;
        }
        button { cursor:pointer; }
    </style>
</head>
<body>

<div class="box">
    <h2>Set Event Location</h2>

    <form action="../controllers/save_event_location.php" method="POST">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">

        <label>Latitude</label>
        <input type="text" id="latitude" name="latitude" required>

        <label>Longitude</label>
        <input type="text" id="longitude" name="longitude" required>

        <label>Radius (meters)</label>
        <input type="number" name="radius" value="150" min="50" max="500" required>

        <button type="button" onclick="getLocation()">Use My Current Location</button>
        <button type="submit">Save Location</button>
    </form>
</div>

<script>
function getLocation() {
    if (!navigator.geolocation) {
        alert("Geolocation not supported");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            document.getElementById("latitude").value = pos.coords.latitude;
            document.getElementById("longitude").value = pos.coords.longitude;
        },
        function() {
            alert("Location permission denied.");
        }
    );
}
</script>

</body>
</html>
