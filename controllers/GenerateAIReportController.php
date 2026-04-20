<?php
session_start();
require_once "../db.php";
require_once "../includes/gemini.php";

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

if (!isset($_POST['event_id'])) {
    die("Invalid request");
}

$event_id = (int)$_POST['event_id'];
$user_id = $_SESSION['user_id'];

// Fetch event data
$event = $conn->query("
    SELECT title, event_date, venue, target_audience, speaker, incharge
    FROM events
    WHERE id = $event_id
")->fetch_assoc();

if (!$event) {
    die("Event not found");
}

// Generate AI report
$summary = generate_gemini_report($event);

// If already exists → update, else insert
$existing = $conn->query("
    SELECT id FROM post_event_reports WHERE event_id = $event_id
")->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare("
        UPDATE post_event_reports 
        SET summary = ?, report_type = 'ai'
        WHERE event_id = ?
    ");
    $stmt->bind_param("si", $summary, $event_id);
} else {
    $stmt = $conn->prepare("
        INSERT INTO post_event_reports 
        (event_id, summary, dignitaries, dignitaries_words, report_type, created_by, created_at)
        VALUES (?, ?, '', '', 'ai', ?, NOW())
    ");
    $stmt->bind_param("isi", $event_id, $summary, $user_id);
}

$stmt->execute();
$stmt->close();

// Redirect back
header("Location: ../views/view_event_report.php?id=" . $event_id);
exit;
