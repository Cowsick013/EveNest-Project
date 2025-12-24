<?php
require_once "../db.php";

$event_id = $_GET['event_id'] ?? null;
if (!$event_id) die("Provide ?event_id=ID");

$event_id = intval($event_id);

// show event
$e = $conn->query("SELECT * FROM events WHERE id = $event_id")->fetch_assoc();
echo "<h2>Event:</h2><pre>" . htmlspecialchars(print_r($e, true)) . "</pre>";

// show post_event_reports rows for this event
$stmt = $conn->prepare("SELECT * FROM post_event_reports WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "<h2>post_event_reports rows:</h2><pre>" . htmlspecialchars(print_r($res, true)) . "</pre>";

// show photos
$stmt2 = $conn->prepare("SELECT * FROM post_event_photos WHERE report_id IN (SELECT id FROM post_event_reports WHERE event_id = ?)");
$stmt2->bind_param("i", $event_id);
$stmt2->execute();
$photos = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
echo "<h2>photos:</h2><pre>" . htmlspecialchars(print_r($photos, true)) . "</pre>";
