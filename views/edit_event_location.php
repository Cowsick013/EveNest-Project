<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    die("Unauthorized");
}

if (!isset($_GET['event_id'])) {
    die("Invalid event");
}

$event_id = intval($_GET['event_id']);
$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];

/* Ownership check for faculty */
if ($role === 'faculty') {
    $chk = $conn->prepare("
        SELECT id FROM events WHERE id=? AND created_by=?
    ");
    $chk->bind_param("ii", $event_id, $user_id);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows === 0) die("Unauthorized");
    $chk->close();
}

/* Event time check */
$evt = $conn->prepare("
    SELECT event_date, time_to FROM events WHERE id=?
");
$evt->bind_param("i", $event_id);
$evt->execute();
$evt->bind_result($event_date, $time_to);
$evt->fetch();
$evt->close();

$eventEnd = strtotime("$event_date $time_to");
if (time() > $eventEnd) {
    die("❌ Event has ended. Location cannot be modified.");
}

/* Fetch location */
$loc = $conn->prepare("
    SELECT latitude, longitude, radius
    FROM event_locations
    WHERE event_id=?
");
$loc->bind_param("i", $event_id);
$loc->execute();
$loc->bind_result($lat, $lon, $radius);
$loc->fetch();
$loc->close();
?>

<h2>📍 Edit Event Location</h2>

<form method="POST" action="../controllers/update_event_location.php">
    <input type="hidden" name="event_id" value="<?= $event_id ?>">

    <label>Latitude</label><br>
    <input type="number" step="any" name="latitude" value="<?= $lat ?>" required><br><br>

    <label>Longitude</label><br>
    <input type="number" step="any" name="longitude" value="<?= $lon ?>" required><br><br>

    <label>Radius (meters)</label><br>
    <input type="number" name="radius" value="<?= $radius ?>" min="50" required><br><br>

    <button type="submit">💾 Update Location</button>
    <a href="view_event.php?id=<?= $event_id ?>">Cancel</a>
</form>
