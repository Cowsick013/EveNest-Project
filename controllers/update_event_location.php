<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'faculty'])) {
    die("Unauthorized");
}

$event_id = intval($_POST['event_id']);
$lat      = floatval($_POST['latitude']);
$lon      = floatval($_POST['longitude']);
$radius   = intval($_POST['radius']);
$user_id  = $_SESSION['user_id'];
$role     = $_SESSION['role'];

/* Ownership check */
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

/* Time lock */
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
    die("Event has ended. Update not allowed.");
}

/* Update location */
$upd = $conn->prepare("
    UPDATE event_locations
    SET latitude=?, longitude=?, radius=?
    WHERE event_id=?
");
$upd->bind_param("ddii", $lat, $lon, $radius, $event_id);
$upd->execute();

header("Location: ../views/view_event.php?id=$event_id&msg=location_updated");
exit;
