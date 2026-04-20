<?php
session_start();
require_once "../db.php";
require_once "../includes/gemini.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$event_id = (int)($_GET['event_id'] ?? 0);
if (!$event_id) die("Invalid event");

// Fetch event
$event = $conn->query("
    SELECT * FROM events WHERE id = $event_id
")->fetch_assoc();

if (!$event) die("Event not found");

// Generate AI report
$ai_report = generate_gemini_report($event);

// Save into DB
$stmt = $conn->prepare("
    INSERT INTO post_event_reports (event_id, summary, created_by, created_at, source)
    VALUES (?, ?, ?, NOW(), 'ai')
    ON DUPLICATE KEY UPDATE summary = VALUES(summary), source='ai'
");

$stmt->bind_param("isi", $event_id, $ai_report, $_SESSION['user_id']);
$stmt->execute();

header("Location: ../views/event_report_print.php?id=$event_id");
exit;
