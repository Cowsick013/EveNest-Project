<?php
session_start();
require_once "../db.php";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin','faculty'])) {
    header("Location: ../views/login.php");
    exit;
}

$event_id = intval($_POST['event_id']);
$lat = $_POST['latitude'];
$lng = $_POST['longitude'];
$radius = intval($_POST['radius']);

// Insert or update location
$stmt = $conn->prepare("
    INSERT INTO event_locations (event_id, latitude, longitude, radius_meters)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE
        latitude = VALUES(latitude),
        longitude = VALUES(longitude),
        radius_meters = VALUES(radius_meters)
");

$stmt->bind_param("iddi", $event_id, $lat, $lng, $radius);

if ($stmt->execute()) {
    header("Location: ../views/view_event.php?id=$event_id");
    exit;
} else {
    echo "Failed to save location.";
}
