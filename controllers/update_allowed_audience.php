<?php
session_start();
require_once "../db.php";

// Only admin & faculty
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin','faculty'])) {
    die("Unauthorized access");
}

if (!isset($_POST['event_id'])) {
    die("Invalid request");
}

$event_id = (int) $_POST['event_id'];

$allowedAudience = [
    "streams" => $_POST['streams'] ?? [],
    "years"   => $_POST['years'] ?? [],
    "gender"  => $_POST['gender'] ?? "All"
];

// Save as JSON
$allowed_json = json_encode($allowedAudience);

// Update event
$stmt = $conn->prepare("
    UPDATE events 
    SET allowed_audience = ?
    WHERE id = ?
");

$stmt->bind_param("si", $allowed_json, $event_id);
$stmt->execute();

$stmt->close();

// Redirect back
header("Location: ../views/view_event.php?id=" . $event_id);
exit;
